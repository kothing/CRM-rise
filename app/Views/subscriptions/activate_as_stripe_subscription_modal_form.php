<?php echo form_open(get_uri("subscriptions/activate_as_stripe_subscription"), array("id" => "request-payment-method-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="subscription_id" value="<?php echo $subscription_info->id; ?>" />
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <?php echo app_lang("activate_as_stripe_subscription_message_1"); ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="stripe_product" class=" col-md-3"><?php echo "Stripe " . app_lang('product'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "stripe_product",
                        "name" => "stripe_product",
                        "class" => "form-control",
                        "value" => $subscription_info->stripe_product_id,
                        "placeholder" => "Stripe " . app_lang('product'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="stripe_product_price_id" class=" col-md-3"><?php echo "Stripe " . app_lang('price'); ?></label>
                <div class="col-md-9" id="dropdown-apploader-section">
                    <?php
                    echo form_input(array(
                        "id" => "stripe_product_price_id",
                        "name" => "stripe_product_price_id",
                        "value" => $subscription_info->stripe_product_price_id,
                        "class" => "form-control",
                        "placeholder" => "Stripe " . app_lang('price')
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-12 text-off">
                    <i data-feather="info" class="icon-16"></i> <?php echo app_lang("activate_as_stripe_subscription_message_2"); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('cancel'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('send'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#request-payment-method-form").appForm({
            onSuccess: function (result) {
                location.reload();
            }
        });

        //load prices of the selected product
        $('#stripe_product').select2({data: <?php echo json_encode($stripe_products_dropdown); ?>}).on("change", function () {
            var productId = $(this).val();
            if ($(this).val()) {
                $('#stripe_product_price_id').select2("destroy");
                $("#stripe_product_price_id").hide();
                appLoader.show({container: "#dropdown-apploader-section", zIndex: 1});
                $.ajax({
                    url: "<?php echo get_uri('subscriptions/get_prices_of_selected_product') ?>" + "/" + productId,
                    dataType: "json",
                    success: function (result) {
                        $("#stripe_product_price_id").show().val("");
                        $('#stripe_product_price_id').select2({data: result.stripe_product_prices_dropdown});
                        appLoader.hide();
                    }
                });
            }
        });

        //intialized select2 dropdown for first time
        $('#stripe_product_price_id').select2({data: <?php echo json_encode($stripe_product_prices_dropdown); ?>});
    });


</script>