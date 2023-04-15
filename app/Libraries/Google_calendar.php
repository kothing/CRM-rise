<?php

namespace App\Libraries;

use App\Controllers\App_Controller;

class Google_calendar {

    private $ci;

    public function __construct() {
        $this->ci = new App_Controller();

        //load resources
        require_once(APPPATH . "ThirdParty/Google/google-api-php-client/vendor/autoload.php");
    }

    //authorize connection
    public function authorize() {
        $client = $this->_get_client_credentials();
        $this->_check_access_token($client, true);
    }

    //check access token
    private function _check_access_token($client, $redirect_to_settings = false) {
        //load previously authorized token from database, if it exists.
        $accessToken = get_setting('google_calendar_oauth_access_token');

        if ($accessToken && get_setting('google_calendar_authorized')) {
            $client->setAccessToken(json_decode($accessToken, true));
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                if ($redirect_to_settings) {
                    app_redirect("settings/events");
                }
            } else {
                $authUrl = $client->createAuthUrl();
                app_redirect($authUrl, true);
            }
        } else {
            if ($redirect_to_settings) {
                app_redirect("settings/events");
            }
        }
    }

    //fetch access token with auth code and save to database
    public function save_access_token($auth_code) {
        $client = $this->_get_client_credentials();

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($auth_code);

        $error = get_array_value($accessToken, "error");
        if ($error)
            die($error);

        $client->setAccessToken($accessToken);

        // Save the token to database
        $new_access_token = json_encode($client->getAccessToken());

        if ($new_access_token) {
            $this->ci->Settings_model->save_setting('google_calendar_oauth_access_token', $new_access_token);

            //got the valid access token. store to setting that it's authorized
            $this->ci->Settings_model->save_setting('google_calendar_authorized', "1");
        }
    }

    //get client credentials
    private function _get_client_credentials() {
        $url = get_uri("google_api/save_access_token_of_calendar");

        $client = new \Google_Client();
        $client->setApplicationName(get_setting('app_title'));
        $client->setRedirectUri($url);
        $client->setAccessType("offline");
        $client->setPrompt('select_account consent');
        $client->setClientId(get_setting('google_calendar_client_id'));
        $client->setClientSecret(get_setting('google_calendar_client_secret'));
        $client->setScopes(\Google_Service_Calendar::CALENDAR);

        return $client;
    }

}
