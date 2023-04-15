<div id="page-content" class="page-wrapper clearfix grid-button">

    <ul class="nav nav-tabs bg-white title" role="tablist">
        <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang('tickets'); ?></h4></li>

        <?php echo view("tickets/index", array("active_tab" => "tickets_list")); ?>

        <div class="tab-title clearfix no-border tickets-page-title">
            <div class="title-button-group">
                <?php
                echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default", "title" => app_lang('manage_labels'), "data-post-type" => "ticket"));
                echo modal_anchor("", "<i data-feather='edit' class='icon-16'></i> " . app_lang('batch_update'), array("class" => "btn btn-info batch-update-btn text-white hide", "title" => app_lang('batch_update')));
                echo js_anchor("<i data-feather='check-square' class='icon-16'></i> " . app_lang("batch_update"), array("class" => "btn btn-default batch-active-btn"));
                echo js_anchor("<i data-feather='x-square' class='icon-16'></i> " . app_lang("cancel_selection"), array("class" => "btn btn-default batch-cancel-btn hide"));
                echo modal_anchor(get_uri("tickets/settings_modal_form"), "<i data-feather='settings' class='icon-16'></i> " . app_lang('settings'), array("class" => "btn btn-default", "title" => app_lang('settings')));
                echo modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_ticket'), array("class" => "btn btn-default", "title" => app_lang('add_ticket')));
                ?>
            </div>
        </div>

    </ul>

    <div class="card no-border-top-radius">
        <div class="table-responsive pb50">
            <table id="ticket-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>

</div>


<script type="text/javascript">
    $(document).ready(function () {

        var optionsVisibility = false;
        if ("<?php
                if (isset($show_options_column) && $show_options_column) {
                    echo '1';
                }
                ?>" == "1") {
            optionsVisibility = true;
        }

        var projectVisibility = false;
        if ("<?php echo $show_project_reference; ?>" == "1") {
            projectVisibility = true;
        }

        var selectOpenStatus = true, selectClosedStatus = false;
<?php if (isset($status) && $status == "closed") { ?>
            selectOpenStatus = false;
            selectClosedStatus = true;
<?php } ?>

        $("#ticket-table").appTable({
            source: '<?php echo_uri("tickets/list_data") ?>',
            serverSide: true,
            order: [[6, "desc"]],
            radioButtons: [{text: '<?php echo app_lang("open") ?>', name: "status", value: "open", isChecked: selectOpenStatus}, {text: '<?php echo app_lang("closed") ?>', name: "status", value: "closed", isChecked: selectClosedStatus}],
            filterDropdown: [{name: "ticket_type_id", class: "w200", options: <?php echo $ticket_types_dropdown; ?>}, {name: "ticket_label", class: "w200", options: <?php echo $ticket_labels_dropdown; ?>}, {name: "assigned_to", class: "w200", options: <?php echo $assigned_to_dropdown; ?>}, <?php echo $custom_field_filters; ?>],
            singleDatepicker: [{name: "created_at", defaultText: "<?php echo app_lang('created') ?>",
                    options: [
                        {value: moment().subtract(2, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_last_number_of_days'), 2); ?>"},
                        {value: moment().subtract(7, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_last_number_of_days'), 7); ?>"},
                        {value: moment().subtract(15, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_last_number_of_days'), 15); ?>"},
                        {value: moment().subtract(1, 'months').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_last_number_of_month'), 1); ?>"},
                        {value: moment().subtract(3, 'months').format("YYYY-MM-DD"), text: "<?php echo sprintf(app_lang('in_last_number_of_months'), 3); ?>"}
                    ]}],
            columns: [
                {visible: false, searchable: false, order_by: "id"},
                {title: '<?php echo app_lang("ticket_id") ?>', "iDataSort": 0, "class": "w10p all", order_by: "id"},
                {title: '<?php echo app_lang("title") ?>', order_by: "title"},
                {title: '<?php echo app_lang("client") ?>', "class": "w15p", order_by: "client"},
                {title: '<?php echo app_lang("project") ?>', "class": "w15p", visible: projectVisibility, order_by: "project"},
                {title: '<?php echo app_lang("ticket_type") ?>', "class": "w10p", order_by: "ticket_type"},
                {title: '<?php echo app_lang("assigned_to") ?>', "class": "w10p", order_by: "assigned_to"},
                {visible: false, searchable: false, order_by: "last_activity"},
                {title: '<?php echo app_lang("last_activity") ?>', "iDataSort": 7, "class": "w10p", order_by: "last_activity"},
                {title: '<?php echo app_lang("status") ?>', "class": "w5p"}
<?php echo $custom_field_headers; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center dropdown-option w10p", visible: optionsVisibility}
            ],
            printColumns: combineCustomFieldsColumns([1, 2, 3, 4, 5, 6, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 2, 3, 4, 5, 6, 8, 9], '<?php echo $custom_field_headers; ?>')
        });

    });
</script>
<?php echo view("tickets/batch_update/batch_update_script"); ?>