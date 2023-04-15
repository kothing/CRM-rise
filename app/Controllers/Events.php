<?php

namespace App\Controllers;

use App\Libraries\Google_calendar_events;

class Events extends Security_Controller {

    private $Google_calendar_events;

    function __construct() {
        parent::__construct();
        $this->Google_calendar_events = new Google_calendar_events();
    }

    //load calendar view
    function index($encrypted_event_id = "") {
        $this->check_module_availability("module_event");
        $view_data['encrypted_event_id'] = clean_data($encrypted_event_id);
        $view_data['calendar_filter_dropdown'] = $this->get_calendar_filter_dropdown();
        $view_data['event_labels_dropdown'] = json_encode($this->make_labels_dropdown("event", "", true, app_lang("event_label")));
        return $this->template->rander("events/index", $view_data);
    }

    private function can_share_events() {
        if ($this->login_user->user_type === "staff") {
            return get_array_value($this->login_user->permissions, "disable_event_sharing") == "1" ? false : true;
        }
    }

    //show add/edit event modal form
    function modal_form() {
        $event_id = decode_id($this->request->getPost('encrypted_event_id'), "event_id");
        $model_info = $this->Events_model->get_one($event_id);

        $model_info->start_date = $model_info->start_date ? $model_info->start_date : $this->request->getPost('start_date');
        $model_info->end_date = $model_info->end_date ? $model_info->end_date : $this->request->getPost('end_date');
        $model_info->start_time = $model_info->start_time ? $model_info->start_time : $this->request->getPost('start_time');
        $model_info->end_time = $model_info->end_time ? $model_info->end_time : $this->request->getPost('end_time');

        //for a specific share, we have to find that if it's been shared with team member or client's contact
        $model_info->share_with_specific = "";
        if ($model_info->share_with && $model_info->share_with != "all") {
            $share_with_explode = explode(":", $model_info->share_with);
            $model_info->share_with_specific = $share_with_explode[0];
        }

        $view_data['client_id'] = $this->request->getPost('client_id');

        //don't show clients dropdown for lead's estimate editing
        $client_info = $this->Clients_model->get_one($model_info->client_id);
        if ($client_info->is_lead) {
            $view_data['client_id'] = $client_info->id;
        }

        $view_data['model_info'] = $model_info;
        $view_data['members_and_teams_dropdown'] = json_encode(get_team_members_and_teams_select2_data_list(true));
        $view_data['time_format_24_hours'] = get_setting("time_format") == "24_hours" ? true : false;

        //prepare clients dropdown, check if user has permission to access the client
        $client_access_info = $this->get_access_info("client");

        $clients_dropdown = array();
        if ($this->login_user->is_admin || $client_access_info->access_type == "all") {
            $clients_dropdown = $this->get_clients_and_leads_dropdown(true);
        }

        $view_data['clients_dropdown'] = $clients_dropdown;

        $view_data["can_share_events"] = $this->can_share_events();

        //prepare label suggestion dropdown
        $view_data['label_suggestions'] = $this->make_labels_dropdown("event", $model_info->labels);

        $view_data["custom_fields"] = $this->Custom_fields_model->get_combined_details("events", $view_data['model_info']->id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

        return $this->template->view('events/modal_form', $view_data);
    }

    //save an event
    function save() {
        $type = $this->request->getPost('type');
        $validation_array = array(
            "title" => "required",
            "start_date" => "required"
        );

        if ($type === "reminder") {
            $validation_array["start_time"] = "required";
        } 
        
        $this->validate_submitted_data($validation_array);

        $id = $this->request->getPost('id');

        //convert to 24hrs time format
        $start_time = $this->request->getPost('start_time');
        $end_time = $this->request->getPost('end_time');

        if (get_setting("time_format") != "24_hours") {
            $start_time = convert_time_to_24hours_format($start_time);
            $end_time = convert_time_to_24hours_format($end_time);
        }

        //prepare share with data
        $share_with = $this->request->getPost('share_with');
        if ($share_with == "specific") {
            $share_with = $this->request->getPost('share_with_specific');
        } else if ($share_with == "specific_client_contacts") {
            $share_with = $this->request->getPost('share_with_specific_client_contact');
        }

        $start_date = $this->request->getPost('start_date');
        $end_date = $this->request->getPost('end_date');

        $recurring = $this->request->getPost('recurring') ? 1 : 0;
        $repeat_every = $this->request->getPost('repeat_every');
        $repeat_type = $this->request->getPost('repeat_type');
        $no_of_cycles = $this->request->getPost('no_of_cycles');
        $client_id = $this->request->getPost('client_id');

        $target_path = get_setting("timeline_file_path");
        $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "event");
        $new_files = unserialize($files_data);

        $data = array(
            "title" => $this->request->getPost('title'),
            "description" => $this->request->getPost('description'),
            "start_date" => $start_date,
            "start_time" => $start_time,
            "end_time" => $end_time,
            "location" => $this->request->getPost('location'),
            "labels" => $this->request->getPost('labels'),
            "color" => $this->request->getPost('color'),
            "created_by" => $this->login_user->id,
            "share_with" => $share_with,
            "recurring" => $recurring,
            "repeat_every" => $repeat_every,
            "repeat_type" => $repeat_type ? $repeat_type : NULL,
            "no_of_cycles" => $no_of_cycles ? $no_of_cycles : 0,
            "client_id" => $client_id ? $client_id : 0,
            "type" => $type ? $type : "event",
            "task_id" => $this->request->getPost('task_id'),
            "project_id" => $this->request->getPost('project_id'),
            "lead_id" => $this->request->getPost('lead_id'),
            "ticket_id" => $this->request->getPost('ticket_id'),
        );

        if ($end_date) {
            $data["end_date"] = $end_date;
        }

        if (!$id) {
            $data["confirmed_by"] = 0;
            $data["rejected_by"] = 0;
        }

        //prepare a comma sepearted dates of start date.
        $recurring_dates = "";
        $last_start_date = NULL;

        if ($recurring) {
            $no_of_cycles = $this->Events_model->get_no_of_cycles($repeat_type, $no_of_cycles);

            for ($i = 1; $i <= $no_of_cycles; $i++) {
                $start_date = add_period_to_date($start_date, $repeat_every, $repeat_type);
                $recurring_dates .= $start_date . ",";

                $last_start_date = $start_date; //collect the last start date
            }
        }

        $data["recurring_dates"] = $recurring_dates;
        $data["last_start_date"] = $last_start_date;

        if (!$this->can_share_events()) {
            $data["share_with"] = "";
        }


        //only admin can edit other team members events
        //non-admin team members can edit only their own events
        if ($id && !$this->login_user->is_admin) {
            $event_info = $this->Events_model->get_one($id);
            if ($event_info->created_by != $this->login_user->id) {
                app_redirect("forbidden");
            }
        }

        if ($id) {
            $event_info = $this->Events_model->get_one($id);
            $timeline_file_path = get_setting("timeline_file_path");

            $new_files = update_saved_files($timeline_file_path, $event_info->files, $new_files);
        }

        $data["files"] = serialize($new_files);

        $data = clean_data($data);

        $save_id = $this->Events_model->ci_save($data, $id);
        if ($save_id) {
            //if the google calendar is integrated, add/modify the event
            if (get_setting("enable_google_calendar_api") && get_setting('user_' . $this->login_user->id . '_integrate_with_google_calendar') && (get_setting("google_calendar_authorized") || get_setting('user_' . $this->login_user->id . '_google_calendar_authorized'))) {
                $this->Google_calendar_events->save_event($this->login_user->id, $save_id);
            }

            save_custom_fields("events", $save_id, $this->login_user->is_admin, $this->login_user->user_type);

            if ($type === "reminder") {
                $reminder_info = $this->Events_model->get_one($save_id);
                $success_data = $this->_make_reminder_row($reminder_info);
                echo json_encode(array("success" => true, "id" => $save_id, "data" => $success_data, 'message' => app_lang('record_saved'), "reminder_info" => $reminder_info));
            } else {
                echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
            }

            if ($share_with) {
                if ($id) {
                    //the event modified and shared with others, log the notificaiton
                    log_notification("calendar_event_modified", array("event_id" => $save_id));
                } else {
                    //new event added and shared with others, log the notificaiton
                    log_notification("new_event_added_in_calendar", array("event_id" => $save_id));
                }
            }
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //delete/undo an event
    function delete() {
        $id = $this->request->getPost('id'); //reminder
        if (!$id) { //event
            $this->validate_submitted_data(array(
                "encrypted_event_id" => "required"
            ));

            $id = decode_id($this->request->getPost('encrypted_event_id'), "event_id"); //to make is secure we'll use the encrypted id
        }

        $event_info = $this->Events_model->get_one($id);

        //only admin can delete other team members events
        //non-admin team members can delete only their own events
        if ($id && !$this->login_user->is_admin) {
            if ($event_info->created_by != $this->login_user->id) {
                app_redirect("forbidden");
            }
        }


        if ($this->Events_model->delete($id)) {
            //if there has event associated with this on google calendar, delete that too
            if (get_setting("enable_google_calendar_api") && $event_info->google_event_id && $event_info->editable_google_event && get_setting('user_' . $this->login_user->id . '_integrate_with_google_calendar') && (get_setting("google_calendar_authorized") || get_setting('user_' . $this->login_user->id . '_google_calendar_authorized'))) {
                $this->Google_calendar_events->delete($event_info->google_event_id, $this->login_user->id);
            }

            //delete the files
            $file_path = get_setting("timeline_file_path");
            if ($event_info->files) {
                $files = unserialize($event_info->files);

                foreach ($files as $file) {
                    delete_app_files($file_path, array($file));
                }
            }

            echo json_encode(array("success" => true, 'message' => app_lang('event_deleted')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
        }
    }

    //get calendar event
    function calendar_events($filter_values = "", $event_label_id = 0, $client_id = 0) {
        $start = $_GET["start"];
        $end = $_GET["end"];

        $result = array();

        $filter_values_array = explode('-', $filter_values);

        if (in_array("events", $filter_values_array)) {
            //get all events
            $is_client = false;
            if ($this->login_user->user_type == "client") {
                $is_client = true;
            }

            validate_numeric_value($event_label_id);
            validate_numeric_value($client_id);
            $options_of_events = array("user_id" => $this->login_user->id, "team_ids" => $this->login_user->team_ids, "client_id" => $client_id, "start_date" => $start, "end_date" => $end, "include_recurring" => true, "is_client" => $is_client, "label_id" => $event_label_id);

            $list_data_of_events = $this->Events_model->get_details($options_of_events)->getResult();

            foreach ($list_data_of_events as $data) {

                //check if this recurring event, generate recurring evernts based on the condition

                $data->cycle = 0; //it's required to calculate the recurring events

                $result[] = $this->_make_calendar_event($data); //add regular event

                if ($data->recurring) {
                    $no_of_cycles = $this->Events_model->get_no_of_cycles($data->repeat_type, $data->no_of_cycles);

                    for ($i = 1; $i <= $no_of_cycles; $i++) {
                        $data->start_date = add_period_to_date($data->start_date, $data->repeat_every, $data->repeat_type);
                        $data->end_date = add_period_to_date($data->end_date, $data->repeat_every, $data->repeat_type);
                        $data->cycle = $i;

                        $result[] = $this->_make_calendar_event($data);
                    }
                }
            }
        }

        if (in_array("leave", $filter_values_array) && $this->login_user->user_type == "staff") {
            //get all approved leaves
            $leave_access_info = $this->get_access_info("leave");
            $options_of_leaves = array("start_date" => $start, "end_date" => $end, "login_user_id" => $this->login_user->id, "access_type" => $leave_access_info->access_type, "allowed_members" => $leave_access_info->allowed_members, "status" => "approved");

            $list_data_of_leaves = $this->Leave_applications_model->get_list($options_of_leaves)->getResult();

            foreach ($list_data_of_leaves as $leave) {
                $result[] = $this->_make_leave_event($leave);
            }
        }

        if (in_array("project_deadline", $filter_values_array) || in_array("project_start_date", $filter_values_array)) {
            //get all project deadlines
            $options = array(
                "status" => "open",
                "start_date" => $start,
                "deadline" => $end,
                "client_id" => $client_id,
                "for_events_table" => true
            );

            if ($this->login_user->user_type == "staff") {
                if (!$this->can_manage_all_projects()) {
                    $options["user_id"] = $this->login_user->id;
                }
            } else {
                $options["client_id"] = $this->login_user->client_id;
            }

            //project start dates
            if (in_array("project_start_date", $filter_values_array)) {
                $options["start_date_for_events"] = true;
                $list_data_of_projects = $this->Projects_model->get_details($options)->getResult();
                if ($list_data_of_projects) {
                    foreach ($list_data_of_projects as $project) {
                        $result[] = $this->_make_project_event($project, true);
                    }
                }
            }

            //project deadlines
            if (in_array("project_deadline", $filter_values_array)) {
                unset($options["start_date_for_events"]);
                $list_data_of_projects = $this->Projects_model->get_details($options)->getResult();
                if ($list_data_of_projects) {
                    foreach ($list_data_of_projects as $project) {
                        $result[] = $this->_make_project_event($project);
                    }
                }
            }
        }

        if ($this->login_user->user_type == "staff" && (in_array("task_deadline", $filter_values_array) || in_array("task_start_date", $filter_values_array))) {
            //get all task deadlines
            $options = array(
                "start_date" => $start,
                "deadline" => $end,
                "project_status" => "open",
                "show_assigned_tasks_only_user_id" => $this->show_assigned_tasks_only_user_id(),
                "for_events" => true
            );

            if (!$this->can_manage_all_projects()) {
                $options["project_member_id"] = $this->login_user->id; //don't show all tasks to non-admin users
            }

            if (in_array("task_deadline", $filter_values_array)) {
                //deadlines
                $options["deadline_for_events"] = true;
                $list_data_of_tasks = $this->Tasks_model->get_details($options)->getResult();
                foreach ($list_data_of_tasks as $task) {
                    $result[] = $this->_make_task_event($task);
                }
            }

            if (in_array("task_start_date", $filter_values_array)) {
                //start dates
                $options["start_date_for_events"] = true;
                $list_data_of_tasks = $this->Tasks_model->get_details($options)->getResult();
                foreach ($list_data_of_tasks as $task) {
                    $result[] = $this->_make_task_event($task, true);
                }
            }
        }

        echo json_encode($result);
    }

    //prepare calendar event
    private function _make_calendar_event($data) {

        return array(
            "title" => $data->title,
            "start" => $data->start_date . " " . $data->start_time,
            "end" => $data->end_date . " " . $data->end_time,
            "backgroundColor" => $data->color ? $data->color : "#83c340",
            "borderColor" => $data->color ? $data->color : "#83c340",
            "extendedProps" => array(
                "icon" => get_event_icon($data->share_with),
                "encrypted_event_id" => encode_id($data->id, "event_id"), //to make is secure we'll use the encrypted id
                "cycle" => $data->cycle,
                "event_type" => "event",
            )
        );
    }

    //prepare approved leave event
    private function _make_leave_event($data) {

        return array(
            "title" => $data->applicant_name,
            "start" => $data->start_date . " " . "00:00:00",
            "end" => $data->end_date . " " . "23:59:59", //show leave applications for the full day
            "backgroundColor" => $data->leave_type_color,
            "borderColor" => $data->leave_type_color,
            "extendedProps" => array(
                "icon" => "log-out",
                "leave_id" => $data->id, //to make is secure we'll use the encrypted id
                "cycle" => 0,
                "event_type" => "leave",
            )
        );
    }

    //prepare project deadline event
    private function _make_project_event($data, $start_date_event = false) {
        $color = "#1ccacc"; //future events
        $my_local_time = get_my_local_time("Y-m-d");
        if (($data->deadline && ($my_local_time > $data->deadline)) || (!$data->deadline && $data->start_date && ($my_local_time > $data->start_date))) { //back-dated events
            $color = "#d9534f";
        } else if (($data->deadline && $my_local_time == $data->deadline) || (!$data->deadline && $data->start_date && $my_local_time == $data->start_date)) { //today events
            $color = "#f0ad4e";
        }

        $event_type = "project_deadline";
        $event_custom_class = "event-deadline-border";
        if ($start_date_event) {
            $event_type = "project_start_date";
            $event_custom_class = "";
        }

        return array(
            "title" => $data->title,
            "start" => ($start_date_event ? $data->start_date : $data->deadline) . " " . "00:00:00",
            "end" => ($start_date_event ? $data->start_date : $data->deadline) . " " . "23:59:59", //show project deadline for the full day
            "backgroundColor" => $color,
            "borderColor" => $color,
            "classNames" => $event_custom_class,
            "extendedProps" => array(
                "icon" => "grid",
                "project_id" => $data->id,
                "cycle" => 0,
                "event_type" => $event_type,
            )
        );
    }

    //prepare task deadline event
    private function _make_task_event($data, $start_date_event = false) {
        $event_type = "task_deadline";
        $event_custom_class = "event-deadline-border";
        if ($start_date_event) {
            $event_type = "task_start_date";
            $event_custom_class = "";
        }

        return array(
            "title" => $data->title,
            "start" => ($start_date_event ? $data->start_date : $data->deadline) . " " . "00:00:00",
            "end" => ($start_date_event ? $data->start_date : $data->deadline) . " " . "23:59:59", //show task deadline for the full day
            "backgroundColor" => $data->status_color,
            "borderColor" => $data->status_color,
            "classNames" => $event_custom_class,
            "extendedProps" => array(
                "icon" => "list",
                "task_id" => $data->id,
                "cycle" => 0,
                "event_type" => $event_type,
            )
        );
    }

    //view an evnet
    function view() {
        $encrypted_event_id = $this->request->getPost('id');
        $cycle = $this->request->getPost('cycle');

        $this->validate_submitted_data(array(
            "id" => "required"
        ));

        $view_data = $this->_make_view_data($encrypted_event_id, $cycle);

        return $this->template->view('events/view', $view_data);
    }

    private function _make_view_data($encrypted_event_id, $cycle = "0") {
        $event_id = decode_id($encrypted_event_id, "event_id");

        $model_info = $this->Events_model->get_details(array("id" => $event_id))->getRow();

        if (!$model_info->end_date) {
            $model_info->end_date = $model_info->start_date;
        }

        if ($event_id && $model_info->id) {

            $model_info->cycle = $cycle * 1;

            if ($model_info->recurring && $cycle) {
                $model_info->start_date = add_period_to_date($model_info->start_date, $model_info->repeat_every * $cycle, $model_info->repeat_type);
                $model_info->end_date = add_period_to_date($model_info->end_date, $model_info->repeat_every * $cycle, $model_info->repeat_type);
            }


            $view_data['encrypted_event_id'] = $encrypted_event_id; //to make is secure we'll use the encrypted id 
            $view_data['editable'] = $this->request->getPost('editable');
            $view_data['model_info'] = $model_info;
            $view_data['event_icon'] = get_event_icon($model_info->share_with);
            $view_data['custom_fields_list'] = $this->Custom_fields_model->get_combined_details("events", $event_id, $this->login_user->is_admin, $this->login_user->user_type)->getResult();

            $confirmed_by_array = explode(",", $model_info->confirmed_by);
            $rejected_by_array = explode(",", $model_info->rejected_by);

            //prepare event lable
            $view_data['labels'] = make_labels_view_data($model_info->labels_list, "", true);

            //prepare status lable and status buttons
            $status = "";
            $status_button = "";

            $status_confirm = modal_anchor(get_uri("events/save_event_status/"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('confirm'), array("class" => "btn btn-success float-start", "data-post-encrypted_event_id" => $encrypted_event_id, "title" => app_lang('event_details'), "data-post-status" => "confirmed", "data-post-editable" => "1"));
            $status_reject = modal_anchor(get_uri("events/save_event_status/"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('reject'), array("class" => "btn btn-danger float-start", "data-post-encrypted_event_id" => $encrypted_event_id, "title" => app_lang('event_details'), "data-post-status" => "rejected", "data-post-editable" => "1"));

            if (in_array($this->login_user->id, $confirmed_by_array)) {
                $status = "<span class='badge large' style='background-color:#5CB85C;' title=" . app_lang("event_status") . ">" . app_lang("confirmed") . "</span> ";
                $status_button = $status_reject;
            } else if (in_array($this->login_user->id, $rejected_by_array)) {
                $status = "<span class='badge large' style='background-color:#D9534F;' title=" . app_lang("event_status") . ">" . app_lang("rejected") . "</span> ";
                $status_button = $status_confirm;
            } else {
                $status_button = $status_confirm . $status_reject;
            }

            $view_data["status"] = $status;
            $view_data['status_button'] = $status_button;

            //prepare confimed/rejected user's list
            $confimed_rejected_users = $this->_get_confirmed_and_rejected_users_list($confirmed_by_array, $rejected_by_array);

            $view_data['confirmed_by'] = get_array_value($confimed_rejected_users, 'confirmed_by');
            $view_data['rejected_by'] = get_array_value($confimed_rejected_users, 'rejected_by');

            return $view_data;
        } else {
            show_404();
        }
    }

    private function _get_confirmed_and_rejected_users_list($confirmed_by_array, $rejected_by_array) {

        $confirmed_by = "";
        $rejected_by = "";

        $response_by_users = $this->Events_model->get_response_by_users(($confirmed_by_array + $rejected_by_array));
        if ($response_by_users) {
            foreach ($response_by_users->getResult() as $user) {
                $image_url = get_avatar($user->image);
                $response_by_user = "<span data-bs-toggle='tooltip' title='" . $user->member_name . "' class='avatar avatar-xs mr10'><img src='$image_url' alt='...'></span>";

                if ($user->user_type === "client") {
                    $profile_link = get_client_contact_profile_link($user->id, $response_by_user);
                } else {
                    $profile_link = get_team_member_profile_link($user->id, $response_by_user);
                }

                if (in_array($user->id, $confirmed_by_array)) {
                    $confirmed_by .= $profile_link;
                } else {
                    $rejected_by .= $profile_link;
                }
            }
        }

        return array("confirmed_by" => $confirmed_by, "rejected_by" => $rejected_by);
    }

    function save_event_status() {
        $encrypted_event_id = $this->request->getPost('encrypted_event_id');
        $event_id = decode_id($encrypted_event_id, "event_id");

        $status = $this->request->getPost('status');
        $user_id = $this->login_user->id;

        $this->Events_model->save_event_status($event_id, $user_id, $status);

        $view_data = $this->_make_view_data($encrypted_event_id);

        return $this->template->view('events/view', $view_data);
    }

    //get all contacts of a selected client
    function get_all_contacts_of_client($client_id) {

        $client_access_info = $this->get_access_info("client");
        validate_numeric_value($client_id);
        if ($client_id && ($this->login_user->is_admin || $client_access_info->access_type == "all")) {
            $client_contacts = $this->Users_model->get_all_where(array("status" => "active", "client_id" => $client_id, "deleted" => 0))->getResult();
            $client_contacts_array = array();

            if ($client_contacts) {
                foreach ($client_contacts as $contacts) {
                    $client_contacts_array[] = array("type" => "contact", "id" => "contact:" . $contacts->id, "text" => $contacts->first_name . " " . $contacts->last_name);
                }
            }
            echo json_encode($client_contacts_array);
        }
    }

    function google_calendar_settings_modal_form() {
        if (get_setting("enable_google_calendar_api") && (get_setting("google_calendar_authorized") || get_setting('user_' . $this->login_user->id . '_google_calendar_authorized'))) {
            $user_calendar_ids = get_setting('user_' . $this->login_user->id . '_calendar_ids');
            $calendar_ids = $user_calendar_ids ? unserialize($user_calendar_ids) : array();

            return $this->template->view("events/google_calendar_settings_modal_form", array("calendar_ids" => $calendar_ids));
        }
    }

    function save_google_calendar_settings() {
        if (get_setting("enable_google_calendar_api") && (get_setting("google_calendar_authorized") || get_setting('user_' . $this->login_user->id . '_google_calendar_authorized'))) {
            $integrate_with_google_calendar = $this->request->getPost("integrate_with_google_calendar");
            $this->Settings_model->save_setting("user_" . $this->login_user->id . "_integrate_with_google_calendar", $integrate_with_google_calendar, "user");

            //save calendar ids
            $calendar_ids_array = $this->request->getPost('calendar_id');
            if (!is_null($calendar_ids_array) && count($calendar_ids_array)) {
                //remove null value
                foreach ($calendar_ids_array as $key => $value) {
                    if (!get_array_value($calendar_ids_array, $key)) {
                        unset($calendar_ids_array[$key]);
                    }
                }

                $calendar_ids_array = array_unique($calendar_ids_array);
                $this->Settings_model->save_setting("user_" . $this->login_user->id . "_calendar_ids", serialize($calendar_ids_array), "user");
            }

            echo json_encode(array("success" => true, 'message' => app_lang('settings_updated')));
        }
    }

    function show_event_in_google_calendar($google_event_id = "") {
        if (!$google_event_id) {
            show_404();
        }

        $event_link = $this->Google_calendar_events->get_event_link($google_event_id, $this->login_user->id);
        $event_link ? app_redirect($event_link, true) : show_404();
    }

    /* upload a file */

    function upload_file() {
        upload_file_to_temp();
    }

    /* check valid file for events */

    function validate_events_file() {
        return validate_post_file($this->request->getPost("file_name"));
    }

    function file_preview($id = "", $key = "") {
        if ($id) {
            validate_numeric_value($id);
            $event_info = $this->Events_model->get_one($id);
            $files = unserialize($event_info->files);
            $file = get_array_value($files, $key);

            $file_name = get_array_value($file, "file_name");
            $file_id = get_array_value($file, "file_id");
            $service_type = get_array_value($file, "service_type");

            $view_data["file_url"] = get_source_url_of_file($file, get_setting("timeline_file_path"));
            $view_data["is_image_file"] = is_image_file($file_name);
            $view_data["is_iframe_preview_available"] = is_iframe_preview_available($file_name);
            $view_data["is_google_preview_available"] = is_google_preview_available($file_name);
            $view_data["is_viewable_video_file"] = is_viewable_video_file($file_name);
            $view_data["is_google_drive_file"] = ($file_id && $service_type == "google") ? true : false;
            $view_data["is_iframe_preview_available"] = is_iframe_preview_available($file_name);

            return $this->template->view("events/file_preview", $view_data);
        } else {
            show_404();
        }
    }

    function reminders() {
        $this->can_create_reminders();
        $view_data["project_id"] = $this->request->getPost("project_id");
        $view_data["client_id"] = $this->request->getPost("client_id");
        $view_data["lead_id"] = $this->request->getPost("lead_id");
        $view_data["ticket_id"] = $this->request->getPost("ticket_id");
        return $this->template->view("reminders/index", $view_data);
    }

    function reminders_list_data($type = "", $task_id = 0, $project_id = 0, $client_id = 0, $lead_id = 0, $ticket_id = 0) {
        $this->can_create_reminders();

        $options = array(
            "user_id" => $this->login_user->id,
            "type" => "reminder",
            "task_id" => $task_id,
            "project_id" => $project_id,
            "client_id" => $client_id,
            "lead_id" => $lead_id,
            "ticket_id" => $ticket_id,
        );

        if ($type !== "all") {
            $options["reminder_start_date_time"] = get_my_local_time("Y-m-d H:i") . ":00";
            $options["reminder_status"] = "new";
        }

        $list_data = $this->Events_model->get_details($options)->getResult();

        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_reminder_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    private function _make_reminder_row($data = array()) {
        $reminder_status_value = "done";

        if ($data->reminder_status === "done" || $data->reminder_status === "shown") {
            $reminder_status_value = "new";
        }

        $context_info = get_reminder_context_info($data);
        $context_icon = get_array_value($context_info, "context_icon");
        $context_icon = $context_icon ? "<i class='icon-14 text-off' data-feather='$context_icon'></i> " : "";
        $context_url = get_array_value($context_info, "context_url");
        $title_value = "<span class='strong'>$context_icon" . ($context_url ? anchor($context_url, $data->title) : link_it($data->title)) . "</span>";

        $icon = "";
        $target_date = "";
        if ($data->snoozing_time) {
            $icon = "<span class='icon-14 text-off'>" . view("reminders/svg_icons/snooze") . "</span>";
            $target_date = new \DateTime($data->snoozing_time);
        } else if ($data->recurring) {
            $icon = "<i class='icon-14 text-off' data-feather='repeat'></i>";

            if ($data->next_recurring_time) {
                $target_date = new \DateTime($data->next_recurring_time);
            }
        }

        if ($target_date) {
            //assign dedicated values to main start and end date time to work with existing method
            $data->start_date = $target_date->format("Y-m-d");
            $data->start_time = $target_date->format("H:i:s");
        }

        $data->end_date = $data->start_date;
        $time_value = view("events/event_time", array("model_info" => $data, "is_reminder" => true));
        $time_value = "<div class='small'>$icon " . $time_value . "</div>";

        //show left border for missed reminders
        $missed_reminder_class = "";
        $local_time = get_my_local_time("Y-m-d H:i") . ":00";

        if ($data->reminder_status === 'new' && ($data->start_date . ' ' . $data->start_time) < $local_time && $data->snoozing_time < $local_time && $data->next_recurring_time < $local_time) {
            $missed_reminder_class = "missed-reminder";
        }

        $title = "<span class='$missed_reminder_class'>" . $title_value . $time_value . "</span>";

        $delete = '<li role="presentation">' . js_anchor("<i data-feather='x' class='icon-16'></i>" . app_lang('delete'), array('title' => app_lang('delete_reminder'), "class" => "delete dropdown-item reminder-action", "data-id" => $data->id, "data-post-id" => $data->id, "data-action-url" => get_uri("events/delete"), "data-action" => "delete", "data-undo" => "0")) . '</li>';

        $status = '<li role="presentation">' . js_anchor("<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_done'), array('title' => app_lang('mark_as_done'), "class" => "dropdown-item reminder-action", "data-action-url" => get_uri("events/save_reminder_status/$data->id/done"), "data-action" => "delete", "data-undo" => "0")) . '</li>';
        if ($data->reminder_status === "done" || $data->reminder_status === "shown") {
            $status = "";
        }

        $options = '<span class="dropdown inline-block">
                        <div class="dropdown-toggle clickable p10" type="button" data-bs-toggle="dropdown" aria-expanded="true" data-bs-display="static">
                            <i data-feather="more-horizontal" class="icon-16"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end" role="menu">' . $status . $delete . '</ul>
                    </span>';

        if ($missed_reminder_class) {
            //show direct option to complete the missed reminders
            $options = js_anchor("<i data-feather='check-circle' class='icon-16'></i>", array('title' => app_lang('mark_as_done'), "class" => "reminder-action p10", "data-action-url" => get_uri("events/save_reminder_status/$data->id/done"), "data-action" => "delete", "data-undo" => "0"));
        }

        return array(
            $data->start_date . " " . $data->start_time, //for sort
            $title,
            $options
        );
    }

    private function can_access_this_reminder($reminder_info) {
        if ($reminder_info->created_by === $this->login_user->id) {
            //this user is the creator of the event/reminder
            return true;
        }

        if ($reminder_info->share_with) {
            //this user is not the creator of the event/reminder
            //check in shared users
            $shared_users = $this->Events_model->get_share_with_users_of_event($reminder_info)->getResult();
            foreach ($shared_users as $user) {
                if ($user->id === $this->login_user->id) {
                    return true;
                }
            }
        }

        app_redirect("forbidden");
    }

    private function can_create_reminders() {
        if (get_setting("module_reminder") && ($this->login_user->user_type === "staff" || ($this->login_user->user_type === "client" && get_setting("client_can_create_reminders")))) {
            return true;
        }

        app_redirect("forbidden");
    }

    function save_reminder_status($id = 0, $status = "") {
        $this->can_create_reminders();
        if (!$id) {
            show_404();
        }

        validate_numeric_value($id);

        if (!$status) {
            $this->validate_submitted_data(array(
                "value" => "required"
            ));
            $status = $this->request->getPost("value");
        }

        $reminder_info = $this->Events_model->get_one($id);
        $this->can_access_this_reminder($reminder_info);

        if ($reminder_info->share_with) {
            echo json_encode(array("success" => true, 'message' => app_lang('record_saved')));
        } else {
            if ($reminder_info->recurring && (!$reminder_info->no_of_cycles || $reminder_info->no_of_cycles_completed < $reminder_info->no_of_cycles) && ($status === "shown" || $status === "done")) {
                //calculate next recurring time on reminder action
                $next_recurring_time = add_period_to_date(is_null($reminder_info->next_recurring_time) ? ($reminder_info->start_date . " " . $reminder_info->start_time) : $reminder_info->next_recurring_time, $reminder_info->repeat_every, $reminder_info->repeat_type, "Y-m-d H:i:s");
                $data['next_recurring_time'] = $next_recurring_time;
                $data['no_of_cycles_completed'] = (int) $reminder_info->no_of_cycles_completed + 1;

                if ($next_recurring_time < get_my_local_time()) {
                    //if the next recurring time is a past date, mark it as done
                    $status = "done";
                } else {
                    //to remind again
                    $status = "new";
                }
            }

            $data["reminder_status"] = $status;

            $save_id = $this->Events_model->ci_save($data, $id);
            if ($save_id) {
                $reminder_info = $this->Events_model->get_one($id);
                echo json_encode(array("success" => true, "data" => $this->_make_reminder_row($reminder_info), 'id' => $save_id, 'message' => app_lang('record_saved')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            }
        }
    }

    function snooze_reminder() {
        $this->can_create_reminders();
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');

        $reminder_info = $this->Events_model->get_one($id);
        $this->can_access_this_reminder($reminder_info);
        if ($reminder_info->share_with) {
            app_redirect("forbidden");
        }

        $snooze_length = get_setting('user_' . $this->login_user->id . '_reminder_snooze_length');
        $snooze_length = $snooze_length ? $snooze_length : 5;

        $reminder_time = $reminder_info->start_date . " " . $reminder_info->start_time;
        if (!is_null($reminder_info->snoozing_time)) {
            $reminder_time = $reminder_info->snoozing_time;
        } else if (!is_null($reminder_info->next_recurring_time)) {
            $reminder_time = $reminder_info->next_recurring_time;
        }

        $data["snoozing_time"] = add_period_to_date($reminder_time, $snooze_length, "minutes", "Y-m-d H:i:s");

        $save_id = $this->Events_model->ci_save($data, $id);
        if ($save_id) {
            $reminder_info = $this->Events_model->get_one($id);
            echo json_encode(array("success" => true, "reminder_info" => $reminder_info, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function reminder_view() {
        $this->can_create_reminders();
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');

        $reminder_info = $this->Events_model->get_one($id);
        $this->can_access_this_reminder($reminder_info);

        $reminder_info->end_date = $reminder_info->start_date;
        $view_data["model_info"] = $reminder_info;
        return $this->template->view("reminders/view", $view_data);
    }

    function get_reminders_for_current_user() {
        $this->can_create_reminders();
        echo json_encode(array("success" => true, "reminders" => reminders_widget(true)));
    }

    function count_missed_reminders() {
        $this->can_create_reminders();
        $reminders = $this->Events_model->count_missed_reminders($this->login_user->id, $this->login_user->notification_checked_at);
        echo json_encode(array("success" => true, 'total_reminders' => $reminders));
    }

}

/* End of file events.php */
    /* Location: ./app/controllers/events.php */