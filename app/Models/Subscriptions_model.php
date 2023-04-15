<?php

namespace App\Models;

class Subscriptions_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'subscriptions';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $subscriptions_table = $this->db->prefixTable('subscriptions');
        $clients_table = $this->db->prefixTable('clients');
        $taxes_table = $this->db->prefixTable('taxes');
        $subscription_items_table = $this->db->prefixTable('subscription_items');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $subscriptions_table.id=$id";
        }
        $client_id = get_array_value($options, "client_id");
        if ($client_id) {
            $where .= " AND $subscriptions_table.client_id=$client_id";
        }

        $status = get_array_value($options, "status");
        if ($status) {
            $where .= " AND $subscriptions_table.status='$status'";
        }

        $start_date = get_array_value($options, "start_date");
        $end_date = get_array_value($options, "end_date");
        if ($start_date && $end_date) {
            $where .= " AND ($subscriptions_table.end_date BETWEEN '$start_date' AND '$end_date') ";
        }
        
        $exclude_draft = $this->_get_clean_value($options, "exclude_draft");
        if ($exclude_draft) {
            $where .= " AND $subscriptions_table.status!='draft' ";
        }

        $next_recurring_start_date = get_array_value($options, "next_recurring_start_date");
        $next_recurring_end_date = get_array_value($options, "next_recurring_end_date");
        if ($next_recurring_start_date && $next_recurring_start_date) {
            $where .= " AND ($subscriptions_table.next_recurring_date BETWEEN '$next_recurring_start_date' AND '$next_recurring_end_date') ";
        } else if ($next_recurring_start_date) {
            $where .= " AND $subscriptions_table.next_recurring_date >= '$next_recurring_start_date' ";
        } else if ($next_recurring_end_date) {
            $where .= " AND $subscriptions_table.next_recurring_date <= '$next_recurring_end_date' ";
        }

        $tax_1 = "(IFNULL(tax_table.percentage,0)/100*IFNULL(items_table.subscription_value,0))";
        $tax_2 = "(IFNULL(tax_table2.percentage,0)/100*IFNULL(items_table.subscription_value,0))";

        $subscription_value_calculation = "(
            IFNULL(items_table.subscription_value,0)+
            ($tax_1+ $tax_2)
           )";
        
        $currency = get_array_value($options, "currency");
        if ($currency) {
            $where .= $this->_get_clients_of_currency_query($currency, $subscriptions_table, $clients_table);
        }

        $select_labels_data_query = $this->get_labels_data_query();

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("subscriptions", $custom_fields, $subscriptions_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $sql = "SELECT $subscriptions_table.*, $clients_table.currency, $clients_table.currency_symbol, $clients_table.company_name, 
           $subscription_value_calculation AS subscription_value, tax_table.percentage AS tax_percentage, tax_table.stripe_tax_id AS stripe_tax_id, tax_table2.percentage AS tax_percentage2, tax_table2.stripe_tax_id AS stripe_tax_id2, CONCAT($users_table.first_name, ' ',$users_table.last_name) AS cancelled_by_user, $select_labels_data_query $select_custom_fieds
        FROM $subscriptions_table
        LEFT JOIN $clients_table ON $clients_table.id= $subscriptions_table.client_id
        LEFT JOIN $users_table ON $users_table.id= $subscriptions_table.cancelled_by
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $subscriptions_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $subscriptions_table.tax_id2
        LEFT JOIN (SELECT subscription_id, SUM(total) AS subscription_value FROM $subscription_items_table WHERE deleted=0 GROUP BY subscription_id) AS items_table ON items_table.subscription_id = $subscriptions_table.id 
        $join_custom_fieds
        WHERE $subscriptions_table.deleted=0 $where $custom_fields_where";
        return $this->db->query($sql);
    }

    function get_subscription_total_summary($subscription_id = 0) {
        $subscription_items_table = $this->db->prefixTable('subscription_items');
        $subscriptions_table = $this->db->prefixTable('subscriptions');
        $clients_table = $this->db->prefixTable('clients');
        $taxes_table = $this->db->prefixTable('taxes');

        $item_sql = "SELECT SUM($subscription_items_table.total) AS subscription_subtotal
        FROM $subscription_items_table
        LEFT JOIN $subscriptions_table ON $subscriptions_table.id= $subscription_items_table.subscription_id    
        WHERE $subscription_items_table.deleted=0 AND $subscription_items_table.subscription_id=$subscription_id AND $subscriptions_table.deleted=0";
        $item = $this->db->query($item_sql)->getRow();

        $subscription_sql = "SELECT $subscriptions_table.*, tax_table.percentage AS tax_percentage, tax_table.title AS tax_name,
            tax_table2.percentage AS tax_percentage2, tax_table2.title AS tax_name2
        FROM $subscriptions_table
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $subscriptions_table.tax_id
        LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $subscriptions_table.tax_id2
        WHERE $subscriptions_table.deleted=0 AND $subscriptions_table.id=$subscription_id";
        $subscription = $this->db->query($subscription_sql)->getRow();

        $client_sql = "SELECT $clients_table.currency_symbol, $clients_table.currency FROM $clients_table WHERE $clients_table.id=$subscription->client_id";
        $client = $this->db->query($client_sql)->getRow();

        $result = new \stdClass();
        $result->subscription_subtotal = $item->subscription_subtotal;
        $result->tax_percentage = $subscription->tax_percentage;
        $result->tax_percentage2 = $subscription->tax_percentage2;
        $result->tax_name = $subscription->tax_name;
        $result->tax_name2 = $subscription->tax_name2;
        $result->tax = 0;
        $result->tax2 = 0;

        $subscription_subtotal = $result->subscription_subtotal;
        $subscription_subtotal_for_taxes = $subscription_subtotal;

        if ($subscription->tax_percentage) {
            $result->tax = $subscription_subtotal_for_taxes * ($subscription->tax_percentage / 100);
        }
        if ($subscription->tax_percentage2) {
            $result->tax2 = $subscription_subtotal_for_taxes * ($subscription->tax_percentage2 / 100);
        }
        $result->subscription_total = ($item->subscription_subtotal + $result->tax + $result->tax2);

        $result->currency_symbol = $client->currency_symbol ? $client->currency_symbol : get_setting("currency_symbol");
        $result->currency = $client->currency ? $client->currency : get_setting("default_currency");

        $result->subscription_total = is_null($result->subscription_total) ? 0 : $result->subscription_total;
        $result->balance_due = number_format($result->subscription_total, 2, ".", "");

        return $result;
    }

    //update subscription status
    function update_subscription_status($subscription_id = 0, $status = "draft") {
        $status = $status ? $this->db->escapeString($status) : $status;
        $status_data = array("status" => $status);
        return $this->ci_save($status_data, $subscription_id);
    }

    //get the recurring subscriptions which are ready to renew as on a given date
    function get_renewable_subscriptions($date) {
        $subscriptions_table = $this->db->prefixTable('subscriptions');

        $sql = "SELECT * FROM $subscriptions_table
                        WHERE $subscriptions_table.deleted=0 
                        AND $subscriptions_table.status='active' AND $subscriptions_table.type='app'
                        AND $subscriptions_table.next_recurring_date IS NOT NULL AND $subscriptions_table.next_recurring_date<='$date'
                        AND ($subscriptions_table.no_of_cycles < 1 OR ($subscriptions_table.no_of_cycles_completed < $subscriptions_table.no_of_cycles ))";

        return $this->db->query($sql);
    }

    //get subscriptions dropdown list
    function get_subscriptions_dropdown_list() {
        $subscriptions_table = $this->db->prefixTable('subscriptions');

        $sql = "SELECT $subscriptions_table.id FROM $subscriptions_table
                        WHERE $subscriptions_table.deleted=0 
                        ORDER BY $subscriptions_table.id DESC";

        return $this->db->query($sql);
    }
    
    //get label suggestions
    function get_label_suggestions() {
        $subscriptions_table = $this->db->prefixTable('subscriptions');
        $sql = "SELECT GROUP_CONCAT(labels) as label_groups
        FROM $subscriptions_table
        WHERE $subscriptions_table.deleted=0";
        return $this->db->query($sql)->getRow()->label_groups;
    }

    //get subscription last id
    function get_last_subscription_id() {
        $subscriptions_table = $this->db->prefixTable('subscriptions');

        $sql = "SELECT MAX($subscriptions_table.id) AS last_id FROM $subscriptions_table";

        return $this->db->query($sql)->getRow()->last_id;
    }

    //save initial number of subscription
    function save_initial_number_of_subscription($value) {
        $subscriptions_table = $this->db->prefixTable('subscriptions');

        $sql = "ALTER TABLE $subscriptions_table AUTO_INCREMENT=$value;";

        return $this->db->query($sql);
    }

}
