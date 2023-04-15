<?php

$show_in_kanban = get_setting("show_in_kanban");
$show_in_kanban_items = explode(',', $show_in_kanban);

foreach ($tasks as $task) {
    $task_labels = "";
    $task_checklist_status = "";
    $checklist_label_color = "#6690F4";

    if ($task->total_checklist_checked <= 0) {
        $checklist_label_color = "#E18A00";
    } else if ($task->total_checklist_checked == $task->total_checklist) {
        $checklist_label_color = "#01B392";
    }

    if ($task->priority_id) {
        $task_labels .= "<div class='meta float-start mr5'><span class='sub-task-icon priority-badge' data-bs-toggle='tooltip' title='" . app_lang("priority") . ": " . $task->priority_title . "' style='background: $task->priority_color'><i data-feather='$task->priority_icon' class='icon-14'></i></span></div>";
    }

    if ($task->total_checklist) {
        $task_checklist_status .= "<div class='meta float-start badge rounded-pill mr5' style='background-color:$checklist_label_color'><span data-bs-toggle='tooltip' title='" . app_lang("checklist_status") . "'><i data-feather='check' class='icon-14'></i> $task->total_checklist_checked/$task->total_checklist</span></div>";
    }

    $task_labels_data = make_labels_view_data($task->labels_list);
    $sub_task_icon = "";
    if ($task->parent_task_id) {
        $sub_task_icon = "<span class='sub-task-icon mr5' title='" . app_lang("sub_task") . "'><i data-feather='git-merge' class='icon-14'></i></span>";
    }

    if ($task_labels_data) {
        $task_labels .= "<div class='meta float-start mr5'>$task_labels_data</div>";
    }

    $unread_comments_class = "";
    if (isset($task->unread) && $task->unread && $task->unread != "0") {
        $unread_comments_class = "unread-comments-of-kanban unread";
    }

    $batch_operation_checkbox = "";
    if ($login_user->user_type == "staff" && $can_edit_tasks && $project_id) {
        $batch_operation_checkbox = "<span data-act='batch-operation-task-checkbox' title='" . app_lang("batch_update") . "' class='checkbox-blank-sm float-end invisible'></span>";
    }

    $toggle_sub_task_icon = "";

    if ($task->has_sub_tasks) {
        $toggle_sub_task_icon = "<span class='filter-sub-task-kanban-button clickable float-end ml5' title='" . app_lang("show_sub_tasks") . "' main-task-id= '#$task->id'><i data-feather='filter' class='icon-14'></i></span>";
    }

    $disable_dragging = can_edit_this_task_status($task->assigned_to) ? "" : "disable-dragging";

    //custom fields to show in kanban
    $kanban_custom_fields_data = "";
    $kanban_custom_fields = get_custom_variables_data("tasks", $task->id, $login_user->is_admin);
    if ($kanban_custom_fields) {
        foreach ($kanban_custom_fields as $kanban_custom_field) {
            $kanban_custom_fields_data .= "<div class='mt5 font-12'>" . get_array_value($kanban_custom_field, "custom_field_title") . ": " . view("custom_fields/output_" . get_array_value($kanban_custom_field, "custom_field_type"), array("value" => get_array_value($kanban_custom_field, "value"))) . "</div>";
        }
    }

    $start_date = "";
    if ($task->start_date) {
        $start_date = "<div class='mt10 font-12 float-start' title='" . app_lang("start_date") . "'><i data-feather='calendar' class='icon-14 text-off mr5'></i> " . format_to_date($task->start_date, false) . "</div>";
    }

    $deadline_text = "-";
    if ($task->deadline && is_date_exists($task->deadline)) {
        $deadline_text = format_to_date($task->deadline, false);
        if (get_my_local_time("Y-m-d") > $task->deadline && $task->status_id != "3") {
            $deadline_text = "<span class='text-danger'>" . $deadline_text . "</span> ";
        } else if (get_my_local_time("Y-m-d") == $task->deadline && $task->status_id != "3") {
            $deadline_text = "<span class='text-warning'>" . $deadline_text . "</span> ";
        }
    }

    $end_date = "";
    if ($task->deadline) {
        $end_date = "<div class='mt10 font-12 float-end' title='" . app_lang("deadline") . "'><i data-feather='calendar' class='icon-14 text-off mr5'></i> " . $deadline_text . "</div>";
    }

    $task_id = "";
    $parent_task_id = "";
    if (in_array("id", $show_in_kanban_items)) {
        $task_id = $task->id . ". ";
        $parent_task_id = $task->parent_task_id . ". ";
    }

    $project_name = "";
    if (in_array("project_name", $show_in_kanban_items)) {
        $project_name = "<div class='clearfix mt5 text-truncate'><i data-feather='grid' class='icon-14 text-off mr5'></i> " . $task->project_title . "</div>";
    }

    $client_name = "";
    if (in_array("client_name", $show_in_kanban_items) && $task->project_type == "client_project") {
        $client_name = "<div class='clearfix mt5 text-truncate'><i data-feather='briefcase' class='icon-14 text-off mr5'></i> " . $task->client_name . "</div>";
    }

    $sub_task_status = "";
    $sub_task_label_color = "#6690F4";

    if ($task->total_sub_tasks_done <= 0) {
        $sub_task_label_color = "#E18A00";
    } else if ($task->total_sub_tasks_done == $task->total_sub_tasks) {
        $sub_task_label_color = "#01B392";
    }

    if ($task->total_sub_tasks) {
        $sub_task_status .= "<div class='meta float-start badge rounded-pill' style='background-color:$sub_task_label_color'><span data-bs-toggle='tooltip' title='" . app_lang("sub_task_status") . "'><i data-feather='git-merge' class='icon-14'></i> " . ($task->total_sub_tasks_done ? $task->total_sub_tasks_done : 0) . "/$task->total_sub_tasks</span></div>";
    }

    $parent_task = "";
    if (in_array("parent_task", $show_in_kanban_items) && $task->parent_task_title) {
        $parent_task = "<div class='mt5 text-truncate text-off'>" . $parent_task_id . $task->parent_task_title . "</div>";
    }

    echo modal_anchor(get_uri("projects/task_view"), "<span class='avatar'>" .
            "<img src='" . get_avatar($task->assigned_to_avatar) . "'>" .
            "</span>" . $sub_task_icon . $task_id . $task->title . $toggle_sub_task_icon . $batch_operation_checkbox . "<div class='clearfix'>" . $start_date . $end_date . "</div>" . $project_name . $client_name . $kanban_custom_fields_data .
            $task_labels . $task_checklist_status . $sub_task_status . "<div class='clearfix'></div>" . $parent_task, array("class" => "kanban-item d-block $disable_dragging $unread_comments_class", "data-status_id" => $task->status_id,  "data-id" => $task->id, "data-project_id" => $task->project_id, "data-sort" => $task->new_sort, "data-post-id" => $task->id, "title" => app_lang('task_info') . " #$task->id", "data-modal-lg" => "1"));
}