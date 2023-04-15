<div class="tab-content">
    <?php echo form_open(get_uri("clients/save/"), array("id" => "company-form", "class" => "general-form dashed-row white", "role" => "form")); ?>
    <div class="card">
        <div class=" card-header">
            <?php if ($model_info->type == "person") { ?>
                <h4> <?php echo app_lang('contact_info'); ?></h4>
            <?php } else { ?>
                <h4> <?php echo app_lang('client_info'); ?></h4>
            <?php } ?>
        </div>
        <div class="card-body">
            <?php echo view("clients/client_form_fields"); ?>
        </div>
        <?php if ($can_edit_clients) { ?>
            <div class="card-footer rounded-bottom">
                <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
            </div>
        <?php } ?>
    </div>
    <?php echo form_close(); ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#company-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                appAlert.success(result.message, {duration: 10000});
            }
        });
    });
</script>