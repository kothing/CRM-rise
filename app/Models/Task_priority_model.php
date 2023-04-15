<?php

namespace App\Models;

class Task_priority_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'task_priority';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $task_priority_table = $this->db->prefixTable('task_priority');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $task_priority_table.id=$id";
        }

        $sql = "SELECT $task_priority_table.*
        FROM $task_priority_table
        WHERE $task_priority_table.deleted=0 $where";
        return $this->db->query($sql);
    }

}
