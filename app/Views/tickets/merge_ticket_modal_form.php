<?php echo form_open(get_uri("tickets/save_merge_ticket"), array("id" => "merge-ticket-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="ticket_id" value="<?php echo $model_info->id; ?>" />

        <div class="form-group">
            <div class="row">
                <label for="merge_with_ticket_id" class=" col-md-3"><?php echo app_lang('move_all_comments_or_notes_from'); ?></label>
                <div class="col-md-9">
                    <?php
                    echo form_input(array(
                        "id" => "merge_with_ticket_id",
                        "name" => "merge_with_ticket_id",
                        "class" => "form-control validate-hidden",
                        "placeholder" => app_lang('ticket'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required")
                    ));
                    ?>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('merge'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {

        $("#merge-ticket-form").appForm({
            onSuccess: function () {
                location.reload();
            }
        });

        $("#merge_with_ticket_id").select2({data: <?php echo json_encode($tickets_dropdown); ?>});

    });

</script>