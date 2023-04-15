<?php

namespace App\Models;

class Proposal_templates_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'proposal_templates';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $proposal_templates_table = $this->db->prefixTable('proposal_templates');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $proposal_templates_table.id=$id";
        }

        $sql = "SELECT $proposal_templates_table.*
        FROM $proposal_templates_table
        WHERE $proposal_templates_table.deleted=0 $where";
        return $this->db->query($sql);
    }

}
