<?php

namespace App\Controllers;

class Dashboard extends Security_Controller {

    private $show_staff_on_staff = true;
    protected $Custom_widgets_model;

    function __construct() {
        parent::__construct();
        $this->Custom_widgets_model = model('App\Models\Custom_widgets_model');
    }

    public function index() {

        $view_data["dashboards"] = array();

        $dashboards = $this->Dashboards_model->get_details(array("user_id" => $this->login_user->id));

        if ($dashboards) {
            $view_data["dashboards"] = $dashboards->getResult();
        }

        $view_data["dashboard_type"] = "default";

        if ($this->login_user->user_type === "staff" && $this->show_staff_on_staff) {
            //admin or team member dashboard
            $staff_default_dashboard = get_setting("staff_default_dashboard");
            if ($staff_default_dashboard) {
                return $this->view($staff_default_dashboard);
            }

            $view_data["widget_columns"] = $this->make_dashboard($this->_get_admin_and_team_dashboard_data());
            $view_data["dashboard_id"] = 0;

            $this->Settings_model->save_setting("user_" . $this->login_user->id . "_dashboard", "", "user");
            return $this->template->rander("dashboards/custom_dashboards/view", $view_data);
        } else {
            // client dashboard
            $widgets = $this->_check_widgets_permissions();

            $client_default_dashboard = get_setting("client_default_dashboard");
            if ($client_default_dashboard) {
                $view_data["widget_columns"] = $this->make_dashboard(unserialize($client_default_dashboard));

                echo $this->template->rander("dashboards/custom_dashboards/view", $view_data);
            } else {
                $view_data['show_invoice_info'] = get_array_value($widgets, "show_invoice_info");
                $view_data["show_project_info"] = true; //client can view projects
                $view_data['hidden_menu'] = get_array_value($widgets, "hidden_menu");
                $view_data['client_info'] = get_array_value($widgets, "client_info");
                $view_data['client_id'] = get_array_value($widgets, "client_id");
                $view_data['page_type'] = get_array_value($widgets, "page_type");
                $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("projects", $this->login_user->is_admin, $this->login_user->user_type);
                $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("projects", $this->login_user->is_admin, $this->login_user->user_type);

                echo $this->template->rander("dashboards/client_dashboard", $view_data);
            }
        }

        $this->Settings_model->save_setting("user_" . $this->login_user->id . "_dashboard", "", "user");
    }

    private function _check_widgets_permissions() {
        if ($this->login_user->user_type === "staff" && $this->show_staff_on_staff) {
            $widgets = $this->_check_widgets_for_staffs();
        } else {
            $widgets = $this->_check_widgets_for_clients();
        }

        $plugin_widgets = array();
        $plugin_widgets = app_hooks()->apply_filters('app_filter_dashboard_widgets', $plugin_widgets);
        if ($plugin_widgets && is_array($plugin_widgets)) {
            foreach ($plugin_widgets as $plugin_widget) {
                if (is_array($plugin_widget)) {
                    $widgets[get_array_value($plugin_widget, "widget")] = true;
                }
            }
        }

        return $widgets;
    }

    private function _check_widgets_for_staffs() {
        //check which widgets are viewable to current logged in user
        $widget = array();

        $show_attendance = get_setting("module_attendance");
        $show_invoice = get_setting("module_invoice");
        $show_expense = get_setting("module_expense");
        $show_ticket = get_setting("module_ticket");
        $show_events = get_setting("module_event");
        $show_message = get_setting("module_message");
        $show_leave = get_setting("module_leave");
        $show_announcement = get_setting("module_announcement");
        $show_estimate = get_setting("module_estimate");
        $show_timesheet = get_setting("module_project_timesheet");
        $show_lead = get_setting("module_lead");

        $access_expense = $this->get_access_info("expense");
        $access_invoice = $this->get_access_info("invoice");
        $access_ticket = $this->get_access_info("ticket");
        $access_timecards = $this->get_access_info("attendance");
        $access_timesheets = $this->get_access_info("timesheet_manage_permission");
        $access_client = $this->get_access_info("client");
        $access_leads = $this->get_access_info("lead");
        $access_estiamtes = $this->get_access_info("estiamte");

        $widget["new_posts"] = get_setting("module_timeline");

        if ($show_attendance) {
            $widget["clock_in_out"] = true;
            $widget["timecard_statistics"] = true;
        }

        if ($show_events) {
            $widget["events_today"] = true;
            $widget["events"] = true;
        }

        if (get_setting("module_todo")) {
            $widget["todo_list"] = true;
        }

        //check module availability and access permission to show any widget

        if ($show_invoice && $show_expense && $access_expense->access_type === "all" && $this->can_view_invoices()) {
            $widget["income_vs_expenses"] = true;
            $widget["total_due"] = true;
        }

        if ($show_invoice && $this->can_view_invoices()) {
            $widget["invoice_statistics"] = true;
        }

        if ($show_ticket && $access_ticket->access_type) {
            $widget["ticket_status"] = true;
        }

        if ($show_attendance && ($access_timecards->access_type === "all" || $access_timecards->allowed_members)) {
            $widget["clock_status"] = true;
            $widget["members_clocked_in"] = true;
            $widget["members_clocked_out"] = true;
        }

        if ($show_ticket && ($this->login_user->is_admin || $access_ticket->access_type)) {
            $widget["new_tickets"] = true;
            $widget["open_tickets"] = true;
            $widget["closed_tickets"] = true;
            $widget["open_tickets_list"] = true;
        }

        if ($this->can_view_team_members_list()) {
            $widget["all_team_members"] = true;
        }

        if ($this->can_view_team_members_list() && $show_attendance && ($access_timecards->access_type === "all" || $access_timecards->allowed_members)) {
            $widget["clocked_in_team_members"] = true;
            $widget["clocked_out_team_members"] = true;
        }

        if ($this->can_view_team_members_list()) {
            $widget["latest_online_team_members"] = true;
        }

        if ($this->login_user->is_admin || $access_client->access_type) {
            $widget["latest_online_client_contacts"] = true;
        }

        if ($show_invoice && $this->can_view_invoices()) {
            $widget["total_invoices"] = true;
            $widget["total_payments"] = true;
            $widget["draft_invoices_value"] = true;
            $widget["invoice_overview"] = true;
        }

        if ($show_expense && $show_invoice && $this->can_view_invoices()) {
            $widget["total_due"] = true;
        }

        if ($show_timesheet && $this->login_user->is_admin) {
            $widget["all_timesheets_statistics"] = true;
        }

        if ($show_leave) {
            $widget["pending_leave_approval"] = true;
        }

        if ($this->can_manage_all_projects() && !$this->has_all_projects_restricted_role()) {
            $widget["open_projects"] = true;
            $widget["completed_projects"] = true;
        }

        if (get_setting("module_attendance") == "1" && ($this->login_user->is_admin || $access_timecards->access_type)) {
            $widget["total_hours_worked"] = true;
        }

        if (get_setting("module_project_timesheet") == "1" && ($this->login_user->is_admin || ($access_timesheets->access_type && !$this->has_all_projects_restricted_role()))) {
            $widget["total_project_hours"] = true;
        }

        if ($this->login_user->is_admin || (get_array_value($this->login_user->permissions, "can_manage_all_projects") === "1" && !$this->has_all_projects_restricted_role())) {
            $widget["active_members_on_projects"] = true;
        }

        if ($show_invoice && $this->can_view_invoices()) {
            $widget["draft_invoices"] = true;
        }

        if ($this->login_user->is_admin || $access_client->access_type) {
            $widget["total_clients"] = true;
            $widget["total_contacts"] = true;
        }

        if ($show_lead && ($this->login_user->is_admin || $access_leads->access_type)) {
            $widget["total_leads"] = true;
            $widget["leads_overview"] = true;
        }

        if ($show_estimate && ($this->login_user->is_admin || $access_estiamtes->access_type)) {
            $widget["estimate_sent_statistics"] = true;
        }

        if ($this->can_view_team_members_list() && $show_attendance && ($access_timecards->access_type === "all" || $access_timecards->allowed_members) && $show_leave) {
            $widget["team_members_overview"] = true;
        }

        if (can_access_reminders_module()) {
            $widget["next_reminder"] = true;
        }

        if (!$this->has_all_projects_restricted_role()) {
            $widget["my_timesheet_statistics"] = get_setting("module_project_timesheet");
            $widget["open_projects_list"] = true;
            $widget["project_timeline"] = true;
            $widget["starred_projects"] = true;
            $widget["my_tasks_list"] = true;
            $widget["my_open_tasks"] = true;
            $widget["task_status"] = true;
            $widget["all_tasks_kanban"] = true;
            $widget["projects_overview"] = true;
            $widget["all_tasks_overview"] = true;
            $widget["my_tasks_overview"] = true;
        }

        if ($show_announcement) {
            $widget["last_announcement"] = true;
        }

        //universal widgets
        $widget["sticky_note"] = true;

        return $widget;
    }

    private function _check_widgets_for_clients() {
        //check widgets permission for client users

        $widget = array();

        $options = array("id" => $this->login_user->client_id);
        $client_info = $this->Clients_model->get_details($options)->getRow();
        $hidden_menu = explode(",", get_setting("hidden_client_menus"));

        $show_invoice_info = get_setting("module_invoice");
        $show_events = get_setting("module_event");

        $widget['show_invoice_info'] = $show_invoice_info;
        $widget['hidden_menu'] = $hidden_menu;
        $widget['client_info'] = $client_info;

        if (is_object($client_info) && property_exists($client_info, "id")) {
            $widget['client_id'] = $client_info->id;
        } else {
            $widget['client_id'] = 0;
        }

        $widget['page_type'] = "dashboard";

        if ($show_invoice_info) {
            if (!in_array("projects", $hidden_menu)) {
                $widget["total_projects"] = true;
            }
            if (!in_array("invoices", $hidden_menu)) {
                $widget["total_invoices"] = true;
            }
            if (!in_array("payments", $hidden_menu)) {
                $widget["total_payments"] = true;
                $widget["total_due"] = true;
            }
        }

        if (!in_array("projects", $hidden_menu)) {
            $widget["open_projects_list"] = true;
        }

        if (get_setting("client_can_view_activity") && get_setting("client_can_view_overview")) {
            $widget["project_timeline"] = true;
        }

        if ($show_events && !in_array("events", $hidden_menu)) {
            $widget["events"] = true;
        }

        if ($show_invoice_info && !in_array("invoices", $hidden_menu)) {
            $widget["invoice_statistics"] = true;
        }

        if ($show_events && !in_array("events", $hidden_menu)) {
            $widget["events_today"] = true;
        }

        if (get_setting("module_todo")) {
            $widget["todo_list"] = true;
        }

        if (!in_array("tickets", $hidden_menu) && get_setting("module_ticket") && $this->access_only_allowed_members_or_client_contact($this->login_user->client_id)) {
            $widget["new_tickets"] = true;
            $widget["open_tickets"] = true;
            $widget["closed_tickets"] = true;
            $widget["open_tickets_list"] = true;
        }

        if (get_setting("module_announcement")) {
            $widget["last_announcement"] = true;
        }

        //universal widgets
        $widget["sticky_note"] = true;

        return $widget;
    }

    public function save_sticky_note() {
        $note_data = array("sticky_note" => $this->request->getPost("sticky_note"));
        $this->Users_model->ci_save($note_data, $this->login_user->id);
    }

    function modal_form($id = 0) {
        $view_data['model_info'] = $this->Dashboards_model->get_one($id);
        return $this->template->view("dashboards/custom_dashboards/modal_form", $view_data);
    }

    function custom_widget_modal_form($id = 0) {
        $view_data['model_info'] = $this->Custom_widgets_model->get_one($id);
        return $this->template->view("dashboards/custom_widgets/modal_form", $view_data);
    }

    function save_custom_widget() {
        $id = $this->request->getPost("id");

        if ($id) {
            $custom_widget_info = $this->_get_my_custom_widget($id);
            if (!$custom_widget_info) {
                app_redirect("forbidden");
            }
        }

        $data = array(
            "user_id" => $this->login_user->id,
            "title" => $this->request->getPost("title"),
            "content" => $this->request->getPost("content"),
            "show_title" => is_null($this->request->getPost("show_title")) ? "" : $this->request->getPost("show_title"),
            "show_border" => is_null($this->request->getPost("show_border")) ? "" : $this->request->getPost("show_border")
        );

        $save_id = $this->Custom_widgets_model->ci_save($data, $id);

        if ($save_id) {
            $custom_widgets_info = $this->Custom_widgets_model->get_one($save_id);

            $custom_widgets_data = array(
                $custom_widgets_info->id => $custom_widgets_info->title
            );

            echo json_encode(array("success" => true, "id" => $save_id, "custom_widgets_row" => $this->_make_widgets_row($custom_widgets_data), "custom_widgets_data" => $this->_widgets_row_data($custom_widgets_data), 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function show_my_dashboards() {
        $view_data["dashboards"] = $this->Dashboards_model->get_details(array("user_id" => $this->login_user->id))->getResult();
        return $this->template->view('dashboards/list/dashboards_list', $view_data);
    }

    function view($id = 0) {

        validate_numeric_value($id);

        $selected_dashboard_id = get_setting("user_" . $this->login_user->id . "_dashboard");
        if (!$id) {
            $id = $selected_dashboard_id;
        }

        $dashboard_info = $this->_get_my_dashboard($id, $this->is_staff_dashboard($id));

        if ($dashboard_info) {
            if (get_setting("disable_dashboard_customization_by_clients") && $this->login_user->user_type == "client") {
                app_redirect("forbidden");
            }

            $user_selected_dashboard = $dashboard_info->id;
            if ($this->is_staff_dashboard($id)) {
                $user_selected_dashboard = "";
            }

            $this->Settings_model->save_setting("user_" . $this->login_user->id . "_dashboard", $user_selected_dashboard, "user");

            $view_data["dashboard_info"] = $dashboard_info;
            $view_data["widget_columns"] = $this->make_dashboard(unserialize($dashboard_info->data));

            $view_data["dashboards"] = $this->Dashboards_model->get_details(array("user_id" => $this->login_user->id))->getResult();
            $view_data["dashboard_type"] = "custom";
            $view_data["dashboard_id"] = $id;

            return $this->template->rander("dashboards/custom_dashboards/view", $view_data);
        } else {
            app_redirect("dashboard"); //no dashbord selected. go to default dashboard  
        }
    }

    private function _convert_widgets_array_to_formated_obj($row_widgets = array()) {
        $dashboard_data = array();

        foreach ($row_widgets as $widgets) {

            $columns_array = get_array_value($widgets, "columns");
            $ratio = get_array_value($widgets, "ratio");
            $widget_obj = new \stdClass();

            $columns = array();
            foreach ($columns_array as $column) {
                $column_rows = array();
                foreach ($column as $widget) {
                    $inner_widget = new \stdClass();
                    $inner_widget->widget = $widget;
                    $column_rows[] = $inner_widget;
                }
                $columns[] = $column_rows;
            }

            $widget_obj->columns = $columns;
            $widget_obj->ratio = $ratio;

            $dashboard_data[] = $widget_obj;
        }
        return $dashboard_data;
    }

    private function _get_admin_and_team_dashboard_widgets() {

        $widgets = $this->_check_widgets_permissions();
        $first_row = $this->_get_first_row_of_admin_and_team_dashboard($widgets);

        $row_columns = $this->_get_second_and_third_row_of_admin_and_team_dashboard_widget_columns($widgets);
        $second_row = $this->_get_second_row_of_admin_and_team_dashboard($row_columns);
        $third_row = $this->_get_third_row_of_admin_and_team_dashboard($row_columns);

        $fourth_row = $this->_get_fourth_row_of_admin_and_team_dashboard($widgets);
        $fifth_row = $this->_get_fifth_row_of_admin_and_team_dashboard($widgets);

        $row_widgets = array(
            $first_row,
            $second_row,
            $third_row,
            $fourth_row,
            $fifth_row
        );

        return $row_widgets;
    }

    private function _get_first_row_of_admin_and_team_dashboard($widgets) {

        $row = array();
        $columns = array();

        if (get_array_value($widgets, "clock_in_out")) {
            $columns[] = array("clock_in_out");
        }

        $columns[] = array("my_open_tasks");

        if (get_array_value($widgets, "events_today")) {
            $columns[] = array("events_today");
        }

        if (get_array_value($widgets, "total_due")) {
            $columns[] = array("total_due");
        }

        if (count($columns) < 4 && get_array_value($widgets, "total_clients")) {
            $columns[] = array("total_clients");
        }

        if (count($columns) < 4 && get_array_value($widgets, "total_leads")) {
            $columns[] = array("total_leads");
        }

        if (count($columns) < 4 && get_array_value($widgets, "total_contacts")) {
            $columns[] = array("total_contacts");
        }


        if (count($columns) < 4 && get_array_value($widgets, "new_posts")) {
            $columns[] = array("new_posts");
        }

        if (count($columns) < 4 && get_array_value($widgets, "total_hours_worked")) {
            $columns[] = array("total_hours_worked");
        }

        if (count($columns) < 4 && get_array_value($widgets, "open_projects")) {
            $columns[] = array("open_projects");
        }


        $ratio = "3-3-3-3";
        if (count($columns) == 3) {
            $ratio = "4-4-4";
        } else if (count($columns) == 2) {
            $ratio = "6-6";
        }


        $row["columns"] = $columns;
        $row["ratio"] = $ratio;

        return $row;
    }

    private function _get_second_and_third_row_of_admin_and_team_dashboard_widget_columns($widgets) {
        $columns = array();

        if (get_array_value($widgets, "projects_overview")) {
            if (get_array_value($widgets, "next_reminder")) {
                $columns[] = array("projects_overview", "next_reminder");
            } else {
                $columns[] = array("projects_overview");
            }
        }


        if (get_array_value($widgets, "invoice_overview")) {
            $columns[] = array("invoice_overview");
        }

        if (get_array_value($widgets, "income_vs_expenses")) {
            $columns[] = array("income_vs_expenses");
        }


        if (get_array_value($widgets, "all_tasks_overview")) {
            $columns[] = array("all_tasks_overview");
        }

        if (get_array_value($widgets, "team_members_overview")) {
            if (get_array_value($widgets, "last_announcement")) {
                $columns[] = array("team_members_overview", "last_announcement");
            } else {
                $columns[] = array("team_members_overview");
            }
        }

        if (get_array_value($widgets, "ticket_status")) {
            $columns[] = array("ticket_status");
        }


        if (get_array_value($widgets, "all_timesheets_statistics")) {
            $columns[] = array("all_timesheets_statistics");
        } else if (get_array_value($widgets, "my_timesheet_statistics")) {
            $columns[] = array("my_timesheet_statistics");
        }

        if (get_array_value($widgets, "estimate_sent_statistics")) {
            $columns[] = array("estimate_sent_statistics");
        }

        if (get_array_value($widgets, "invoice_statistics")) {
            $columns[] = array("invoice_statistics");
        }

        return $columns;
    }

    private function _get_second_row_of_admin_and_team_dashboard($all_columns) {

        $row = array();
        $columns = array();

        $column1 = get_array_value($all_columns, 0);
        $column2 = get_array_value($all_columns, 1);
        $column3 = get_array_value($all_columns, 2);

        if ($column1) {
            $columns[] = $column1;
        }
        if ($column2) {
            $columns[] = $column2;
        }
        if ($column3) {
            $columns[] = $column3;
        }

        $row["columns"] = $columns;

        $row["ratio"] = "4-4-4";

        return $row;
    }

    private function _get_third_row_of_admin_and_team_dashboard($all_columns) {

        $row = array();
        $columns = array();

        $column1 = get_array_value($all_columns, 3);
        $column2 = get_array_value($all_columns, 4);
        $column3 = get_array_value($all_columns, 5);

        if ($column1) {
            $columns[] = $column1;
        }
        if ($column2) {
            $columns[] = $column2;
        }
        if ($column3) {
            $columns[] = $column3;
        }

        $row["columns"] = $columns;

        $row["ratio"] = "4-4-4";

        return $row;
    }

    private function _get_fourth_row_of_admin_and_team_dashboard($widgets) {

        $row = array();
        $columns = array();

        $columns[] = array("project_timeline");
        if (get_array_value($widgets, "events") && get_array_value($widgets, "open_projects_list")) {
            $columns[] = array("events", "open_projects_list");
        } else if (get_array_value($widgets, "open_projects_list") && get_array_value($widgets, "starred_projects")) {
            $columns[] = array("open_projects_list", "starred_projects");
        }

        $columns[] = array("todo_list");

        $row["columns"] = $columns;
        $row["ratio"] = "4-4-4";

        return $row;
    }

    private function _get_fifth_row_of_admin_and_team_dashboard($widgets) {

        $row = array();
        $columns = array();

        $columns[] = array("my_tasks_list");
        $columns[] = array("sticky_note");

        $row["columns"] = $columns;
        $row["ratio"] = "8-4";

        return $row;
    }

    private function _get_admin_and_team_dashboard_data() {
        $row_widgets = $this->_get_admin_and_team_dashboard_widgets();
        return $this->_convert_widgets_array_to_formated_obj($row_widgets);
    }

    function view_custom_widget() {
        $id = $this->request->getPost("id");

        validate_numeric_value($id);

        $widget_info = $this->Custom_widgets_model->get_one($id);

        $view_data["model_info"] = $widget_info;

        return $this->template->view("dashboards/custom_widgets/view", $view_data);
    }

    function view_default_widget() {
        $widget = $this->request->getPost("widget");

        $view_data["widget"] = $this->_make_dashboard_widgets($widget);

        return $this->template->view("dashboards/custom_dashboards/edit/view_default_widget", $view_data);
    }

    private function _get_my_dashboard($id = 0, $is_staff_dashboard = false) {
        if ($id) {
            $options = array("id" => $id);
            if (!$is_staff_dashboard) {
                $options["user_id"] = $this->login_user->id;
            }

            return $this->Dashboards_model->get_details($options)->getRow();
        }
    }

    private function is_staff_dashboard($id) {
        return $id === get_setting("staff_default_dashboard") && $this->login_user->user_type === "staff";
    }

    private function _get_my_custom_widget($id = 0) {
        if ($id) {
            return $this->Custom_widgets_model->get_details(array("user_id" => $this->login_user->id, "id" => $id))->getRow();
        }
    }

    function edit_dashboard($id = 0) {
        if (get_setting("disable_dashboard_customization_by_clients") && $this->login_user->user_type == "client") {
            app_redirect("forbidden");
        }

        validate_numeric_value($id);

        $dashboard_info = $this->_get_my_dashboard($id);

        if (!$dashboard_info) {
            app_redirect("forbidden");
        }


        $view_data["dashboard_info"] = $dashboard_info;
        $view_data["widget_sortable_rows"] = $this->_make_editable_rows(unserialize($dashboard_info->data));
        $view_data["widgets"] = $this->_make_widgets($dashboard_info->id);

        return $this->template->rander("dashboards/custom_dashboards/edit/index", $view_data);
    }

    function save() {
        if (get_setting("disable_dashboard_customization_by_clients") && $this->login_user->user_type == "client") {
            app_redirect("forbidden");
        }

        $id = $this->request->getPost("id");

        if ($id) {
            $dashboard_info = $this->_get_my_dashboard($id);
            if (!$dashboard_info) {
                app_redirect("forbidden");
            }
        }

        $dashboard_data = json_decode($this->request->getPost("data"));

        $data = array(
            "user_id" => $this->login_user->id,
            "title" => $this->request->getPost("title"),
            "data" => $dashboard_data ? serialize($dashboard_data) : serialize(array()),
            "color" => $this->request->getPost("color")
        );

        $save_id = $this->Dashboards_model->ci_save($data, $id);

        if ($save_id) {
            echo json_encode(array("success" => true, "dashboard_id" => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function delete() {
        $id = $this->request->getPost('id');

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        if ($this->_get_my_dashboard($id) && $this->Dashboards_model->delete($id)) {
            if ($this->is_staff_dashboard($id)) {
                $this->Settings_model->save_setting("staff_default_dashboard", "");
            }

            echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    function delete_custom_widgets() {
        $id = $this->request->getPost('id');

        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        if ($this->_get_my_custom_widget($id) && $this->Custom_widgets_model->delete($id)) {
            echo json_encode(array("success" => true, "id" => $id, 'message' => app_lang('record_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    private function _remove_widgets($widgets = array()) {
        $widgets_permission = $this->_check_widgets_permissions();

        foreach ($widgets as $widget) {
            if (!get_array_value($widgets_permission, $widget) && !is_numeric($widget)) {
                unset($widgets[array_search($widget, $widgets)]);
            }
        }

        return $widgets;
    }

    private function _get_default_widgets() {
        //app widgets
        if ($this->login_user->user_type == "staff" && $this->show_staff_on_staff) {
            $default_widgets_array = array(
                "open_projects",
                "open_projects_list",
                "completed_projects",
                "starred_projects",
                "project_timeline",
                "my_open_tasks",
                "my_tasks_list",
                "all_tasks_kanban",
                "task_status",
                "clock_in_out",
                "members_clocked_in",
                "members_clocked_out",
                "all_team_members",
                "clocked_in_team_members",
                "clocked_out_team_members",
                "latest_online_team_members",
                "latest_online_client_contacts",
                "total_project_hours",
                "my_timesheet_statistics",
                "all_timesheets_statistics",
                "total_hours_worked",
                "timecard_statistics",
                "total_invoices",
                "total_payments",
                "total_due",
                "draft_invoices_value",
                "invoice_statistics",
                "income_vs_expenses",
                "new_tickets",
                "open_tickets",
                "closed_tickets",
                "ticket_status",
                "events_today",
                "events",
                "sticky_note",
                "todo_list",
                "new_posts",
                "active_members_on_projects",
                "pending_leave_approval",
                "draft_invoices",
                "total_clients",
                "total_contacts",
                "open_tickets_list",
                "total_leads",
                "projects_overview",
                "estimate_sent_statistics",
                "last_announcement",
                "team_members_overview",
                "all_tasks_overview",
                "invoice_overview",
                "next_reminder",
                "leads_overview",
                "my_tasks_overview",
            );
        } else {
            $default_widgets_array = array(
                "total_projects",
                "open_projects_list",
                "project_timeline",
                "total_invoices",
                "total_payments",
                "total_due",
                "invoice_statistics",
                "new_tickets",
                "open_tickets",
                "closed_tickets",
                "events_today",
                "events",
                "sticky_note",
                "todo_list",
                "open_tickets_list",
                "last_announcement",
            );
        }

        $plugin_widgets = array();
        $plugin_widgets = app_hooks()->apply_filters('app_filter_dashboard_widgets', $plugin_widgets);
        if ($plugin_widgets && is_array($plugin_widgets)) {
            foreach ($plugin_widgets as $plugin_widget) {
                if (is_array($plugin_widget)) {
                    array_push($default_widgets_array, get_array_value($plugin_widget, "widget"));
                }
            }
        }

        return $default_widgets_array;
    }

    private function _make_widgets($dashboard_id = 0) {

        $default_widgets_array = $this->_get_default_widgets();
        $checked_widgets_array = $this->_remove_widgets($default_widgets_array);

        $widgets_array = array_fill_keys($checked_widgets_array, "default_widgets");

        //custom widgets
        $custom_widgets = $this->Custom_widgets_model->get_details(array("user_id" => $this->login_user->id))->getResult();
        if ($custom_widgets) {
            foreach ($custom_widgets as $custom_widget) {
                $widgets_array[$custom_widget->id] = $custom_widget->title;
            }
        }

        //when its edit mode, we have to remove the widgets which have already in the dashboard
        $dashboard_info = $this->Dashboards_model->get_one($dashboard_id);
        $dashboard_elements_array = $dashboard_info->id ? unserialize($dashboard_info->data) : unserialize(get_setting("client_default_dashboard"));

        if ($dashboard_elements_array) {
            foreach ($dashboard_elements_array as $element) {
                $columns = get_array_value((array) $element, "columns");
                if ($columns) {
                    foreach ($columns as $contents) {
                        foreach ($contents as $content) {
                            $widget = get_array_value((array) $content, "widget");
                            if ($widget && array_key_exists($widget, $widgets_array)) {
                                unset($widgets_array[$widget]);
                            }
                        }
                    }
                }
            }
        }

        return $this->_make_widgets_row($widgets_array);
    }

    private function _make_widgets_row($widgets_array = array(), $permissions_array = array()) {
        $widgets = "";

        foreach ($widgets_array as $key => $value) {
            $error_class = "";
            if (count($permissions_array) && !is_numeric($key) && !get_array_value($permissions_array, $key)) {
                $error_class = "error";
            }
            $widgets .= "<div data-value=" . $key . " class='mb5 widget clearfix p10 bg-white $error_class'>" .
                    $this->_widgets_row_data(array($key => $value))
                    . "</div>";
        }

        if ($widgets) {
            return $widgets;
        } else {
            return "<span class='text-off empty-area-text'>" . app_lang('no_more_widgets_available') . "</span>";
        }
    }

    private function _widgets_row_data($widget_array) {
        $key = key($widget_array);
        $value = $widget_array[key($widget_array)];
        $details_button = "";
        if (is_numeric($key)) {

            $widgets_title = $value;
            $details_button = modal_anchor(get_uri("dashboard/view_custom_widget"), "<i data-feather='more-horizontal' class='icon-16'></i>", array("class" => "text-off pr10 pl10", "title" => app_lang('custom_widget_details'), "data-post-id" => $key));
        } else {
            $details_button = modal_anchor(get_uri("dashboard/view_default_widget"), "<i data-feather='more-horizontal' class='icon-16'></i>", array("class" => "text-off pr10 pl10", "title" => app_lang($key), "data-post-widget" => $key));
            $widgets_title = app_lang($key);
        }

        return "<span class='float-start text-left'>" . $widgets_title . "</span>
                <span class='float-end'>" . $details_button . "<i data-feather='move' class='icon-16 text-off'></i>";
    }

    private function _make_editable_rows($elements) {
        $view = "";
        $permissions_array = $this->_check_widgets_permissions();

        if ($elements) {
            foreach ($elements as $element) {

                $column_ratio = get_array_value((array) $element, "ratio");
                $column_ratio_explode = explode("-", $column_ratio);

                $view .= "<row class='widget-row clearfix d-flex bg-white' data-column-ratio='" . $column_ratio . "'>
                            <div class='float-start row-controller text-off font-16'>
                                <span class='move'><i data-feather='menu' class='icon-16'></i></span>
                                <span class='delete delete-widget-row'><i data-feather='x' class='icon-16'></i></span>
                            </div>
                            <div class = 'float-start clearfix row-container row pr15 pl15'>";

                $columns = get_array_value((array) $element, "columns");

                if ($columns) {
                    foreach ($columns as $key => $value) {
                        $column_class_value = $this->_get_column_class_value($key, $columns, $column_ratio_explode);
                        $view .= "<div class = 'pr0 pl15 widget-column col-md-" . $column_class_value . " col-sm-" . $column_class_value . "'>
                                    <div id = 'add-column-panel-" . rand(500, 10000) . "' class = 'add-column-panel add-column-drop text-center p15'>";

                        foreach ($value as $content) {
                            $widget_value = get_array_value((array) $content, "widget");
                            $view .= $this->_make_widgets_row(array($widget_value => get_array_value((array) $content, "title")), $permissions_array);
                        }

                        $view .= "</div></div>";
                    }
                }
                $view .= "</div></row>";
            }
            return $view;
        }
    }

    private function make_dashboard($elements) {
        $view = "";
        if ($elements) {

            foreach ($elements as $element) {
                $view .= "<div class='dashboards-row clearfix row'>";

                $columns = get_array_value((array) $element, "columns");
                $column_ratio = explode("-", get_array_value((array) $element, "ratio"));

                if ($columns) {

                    foreach ($columns as $key => $value) {
                        $view .= "<div class='widget-container col-md-" . $this->_get_column_class_value($key, $columns, $column_ratio) . "'>";

                        foreach ($value as $content) {
                            $widget = get_array_value((array) $content, "widget");
                            if ($widget) {
                                $view .= $this->_make_dashboard_widgets($widget);
                            }
                        }
                        $view .= "</div>";
                    }
                }

                $view .= "</div>";
            }
            return $view;
        }
    }

    private function _make_dashboard_widgets($widget = "") {
        $widgets_array = $this->_check_widgets_permissions();

        //custom widgets
        if (is_numeric($widget)) {
            $view_data["widget_info"] = $this->Custom_widgets_model->get_one($widget);
            return $this->template->view("dashboards/custom_dashboards/extra_data/custom_widget", $view_data);
        }

        if ($this->login_user->user_type == "staff" && $this->show_staff_on_staff) {
            return $this->_get_widgets_for_staffs($widget, $widgets_array);
        } else {
            return $this->_get_widgets_for_client($widget, $widgets_array);
        }
    }

    private function _get_widgets_for_staffs($widget, $widgets_array) {
        if (get_array_value($widgets_array, $widget)) {
            if ($widget == "clock_in_out") {
                return clock_widget();
            } else if ($widget == "events_today") {
                return events_today_widget();
            } else if ($widget == "new_posts") {
                return new_posts_widget();
            } else if ($widget == "invoice_statistics") {
                return invoice_statistics_widget();
            } else if ($widget == "my_timesheet_statistics") {
                return project_timesheet_statistics_widget("my_timesheet_statistics");
            } else if ($widget == "ticket_status") {
                $this->init_permission_checker("ticket");
                return ticket_status_widget(array("allowed_ticket_types" => $this->allowed_ticket_types, "show_assigned_tickets_only_user_id" => $this->show_assigned_tickets_only_user_id()));
            } else if ($widget == "timecard_statistics") {
                return timecard_statistics_widget();
            } else if ($widget == "income_vs_expenses") {
                return income_vs_expenses_widget("h373");
            } else if ($widget == "events") {
                return events_widget();
            } else if ($widget == "my_open_tasks") {
                return my_open_tasks_widget();
            } else if ($widget == "project_timeline") {
                return $this->template->view("dashboards/custom_dashboards/extra_data/widget_with_heading", array("icon" => "clock", "widget" => $widget));
            } else if ($widget == "task_status") {
                return my_task_stataus_widget("h370");
            } else if ($widget == "sticky_note") {
                return sticky_note_widget("h370");
            } else if ($widget == "all_tasks_kanban") {
                return all_tasks_kanban_widget();
            } else if ($widget == "todo_list") {
                return todo_list_widget();
            } else if ($widget == "open_projects") {
                return open_projects_widget("");
            } else if ($widget == "completed_projects") {
                return completed_projects_widget("");
            } else if ($widget == "members_clocked_in" || $widget == "members_clocked_out") {
                $access_attendance = $this->get_access_info("attendance");
                return count_clock_in_out_widget_small(array(
                    "widget" => $widget,
                    "attendance_access_type" => $access_attendance->access_type,
                    "attendance_allowed_members" => $access_attendance->allowed_members
                ));
            } else if ($widget == "open_projects_list") {
                return my_open_projects_widget();
            } else if ($widget == "starred_projects") {
                return my_starred_projects_widget();
            } else if ($widget == "new_tickets" || $widget == "open_tickets" || $widget == "closed_tickets") {
                $this->init_permission_checker("ticket");
                $explode_widget = explode("_", $widget);
                return ticket_status_widget_small(array("status" => $explode_widget[0], "allowed_ticket_types" => $this->allowed_ticket_types, "show_assigned_tickets_only_user_id" => $this->show_assigned_tickets_only_user_id()));
            } else if ($widget == "all_team_members") {
                return all_team_members_widget();
            } else if ($widget == "clocked_in_team_members") {
                $this->init_permission_checker("attendance");
                return clocked_in_team_members_widget(array("access_type" => $this->access_type, "allowed_members" => $this->allowed_members));
            } else if ($widget == "clocked_out_team_members") {
                $this->init_permission_checker("attendance");
                return clocked_out_team_members_widget(array("access_type" => $this->access_type, "allowed_members" => $this->allowed_members));
            } else if ($widget == "latest_online_team_members") {
                return active_members_and_clients_widget("staff");
            } else if ($widget == "total_invoices" || $widget == "total_payments" || $widget == "total_due" || $widget == "draft_invoices_value") {
                $explode_widget = explode("_", $widget);
                $value = get_array_value($explode_widget, 1);
                if ($widget == "draft_invoices_value") {
                    $value = "draft";
                }
                return get_invoices_value_widget($value);
            } else if ($widget == "my_tasks_list") {
                return my_tasks_list_widget();
            } else if ($widget == "all_timesheets_statistics") {
                return project_timesheet_statistics_widget("all_timesheets_statistics");
            } else if ($widget == "pending_leave_approval") {
                $this->init_permission_checker("leave");
                return pending_leave_approval_widget(array("access_type" => $this->access_type, "allowed_members" => $this->allowed_members));
            } else if ($widget == "total_hours_worked" || $widget == "total_project_hours") {
                return count_total_time_widget_small(0, $widget);
            } else if ($widget == "active_members_on_projects") {
                return active_members_on_projects_widget();
            } else if ($widget == "draft_invoices") {
                return draft_invoices_widget();
            } else if ($widget == "total_clients" || $widget == "total_contacts" || $widget == "latest_online_client_contacts") {
                $show_own_clients_only_user_id = $this->show_own_clients_only_user_id();
                $this->init_permission_checker("client");
                if ($widget == "total_clients") {
                    return total_clients_widget($show_own_clients_only_user_id, $this->allowed_client_groups);
                } else if ($widget == "total_contacts") {
                    return total_contacts_widget($show_own_clients_only_user_id, $this->allowed_client_groups);
                } else if ($widget == "latest_online_client_contacts") {
                    return active_members_and_clients_widget("client", $show_own_clients_only_user_id, $this->allowed_client_groups);
                }
            } else if ($widget == "open_tickets_list") {
                return open_tickets_list_widget();
            } else if ($widget == "total_leads") {
                $show_own_leads_only_user_id = $this->show_own_leads_only_user_id();
                return total_leads_widget(true, $show_own_leads_only_user_id);
            } else if ($widget == "projects_overview") {
                return projects_overview_widget();
            } else if ($widget == "estimate_sent_statistics") {
                return estimate_sent_statistics_widget();
            } else if ($widget == "last_announcement") {
                return last_announcement_widget();
            } else if ($widget == "team_members_overview") {
                $access_leave = $this->get_access_info("leave");
                $access_attendance = $this->get_access_info("attendance");
                return team_members_overview_widget(array(
                    "leave_access_type" => $access_leave->access_type,
                    "leave_allowed_members" => $access_leave->allowed_members,
                    "attendance_access_type" => $access_attendance->access_type,
                    "attendance_allowed_members" => $access_attendance->allowed_members
                ));
            } else if ($widget == "all_tasks_overview") {
                return tasks_overview_widget("all_tasks_overview");
            } else if ($widget == "invoice_overview") {
                return invoice_overview_widget();
            } else if ($widget == "next_reminder") {
                return next_reminder_widget();
            } else if ($widget == "leads_overview") {
                return leads_overview_widget();
            } else if ($widget == "my_tasks_overview") {
                return tasks_overview_widget("my_tasks_overview");
            }

            $plugin_widget = $this->_get_plugin_widgets($widget);
            if ($plugin_widget) {
                return $plugin_widget;
            }
        } else {
            return invalid_access_widget();
        }
    }

    private function _get_widgets_for_client($widget, $widgets_array) {
        //client's widgets
        $client_info = get_array_value($widgets_array, "client_info");
        $client_id = get_array_value($widgets_array, "client_id");

        if (get_array_value($widgets_array, $widget)) {
            if ($widget == "total_projects") {
                return $this->template->view("clients/info_widgets/tab", array("tab" => "projects", "client_info" => $client_info));
            } else if ($widget == "total_invoices") {
                return $this->template->view("clients/info_widgets/tab", array("tab" => "total_invoiced", "client_info" => $client_info));
            } else if ($widget == "total_payments") {
                return $this->template->view("clients/info_widgets/tab", array("tab" => "payments", "client_info" => $client_info));
            } else if ($widget == "total_due") {
                return $this->template->view("clients/info_widgets/tab", array("tab" => "due", "client_info" => $client_info));
            } else if ($widget == "open_projects_list") {
                return my_open_projects_widget($client_id);
            } else if ($widget == "events") {
                return events_widget();
            } else if ($widget == "sticky_note") {
                return sticky_note_widget("h370");
            } else if ($widget == "invoice_statistics") {
                return invoice_statistics_widget();
            } else if ($widget == "events_today") {
                return events_today_widget();
            } else if ($widget == "todo_list") {
                return todo_list_widget();
            } else if ($widget == "new_tickets" || $widget == "open_tickets" || $widget == "closed_tickets") {
                $explode_widget = explode("_", $widget);
                return ticket_status_widget_small(array("status" => $explode_widget[0]));
            } else if ($widget == "project_timeline") {
                return $this->template->view("dashboards/custom_dashboards/extra_data/widget_with_heading", array("icon" => "clock", "widget" => $widget));
            } else if ($widget == "open_tickets_list") {
                return open_tickets_list_widget();
            } else if ($widget == "last_announcement") {
                return last_announcement_widget();
            }

            $plugin_widget = $this->_get_plugin_widgets($widget);
            if ($plugin_widget) {
                return $plugin_widget;
            }
        } else {
            return invalid_access_widget();
        }
    }

    private function _get_plugin_widgets($widget = "") {
        $plugin_widgets = array();
        $plugin_widgets = app_hooks()->apply_filters('app_filter_dashboard_widgets', $plugin_widgets);
        if ($plugin_widgets && is_array($plugin_widgets)) {
            foreach ($plugin_widgets as $plugin_widget) {
                if (is_array($plugin_widget) && get_array_value($plugin_widget, "widget") == $widget) {
                    return get_array_value($plugin_widget, "widget_view");
                }
            }
        }
    }

    private function _get_column_class_value($key, $columns, $column_ratio) {
        $columns_array = array(1 => 12, 2 => 6, 3 => 4, 4 => 3);

        $column_count = count($columns);
        $column_ratio_count = count($column_ratio);

        $class_value = $column_ratio[$key];

        if ($column_count < $column_ratio_count) {
            $class_value = $columns_array[$column_count];
        }

        return $class_value;
    }

    function save_dashboard_sort() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');

        $data = array(
            "sort" => $this->request->getPost('sort')
        );

        if ($id) {
            $save_id = $this->Dashboards_model->ci_save($data, $id);

            if ($save_id) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        }
    }

    function client_default_dashboard() {
        $this->access_only_admin_or_settings_admin();
        $this->show_staff_on_staff = false;

        $widgets = $this->_check_widgets_permissions();
        $view_data["dashboards"] = array();

        $client_default_dashboard = get_setting("client_default_dashboard");
        if ($client_default_dashboard) {
            $view_data["widget_columns"] = $this->make_dashboard(unserialize($client_default_dashboard));

            $dashboard_view = $this->template->view("dashboards/custom_dashboards/view", $view_data);
        } else {
            $view_data['show_invoice_info'] = get_array_value($widgets, "show_invoice_info");
            $view_data["show_project_info"] = true; //client can view projects
            $view_data['hidden_menu'] = get_array_value($widgets, "hidden_menu");
            $view_data['client_info'] = get_array_value($widgets, "client_info");
            $view_data['client_id'] = get_array_value($widgets, "client_id");
            $view_data['page_type'] = get_array_value($widgets, "page_type");
            $view_data["custom_field_headers"] = $this->Custom_fields_model->get_custom_field_headers_for_table("projects", $this->login_user->is_admin, $this->login_user->user_type);
            $view_data["custom_field_filters"] = $this->Custom_fields_model->get_custom_field_filters("projects", $this->login_user->is_admin, $this->login_user->user_type);

            $dashboard_view = $this->template->view("dashboards/client_dashboard", $view_data);
        }

        $view_data["dashboard_view"] = $dashboard_view;

        return $this->template->rander("settings/client_default_dashboard/index", $view_data);
    }

    function edit_client_default_dashboard() {
        $this->access_only_admin_or_settings_admin();
        $this->show_staff_on_staff = false;

        $view_data["widget_sortable_rows"] = $this->_make_editable_rows(unserialize(get_setting("client_default_dashboard")));
        $view_data["widgets"] = $this->_make_widgets();

        return $this->template->rander("settings/client_default_dashboard/edit_dashboard", $view_data);
    }

    function save_client_default_dashboard() {
        $this->access_only_admin_or_settings_admin();

        $dashboard_data = json_decode($this->request->getPost("data"));
        $serialized_data = $dashboard_data ? serialize($dashboard_data) : serialize(array());

        $this->Settings_model->save_setting("client_default_dashboard", $serialized_data);

        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }

    function restore_to_default_client_dashboard() {
        $this->access_only_admin_or_settings_admin();
        $this->Settings_model->save_setting("client_default_dashboard", "");
        app_redirect("dashboard/client_default_dashboard");
    }

    function mark_as_default() {
        $this->access_only_admin();

        $id = $this->request->getPost('id');
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        if (is_null($id)) {
            $id = "";
        }

        $this->Settings_model->save_setting("staff_default_dashboard", $id);
        echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
    }

}

/* End of file dashboard.php */
/* Location: ./app/controllers/dashboard.php */