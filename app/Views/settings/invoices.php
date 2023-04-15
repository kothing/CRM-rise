<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "invoices";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_invoice_settings"), array("id" => "invoice-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="card">
                <div class=" card-header">
                    <h4><?php echo app_lang("invoice_settings"); ?></h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="row">
                            <label for="invoice_prefix" class=" col-md-2"><?php echo app_lang('invoice_prefix'); ?></label>
                            <div class=" col-md-10">
                                <?php
                                echo form_input(array(
                                    "id" => "invoice_prefix",
                                    "name" => "invoice_prefix",
                                    "value" => get_setting("invoice_prefix"),
                                    "class" => "form-control",
                                    "placeholder" => strtoupper(app_lang("invoice")) . " #"
                                ));
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="invoice_color" class=" col-md-2"><?php echo app_lang('invoice_color'); ?></label>
                            <div class=" col-md-10">
                                <input type="color" id="invoice_color" name="invoice_color" value="<?php echo get_setting("invoice_color"); ?>" />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <label for="invoice_footer" class=" col-md-2"><?php echo app_lang('invoice_footer'); ?></label>
                            <div class=" col-md-10">
                                <?php
                                echo form_textarea(array(
                                    "id" => "invoice_footer",
                                    "name" => "invoice_footer",
                                    "value" => process_images_from_content(get_setting("invoice_footer"), false),
                                    "class" => "form-control"
                                ));
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="invoice_style" class=" col-md-2"><?php echo app_lang('invoice_style'); ?></label>
                            <div class="col-md-10">
                                <?php
                                $invoice_style = get_setting("invoice_style") ? get_setting("invoice_style") : "style_1";
                                ?>
                                <input type="hidden" id="invoice_style" name="invoice_style" value="<?php echo $invoice_style; ?>" />

                                <div class="clearfix invoice-styles">
                                    <div data-value="style_1" class="item <?php echo $invoice_style == 'style_1' ? ' active ' : ''; ?>" >
                                        <span class="selected-mark <?php echo $invoice_style == 'style_1' ? '' : 'hide'; ?>"><i data-feather="check-circle"></i></span>
                                        <img src="<?php echo get_file_uri("assets/images/invoice_style_1.png") ?>" alt="style_1" />
                                    </div>
                                    <div data-value="style_2" class="item <?php echo $invoice_style === 'style_2' ? ' active ' : ''; ?>" >
                                        <span class="selected-mark <?php echo $invoice_style === 'style_2' ? '' : 'hide'; ?>"><i data-feather="check-circle"></i></span>
                                        <img src="<?php echo get_file_uri("assets/images/invoice_style_2.png") ?>" alt="style_2" />
                                    </div>
                                    <div data-value="style_3" class="item <?php echo $invoice_style === 'style_3' ? ' active ' : ''; ?>" >
                                        <span class="selected-mark <?php echo $invoice_style === 'style_3' ? '' : 'hide'; ?>"><i data-feather="check-circle"></i></span>
                                        <img src="<?php echo get_file_uri("assets/images/invoice_style_3.png") ?>" alt="style_3" />
                                    </div>
                                </div>    
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="default_due_date_after_billing_date" class="col-md-2"><?php echo app_lang('default_due_date_after_billing_date'); ?></label>
                            <div class="col-md-3">
                                <?php
                                echo form_input(array(
                                    "id" => "default_due_date_after_billing_date",
                                    "name" => "default_due_date_after_billing_date",
                                    "type" => "number",
                                    "value" => get_setting("default_due_date_after_billing_date"),
                                    "class" => "form-control mini",
                                    "min" => 0
                                ));
                                ?>
                            </div>
                            <label class="col-md-1 mt5"><?php echo app_lang('days'); ?></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="send_bcc_to" class=" col-md-2"><?php echo app_lang('send_bcc_to'); ?></label>
                            <div class=" col-md-10">
                                <?php
                                echo form_input(array(
                                    "id" => "send_bcc_to",
                                    "name" => "send_bcc_to",
                                    "value" => get_setting("send_bcc_to"),
                                    "class" => "form-control",
                                    "placeholder" => app_lang("email")
                                ));
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="allow_partial_invoice_payment_from_clients" class=" col-md-2"><?php echo app_lang('allow_partial_invoice_payment_from_clients'); ?></label>

                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "allow_partial_invoice_payment_from_clients", array("1" => app_lang("yes"), "0" => app_lang("no")), get_setting('allow_partial_invoice_payment_from_clients'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $days_dropdown = array(
                        "" => " - ",
                        "1" => "1 " . app_lang("day"),
                        "2" => "2 " . app_lang("days"),
                        "3" => "3 " . app_lang("days"),
                        "5" => "5 " . app_lang("days"),
                        "7" => "7 " . app_lang("days"),
                        "10" => "10 " . app_lang("days"),
                        "14" => "14 " . app_lang("days"),
                        "15" => "15 " . app_lang("days"),
                        "20" => "20 " . app_lang("days"),
                        "30" => "30 " . app_lang("days"),
                    );
                    ?>
                    <div class="form-group">
                        <div class="row">
                            <label for="send_invoice_due_pre_reminder" class=" col-md-2"><?php echo app_lang('send_first_due_invoice_reminder_notification_before'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>

                            <div class="col-md-3">
                                <?php
                                echo form_dropdown(
                                        "send_invoice_due_pre_reminder", $days_dropdown, get_setting('send_invoice_due_pre_reminder'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="send_invoice_due_pre_second_reminder" class=" col-md-2"><?php echo app_lang('send_second_due_invoice_reminder_notification_before'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>

                            <div class="col-md-3">
                                <?php
                                echo form_dropdown(
                                        "send_invoice_due_pre_second_reminder", $days_dropdown, get_setting('send_invoice_due_pre_second_reminder'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="send_invoice_due_after_reminder" class=" col-md-2"><?php echo app_lang('send_first_invoice_overdue_reminder_after'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>

                            <div class="col-md-3">
                                <?php
                                echo form_dropdown(
                                        "send_invoice_due_after_reminder", $days_dropdown, get_setting('send_invoice_due_after_reminder'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="send_invoice_due_after_second_reminder" class=" col-md-2"><?php echo app_lang('send_second_invoice_overdue_reminder_after'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>

                            <div class="col-md-3">
                                <?php
                                echo form_dropdown(
                                        "send_invoice_due_after_second_reminder", $days_dropdown, get_setting('send_invoice_due_after_second_reminder'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="send_recurring_invoice_reminder_before_creation" class=" col-md-2"><?php echo app_lang('send_recurring_invoice_reminder_before_creation'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>

                            <div class="col-md-3">
                                <?php
                                echo form_dropdown(
                                        "send_recurring_invoice_reminder_before_creation", $days_dropdown, get_setting('send_recurring_invoice_reminder_before_creation'), "class='select2 mini'"
                                );
                                ?>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="last_invoice_id" name="last_invoice_id" value="<?php echo $last_id; ?>" />
                    <div class="form-group">
                        <div class="row">
                            <label for="initial_number_of_the_invoice" class="col-md-2"><?php echo app_lang('initial_number_of_the_invoice'); ?></label>
                            <div class="col-md-3">
                                <?php
                                echo form_input(array(
                                    "id" => "initial_number_of_the_invoice",
                                    "name" => "initial_number_of_the_invoice",
                                    "type" => "number",
                                    "value" => (get_setting("initial_number_of_the_invoice") > ($last_id + 1)) ? get_setting("initial_number_of_the_invoice") : ($last_id + 1),
                                    "class" => "form-control mini",
                                    "data-rule-greaterThan" => "#last_invoice_id",
                                    "data-msg-greaterThan" => app_lang("the_invoices_id_must_be_larger_then_last_invoice_id")
                                ));
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <label for="client_can_pay_invoice_without_login" class=" col-md-2"><?php echo app_lang('client_can_pay_invoice_without_login'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('client_can_pay_invoice_without_login_help_message'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>

                            <div class="col-md-10">
                                <?php
                                echo form_dropdown(
                                        "client_can_pay_invoice_without_login", array("1" => app_lang("yes"), "0" => app_lang("no")), get_setting('client_can_pay_invoice_without_login'), "class='select2 mini'"
                                );
                                ?>
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
<?php echo view("includes/cropbox"); ?>

<?php
load_css(array(
    "assets/js/summernote/summernote.css"
));
load_js(array(
    "assets/js/summernote/summernote.min.js"
));
?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#invoice-settings-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function (data) {
                $.each(data, function (index, obj) {
                    if (obj.name === "invoice_footer") {
                        data[index]["value"] = encodeAjaxPostData(getWYSIWYGEditorHTML("#invoice_footer"));
                    }
                });
            },
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    appAlert.error(result.message);
                }
            }
        });
        $("#invoice-settings-form .select2").select2();

        initWYSIWYGEditor("#invoice_footer", {height: 100});

        $(".cropbox-upload").change(function () {
            showCropBox(this);
        });

        $(".invoice-styles .item").click(function () {
            $(".invoice-styles .item").removeClass("active");
            $(".invoice-styles .item .selected-mark").addClass("hide");
            $(this).addClass("active");
            $(this).find(".selected-mark").removeClass("hide");
            $("#invoice_style").val($(this).attr("data-value"));
        });

        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>