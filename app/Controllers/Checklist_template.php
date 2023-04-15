<?php

namespace App\Controllers;

class Checklist_template extends Security_Controller {

    function __construct() {
        parent::__construct();
    }

    //load checklist template list view
    function index() {
        $this->access_only_admin_or_settings_admin();
        return $this->template->view("checklist_template/index");
    }

    //load checklist template add/edit modal form
    function modal_form() {
        $this->access_only_admin_or_settings_admin();
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data['model_info'] = $this->Checklist_template_model->get_one($this->request->getPost('id'));
        return $this->template->view('checklist_template/modal_form', $view_data);
    }

    //save checklist template 
    function save() {
        $this->access_only_admin_or_settings_admin();

        $this->validate_submitted_data(array(
            "id" => "numeric",
            "title" => "required"
        ));

        $id = $this->request->getPost('id');
        $data = array(
            "title" => $this->request->getPost('title')
        );
        $save_id = $this->Checklist_template_model->ci_save($data, $id);
        if ($save_id) {
            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    //delete/undo checklist template 
    function delete() {
        $this->access_only_admin_or_settings_admin();
        $this->validate_submitted_data(array(
            "id" => "required|numeric"
        ));

        $id = $this->request->getPost('id');
        if ($this->request->getPost('undo')) {
            if ($this->Checklist_template_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Checklist_template_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    //get data for checklist template list
    function list_data() {
        $this->access_only_admin_or_settings_admin();
        $list_data = $this->Checklist_template_model->get_details()->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    //get checklist template list row
    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Checklist_template_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    //prepare checklist template list row
    private function _make_row($data) {
        return array($data->title,
            modal_anchor(get_uri("checklist_template/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_checklist_template'), "data-post-id" => $data->id))
            . js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_checklist_template'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("checklist_template/delete"), "data-action" => "delete"))
        );
    }

    //prepare suggestion of checklist template
    function get_checklist_template_suggestion() {
        $project_id = $this->request->getPost("project_id");
        $this->init_project_permission_checker($project_id);
        if (!$this->can_edit_tasks()) {
            app_redirect("forbidden");
        }

        $key = $this->request->getPost("q");
        $suggestion = array();

        $items = $this->Checklist_template_model->get_template_suggestion($key);

        foreach ($items as $item) {
            $suggestion[] = array("id" => $item->title, "text" => $item->title);
        }

        echo json_encode($suggestion);
    }

}

/* End of file Task_checklist_template.php */
/* Location: ./app/controllers/Task_checklist_template.php */