<div id="page-content" class="page-wrapper clearfix grid-button leads-view">
    <ul class="nav nav-tabs bg-white title" role="tablist">
        <li class="title-tab leads-title-section"><h4 class="pl15 pt10 pr15"><?php echo app_lang("leads"); ?></h4></li>

        <?php echo view("leads/tabs", array("active_tab" => "leads_list")); ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">
                <?php echo modal_anchor(get_uri("leads/import_leads_modal_form"), "<i data-feather='upload' class='icon-16'></i> " . app_lang('import_leads'), array("class" => "btn btn-default", "title" => app_lang('import_leads'))); ?>
                <?php echo modal_anchor(get_uri("leads/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_lead'), array("class" => "btn btn-default", "title" => app_lang('add_lead'))); ?>
            </div>
        </div>
    </ul>

    <div class="card">
        <div class="table-responsive">
            <table id="lead-table" class="display" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

    $("#lead-table").appTable({
    source: '<?php echo_uri("leads/list_data") ?>',
            serverSide: true,
            columns: [
            {title: "<?php echo app_lang("company_name") ?>", "class": "all", order_by: "company_name"},
            {title: "<?php echo app_lang("primary_contact") ?>", order_by: "primary_contact"},
            {title: "<?php echo app_lang("owner") ?>", order_by: "owner_name"},
            {visible: false, searchable: false, order_by: "created_date"},
            {title: "<?php echo app_lang("created_date") ?>", "iDataSort": 3, order_by: "created_date"},
            {title: "<?php echo app_lang("status") ?>", order_by: "status"}
<?php echo $custom_field_headers; ?>,
            {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            filterDropdown: [
            {name: "status", class: "w200", options: <?php echo view("leads/lead_statuses"); ?>},
            {name: "source", class: "w200", options: <?php echo view("leads/lead_sources"); ?>}
<?php if (get_array_value($login_user->permissions, "lead") !== "own") { ?>
                , {name: "owner_id", class: "w200", options: <?php echo json_encode($owners_dropdown); ?>}
<?php } ?>
            , <?php echo $custom_field_filters; ?>
            ],
            rangeDatepicker: [{startDate: {name: "start_date", value: ""}, endDate: {name: "end_date", value: ""}, showClearButton: true}],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 4, 5], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 4, 5], '<?php echo $custom_field_headers; ?>')
    });
    }
    );
</script>

<?php echo view("leads/update_lead_status_script"); ?>