<?php

namespace App\Models;

class Contracts_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'contracts';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $contracts_table = $this->db->prefixTable('contracts');
        $clients_table = $this->db->prefixTable('clients');
        $taxes_table = $this->db->prefixTable('taxes');
        $contract_items_table = $this->db->prefixTable('contract_items');
        $users_table = $this->db->prefixTable('users');
        $projects_table = $this->db->prefixTable('projects');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $contracts_table.id=$id";
        }
        $client_id = $this->_get_clean_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $contracts_table.client_id=$client_id";
        }

        $start_date = $this->_get_clean_value($options, "start_date");
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($contracts_table.contract_date BETWEEN '$start_date' AND '$end_date') ";
        }
        
        $project_id = $this->_get_clean_value($options, "project_id");
        if ($project_id) {
            $where .= " AND $contracts_table.project_id=$project_id";
        }

        $after_tax_1 = "(IFNULL(tax_table.percentage,0)/100*IFNULL(items_table.contract_value,0))";
        $after_tax_2 = "(IFNULL(tax_table2.percentage,0)/100*IFNULL(items_table.contract_value,0))";

        $discountable_contract_value = "IF($contracts_table.discount_type='after_tax', (IFNULL(items_table.contract_value,0) + $after_tax_1 + $after_tax_2), IFNULL(items_table.contract_value,0) )";

        $discount_amount = "IF($contracts_table.discount_amount_type='percentage', IFNULL($contracts_table.discount_amount,0)/100* $discountable_contract_value, $contracts_table.discount_amount)";

        $before_tax_1 = "(IFNULL(tax_table.percentage,0)/100* (IFNULL(items_table.contract_value,0)- $discount_amount))";
        $before_tax_2 = "(IFNULL(tax_table2.percentage,0)/100* (IFNULL(items_table.contract_value,0)- $discount_amount))";

        $contract_value_calculation = "(
            IFNULL(items_table.contract_value,0)+
            IF($contracts_table.discount_type='before_tax',  ($before_tax_1+ $before_tax_2), ($after_tax_1 + $after_tax_2))
            - $discount_amount
           )";

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND $contracts_table.status='$status'";
        }

        $exclude_draft = $this->_get_clean_value($options, "exclude_draft");
        if ($exclude_draft) {
            $where .= " AND $contracts_table.status!='draft' ";
        }


        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("contracts", $custom_fields, $contracts_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");



        $sql = "SELECT $contracts_table.*, $clients_table.currency, $clients_table.currency_symbol, $clients_table.company_name, $clients_table.is_lead, $projects_table.title AS project_title, 
           CONCAT($users_table.first_name, ' ',$users_table.last_name) AS signer_name, $users_table.email AS signer_email,
           IF($contracts_table.staff_signed_by, (SELECT CONCAT($users_table.first_name, ' ',$users_table.last_name) FROM $users_table WHERE $users_table.id=$contracts_table.staff_signed_by), '') AS staff_signer_name,
           $contract_value_calculation AS contract_value, tax_table.percentage AS tax_percentage, tax_table2.percentage AS tax_percentage2 $select_custom_fieds
        FROM $contracts_table
        LEFT JOIN $clients_table ON $clients_table.id= $contracts_table.client_id
        LEFT JOIN $users_table ON $users_table.id= $contracts_table.accepted_by
        LEFT JOIN $projects_table ON $projects_table.id= $contracts_table.project_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $contracts_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $contracts_table.tax_id2 
        LEFT JOIN (SELECT contract_id, SUM(total) AS contract_value FROM $contract_items_table WHERE deleted=0 GROUP BY contract_id) AS items_table ON items_table.contract_id = $contracts_table.id 
        $join_custom_fieds
        WHERE $contracts_table.deleted=0 $where $custom_fields_where";
        return $this->db->query($sql);
    }

    function get_contract_total_summary($contract_id = 0) {
        $contract_items_table = $this->db->prefixTable('contract_items');
        $contracts_table = $this->db->prefixTable('contracts');
        $clients_table = $this->db->prefixTable('clients');
        $taxes_table = $this->db->prefixTable('taxes');

        $item_sql = "SELECT SUM($contract_items_table.total) AS contract_subtotal
        FROM $contract_items_table
        LEFT JOIN $contracts_table ON $contracts_table.id= $contract_items_table.contract_id    
        WHERE $contract_items_table.deleted=0 AND $contract_items_table.contract_id=$contract_id AND $contracts_table.deleted=0";
        $item = $this->db->query($item_sql)->getRow();


        $contract_sql = "SELECT $contracts_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2
        FROM $contracts_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $contracts_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $contracts_table.tax_id2
        WHERE $contracts_table.deleted=0 AND $contracts_table.id=$contract_id";
        $contract = $this->db->query($contract_sql)->getRow();

        $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id=$contract->client_id";
        $client = $this->db->query($client_sql)->getRow();


        $result = new \stdClass();
        $result->contract_subtotal = $item->contract_subtotal;
        $result->tax_percentage = $contract->tax_percentage;
        $result->tax_percentage2 = $contract->tax_percentage2;
        $result->tax_name = $contract->tax_name;
        $result->tax_name2 = $contract->tax_name2;
        $result->tax = 0;
        $result->tax2 = 0;

        $contract_subtotal = $result->contract_subtotal;
        $contract_subtotal_for_taxes = $contract_subtotal;
        if ($contract->discount_type == "before_tax") {
            $contract_subtotal_for_taxes = $contract_subtotal - ($contract->discount_amount_type == "percentage" ? ($contract_subtotal * ($contract->discount_amount / 100)) : $contract->discount_amount);
        }

        if ($contract->tax_percentage) {
            $result->tax = $contract_subtotal_for_taxes * ($contract->tax_percentage / 100);
        }
        if ($contract->tax_percentage2) {
            $result->tax2 = $contract_subtotal_for_taxes * ($contract->tax_percentage2 / 100);
        }
        $contract_total = $item->contract_subtotal + $result->tax + $result->tax2;

        //get discount total
        $result->discount_total = 0;
        if ($contract->discount_type == "after_tax") {
            $contract_subtotal = $contract_total;
        }

        $result->discount_total = $contract->discount_amount_type == "percentage" ? ($contract_subtotal * ($contract->discount_amount / 100)) : $contract->discount_amount;

        $result->discount_type = $contract->discount_type;

        $result->discount_total = is_null($result->discount_total) ? 0 : $result->discount_total;
        $result->contract_total = $contract_total - number_format($result->discount_total, 2, ".", "");

        $result->currency_symbol = $client->currency_symbol ? $client->currency_symbol : get_setting("currency_symbol");
        $result->currency = $client->currency ? $client->currency : get_setting("default_currency");
        return $result;
    }

    //get contract last id
    function get_contract_last_id() {
        $contracts_table = $this->db->prefixTable('contracts');

        $sql = "SELECT MAX($contracts_table.id) AS last_id FROM $contracts_table";

        return $this->db->query($sql)->getRow()->last_id;
    }

    //save initial number of contract
    function save_initial_number_of_contract($value) {
        $contracts_table = $this->db->prefixTable('contracts');

        $sql = "ALTER TABLE $contracts_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }

}
