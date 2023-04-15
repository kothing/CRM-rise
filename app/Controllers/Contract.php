<?php

namespace App\Controllers;

class Contract extends Security_Controller {

    function __construct() {
        parent::__construct(false);
    }

    function index() {
        app_redirect("forbidden");
    }

    function preview($contract_id = 0, $public_key = "") {
        if (!($contract_id && $public_key)) {
            show_404();
        }

        validate_numeric_value($contract_id);

        //check public key
        $contract_info = $this->Contracts_model->get_one($contract_id);
        if ($contract_info->public_key !== $public_key) {
            show_404();
        }

        $view_data = array();

        $contract_data = get_contract_making_data($contract_id);
        if (!$contract_data) {
            show_404();
        }

        $view_data['contract_preview'] = prepare_contract_view($contract_data);
        $view_data['show_close_preview'] = false; //don't show back button
        $view_data['contract_id'] = $contract_id;
        $view_data['contract_type'] = "public";
        $view_data['public_key'] = clean_data($public_key);

        return view("contracts/contract_public_preview", $view_data);
    }

    //update contract status
    function update_contract_status($contract_id, $public_key, $status) {
        validate_numeric_value($contract_id);
        if (!($contract_id && $public_key && $status)) {
            show_404();
        }

        $contract_info = $this->Contracts_model->get_one($contract_id);
        if (!($contract_info->id && $contract_info->public_key === $public_key)) {
            show_404();
        }

        //client can only update the status once and the value should be either accepted or declined
        if ($status == "accepted" || $status == "declined") {
            $contract_data = array("status" => $status);
            $contract_id = $this->Contracts_model->ci_save($contract_data, $contract_id);

            //create notification
            if ($status == "accepted") {
                log_notification("contract_accepted", array("contract_id" => $contract_id), isset($this->login_user->id) ? $this->login_user->id : "999999996");
                $this->session->setFlashdata("success_message", app_lang("contract_accepted"));
            } else if ($status == "declined") {
                log_notification("contract_rejected", array("contract_id" => $contract_id), isset($this->login_user->id) ? $this->login_user->id : "999999996");
                $this->session->setFlashdata("error_message", app_lang('contract_rejected'));
            }
        }
    }

    //print contract
    function print_contract($contract_id = 0, $public_key = "") {
        validate_numeric_value($contract_id);
        if ($contract_id && $public_key) {
            $view_data = get_contract_making_data($contract_id);

            //check public key
            $contract_info = get_array_value($view_data, "contract_info");
            if ($contract_info->public_key !== $public_key) {
                show_404();
            }

            $view_data['contract_preview'] = prepare_contract_view($view_data);

            echo json_encode(array("success" => true, "print_view" => $this->template->view("contracts/print_contract", $view_data)));
        } else {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
        }
    }

    function accept_contract_modal_form($contract_id = 0, $public_key = "") {
        validate_numeric_value($contract_id);
        if (!$contract_id) {
            show_404();
        }

        $contract_info = $this->Contracts_model->get_one($contract_id);
        if (!$contract_info->id) {
            show_404();
        }

        if ($public_key) {
            //public contract
            if ($contract_info->public_key !== $public_key) {
                show_404();
            }

            $view_data["show_info_fields"] = true;
        } else {
            //contract preview, should be logged in client contact or team member
            $this->init_permission_checker("contract");
            $this->access_only_allowed_members_or_client_contact($contract_info->client_id);
            if ($this->login_user->user_type === "client" && $this->login_user->client_id !== $contract_info->client_id) {
                show_404();
            }

            $view_data["show_info_fields"] = false;
        }

        $view_data["model_info"] = $contract_info;
        return $this->template->view('contracts/accept_contract_modal_form', $view_data);
    }

    function accept_contract() {
        $validation_array = array(
            "id" => "numeric|required",
            "public_key" => "required"
        );

        if (get_setting("add_signature_option_on_accepting_contract") || get_setting("add_signature_option_for_team_members")) {
            $validation_array["signature"] = "required";
        }

        $this->validate_submitted_data($validation_array);

        $contract_id = $this->request->getPost("id");
        $contract_info = $this->Contracts_model->get_one($contract_id);
        if (!$contract_info->id) {
            show_404();
        }

        $public_key = $this->request->getPost("public_key");
        if ($contract_info->public_key !== $public_key) {
            show_404();
        }

        $name = $this->request->getPost("name");
        $email = $this->request->getPost("email");
        $signature = $this->request->getPost("signature");

        $meta_data = $contract_info->meta_data ? unserialize($contract_info->meta_data) : array(); //check if ther has already some meta data
        $contract_data = array();

        if ($signature) {
            $signature = explode(",", $signature);
            $signature = get_array_value($signature, 1);
            $signature = base64_decode($signature);
            $signature = serialize(move_temp_file("signature.jpg", get_setting("timeline_file_path"), "contract", NULL, "", $signature));

            if (!$name && $this->login_user->user_type === "staff") {
                $meta_data["staff_signature"] = $signature;
                $meta_data["staff_signed_date"] = get_current_utc_time();
            } else {
                $meta_data["signature"] = $signature;
                $meta_data["signed_date"] = get_current_utc_time();
            }
        }

        if ($name) {
            //from public contract
            if (!$email) {
                show_404();
            }

            $meta_data["name"] = $name;
            $meta_data["email"] = $email;
        } else {
            //from preview, should be logged in client contact/team member
            $this->init_permission_checker("contract");
            $this->access_only_allowed_members_or_client_contact($contract_info->client_id);
            if ($this->login_user->user_type === "client" && $this->login_user->client_id !== $contract_info->client_id) {
                show_404();
            }

            if ($this->login_user->user_type === "staff") {
                $contract_data["staff_signed_by"] = $this->login_user->id;
            } else {
                $contract_data["accepted_by"] = $this->login_user->id;
            }
        }

        $contract_data["meta_data"] = serialize($meta_data);
        $contract_data["status"] = "accepted";

        if ($this->Contracts_model->ci_save($contract_data, $contract_id)) {
            log_notification("contract_accepted", array("contract_id" => $contract_id), ($name ? "999999996" : $this->login_user->id));
            echo json_encode(array("success" => true, "message" => app_lang("contract_accepted")));
        } else {
            echo json_encode(array("success" => false, "message" => app_lang("error_occurred")));
        }
    }

    function file_preview($id = "", $key = "", $public_key = "") {
        if (!$id) {
            show_404();
        }

        $contract_info = $this->Contracts_model->get_one($id);
        if ($contract_info->public_key !== $public_key) {
            show_404();
        }

        $files = unserialize($contract_info->files);
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
        $view_data["is_iframe_preview_available"] = is_iframe_preview_available($file_name);

        return $this->template->view("contracts/file_preview", $view_data);
    }

}

/* End of file Contract.php */
/* Location: ./app/controllers/Contract.php */