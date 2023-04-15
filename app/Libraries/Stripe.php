<?php

namespace App\Libraries;

class Stripe {

    private $stripe_config;
    private $Users_model;
    private $Clients_model;
    private $Subscriptions_model;

    public function __construct() {
        $Payment_methods_model = model("App\Models\Payment_methods_model");

        $this->stripe_config = $Payment_methods_model->get_oneline_payment_method("stripe");
        $this->Users_model = model("App\Models\Users_model");
        $this->Clients_model = model("App\Models\Clients_model");
        $this->Subscriptions_model = model("App\Models\Subscriptions_model");

        require_once(APPPATH . "ThirdParty/Stripe/vendor/autoload.php");

        \Stripe\Stripe::setApiKey($this->stripe_config->secret_key);
    }

    public function get_stripe_checkout_session($data = array(), $login_user = 0) {
        $invoice_id = get_array_value($data, "invoice_id");
        $subscription_id = get_array_value($data, "subscription_id");
        $currency = get_array_value($data, "currency");
        $payment_amount = get_array_value($data, "payment_amount");
        $description = get_array_value($data, "description");
        $verification_code = get_array_value($data, "verification_code");
        $contact_user_id = $login_user ? $login_user : get_array_value($data, "contact_user_id");
        $client_id = get_array_value($data, "client_id");
        $payment_method_id = get_array_value($data, "payment_method_id");
        $balance_due = get_array_value($data, "balance_due");

        if (!($invoice_id || $subscription_id)) {
            return false;
        }

        //validate public invoice information
        if (!$login_user && !validate_invoice_verification_code($verification_code, array("invoice_id" => $invoice_id, "client_id" => $client_id, "contact_id" => $contact_user_id))) {
            return false;
        }

        //check if partial payment allowed or not
        if (get_setting("allow_partial_invoice_payment_from_clients")) {
            $payment_amount = unformat_currency($payment_amount);
        } else {
            $payment_amount = $balance_due;
        }

        $redirect_to = "invoices/preview/$invoice_id";
        if ($verification_code) {
            $redirect_to = "pay_invoice/index/$verification_code";
        }

        if ($subscription_id) {
            $redirect_to = "subscriptions/preview/$subscription_id";
        }

        //validate payment amount
        if ($payment_amount < $this->stripe_config->minimum_payment_amount * 1) {
            $error_message = app_lang('minimum_payment_validation_message') . " " . to_currency($this->stripe_config->minimum_payment_amount, $currency . " ");
            $session = \Config\Services::session();
            $session->setFlashdata("error_message", $error_message);
            app_redirect($redirect_to);
        }

        //we'll verify the transaction with a random string code after completing the transaction
        $payment_verification_code = make_random_string();

        $stripe_ipn_data = array(
            "verification_code" => $verification_code ? $verification_code : "",
            "invoice_id" => $invoice_id ? $invoice_id : 0,
            "subscription_id" => $subscription_id ? $subscription_id : 0,
            "contact_user_id" => $contact_user_id,
            "client_id" => $client_id,
            "payment_method_id" => $payment_method_id,
            "payment_verification_code" => $payment_verification_code
        );

        if ($subscription_id) {

            //create/get existing stripe client first
            $stripe_customer_id = $this->get_customer_id($client_id, $contact_user_id);

            //create session to add card
            $session = \Stripe\Checkout\Session::create([
                        'payment_method_types' => array('card'),
                        'mode' => 'setup',
                        'customer' => $stripe_customer_id,
                        'success_url' => get_uri("stripe_redirect/subscription/$payment_verification_code"),
                        'cancel_url' => get_uri($redirect_to),
            ]);
        } else { //single time payment
            $session = \Stripe\Checkout\Session::create(array(
                        'mode' => 'payment',
                        'payment_method_types' => array('card'),
                        'line_items' => array(
                            array(
                                'quantity' => 1,
                                'price_data' => array(
                                    'unit_amount' => $payment_amount * 100, //stripe will devide it with 100
                                    'currency' => $currency,
                                    'product_data' => array(
                                        'name' => 'INVOICE #' . $invoice_id,
                                        'description' => $description,
                                        'images' => array(
                                            get_file_uri("assets/images/stripe-payment-logo.png")
                                        ),
                                    )
                                ),
                            )
                        ),
                        'payment_intent_data' => array(
                            'description' => get_invoice_id($invoice_id) . ", " . app_lang('amount') . ": " . to_currency($payment_amount, $currency . " "),
                            'metadata' => $stripe_ipn_data,
                            'setup_future_usage' => 'off_session', //save this paymentIntent's payment method for future use
                        ),
                        'success_url' => get_uri("stripe_redirect/index/$payment_verification_code"),
                        'cancel_url' => get_uri($redirect_to),
            ));
        }

        if ($session->id) {
            //so, the session creation is success
            //save ipn data to db
            if ($subscription_id) {
                $stripe_ipn_data["setup_intent"] = $session->setup_intent;
            } else {
                /**
                  so, the session creation is success
                  save ipn data to db
                  store the session id now
                  because in the latest version, we won't get payment_intent here
                  but it'll be available after the payment
                  so get the payment_intent after the payment with the session_id
                 */
                $stripe_ipn_data["session_id"] = $session->id;
            }
            $Stripe_ipn_model = model("App\Models\Stripe_ipn_model");
            $Stripe_ipn_model->ci_save($stripe_ipn_data);

            return $session;
        }
    }

    private function get_customer_id($client_id, $contact_user_id) {
        $client_info = $this->Clients_model->get_one($client_id);

        if ($client_info->stripe_customer_id) {
            return $client_info->stripe_customer_id;
        } else {
            //create stripe client
            $user_info = $this->Users_model->get_one($contact_user_id);
            $customer = \Stripe\Customer::create(array(
                        "name" => $client_info->company_name,
                        "phone" => $client_info->phone,
                        "email" => $user_info->email,
                        "address" => array(
                            "line1" => $client_info->address,
                            "city" => $client_info->city,
                            "state" => $client_info->state,
                            "postal_code" => $client_info->zip,
                            "country" => $client_info->country,
                        ),
            ));

            //save the stripe customer id to clients table
            $client_data = array("stripe_customer_id" => $customer->id);
            $this->Clients_model->ci_save($client_data, $client_id);

            return $customer->id;
        }
    }

    public function get_publishable_key() {
        return $this->stripe_config->publishable_key;
    }

    public function is_valid_ipn($stripe_ipn_info) {
        //get the payment_intent with the session_id
        $session = \Stripe\Checkout\Session::retrieve($stripe_ipn_info->session_id);
        if (!($session && $session->payment_intent)) {
            return false;
        }

        $payment = \Stripe\PaymentIntent::retrieve($session->payment_intent);
        if ($payment && $payment->status == "succeeded") {
            //so the payment is successful
            return $payment;
        }
    }

    public function get_products_list() {
        return \Stripe\Product::all(array("active" => true, "limit" => 100));
    }

    public function retrieve_customer($customer_id) {
        return \Stripe\Customer::retrieve($customer_id);
    }

    public function update_customer($customer_id, $options = array()) {
        return \Stripe\Customer::update($customer_id, $options);
    }

    public function retrieve_setup_intent($setup_intent) {
        return \Stripe\SetupIntent::retrieve($setup_intent);
    }

    public function retrieve_product($product_id) {
        return \Stripe\Product::retrieve($product_id);
    }

    public function create_subscription($options = array()) {
        return \Stripe\Subscription::create($options);
    }

    public function retrieve_payment_method($payment_method_id) {
        return \Stripe\PaymentMethod::retrieve($payment_method_id);
    }

    public function retrieve_all_taxes() {
        return \Stripe\TaxRate::all(array("inclusive" => false));
    }

    public function retrieve_all_prices_of_the_product($product_id) {
        return \Stripe\Price::all(array("product" => $product_id, "type" => "recurring"));
    }

    public function retrieve_price($price_id) {
        return \Stripe\Price::retrieve($price_id);
    }

    public function create_webhook($webhook_listener_link) {
        return \Stripe\WebhookEndpoint::create(array(
                    'url' => get_uri("webhooks_listener/stripe_subscription") . "/" . $webhook_listener_link,
                    'enabled_events' => array('invoice.payment_succeeded', 'invoice.payment_failed'),
        ));
    }

    public function update_webhook($webhook_id, $webhook_listener_link) {
        return \Stripe\WebhookEndpoint::update($webhook_id, array(
                    'url' => get_uri("webhooks_listener/stripe_subscription") . "/" . $webhook_listener_link,
        ));
    }

    public function retrieve_subscription($subscription_id) {
        return \Stripe\Subscription::retrieve($subscription_id);
    }

    public function cancel_subscription($subscription_id) {
        $subscription = $this->retrieve_subscription($subscription_id);
        $subscription->cancel();
    }

}
