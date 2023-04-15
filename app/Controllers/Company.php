<?php

namespace App\Controllers;

class Company extends Security_Controller {

    private $Company_model;

    function __construct() {
        parent::__construct();
        $this->access_only_admin_or_settings_admin();
        $this->Company_model = model('App\Models\Company_model');
    }

    function index() {
        return $this->template->rander("company/index");
    }

    function modal_form() {
        $this->validate_submitted_data(array(
            "id" => "numeric"
        ));

        $view_data['model_info'] = $this->Company_model->get_one($this->request->getPost('id'));
        return $this->template->view('company/modal_form', $view_data);
    }

    function save() {
        $this->validate_submitted_data(array(
            "id" => "numeric",
            "name" => "required"
        ));

        $is_default = $this->request->getPost('is_default');
        $data = array(
            "name" => $this->request->getPost('name'),
            "address" => $this->request->getPost('address'),
            "phone" => $this->request->getPost('phone'),
            "email" => $this->request->getPost('email'),
            "website" => $this->request->getPost('website'),
            "vat_number" => $this->request->getPost('vat_number'),
            "is_default" => $is_default ? $is_default : 0,
        );

        $id = $this->request->getPost('id');
        $company_info = $this->Company_model->get_one($id);

        $save_id = $this->Company_model->ci_save($data, $id);

        if ($save_id) {
            if ($is_default) {
                //remove if there has any other default company
                $this->Company_model->remove_other_default_company($save_id);
            }

            $target_path = get_setting("system_file_path");
            $files_data = move_files_from_temp_dir_to_permanent_dir($target_path, "company_$save_id");
            $logo = unserialize($files_data);

            if ($logo) {
                //delete old file
                if ($company_info->logo) {
                    $files = unserialize($company_info->logo);
                    foreach ($files as $file) {
                        delete_app_files(get_setting("system_file_path"), array($file));
                    }
                }

                $data["logo"] = serialize($logo);

                $this->Company_model->ci_save($data, $save_id);
            }

            echo json_encode(array("success" => true, "data" => $this->_row_data($save_id), 'id' => $save_id, 'message' => app_lang('record_saved')));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
        }
    }

    function delete() {
        $this->validate_submitted_data(array(
            "id" => "numeric|required"
        ));

        $id = $this->request->getPost('id');
        $company_info = $this->Company_model->get_one($id);
        if ($company_info->is_default) {
            //default company can't be deleted
            show_404();
        }

        if ($this->request->getPost('undo')) {
            if ($this->Company_model->delete($id, true)) {
                echo json_encode(array("success" => true, "data" => $this->_row_data($id), "message" => app_lang('record_undone')));
            } else {
                echo json_encode(array("success" => false, app_lang('error_occurred')));
            }
        } else {
            if ($this->Company_model->delete($id)) {
                echo json_encode(array("success" => true, 'message' => app_lang('record_deleted')));
            } else {
                echo json_encode(array("success" => false, 'message' => app_lang('record_cannot_be_deleted')));
            }
        }
    }

    function list_data() {
        $list_data = $this->Company_model->get_details()->getResult();
        $result = array();
        foreach ($list_data as $data) {
            $result[] = $this->_make_row($data);
        }
        echo json_encode(array("data" => $result));
    }

    private function _row_data($id) {
        $options = array("id" => $id);
        $data = $this->Company_model->get_details($options)->getRow();
        return $this->_make_row($data);
    }

    private function _make_row($data) {
        $default_company = "";
        $delete = js_anchor("<i data-feather='x' class='icon-16'></i>", array('title' => app_lang('delete_company'), "class" => "delete", "data-id" => $data->id, "data-action-url" => get_uri("company/delete"), "data-action" => "delete"));
        if ($data->is_default) {
            $default_company = " <span class='bg-info badge text-white'>" . app_lang('default_company') . "</span>";
            $delete = "";
        }

        return array(
            $data->name . $default_company,
            $data->address,
            $data->phone,
            $data->email,
            $data->website,
            $data->vat_number,
            modal_anchor(get_uri("company/modal_form"), "<i data-feather='edit' class='icon-16'></i>", array("class" => "edit", "title" => app_lang('edit_company'), "data-post-id" => $data->id))
            . $delete
        );
    }

    function upload_file() {
        $this->access_only_admin_or_settings_admin();
        upload_file_to_temp();
    }

    function validate_company_file() {
        $this->access_only_admin_or_settings_admin();
        $file_name = $this->request->getPost("file_name");
        if (!is_valid_file_to_upload($file_name)) {
            echo json_encode(array("success" => false, 'message' => app_lang('invalid_file_type')));
            exit();
        }

        if (is_image_file($file_name)) {
            echo json_encode(array("success" => true));
        } else {
            echo json_encode(array("success" => false, 'message' => app_lang('please_upload_valid_image_files')));
        }
    }

}

/* End of file company.php */
/* Location: ./app/controllers/company.php */