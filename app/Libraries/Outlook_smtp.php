<?php

namespace App\Libraries;

use App\Controllers\App_Controller;
use Config\Mimes;

class Outlook_smtp {

    private $responseCode = 0;
    private $ci;
    protected static $func_overload;

    public function __construct() {
        $this->ci = new App_Controller();
        $this->client_id = get_setting("outlook_smtp_client_id");
        $this->client_secret = get_setting('outlook_smtp_client_secret');
        $this->login_url = "https://login.microsoftonline.com/common/oauth2/v2.0";
        $this->graph_url = "https://graph.microsoft.com/beta/me/";
        $this->redirect_uri = get_uri("microsoft_api/save_outlook_smtp_access_token");

        if (!isset(static::$func_overload)) {
            static::$func_overload = (extension_loaded('mbstring') && ini_get('mbstring.func_overload'));
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
            "scope" => "offline_access%20user.read%20Mail.Send",
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
            "scope" => "Mail.Send",
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
            $this->ci->Settings_model->save_setting('outlook_smtp_oauth_access_token', $new_access_token);

            //got the valid access token. store to setting that it's authorized
            $this->ci->Settings_model->save_setting('outlook_smtp_authorized', "1");

            //send test email if any
            $test_mail_to = get_setting("send_test_mail_to");

            if ($test_mail_to && !$is_refresh_token) {
                $email = array(
                    "message" => array(
                        "subject" => "Test message",
                        "body" => array(
                            "contentType" => "Html",
                            "content" => "This is a test message to check mail configuration."
                        ),
                        "toRecipients" => array(
                            array(
                                "emailAddress" => array(
                                    "address" => $test_mail_to
                                )
                            )
                        )
                    )
                );

                $this->do_request("POST", "sendMail", $email);

                //delete temporary data
                $this->ci->Settings_model->save_setting('send_test_mail_to', "");
            }
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

        $oauth_access_token = $this->ci->Settings_model->get_setting('outlook_smtp_oauth_access_token');
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

    public function send_app_mail($to, $subject, $message, $optoins = array(), $convert_message_to_html = true) {
        if ($convert_message_to_html) {
            $message = htmlspecialchars_decode($message);
        }

        $message = rtrim(str_replace("\r", '', $message)); //from ci

        $email = array(
            "message" => array(
                "subject" => $subject,
                "body" => array(
                    "contentType" => "Html",
                    "content" => $message
                ),
                "toRecipients" => $this->prepare_emails_array($to)
            ),
        );

        //add attachment
        $attachments = get_array_value($optoins, "attachments");
        if (is_array($attachments)) {
            $email["message"]["attachments"] = $this->generate_attachments_array($attachments);
        }

        //check reply-to
        $reply_to = get_array_value($optoins, "reply_to");
        if ($reply_to) {
            $email["message"]["replyTo"] = $this->prepare_emails_array($reply_to);
        }

        //check cc
        $cc = get_array_value($optoins, "cc");
        if ($cc) {
            $email["message"]["ccRecipients"] = $this->prepare_emails_array($cc);
        }

        //check bcc
        $bcc = get_array_value($optoins, "bcc");
        if ($bcc) {
            $email["message"]["bccRecipients"] = $this->prepare_emails_array($bcc);
        }

        $this->do_request("POST", "sendMail", $email);

        return true;
    }

    private function generate_attachments_array($attachments) {
        $attachments_array = array();

        foreach ($attachments as $value) {
            $file_path = get_array_value($value, "file_path");
            $file_name = get_array_value($value, "file_name");
            $file_path = trim($file_path);

            if (strpos($file_path, '://') === false && !is_file($file_path)) {
                log_message('error', lang('Email.attachmentMissing', [$file_path]));
                continue;
            }

            if (!$fp = @fopen($file_path, 'rb')) {
                log_message('error', lang('Email.attachmentUnreadable', [$file_path]));
                continue;
            }

            $fileContent = stream_get_contents($fp);

            $mime = $this->mimeTypes(pathinfo($file_path, PATHINFO_EXTENSION));

            fclose($fp);

            if (!$file_name) {
                $file_path = explode("/", $file_path);
                $file_name = end($file_path);
            }

            $attachments_array[] = array(
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'name' => $file_name,
                'contentType' => $mime,
                'contentBytes' => chunk_split(base64_encode($fileContent))
            );
        }

        return $attachments_array;
    }

    private function prepare_emails_array($emails = "") {
        $emails = $this->stringToArray($emails);
        $emails = $this->cleanEmail($emails);
        $this->validateEmail($emails);

        $emails_array = array();
        foreach ($emails as $email) {
            $emails_array[] = array(
                "emailAddress" => array(
                    "address" => $email
                )
            );
        }

        return $emails_array;
    }

    /**
     * Byte-safe substr()
     *
     * @param string   $str
     * @param int      $start
     * @param int|null $length
     *
     * @return string
     */
    protected static function substr($str, $start, $length = null) {
        if (static::$func_overload) {
            return mb_substr($str, $start, $length, '8bit');
        }

        return isset($length) ? substr($str, $start, $length) : substr($str, $start);
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    public function isValidEmail($email) {
        if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46') && $atpos = strpos($email, '@')) {
            $email = static::substr($email, 0, ++$atpos)
                    . idn_to_ascii(static::substr($email, $atpos), 0, INTL_IDNA_VARIANT_UTS46);
        }

        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param array|string $email
     *
     * @return bool
     */
    public function validateEmail($email) {
        if (!is_array($email)) {
            log_message('error', lang('Email.mustBeArray'));

            return false;
        }

        foreach ($email as $val) {
            if (!$this->isValidEmail($val)) {
                log_message('error', lang('Email.invalidAddress', [$val]));
                return false;
            }
        }

        return true;
    }

    /**
     * @param array|string $email
     *
     * @return array|string
     */
    public function cleanEmail($email) {
        if (!is_array($email)) {
            return preg_match('/\<(.*)\>/', $email, $match) ? $match[1] : $email;
        }

        $cleanEmail = [];

        foreach ($email as $addy) {
            $cleanEmail[] = preg_match('/\<(.*)\>/', $addy, $match) ? $match[1] : $addy;
        }

        return $cleanEmail;
    }

    /**
     * @param string $email
     *
     * @return array
     */
    protected function stringToArray($email) {
        if (!is_array($email)) {
            return (strpos($email, ',') !== false) ? preg_split('/[\s,]/', $email, -1, PREG_SPLIT_NO_EMPTY) :
                    (array) trim($email);
        }

        return $email;
    }

    /**
     * Mime Types
     *
     * @param string $ext
     *
     * @return string
     */
    protected function mimeTypes($ext = '') {
        $mime = Mimes::guessTypeFromExtension(strtolower($ext));

        return !empty($mime) ? $mime : 'application/x-unknown-content-type';
    }

}
