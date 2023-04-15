<?php

namespace App\Controllers;

class Collect_leads extends App_Controller {

    function __construct() {
        parent::__construct();
    }

    function index($lead_source_id = 0) { //embedded
        if (!get_setting("enable_embedded_form_to_get_leads")) {
            show_404();
        }
        
        validate_numeric_value($lead_source_id);

        $view_data['topbar'] = false;
        $view_data['left_menu'] = false;

        $view_data["currency_dropdown"] = $this->_get_currency_dropdown_select2_data();

        //get custom fields
        $view_data["custom_fields"] = $this->Custom_fields_model->get_details(array("show_in_embedded_form" => true))->getResult();
        $view_data["lead_source_id"] = $lead_source_id;
        
        return $this->template->rander("collect_leads/index", $view_data);
    }

    private function is_valid_recaptcha($recaptcha_post_data) {
        //load recaptcha lib
        require_once(APPPATH . "ThirdParty/recaptcha/autoload.php");
        $recaptcha = new \ReCaptcha\ReCaptcha(get_setting("re_captcha_secret_key"));
        $resp = $recaptcha->verify($recaptcha_post_data, $_SERVER['REMOTE_ADDR']);

        if ($resp->isSuccess()) {
            return true;
        } else {

            $error = "";
            foreach ($resp->getErrorCodes() as $code) {
                $error = $code;
            }

            return $error;
        }
    }

    //save external lead
    function save() {
        if (!get_setting("can_create_lead_from_public_form")) {
            show_404();
        }

        $this->validate_submitted_data(array(
            "company_name" => "required",
            "lead_source_id" => "numeric",
        ));

        $is_embedded_form = $this->request->getPost("is_embedded_form");

        //check if there reCaptcha is enabled
        //if reCaptcha is enabled, check the validation
        if ($is_embedded_form && get_setting("re_captcha_secret_key")) {

            $response = $this->is_valid_recaptcha($this->request->getPost("g-recaptcha-response"));

            if ($response !== true) {

                if ($response) {
                    echo json_encode(array('success' => false, 'message' => app_lang("re_captcha_error-" . $response)));
                } else {
                    echo json_encode(array('success' => false, 'message' => app_lang("re_captcha_expired")));
                }

                return false;
            }
        }

        //validate duplicate email address
        if ($this->request->getPost('email') && $this->Users_model->is_email_exists(trim($this->request->getPost('email')))) {
            echo json_encode(array("success" => false, 'message' => app_lang('duplicate_email')));
            exit();
        }

        $leads_data = array(
            "company_name" => $this->request->getPost('company_name'),
            "address" => $this->request->getPost('address'),
            "city" => $this->request->getPost('city'),
            "state" => $this->request->getPost('state'),
            "zip" => $this->request->getPost('zip'),
            "country" => $this->request->getPost('country'),
            "phone" => $this->request->getPost('phone'),
            "is_lead" => 1,
            "lead_status_id" => $this->Lead_status_model->get_first_status(),
            "lead_source_id" => $this->request->getPost("lead_source_id"),
            "created_date" => get_current_utc_time(),
            "owner_id" => 1 //add default admin
        );

        $leads_data = clean_data($leads_data);
        $lead_id = $this->Clients_model->ci_save($leads_data);
        if (!$lead_id) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }

        save_custom_fields("leads", $lead_id, 1, "staff");

        //lead created, create a contact on that lead
        $lead_contact_data = array(
            "first_name" => $this->request->getPost('first_name'),
            "last_name" => $this->request->getPost('last_name'),
            "client_id" => $lead_id,
            "user_type" => "lead",
            "email" => trim($this->request->getPost('email')),
            "created_at" => get_current_utc_time(),
            "is_primary_contact" => 1
        );

        $lead_contact_data = clean_data($lead_contact_data);
        $lead_contact_id = $this->Users_model->ci_save($lead_contact_data);
        if (!$lead_contact_id) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }

        log_notification("lead_created", array("lead_id" => $lead_id), "0");

        $after_submit_action_of_public_lead_form = get_setting("after_submit_action_of_public_lead_form");
        $after_submit_action_of_public_lead_form_redirect_url = get_setting("after_submit_action_of_public_lead_form_redirect_url");
        if ($is_embedded_form || $after_submit_action_of_public_lead_form === "json") {
            echo json_encode(array("success" => true, 'message' => app_lang('lead_created')));
        } else if ($after_submit_action_of_public_lead_form === "text") {
            echo app_lang('lead_created');
        } else if ($after_submit_action_of_public_lead_form === "redirect" && $after_submit_action_of_public_lead_form_redirect_url) {
            app_redirect($after_submit_action_of_public_lead_form_redirect_url, true);
        }
    }

    function lead_html_form_code_modal_form() {
        $lead_html_form_code = view("collect_leads/lead_html_form_code");
        $view_data['lead_html_form_code'] = $lead_html_form_code;

        return $this->template->view('collect_leads/lead_html_form_code_modal_form', $view_data);
    }

    function embedded_code_modal_form() {
        $embedded_code = "<iframe width='768' height='360' src='" . get_uri("collect_leads") . "' frameborder='0'></iframe>";
        $view_data['embedded'] = $embedded_code;

        return $this->template->view('collect_leads/embedded_code_modal_form', $view_data);
    }

}

/* End of file Collect_leads.php */
/* Location: ./app/controllers/Collect_leads.php */