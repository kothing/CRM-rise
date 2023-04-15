<?php

namespace App\Models;

class Paypal_ipn_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'paypal_ipn';
        parent::__construct($this->table);
    }
    
    function get_one_payment_where($payment_verification_code) {
        $paypal_ipn_table = $this->db->prefixTable('paypal_ipn');
        $payment_verification_code = $payment_verification_code ? $this->db->escapeString($payment_verification_code) : $payment_verification_code;

        $sql = "SELECT $paypal_ipn_table.*
        FROM $paypal_ipn_table
        WHERE $paypal_ipn_table.deleted=0 AND $paypal_ipn_table.payment_verification_code='$payment_verification_code'
        ORDER BY $paypal_ipn_table.id DESC
        LIMIT 1";

        return $this->db->query($sql)->getRow();
    }


}