<div class="modal-body clearfix" id="reminder-action-modal">
    <div class="container-fluid">
        <div class="clearfix">
            <div class="col-md-12">
                <strong class="font-18">
                    <?php
                    $context_info = get_reminder_context_info($model_info);
                    $context_url = get_array_value($context_info, "context_url");
                    $context_icon = get_array_value($context_info, "context_icon");
                    $context_icon = $context_icon ? "<i class='icon-16 text-off' data-feather='$context_icon'></i> " : "";

                    if ($context_url) {
                        echo $context_icon . js_anchor($model_info->title, array("data-act" => "dismiss-reminder", "data-id" => $model_info->id, "data-details-url" => $context_url));
                    } else {
                        echo $model_info->title;
                    }
                    ?>
                </strong>
            </div>
        </div>

        <div class="col-md-12">
            <span><?php echo view("events/event_time", array("model_info" => $model_info, "is_reminder" => true)); ?></span>
        </div>
    </div>
</div>

<div class="modal-footer">
    <?php
    if (!$model_info->share_with) {
        echo js_anchor("<i data-feather='bell-off' class='icon-16'></i> " . app_lang("snooze"), array("class" => "btn btn-warning text-white", "data-act" => "snooze-reminder", "data-id" => $model_info->id));
    }
    echo js_anchor("<i data-feather='check' class='icon-16'></i> " . app_lang("dismiss"), array("class" => "btn btn-success text-white", "data-act" => "dismiss-reminder", "data-id" => $model_info->id));
    ?>
</div>