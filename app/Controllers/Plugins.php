<?php

namespace App\Controllers;

class Plugins extends Security_Controller {

    function __construct() {
        parent::__construct();
        $this->access_only_admin();
    }

    //load plugin list view
    function index() {
        return $this->template->rander("plugins/index");
    }

    //load plugin upload modal form
    function modal_form() {
        return $this->template->view('plugins/modal_form');
    }

    /* upload a post file */

    function upload_file() {
        upload_file_to_temp(true);
    }

    /* check valid file for plugin */

    function validate_plugin_file() {
        $file_name = $this->request->getPost("file_name");
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!is_valid_file_to_upload($file_name)) {
            echo json_encode(array("success" => false, 'message' => app_lang('invalid_file_type')));
            exit();
        }

        if ($file_ext == "zip") {
            echo json_encode(array("success" => true));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('please_upload_a_zip_file') . " (.zip)"));
        }
    }

    //install plugin
    function save() {
        $this->validate_submitted_data(array(
            "file_name" => "required"
        ));

        $temp_file_path = get_setting("temp_file_path");
        $file_name = $this->request->getPost("file_name");
        $plugin_zip_file = $temp_file_path . $file_name;
        $plugin_name = "";

        if (!class_exists('ZipArchive')) {
            echo json_encode(array("success" => false, 'message' => "Please install the ZipArchive package in your server."));
            exit();
        }

        if (!file_exists($plugin_zip_file)) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            exit();
        }

        $zip = new \ZipArchive;
        $zip->open($plugin_zip_file);

        //the index.php is required
        $has_index_file = false;

        //extract zip
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $file_info_array = $zip->statIndex($i);
            $file_name = get_array_value($file_info_array, "name");
            $dir = dirname($file_name);

            if (!$plugin_name) {
                //first folder should be the plugin name
                $plugin_name = explode('/', $file_name);
                $plugin_name = get_array_value($plugin_name, 0);

                if ($this->this_plugin_exists($plugin_name)) {
                    //this plugin is already installed
                    echo json_encode(array("success" => false, 'message' => app_lang("this_plugin_is_already_installed")));
                    exit();
                }
            }

            if (substr($file_name, -1, 1) == '/') {
                continue;
            }

            //create new directory if it's not exists
            if (!is_dir(PLUGINPATH . $dir)) {
                mkdir(PLUGINPATH . $dir, 0755, true);
            }

            //overwrite the existing file
            if (!is_dir(PLUGINPATH . $file_name)) {
                $contents = $zip->getFromIndex($i);

                if ($file_name == $plugin_name . '/index.php') {
                    $has_index_file = true;
                }

                file_put_contents(PLUGINPATH . $file_name, $contents);
            }
        }

        if (!($has_index_file && $plugin_name)) {
            //required files are missing
            echo json_encode(array("success" => false, 'message' => app_lang("the_required_files_missing")));
            exit();
        }

        $this->save_status_of_plugin($plugin_name, "installed");

        $zip->close(); //unset zip extraction variable to delete temp file
        delete_file_from_directory($plugin_zip_file); //delete temp file

        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }

    private function get_plugins_array($include_directories = false) {
        $plugins = get_setting("plugins");
        $plugins = @unserialize($plugins);
        if (!($plugins && is_array($plugins))) {
            $plugins = array();
        }

        //get indexed folders
        if ($include_directories && is_dir(PLUGINPATH)) {
            if ($dh = opendir(PLUGINPATH)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file && $file != "." && $file != ".." && $file != "index.html" && $file != ".gitkeep" && !array_key_exists($file, $plugins)) {
                        $plugins[$file] = "indexed";
                    }
                }
                closedir($dh);
            }
        }

        return $plugins;
    }

    private function this_plugin_exists($plugin_name = "") {
        $plugins = $this->get_plugins_array();
        if (array_key_exists($plugin_name, $plugins)) {
            return true;
        }
    }

    //save status of plugin
    function save_status_of_plugin($plugin_name = "", $status = "", $echo_json = false) {
        if (!($status === "installed" || $status === "activated" || $status === "deactivated")) {
            show_404();
        }

        $plugins = $this->get_plugins_array();

        if ($status === "installed") {
            if (!file_exists(PLUGINPATH . $plugin_name . "/index.php")) {
                //required files are missing
                echo json_encode(array("success" => false, 'message' => app_lang("the_required_files_missing")));
                exit();
            }

            if ($this->this_plugin_exists($plugin_name)) {
                //this plugin is already installed
                echo json_encode(array("success" => false, 'message' => app_lang("this_plugin_is_already_installed")));
                exit();
            }

            //install plugin 
            $this->install_plugin($plugin_name);
        } else if ($status === "activated") {
            $unsupported_error = is_unsupported_plugin($plugin_name);
            if ($unsupported_error) {
                echo json_encode(array("success" => false, 'message' => $unsupported_error));
                exit();
            }

            //since this plugin isn't activated, the index file won't be loaded
            //that's why, load it's index file to register activation hook
            if (file_exists(PLUGINPATH . $plugin_name . "/index.php")) {
                include (PLUGINPATH . $plugin_name . "/index.php");
            }

            app_hooks()->do_action("app_hook_activate_plugin_$plugin_name");
        } else if ($status === "deactivated") {
            app_hooks()->do_action("app_hook_deactivate_plugin_$plugin_name");
        }

        $plugins[$plugin_name] = $status;
        save_plugins_config($plugins);

        $this->Settings_model->save_setting("plugins", serialize($plugins));

        if ($echo_json) {
            echo json_encode(array("success" => true));
        }
    }

    //install plugin
    private function install_plugin($plugin_name = "") {
        $unsupported_error = is_unsupported_plugin($plugin_name);
        if ($unsupported_error) {
            echo json_encode(array("success" => false, 'message' => $unsupported_error));
            exit();
        }

        include (PLUGINPATH . $plugin_name . '/index.php');

        //call plugin installation hook
        $item_purchase_code = $this->request->getPost("file_description");
        app_hooks()->do_action("app_hook_install_plugin_$plugin_name", $item_purchase_code);
    }

    //delete/undo a plugin
    function delete($plugin_name = "") {
        if (!$plugin_name) {
            show_404();
        }

        $plugins = $this->get_plugins_array();
        $plugin_folder = PLUGINPATH . $plugin_name;
        if (!is_dir($plugin_folder)) {
            //no accurate directory found
            show_404();
        }

        if (array_key_exists($plugin_name, $plugins)) {
            //this is not on indexed state, means installed before
            $plugin_index_file = PLUGINPATH . $plugin_name . '/index.php';
            if (!file_exists($plugin_index_file)) {
                show_404();
            }

            //call uninstallation hook
            include ($plugin_index_file);

            //call plugin uninstallation hook
            app_hooks()->do_action("app_hook_uninstall_plugin_$plugin_name");
        }

        //delete files
        helper("filesystem");
        if (!delete_files($plugin_folder, true, false, true)) {
            show_404();
        }

        //delete empty folder
        rmdir($plugin_folder);

        //save plugins
        if (array_key_exists($plugin_name, $plugins)) {
            unset($plugins[$plugin_name]);
            $this->Settings_model->save_setting("plugins", serialize($plugins));
        }

        echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
    }

    //get data for plugins plugin list
    function list_data() {
        $result = array();

        $plugins = $this->get_plugins_array(true);
        foreach ($plugins as $plugin => $status) {
            $result[] = $this->_make_row($plugin, $status);
        }

        echo json_encode(array("data" => $result));
    }

    //prepare an plugin list row
    //indexed, installed, activated, deactivated
    private function _make_row($plugin, $status) {
        $main_plugin_name = $plugin;
        $plugin_info = get_plugin_meta_data($plugin);

        //status: installed
        $action_type = "activated";
        $icon = "play";
        $status_class = "bg-warning";
        $lang_key = "activate";

        if ($status === "indexed") {
            $action_type = "installed";
            $lang_key = "install";
            $icon = "download";
            $status_class = "bg-secondary";
        } else if ($status === "activated") {
            $action_type = "deactivated";
            $lang_key = "deactivate";
            $icon = "pause";
            $status_class = "bg-success";
        } else if ($status === "deactivated") {
            $status_class = "bg-danger";
        }

        $action = '<li role="presentation">' . ajax_anchor(get_uri("plugins/save_status_of_plugin/$plugin/$action_type/1"), "<i data-feather='$icon' class='icon-16'></i> " . app_lang($lang_key), array("data-reload-on-success" => true, "class" => "dropdown-item", "data-show-response" => true)) . '</li>';

        $update = "";
        if ($status === "activated") {
            $update = '<li role="presentation">' . modal_anchor(get_uri("plugins/updates/$plugin"), "<i data-feather='refresh-cw' class='icon-16'></i> " . app_lang('updates'), array("title" => app_lang('updates'), "class" => "dropdown-item")) . '</li>';
        }

        $delete = "";
        if ($status !== "activated") {
            $delete = '<li role="presentation">' . js_anchor("<i data-feather='x' class='icon-16'></i>" . app_lang('delete'), array('title' => app_lang('delete'), "class" => "delete dropdown-item", "data-action-url" => get_uri("plugins/delete/$plugin"), "data-action" => "delete-confirmation", "data-reload-on-success" => true)) . '</li>';
        }

        $option = '
                <span class="dropdown inline-block">
                    <button class="btn btn-default dropdown-toggle caret mt0 mb0" type="button" data-bs-toggle="dropdown" aria-expanded="true" data-bs-display="static">
                        <i data-feather="tool" class="icon-16"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" role="menu">' . $action . $update . $delete . '</ul>
                </span>';

        $plugin = "<b>" . (get_array_value($plugin_info, "plugin_name") ? get_array_value($plugin_info, "plugin_name") : $plugin) . "</b>";

        $action_links = "";
        $action_links_array = app_hooks()->apply_filters("app_filter_action_links_of_$main_plugin_name", array());
        if ($action_links_array && is_array($action_links_array)) {
            $action_links = "<br />";

            foreach ($action_links_array as $action_link) {
                if ($action_links === "<br />") {
                    $action_links .= $action_link;
                } else {
                    $action_links .= " | ";
                    $action_links .= $action_link;
                }
            }
        }

        if (get_array_value($plugin_info, "version")) {
            $plugin .= "<br />" . "<small>" . app_lang("version") . " " . get_array_value($plugin_info, "version") . "</small>";
        } else {
            $plugin .= "<br />";
        }

        $plugin .= "<small>" . $action_links . "</small>";

        return array(
            $plugin,
            $this->prepare_plugin_description($plugin_info),
            "<span class='mt0 badge $status_class large'>" . app_lang($status) . "</span>",
            $option
        );
    }

    private function prepare_plugin_description($plugin_info) {
        $description = get_array_value($plugin_info, "description");
        $other_desc = "";

        $author = get_array_value($plugin_info, "author");
        $author_url = get_array_value($plugin_info, "author_url");
        $plugin_url = get_array_value($plugin_info, "plugin_url");

        if ($author) {
            if ($author_url) {
                $other_desc .= app_lang("by") . " " . anchor($author_url, $author, array("target" => "_blank"));
            } else {
                $other_desc .= app_lang("by") . " " . $author;
            }
        }

        if ($plugin_url) {
            if ($other_desc) {
                $other_desc .= " | ";
            }

            $other_desc .= anchor($plugin_url, app_lang("visit_plugin_site"), array("target" => "_blank"));
        }

        if ($other_desc) {
            $other_desc = "<br />" . "<small>" . $other_desc . "</small>";
        }

        $description .= $other_desc;

        return $description;
    }

    function updates($plugin_name = "") {
        $plugins = $this->get_plugins_array();
        if ($plugins[$plugin_name] !== "activated") {
            show_404();
        }

        if (app_hooks()->has_action("app_hook_update_plugin_$plugin_name")) {
            app_hooks()->do_action("app_hook_update_plugin_$plugin_name");
        } else {
            return $this->template->view('plugins/no_hook_modal');
        }
    }

}

/* End of file plugins.php */
/* Location: ./app/controllers/plugins.php */