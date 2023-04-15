<div class="card clearfix">
    <div class="table-responsive">
        <table id="sub-invoice-table" class="display" cellspacing="0" width="100%">   
        </table>
    </div>

</div>

<script type="text/javascript">

    $(document).ready(function () {

        $("#sub-invoice-table").appTable({
            source: '<?php echo_uri("invoices/sub_invoices_list_data/" . $recurring_invoice_id) ?>',
            order: [[0, "desc"]],
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("invoice_id") ?>", "class": "w10p", "iDataSort": 0},
                {visible: false},
                {visible: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("bill_date") ?>", "class": "w10p", "iDataSort": 4},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("due_date") ?>", "class": "w10p", "iDataSort": 6},
                {title: "<?php echo app_lang("total_invoiced") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("payment_received") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("due") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("status") ?>", "class": "w10p text-center"}
            ],
            summation: [
                {column: 8, dataType: 'currency', currencySymbol: 'none'},
                {column: 9, dataType: 'currency', currencySymbol: 'none'},
                {column: 10, dataType: 'currency', currencySymbol: 'none'}
            ]
        });

    });
</script>