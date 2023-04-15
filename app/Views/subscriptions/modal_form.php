<?php echo form_open(get_uri("subscriptions/save"), array("id" => "subscription-form", "class" => "general-form", "role" => "form")); ?>
<div id="subscriptions-dropzone" class="post-dropzone">
    <div class="modal-body clearfix">
        <div class="container-fluid">
            <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />

            <?php if ($is_clone || $estimate_id) { ?>
                <?php if ($is_clone) { ?>
                    <input type="hidden" name="is_clone" value="1" />
                <?php } ?>
            <?php } ?>

            <div class="form-group">
                <div class="row">
                    <label for="title" class=" col-md-3"><?php echo app_lang('title'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "title",
                            "name" => "title",
                            "value" => $model_info->title,
                            "class" => "form-control",
                            "placeholder" => app_lang('title'),
                            "autofocus" => true,
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div> 
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <label for="subscription_bill_date" class=" col-md-3"><?php echo app_lang('first_billing_date'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('first_billing_date_cant_be_past_message'); ?>"><i data-feather="help-circle" class="icon-16"></i></span></label>
                    <div class="col-md-9">
                        <input type="hidden" id="today_date" value="<?php echo get_my_local_time('Y-m-d'); ?>" />
                        <?php
                        echo form_input(array(
                            "id" => "subscription_bill_date",
                            "name" => "subscription_bill_date",
                            "value" => is_date_exists($model_info->bill_date) ? $model_info->bill_date : "",
                            "class" => "form-control recurring_element",
                            "placeholder" => app_lang('first_billing_date'),
                            "autocomplete" => "off",
                            "data-rule-greaterThanOrEqual" => "#today_date",
                            "data-msg-greaterThanOrEqual" => app_lang("date_must_be_equal_or_greater_than_today")
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <?php if (count($companies_dropdown) > 1) { ?>
                <div class="form-group">
                    <div class="row">
                        <label for="company_id" class=" col-md-3"><?php echo app_lang('company'); ?></label>
                        <div class="col-md-9">
                            <?php
                            echo form_input(array(
                                "id" => "company_id",
                                "name" => "company_id",
                                "value" => $model_info->company_id,
                                "class" => "form-control",
                                "placeholder" => app_lang('company')
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if ($client_id) { ?>
                <input type="hidden" name="subscription_client_id" value="<?php echo $client_id; ?>" />
            <?php } else { ?>
                <div class="form-group">
                    <div class="row">
                        <label for="subscription_client_id" class=" col-md-3"><?php echo app_lang('client'); ?></label>
                        <div class="col-md-9">
                            <?php
                            echo form_dropdown("subscription_client_id", $clients_dropdown, array($model_info->client_id), "class='select2 validate-hidden' id='subscription_client_id' data-rule-required='true', data-msg-required='" . app_lang('field_required') . "'");
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <div class="form-group">
                <div class="row">
                    <label for="tax_id" class=" col-md-3"><?php echo app_lang('tax'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_dropdown("tax_id", $taxes_dropdown, array($model_info->tax_id), "class='select2 tax-select2'");
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="tax_id" class=" col-md-3"><?php echo app_lang('second_tax'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_dropdown("tax_id2", $taxes_dropdown, array($model_info->tax_id2), "class='select2 tax-select2'");
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="repeat_type" class=" col-md-3"><?php echo app_lang('repeat_type'); ?></label>
                    <div class="col-md-4">
                        <?php
                        echo form_dropdown(
                                "repeat_type", array(
                            "days" => app_lang("interval_days"),
                            "weeks" => app_lang("interval_weeks"),
                            "months" => app_lang("interval_months"),
                            "years" => app_lang("interval_years"),
                                ), $model_info->repeat_type ? $model_info->repeat_type : "months", "class='select2 recurring_element' id='repeat_type'"
                        );
                        ?>
                    </div>
                </div>
            </div>

            <div class = "form-group hide" id = "next_recurring_date_container" >
                <div class="row">
                    <label for = "next_recurring_date" class = " col-md-3"><?php echo app_lang('next_recurring_date'); ?>  </label>
                    <div class=" col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "next_recurring_date",
                            "name" => "next_recurring_date",
                            "class" => "form-control",
                            "placeholder" => app_lang('next_recurring_date'),
                            "autocomplete" => "off",
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="subscription_note" class=" col-md-3"><?php echo app_lang('note'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_textarea(array(
                            "id" => "subscription_note",
                            "name" => "subscription_note",
                            "value" => $model_info->note ? $model_info->note : "",
                            "class" => "form-control",
                            "placeholder" => app_lang('note'),
                            "data-rich-text-editor" => true
                        ));
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="row">
                    <label for="subscription_labels" class=" col-md-3"><?php echo app_lang('labels'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "subscription_labels",
                            "name" => "labels",
                            "value" => $model_info->labels,
                            "class" => "form-control",
                            "placeholder" => app_lang('labels')
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <?php echo view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "col-md-3", "field_column" => " col-md-9")); ?> 

            <div class="form-group">
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        echo view("includes/file_list", array("files" => $model_info->files));
                        ?>
                    </div>
                </div>
            </div>

            <?php echo view("includes/dropzone_preview"); ?>
        </div>
    </div>

    <div class="modal-footer">
        <button class="btn btn-default upload-file-button float-start btn-sm round me-auto" type="button" style="color:#7988a2"><i data-feather="camera" class="icon-16"></i> <?php echo app_lang("upload_file"); ?></button>
        <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    </div>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        if ("<?php echo $estimate_id; ?>" || "<?php echo $proposal_id; ?>" || "<?php echo $order_id; ?>" || "<?php echo $contract_id; ?>") {
            RELOAD_VIEW_AFTER_UPDATE = false; //go to related page
        }

        var uploadUrl = "<?php echo get_uri("subscriptions/upload_file"); ?>";
        var validationUri = "<?php echo get_uri("subscriptions/validate_subscriptions_file"); ?>";

        var dropzone = attachDropzoneWithForm("#subscriptions-dropzone", uploadUrl, validationUri);

        $("#subscription-form").appForm({
            onSuccess: function (result) {
                if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    location.reload();
                } else {
                    window.location = "<?php echo site_url('subscriptions/view'); ?>/" + result.id;
                }
            },
            onAjaxSuccess: function (result) {
                if (!result.success && result.next_recurring_date_error) {
                    $("#next_recurring_date").val(result.next_recurring_date_value);
                    $("#next_recurring_date_container").removeClass("hide");

                    $("#subscription-form").data("validator").showErrors({
                        "next_recurring_date": result.next_recurring_date_error
                    });
                }
            }
        });
        $("#subscription-form .tax-select2").select2();
        $("#repeat_type").select2();

        $("#subscription_labels").select2({multiple: true, data: <?php echo json_encode($label_suggestions); ?>});
        $("#company_id").select2({data: <?php echo json_encode($companies_dropdown); ?>});

        $("#subscription_client_id").select2();

        setDatePicker("#today_date");
        
        setDatePicker("#subscription_bill_date", {
            startDate: moment().local().format() //set min date = today
        });
        
        setDatePicker("#next_recurring_date", {
            startDate: moment().add(1, 'days').local().format() //set min date = tomorrow
        });

        $('[data-bs-toggle="tooltip"]').tooltip();

    });
</script>