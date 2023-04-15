<?php
$task_id = isset($task_id) ? $task_id : 0;
$project_id = (isset($project_id) && !$task_id) ? $project_id : 0; //when loading from task view, there'll be $project_id and we should save reminders of projects and tasks separately
$client_id = isset($client_id) ? $client_id : 0;
$ticket_id = isset($ticket_id) ? $ticket_id : 0;
$lead_id = isset($lead_id) ? $lead_id : 0;
$hide_form = isset($hide_form) ? $hide_form : false;

if ($hide_form) {
    echo js_anchor(app_lang("add_reminder"), array("id" => "show-add-reminder-form", "class" => "inline-block mb15"));
}
?>

<div id="reminder-form-container" class="<?php echo $hide_form ? "hide" : ""; ?>">
    <?php echo form_open(get_uri("events/save"), array("id" => "reminder_form", "class" => "general-form", "role" => "form")); ?>
    <input type="hidden" name="type" value="reminder" />
    <input type="hidden" name="task_id" value="<?php echo $task_id; ?>" />
    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
    <input type="hidden" name="client_id" value="<?php echo $client_id; ?>" />
    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>" />
    <input type="hidden" name="lead_id" value="<?php echo $lead_id; ?>" />
    <div class="form-group">
        <div class="mt5 p0">
            <?php
            echo form_input(array(
                "id" => "title",
                "name" => "title",
                "class" => "form-control",
                "placeholder" => app_lang('title'),
                "data-rule-required" => true,
                "data-msg-required" => app_lang("field_required"),
                "autocomplete" => "off"
            ));
            ?>
        </div>
    </div>
    <div class="clearfix">
        <div class="row">
            <div class="col-md-6 col-sm-6 form-group">
                <?php
                echo form_input(array(
                    "id" => "start_date",
                    "name" => "start_date",
                    "class" => "form-control",
                    "placeholder" => app_lang('date'),
                    "autocomplete" => "off",
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required"),
                ));
                ?>
            </div>
            <div class=" col-md-6 col-sm-6 form-group">
                <?php
                echo form_input(array(
                    "id" => "start_time",
                    "name" => "start_time",
                    "class" => "form-control",
                    "placeholder" => app_lang('time'),
                    "autocomplete" => "off",
                    "data-rule-required" => true,
                    "data-msg-required" => app_lang("field_required"),
                ));
                ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <label for="event_recurring" class=" col-md-4 col-xs-5 col-sm-4"><?php echo app_lang('repeat'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather="help-circle" class="icon-16"></i></span></label>
            <div class=" col-md-8 col-xs-7 col-sm-8">
                <?php
                echo form_checkbox("recurring", "1", false, "id='event_recurring' class='form-check-input'");
                ?>                       
            </div>
        </div>
    </div>  

    <div id="recurring_fields" class="hide"> 
        <div class="form-group">
            <div class="row">
                <label for="repeat_every" class=" col-md-3 col-xs-12"><?php echo app_lang('repeat_every'); ?></label>
                <div class="col-md-4 col-xs-6">
                    <?php
                    echo form_input(array(
                        "id" => "repeat_every",
                        "name" => "repeat_every",
                        "type" => "number",
                        "value" => 1,
                        "min" => 1,
                        "class" => "form-control recurring_element",
                        "placeholder" => app_lang('repeat_every'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required")
                    ));
                    ?>
                </div>
                <div class="col-md-5 col-xs-6">
                    <?php
                    echo form_dropdown(
                            "repeat_type", array(
                        "days" => app_lang("interval_days"),
                        "weeks" => app_lang("interval_weeks"),
                        "months" => app_lang("interval_months"),
                        "years" => app_lang("interval_years"),
                            ), "days", "class='select2 recurring_element' id='repeat_type'"
                    );
                    ?>
                </div>
            </div>
        </div>    

        <div class="form-group">
            <div class="row">
                <label for="no_of_cycles" class=" col-md-3"><?php echo app_lang('cycles'); ?></label>
                <div class="col-md-4">
                    <?php
                    echo form_input(array(
                        "id" => "no_of_cycles",
                        "name" => "no_of_cycles",
                        "type" => "number",
                        "min" => 1,
                        "value" => "",
                        "class" => "form-control",
                        "placeholder" => app_lang('cycles')
                    ));
                    ?>
                </div>
                <div class="col-md-5 mt5">
                    <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('recurring_cycle_instructions'); ?>"><i data-feather="help-circle" class="icon-14"></i></span>
                </div>
            </div>
        </div>

    </div>

    <div class="mb20 p0">
        <button type="submit" class="btn btn-primary w100p"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('add'); ?></button>
    </div>

    <?php echo form_close(); ?>

</div>

<div class="table-responsive pb100">
    <table id="reminders-table" class="display no-thead b-t b-b-only no-hover" cellspacing="0" width="100%">         
    </table>
</div>

<div class="table-responsive pb100 hide">
    <table id="all-reminders-table" class="display no-thead b-t b-b-only no-hover" cellspacing="0" width="100%">         
    </table>
</div>

<script type="text/javascript">
    var $tableSelector = $("#reminders-table");

    $(document).ready(function () {
        initScrollbar('#reminder-modal-body', {
            setHeight: $(window).height() - 139
        });

        loadReminderTable = function (type) {
            type = type || "reminders";
            var taskId = "<?php echo $task_id; ?>" || 0,
                    projectId = "<?php echo $project_id; ?>" || 0,
                    clientId = "<?php echo $client_id; ?>" || 0,
                    leadId = "<?php echo $lead_id; ?>" || 0,
                    ticketId = "<?php echo $ticket_id; ?>" || 0;


            if (type === "all") {
                if ($("#all-reminders-table").hasClass("dataTable")) {
                    //loading again after closing reminders modal without page reload
                    return;
                }

                $("#reminders-table").closest(".table-responsive").addClass("hide");
                $("#all-reminders-table").closest(".table-responsive").removeClass("hide");
                $tableSelector = $("#all-reminders-table");
            }

            $tableSelector.appTable({
                source: '<?php echo_uri("events/reminders_list_data") ?>/' + type + '/' + taskId + '/' + projectId + '/' + clientId + '/' + leadId + '/' + ticketId,
                hideTools: true,
                order: [[0, "asc"]],
                displayLength: 100,
                columns: [
                    {visible: false},
                    {title: '<?php echo app_lang("title"); ?>'},
                    {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center dropdown-option w35"}
                ],
                onInitComplete: function () {
                    appLoader.hide();
                }
            });
        };

        $('body').on('click', "#show-all-reminders-btn", function () {
            loadReminderTable("all");
            appLoader.show({container: "#all-reminders-table", css: "left:0; top:170px"});
            $(this).addClass("disabled");
        });

        loadReminderTable();

        $('#ajaxModal').on('hidden.bs.modal', function () {
            $("#ajaxModal").removeClass("reminder-modal");

            //reload task details page
            if ($("#task-reminders").length) {
                location.reload();
            }
        });

        setDatePicker("#start_date");
        setTimePicker("#start_time");

        feather.replace();

        $("#reminder_form").appForm({
            isModal: false,
            onSuccess: function (result) {
                $tableSelector.appTable({newData: result.data, dataId: result.id});

                $("#title").val("");
                if ($("#event_recurring").is(":checked")) {
                    $("#event_recurring").trigger("click");
                }

                $("#title").focus();

                if (typeof getReminders === 'function') {
                    getReminders();
                }
            }
        });

        //show/hide recurring fields
        $("#event_recurring").click(function () {
            if ($(this).is(":checked")) {
                $("#recurring_fields").removeClass("hide");
            } else {
                $("#recurring_fields").addClass("hide");
            }
        });

        //show reminder form
        $("#show-add-reminder-form").click(function () {
            $(this).addClass("hide");
            $("#reminder-form-container").removeClass("hide");
        });

        $('[data-bs-toggle="tooltip"]').tooltip();

        $("#reminder_form .select2").select2();
    });
</script>
