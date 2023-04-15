<?php echo form_open(get_uri("projects/save_project_member"), array("id" => "project-member-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
        <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />

        <div class="form-group" style="min-height: 50px">
            <div class="row">
                <label for="user_id" class=" col-md-3"><?php echo ($add_user_type == "client_contacts") ? app_lang('contact') : app_lang('member'); ?></label>
                <div class="col-md-9">
                    <div class="select-member-field">
                        <div class="select-member-form clearfix pb10">
                            <?php echo form_dropdown("user_id[]", $users_dropdown, array($model_info->user_id), "class='user_select2 col-md-10 p0' id='user_id'"); ?>
                            <?php echo js_anchor("<i data-feather='x' class='icon-16'></i> ", array("class" => "remove-member delete ml20")); ?>
                        </div>                                
                    </div>
                    <?php echo js_anchor("<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_more'), array("class" => "add-member", "id" => "add-more-user")); ?>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal-footer">
    <div id="link-of-add-task-modal" class="hide">
        <?php echo modal_anchor(get_uri("projects/task_modal_form"), "", array()); ?>
    </div>

    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>

    <?php if ($view_type == "from_project_modal") { ?>
        <button type="button" id="next-button" class="btn btn-info text-white"><span data-feather="arrow-right-circle" class="icon-16"></span> <?php echo app_lang('next'); ?></button>
        <button type="button" id="save-and-continue-button" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save_and_continue'); ?></button>
    <?php } else { ?>
        <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    <?php } ?>

</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        window.projectMemberForm = $("#project-member-form").appForm({
            closeModalOnSuccess: false,
            onSuccess: function (result) {
                if (result.id !== "exists") {
                    for (i = 0; i < result.data.length; i++) {
<?php if ($add_user_type == "client_contacts") { ?>
                            $("#project-client-contacts-table").appTable({newData: result.data[i], dataId: result.id[i]});
<?php } else { ?>
                            $("#project-member-table").appTable({newData: result.data[i], dataId: result.id[i]});
<?php } ?>
                    }

                }

                if (window.showAddMultipleTasksModal) {
                    showAddMultipleTaskModal();
                } else {
                    window.projectMemberForm.closeModal();
                }
            }
        });

        var $wrapper = $('.select-member-field'),
                $field = $('.select-member-form:first-child', $wrapper).clone(); //keep a clone for future use.

        $(".add-member", $(this)).click(function (e) {
            var $newField = $field.clone();

            //remove used options
            $('.user_select2').each(function () {
                $newField.find("option[value='" + $(this).val() + "']").remove();
            });

            var $newObj = $newField.appendTo($wrapper);
            $newObj.find(".user_select2").select2();

            $newObj.find('.remove-member').click(function () {
                $(this).parent('.select-member-form').remove();
                showHideAddMore($field);
            });

            showHideAddMore($field);
        });

        showHideAddMore($field);

        $(".remove-member").hide();
        $(".user_select2").select2();

        function showHideAddMore($field) {
            //hide add more button if there are no options 
            if ($('.select-member-form').length < $field.find("option").length) {
                $("#add-more-user").show();
            } else {
                $("#add-more-user").hide();
            }
        }

        //open add multiple task modal
        window.showAddMultipleTasksModal = false;

        $("#save-and-continue-button").click(function () {
            window.showAddMultipleTasksModal = true;
            $(this).trigger("submit");
        });

        $("#next-button").click(function () {
            showAddMultipleTaskModal();
        });

        function showAddMultipleTaskModal() {
            var $addMultipleTasksLink = $("#link-of-add-task-modal").find("a");
            $addMultipleTasksLink.attr("data-post-project_id", <?php echo $project_id; ?>);
            $addMultipleTasksLink.attr("data-title", "<?php echo app_lang('add_multiple_tasks') ?>");
            $addMultipleTasksLink.attr("data-post-add_type", "multiple");

            $addMultipleTasksLink.trigger("click");
        }

    });
</script>    