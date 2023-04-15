<div class="table-responsive">
    <table id="expenses-summary-table" class="display" cellspacing="0" width="100%">      
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#expenses-summary-table").appTable({
            source: '<?php echo_uri("expenses/summary_list_data") ?>',
            order: [[0, "asc"]],
            rangeDatepicker: [{
                    startDate: {name: "start_date", value: ""},
                    endDate: {name: "end_date", value: ""},
                    showClearButton: true
                }],
            columns: [
                {title: '<?php echo app_lang("category") ?>'},
                {title: '<?php echo app_lang("amount") ?>', "class": "text-right"},
                {title: '<?php echo app_lang("tax") ?>', "class": "text-right"},
                {title: '<?php echo app_lang("second_tax") ?>', "class": "text-right"},
                {title: '<?php echo app_lang("total") ?>', "class": "text-right"}
            ],
            printColumns: [0, 1, 2, 3, 4],
            xlsColumns: [0, 1, 2, 3, 4],
            summation: [{column: 1, dataType: 'currency'}, {column: 2, dataType: 'currency'}, {column: 3, dataType: 'currency'}, {column: 4, dataType: 'currency'}]
        });
    });
</script>