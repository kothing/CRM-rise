<div class="table-responsive">
    <table id="recurring-invoice-table" class="display" cellspacing="0" width="100%">   
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {
    var optionVisibility = false;
            if ("<?php echo $can_edit_invoices ?>") {
    optionVisibility = true;
    }

    $("#recurring-invoice-table").appTable({
    source: '<?php echo_uri("invoices/recurring_list_data") ?>',
            order: [[0, "desc"]],
            rangeDatepicker: [{startDate: {name: "next_recurring_start_date"}, endDate: {name: "next_recurring_end_date"}, showClearButton: true}],
<?php if ($currencies_dropdown) { ?>
        filterDropdown: [
        {name: "currency", class: "w150", options: <?php echo $currencies_dropdown; ?>}
        ],
<?php } ?>
    columns: [
    {visible: false, searchable: false},
    {title: "<?php echo app_lang("invoice_id") ?>", "class": "w10p", "iDataSort": 0},
    {title: "<?php echo app_lang("client") ?>", "class": ""},
    {title: "<?php echo app_lang("project") ?>", "class": "w15p"},
    {visible: false, searchable: false},
    {title: "<?php echo app_lang("next_recurring_date") ?>", "iDataSort": 4, "class": "w10p"},
    {visible: false, searchable: false},
    {title: "<?php echo app_lang("repeat_every") ?>", "class": "w10p text-center", "iDataSort": 6},
    {title: "<?php echo app_lang("cycles") ?>", "class": "w10p text-center"},
    {title: "<?php echo app_lang("status") ?>", "class": "w10p text-center"},
    {title: "<?php echo app_lang("total_invoiced") ?>", "class": "w10p text-right"},
    {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center dropdown-option w100", visible: optionVisibility}
    ],
            printColumns: [1, 2, 3, 5, 7, 8, 9, 10],
            xlsColumns: [1, 2, 3, 5, 7, 8, 9, 10],
            summation: [{column: 10, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol}]
    });
    });
</script>