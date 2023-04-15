<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card clearfix">
        <ul id="contract-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang('contracts'); ?></h4></li>
            <li><a id="monthly-contract-button" role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#monthly-contracts"><?php echo app_lang("monthly"); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("contracts/yearly/"); ?>" data-bs-target="#yearly-contracts"><?php echo app_lang('yearly'); ?></a></li>
            <div class="tab-title clearfix no-border contracts-page-title">
                <div class="title-button-group">
                    <?php echo modal_anchor(get_uri("contracts/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_contract'), array("class" => "btn btn-default", "title" => app_lang('add_contract'))); ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-contracts">
                <div class="table-responsive">
                    <table id="monthly-contract-table" class="display" cellspacing="0" width="100%">   
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-contracts"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    loadContractsTable = function (selector, dateRange) {
        $(selector).appTable({
            source: '<?php echo_uri("contracts/list_data") ?>',
            order: [[0, "desc"]],
            dateRangeType: dateRange,
            filterDropdown: [{name: "status", class: "w150", options: <?php echo view("contracts/contract_statuses_dropdown"); ?>}, <?php echo $custom_field_filters; ?>],
            columns: [
                {title: '<?php echo app_lang("contract") ?>', "class": "w100 all"},
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
    };

    $(document).ready(function () {
        loadContractsTable("#monthly-contract-table", "monthly");
    });

</script>