<?php

/* Don't change or add any new config in this file */

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Rise extends BaseConfig {

    public $app_settings_array = array(
        "app_version" => "3.4.1",
        "app_update_url" => 'https://releases.fairsketch.com/rise/',
        "updates_path" => './updates/',
    );
    public $app_csrf_exclude_uris = array(
        "notification_processor/create_notification",
        "paypal_redirect", "paypal_redirect/index",
        "paytm_redirect", "paytm_redirect/index", "paytm_redirect.*+",
        "stripe_redirect", "stripe_redirect/index",
        "pay_invoice", "pay_invoice/*",
        "google_api/save_access_token", "google_api/save_access_token_of_calendar", "google_api/save_access_token_of_own_calendar",
        "webhooks_listener.*+",
        "external_tickets.*+",
        "collect_leads.*+",
        "upload_pasted_image.*+",
        "request_estimate.*+",
        "events/snooze_reminder", "events/reminder_view", "events/save_reminder_status",
        "cron",
        "notifications/count_notifications",
        "messages/count_notifications",
    );

    public function __construct() {
        $this->app_csrf_exclude_uris = app_hooks()->apply_filters('app_filter_app_csrf_exclude_uris', $this->app_csrf_exclude_uris);
    }

}
