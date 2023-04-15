<?php

namespace App\Models;

class Proposal_items_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'proposal_items';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $proposal_items_table = $this->db->prefixTable('proposal_items');
        $proposals_table = $this->db->prefixTable('proposals');
        $clients_table = $this->db->prefixTable('clients');
        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $proposal_items_table.id=$id";
        }
        $proposal_id = $this->_get_clean_value($options, "proposal_id");
        if ($proposal_id) {
            $where .= " AND $proposal_items_table.proposal_id=$proposal_id";
        }

        $sql = "SELECT $proposal_items_table.*, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$proposals_table.client_id limit 1) AS currency_symbol
        FROM $proposal_items_table
        LEFT JOIN $proposals_table ON $proposals_table.id=$proposal_items_table.proposal_id
        WHERE $proposal_items_table.deleted=0 $where
        ORDER BY $proposal_items_table.sort ASC";
        return $this->db->query($sql);
    }

}
