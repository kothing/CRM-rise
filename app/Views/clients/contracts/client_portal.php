<?php if (isset($page_type) && $page_type === "full") { ?>
    <div id="page-content" class="clearfix page-wrapper">
    <?php } ?>

    <div class="card clearfix">
        <?php if (isset($page_type) && $page_type === "full") { ?>
            <div class="page-title clearfix">
                <h1><?php echo app_lang('contracts'); ?></h1>
            </div>
        <?php } else { ?>
            <div class="tab-title clearfix">
                <h4><?php echo app_lang('contracts'); ?></h4>
            </div>
        <?php } ?>

        <div class="table-responsive">
            <table id="contract-table" class="display" width="100%">
            </table>
        </div>
    </div>
    <?php if (isset($page_type) && $page_type === "full") { ?>
    </div>
<?php } ?>

<script type="text/javascript">
    $(document).ready(function () {
        var currencySymbol = "<?php echo $client_info->currency_symbol; ?>";
        $("#contract-table").appTable({
            source: '<?php echo_uri("contracts/contract_list_data_of_client/" . $client_id) ?>',
            order: [[0, "desc"]],
            filterDropdown: [<?php echo $custom_field_filters; ?>],
            columns: [
                {title: '<?php echo app_lang("id") ?>', "class": "w50"},
                {title: "<?php echo app_lang("title") ?>", "class": "w25p"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("project") ?>", "class": "w25p"},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("contract_date") ?>", "iDataSort": 4},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("valid_until") ?>", "iDataSort": 6, "class": "w20p"},
                {title: "<?php echo app_lang("amount") ?>", "class": "text-right"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center"}
<?php echo $custom_field_headers; ?>,
                {visible: false}
            ],
            summation: [{column: 8, dataType: 'currency', currencySymbol: currencySymbol}]
        });
    });
</script>