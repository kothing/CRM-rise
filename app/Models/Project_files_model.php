<?php

namespace App\Models;

class Project_files_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'project_files';
        parent::__construct($this->table);
        parent::init_activity_log("project_file", "file_name", "project", "project_id");
    }

    function schema() {
        return array(
            "id" => array(
                "label" => app_lang("id"),
                "type" => "int"
            ),
            "file_name" => array(
                "label" => app_lang("file_name"),
                "type" => "text"
            ),
            "project_id" => array(
                "label" => app_lang("project"),
                "type" => "foreign_key",
                "linked_model" => model("App\Models\Projects_model"),
                "label_fields" => array("title"),
            ),
            "start_date" => array(
                "label" => app_lang("start_date"),
                "type" => "date"
            ),
            "end_date" => array(
                "label" => app_lang("end_date"),
                "type" => "date"
            ),
            "deleted" => array(
                "label" => app_lang("deleted"),
                "type" => "int"
            ),
            "category_id" => array(
                "label" => app_lang("category"),
                "type" => "foreign_key",
                "linked_model" => model("App\Models\File_category_model"),
                "label_fields" => array("name"),
            ),
            "description" => array(
                "label" => app_lang("description"),
                "type" => "text"
            )
        );
    }

    function get_details($options = array()) {
        $project_files_table = $this->db->prefixTable('project_files');
        $users_table = $this->db->prefixTable('users');
        $file_category_table = $this->db->prefixTable('file_category');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $project_files_table.id=$id";
        }

        $project_id = $this->_get_clean_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $project_files_table.project_id=$project_id";
        }

        $category_id = $this->_get_clean_value($options, "category_id");
        if ($category_id) {
            $where .= " AND $project_files_table.category_id=$category_id";
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("project_files", $custom_fields, $project_files_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $sql = "SELECT $project_files_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS uploaded_by_user_name, $users_table.image AS uploaded_by_user_image, $users_table.user_type AS uploaded_by_user_type, $file_category_table.name AS category_name $select_custom_fieds
        FROM $project_files_table
        LEFT JOIN $users_table ON $users_table.id= $project_files_table.uploaded_by
        LEFT JOIN $file_category_table ON $file_category_table.id= $project_files_table.category_id
        $join_custom_fieds
        WHERE $project_files_table.deleted=0 $where $custom_fields_where";
        return $this->db->query($sql);
    }

    function get_files($ids = array()) {
        $string_of_ids = implode(",", $ids);
        $string_of_ids = $string_of_ids ? $this->db->escapeString($string_of_ids) : $string_of_ids;

        $project_files_table = $this->db->prefixTable("project_files");
        $sql = "SELECT * FROM $project_files_table WHERE deleted=0 AND FIND_IN_SET($project_files_table.id, '$string_of_ids')";
        if ($this->db->query($sql)->resultID->num_rows > 0) {
            return $this->db->query($sql);
        }
    }

}
