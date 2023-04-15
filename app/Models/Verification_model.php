<?php

namespace App\Models;

class Verification_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'verification';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $verification_table = $this->db->prefixTable("verification");

        $where = "";

        $code = $this->_get_clean_value($options, "code");
        if ($code) {
            $where .= " AND $verification_table.code='$code'";
        }

        $type = $this->_get_clean_value($options, "type");
        if ($type) {
            $where .= " AND $verification_table.type='$type'";
        }

        $sql = "SELECT $verification_table.*
        FROM $verification_table
        WHERE $verification_table.deleted=0 $where";
        return $this->db->query($sql);
    }

}
