<div class="modal-body clearfix" id="reminder-modal-body">
    <div class="container-fluid">
        <?php echo view("reminders/reminders_view_data"); ?>
    </div>
</div>

<div class="modal-footer">
    <?php echo js_anchor(app_lang('show_all_reminders'), array('id' => 'show-all-reminders-btn', "class" => "btn btn-default w100p", "type" => "button")); ?>
</div>

