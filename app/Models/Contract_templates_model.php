<?php

namespace App\Models;

class Contract_templates_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'contract_templates';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $contract_templates_table = $this->db->prefixTable('contract_templates');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where = " AND $contract_templates_table.id=$id";
        }

        $sql = "SELECT $contract_templates_table.*
        FROM $contract_templates_table
        WHERE $contract_templates_table.deleted=0 $where";
        return $this->db->query($sql);
    }

}
