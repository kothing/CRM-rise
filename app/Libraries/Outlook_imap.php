<?php

namespace App\Libraries;

use App\Controllers\App_Controller;

class Outlook_imap {

    private $responseCode = 0;
    private $ci;

    public function __construct() {
        $this->ci = new App_Controller();
        $this->client_id = get_setting("outlook_imap_client_id");
        $this->client_secret = get_setting('outlook_imap_client_secret');
        $this->login_url = "https://login.microsoftonline.com/common/oauth2/v2.0";
        $this->graph_url = "https://graph.microsoft.com/beta/me/";
        $this->redirect_uri = get_uri("microsoft_api/save_outlook_imap_access_token");

        //load EmailReplyParser resources
        require_once(APPPATH . "ThirdParty/Imap/EmailReplyParser/vendor/autoload.php");
    }

    public function run_imap() {
        $messages = $this->do_request("GET", 'mailFolders/inbox/messages');

        foreach ($messages->value as $message) {
            //create tickets for unread mails
            if (!$message->isRead) {
                $this->_create_ticket_from_imap($message);

                //mark the mail as read
                $this->do_request("PATCH", "messages/$message->id", array("isRead" => true));
            }
        }
    }

    //authorize connection
    public function authorize() {
        $url = "$this->login_url/authorize?";
        $auth_array = array(
            "client_id" => $this->client_id,
            "response_type" => "code",
            "redirect_uri" => $this->redirect_uri,
            "response_mode" => "query",
            "scope" => "offline_access%20user.read%20IMAP.AccessAsUser.All%20Mail.ReadWrite",
        );

        foreach ($auth_array as $key => $value) {
            $url .= "$key=$value";

            if ($key !== "scope") {
                $url .= "&";
            }
        }

        app_redirect($url, true);
    }

    private function common_error_handling_for_curl($result, $err, $decode_result = true) {
        if ($decode_result) {
            try {
                $result = json_decode($result);
            } catch (\Exception $ex) {
                echo json_encode(array("success" => false, 'message' => $ex->getMessage()));
                log_message('error', $ex); //log error for every exception
                exit();
            }
        }

        if ($err) {
            //got curl error
            echo json_encode(array("success" => false, 'message' => "cURL Error #:" . $err));
            log_message('error', $err); //log error for every exception
            exit();
        }

        if (isset($result->error_description) && $result->error_description) {
            //got error message from curl
            echo json_encode(array("success" => false, 'message' => $result->error_description));
            log_message('error', $result->error_description); //log error for every exception
            exit();
        }

        if (isset($result->error) && $result->error &&
                isset($result->error->message) && $result->error->message &&
                isset($result->error->code) && $result->error->code !== "InvalidAuthenticationToken") {
            //got error message from curl
            echo json_encode(array("success" => false, 'message' => $result->error->message));
            log_message('error', $result->error->message); //log error for every exception
            exit();
        }

        return $result;
    }

    //fetch access token with auth code and save to database
    public function save_access_token($code, $is_refresh_token = false) {
        $fields = array(
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "redirect_uri" => $this->redirect_uri,
            "scope" => "IMAP.AccessAsUser.All Mail.ReadWrite",
            "grant_type" => "authorization_code",
        );

        if ($is_refresh_token) {
            $fields["refresh_token"] = $code;
            $fields["grant_type"] = "refresh_token";
        } else {
            $fields["code"] = $code;
        }

        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, "$this->login_url/token");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
        ));

        //So that curl_exec returns the contents of the cURL;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $result = $this->common_error_handling_for_curl($result, $err);

        if (!(
                (!$is_refresh_token && isset($result->access_token) && isset($result->refresh_token)) ||
                ($is_refresh_token && isset($result->access_token))
                )) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            exit();
        }

        if ($is_refresh_token) {
            //while refreshing token, refresh_token value won't be available
            $result->refresh_token = $code;
        }

        // Save the token to database
        $new_access_token = json_encode($result);

        if ($new_access_token) {
            $this->ci->Settings_model->save_setting('outlook_imap_oauth_access_token', $new_access_token);

            if (!$is_refresh_token) {
                //store email address for the first time
                $user_info = $this->do_request("GET");
                if (isset($user_info->userPrincipalName) && $user_info->userPrincipalName) {
                    $this->ci->Settings_model->save_setting('outlook_imap_email', $user_info->userPrincipalName);
                } else {
                    echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
                    exit();
                }
            }

            //got the valid access token. store to setting that it's authorized
            $this->ci->Settings_model->save_setting('imap_authorized', "1");
        }
    }

    private function headers($access_token) {
        return array(
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json'
        );
    }

    private function do_request($method, $path = "", $body = array(), $decode_result = true) {
        if (is_array($body)) {
            // Treat an empty array in the body data as if no body data was set
            if (!count($body)) {
                $body = '';
            } else {
                $body = json_encode($body);
            }
        }

        $oauth_access_token = $this->ci->Settings_model->get_setting('outlook_imap_oauth_access_token');
        $oauth_access_token = json_decode($oauth_access_token);

        $method = strtoupper($method);
        $url = $this->graph_url . $path;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers($oauth_access_token->access_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (in_array($method, array('DELETE', 'PATCH', 'POST', 'PUT', 'GET'))) {

            // All except DELETE can have a payload in the body
            if ($method != 'DELETE' && strlen($body)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = $this->common_error_handling_for_curl($result, $err, $decode_result);

        if (isset($result->error->code) && $result->error->code === "InvalidAuthenticationToken") {
            //access token is expired
            $this->save_access_token($oauth_access_token->refresh_token, true);
            return $this->do_request($method, $path, $body, $decode_result);
        }

        return $result;
    }

    private function _create_ticket_from_imap($message_info = "") {
        if ($message_info) {
            $email = $message_info->from->emailAddress->address;
            $subject = $message_info->subject;

            //check if there has any client containing this email address
            //if so, go through with the client id
            $client_info = $this->ci->Users_model->get_one_where(array("email" => $email, "user_type" => "client", "deleted" => 0));

            if (get_setting("create_tickets_only_by_registered_emails") && !$client_info->id) {
                return false;
            }

            $ticket_id = $this->_get_ticket_id_from_subject($subject);

            //check if the ticket is exists on the app
            //if not, that will be considered as a new ticket
            //but for this case, it's a replying email. we've to parse the message
            $replying_email = false;
            if ($ticket_id) {
                $existing_ticket_info = $this->ci->Tickets_model->get_one_where(array("id" => $ticket_id, "deleted" => 0));
                if (!$existing_ticket_info->id) {
                    $ticket_id = "";
                    $replying_email = true;
                }
            }

            if ($ticket_id) {
                //if the message have ticket id, we have to assume that, it's a reply of the specific ticket
                $ticket_comment_id = $this->_save_tickets_comment($ticket_id, $message_info, $client_info, true);

                if ($ticket_id && $ticket_comment_id) {
                    log_notification("ticket_commented", array("ticket_id" => $ticket_id, "ticket_comment_id" => $ticket_comment_id, "exclude_ticket_creator" => true), $client_info->id ? $client_info->id : "0");
                }
            } else {

                $creator_name = $message_info->from->emailAddress->name;
                $now = get_current_utc_time();
                $ticket_data = array(
                    "title" => $subject ? $subject : $email, //show creator's email as ticket's title, if there is no subject
                    "created_at" => $now,
                    "creator_name" => $creator_name ? $creator_name : "",
                    "creator_email" => $email ? $email : "",
                    "client_id" => $client_info->id ? $client_info->client_id : 0,
                    "created_by" => $client_info->id ? $client_info->id : 0,
                    "last_activity_at" => $now
                );

                $ticket_id = $this->ci->Tickets_model->ci_save($ticket_data);

                if ($ticket_id) {
                    //save email message as the ticket's comment
                    $ticket_comment_id = $this->_save_tickets_comment($ticket_id, $message_info, $client_info, $replying_email);

                    if ($ticket_id && $ticket_comment_id) {
                        log_notification("ticket_created", array("ticket_id" => $ticket_id, "ticket_comment_id" => $ticket_comment_id, "exclude_ticket_creator" => true), $client_info->id ? $client_info->id : "0");
                    }
                }
            }
        }
    }

    //save tickets comment
    private function _save_tickets_comment($ticket_id, $message_info, $client_info, $is_reply = false) {
        if ($ticket_id) {
            $description = $message_info->body->content;
            if ($is_reply) {
                $description = $this->_prepare_replying_message($description);
            }

            try {
                //get content inside body tag only if it exists
                if ($description) {
                    preg_match("/<body[^>]*>(.*?)<\/body>/is", $description, $body_matches);
                    $description = isset($body_matches[1]) ? $body_matches[1] : $description;
                }
            } catch (\Exception $ex) {
                
            }

            $comment_data = array(
                "description" => $description,
                "ticket_id" => $ticket_id,
                "created_by" => $client_info->id ? $client_info->id : 0,
                "created_at" => get_current_utc_time()
            );

            $comment_data = clean_data($comment_data);

            $files_data = $this->_prepare_attachment_data_of_mail($message_info);
            $comment_data["files"] = serialize($files_data);

            //add client_replied status when it's a reply
            if ($is_reply) {
                $ticket_data = array(
                    "status" => "client_replied",
                    "last_activity_at" => get_current_utc_time()
                );

                $this->ci->Tickets_model->ci_save($ticket_data, $ticket_id);
            }

            $ticket_comment_id = $this->ci->Ticket_comments_model->ci_save($comment_data);

            if (!$is_reply) {
                add_auto_reply_to_ticket($ticket_id);
            }

            return $ticket_comment_id;
        }
    }

    private function _prepare_replying_message($message = "") {
        try {
            $reply_parser = new \EmailReplyParser\EmailReplyParser();
            return $reply_parser->parseReply($message);
        } catch (\Exception $ex) {
            return "";
        }
    }

    //get ticket id
    private function _get_ticket_id_from_subject($subject = "") {
        if ($subject) {
            $find_hash = strpos($subject, "#");
            if ($find_hash) {
                $rest_from_hash = substr($subject, $find_hash + 1); //get the rest text from ticket's #
                $ticket_id = (int) substr($rest_from_hash, 0, strpos($rest_from_hash, " "));

                if ($ticket_id && is_int($ticket_id)) {
                    return $ticket_id;
                }
            }
        }
    }

    //download attached files to local
    private function _prepare_attachment_data_of_mail($message_info = "") {
        $files_data = array();

        if ($message_info && $message_info->hasAttachments) {
            $attachments = $this->do_request("GET", "messages/$message_info->id/attachments");

            foreach ($attachments->value as $attachment) {
                $content = $this->do_request("GET", "messages/$message_info->id/attachments/$attachment->id/" . '$value', array(), false);
                $file_data = move_temp_file($attachment->name, get_setting("timeline_file_path"), "imap_ticket", NULL, "", $content);

                array_push($files_data, $file_data);
            }
        }

        return $files_data;
    }

}
