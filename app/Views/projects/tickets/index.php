<div class="card">
    <div class="tab-title clearfix">
        <h4><?php echo app_lang('tickets'); ?></h4>
        <div class="title-button-group">
            <?php echo modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_ticket'), array("class" => "btn btn-default mb0", "title" => app_lang('add_ticket'), "data-post-project_id" => $project_id)); ?>
        </div>
    </div>
    <div class="table-responsive">
        <table id="ticket-table" class="display" cellspacing="0" width="100%">
        </table>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#ticket-table").appTable({
            source: '<?php echo_uri("tickets/ticket_list_data_of_project/" . $project_id) ?>',
            order: [[0, "asc"]],
            columns: [
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("ticket_id") ?>', "class": "w10p"},
                {title: '<?php echo app_lang("title") ?>'},
                {title: '<?php echo app_lang("client") ?>', "class": "w15p"},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("ticket_type") ?>', "class": "w10p"},
                {title: '<?php echo app_lang("assigned_to") ?>', "class": "w10p"},
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("last_activity") ?>', "iDataSort": 7, "class": "w10p"},
                {title: '<?php echo app_lang("status") ?>', "class": "w5p"}
<?php echo $custom_field_headers; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center dropdown-option w50"}
            ],
            printColumns: combineCustomFieldsColumns([1, 2, 3, 5, 6, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 2, 3, 5, 6, 8, 9], '<?php echo $custom_field_headers; ?>')
        });
    });
</script>