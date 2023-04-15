<?php

namespace App\Models;

class Proposals_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'proposals';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $proposals_table = $this->db->prefixTable('proposals');
        $clients_table = $this->db->prefixTable('clients');
        $taxes_table = $this->db->prefixTable('taxes');
        $proposal_items_table = $this->db->prefixTable('proposal_items');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $proposals_table.id=$id";
        }
        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $proposals_table.client_id=$client_id";
        }

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($proposals_table.proposal_date BETWEEN '$start_date' AND '$end_date') ";
        }

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*IFNULL(items_table.proposal_value,0))";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*IFNULL(items_table.proposal_value,0))";

        $discountable_proposal_value = "IF($proposals_table.discount_type='after_tax', (IFNULL(items_table.proposal_value,0) + $after_tax_1 + $after_tax_2), IFNULL(items_table.proposal_value,0) )";

        $discount_amount = "IF($proposals_table.discount_amount_type='percentage', IFNULL($proposals_table.discount_amount,0)/100* $discountable_proposal_value, $proposals_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* (IFNULL(items_table.proposal_value,0)- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* (IFNULL(items_table.proposal_value,0)- $discount_amount))";

        $proposal_value_calculation = "(
            IFNULL(items_table.proposal_value,0)+
            IF($proposals_table.discount_type='before_tax',  ($before_tax_1+ $before_tax_2), ($after_tax_1 + $after_tax_2))
            - $discount_amount
           )";

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND $proposals_table.status='$status'";
        }

        $exclude_draft = $this->_get_clean_value($options, "exclude_draft");
        if ($exclude_draft) {
            $where .= " AND $proposals_table.status!='draft' ";
        }


        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("proposals", $custom_fields, $proposals_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");



        $sql = "SELECT $proposals_table.*, $clients_table.currency, $clients_table.currency_symbol, $clients_table.company_name, $clients_table.is_lead,
           CONCAT($users_table.first_name, ' ',$users_table.last_name) AS signer_name, $users_table.email AS signer_email,
           $proposal_value_calculation AS proposal_value, tax_table.percentage AS tax_percentage, tax_table2.percentage AS tax_percentage2 $select_custom_fieds
        FROM $proposals_table
        LEFT JOIN $clients_table ON $clients_table.id= $proposals_table.client_id
        LEFT JOIN $users_table ON $users_table.id= $proposals_table.accepted_by
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $proposals_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $proposals_table.tax_id2 
        LEFT JOIN (SELECT proposal_id, SUM(total) AS proposal_value FROM $proposal_items_table WHERE deleted=0 GROUP BY proposal_id) AS items_table ON items_table.proposal_id = $proposals_table.id 
        $join_custom_fieds
        WHERE $proposals_table.deleted=0 $where $custom_fields_where";
        return $this->db->query($sql);
    }

    function get_proposal_total_summary($proposal_id = 0) {
        $proposal_items_table = $this->db->prefixTable('proposal_items');
        $proposals_table = $this->db->prefixTable('proposals');
        $clients_table = $this->db->prefixTable('clients');
        $taxes_table = $this->db->prefixTable('taxes');

        $item_sql = "SELECT SUM($proposal_items_table.total) AS proposal_subtotal
        FROM $proposal_items_table
        LEFT JOIN $proposals_table ON $proposals_table.id= $proposal_items_table.proposal_id    
        WHERE $proposal_items_table.deleted=0 AND $proposal_items_table.proposal_id=$proposal_id AND $proposals_table.deleted=0";
        $item = $this->db->query($item_sql)->getRow();


        $proposal_sql = "SELECT $proposals_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2
        FROM $proposals_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $proposals_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $proposals_table.tax_id2
        WHERE $proposals_table.deleted=0 AND $proposals_table.id=$proposal_id";
        $proposal = $this->db->query($proposal_sql)->getRow();

        $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id=$proposal->client_id";
        $client = $this->db->query($client_sql)->getRow();


        $result = new \stdClass();
        $result->proposal_subtotal = $item->proposal_subtotal;
        $result->tax_percentage = $proposal->tax_percentage;
        $result->tax_percentage2 = $proposal->tax_percentage2;
        $result->tax_name = $proposal->tax_name;
        $result->tax_name2 = $proposal->tax_name2;
        $result->tax = 0;
        $result->tax2 = 0;

        $proposal_subtotal = $result->proposal_subtotal;
        $proposal_subtotal_for_taxes = $proposal_subtotal;
        if ($proposal->discount_type == "before_tax") {
            $proposal_subtotal_for_taxes = $proposal_subtotal - ($proposal->discount_amount_type == "percentage" ? ($proposal_subtotal * ($proposal->discount_amount / 100)) : $proposal->discount_amount);
        }

        if ($proposal->tax_percentage) {
            $result->tax = $proposal_subtotal_for_taxes * ($proposal->tax_percentage / 100);
        }
        if ($proposal->tax_percentage2) {
            $result->tax2 = $proposal_subtotal_for_taxes * ($proposal->tax_percentage2 / 100);
        }
        $proposal_total = $item->proposal_subtotal + $result->tax + $result->tax2;

        //get discount total
        $result->discount_total = 0;
        if ($proposal->discount_type == "after_tax") {
            $proposal_subtotal = $proposal_total;
        }

        $result->discount_total = $proposal->discount_amount_type == "percentage" ? ($proposal_subtotal * ($proposal->discount_amount / 100)) : $proposal->discount_amount;

        $result->discount_type = $proposal->discount_type;

        $result->discount_total = is_null($result->discount_total) ? 0 : $result->discount_total;
        $result->proposal_total = $proposal_total - number_format($result->discount_total, 2, ".", "");

        $result->currency_symbol = $client->currency_symbol ? $client->currency_symbol : get_setting("currency_symbol");
        $result->currency = $client->currency ? $client->currency : get_setting("default_currency");
        return $result;
    }

    //get proposal last id
    function get_proposal_last_id() {
        $proposals_table = $this->db->prefixTable('proposals');

        $sql = "SELECT MAX($proposals_table.id) AS last_id FROM $proposals_table";

        return $this->db->query($sql)->getRow()->last_id;
    }

    //save initial number of proposal
    function save_initial_number_of_proposal($value) {
        $proposals_table = $this->db->prefixTable('proposals');

        $sql = "ALTER TABLE $proposals_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }

}
