<?php echo form_open(get_uri("checklist_template/save"), array("id" => "checklist-template-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <div class="form-group">
            <br />
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
    </div>
</div>

<div class="modal-footer">
    <div id="link-of-add-new-modal" class="hide">
        <?php
        echo modal_anchor(get_uri("checklist_template/modal_form"), "", array());
        ?>
    </div>

    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button id="save-and-add-button" type="button" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save_and_add_more'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        //show add new template modal after save
        window.showAddNewModal = false;

        $("#save-and-add-button").click(function () {
            window.showAddNewModal = true;
            $(this).trigger("submit");
        });

        window.checklistTemplateForm = $("#checklist-template-form").appForm({
            closeModalOnSuccess: false,
            onSuccess: function (result) {
                $("#task-checklist-template-table").appTable({newData: result.data, dataId: result.id});

                if (window.showAddNewModal) {
                    var $checklistTemplateAddLink = $("#link-of-add-new-modal").find("a");

                    //add new template
                    $checklistTemplateAddLink.attr("data-action-url", "<?php echo get_uri("checklist_template/modal_form"); ?>");
                    $checklistTemplateAddLink.attr("data-title", "<?php echo app_lang('add_checklist_template') ?>");

                    $checklistTemplateAddLink.trigger("click");
                } else {
                    window.checklistTemplateForm.closeModal();
                }
            }
        });

        setTimeout(function () {
            $("#title").focus();
        }, 200);
    });
</script>