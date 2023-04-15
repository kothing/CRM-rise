<?php

namespace App\Models;

class Pin_comments_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'pin_comments';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $pin_comments_table = $this->db->prefixTable('pin_comments');
        $users_table = $this->db->prefixTable('users');
        $project_comments_table = $this->db->prefixTable('project_comments');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $pin_comments_table.id=$id";
        }

        $pinned_by = $this->_get_clean_value($options, "pinned_by");
        if ($pinned_by) {
            $where .= " AND $pin_comments_table.pinned_by=$pinned_by";
        }

        $task_id = $this->_get_clean_value($options, "task_id");
        if ($task_id) {
            $where .= " AND $project_comments_table.task_id=$task_id";
        }

        $sql = "SELECT $pin_comments_table.*, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS pinned_by_user, $users_table.image as pinned_by_avatar
        FROM $pin_comments_table
        LEFT JOIN $users_table ON $users_table.id= $pin_comments_table.pinned_by
        LEFT JOIN $project_comments_table ON $project_comments_table.id= $pin_comments_table.project_comment_id
        WHERE $pin_comments_table.deleted=0 $where";
        return $this->db->query($sql);
    }

}
