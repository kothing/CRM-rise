<?php

namespace App\Controllers;

class Estimates extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("estimate");
    }

    /* load estimate list view */

    function index() {
        $this->check_module_availability("module_estimate");
        $view_data['can_request_estimate'] = false;

        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("estimates", $this->login_user->is_admin, $this->login_user->user_type);

        if ($this->login_user->user_type === "staff") {
            $this->access_only_allowed_members();

            $view_data["conversion_rate"] = $this->get_conversion_rate_with_currency_symbol();
            return $this->template->rander("estimates/index", $view_data);
        } else {
            //client view
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";

            if (get_setting("module_estimate_request") == "1") {
                $view_data['can_request_estimate'] = true;
            }

            return $this->template->rander("clients/estimates/client_portal", $view_data);
        }
    }

    private function show_own_estimates_only_user_id() {
        if ($this->login_user->user_type === "staff") {
            return get_array_value($this->login_user->permissions, "estimate") == "own" ? $this->login_user->id : false;
        }
    }

    private function can_access_this_estimate($estimate_id = 0) {
        $estimate_info = $this->Estimates_model->get_one($estimate_id);

        if ($estimate_info->id && get_array_value($this->login_user->permissions, "estimate") == "own" && $estimate_info->created_by !== $this->login_user->id) {
            app_redirect("forbidden");
        }
    }

    private function can_access_this_estimate_item($estimate_item_id = 0) {
        $options = array("id" => $estimate_item_id);
        $item_info = $this->Estimate_items_model->get_details($options)->getRow();

        if ($item_info->id && get_array_value($this->login_user->permissions, "estimate") == "own" && $item_info->created_by !== $this->login_user->id) {
            app_redirect("forbidden");
        }
    }

    //load the yearly view of estimate list
    function yearly() {
        return $this->template->view("estimates/yearly_estimates");
    }

    /* load new estimate modal */

    function modal_form() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $this->can_access_this_estimate($id);
        $client_id = $this->request->getPost('client_id');
        $model_info = $this->Estimates_model->get_one($id);

        //check if proposal_id/contract_id/order_id posted. if found, generate related information
        $proposal_id = $this->request->getPost('proposal_id');
        $contract_id = $this->request->getPost('contract_id');
        $order_id = $this->request->getPost('order_id');
        $view_data['contract_id'] = $contract_id;
        $view_data['proposal_id'] = $proposal_id;
        $view_data['order_id'] = $order_id;
        if ($proposal_id || $contract_id || $order_id) {
            $info = null;
            if ($proposal_id) {
                $info = $this->Proposals_model->get_one($proposal_id);
            } else if ($contract_id) {
                $info = $this->Contracts_model->get_one($contract_id);
            } else if ($order_id) {
                $info = $this->Orders_model->get_one($order_id);
            }

            if ($info) {
                $now = get_my_local_time("Y-m-d");
                $model_info->estimate_date = $now;
                $model_info->valid_until = $now;
                $model_info->client_id = $info->client_id;
                $model_info->tax_id = $info->tax_id;
                $model_info->tax_id2 = $info->tax_id2;
                $model_info->discount_amount = $info->discount_amount;
                $model_info->discount_amount_type = $info->discount_amount_type;
                $model_info->discount_type = $info->discount_type;
            }
        }

        $view_data['model_info'] = $model_info;

        $estimate_request_id = $this->request->getPost('estimate_request_id');
        $view_data['estimate_request_id'] = $estimate_request_id;

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['clients_dropdown'] = $this->get_clients_and_leads_dropdown();

        $view_data['client_id'] = $client_id;

        //clone estimate data
        $is_clone = $this->request->getPost('is_clone');
        $view_data['is_clone'] = $is_clone;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("estimates", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        $view_data['companies_dropdown'] = $this->_get_companies_dropdown();
        if (!$model_info->company_id) {
            $view_data['model_info']->company_id = get_default_company_id();
        }

        return $this->template->view('estimates/modal_form', $view_data);
    }

    /* add, edit or clone an estimate */

    function save() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "estimate_client_id" => "required|numeric",
            "estimate_date" => "required",
            "valid_until" => "required",
            "estimate_request_id" => "numeric"
        ));

        $client_id = $this->request->getPost('estimate_client_id');
        $id = $this->request->getPost('id');
        $this->can_access_this_estimate($id);

        $estimate_data = array(
            "client_id" => $client_id,
            "estimate_date" => $this->request->getPost('estimate_date'),
            "valid_until" => $this->request->getPost('valid_until'),
            "tax_id" => $this->request->getPost('tax_id') ? $this->request->getPost('tax_id') : 0,
            "tax_id2" => $this->request->getPost('tax_id2') ? $this->request->getPost('tax_id2') : 0,
            "company_id" => $this->request->getPost('company_id') ? $this->request->getPost('company_id') : get_default_company_id(),
            "note" => $this->request->getPost('estimate_note')
        );

        $is_clone = $this->request->getPost('is_clone');
        $estimate_request_id = $this->request->getPost('estimate_request_id');
        $contract_id = $this->request->getPost('contract_id');
        $proposal_id = $this->request->getPost('proposal_id');
        $order_id = $this->request->getPost('order_id');

        //estimate creation from estimate request
        //store the estimate request id for the first time only
        //don't copy estimate request id on cloning too
        if ($estimate_request_id && !$id && !$is_clone) {
            $estimate_data["estimate_request_id"] = $estimate_request_id;
        }

        $main_estimate_id = "";
        if (($is_clone && $id) || $order_id || $contract_id || $proposal_id) {
            $main_estimate_id = $id; //store main estimate id to get items later
            $id = ""; //on cloning estimate, save as new
            //save discount when cloning
            $estimate_data["discount_amount"] = $this->request->getPost('discount_amount') ? $this->request->getPost('discount_amount') : 0;
            $estimate_data["discount_amount_type"] = $this->request->getPost('discount_amount_type') ? $this->request->getPost('discount_amount_type') : "percentage";
            $estimate_data["discount_type"] = $this->request->getPost('discount_type') ? $this->request->getPost('discount_type') : "before_tax";
        }

        if (!$id) {
            $estimate_data["created_by"] = $this->login_user->id;
            $estimate_data["public_key"] = make_random_string();
        }

        $estimate_id = $this->Estimates_model->ci_save($estimate_data, $id);
        if ($estimate_id) {

            if ($is_clone && $main_estimate_id) {
                //add estimate items

                save_custom_fields("estimates", $estimate_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a estimate

                $estimate_items = $this->Estimate_items_model->get_all_where(array("estimate_id" => $main_estimate_id, "deleted" => 0))->getResult();

                foreach ($estimate_items as $estimate_item) {
                    //prepare new estimate item data
                    $estimate_item_data = (array) $estimate_item;
                    unset($estimate_item_data["id"]);
                    $estimate_item_data['estimate_id'] = $estimate_id;

                    $estimate_item = $this->Estimate_items_model->ci_save($estimate_item_data);
                }
            } else {
                save_custom_fields("estimates", $estimate_id, $this->login_user->is_admin, $this->login_user->user_type);
            }

            //submitted copy_items_from_proposal/submitted copy_items_from_contract/copy_items_from_order? copy all items from the associated one
            $copy_items_from_proposal = $this->request->getPost("copy_items_from_proposal");
            $copy_items_from_contract = $this->request->getPost("copy_items_from_contract");
            $copy_items_from_order = $this->request->getPost("copy_items_from_order");
            $this->_copy_related_items_to_estimate($copy_items_from_proposal, $copy_items_from_contract, $copy_items_from_order, $estimate_id);

            echo json_encode(array("success" => true, "data" => $this->_row_data($estimate_id), 'id' => $estimate_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    private function _copy_related_items_to_estimate($copy_items_from_proposal, $copy_items_from_contract, $copy_items_from_order, $estimate_id) {
        if (!($copy_items_from_proposal || $copy_items_from_contract || $copy_items_from_order)) {
            return false;
        }

        $copy_items = null;
        if ($copy_items_from_proposal) {
            $copy_items = $this->Proposal_items_model->get_details(array("proposal_id" => $copy_items_from_proposal))->getResult();
        } else if ($copy_items_from_contract) {
            $copy_items = $this->Contract_items_model->get_details(array("contract_id" => $copy_items_from_contract))->getResult();
        } else if ($copy_items_from_order) {
            $copy_items = $this->Order_items_model->get_details(array("order_id" => $copy_items_from_order))->getResult();
        }

        if (!$copy_items) {
            return false;
        }

        foreach ($copy_items as $data) {
            $estimate_item_data = array(
                "estimate_id" => $estimate_id,
                "title" => $data->title ? $data->title : "",
                "description" => $data->description ? $data->description : "",
                "quantity" => $data->quantity ? $data->quantity : 0,
                "unit_type" => $data->unit_type ? $data->unit_type : "",
                "rate" => $data->rate ? $data->rate : 0,
                "total" => $data->total ? $data->total : 0,
            );

            $this->Estimate_items_model->ci_save($estimate_item_data);
        }
    }

    //update estimate status
    function update_estimate_status($estimate_id, $status, $is_modal = false) {
        if (!($estimate_id && $status)) {
            show_404();
        }

        validate_numeric_value($estimate_id);
        $this->can_access_this_estimate($estimate_id);
        $estmate_info = $this->Estimates_model->get_one($estimate_id);
        $this->access_only_allowed_members_or_client_contact($estmate_info->client_id);

        if ($this->login_user->user_type == "client") {
            //updating by client
            //client can only update the status once and the value should be either accepted or declined
            if (!($estmate_info->status == "sent" && ($status == "accepted" || $status == "declined"))) {
                show_404();
            }

            $estimate_data = array("status" => $status);

            //estimate acceptation with signature
            if ($is_modal) {
                if (!get_setting("add_signature_option_on_accepting_estimate") || $status !== "accepted") {
                    show_404();
                }

                $this->validate_submitted_data(array(
                    "signature" => "required"
                ));

                $meta_data = array();
                $signature = $this->request->getPost("signature");
                $signature = explode(",", $signature);
                $signature = get_array_value($signature, 1);
                $signature = base64_decode($signature);
                $signature = serialize(move_temp_file("signature.jpg", get_setting("timeline_file_path"), "estimate", NULL, "", $signature));

                $meta_data["signature"] = $signature;
                $meta_data["signed_date"] = get_current_utc_time();

                $estimate_data["meta_data"] = serialize($meta_data);
                $estimate_data["accepted_by"] = $this->login_user->id;
            }

            $estimate_id = $this->Estimates_model->ci_save($estimate_data, $estimate_id);

            //create notification
            if ($status == "accepted") {
                log_notification("estimate_accepted", array("estimate_id" => $estimate_id));

                //estimate accepted, create a new project
                if (get_setting("create_new_projects_automatically_when_estimates_gets_accepted")) {
                    $this->_create_project_from_estimate($estimate_id);
                }

                if ($is_modal) {
                    echo json_encode(array("success" => true, "message" => app_lang("estimate_accepted")));
                }
            } else if ($status == "declined") {
                log_notification("estimate_rejected", array("estimate_id" => $estimate_id));
            }
        } else {
            //updating by team members
            if (!($status == "accepted" || $status == "declined")) {
                show_404();
            }

            $estimate_data = array("status" => $status);
            $estimate_id = $this->Estimates_model->ci_save($estimate_data, $estimate_id);

            //estimate accepted, create a new project
            if (get_setting("create_new_projects_automatically_when_estimates_gets_accepted") && $status == "accepted") {
                $this->_create_project_from_estimate($estimate_id);
            }
        }
    }

    /* create new project from accepted estimate */

    private function _create_project_from_estimate($estimate_id) {
        if ($estimate_id) {
            $this->can_access_this_estimate($estimate_id);
            $estimate_info = $this->Estimates_model->get_one($estimate_id);

            //don't create new project if there has already been created a new project with this estimate
            if (!$this->Projects_model->get_one_where(array("estimate_id" => $estimate_id))->id) {
                $data = array(
                    "title" => get_estimate_id($estimate_info->id),
                    "client_id" => $estimate_info->client_id,
                    "start_date" => $estimate_info->estimate_date,
                    "deadline" => $estimate_info->valid_until,
                    "estimate_id" => $estimate_id
                );
                $save_id = $this->Projects_model->ci_save($data);

                //save the project id
                $data = array("project_id" => $save_id);
                $this->Estimates_model->ci_save($data, $estimate_id);
            }
        }
    }

    /* delete or undo an estimate */

    function delete() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        $this->can_access_this_estimate($id);
        $estimate_info = $this->Estimates_model->get_one($id);

        if ($this->Estimates_model->delete($id)) {
            //delete signature file
            $signer_info = @unserialize($estimate_info->meta_data);
            if ($signer_info && is_array($signer_info) && get_array_value($signer_info, "signature")) {
                $signature_file = unserialize(get_array_value($signer_info, "signature"));
                delete_app_files(get_setting("timeline_file_path"), $signature_file);
            }

            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    /* list of estimates, prepared for datatable  */

    function list_data() {
        $this->access_only_allowed_members();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "status" => $this->request->getPost("status"),
            "start_date" => $this->request->getPost("start_date"),
            "end_date" => $this->request->getPost("end_date"),
            "show_own_estimates_only_user_id" => $this->show_own_estimates_only_user_id(),
            "custom_fields" => $custom_fields,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("estimates", $this->login_user->is_admin, $this->login_user->user_type)
        );

        $list_data = $this->Estimates_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode(array("data" => $result));
    }

    /* list of estimate of a specific client, prepared for datatable  */

    function estimate_list_data_of_client($client_id) {
        validate_numeric_value($client_id);
        $this->access_only_allowed_members_or_client_contact($client_id);

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "client_id" => $client_id,
            "status" => $this->request->getPost("status"),
            "show_own_estimates_only_user_id" => $this->show_own_estimates_only_user_id(),
            "custom_fields" => $custom_fields,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("estimates", $this->login_user->is_admin, $this->login_user->user_type)
        );

        if ($this->login_user->user_type == "client") {
            //don't show draft estimates to clients.
            $options["exclude_draft"] = true;
        }

        $list_data = $this->Estimates_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* return a row of estimate list table */

    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("estimates", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Estimates_model->get_details($options)->getRow();
        return $this->_make_row($data, $custom_fields);
    }

    /* prepare a row of estimate list table */

    private function _make_row($data, $custom_fields) {
        $estimate_url = "";
        if ($this->login_user->user_type == "staff") {
            $estimate_url = anchor(get_uri("estimates/view/" . $data->id), get_estimate_id($data->id));
        } else {
            //for client client
            $estimate_url = anchor(get_uri("estimates/preview/" . $data->id), get_estimate_id($data->id));
        }

        $client = anchor(get_uri("clients/view/" . $data->client_id), $data->company_name);
        if ($data->is_lead) {
            $client = anchor(get_uri("leads/view/" . $data->client_id), $data->company_name);
        }

        $row_data = array(
            $estimate_url,
            $client,
            $data->estimate_date,
            format_to_date($data->estimate_date, false),
            to_currency($data->estimate_value, $data->currency_symbol),
            $this->_get_estimate_status_label($data),
        );

        $comment_link = "";
        if (get_setting("enable_comments_on_estimates") && $data->status !== "draft") {
            $comment_link = modal_anchor(get_uri("estimates/comment_modal_form"), "<i data-feather='message-circle' class='icon-16'></i>", array("class" => "edit text-muted", "title" => app_lang("estimate") . " #" . $data->id . " " . app_lang("comments"), "data-post-estimate_id" => $data->id));
        }

        $row_data[] = $comment_link;

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id));
        }

        $row_data[] = anchor(get_uri("estimate/preview/" . $data->id . "/" . $data->public_key), "<i data-feather='external-link' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('estimate') . " " . app_lang("url"), "target" => "_blank"))
                . modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_estimate'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_estimate'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("estimates/delete"), "data-action" => "delete-confirmation"));

        return $row_data;
    }

    //prepare estimate status label 
    private function _get_estimate_status_label($estimate_info, $return_html = true) {
        return get_estimate_status_label($estimate_info, $return_html);
    }

    /* load estimate details view */

    function view($estimate_id = 0) {
        $this->access_only_allowed_members();
        $this->can_access_this_estimate($estimate_id);

        if ($estimate_id) {
            validate_numeric_value($estimate_id);

            $sort_as_decending = get_setting("show_most_recent_estimate_comments_at_the_top");
            $view_data = get_estimate_making_data($estimate_id);

            $comments_options = array(
                "estimate_id" => $estimate_id,
                "sort_as_decending" => $sort_as_decending
            );
            $view_data['comments'] = $this->Estimate_comments_model->get_details($comments_options)->getResult();
            $view_data["sort_as_decending"] = $sort_as_decending;

            if ($view_data) {
                $view_data['estimate_status_label'] = $this->_get_estimate_status_label($view_data["estimate_info"]);
                $view_data['estimate_status'] = $this->_get_estimate_status_label($view_data["estimate_info"], false);

                $access_info = $this->get_access_info("invoice");
                $view_data["show_invoice_option"] = (get_setting("module_invoice") && $access_info->access_type == "all") ? true : false;

                $view_data["can_create_projects"] = $this->can_create_projects();

                $view_data["estimate_id"] = clean_data($estimate_id);

                return $this->template->rander("estimates/view", $view_data);
            } else {
                show_404();
            }
        }
    }

    /* estimate total section */

    private function _get_estimate_total_view($estimate_id = 0) {
        $view_data["estimate_total_summary"] = $this->Estimates_model->get_estimate_total_summary($estimate_id);
        $view_data["estimate_id"] = $estimate_id;
        return $this->template->view('estimates/estimate_total_section', $view_data, true);
    }

    /* load discount modal */

    function discount_modal_form() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "estimate_id" => "required|numeric"
        ));

        $estimate_id = $this->request->getPost('estimate_id');
        $this->can_access_this_estimate($estimate_id);

        $view_data['model_info'] = $this->Estimates_model->get_one($estimate_id);

        return $this->template->view('estimates/discount_modal_form', $view_data);
    }

    /* save discount */

    function save_discount() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "estimate_id" => "required|numeric",
            "discount_type" => "required",
            "discount_amount" => "numeric",
            "discount_amount_type" => "required"
        ));

        $estimate_id = $this->request->getPost('estimate_id');
        $this->can_access_this_estimate($estimate_id);

        $data = array(
            "discount_type" => $this->request->getPost('discount_type'),
            "discount_amount" => $this->request->getPost('discount_amount'),
            "discount_amount_type" => $this->request->getPost('discount_amount_type')
        );

        $data = clean_data($data);

        $save_data = $this->Estimates_model->ci_save($data, $estimate_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "estimate_total_view" => $this->_get_estimate_total_view($estimate_id), 'message' => app_lang('record_saved'), "estimate_id" => $estimate_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* load item modal */

    function item_modal_form() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $estimate_id = $this->request->getPost('estimate_id');
        $this->can_access_this_estimate($estimate_id);

        $view_data['model_info'] = $this->Estimate_items_model->get_one($this->request->getPost('id'));
        if (!$estimate_id) {
            $estimate_id = $view_data['model_info']->estimate_id;
        }
        $view_data['estimate_id'] = $estimate_id;
        return $this->template->view('estimates/item_modal_form', $view_data);
    }

    /* add or edit an estimate item */

    function save_item() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "estimate_id" => "required|numeric"
        ));

        $estimate_id = $this->request->getPost('estimate_id');
        $this->can_access_this_estimate($estimate_id);

        $id = $this->request->getPost('id');
        $rate = unformat_currency($this->request->getPost('estimate_item_rate'));
        $quantity = unformat_currency($this->request->getPost('estimate_item_quantity'));
        $estimate_item_title = $this->request->getPost('estimate_item_title');
        $item_id = 0;

        if (!$id) {
            //on adding item for the first time, get the id to store
            $item_id = $this->request->getPost('item_id');
        }

        //check if the add_new_item flag is on, if so, add the item to libary. 
        $add_new_item_to_library = $this->request->getPost('add_new_item_to_library');
        if ($add_new_item_to_library) {
            $library_item_data = array(
                "title" => $estimate_item_title,
                "description" => $this->request->getPost('estimate_item_description'),
                "unit_type" => $this->request->getPost('estimate_unit_type'),
                "rate" => unformat_currency($this->request->getPost('estimate_item_rate'))
            );
            $item_id = $this->Items_model->ci_save($library_item_data);
        }

        $estimate_item_data = array(
            "estimate_id" => $estimate_id,
            "title" => $this->request->getPost('estimate_item_title'),
            "description" => $this->request->getPost('estimate_item_description'),
            "quantity" => $quantity,
            "unit_type" => $this->request->getPost('estimate_unit_type'),
            "rate" => unformat_currency($this->request->getPost('estimate_item_rate')),
            "total" => $rate * $quantity,
        );

        if ($item_id) {
            $estimate_item_data["item_id"] = $item_id;
        }

        $estimate_item_id = $this->Estimate_items_model->ci_save($estimate_item_data, $id);
        if ($estimate_item_id) {
            $options = array("id" => $estimate_item_id);
            $item_info = $this->Estimate_items_model->get_details($options)->getRow();
            echo json_encode(array("success" => true, "estimate_id" => $item_info->estimate_id, "data" => $this->_make_item_row($item_info), "estimate_total_view" => $this->_get_estimate_total_view($item_info->estimate_id), 'id' => $estimate_item_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete or undo an estimate item */

    function delete_item() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        $this->can_access_this_estimate_item($id);
        if ($this->request->getPost('undo')) {
            if ($this->Estimate_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Estimate_items_model->get_details($options)->getRow();
                echo json_encode(array("success" => true, "estimate_id" => $item_info->estimate_id, "data" => $this->_make_item_row($item_info), "estimate_total_view" => $this->_get_estimate_total_view($item_info->estimate_id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Estimate_items_model->delete($id)) {
                $item_info = $this->Estimate_items_model->get_one($id);
                echo json_encode(array("success" => true, "estimate_id" => $item_info->estimate_id, "estimate_total_view" => $this->_get_estimate_total_view($item_info->estimate_id), 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of estimate items, prepared for datatable  */

    function item_list_data($estimate_id = 0) {
        validate_numeric_value($estimate_id);
        $this->access_only_allowed_members();
        $this->can_access_this_estimate($estimate_id);

        $list_data = $this->Estimate_items_model->get_details(array("estimate_id" => $estimate_id))->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    /* prepare a row of estimate item list table */

    private function _make_item_row($data) {
        $item = "<div class='item-row strong mb5' data-id='$data->id'><div class='float-start move-icon'><i data-feather='menu' class='icon-16'></i></div> $data->title</div>";
        if ($data->description) {
            $item .= "<span class='text-wrap' style='margin-left:25px'>" . nl2br($data->description) . "</span>";
        }
        $type = $data->unit_type ? $data->unit_type : "";

        return array(
            $data->sort,
            $item,
            to_decimal_format($data->quantity) . " " . $type,
            to_currency($data->rate, $data->currency_symbol),
            to_currency($data->total, $data->currency_symbol),
            modal_anchor(get_uri("estimates/item_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_estimate'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("estimates/delete_item"), "data-action" => "delete"))
        );
    }

    /* prepare suggestion of estimate item */

    function get_estimate_item_suggestion() {
        $key = $this->request->getPost("q");
        $suggestion = array();

        $items = $this->Invoice_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->id, "text" => $item->title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . app_lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_estimate_item_info_suggestion() {
        $item = $this->Invoice_items_model->get_item_info_suggestion(array("item_id" => $this->request->getPost("item_id")));
        if ($item) {
            $item->rate = $item->rate ? to_decimal_format($item->rate) : "";
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //view html is accessable to client only.
    function preview($estimate_id = 0, $show_close_preview = false) {

        $view_data = array();

        if ($estimate_id) {
            validate_numeric_value($estimate_id);

            $this->can_access_this_estimate($estimate_id);

            $estimate_data = get_estimate_making_data($estimate_id);
            $this->_check_estimate_access_permission($estimate_data);

            $sort_as_decending = get_setting("show_most_recent_estimate_comments_at_the_top");

            $comments_options = array(
                "estimate_id" => $estimate_id,
                "sort_as_decending" => $sort_as_decending
            );
            $view_data['comments'] = $this->Estimate_comments_model->get_details($comments_options)->getResult();
            $view_data["sort_as_decending"] = $sort_as_decending;

            //get the label of the estimate
            $estimate_info = get_array_value($estimate_data, "estimate_info");
            $estimate_data['estimate_status_label'] = $this->_get_estimate_status_label($estimate_info);

            $view_data['estimate_preview'] = prepare_estimate_pdf($estimate_data, "html");

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['estimate_id'] = $estimate_id;

            return $this->template->rander("estimates/estimate_preview", $view_data);
        } else {
            show_404();
        }
    }

    function download_pdf($estimate_id = 0, $mode = "download") {
        if ($estimate_id) {
            validate_numeric_value($estimate_id);
            $this->can_access_this_estimate($estimate_id);
            $estimate_data = get_estimate_making_data($estimate_id);
            $this->_check_estimate_access_permission($estimate_data);

            if (@ob_get_length())
                @ob_clean();
            //so, we have a valid estimate data. Prepare the view.

            prepare_estimate_pdf($estimate_data, $mode);
        } else {
            show_404();
        }
    }

    private function _check_estimate_access_permission($estimate_data) {
        //check for valid estimate
        if (!$estimate_data) {
            show_404();
        }

        //check for security
        $estimate_info = get_array_value($estimate_data, "estimate_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->client_id != $estimate_info->client_id) {
                app_redirect("forbidden");
            }
        } else {
            $this->access_only_allowed_members();
        }
    }

    function get_estimate_status_bar($estimate_id = 0) {
        validate_numeric_value($estimate_id);
        $this->access_only_allowed_members();
        $this->can_access_this_estimate($estimate_id);

        $view_data["estimate_info"] = $this->Estimates_model->get_details(array("id" => $estimate_id))->getRow();
        $view_data['estimate_status_label'] = $this->_get_estimate_status_label($view_data["estimate_info"]);
        return $this->template->view('estimates/estimate_status_bar', $view_data);
    }

    function send_estimate_modal_form($estimate_id) {
        $this->access_only_allowed_members();
        $this->can_access_this_estimate($estimate_id);

        if ($estimate_id) {
            validate_numeric_value($estimate_id);
            $options = array("id" => $estimate_id);
            $estimate_info = $this->Estimates_model->get_details($options)->getRow();
            $view_data['estimate_info'] = $estimate_info;

            $is_lead = $this->request->getPost('is_lead');
            if ($is_lead) {
                $contacts_options = array("user_type" => "lead", "client_id" => $estimate_info->client_id);
            } else {
                $contacts_options = array("user_type" => "client", "client_id" => $estimate_info->client_id);
            }

            $contacts = $this->Users_model->get_details($contacts_options)->getResult();
            $contact_first_name = "";
            $contact_last_name = "";
            $contacts_dropdown = array();
            foreach ($contacts as $contact) {
                if ($contact->is_primary_contact) {
                    $contact_first_name = $contact->first_name;
                    $contact_last_name = $contact->last_name;
                    $contacts_dropdown[$contact->id] = $contact->first_name . " " . $contact->last_name . " (" . app_lang("primary_contact") . ")";
                }
            }

            foreach ($contacts as $contact) {
                if (!$contact->is_primary_contact) {
                    $contacts_dropdown[$contact->id] = $contact->first_name . " " . $contact->last_name;
                }
            }

            $view_data['contacts_dropdown'] = $contacts_dropdown;

            $email_template = $this->Email_templates_model->get_final_template("estimate_sent");

            $parser_data["ESTIMATE_ID"] = $estimate_info->id;
            $parser_data["PUBLIC_ESTIMATE_URL"] = get_uri("estimate/preview/" . $estimate_info->id . "/" . $estimate_info->public_key);
            $parser_data["CONTACT_FIRST_NAME"] = $contact_first_name;
            $parser_data["CONTACT_LAST_NAME"] = $contact_last_name;
            $parser_data["PROJECT_TITLE"] = $estimate_info->project_title;
            $parser_data["ESTIMATE_URL"] = get_uri("estimates/preview/" . $estimate_info->id);
            $parser_data['SIGNATURE'] = $email_template->signature;
            $parser_data["LOGO_URL"] = get_logo_url();

            $message = $this->parser->setData($parser_data)->renderString($email_template->message);
            $subject = $this->parser->setData($parser_data)->renderString($email_template->subject);
            $view_data['message'] = htmlspecialchars_decode($message);
            $view_data['subject'] = htmlspecialchars_decode($subject);

            return $this->template->view('estimates/send_estimate_modal_form', $view_data);
        } else {
            show_404();
        }
    }

    function send_estimate() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $estimate_id = $this->request->getPost('id');
        $this->can_access_this_estimate($estimate_id);

        $contact_id = $this->request->getPost('contact_id');
        $cc = $this->request->getPost('estimate_cc');

        $custom_bcc = $this->request->getPost('estimate_bcc');
        $subject = $this->request->getPost('subject');
        $message = decode_ajax_post_data($this->request->getPost('message'));

        $contact = $this->Users_model->get_one($contact_id);

        $estimate_data = get_estimate_making_data($estimate_id);
        $attachement_url = prepare_estimate_pdf($estimate_data, "send_email");

        $default_bcc = get_setting('send_estimate_bcc_to');
        $bcc_emails = "";

        if ($default_bcc && $custom_bcc) {
            $bcc_emails = $default_bcc . "," . $custom_bcc;
        } else if ($default_bcc) {
            $bcc_emails = $default_bcc;
        } else if ($custom_bcc) {
            $bcc_emails = $custom_bcc;
        }

        if (send_app_mail($contact->email, $subject, $message, array("attachments" => array(array("file_path" => $attachement_url)), "cc" => $cc, "bcc" => $bcc_emails))) {
            // change email status
            $status_data = array("status" => "sent", "last_email_sent_date" => get_my_local_time());
            if ($this->Estimates_model->ci_save($status_data, $estimate_id)) {
                echo json_encode(array('success' => true, 'message' => app_lang("estimate_sent_message"), "estimate_id" => $estimate_id));
            }
            // delete the temp estimate
            if (file_exists($attachement_url)) {
                unlink($attachement_url);
            }
        } else {
            echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
        }
    }

    //update the sort value for estimate item
    function update_item_sort_values($id = 0) {

        $sort_values = $this->request->getPost("sort_values");
        if ($sort_values) {

            //extract the values from the comma separated string
            $sort_array = explode(",", $sort_values);

            //update the value in db
            foreach ($sort_array as $value) {
                $sort_item = explode("-", $value); //extract id and sort value

                $id = get_array_value($sort_item, 0);
                $sort = get_array_value($sort_item, 1);

                $data = array("sort" => $sort);
                $this->Estimate_items_model->ci_save($data, $id);
            }
        }
    }

    /* upload a post file */

    function upload_file() {
        upload_file_to_temp();
    }

    /* check valid file for project */

    function validate_estimate_file() {
        return validate_post_file($this->request->getPost("file_name"));
    }

    /* save estimate comments */

    function save_comment() {
        $estimate_id = $this->request->getPost('estimate_id');
        $now = get_current_utc_time();

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "estimate");

        $this->validate_submitted_data(array(
            "description" => "required",
            "estimate_id" => "required|numeric"
        ));

        $comment_data = array(
            "description" => $this->request->getPost('description'),
            "estimate_id" => $estimate_id,
            "created_by" => $this->login_user->id,
            "created_at" => $now,
            "files" => $files_data
        );

        $comment_data = clean_data($comment_data);
        $comment_data["files"] = $files_data; //don't clean serilized data

        $comment_id = $this->Estimate_comments_model->ci_save($comment_data);
        if ($comment_id) {
            $comments_options = array("id" => $comment_id);
            $view_data['comment'] = $this->Estimate_comments_model->get_details($comments_options)->getRow();
            $comment_view = $this->template->view("estimates/comment_row", $view_data, true);

            echo json_encode(array("success" => true, "data" => $comment_view, 'message' => app_lang('comment_submited')));
            log_notification("estimate_commented", array("estimate_id" => $estimate_id, "estimate_comment_id" => $comment_id));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete estimate comments */

    function delete_comment($id = 0) {

        if (!$id) {
            exit();
        }

        $comment_info = $this->Estimate_comments_model->get_one($id);

        //only admin and creator can delete the comment
        if (!($this->login_user->is_admin || $comment_info->created_by == $this->login_user->id)) {
            redirect("forbidden");
        }


        //delete the comment and files
        if ($this->Estimate_comments_model->delete($id) && $comment_info->files) {

            //delete the files
            $file_path = get_setting("timeline_file_path");
            $files = unserialize($comment_info->files);

            foreach ($files as $file) {
                $source_path = $file_path . get_array_value($file, "file_name");
                delete_file_from_directory($source_path);
            }
        }
    }

    /* download files by zip */

    function download_comment_files($id) {

        $files = $this->Estimate_comments_model->get_one($id)->files;
        return $this->download_app_files(get_setting("timeline_file_path"), $files);
    }

    function comment_modal_form() {
        $this->validate_submitted_data(array(
            "estimate_id" => "numeric|required"
        ));

        if (get_setting("enable_comments_on_estimates") !== "1") {
            app_redirect("forbidden");
        }

        $estimate_id = $this->request->getPost('estimate_id');
        $view_data['estimate_id'] = $estimate_id;

        $sort_as_decending = get_setting("show_most_recent_estimate_comments_at_the_top");

        $view_data = get_estimate_making_data($estimate_id);

        $comments_options = array(
            "estimate_id" => $estimate_id,
            "sort_as_decending" => $sort_as_decending
        );
        $view_data['comments'] = $this->Estimate_comments_model->get_details($comments_options)->getResult();
        $view_data["sort_as_decending"] = $sort_as_decending;

        return $this->template->view('estimates/comment_form', $view_data);
    }

    function load_statistics_of_selected_currency($currency = "") {
        if ($currency) {
            $statistics = estimate_sent_statistics_widget(array("currency" => $currency));

            if ($statistics) {
                echo json_encode(array("success" => true, "statistics" => $statistics));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            }
        }
    }

    //print estimate
    function print_estimate($estimate_id = 0) {
        if ($estimate_id) {
            validate_numeric_value($estimate_id);
            $view_data = get_estimate_making_data($estimate_id);

            $this->_check_estimate_access_permission($view_data);

            $view_data['estimate_preview'] = prepare_estimate_pdf($view_data, "html");

            echo json_encode(array("success" => true, "print_view" => $this->template->view("estimates/print_estimate", $view_data)));
        } else {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
        }
    }

}

/* End of file estimates.php */
    /* Location: ./app/controllers/estimates.php */    