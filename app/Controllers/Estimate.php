<?php

namespace App\Controllers;

class Estimate extends Security_Controller {

    function __construct() {
        parent::__construct(false);
    }

    function index() {
        app_redirect("forbidden");
    }

    function preview($estimate_id = 0, $public_key = "") {
        if (!($estimate_id && $public_key)) {
            show_404();
        }

        validate_numeric_value($estimate_id);

        //check public key
        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        if ($estimate_info->public_key !== $public_key) {
            show_404();
        }

        $view_data = array();

        $estimate_data = get_estimate_making_data($estimate_id);
        if (!$estimate_data) {
            show_404();
        }

        $view_data['estimate_preview'] = prepare_estimate_pdf($estimate_data, "html");
        $view_data['show_close_preview'] = false; //don't show back button
        $view_data['estimate_id'] = $estimate_id;
        $view_data['estimate_type'] = "public";
        $view_data['public_key'] = clean_data($public_key);

        return view("estimates/estimate_public_preview", $view_data);
    }

    //update estimate status
    function update_estimate_status($estimate_id, $public_key, $status) {
        validate_numeric_value($estimate_id);
        if (!($estimate_id && $public_key && $status)) {
            show_404();
        }

        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        if (!($estimate_info->id && $estimate_info->public_key === $public_key)) {
            show_404();
        }

        //client can only update the status once and the value should be either accepted or declined
        if ($status == "accepted" || $status == "declined") {
            $estimate_data = array("status" => $status);
            $estimate_id = $this->Estimates_model->ci_save($estimate_data, $estimate_id);

            //create notification
            if ($status == "accepted") {
                log_notification("estimate_accepted", array("estimate_id" => $estimate_id), isset($this->login_user->id) ? $this->login_user->id : "999999996");
                $this->session->setFlashdata("success_message", app_lang("estimate_accepted"));
            } else if ($status == "declined") {
                log_notification("estimate_rejected", array("estimate_id" => $estimate_id), isset($this->login_user->id) ? $this->login_user->id : "999999996");
                $this->session->setFlashdata("error_message", app_lang('estimate_rejected'));
            }
        }
    }

    function accept_estimate_modal_form($estimate_id = 0, $public_key = "") {
        validate_numeric_value($estimate_id);
        if (!$estimate_id) {
            show_404();
        }

        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        if (!$estimate_info->id) {
            show_404();
        }

        if ($public_key) {
            //public estimate
            if ($estimate_info->public_key !== $public_key) {
                show_404();
            }

            $view_data["show_info_fields"] = true;
        } else {
            //estimate preview, should be logged in client contact
            $this->access_only_clients();
            if ($this->login_user->user_type === "client" && $this->login_user->client_id !== $estimate_info->client_id) {
                show_404();
            }

            $view_data["show_info_fields"] = false;
        }

        $view_data["model_info"] = $estimate_info;
        return $this->template->view('estimates/accept_estimate_modal_form', $view_data);
    }

    function accept_estimate() {
        $validation_array = array(
            "id" => "numeric|required",
            "public_key" => "required"
        );

        if (get_setting("add_signature_option_on_accepting_estimate")) {
            $validation_array["signature"] = "required";
        }

        $this->validate_submitted_data($validation_array);

        $estimate_id = $this->request->getPost("id");
        $estimate_info = $this->Estimates_model->get_one($estimate_id);
        if (!$estimate_info->id) {
            show_404();
        }

        $public_key = $this->request->getPost("public_key");
        if ($estimate_info->public_key !== $public_key) {
            show_404();
        }

        $name = $this->request->getPost("name");
        $email = $this->request->getPost("email");
        $signature = $this->request->getPost("signature");

        $meta_data = array();
        $estimate_data = array();

        if ($signature) {
            $signature = explode(",", $signature);
            $signature = get_array_value($signature, 1);
            $signature = base64_decode($signature);
            $signature = serialize(move_temp_file("signature.jpg", get_setting("timeline_file_path"), "estimate", NULL, "", $signature));

            $meta_data["signature"] = $signature;
        }

        if ($name) {
            //from public estimate
            if (!$email) {
                show_404();
            }

            $meta_data["name"] = $name;
            $meta_data["email"] = $email;
        } else {
            //from preview, should be logged in client contact
            $this->init_permission_checker("estimate");
            $this->access_only_allowed_members_or_client_contact($estimate_info->client_id);
            if ($this->login_user->user_type === "client" && $this->login_user->client_id !== $estimate_info->client_id) {
                show_404();
            }

            $estimate_data["accepted_by"] = $this->login_user->id;
        }

        $estimate_data["meta_data"] = serialize($meta_data);
        $estimate_data["status"] = "accepted";

        if ($this->Estimates_model->ci_save($estimate_data, $estimate_id)) {
            log_notification("estimate_accepted", array("estimate_id" => $estimate_id), ($name ? "999999996" : $this->login_user->id));
            echo json_encode(array("success" => true, "message" => app_lang("estimate_accepted")));
        } else {
            echo json_encode(array("success" => false, "message" => app_lang("error_occurred")));
        }
    }

}

/* End of file Estimate.php */
/* Location: ./app/controllers/Estimate.php */