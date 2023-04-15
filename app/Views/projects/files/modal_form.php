<?php echo form_open(get_uri("projects/save_file"), array("id" => "file-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />

        <div class="form-group">
            <div class="row">
                <label for="category_id" class=" col-md-3"><?php echo app_lang('category'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_dropdown("category_id", $file_categories_dropdown, array($model_info->category_id), "class='select2' id='category_id'");
                    ?>
                </div>
            </div>
        </div>
        <?php echo view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "col-md-3", "field_column" => " col-md-9")); ?> 
        <?php if ($model_info->id) { ?>
            <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />

            <div class="form-group">
                <div class="row">
                    <label for="description" class=" col-md-3"><?php echo app_lang('description'); ?></label>
                    <div class=" col-md-9">
                        <?php
                        echo form_input(array(
                            "id" => "description",
                            "name" => "description",
                            "value" => $model_info->description,
                            "class" => "form-control description-field",
                            "placeholder" => app_lang('description'),
                            "autofocus" => true,
                        ));
                        ?>
                    </div>
                </div>
            </div>

            <?php
        } else {
            echo view("includes/multi_file_uploader", array(
                "upload_url" => get_uri("projects/upload_file"),
                "validation_url" => get_uri("projects/validate_project_file"),
            ));
        }
        ?>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default cancel-upload" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" disabled="disabled" class="btn btn-primary start-upload" id="file-save-button"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {

        $("#file-form").appForm({
            onSuccess: function (result) {
                $("#project-file-table").appTable({reload: true});
            }
        });

        $("#file-form .select2").select2();

<?php if ($model_info->id) { ?>
            $('#file-save-button').removeAttr('disabled');
<?php } ?>

    });



</script>    
