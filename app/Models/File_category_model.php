<?php

namespace App\Models;

class File_category_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'file_category';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $file_category_table = $this->db->prefixTable('file_category');

        $where = "";

        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $file_category_table.id=$id";
        }

        $type = $this->_get_clean_value($options, "type");
        if ($type) {
            $where .= " AND $file_category_table.type='$type'";
        }

        $sql = "SELECT $file_category_table.*
        FROM $file_category_table
        WHERE $file_category_table.deleted=0 $where";
        return $this->db->query($sql);
    }

}
