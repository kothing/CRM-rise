<?php

namespace App\Controllers;

use App\Libraries\Stripe;

class Subscriptions extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("subscription");
    }

    /* load subscription list view */

    function index() {
        $this->check_module_availability("module_subscription");

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("subscriptions", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("subscriptions", $this->login_user->is_admin, $this->login_user->user_type);

        $view_data["can_edit_subscriptions"] = $this->can_edit_subscriptions();

        if ($this->login_user->user_type === "staff") {
            if (!$this->can_view_subscriptions()) {
                app_redirect("forbidden");
            }

            $view_data["currencies_dropdown"] = $this->_get_currencies_dropdown();
            $view_data["conversion_rate"] = $this->get_conversion_rate_with_currency_symbol();

            return $this->template->rander("subscriptions/index", $view_data);
        } else {
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";
            return $this->template->rander("clients/subscriptions/index", $view_data);
        }
    }

    /* load new subscription modal */

    function modal_form() {
        if (!$this->can_edit_subscriptions()) {
            app_redirect("forbidden");
        }

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric"
        ));

        $client_id = $this->request->getPost('client_id');
        $subscription_id = $this->request->getPost('id');
        $model_info = $this->Subscriptions_model->get_one($subscription_id);
        $this->can_edit_this_subscription($subscription_id);

        //check if estimate_id/order_id/proposal_id/contract_id posted. if found, generate related information
        $estimate_id = $this->request->getPost('estimate_id');
        $contract_id = $this->request->getPost('contract_id');
        $proposal_id = $this->request->getPost('proposal_id');
        $order_id = $this->request->getPost('order_id');
        $view_data['estimate_id'] = $estimate_id;
        $view_data['contract_id'] = $contract_id;
        $view_data['proposal_id'] = $proposal_id;
        $view_data['order_id'] = $order_id;

        $view_data['model_info'] = $model_info;

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['clients_dropdown'] = array("" => "-") + $this->Clients_model->get_dropdown_list(array("company_name"), "id", array("is_lead" => 0));

        $view_data['client_id'] = $client_id;

        //prepare label suggestions
        $view_data['label_suggestions'] = $this->make_labels_dropdown("subscription", $model_info->labels);

        //clone subscription
        $is_clone = $this->request->getPost('is_clone');
        $view_data['is_clone'] = $is_clone;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("subscriptions", $model_info->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        $view_data['companies_dropdown'] = $this->_get_companies_dropdown();
        if (!$model_info->company_id) {
            $view_data['model_info']->company_id = get_default_company_id();
        }

        return $this->template->view('subscriptions/modal_form', $view_data);
    }

    /* add or edit an subscription */

    function save() {
        if (!$this->can_edit_subscriptions()) {
            app_redirect("forbidden");
        }

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "subscription_client_id" => "required|numeric",
            "repeat_type" => "required",
        ));

        $client_id = $this->request->getPost('subscription_client_id');
        $id = $this->request->getPost('id');
        $this->can_edit_this_subscription($id);

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "subscription");
        $new_files = unserialize($files_data);

        $bill_date = $this->request->getPost('subscription_bill_date');
        $repeat_every = $this->request->getPost('repeat_every') ? $this->request->getPost('repeat_every') : 1;
        $repeat_type = $this->request->getPost('repeat_type');

        $subscription_data = array(
            "title" => $this->request->getPost('title'),
            "client_id" => $client_id,
            "bill_date" => $bill_date,
            "end_date" => NULL,
            "tax_id" => $this->request->getPost('tax_id') ? $this->request->getPost('tax_id') : 0,
            "tax_id2" => $this->request->getPost('tax_id2') ? $this->request->getPost('tax_id2') : 0,
            "company_id" => $this->request->getPost('company_id') ? $this->request->getPost('company_id') : get_default_company_id(),
            "repeat_every" => $repeat_every,
            "repeat_type" => $repeat_type ? $repeat_type : NULL,
            "no_of_cycles" => 0,
            "note" => $this->request->getPost('subscription_note'),
            "labels" => $this->request->getPost('labels')
        );

        if ($id) {
            $subscription_info = $this->Subscriptions_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $subscription_info->files, $new_files);
        }

        $subscription_data["files"] = serialize($new_files);

        $is_clone = $this->request->getPost('is_clone');
        $estimate_id = $this->request->getPost('estimate_id');

        $main_subscription_id = "";
        if (($is_clone && $id) || $estimate_id) {
            if ($is_clone && $id) {
                $main_subscription_id = $id; //store main subscription id to get items later
                $id = ""; //one cloning subscription, save as new
            }
        }

        if (!$bill_date) {
            $bill_date = get_today_date();
        }

        if (!$subscription_data["bill_date"]) {
            $subscription_data["bill_date"] = NULL;
        }

        if ($id) {
            //update
            if ($this->request->getPost('next_recurring_date')) { //submitted any recurring date? set it.
                $subscription_data['next_recurring_date'] = $this->request->getPost('next_recurring_date');
            } else {
                //re-calculate the next recurring date, if any recurring fields has changed.
                $subscription_info = $this->Subscriptions_model->get_one($id);
                if ($subscription_info->repeat_every != $subscription_data['repeat_every'] || $subscription_info->repeat_type != $subscription_data['repeat_type'] || $subscription_info->bill_date != $subscription_data['bill_date']) {
                    $subscription_data['next_recurring_date'] = add_period_to_date($bill_date, $repeat_every, $repeat_type);
                }
            }
        } else {
            //insert new
            $subscription_data['next_recurring_date'] = add_period_to_date($bill_date, $repeat_every, $repeat_type);
        }


        //recurring date must have to set a future date
        if (get_array_value($subscription_data, "next_recurring_date") && get_today_date() >= $subscription_data['next_recurring_date']) {
            echo json_encode(array("success" => false, 'message' => app_lang('past_recurring_date_error_message_title'), 'next_recurring_date_error' => app_lang('past_recurring_date_error_message'), "next_recurring_date_value" => $subscription_data['next_recurring_date']));
            return false;
        }

        $subscription_id = $this->Subscriptions_model->ci_save($subscription_data, $id);
        if ($subscription_id) {

            if ($is_clone && $main_subscription_id) {
                //add subscription items

                save_custom_fields("subscriptions", $subscription_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a subscription

                $subscription_items = $this->Subscription_items_model->get_all_where(array("subscription_id" => $main_subscription_id, "deleted" => 0))->getResult();

                foreach ($subscription_items as $subscription_item) {
                    //prepare new subscription item data
                    $subscription_item_data = (array) $subscription_item;
                    unset($subscription_item_data["id"]);
                    $subscription_item_data['subscription_id'] = $subscription_id;

                    $subscription_item = $this->Subscription_items_model->ci_save($subscription_item_data);
                }
            } else {
                save_custom_fields("subscriptions", $subscription_id, $this->login_user->is_admin, $this->login_user->user_type);
            }

            //submitted copy_items_from_estimate/copy_items_from_order/copy_items_from_proposal/copy_items_from_contract? copy all items from the associated one
            $copy_items_from_estimate = $this->request->getPost("copy_items_from_estimate");
            $copy_items_from_contract = $this->request->getPost("copy_items_from_contract");
            $copy_items_from_proposal = $this->request->getPost("copy_items_from_proposal");
            $copy_items_from_order = $this->request->getPost("copy_items_from_order");
            $this->_copy_related_items_to_subscription($copy_items_from_estimate, $copy_items_from_proposal, $copy_items_from_order, $copy_items_from_contract, $subscription_id);

            echo json_encode(array("success" => true, "data" => $this->_row_data($subscription_id), 'id' => $subscription_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    private function _copy_related_items_to_subscription($copy_items_from_estimate, $copy_items_from_proposal, $copy_items_from_order, $copy_items_from_contract, $subscription_id) {
        if (!($copy_items_from_estimate || $copy_items_from_proposal || $copy_items_from_order || $copy_items_from_contract)) {
            return false;
        }

        $copy_items = null;
        if ($copy_items_from_estimate) {
            $copy_items = $this->Estimate_items_model->get_details(array("estimate_id" => $copy_items_from_estimate))->getResult();
        } else if ($copy_items_from_contract) {
            $copy_items = $this->Contract_items_model->get_details(array("contract_id" => $copy_items_from_contract))->getResult();
        } else if ($copy_items_from_proposal) {
            $copy_items = $this->Proposal_items_model->get_details(array("proposal_id" => $copy_items_from_proposal))->getResult();
        } else if ($copy_items_from_order) {
            $copy_items = $this->Order_items_model->get_details(array("order_id" => $copy_items_from_order))->getResult();
        }

        if (!$copy_items) {
            return false;
        }

        foreach ($copy_items as $data) {
            $subscription_item_data = array(
                "subscription_id" => $subscription_id,
                "title" => $data->title ? $data->title : "",
                "description" => $data->description ? $data->description : "",
                "quantity" => $data->quantity ? $data->quantity : 0,
                "unit_type" => $data->unit_type ? $data->unit_type : "",
                "rate" => $data->rate ? $data->rate : 0,
                "total" => $data->total ? $data->total : 0,
            );
            $this->Subscription_items_model->ci_save($subscription_item_data);
        }
    }

    /* delete or undo an subscription */

    function delete() {
        if (!$this->can_edit_subscriptions()) {
            app_redirect("forbidden");
        }

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');

        $subscription_info = $this->Subscriptions_model->get_one($id);

        if ($this->Subscriptions_model->delete($id)) {
            //delete the files
            $file_path = get_setting("timeline_file_path");
            if ($subscription_info->files) {
                $files = unserialize($subscription_info->files);

                foreach ($files as $file) {
                    delete_app_files($file_path, array($file));
                }
            }

            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    /* list of subscription of a specific client, prepared for datatable  */

    function subscription_list_data_of_client($client_id) {
        if (!$this->can_view_subscriptions($client_id)) {
            app_redirect("forbidden");
        }

        validate_numeric_value($client_id);
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("subscriptions", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "client_id" => $client_id,
            "custom_fields" => $custom_fields,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("subscriptions", $this->login_user->is_admin, $this->login_user->user_type)
        );

        //don't show draft subscriptions to client
        if ($this->login_user->user_type == "client") {
            $options["exclude_draft"] = true;
        }


        $list_data = $this->Subscriptions_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("subscriptions", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Subscriptions_model->get_details($options)->getRow();
        return $this->_make_row($data, $custom_fields);
    }

    // list of recurring subscriptions, prepared for datatable
    function list_data() {
        if (!$this->can_view_subscriptions()) {
            app_redirect("forbidden");
        }

        $options = array(
            "next_recurring_start_date" => $this->request->getPost("next_recurring_start_date"),
            "next_recurring_end_date" => $this->request->getPost("next_recurring_end_date"),
            "currency" => $this->request->getPost("currency")
        );

        $list_data = $this->Subscriptions_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }

        echo json_encode(array("data" => $result));
    }

    /* prepare a row of recurring subscription list table */

    private function _make_row($data) {

        if ($this->login_user->user_type == "staff") {
            $subscription_url = anchor(get_uri("subscriptions/view/" . $data->id), get_subscription_id($data->id));
        } else {
            $subscription_url = anchor(get_uri("subscriptions/preview/" . $data->id), get_subscription_id($data->id));
        }

        $cycles = $data->no_of_cycles_completed . "/" . $data->no_of_cycles;
        if (!$data->no_of_cycles) { //if not no of cycles, so it's infinity
            $cycles = $data->no_of_cycles_completed . "/&#8734;";
        }

        $subscription_type = $this->_get_subscription_type_label($data, true);
        $subscription_status = $this->_get_subscription_status_label($data, true);
        $cycle_class = "";

        if (!$data->bill_date) {
            $next_billing_date = "-";
            $bill_date = 0;
        } else {
            $next_billing_date = format_to_date($data->bill_date, false);
            $bill_date = $data->bill_date;
        }

        if ($data->no_of_cycles_completed > 0 && $data->no_of_cycles_completed == $data->no_of_cycles) {
            $subscription_status = "<span class='badge bg-danger large'>" . app_lang("stopped") . "</span>";
            $cycle_class = "text-danger";
        }

        return array(
            $data->id,
            $subscription_url,
            $data->title,
            $subscription_type,
            anchor(get_uri("clients/view/" . $data->client_id), $data->company_name),
            $bill_date,
            $next_billing_date,
            $data->repeat_every . " " . app_lang("interval_" . $data->repeat_type),
            "<span class='$cycle_class'>" . $cycles . "</span>",
            $subscription_status,
            to_currency($data->subscription_value, $data->currency_symbol),
            $this->_make_options_dropdown($data)
        );
    }

    //prepare options dropdown for subscriptions list
    private function _make_options_dropdown($data) {
        $options = "";

        if ($data->status !== "active" && $data->status !== "cancelled") {
            $options .= '<li role="presentation">' . modal_anchor(get_uri("subscriptions/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit'), array("title" => app_lang('edit_subscription'), "data-post-id" => $data->id, "class" => "dropdown-item")) . '</li>';
        }

        if ($data->status !== "active") {
            $options .= '<li role="presentation">' . js_anchor("<i data-feather='x' class='icon-16'></i>" . app_lang('delete'), array('title' => app_lang('delete_subscription'), "class" => "delete dropdown-item", "data-id" => $data->id, "data-action-url" => get_uri("subscriptions/delete"), "data-action" => "delete-confirmation")) . '</li>';
        }

        if ($options) {
            return '
                <span class="dropdown inline-block">
                    <button class="btn btn-default dropdown-toggle caret mt0 mb0" type="button" data-bs-toggle="dropdown" aria-expanded="true" data-bs-display="static">
                        <i data-feather="tool" class="icon-16"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" role="menu">' . $options . '</ul>
                </span>';
        } else {
            return "";
        }
    }

    //prepare subscription status label 
    private function _get_subscription_status_label($data, $return_html = true) {
        return get_subscription_status_label($data, $return_html);
    }

    /* load subscription details view */

    function view($subscription_id = 0) {
        if (!($this->can_view_subscriptions() && $subscription_id)) {
            app_redirect("forbidden");
        }

        validate_numeric_value($subscription_id);
        $view_data = get_subscription_making_data($subscription_id);
        if (!$view_data) {
            show_404();
        }

        $view_data['subscription_status'] = $this->_get_subscription_status_label($view_data["subscription_info"], false);
        $view_data["can_edit_subscriptions"] = $this->can_edit_subscriptions();

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("invoices", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("invoices", $this->login_user->is_admin, $this->login_user->user_type);

        $view_data["has_item_in_this_subscription"] = $this->has_item_in_this_subscription($subscription_id);

        return $this->template->rander("subscriptions/view", $view_data);
    }

    private function has_item_in_this_subscription($subscription_id) {
        return $this->Subscription_items_model->get_details(array("subscription_id" => $subscription_id))->getRow();
    }

    /* subscription total section */

    private function _get_subscription_total_view($subscription_id = 0) {
        $view_data["subscription_total_summary"] = $this->Subscriptions_model->get_subscription_total_summary($subscription_id);
        $view_data["subscription_id"] = $subscription_id;
        $view_data["can_edit_subscriptions"] = $this->can_edit_subscriptions();
        return $this->template->view('subscriptions/subscription_total_section', $view_data);
    }

    /* load item modal */

    function item_modal_form() {
        if (!$this->can_edit_subscriptions()) {
            app_redirect("forbidden");
        }

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $subscription_id = $this->request->getPost('subscription_id');

        $view_data['model_info'] = $this->Subscription_items_model->get_one($this->request->getPost('id'));
        if (!$subscription_id) {
            $subscription_id = $view_data['model_info']->subscription_id;
        }
        $this->can_edit_this_subscription($subscription_id);
        $view_data['subscription_id'] = $subscription_id;
        return $this->template->view('subscriptions/item_modal_form', $view_data);
    }

    /* add or edit an subscription item */

    function save_item() {
        if (!$this->can_edit_subscriptions()) {
            app_redirect("forbidden");
        }

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "subscription_id" => "required|numeric"
        ));

        $subscription_id = $this->request->getPost('subscription_id');
        $this->can_edit_this_subscription($subscription_id);
        $id = $this->request->getPost('id');
        if (!$id && $this->has_item_in_this_subscription($subscription_id)) {
            app_redirect("forbidden");
        }

        $rate = unformat_currency($this->request->getPost('subscription_item_rate'));
        $quantity = unformat_currency($this->request->getPost('subscription_item_quantity'));

        $subscription_item_data = array(
            "subscription_id" => $subscription_id,
            "title" => $this->request->getPost('subscription_item_title'),
            "description" => $this->request->getPost('subscription_item_description'),
            "quantity" => $quantity,
            "unit_type" => $this->request->getPost('subscription_unit_type'),
            "rate" => unformat_currency($this->request->getPost('subscription_item_rate')),
            "total" => $rate * $quantity,
        );

        $subscription_item_id = $this->Subscription_items_model->ci_save($subscription_item_data, $id);
        if ($subscription_item_id) {

            //check if the add_new_item flag is on, if so, add the item to libary. 
            $add_new_item_to_library = $this->request->getPost('add_new_item_to_library');
            if ($add_new_item_to_library) {
                $library_item_data = array(
                    "title" => $this->request->getPost('subscription_item_title'),
                    "description" => $this->request->getPost('subscription_item_description'),
                    "unit_type" => $this->request->getPost('subscription_unit_type'),
                    "rate" => unformat_currency($this->request->getPost('subscription_item_rate'))
                );
                $this->Items_model->ci_save($library_item_data);
            }

            $options = array("id" => $subscription_item_id);
            $item_info = $this->Subscription_items_model->get_details($options)->getRow();
            echo json_encode(array("success" => true, "subscription_id" => $item_info->subscription_id, "data" => $this->_make_item_row($item_info), "subscription_total_view" => $this->_get_subscription_total_view($item_info->subscription_id), 'id' => $subscription_item_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* list of subscription items, prepared for datatable  */

    function item_list_data($subscription_id = 0) {
        validate_numeric_value($subscription_id);
        if (!$this->can_view_subscriptions()) {
            app_redirect("forbidden");
        }

        $list_data = $this->Subscription_items_model->get_details(array("subscription_id" => $subscription_id))->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of subscription item list table */

    private function _make_item_row($data) {
        $item = "<div class='item-row strong mb5' data-id='$data->id'>$data->title</div>";
        if ($data->description) {
            $item .= "<span class='text-wrap'>" . nl2br($data->description) . "</span>";
        }
        $type = $data->unit_type ? $data->unit_type : "";

        return array(
            $item,
            to_decimal_format($data->quantity) . " " . $type,
            to_currency($data->rate, $data->currency_symbol),
            to_currency($data->total, $data->currency_symbol),
            modal_anchor(get_uri("subscriptions/item_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_subscription'), "data-post-id" => $data->id))
        );
    }

    /* prepare suggestion of subscription item */

    function get_subscription_item_suggestion() {
        $key =$this->request->getPost("q");
        $suggestion = array();

        $items = $this->Subscription_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->title, "text" => $item->title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . app_lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_subscription_item_info_suggestion() {
        $item = $this->Subscription_items_model->get_item_info_suggestion($this->request->getPost("item_name"));
        if ($item) {
            $item->rate = $item->rate ? to_decimal_format($item->rate) : "";
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //view html is accessable to client only.
    function preview($subscription_id = 0) {
        if ($subscription_id) {
            validate_numeric_value($subscription_id);
            $view_data = get_subscription_making_data($subscription_id);

            $this->_check_subscription_access_permission($view_data);

            $view_data['subscription_preview'] = view("subscriptions/subscription_pdf", $view_data);

            $view_data['subscription_id'] = $subscription_id;
            $view_data['payment_methods'] = $this->Payment_methods_model->get_available_online_payment_methods();

            $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("invoices", $this->login_user->is_admin, $this->login_user->user_type);
            $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("invoices", $this->login_user->is_admin, $this->login_user->user_type);

            return $this->template->rander("subscriptions/subscription_preview", $view_data);
        } else {
            show_404();
        }
    }

    private function _check_subscription_access_permission($subscription_data) {
        //check for valid subscription
        if (!$subscription_data) {
            show_404();
        }

        //check for security
        $subscription_info = get_array_value($subscription_data, "subscription_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->client_id != $subscription_info->client_id || $subscription_info->status == "draft") {
                app_redirect("forbidden");
            }
        } else {
            if (!$this->can_view_subscriptions()) {
                app_redirect("forbidden");
            }
        }
    }

    function get_subscription_status_bar($subscription_id = 0) {
        if (!$this->can_view_subscriptions()) {
            app_redirect("forbidden");
        }

        validate_numeric_value($subscription_id);
        $view_data["subscription_info"] = $this->Subscriptions_model->get_details(array("id" => $subscription_id))->getRow();
        $view_data['subscription_status_label'] = $this->_get_subscription_status_label($view_data["subscription_info"]);
        return $this->template->view('subscriptions/subscription_status_bar', $view_data);
    }

    function update_subscription_status($subscription_id = 0, $status = "", $client_id = 0) {
        if (!$this->can_edit_subscriptions($client_id)) {
            app_redirect("forbidden");
        }

        validate_numeric_value($subscription_id);
        if ($subscription_id && $status) {
            //change the draft status of the subscription
            $this->Subscriptions_model->update_subscription_status($subscription_id, $status);

            //save extra information for cancellation
            if ($status == "cancelled") {
                $data = array(
                    "cancelled_at" => get_current_utc_time(),
                    "cancelled_by" => $this->login_user->id
                );

                $this->Subscriptions_model->ci_save($data, $subscription_id);

                $subscription_info = $this->Subscriptions_model->get_one($subscription_id);
                if ($subscription_info->stripe_subscription_id) {
                    //cancel stripe subscription
                    $Stripe = new Stripe();
                    $Stripe->cancel_subscription($subscription_info->stripe_subscription_id);
                }
            }

            echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
        }

        return "";
    }

    function activate_as_stripe_subscription_modal_form($subscription_id) {
        if (!$this->can_edit_subscriptions()) {
            app_redirect("forbidden");
        }

        if (!$subscription_id) {
            show_404();
        }

        validate_numeric_value($subscription_id);
        $view_data["subscription_info"] = $this->Subscriptions_model->get_one($subscription_id);

        $stripe = new Stripe();
        $products = $stripe->get_products_list();
        $stripe_products_dropdown = array(array("id" => "", "text" => "-"));
        foreach ($products as $product) {
            $stripe_products_dropdown[] = array("id" => $product->id, "text" => $product->name);
        }

        $view_data['stripe_products_dropdown'] = $stripe_products_dropdown;

        if ($view_data["subscription_info"]->stripe_product_id) {
            $view_data['stripe_product_prices_dropdown'] = $this->get_prices_of_selected_product($view_data["subscription_info"]->stripe_product_id, true);
        } else {
            $view_data['stripe_product_prices_dropdown'] = array(array("id" => "", "text" => "-"));
        }

        return $this->template->view('subscriptions/activate_as_stripe_subscription_modal_form', $view_data);
    }

    function activate_as_stripe_subscription() {
        if (!$this->can_edit_subscriptions()) {
            app_redirect("forbidden");
        }

        $this->validate_submitted_data(array(
            "subscription_id" => "required|numeric",
            "stripe_product" => "required",
            "stripe_product_price_id" => "required"
        ));

        $subscription_id = $this->request->getPost('subscription_id');
        $stripe_product = $this->request->getPost('stripe_product');
        $stripe_product_price_id = $this->request->getPost('stripe_product_price_id');

        //check price
        $stripe = new Stripe();
        $stripe_price_info = $stripe->retrieve_price($stripe_product_price_id);
        $subscription_item_info = $this->Subscription_items_model->get_one_where(array("subscription_id" => $subscription_id, "deleted" => 0));
        $subscription_info = $this->Subscriptions_model->get_details(array("id" => $subscription_id))->getRow();

        //check taxes
        if (($subscription_info->tax_id && !$subscription_info->stripe_tax_id) || ($subscription_info->tax_id2 && !$subscription_info->stripe_tax_id2)) {
            echo json_encode(array("success" => false, 'message' => app_lang("stripe_tax_error_message")));
            return false;
        }

        if (($stripe_price_info->recurring->interval . "s") !== $subscription_info->repeat_type) {
            echo json_encode(array("success" => false, 'message' => app_lang("stripe_price_error_message")));
            return false;
        }

        if ($subscription_item_info->id) {
            if (($stripe_price_info->unit_amount / 100) != $subscription_item_info->rate) {
                echo json_encode(array("success" => false, 'message' => app_lang("stripe_price_error_message")));
                return false;
            }
        } else {
            //no item added yet, add from the stripe price
            $price_text = $stripe_price_info->unit_amount / 100;
            $price_text .= " " . strtoupper($stripe_price_info->currency);
            $price_text .= " / " . ucfirst($stripe_price_info->recurring->interval);
            $subscription_item_data = array(
                "subscription_id" => $subscription_id,
                "title" => $price_text,
                "quantity" => 1,
                "rate" => $stripe_price_info->unit_amount / 100,
                "total" => $stripe_price_info->unit_amount / 100,
            );

            $this->Subscription_items_model->ci_save($subscription_item_data);
        }

        $subscription_data = array(
            "stripe_product_id" => $stripe_product,
            "stripe_product_price_id" => $stripe_product_price_id,
            "status" => "pending",
            "type" => "stripe",
        );

        $save_id = $this->Subscriptions_model->ci_save($subscription_data, $subscription_id);
        if ($save_id) {
            log_notification("subscription_request_sent", array("subscription_id" => $subscription_id));
            echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function get_stripe_checkout_session() {
        $stripe = new Stripe();

        try {
            $session = $stripe->get_stripe_checkout_session($this->request->getPost("input_data"), $this->login_user->id);
            if ($session->id) {
                echo json_encode(array("success" => true, "session_id" => $session->id, "publishable_key" => $stripe->get_publishable_key()));
            } else {
                echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
            }
        } catch (\Exception $ex) {
            echo json_encode(array("success" => false, "message" => $ex->getMessage()));
        }
    }

    function get_prices_of_selected_product($product_id, $return_data = false) {
        if (!$product_id) {
            return false;
        }

        $Stripe = new Stripe();
        $stripe_product_prices = $Stripe->retrieve_all_prices_of_the_product($product_id);

        $stripe_product_prices_dropdown = array(array("id" => "", "text" => "-"));
        foreach ($stripe_product_prices as $stripe_product_price) {
            $price_text = $stripe_product_price->unit_amount / 100;
            $price_text .= " " . strtoupper($stripe_product_price->currency);
            $price_text .= " / " . ucfirst($stripe_product_price->recurring->interval);

            $stripe_product_prices_dropdown[] = array("id" => $stripe_product_price->id, "text" => $price_text);
        }

        if ($return_data) {
            return $stripe_product_prices_dropdown;
        } else {
            echo json_encode(array(
                "stripe_product_prices_dropdown" => $stripe_product_prices_dropdown,
            ));
        }
    }

    /* upload a file */

    function upload_file() {
        upload_file_to_temp();
    }

    /* check valid file for subscriptions */

    function validate_subscriptions_file() {
        return validate_post_file($this->request->getPost("file_name"));
    }

    function file_preview($id = "", $key = "") {
        if ($id) {
            validate_numeric_value($id);
            $subscription_info = $this->Subscriptions_model->get_one($id);
            $files = unserialize($subscription_info->files);
            $file = get_array_value($files, $key);

            $file_name = get_array_value($file, "file_name");
            $file_id = get_array_value($file, "file_id");
            $service_type = get_array_value($file, "service_type");

            $view_data["file_url"] = get_source_url_of_file($file, get_setting("timeline_file_path"));
            $view_data["is_image_file"] = is_image_file($file_name);
            $view_data["is_iframe_preview_available"] = is_iframe_preview_available($file_name);
            $view_data["is_google_preview_available"] = is_google_preview_available($file_name);
            $view_data["is_viewable_video_file"] = is_viewable_video_file($file_name);
            $view_data["is_google_drive_file"] = ($file_id && $service_type == "google") ? true : false;

            return $this->template->view("subscriptions/file_preview", $view_data);
        } else {
            show_404();
        }
    }

    function activate_as_internal_subscription_modal_form($subscription_id) {
        if (!$this->can_edit_subscriptions()) {
            app_redirect("forbidden");
        }

        if (!$subscription_id) {
            show_404();
        }

        validate_numeric_value($subscription_id);
        $view_data["subscription_id"] = $subscription_id;

        return $this->template->view('subscriptions/activate_as_internal_subscription_modal_form', $view_data);
    }

    function activate_as_internal_subscription() {
        if (!$this->can_edit_subscriptions()) {
            app_redirect("forbidden");
        }

        $subscription_id = $this->request->getPost('subscription_id');

        validate_numeric_value($subscription_id);
        $subscription_info = $this->Subscriptions_model->get_subscription_total_summary($subscription_id);

        if (!$subscription_info->subscription_total) {
            echo json_encode(array("success" => false, 'message' => app_lang("subscription_toatl_can_not_empty_message")));
            return false;
        }

        if ($subscription_id) {
            $this->Subscriptions_model->update_subscription_status($subscription_id, "active");

            //starting local subscription
            create_invoice_from_subscription($subscription_id);

            echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
        }
    }

    //prepare subscription type label 
    private function _get_subscription_type_label($data, $return_html = true) {
        return get_subscription_type_label($data, $return_html);
    }

}

/* End of file Subscriptions.php */
/* Location: ./app/Controllers/Subscriptions.php */