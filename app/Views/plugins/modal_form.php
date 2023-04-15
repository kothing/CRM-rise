<?php echo form_open(get_uri("plugins/save"), array("id" => "plugin-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <?php
        echo view("includes/multi_file_uploader", array(
            "upload_url" => get_uri("plugins/upload_file"),
            "validation_url" => get_uri("plugins/validate_plugin_file"),
            "max_files" => 1,
            "description_placeholder" => "Envato Purchase Code"
        ));
        ?>
        <input type="hidden" name="file_name" id="plugin_file_name" value="" />
        <input type="hidden" name="file_description" id="plugin_item_purchase_code" value="" />
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default cancel-upload" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" disabled="disabled" class="btn btn-primary start-upload"><span data-feather="download" class="icon-16"></span> <?php echo app_lang('install'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {

        $("#plugin-form").appForm({
            onSubmit: function () {
                var fileName = $("#uploaded-file-previews").find("input[type=hidden]:eq(1)").val();
                var purchaseCode = $("#uploaded-file-previews").find("input:eq(1)").val();
                if (!fileName) {
                    appAlert.error("<?php echo app_lang('something_went_wrong'); ?>");
                    return false;
                }
                appLoader.show({container: ".import-client-modal-body", css: "left:0;"});
                var $button = $(this);
                $button.attr("disabled", true);

                $("#plugin_file_name").val(fileName);
                $("#plugin_item_purchase_code").val(purchaseCode);
            },
            onSuccess: function (result) {
                $("#plugin-table").appTable({reload: true});
                var $button = $(this);
                $button.removeAttr('disabled');
            }
        });

    });



</script>    
