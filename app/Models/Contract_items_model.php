<?php

namespace App\Models;

class Contract_items_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'contract_items';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $contract_items_table = $this->db->prefixTable('contract_items');
        $contracts_table = $this->db->prefixTable('contracts');
        $clients_table = $this->db->prefixTable('clients');
        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $contract_items_table.id=$id";
        }
        $contract_id = $this->_get_clean_value($options, "contract_id");
        if ($contract_id) {
            $where .= " AND $contract_items_table.contract_id=$contract_id";
        }

        $sql = "SELECT $contract_items_table.*, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$contracts_table.client_id limit 1) AS currency_symbol
        FROM $contract_items_table
        LEFT JOIN $contracts_table ON $contracts_table.id=$contract_items_table.contract_id
        WHERE $contract_items_table.deleted=0 $where
        ORDER BY $contract_items_table.sort ASC";
        return $this->db->query($sql);
    }

}
