<?php

namespace App\Controllers;

class Offer extends Security_Controller {

    function __construct() {
        parent::__construct(false);
    }

    function index() {
        app_redirect("forbidden");
    }

    function preview($proposal_id = 0, $public_key = "") {
        if (!($proposal_id && $public_key)) {
            show_404();
        }

        validate_numeric_value($proposal_id);

        //check public key
        $proposal_info = $this->Proposals_model->get_one($proposal_id);
        if ($proposal_info->public_key !== $public_key) {
            show_404();
        }

        $view_data = array();

        $proposal_data = get_proposal_making_data($proposal_id);
        if (!$proposal_data) {
            show_404();
        }

        $view_data['proposal_preview'] = prepare_proposal_view($proposal_data);
        $view_data['show_close_preview'] = false; //don't show back button
        $view_data['proposal_id'] = $proposal_id;
        $view_data['proposal_type'] = "public";
        $view_data['public_key'] = clean_data($public_key);

        return view("proposals/proposal_public_preview", $view_data);
    }

    //update proposal status
    function update_proposal_status($proposal_id, $public_key, $status) {
        validate_numeric_value($proposal_id);
        if (!($proposal_id && $public_key && $status)) {
            show_404();
        }

        $proposal_info = $this->Proposals_model->get_one($proposal_id);
        if (!($proposal_info->id && $proposal_info->public_key === $public_key)) {
            show_404();
        }

        //client can only update the status once and the value should be either accepted or declined
        if ($status == "accepted" || $status == "declined") {
            $proposal_data = array("status" => $status);
            $proposal_id = $this->Proposals_model->ci_save($proposal_data, $proposal_id);

            //create notification
            if ($status == "accepted") {
                log_notification("proposal_accepted", array("proposal_id" => $proposal_id), isset($this->login_user->id) ? $this->login_user->id : "999999996");
                $this->session->setFlashdata("success_message", app_lang("proposal_accepted"));
            } else if ($status == "declined") {
                log_notification("proposal_rejected", array("proposal_id" => $proposal_id), isset($this->login_user->id) ? $this->login_user->id : "999999996");
                $this->session->setFlashdata("error_message", app_lang('proposal_rejected'));
            }
        }
    }

    //print proposal
    function print_proposal($proposal_id = 0, $public_key = "") {
        validate_numeric_value($proposal_id);
        if ($proposal_id && $public_key) {
            $view_data = get_proposal_making_data($proposal_id);

            //check public key
            $proposal_info = get_array_value($view_data, "proposal_info");
            if ($proposal_info->public_key !== $public_key) {
                show_404();
            }

            $view_data['proposal_preview'] = prepare_proposal_view($view_data);

            echo json_encode(array("success" => true, "print_view" => $this->template->view("proposals/print_proposal", $view_data)));
        } else {
            echo json_encode(array("success" => false, app_lang('error_occurred')));
        }
    }

    function accept_proposal_modal_form($proposal_id = 0, $public_key = "") {
        validate_numeric_value($proposal_id);
        if (!$proposal_id) {
            show_404();
        }

        $proposal_info = $this->Proposals_model->get_one($proposal_id);
        if (!$proposal_info->id) {
            show_404();
        }

        if ($public_key) {
            //public proposal
            if ($proposal_info->public_key !== $public_key) {
                show_404();
            }

            $view_data["show_info_fields"] = true;
        } else {
            //proposal preview, should be logged in client contact
            $this->init_permission_checker("proposal");
            $this->access_only_allowed_members_or_client_contact($proposal_info->client_id);
            if ($this->login_user->user_type === "client" && $this->login_user->client_id !== $proposal_info->client_id) {
                show_404();
            }

            $view_data["show_info_fields"] = false;
        }

        $view_data["model_info"] = $proposal_info;
        return $this->template->view('proposals/accept_proposal_modal_form', $view_data);
    }

    function accept_proposal() {
        $validation_array = array(
            "id" => "numeric|required",
            "public_key" => "required"
        );

        if (get_setting("add_signature_option_on_accepting_proposal")) {
            $validation_array["signature"] = "required";
        }

        $this->validate_submitted_data($validation_array);

        $proposal_id = $this->request->getPost("id");
        $proposal_info = $this->Proposals_model->get_one($proposal_id);
        if (!$proposal_info->id) {
            show_404();
        }

        $public_key = $this->request->getPost("public_key");
        if ($proposal_info->public_key !== $public_key) {
            show_404();
        }

        $name = $this->request->getPost("name");
        $email = $this->request->getPost("email");
        $signature = $this->request->getPost("signature");

        $meta_data = array();
        $proposal_data = array();

        if ($signature) {
            $signature = explode(",", $signature);
            $signature = get_array_value($signature, 1);
            $signature = base64_decode($signature);
            $signature = serialize(move_temp_file("signature.jpg", get_setting("timeline_file_path"), "proposal", NULL, "", $signature));

            $meta_data["signature"] = $signature;
            $meta_data["signed_date"] = get_current_utc_time();
        }

        if ($name) {
            //from public proposal
            if (!$email) {
                show_404();
            }

            $meta_data["name"] = $name;
            $meta_data["email"] = $email;
        } else {
            //from preview, should be logged in client contact
            $this->init_permission_checker("proposal");
            $this->access_only_allowed_members_or_client_contact($proposal_info->client_id);
            if ($this->login_user->user_type === "client" && $this->login_user->client_id !== $proposal_info->client_id) {
                show_404();
            }

            $proposal_data["accepted_by"] = $this->login_user->id;
        }

        $proposal_data["meta_data"] = serialize($meta_data);
        $proposal_data["status"] = "accepted";

        if ($this->Proposals_model->ci_save($proposal_data, $proposal_id)) {
            log_notification("proposal_accepted", array("proposal_id" => $proposal_id), ($name ? "999999996" : $this->login_user->id));
            echo json_encode(array("success" => true, "message" => app_lang("proposal_accepted")));
        } else {
            echo json_encode(array("success" => false, "message" => app_lang("error_occurred")));
        }
    }

}

/* End of file Offer.php */
/* Location: ./app/controllers/Offer.php */