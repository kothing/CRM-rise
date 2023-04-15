<?php echo form_open(get_uri("subscriptions/activate_as_internal_subscription"), array("id" => "activate-as-internal-subscription-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="subscription_id" value="<?php echo $subscription_id; ?>" />
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <?php echo app_lang("activate_as_internal_subscription_message_1"); ?>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <?php echo app_lang("activate_as_internal_subscription_message_2"); ?>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <i data-feather="help-circle" class="icon-16"></i> <?php echo app_lang("cron_job_required"); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('cancel'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('ok'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#activate-as-internal-subscription-form").appForm({
            onSuccess: function (result) {
                location.reload();
            }
        });
    });


</script>