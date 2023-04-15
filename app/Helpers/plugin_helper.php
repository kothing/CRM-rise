<?php

if (!function_exists('app_hooks')) {

    function app_hooks() {
        global $hooks;
        return $hooks;
    }

}

if (!function_exists('get_plugin_meta_data')) {

    function get_plugin_meta_data($plugin_name = "") {
        $plugin_info_array = array();

        if (!file_exists(PLUGINPATH . $plugin_name . "/index.php")) {
            return $plugin_info_array;
        }

        $plugin_index_file_contents = file_get_contents(PLUGINPATH . $plugin_name . "/index.php");

        preg_match('|Plugin Name:(.*)$|mi', $plugin_index_file_contents, $plugin_name);
        preg_match('|Plugin URL:(.*)$|mi', $plugin_index_file_contents, $plugin_url);
        preg_match('|Description:(.*)$|mi', $plugin_index_file_contents, $description);
        preg_match('|Version:(.*)|i', $plugin_index_file_contents, $version);
        preg_match('|Requires at least:(.*)$|mi', $plugin_index_file_contents, $requires_at_least);
        preg_match('|Author:(.*)$|mi', $plugin_index_file_contents, $author);
        preg_match('|Author URL:(.*)$|mi', $plugin_index_file_contents, $author_url);

        if (isset($plugin_name[1])) {
            $plugin_info_array['plugin_name'] = trim($plugin_name[1]);
        }

        if (isset($plugin_url[1])) {
            $plugin_info_array['plugin_url'] = trim($plugin_url[1]);
        }

        if (isset($description[1])) {
            $plugin_info_array['description'] = trim($description[1]);
        }

        if (isset($version[1])) {
            $plugin_info_array['version'] = trim($version[1]);
        } else {
            $plugin_info_array['version'] = 0;
        }

        if (isset($requires_at_least[1])) {
            $plugin_info_array['requires_at_least'] = trim($requires_at_least[1]);
        }

        if (isset($author[1])) {
            $plugin_info_array['author'] = trim($author[1]);
        }

        if (isset($author_url[1])) {
            $plugin_info_array['author_url'] = trim($author_url[1]);
        }

        return $plugin_info_array;
    }

}

if (!function_exists('register_installation_hook')) {

    function register_installation_hook($plugin_name, $function) {
        app_hooks()->add_action("app_hook_install_plugin_$plugin_name", $function);
    }

}

if (!function_exists('register_uninstallation_hook')) {

    function register_uninstallation_hook($plugin_name, $function) {
        app_hooks()->add_action("app_hook_uninstall_plugin_$plugin_name", $function);
    }

}

if (!function_exists('register_activation_hook')) {

    function register_activation_hook($plugin_name, $function) {

        app_hooks()->add_action("app_hook_activate_plugin_$plugin_name", $function);
    }

}

if (!function_exists('register_deactivation_hook')) {

    function register_deactivation_hook($plugin_name, $function) {
        app_hooks()->add_action("app_hook_deactivate_plugin_$plugin_name", $function);
    }

}

if (!function_exists('is_unsupported_plugin')) {

    function is_unsupported_plugin($plugin_name) {
        $plugin_info = get_plugin_meta_data($plugin_name);
        $app_version = get_setting("app_version");
        $error = "";

        if (get_array_value($plugin_info, "requires_at_least") && ($app_version < get_array_value($plugin_info, "requires_at_least"))) {
            $error = sprintf(app_lang("plugin_requires_at_least_error_message"), get_array_value($plugin_info, "requires_at_least"));
        }

        return $error;
    }

}

//save activated plugins to a config file as data
if (!function_exists('save_plugins_config')) {

    function save_plugins_config($plugins = array()) {
        $activated_plugins = array();
        foreach ($plugins as $plugin => $status) {
            if ($status === "activated") {
                array_push($activated_plugins, $plugin);
            }
        }

        $contents = json_encode($activated_plugins);
        file_put_contents(APPPATH . "Config/activated_plugins.json", $contents);
    }

}

if (!function_exists('register_update_hook')) {

    function register_update_hook($plugin_name, $function) {
        app_hooks()->add_action("app_hook_update_plugin_$plugin_name", $function);
    }

}

if (!function_exists('register_data_insert_hook')) {

    function register_data_insert_hook($function) {
        app_hooks()->add_action("app_hook_data_insert", $function);
    }

}

if (!function_exists('register_data_update_hook')) {

    function register_data_update_hook($function) {
        app_hooks()->add_action("app_hook_data_update", $function);
    }

}

if (!function_exists('register_data_delete_hook')) {

    function register_data_delete_hook($function) {
        app_hooks()->add_action("app_hook_data_delete", $function);
    }

}
