<?php

namespace App\Controllers;

class Contracts extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->init_permission_checker("contract");
    }

    /* load contract list view */

    function index() {
        $this->check_module_availability("module_contract");
        $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("contracts", $this->login_user->is_admin, $this->login_user->user_type);
        $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("contracts", $this->login_user->is_admin, $this->login_user->user_type);

        if ($this->login_user->user_type === "staff") {
            $this->access_only_allowed_members();

            return $this->template->rander("contracts/index", $view_data);
        } else {
            //client view
            $view_data["client_info"] = $this->Clients_model->get_one($this->login_user->client_id);
            $view_data['client_id'] = $this->login_user->client_id;
            $view_data['page_type'] = "full";

            return $this->template->rander("clients/contracts/client_portal", $view_data);
        }
    }

    //load the yearly view of contract list
    function yearly() {
        return $this->template->view("contracts/yearly_contracts");
    }

    /* load new contract modal */

    function modal_form() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "client_id" => "numeric",
            "project_id" => "numeric"
        ));

        $client_id = $this->request->getPost('client_id');
        $project_id = $this->request->getPost('project_id');
        $model_info = $this->Contracts_model->get_one($this->request->getPost('id'));

        //here has a project id. now set the client from the project
        if ($project_id) {
            $client_id = $this->Projects_model->get_one($project_id)->client_id;
            $model_info->client_id = $client_id;
        }

        $view_data['model_info'] = $model_info;

        $project_client_id = $client_id;
        if ($model_info->client_id) {
            $project_client_id = $model_info->client_id;
        }

        //make the drodown lists
        $view_data['taxes_dropdown'] = array("" => "-") + $this->Taxes_model->get_dropdown_list(array("title"));
        $view_data['clients_dropdown'] = $this->get_contract_clients_and_leads_dropdown();

        //don't show clients dropdown for lead's contract editing
        $client_info = $this->Clients_model->get_one($view_data['model_info']->client_id);
        if ($client_info->is_lead) {
            $client_id = $client_info->id;
        }

        $projects = $this->Projects_model->get_dropdown_list(array("title"), "id", array("client_id" => $project_client_id, "project_type" => "client_project"));
        $suggestion = array(array("id" => "", "text" => "-"));
        foreach ($projects as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }
        $view_data['projects_suggestion'] = $suggestion;

        $view_data['client_id'] = $client_id;
        $view_data['project_id'] = $project_id;

        //clone contract data
        $is_clone = $this->request->getPost('is_clone');
        $view_data['is_clone'] = $is_clone;

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("contracts", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        $view_data['companies_dropdown'] = $this->_get_companies_dropdown();
        if (!$model_info->company_id) {
            $view_data['model_info']->company_id = get_default_company_id();
        }

        return $this->template->view('contracts/modal_form', $view_data);
    }

    private function get_contract_clients_and_leads_dropdown() {
        $clients_dropdown = array("" => "-");
        $clients = $this->Clients_model->get_all_where(array("deleted" => 0), 0, 0, "is_lead")->getResult();

        foreach ($clients as $client) {
            $company_name = $client->is_lead ? (app_lang("lead") . ": " . $client->company_name) : (app_lang("client") . ": " . $client->company_name);
            $clients_dropdown[$client->id] = $company_name;
        }

        return $clients_dropdown;
    }

    function save_view() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost("id");

        $contract_data = array(
            "content" => decode_ajax_post_data($this->request->getPost('view'))
        );

        $this->Contracts_model->ci_save($contract_data, $id);

        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }

    /* add, edit or clone an contract */

    function save() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "contract_project_id" => "numeric",
            "title" => "required",
            "contract_client_id" => "required|numeric",
            "contract_date" => "required",
            "valid_until" => "required"
        ));

        $client_id = $this->request->getPost('contract_client_id');
        $id = $this->request->getPost('id');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "contract");
        $new_files = unserialize($files_data);

        $contract_data = array(
            "client_id" => $client_id,
            "title" => $this->request->getPost('title'),
            "project_id" => $this->request->getPost('contract_project_id') ? $this->request->getPost('contract_project_id') : 0,
            "contract_date" => $this->request->getPost('contract_date'),
            "valid_until" => $this->request->getPost('valid_until'),
            "tax_id" => $this->request->getPost('tax_id') ? $this->request->getPost('tax_id') : 0,
            "tax_id2" => $this->request->getPost('tax_id2') ? $this->request->getPost('tax_id2') : 0,
            "company_id" => $this->request->getPost('company_id') ? $this->request->getPost('company_id') : get_default_company_id(),
            "note" => $this->request->getPost('contract_note')
        );

        if ($id) {
            $contract_info = $this->Contracts_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $contract_info->files, $new_files);
        }

        $contract_data["files"] = serialize($new_files);

        if (!$id) {
            $contract_data["public_key"] = make_random_string();

            //add default template
            if (get_setting("default_contract_template")) {
                $Contract_templates_model = model("App\Models\Contract_templates_model");
                $contract_data["content"] = $Contract_templates_model->get_one(get_setting("default_contract_template"))->template;
            }
        }

        $is_clone = $this->request->getPost('is_clone');

        $main_contract_id = "";
        if ($is_clone && $id) {
            $main_contract_id = $id; //store main contract id to get items later
            $id = ""; //on cloning contract, save as new
            //save discount when cloning
            $main_contract_info = $this->Contracts_model->get_one($main_contract_id);
            $contract_data["discount_amount"] = $main_contract_info->discount_amount;
            $contract_data["discount_amount_type"] = $main_contract_info->discount_amount_type;
            $contract_data["discount_type"] = $main_contract_info->discount_type;
            $contract_data["content"] = $main_contract_info->content;
            $contract_data["public_key"] = make_random_string();
        }

        $contract_id = $this->Contracts_model->ci_save($contract_data, $id);
        if ($contract_id) {

            if ($is_clone && $main_contract_id) {
                //add contract items

                save_custom_fields("contracts", $contract_id, 1, "staff"); //we have to keep this regarding as an admin user because non-admin user also can acquire the access to clone a contract

                $contract_items = $this->Contract_items_model->get_all_where(array("contract_id" => $main_contract_id, "deleted" => 0))->getResult();

                foreach ($contract_items as $contract_item) {
                    //prepare new contract item data
                    $contract_item_data = (array) $contract_item;
                    unset($contract_item_data["id"]);
                    $contract_item_data['contract_id'] = $contract_id;

                    $contract_item = $this->Contract_items_model->ci_save($contract_item_data);
                }
            } else {
                save_custom_fields("contracts", $contract_id, $this->login_user->is_admin, $this->login_user->user_type);
            }

            echo json_encode(array("success" => true, "data" => $this->_row_data($contract_id), 'id' => $contract_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //update contract status
    function update_contract_status($contract_id, $status) {
        if ($contract_id && $status) {
            $contract_info = $this->Contracts_model->get_one($contract_id);
            $this->access_only_allowed_members_or_client_contact($contract_info->client_id);

            if ($this->login_user->user_type == "client") {
                //updating by client
                //client can only update the status once and the value should be either accepted or declined
                if ($contract_info->status == "sent" && ($status == "accepted" || $status == "declined")) {

                    $contract_data = array("status" => $status);
                    if ($status == "accepted") {
                        $contract_data["accepted_by"] = $this->login_user->id;
                    }

                    $contract_id = $this->Contracts_model->ci_save($contract_data, $contract_id);

                    //create notification
                    if ($status == "accepted") {
                        log_notification("contract_accepted", array("contract_id" => $contract_id));
                    } else if ($status == "declined") {
                        log_notification("contract_rejected", array("contract_id" => $contract_id));
                    }
                }
            } else {
                //updating by team members
                if ($status == "accepted" || $status == "declined" || $status == "sent") {
                    $contract_data = array("status" => $status);
                    $contract_id = $this->Contracts_model->ci_save($contract_data, $contract_id);
                }
            }
        }
    }

    /* delete or undo an contract */

    function delete() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        $contract_info = $this->Contracts_model->get_one($id);

        if ($this->Contracts_model->delete($id)) {
            //delete signature file
            $signer_info = @unserialize($contract_info->meta_data);
            if ($signer_info && is_array($signer_info)) {
                if (get_array_value($signer_info, "signature")) {
                    $signature_file = unserialize(get_array_value($signer_info, "signature"));
                    delete_app_files(get_setting("timeline_file_path"), $signature_file);
                }
                if (get_array_value($signer_info, "staff_signature")) {
                    $signature_file = unserialize(get_array_value($signer_info, "staff_signature"));
                    delete_app_files(get_setting("timeline_file_path"), $signature_file);
                }
            }

            //delete the files
            $file_path = get_setting("timeline_file_path");
            if ($contract_info->files) {
                $files = unserialize($contract_info->files);

                foreach ($files as $file) {
                    delete_app_files($file_path, array($file));
                }
            }

            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    /* list of contracts, prepared for datatable  */

    function list_data() {
        $this->access_only_allowed_members();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("contracts", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "status" => $this->request->getPost("status"),
            "start_date" => $this->request->getPost("start_date"),
            "end_date" => $this->request->getPost("end_date"),
            "custom_fields" => $custom_fields,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("contracts", $this->login_user->is_admin, $this->login_user->user_type)
        );

        $list_data = $this->Contracts_model->get_details($options)->getResult();
        $getResult = array();
        foreach ($list_data as $data) {
            $getResult[] = $this->_make_row($data, $custom_fields);
        }

        echo json_encode(array("data" => $getResult));
    }

    /* list of contract of a specific client, prepared for datatable  */

    function contract_list_data_of_client($client_id) {
        $this->access_only_allowed_members_or_client_contact($client_id);

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("contracts", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("client_id" => $client_id, "status" => $this->request->getPost("status"), "custom_fields" => $custom_fields, "custom_field_filter" => $this->prepare_custom_field_filter_values("contracts", $this->login_user->is_admin, $this->login_user->user_type));

        if ($this->login_user->user_type == "client") {
            //don't show draft contracts to clients.
            $options["exclude_draft"] = true;
        }

        $list_data = $this->Contracts_model->get_details($options)->getResult();
        $getResult = array();
        foreach ($list_data as $data) {
            $getResult[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $getResult));
    }

    /* return a row of contract list table */

    private function _row_data($id) {
        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("contracts", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array("id" => $id, "custom_fields" => $custom_fields);
        $data = $this->Contracts_model->get_details($options)->getRow();
        return $this->_make_row($data, $custom_fields);
    }

    /* prepare a row of contract list table */

    private function _make_row($data, $custom_fields) {
        $contract_id = "";
        if ($this->login_user->user_type == "staff") {
            $contract_id = anchor(get_uri("contracts/view/" . $data->id), get_contract_id($data->id));
        } else {
            //for client
            $contract_id = anchor(get_uri("contracts/preview/" . $data->id), get_contract_id($data->id));
        }

        $contract_url = "";
        if ($this->login_user->user_type == "staff") {
            $contract_url = anchor(get_uri("contracts/view/" . $data->id), $data->title);
        } else {
            //for client
            $contract_url = anchor(get_uri("contracts/preview/" . $data->id), $data->title);
        }

        $client = anchor(get_uri("clients/view/" . $data->client_id), $data->company_name);
        if ($data->is_lead) {
            $client = anchor(get_uri("leads/view/" . $data->client_id), $data->company_name);
        }

        $row_data = array(
            $contract_id,
            $contract_url,
            $client,
            $data->project_title ? anchor(get_uri("projects/view/" . $data->project_id), $data->project_title) : "-",
            $data->contract_date,
            format_to_date($data->contract_date, false),
            $data->valid_until,
            format_to_date($data->valid_until, false),
            to_currency($data->contract_value, $data->currency_symbol),
            $this->_get_contract_status_label($data),
        );

        foreach ($custom_fields as $field) {
            $cf_id = "cfv_" . $field->id;
            $row_data[] = $this->template->view("custom_fields/output_" . $field->field_type, array("value" => $data->$cf_id));
        }

        $row_data[] = anchor(get_uri("contract/preview/" . $data->id . "/" . $data->public_key), "<i data-feather='external-link' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('contract') . " " . app_lang("url"), "target" => "_blank"))
                . modal_anchor(get_uri("contracts/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_contract'), "data-post-id" => $data->id))
                . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_contract'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("contracts/delete"), "data-action" => "delete-confirmation"));

        return $row_data;
    }

    //prepare contract status label 
    private function _get_contract_status_label($contract_info, $return_html = true) {
        $contract_status_class = "bg-secondary";

        //don't show sent status to client, change the status to 'new' from 'sent'

        if ($this->login_user->user_type == "client") {
            if ($contract_info->status == "sent") {
                $contract_info->status = "new";
            } else if ($contract_info->status == "declined") {
                $contract_info->status = "rejected";
            }
        }

        if ($contract_info->status == "draft") {
            $contract_status_class = "bg-secondary";
        } else if ($contract_info->status == "declined" || $contract_info->status == "rejected") {
            $contract_status_class = "bg-danger";
        } else if ($contract_info->status == "accepted") {
            $contract_status_class = "bg-success";
        } else if ($contract_info->status == "sent") {
            $contract_status_class = "bg-primary";
        } else if ($contract_info->status == "new") {
            $contract_status_class = "bg-warning";
        }

        $contract_status = "<span class='mt0 badge $contract_status_class large'>" . app_lang($contract_info->status) . "</span>";
        if ($return_html) {
            return $contract_status;
        } else {
            return $contract_info->status;
        }
    }

    /* load contract details view */

    function view($contract_id = 0) {
        $this->access_only_allowed_members();

        if ($contract_id) {

            $view_data = get_contract_making_data($contract_id);

            if ($view_data) {
                $view_data['contract_status_label'] = $this->_get_contract_status_label($view_data["contract_info"]);
                $view_data['contract_status'] = $this->_get_contract_status_label($view_data["contract_info"], false);

                $access_info = $this->get_access_info("invoice");
                $view_data["show_invoice_option"] = (get_setting("module_invoice") && $access_info->access_type == "all") ? true : false;

                $access_info = $this->get_access_info("estimate");
                $view_data["show_estimate_option"] = (get_setting("module_estimate") && $access_info->access_type == "all") ? true : false;

                $view_data["contract_id"] = $contract_id;

                return $this->template->rander("contracts/view", $view_data);
            } else {
                show_404();
            }
        }
    }

    /* contract total section */

    private function _get_contract_total_view($contract_id = 0) {
        $view_data["contract_total_summary"] = $this->Contracts_model->get_contract_total_summary($contract_id);
        $view_data["contract_id"] = $contract_id;
        return $this->template->view('contracts/contract_total_section', $view_data);
    }

    /* load discount modal */

    function discount_modal_form() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "contract_id" => "required|numeric"
        ));

        $contract_id = $this->request->getPost('contract_id');

        $view_data['model_info'] = $this->Contracts_model->get_one($contract_id);

        return $this->template->view('contracts/discount_modal_form', $view_data);
    }

    /* save discount */

    function save_discount() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "contract_id" => "required|numeric",
            "discount_type" => "required",
            "discount_amount" => "numeric",
            "discount_amount_type" => "required"
        ));

        $contract_id = $this->request->getPost('contract_id');

        $data = array(
            "discount_type" => $this->request->getPost('discount_type'),
            "discount_amount" => $this->request->getPost('discount_amount'),
            "discount_amount_type" => $this->request->getPost('discount_amount_type')
        );

        $data = clean_data($data);

        $save_data = $this->Contracts_model->ci_save($data, $contract_id);
        if ($save_data) {
            echo json_encode(array("success" => true, "contract_total_view" => $this->_get_contract_total_view($contract_id), 'message' => app_lang('record_saved'), "contract_id" => $contract_id));
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

        $contract_id = $this->request->getPost('contract_id');

        $view_data['model_info'] = $this->Contract_items_model->get_one($this->request->getPost('id'));
        if (!$contract_id) {
            $contract_id = $view_data['model_info']->contract_id;
        }
        $view_data['contract_id'] = $contract_id;
        return $this->template->view('contracts/item_modal_form', $view_data);
    }

    /* add or edit an contract item */

    function save_item() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "contract_id" => "required|numeric"
        ));

        $contract_id = $this->request->getPost('contract_id');

        $id = $this->request->getPost('id');
        $rate = unformat_currency($this->request->getPost('contract_item_rate'));
        $quantity = unformat_currency($this->request->getPost('contract_item_quantity'));
        $contract_item_title = $this->request->getPost('contract_item_title');
        $item_id = 0;

        if (!$id) {
            //on adding item for the first time, get the id to store
            $item_id = $this->request->getPost('item_id');
        }

        //check if the add_new_item flag is on, if so, add the item to libary. 
        $add_new_item_to_library = $this->request->getPost('add_new_item_to_library');
        if ($add_new_item_to_library) {
            $library_item_data = array(
                "title" => $contract_item_title,
                "description" => $this->request->getPost('contract_item_description'),
                "unit_type" => $this->request->getPost('contract_unit_type'),
                "rate" => unformat_currency($this->request->getPost('contract_item_rate'))
            );
            $item_id = $this->Items_model->ci_save($library_item_data);
        }

        $contract_item_data = array(
            "contract_id" => $contract_id,
            "title" => $contract_item_title,
            "description" => $this->request->getPost('contract_item_description'),
            "quantity" => $quantity,
            "unit_type" => $this->request->getPost('contract_unit_type'),
            "rate" => unformat_currency($this->request->getPost('contract_item_rate')),
            "total" => $rate * $quantity,
        );

        if ($item_id) {
            $contract_item_data["item_id"] = $item_id;
        }

        $contract_item_id = $this->Contract_items_model->ci_save($contract_item_data, $id);
        if ($contract_item_id) {
            $options = array("id" => $contract_item_id);
            $item_info = $this->Contract_items_model->get_details($options)->getRow();
            echo json_encode(array("success" => true, "contract_id" => $item_info->contract_id, "data" => $this->_make_item_row($item_info), "contract_total_view" => $this->_get_contract_total_view($item_info->contract_id), 'id' => $contract_item_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    /* delete or undo an contract item */

    function delete_item() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Contract_items_model->delete($id, true)) {
                $options = array("id" => $id);
                $item_info = $this->Contract_items_model->get_details($options)->getRow();
                echo json_encode(array("success" => true, "contract_id" => $item_info->contract_id, "data" => $this->_make_item_row($item_info), "contract_total_view" => $this->_get_contract_total_view($item_info->contract_id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Contract_items_model->delete($id)) {
                $item_info = $this->Contract_items_model->get_one($id);
                echo json_encode(array("success" => true, "contract_id" => $item_info->contract_id, "contract_total_view" => $this->_get_contract_total_view($item_info->contract_id), 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    /* list of contract items, prepared for datatable  */

    function item_list_data($contract_id = 0) {
        $this->access_only_allowed_members();

        $list_data = $this->Contract_items_model->get_details(array("contract_id" => $contract_id))->getResult();
        $getResult = array();
        foreach ($list_data as $data) {
            $getResult[] = $this->_make_item_row($data);
        }
        echo json_encode(array("data" => $getResult));
    }

    /* prepare a row of contract item list table */

    private function _make_item_row($data) {
        $item = "<div class='item-row strong mb5' data-id='$data->id'><div class='float-start move-icon'><i data-feather='menu' class='icon-16'></i></div> $data->title</div>";
        if ($data->description) {
            $item .= "<span style='margin-left:25px'>" . nl2br($data->description) . "</span>";
        }
        $type = $data->unit_type ? $data->unit_type : "";

        return array(
            $data->sort,
            $item,
            to_decimal_format($data->quantity) . " " . $type,
            to_currency($data->rate, $data->currency_symbol),
            to_currency($data->total, $data->currency_symbol),
            modal_anchor(get_uri("contracts/item_modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_contract'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("contracts/delete_item"), "data-action" => "delete"))
        );
    }

    /* prepare suggestion of contract item */

    function get_contract_item_suggestion() {
        $key = $this->request->getPost("q");
        $suggestion = array();

        $items = $this->Invoice_items_model->get_item_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->id, "text" => $item->title);
        }

        $suggestion[] = array("id" => "+", "text" => "+ " . app_lang("create_new_item"));

        echo json_encode($suggestion);
    }

    function get_contract_item_info_suggestion() {
        $item = $this->Invoice_items_model->get_item_info_suggestion(array("item_id" => $this->request->getPost("item_id")));
        if ($item) {
            $item->rate = $item->rate ? to_decimal_format($item->rate) : "";
            echo json_encode(array("success" => true, "item_info" => $item));
        } else {
            echo json_encode(array("success" => false));
        }
    }

    //view html is accessable to client only.
    function preview($contract_id = 0, $show_close_preview = false, $is_editor_preview = false) {

        $view_data = array();

        if ($contract_id) {

            $contract_data = get_contract_making_data($contract_id);
            $this->_check_contract_access_permission($contract_data);

            //get the label of the contract
            $contract_info = get_array_value($contract_data, "contract_info");
            $contract_data['contract_status_label'] = $this->_get_contract_status_label($contract_info);

            $view_data['contract_preview'] = prepare_contract_view($contract_data);

            //show a back button
            $view_data['show_close_preview'] = $show_close_preview && $this->login_user->user_type === "staff" ? true : false;

            $view_data['contract_id'] = $contract_id;

            if ($is_editor_preview) {
                $view_data["is_editor_preview"] = $is_editor_preview;
                return $this->template->view("contracts/contract_preview", $view_data);
            } else {
                return $this->template->rander("contracts/contract_preview", $view_data);
            }
        } else {
            show_404();
        }
    }

    private function _check_contract_access_permission($contract_data) {
        //check for valid contract
        if (!$contract_data) {
            show_404();
        }

        //check for security
        $contract_info = get_array_value($contract_data, "contract_info");
        if ($this->login_user->user_type == "client") {
            if ($this->login_user->client_id != $contract_info->client_id || $contract_info->status === "draft") {
                app_redirect("forbidden");
            }
        } else {
            $this->access_only_allowed_members();
        }
    }

    function get_contract_status_bar($contract_id = 0) {
        $this->access_only_allowed_members();

        $view_data["contract_info"] = $this->Contracts_model->get_details(array("id" => $contract_id))->getRow();
        $view_data['contract_status_label'] = $this->_get_contract_status_label($view_data["contract_info"]);
        return $this->template->view('contracts/contract_status_bar', $view_data);
    }

    function send_contract_modal_form($contract_id) {
        $this->access_only_allowed_members();

        if ($contract_id) {
            $options = array("id" => $contract_id);
            $contract_info = $this->Contracts_model->get_details($options)->getRow();
            $view_data['contract_info'] = $contract_info;

            $is_lead = $this->request->getPost('is_lead');
            if ($is_lead) {
                $contacts_options = array("user_type" => "lead", "client_id" => $contract_info->client_id);
            } else {
                $contacts_options = array("user_type" => "client", "client_id" => $contract_info->client_id);
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

            $email_template = $this->Email_templates_model->get_final_template("contract_sent");

            $parser_data["CONTRACT_ID"] = $contract_info->id;
            $parser_data["PROJECT_TITLE"] = $contract_info->project_title;
            $parser_data["CONTACT_FIRST_NAME"] = $contact_first_name;
            $parser_data["CONTACT_LAST_NAME"] = $contact_last_name;
            $parser_data["CONTRACT_URL"] = get_uri("contracts/preview/" . $contract_info->id);
            $parser_data["PUBLIC_CONTRACT_URL"] = get_uri("contract/preview/" . $contract_info->id . "/" . $contract_info->public_key);
            $parser_data['SIGNATURE'] = $email_template->signature;
            $parser_data["LOGO_URL"] = get_logo_url();

            $message = $this->parser->setData($parser_data)->renderString($email_template->message);
            $subject = $this->parser->setData($parser_data)->renderString($email_template->subject);
            $view_data['message'] = htmlspecialchars_decode($message);
            $view_data['subject'] = htmlspecialchars_decode($subject);

            return $this->template->view('contracts/send_contract_modal_form', $view_data);
        } else {
            show_404();
        }
    }

    function send_contract() {
        $this->access_only_allowed_members();

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $contract_id = $this->request->getPost('id');

        $contact_id = $this->request->getPost('contact_id');
        $cc = $this->request->getPost('contract_cc');

        $custom_bcc = $this->request->getPost('contract_bcc');
        $subject = $this->request->getPost('subject');
        $message = decode_ajax_post_data($this->request->getPost('message'));

        $contact = $this->Users_model->get_one($contact_id);

        $default_bcc = get_setting('send_contract_bcc_to');
        $bcc_emails = "";

        if ($default_bcc && $custom_bcc) {
            $bcc_emails = $default_bcc . "," . $custom_bcc;
        } else if ($default_bcc) {
            $bcc_emails = $default_bcc;
        } else if ($custom_bcc) {
            $bcc_emails = $custom_bcc;
        }

        if (send_app_mail($contact->email, $subject, $message, array("cc" => $cc, "bcc" => $bcc_emails))) {
            // change email status
            $status_data = array("status" => "sent", "last_email_sent_date" => get_my_local_time());
            if ($this->Contracts_model->ci_save($status_data, $contract_id)) {
                echo json_encode(array('success' => true, 'message' => app_lang("contract_sent_message"), "contract_id" => $contract_id));
            }
        } else {
            echo json_encode(array('success' => false, 'message' => app_lang('error_occurred')));
        }
    }

    //update the sort value for contract item
    function update_item_sort_values($id = 0) {
        $this->access_only_allowed_members();

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
                $this->Contract_items_model->ci_save($data, $id);
            }
        }
    }

    function editor($contract_id = 0) {
        $this->access_only_allowed_members();
        $view_data['contract_info'] = $this->Contracts_model->get_details(array("id" => $contract_id))->getRow();
        return $this->template->view("contracts/contract_editor", $view_data);
    }

    /* prepare project dropdown based on this suggestion */

    function get_project_suggestion($client_id = 0) {
        $this->access_only_allowed_members();

        $projects = $this->Projects_model->get_dropdown_list(array("title"), "id", array("client_id" => $client_id, "project_type" => "client_project"));
        $suggestion = array(array("id" => "", "text" => "-"));
        foreach ($projects as $key => $value) {
            $suggestion[] = array("id" => $key, "text" => $value);
        }
        echo json_encode($suggestion);
    }

    /* list of contract of a specific project, prepared for datatable  */

    function contract_list_data_of_project($project_id) {
        $this->access_only_allowed_members();

        $custom_fields = $this->Custom_fields_model->get_available_fields_for_table("contracts", $this->login_user->is_admin, $this->login_user->user_type);

        $options = array(
            "project_id" => $project_id,
            "status" => $this->request->getPost("status"),
            "custom_fields" => $custom_fields,
            "custom_field_filter" => $this->prepare_custom_field_filter_values("contracts", $this->login_user->is_admin, $this->login_user->user_type)
        );
        $list_data = $this->Contracts_model->get_details($options)->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data, $custom_fields);
        }
        echo json_encode(array("data" => $result));
    }

    /* upload a file */

    function upload_file() {
        upload_file_to_temp();
    }

    /* check valid file for contracts */

    function validate_contracts_file() {
        return validate_post_file($this->request->getPost("file_name"));
    }

}

/* End of file Contracts.php */
/* Location: ./app/Controllers/Contracts.php */