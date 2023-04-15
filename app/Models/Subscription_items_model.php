<?php

namespace App\Models;

class Subscription_items_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'subscription_items';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $subscription_items_table = $this->db->prefixTable('subscription_items');
        $subscriptions_table = $this->db->prefixTable('subscriptions');
        $clients_table = $this->db->prefixTable('clients');
        $where = "";
        $id = get_array_value($options, "id");
        if ($id) {
            $where .= " AND $subscription_items_table.id=$id";
        }
        $subscription_id = get_array_value($options, "subscription_id");
        if ($subscription_id) {
            $where .= " AND $subscription_items_table.subscription_id=$subscription_id";
        }

        $sql = "SELECT $subscription_items_table.*, (SELECT $clients_table.currency_symbol FROM $clients_table WHERE $clients_table.id=$subscriptions_table.client_id limit 1) AS currency_symbol
        FROM $subscription_items_table
        LEFT JOIN $subscriptions_table ON $subscriptions_table.id=$subscription_items_table.subscription_id
        WHERE $subscription_items_table.deleted=0 $where
        ORDER BY $subscription_items_table.sort ASC";
        return $this->db->query($sql);
    }

    function get_item_suggestion($keyword = "", $user_type = "") {
        $items_table = $this->db->prefixTable('items');

        if ($keyword) {
            $keyword = $this->db->escapeLikeString($keyword);
        }

        $where = "";
        if ($user_type && $user_type === "client") {
            $where = " AND $items_table.show_in_client_portal=1";
        }

        $sql = "SELECT $items_table.title
        FROM $items_table
        WHERE $items_table.deleted=0  AND $items_table.title LIKE '%$keyword%' ESCAPE '!' $where
        LIMIT 10 
        ";
        return $this->db->query($sql)->getResult();
    }

    function get_item_info_suggestion($item_name = "", $user_type = "") {

        $items_table = $this->db->prefixTable('items');

        if ($item_name) {
            $item_name = $this->db->escapeLikeString($item_name);
        }

        $where = "";
        if ($user_type && $user_type === "client") {
            $where = " AND $items_table.show_in_client_portal=1";
        }

        $sql = "SELECT $items_table.*
        FROM $items_table
        WHERE $items_table.deleted=0  AND $items_table.title LIKE '%$item_name%' ESCAPE '!' $where
        ORDER BY id DESC LIMIT 1
        ";

        $result = $this->db->query($sql);

        if ($result->resultID->num_rows) {
            return $result->getRow();
        }
    }

}
