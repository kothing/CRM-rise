<div id="page-content" class="page-wrapper clearfix">
    <div class="row">

        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "localization";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_localization_settings"), array("id" => "localization-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="card">
                <div class="card-header">
                    <h4><?php echo app_lang("localization_settings"); ?></h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="row">
                            <label for="language" class=" col-md-2"><?php echo app_lang('language'); ?></label>
                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "language", $language_dropdown, get_setting('language') ? get_setting('language') : "english", "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="timezone" class=" col-md-2"><?php echo app_lang('timezone'); ?></label>
                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "timezone", $timezone_dropdown, get_setting('timezone'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="date_format" class=" col-md-2"><?php echo app_lang('date_format'); ?></label>
                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "date_format", array(
                                    "d-m-Y" => "d-m-Y",
                                    "m-d-Y" => "m-d-Y",
                                    "Y-m-d" => "Y-m-d",
                                    "d/m/Y" => "d/m/Y",
                                    "m/d/Y" => "m/d/Y",
                                    "Y/m/d" => "Y/m/d",
                                    "d.m.Y" => "d.m.Y",
                                    "m.d.Y" => "m.d.Y",
                                    "Y.m.d" => "Y.m.d",
                                        ), get_setting('date_format'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="time_format" class=" col-md-2"><?php echo app_lang('time_format'); ?></label>
                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "time_format", array(
                                    "capital" => "12 AM",
                                    "small" => "12 am",
                                    "24_hours" => "24 hours"
                                        ), get_setting('time_format'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <label for="first_day_of_week" class=" col-md-2"><?php echo app_lang('first_day_of_week'); ?></label>
                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "first_day_of_week", array(
                                    "0" => app_lang("sunday"),
                                    "1" => app_lang("monday"),
                                    "2" => app_lang("tuesday"),
                                    "3" => app_lang("wednesday"),
                                    "4" => app_lang("thursday"),
                                    "5" => app_lang("friday"),
                                    "6" => app_lang("saturday")
                                        ), get_setting('first_day_of_week'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <label for="weekends" class=" col-md-2"><?php echo app_lang('weekends'); ?></label>
                            <div class="col-md-10">
                                <?php
                                $days_dropdown = array(
                                    array("id" => "0", "text" => app_lang("sunday")),
                                    array("id" => "1", "text" => app_lang("monday")),
                                    array("id" => "2", "text" => app_lang("tuesday")),
                                    array("id" => "3", "text" => app_lang("wednesday")),
                                    array("id" => "4", "text" => app_lang("thursday")),
                                    array("id" => "5", "text" => app_lang("friday")),
                                    array("id" => "6", "text" => app_lang("saturday")),
                                );

                                echo form_input(array(
                                    "id" => "weekends",
                                    "name" => "weekends",
                                    "value" => get_setting("weekends"),
                                    "class" => "form-control",
                                    "placeholder" => app_lang('weekends')
                                ));
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <label for="default_currency" class=" col-md-2"><?php echo app_lang('currency'); ?></label>
                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "default_currency", $currency_dropdown, get_setting('default_currency'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="currency_symbol" class=" col-md-2"><?php echo app_lang('currency_symbol'); ?></label>
                            <div class=" col-md-10">
                                <?php
                                echo form_input(array(
                                    "id" => "currency_symbol",
                                    "name" => "currency_symbol",
                                    "value" => get_setting('currency_symbol'),
                                    "class" => "form-control",
                                    "placeholder" => app_lang('currency_symbol'),
                                    "data-rule-required" => true,
                                    "data-msg-required" => app_lang("field_required"),
                                ));
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="currency_position" class=" col-md-2"><?php echo app_lang('currency_position'); ?></label>
                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "currency_position", array(
                                    "left" => app_lang("left"),
                                    "right" => app_lang("right")
                                        ), get_setting('currency_position'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="decimal_separator" class=" col-md-2"><?php echo app_lang('decimal_separator'); ?></label>
                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "decimal_separator", array("." => "Dot (.)", "," => "Comma (,)"), get_setting('decimal_separator'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="no_of_decimals" class=" col-md-2"><?php echo app_lang('no_of_decimals'); ?></label>
                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "no_of_decimals", array(
                                    "0" => "0",
                                    "2" => "2"
                                        ), get_setting('no_of_decimals') == "0" ? "0" : "2", "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="conversion_rate" class=" col-md-2"><?php echo app_lang('conversion_rate'); ?></label>
                            <div class="col-md-10">
                                <div class="conversion-rate-field">
                                    <?php
                                    //show existing conversion rates
                                    $conversion_rate = get_setting("conversion_rate");
                                    $conversion_rate = @unserialize($conversion_rate);
                                    if ($conversion_rate && is_array($conversion_rate) && count($conversion_rate)) {
                                        $decimal_separator = get_setting("decimal_separator");
                                        foreach ($conversion_rate as $currency => $rate) {
                                            if (!is_numeric($rate)) {
                                                $rate = 0;
                                            }

                                            $no_of_decimals = strlen(substr(strrchr($rate, "."), 1));
                                            if ($decimal_separator === ",") {
                                                $rate = number_format($rate, $no_of_decimals, ",", ".");
                                            } else {
                                                $rate = number_format($rate, $no_of_decimals, ".", ",");
                                            }
                                            ?>

                                            <div class="conversion-rate-form clearfix pb10 ml10 mr10">
                                                <div class="row">
                                                    <div class="clearfix p0">
                                                        <?php
                                                        echo form_dropdown(
                                                                "conversion_rate_currency[]", $currency_dropdown, $currency, "class='select2 mini float-start mr10'"
                                                        );
                                                        ?>
                                                        <div class="float-start">
                                                            <?php
                                                            echo form_input(array(
                                                                "id" => "conversion_rate",
                                                                "name" => "conversion_rate[]",
                                                                "class" => "form-control",
                                                                "value" => $rate,
                                                                "placeholder" => app_lang('conversion_rate')
                                                            ));
                                                            ?>
                                                        </div>
                                                        <div class="float-start">
                                                            <?php echo js_anchor("<i data-feather='x' class='icon-16'></i> ", array("class" => "remove-conversion-rate delete ml10 mt-2")); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>

                                    <div class="conversion-rate-form clearfix pb10 ml10 mr10">
                                        <div class="row">
                                            <div class="clearfix p0">
                                                <?php
                                                echo form_dropdown(
                                                        "conversion_rate_currency[]", $currency_dropdown, "", "class='select2 mini float-start mr10'"
                                                );
                                                ?>
                                                <div class="float-start">
                                                    <?php
                                                    echo form_input(array(
                                                        "id" => "conversion_rate",
                                                        "name" => "conversion_rate[]",
                                                        "class" => "form-control",
                                                        "placeholder" => app_lang('conversion_rate')
                                                    ));
                                                    ?>
                                                </div>
                                                <div class="float-start">
                                                    <?php echo js_anchor("<i data-feather='x' class='icon-16'></i> ", array("class" => "remove-conversion-rate delete ml10 mt-2")); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php echo js_anchor("<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_more'), array("class" => "add-conversion-rate")); ?>


                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php echo view("includes/cropbox"); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#localization-settings-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    appAlert.error(result.message);
                }
            }
        });

        $("#weekends").select2({
            multiple: true,
            data: <?php echo (json_encode($days_dropdown)); ?>
        });

        var $wrapper = $('.conversion-rate-field'),
                $field = $('.conversion-rate-form:last-child', $wrapper).clone(); //keep a clone for future use.

        $(".add-conversion-rate", $(this)).click(function (e) {
            var $newField = $field.clone();

            var $newObj = $newField.appendTo($wrapper);
            $newObj.find("input").val("").focus();
            $newObj.find(".select2").select2();
        });

        //remove conversion rate input field
        $('body').on('click', '.remove-conversion-rate', function () {
            $(this).closest(".conversion-rate-form").remove();
        });

<?php if (!($conversion_rate && is_array($conversion_rate) && count($conversion_rate))) { ?>
            $(".remove-conversion-rate").hide();
<?php } ?>

        $("#localization-settings-form .select2").select2();

    });
</script>