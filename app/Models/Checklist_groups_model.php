<?php

namespace App\Models;

class Checklist_groups_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'checklist_groups';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $checklist_groups_table = $this->db->prefixTable('checklist_groups');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $checklist_groups_table.id=$id";
        }

        $sql = "SELECT $checklist_groups_table.*
        FROM $checklist_groups_table
        WHERE $checklist_groups_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_group_suggestion($keyword = "") {
        $checklist_groups_table = $this->db->prefixTable('checklist_groups');

        if ($keyword) {
            $keyword = $this->db->escapeString($keyword);
        }

        $where = "";

        $sql = "SELECT  $checklist_groups_table.*
        FROM $checklist_groups_table
        WHERE $checklist_groups_table.deleted=0  AND $checklist_groups_table.title LIKE '%$keyword%' $where";

        return $this->db->query($sql)->getResult();
    }

    function get_templates($options = array()) {
        $checklist_items_table = $this->db->prefixTable('checklist_items');
        $checklist_groups_table = $this->db->prefixTable('checklist_groups');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $checklist_items_table.id=$id";
        }

        $sql = "SELECT $checklist_items_table.*
                FROM $checklist_items_table
                WHERE $checklist_items_table.id IN(SELECT $checklist_groups_table.checklists FROM $checklist_groups_table WHERE $checklist_groups_table.id=$id )";
        return $this->db->query($sql);
    }

}
