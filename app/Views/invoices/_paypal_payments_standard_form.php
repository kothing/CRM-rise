<?php
$form_action = isset($contact_user_id) ? get_uri("pay_invoice/get_paypal_checkout_url") : get_uri("invoice_payments/get_paypal_checkout_url");
echo form_open("", array("id" => "paypal-checkout-form", "class" => "float-start", "role" => "form"));
?>

<input type="hidden" name="invoice_id" value="<?php echo $invoice_id; ?>" />
<input type="hidden" name="payment_amount" value="<?php echo to_decimal_format($balance_due); ?>"  id="paypal-payments-standard-amount-field" />
<input type="hidden" name="verification_code" value="<?php echo isset($verification_code) ? $verification_code : ""; ?>"  id="verification_code" />
<input type="hidden" name="contact_user_id" value="<?php echo isset($contact_user_id) ? $contact_user_id : ""; ?>"  id="contact_user_id" />

<input type="hidden" name="currency" value="<?php echo $currency; ?>" />
<input type="hidden" name="balance_due" value="<?php echo $balance_due; ?>" />
<input type="hidden" name="client_id" value="<?php echo $invoice_info->client_id; ?>" />
<input type="hidden" name="payment_method_id" value="<?php echo get_array_value($payment_method, "id"); ?>" />
<input type="hidden" name="description" value="<?php echo app_lang("pay_invoice"); ?>: (<?php echo to_currency($balance_due, $currency . " "); ?>)" id="description" />

<button type="button" id="paypal-payments-stundard-button" class="btn btn-primary mr15 spinning-btn"><?php echo get_array_value($payment_method, "pay_button_text"); ?></button>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#paypal-payments-stundard-button").click(function () {

            //show an error message if user attempt to pay more than the invoice due and exit
<?php if (get_setting("allow_partial_invoice_payment_from_clients")) { ?>
                if (unformatCurrency($("#payment-amount").val()) > "<?php echo $balance_due; ?>") {
                    appAlert.error("<?php echo app_lang("invoice_over_payment_error_message"); ?>");
                    return false;
                }
<?php } ?>

            $(this).addClass("spinning");
            
            //prepare the data
            var data = {};
            $("#paypal-checkout-form input").each(function () {
                data[$(this).attr("name")] = $(this).val();
            });

            //get the payment url
            $.ajax({
                url: "<?php echo $form_action; ?>",
                type: 'POST',
                dataType: 'json',
                data: {input_data: data},
                success: function (result) {
                    if (result.success && result.checkout_url) {
                        window.location.href = result.checkout_url;
                    } else {
                        appAlert.error(result.message);
                    }
                }
            });
        });
        
        var minimumPaymentAmount = "<?php echo get_array_value($payment_method, 'minimum_payment_amount'); ?>" * 1;
        if (!minimumPaymentAmount || isNaN(minimumPaymentAmount)) {
            minimumPaymentAmount = 1;
        }

        $("#payment-amount").change(function () {
            //change paypal payment amount
            var value = unformatCurrency($(this).val());

            $("#paypal-payments-standard-amount-field").val(value);

            //check minimum payment amount and show/hide payment button
            if (value < minimumPaymentAmount) {
                $("#paypal-payments-stundard-button").hide();
            } else {
                $("#paypal-payments-stundard-button").show();
            }
        });
    });
</script>