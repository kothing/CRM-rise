<?php

namespace App\Controllers;

use App\Libraries\Stripe;

//don't extend this controller from Pre_loader 
//because this will be called by Stripe 
//and login check is not required since we'll validate the data

class Stripe_redirect extends App_Controller {

    protected $Stripe_ipn_model;
    private $stripe;

    function __construct() {
        parent::__construct();
        $this->Stripe_ipn_model = model('App\Models\Stripe_ipn_model');
        $this->stripe = new Stripe();
    }

    function index($payment_verification_code = "") {
        if (!$payment_verification_code) {
            show_404();
        }

        $stripe_ipn_info = $this->Stripe_ipn_model->get_one_payment_where($payment_verification_code);
        if (!$stripe_ipn_info) {
            show_404();
        }

        $payment = $this->stripe->is_valid_ipn($stripe_ipn_info);
        if (!$payment) {
            show_404();
        }

        //so, the payment is valid
        //save the payment
        //set login user id = contact id for future processing
        $this->login_user = new \stdClass();
        $this->login_user->id = $stripe_ipn_info->contact_user_id;
        $this->login_user->user_type = "client";

        $invoice_id = $stripe_ipn_info->invoice_id;

        $invoice_payment_data = array(
            "invoice_id" => $invoice_id,
            "payment_date" => get_current_utc_time(),
            "payment_method_id" => $stripe_ipn_info->payment_method_id,
            "note" => "",
            "amount" => $payment->amount / 100,
            "transaction_id" => $payment->id,
            "created_at" => get_current_utc_time(),
            "created_by" => $this->login_user->id,
        );

        //check if already a payment done with this transaction
        $existing = $this->Invoice_payments_model->get_one_where(array("transaction_id" => $payment->id));
        if ($existing->id) {
            show_404();
        }

        $invoice_payment_id = $this->Invoice_payments_model->ci_save($invoice_payment_data);
        if (!$invoice_payment_id) {
            show_404();
        }

        //as receiving payment for the invoice, we'll remove the 'draft' status from the invoice 
        $this->Invoices_model->update_invoice_status($invoice_id);

        log_notification("invoice_payment_confirmation", array("invoice_payment_id" => $invoice_payment_id, "invoice_id" => $invoice_id), "0");

        log_notification("invoice_online_payment_received", array("invoice_payment_id" => $invoice_payment_id, "invoice_id" => $invoice_id), $this->login_user->id);

        //delete the ipn data
        $this->Stripe_ipn_model->delete($stripe_ipn_info->id);

        $verification_code = $stripe_ipn_info->verification_code;
        if ($verification_code) {
            $redirect_to = "pay_invoice/index/$verification_code";
        } else {
            $redirect_to = "invoices/preview/$invoice_id";
        }

        $this->session->setFlashdata("success_message", app_lang("payment_success_message"));
        app_redirect($redirect_to);
    }

    function subscription($payment_verification_code = "") {
        if (!$payment_verification_code) {
            show_404();
        }

        $stripe_ipn_info = $this->Stripe_ipn_model->get_one_payment_where($payment_verification_code);
        if (!($stripe_ipn_info && $stripe_ipn_info->subscription_id)) {
            show_404();
        }

        $customer_id = $this->Stripe_ipn_model->get_customer_id($stripe_ipn_info->subscription_id);
        $subscription_info = $this->Subscriptions_model->get_details(array("id" => $stripe_ipn_info->subscription_id))->getRow();

        $stripe = new Stripe();
        $stripe_payment_method_id = $stripe->retrieve_setup_intent($stripe_ipn_info->setup_intent)->payment_method;
        $stripe_product_info = $stripe->retrieve_product($subscription_info->stripe_product_id);
        $subscription_item_info = $this->Subscription_items_model->get_one_where(array("subscription_id" => $subscription_info->id, "deleted" => 0));

        $tax_rates = array();
        if ($subscription_info->stripe_tax_id) {
            array_push($tax_rates, $subscription_info->stripe_tax_id);
        }
        if ($subscription_info->stripe_tax_id2) {
            array_push($tax_rates, $subscription_info->stripe_tax_id2);
        }
        
        
        $subscription_data = array();
        
        

        //create subscription with this payment method
        $stripe_subscription_data = array(
            "customer" => $customer_id,
            "items" => array(
                array(
                    "price" => $subscription_info->stripe_product_price_id,
                    "quantity" => $subscription_item_info->quantity,
                    "tax_rates" => $tax_rates
                )
            ),
            "default_payment_method" => $stripe_payment_method_id,
            "metadata" => array(
                "subscription_id" => $stripe_ipn_info->subscription_id,
                "contact_user_id" => $stripe_ipn_info->contact_user_id,
                "payment_method_id" => $stripe_ipn_info->payment_method_id,
            ),
            "proration_behavior" => "none"
        );
        
        $billing_cycle_anchor = $subscription_info->bill_date;
        $today = get_my_local_time("Y-m-d H:i:s");
        if($billing_cycle_anchor>$today){
            $stripe_subscription_data["billing_cycle_anchor"] = strtotime($billing_cycle_anchor);
        }else{
             $subscription_data["bill_date"] = $today;
        }
        
        

        //prepare the last billed date 
        if ($subscription_info->no_of_cycles) {
            $last_billed_date = $subscription_info->bill_date;
            for ($i = 0; $i < $subscription_info->no_of_cycles; $i++) {
                $last_billed_date = add_period_to_date($last_billed_date, $subscription_info->repeat_every, $subscription_info->repeat_type);
            }

            //add one more day to work on stripe
            $last_billed_date = add_period_to_date($last_billed_date, 1, "days");

            $stripe_subscription_data["cancel_at"] = strtotime($last_billed_date);
        }

        try {
            $stripe_subscription_info = $stripe->create_subscription($stripe_subscription_data);

            //save subscription id on the subscription
            //it'll also take the first payment now
            //grab that with the same webhook
            $subscription_data["stripe_subscription_id"] = $stripe_subscription_info->id;
            $subscription_data["status"] = "active";
            $this->Subscriptions_model->ci_save($subscription_data, $stripe_ipn_info->subscription_id);

            //save the last 4 digits of card to clients table        
            $client_data = array("stripe_card_ending_digit" => $stripe->retrieve_payment_method($stripe_payment_method_id)->card->last4);
            $this->Clients_model->ci_save($client_data, $stripe_ipn_info->client_id);

            //delete the ipn data
            $this->Stripe_ipn_model->delete($stripe_ipn_info->id);

            $this->session->setFlashdata("success_message", app_lang("subscription_success_message"));
            app_redirect("subscriptions/preview/$stripe_ipn_info->subscription_id");
        } catch (\Exception $ex) {
            echo json_encode(array("success" => false, "message" => $ex->getMessage()));
        }
    }

}

/* End of file Stripe_redirect.php */
/* Location: ./app/controllers/Stripe_redirect.php */