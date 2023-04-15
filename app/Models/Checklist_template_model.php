<?php

namespace App\Models;

class Checklist_template_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'checklist_template';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $checklist_template_table = $this->db->prefixTable('checklist_template');
        $checklist_groups_table = $this->db->prefixTable('checklist_groups');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $checklist_template_table.id=$id";
        }

        $group_id = $this->_get_clean_value($options, "group_id");
        if ($group_id) {
            $where .= " AND FIND_IN_SET($checklist_template_table.id, (SELECT $checklist_groups_table.checklists FROM $checklist_groups_table WHERE $checklist_groups_table.id=$group_id ))";
        }

        $sql = "SELECT $checklist_template_table.*
        FROM $checklist_template_table
        WHERE $checklist_template_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_template_suggestion($keyword = "") {
        $checklist_template_table = $this->db->prefixTable('checklist_template');

        if ($keyword) {
            $keyword = $this->db->escapeString($keyword);
        }

        $where = "";

        $sql = "SELECT $checklist_template_table.title
        FROM $checklist_template_table
        WHERE $checklist_template_table.deleted=0  AND $checklist_template_table.title LIKE '%$keyword%' $where";

        return $this->db->query($sql)->getResult();
    }

    function get_checklists($checklist_ids = "") {
        $checklist_template_table = $this->db->prefixTable('checklist_template');

        $sql = "SELECT $checklist_template_table.*
        FROM $checklist_template_table
        WHERE $checklist_template_table.deleted=0 AND FIND_IN_SET($checklist_template_table.id, '$checklist_ids')";
        return $this->db->query($sql);
    }

}
