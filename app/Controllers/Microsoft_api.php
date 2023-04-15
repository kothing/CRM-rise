<?php

namespace App\Controllers;

use App\Libraries\Outlook_imap;
use App\Libraries\Outlook_smtp;

class Microsoft_api extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->Outlook_imap = new Outlook_imap();
        $this->Outlook_smtp = new Outlook_smtp();
    }

    function index() {
        show_404();
    }

    function authorize_outlook_imap() {
        $this->Outlook_imap->authorize();
    }

    function save_outlook_imap_access_token() {
        if (!empty($_GET)) {
            $this->Outlook_imap->save_access_token(get_array_value($_GET, 'code'));
            app_redirect("ticket_types");
        }
    }

    function authorize_outlook_smtp() {
        $this->Outlook_smtp->authorize();
    }

    function save_outlook_smtp_access_token() {
        if (!empty($_GET)) {
            $this->Outlook_smtp->save_access_token(get_array_value($_GET, 'code'));
            app_redirect("settings/email");
        }
    }

}

/* End of file Microsoft_api.php */
/* Location: ./app/controllers/Microsoft_api.php */