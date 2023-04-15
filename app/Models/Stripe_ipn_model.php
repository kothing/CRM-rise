<?php

namespace App\Models;

class Stripe_ipn_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'stripe_ipn';
        parent::__construct($this->table);
    }

    function get_one_payment_where($payment_verification_code) {
        $stripe_ipn_table = $this->db->prefixTable('stripe_ipn');
        $payment_verification_code = $payment_verification_code ? $this->db->escapeString($payment_verification_code) : $payment_verification_code;

        $sql = "SELECT $stripe_ipn_table.*
        FROM $stripe_ipn_table
        WHERE $stripe_ipn_table.deleted=0 AND $stripe_ipn_table.payment_verification_code='$payment_verification_code'
        ORDER BY $stripe_ipn_table.id DESC
        LIMIT 1";

        return $this->db->query($sql)->getRow();
    }

    function get_customer_id($subscription_id) {
        $subscriptions_table = $this->db->prefixTable('subscriptions');
        $clients_table = $this->db->prefixTable('clients');

        $sql = "SELECT $clients_table.stripe_customer_id 
        FROM $clients_table
        WHERE $clients_table.deleted=0 AND $clients_table.id=(SELECT $subscriptions_table.client_id FROM $subscriptions_table WHERE $subscriptions_table.deleted=0 AND $subscriptions_table.id=$subscription_id)";

        return $this->db->query($sql)->getRow()->stripe_customer_id;
    }

}
