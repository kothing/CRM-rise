<?php

namespace App\Models;

class Estimate_comments_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'estimate_comments';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $estimate_comments_table = $this->db->prefixTable('estimate_comments');
        $users_table = $this->db->prefixTable('users');
        $where = "";
        $sort = "ASC";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $estimate_comments_table.id=$id";
        }

        $estimate_id = $this->_get_clean_value($options, "estimate_id");
        if ($estimate_id) {
            $where .= " AND $estimate_comments_table.estimate_id=$estimate_id";
        }

        $sort_decending = $this->_get_clean_value($options, "sort_as_decending");
        if ($sort_decending) {
            $sort = "DESC";
        }



        $sql = "SELECT $estimate_comments_table.*, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS created_by_user, $users_table.image as created_by_avatar, $users_table.user_type
        FROM $estimate_comments_table
        LEFT JOIN $users_table ON $users_table.id= $estimate_comments_table.created_by
        WHERE $estimate_comments_table.deleted=0 $where
        ORDER BY $estimate_comments_table.created_at $sort";

        return $this->db->query($sql);
    }

}
