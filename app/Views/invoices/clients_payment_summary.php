<div class="table-responsive">
    <table id="clients-payment-summary-table" class="display" width="100%">
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#clients-payment-summary-table").appTable({
            source: '<?php echo_uri("invoice_payments/clients_payment_summary_list_data/") ?>',
            order: [[0, "asc"]],
            rangeDatepicker: [{
                    startDate: {name: "start_date", value: ""},
                    endDate: {name: "end_date", value: ""},
                    showClearButton: true
                }],
            columns: [
                {title: '<?php echo app_lang("client") ?> '},
                {title: '<?php echo app_lang("amount") ?>', "class": "text-right w15p"}
            ],
            summation: [{column: 1, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol, conversionRate: <?php echo $conversion_rate; ?>}],
            printColumns: [0, 1],
            xlsColumns: [0, 1]
        });
    });
</script>