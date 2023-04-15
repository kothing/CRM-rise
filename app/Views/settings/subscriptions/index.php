<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "subscriptions";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_subscription_settings"), array("id" => "subscription-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="card">
                <div class=" card-header">
                    <h4><?php echo app_lang("subscription_settings"); ?></h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="row">
                            <label for="subscription_prefix" class=" col-md-2"><?php echo app_lang('subscription_prefix'); ?></label>
                            <div class=" col-md-10">
                                <?php
                                echo form_input(array(
                                    "id" => "subscription_prefix",
                                    "name" => "subscription_prefix",
                                    "value" => get_setting("subscription_prefix"),
                                    "class" => "form-control",
                                    "placeholder" => strtoupper(app_lang("subscription")) . " #"
                                ));
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="initial_number_of_the_subscription" class="col-md-2"><?php echo app_lang('initial_number_of_the_subscription'); ?></label>
                            <input type="hidden" id="last_subscription_id" name="last_subscription_id" value="<?php echo $last_id; ?>" />
                            <div class="col-md-3">
                                <?php
                                echo form_input(array(
                                    "id" => "initial_number_of_the_subscription",
                                    "name" => "initial_number_of_the_subscription",
                                    "type" => "number",
                                    "value" => $last_id + 1,
                                    "class" => "form-control mini",
                                    "data-rule-greaterThan" => "#last_subscription_id",
                                    "data-msg-greaterThan" => app_lang("the_subscriptions_id_must_be_larger_then_last_subscription_id")
                                ));
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="enable_stripe_subscription" class=" col-md-2 col-xs-8 col-sm-4"><?php echo app_lang('enable_stripe_subscription'); ?></label>
                            <div class="col-md-10 col-xs-4 col-sm-8">
                                <?php
                                $disable = "";
                                if (!$stripe_payment_method_enabled) {
                                    $disable = "disabled='disabled'";
                                }

                                if ($stripe_payment_method_enabled) {
                                    echo form_checkbox("enable_stripe_subscription", true, get_setting("enable_stripe_subscription") ? true : false, "id='enable_stripe_subscription' class='form-check-input' $disable");
                                    ?>

                                <?php } else { ?>
                                    <span class="help-block"><i data-feather="alert-triangle" class="icon-16 text-warning"></i> <?php echo app_lang("please_enable_the_stripe_payment_method_first"); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div id="stripe-subscription-area" class="<?php echo get_setting("enable_stripe_subscription") ? "" : "hide" ?>">
                        <div class="form-group clearfix">
                            <div class="row">
                                <label for="tax_mapping" class=" col-md-2 col-xs-8 col-sm-4"><?php echo app_lang('tax_mapping'); ?></label>
                                <div class=" col-md-10 col-xs-4 col-sm-8">
                                    <?php
                                    echo modal_anchor(get_uri("taxes/stripe_tax_mapping_modal_form"), "<i data-feather='link' class='icon-16'></i> " . app_lang('tax_mapping'), array("class" => "btn btn-default", "title" => app_lang('tax_mapping')));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group clearfix">
                            <input type="hidden" name="webhook_listener_link_of_stripe_subscription" id="webhook_listener_link_of_stripe_subscription_value" value="<?php echo get_setting("webhook_listener_link_of_stripe_subscription"); ?>" />
                            <div class="row">
                                <label for="webhook_listener_link_of_stripe_subscription" class=" col-md-2"><?php echo app_lang('webhook_listener_link'); ?></label>
                                <div class=" col-md-10">
                                    <!--Don't add space between this spans. It'll make problem on copying code-->
                                    <span id="webhook_listener_link_of_stripe_subscription"><?php echo get_uri("webhooks_listener/stripe_subscription") . "/" . get_setting("webhook_listener_link_of_stripe_subscription"); ?></span><span id="reset-key-stripe-webhook" class="p10 ml15 clickable"><i data-feather="refresh-cw" class="icon-16"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                </div>
            </div>

            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#subscription-settings-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    appAlert.error(result.message);
                }
            }
        });

        $("#enable_stripe_subscription").click(function () {
            if ($(this).is(":checked")) {
                $("#stripe-subscription-area").removeClass("hide");
            } else {
                $("#stripe-subscription-area").addClass("hide");
            }
        });

        var stripeUrl = "<?php echo get_uri('webhooks_listener/stripe_subscription'); ?>";

        //for security purpose, add random string at the end of webhook listener link
        var setstripeUrl = function () {
            var randomString = getRandomAlphabet(20);
            $("#webhook_listener_link_of_stripe_subscription_value").val(randomString);
            $("#webhook_listener_link_of_stripe_subscription").html(stripeUrl + "/" + randomString);
        };

        //prepare url at first time
        if (!$("#webhook_listener_link_of_stripe_subscription_value").val()) {
            setstripeUrl();
        }

        //reset url
        $("#reset-key-stripe-webhook").click(function () {
            setstripeUrl();
        });
    });
</script>