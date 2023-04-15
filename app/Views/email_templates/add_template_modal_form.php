<?php echo form_open(get_uri("email_templates/save_template"), array("id" => "add-template-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="" />
        <input type="hidden" name="template_name" value="<?php echo $template_name; ?>" />

        <div class="form-group">
            <div class="row">
                <label for="language" class=" col-md-3"><?php echo app_lang('language'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_dropdown(
                            "language", $language_dropdown, "", "class='select2'"
                    );
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
        $("#add-template-form").appForm({
            onSuccess: function (result) {
                $(result.data).insertAfter(".email-template-tabs:last");

                appAlert.success(result.message, {duration: 10000});
            }
        });

        $("#add-template-form .select2").select2();
    });
</script>