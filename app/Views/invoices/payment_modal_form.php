<?php echo form_open(get_uri("invoice_payments/save_payment"), array("id" => "invoice-payment-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />

        <?php if ($invoice_id) { ?>
            <input type="hidden" name="invoice_id" value="<?php echo $invoice_id; ?>" />
        <?php } else { ?>
            <div class="form-group">
                <div class="row">
                    <label for="invoice_id" class=" col-md-3"><?php echo app_lang('invoice'); ?></label>
                    <div class="col-md-9">
                        <?php
                        echo form_dropdown("invoice_id", $invoices_dropdown, "", "class='select2 validate-hidden' id='invoice_id' data-rule-required='true' data-msg-required='" . app_lang('field_required') . "' ");
                        ?>
                    </div>
                </div>
            </div>
        <?php } ?>

        <div class="form-group">
            <div class="row">
                <label for="invoice_payment_method_id" class=" col-md-3"><?php echo app_lang('payment_method'); ?></label>
                <div class="col-md-9">
                    <?php
                    helper('cookie');

                    echo form_dropdown("invoice_payment_method_id", $payment_methods_dropdown, array($model_info->payment_method_id ? $model_info->payment_method_id : get_cookie("user_" . $login_user->id . "_payment_method")), "class='select2 selected_payment_method'");
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="invoice_payment_date" class=" col-md-3"><?php echo app_lang('payment_date'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "invoice_payment_date",
                        "name" => "invoice_payment_date",
                        "value" => $model_info->payment_date ? $model_info->payment_date : get_my_local_time("Y-m-d"),
                        "class" => "form-control",
                        "placeholder" => app_lang('payment_date'),
                        "autocomplete" => "off",
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required")
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="invoice_payment_amount" class=" col-md-3"><?php echo app_lang('amount'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "invoice_payment_amount",
                        "name" => "invoice_payment_amount",
                        "value" => $amount,
                        "class" => "form-control",
                        "placeholder" => app_lang('amount'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <label for="invoice_payment_note" class="col-md-3"><?php echo app_lang('note'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_textarea(array(
                        "id" => "invoice_payment_note",
                        "name" => "invoice_payment_note",
                        "value" => $model_info->note ? process_images_from_content($model_info->note, false) : "",
                        "class" => "form-control",
                        "placeholder" => app_lang('description'),
                        "data-rich-text-editor" => true
                    ));
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#invoice-payment-form").appForm({
            onSuccess: function (result) {
                if (typeof RELOAD_VIEW_AFTER_UPDATE !== "undefined" && RELOAD_VIEW_AFTER_UPDATE) {
                    location.reload();
                } else {
                    if ($("#invoice-payment-table").length) {
                        //it's from invoice details view
                        $("#invoice-payment-table").appTable({newData: result.data, dataId: result.id});
                        $("#invoice-total-section").html(result.invoice_total_view);
                        if (typeof updateInvoiceStatusBar == 'function') {
                            updateInvoiceStatusBar(result.invoice_id);
                        }
                    } else {
                        //it's from invoices list view
                        //update table data
                        $("#" + $(".dataTable:visible").attr("id")).appTable({reload: true});
                    }
                }
            }
        });
        $("#invoice-payment-form .select2").select2();

        setDatePicker("#invoice_payment_date");

        //save the lastly selected payment method to cookie user-wise
        $(".selected_payment_method").on("change", function () {
            var paymentMethodId = $(this).val();
            if (paymentMethodId) {
                setCookie("user_" + "<?php echo $login_user->id; ?>" + "_payment_method", paymentMethodId);
            }
        });
        
        //get due balance of selected invoice
        $("#invoice_id").select2().on("change", function () {
            var invoice_id = $(this).val();
            if ($(this).val()) {
                $.ajax({
                    url: "<?php echo get_uri("invoice_payments/get_invoice_payment_amount_suggestion"); ?>" + "/" + invoice_id,
                    cache: false,
                    type: 'POST',
                    dataType: "json",
                    success: function (response) {
                        if (response && response.success) {
                            $("#invoice_payment_amount").val(response.invoice_total_summary.balance_due);
                        }
                    }
                });
            }
        });
    });
</script>