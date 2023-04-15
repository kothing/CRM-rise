<?php echo form_open(get_uri("tickets/save_batch_update"), array("id" => "batch-update-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="ticket_ids" value="<?php echo $ticket_ids; ?>" />
        <input type="hidden" name="batch_fields" value="" id="batch_fields" />

        <div class="form-group">
            <div class="row">
                <div class="col-md-1">
                    <?php
                    echo form_checkbox("", "1", false, "class=' batch-update-checkbox form-check-input'");
                    ?>
                </div>
                <label for="ticket_type_id" class=" col-md-2 text-off"><?php echo app_lang('ticket_type'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_dropdown("ticket_type_id", $ticket_types_dropdown, "", "class='select2'");
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-1">
                    <?php
                    echo form_checkbox("", "1", false, "class=' batch-update-checkbox form-check-input'");
                    ?>
                </div>
                <label for="assigned_to" class=" col-md-2 text-off"><?php echo app_lang('assign_to'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_dropdown("assigned_to", $assigned_to_dropdown, "", "class='select2'");
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-1">
                    <?php
                    echo form_checkbox("", "1", false, "class=' batch-update-checkbox form-check-input'");
                    ?>
                </div>
                <label for="ticket_labels" class=" col-md-2 text-off"><?php echo app_lang('labels'); ?></label>
                <div class=" col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "ticket_labels",
                        "name" => "labels",
                        "class" => "form-control",
                        "placeholder" => app_lang('labels')
                    ));
                    ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-1">
                    <?php
                    echo form_checkbox("", "1", false, "class=' batch-update-checkbox form-check-input'");
                    ?>                       
                </div>
                <label for="status_id" class=" col-md-2 text-off"><?php echo app_lang('status'); ?></label>
                <div class="col-md-9">
                    <?php
                    $status_dropdown = array(
                        "open" => app_lang("open"),
                        "closed" => app_lang("closed")
                    );

                    echo form_dropdown("status", $status_dropdown, "", "class='select2'");
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span class="fa fa-close"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span class="fa fa-check-circle"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        //store all checked field name to an input field
        var batchFields = [];

        $("#batch-update-form").appForm({
            beforeAjaxSubmit: function (data) {
                var batchFieldsIndex = 0;

                $.each(data, function (index, obj) {
                    var $checkBox = $("[name='" + obj.name + "']").closest(".form-group").find("input.batch-update-checkbox");
                    if ($checkBox && $checkBox.is(":checked")) {
                        batchFields.push(obj.name);
                    }

                    if (obj.name === "batch_fields") {
                        batchFieldsIndex = index;
                    }
                });

                var serializeOfArray = batchFields.join("-");
                data[batchFieldsIndex]["value"] = serializeOfArray;
            },
            onSuccess: function (result) {
                hideBatchTicketsBtn();
                batchFields = [];

                if (result.success) {
                    if ($(".dataTable:visible").attr("id")) {
                        //update data of tickets table 
                        $("#" + $(".dataTable:visible").attr("id")).appTable({reload: true});
                    }

                    appAlert.success(result.message, {duration: 10000});
                }
            }
        });

        $("#batch-update-form .select2").select2();

        $("#ticket_labels").select2({multiple: true, data: <?php echo json_encode($label_suggestions); ?>});

        //toggle checkbox and label
        $(".form-group .col-md-9 input, select").on('change', function () {
            var checkBox = $(this).closest(".form-group").find("input.batch-update-checkbox"),
                    label = $(this).closest(".form-group").find("label");

            if ($(this).val()) {
                if (!checkBox.is(":checked")) {
                    checkBox.trigger('click');
                    label.removeClass("text-off");
                }
            } else {
                checkBox.removeAttr("checked");
                label.addClass("text-off");
            }
        });

        //toggle labels
        $(".batch-update-checkbox").click(function () {
            var label = $(this).closest(".form-group").find("label");

            if ($(this).is(":checked")) {
                label.removeClass("text-off");
            } else {
                label.addClass("text-off");
            }
        });
    });
</script>