<div class="card">
    <div class="tab-title clearfix">
        <h4><?php echo app_lang('contracts'); ?></h4>
        <div class="title-button-group">
            <?php
            echo modal_anchor(get_uri("contracts/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_contract'), array("class" => "btn btn-default", "title" => app_lang('add_contract'), "data-post-project_id" => $project_id));
            ?>
        </div>
    </div>

    <div class="table-responsive">
        <table id="contract-table" class="display" width="100%">       
        </table>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        $("#contract-table").appTable({
            source: '<?php echo_uri("contracts/contract_list_data_of_project/" . $project_id) ?>',
            order: [[0, "desc"]],
            filterDropdown: [{name: "status", class: "w150", options: <?php echo view("contracts/contract_statuses_dropdown"); ?>}, <?php echo $custom_field_filters; ?>],
            columns: [
                {title: '<?php echo app_lang("id") ?>', "class": "w50 all"},
                {title: "<?php echo app_lang("title") ?> ", "class": "w15p all"},
                {title: "<?php echo app_lang("client") ?>", "class": "w15p"},
                {title: "<?php echo app_lang("project") ?>", "class": "w15p"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("contract_date") ?>", "iDataSort": 4, "class": "w10p"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("valid_until") ?>", "iDataSort": 6, "class": "w10p"},
                {title: "<?php echo app_lang("amount") ?>", "class": "text-right w10p"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center"}
<?php echo $custom_field_headers; ?>,
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w150"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            summation: [{column: 8, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol}]
        });
    });
</script>