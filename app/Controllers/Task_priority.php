<?php

namespace App\Controllers;

class Task_priority extends Security_Controller {

    private $Task_priority_model;

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->Task_priority_model = model("App\Models\Task_priority_model");
    }

    function index() {
        return $this->template->view("task_priority/index");
    }

    function modal_form() {
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data['model_info'] = $this->Task_priority_model->get_one($this->request->getPost('id'));
        return $this->template->view('task_priority/modal_form', $view_data);
    }

    function save() {
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $id = $this->request->getPost('id');
        $data = array(
            "title" => $this->request->getPost('title'),
            "color" => $this->request->getPost('color'),
            "icon" => $this->request->getPost('icon'),
        );

        $save_id = $this->Task_priority_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function delete() {
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Task_priority_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Task_priority_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    function list_data() {
        $list_data = $this->Task_priority_model->get_details()->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Task_priority_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    private function _make_row($data) {
        $edit = modal_anchor(get_uri("task_priority/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_task_priority'), "data-post-id" => $data->id));

        $delete = js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_task_priority'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("task_priority/delete"), "data-action" => "delete"));

        return array(
            "<span class='sub-task-icon priority-badge' data-id='$data->id' style='background: $data->color'><i data-feather='$data->icon' class='icon-14'></i></span> <span class='ml5'>$data->title</span>",
            $edit . $delete
        );
    }

}

/* End of file task_priority.php */
/* Location: ./app/controllers/task_priority.php */