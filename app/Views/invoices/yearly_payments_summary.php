<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card clearfix">
        <ul id="payment-summary-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white inner" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("payments_summary"); ?></h4></li>
            <li><a id="yearly-summary-button"  role="presentation" data-bs-toggle="tab"  href="javascript:;" data-bs-target="#yearly-payment-summary"><?php echo app_lang("yearly_summary"); ?></a></li>
            <?php if ($can_access_clients) { ?>
                <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("invoice_payments/clients_payment_summary/"); ?>" data-bs-target="#clients-payment-summary"><?php echo app_lang('clients_summary'); ?></a></li>
            <?php } ?>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="yearly-payment-summary">
                <div class="table-responsive">
                    <table id="yearly-payment-summary-table" class="display" width="100%">
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="clients-payment-summary"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#yearly-payment-summary-table").appTable({
            source: '<?php echo_uri("invoice_payments/yearly_payment_summary_list_data/") ?>',
            order: [[0, "asc"]],
            dateRangeType: "yearly",
            columns: [
                {title: '<?php echo app_lang("month") ?> '},
                {title: '<?php echo app_lang("amount") ?>', "class": "text-right w15p"}
            ],
            summation: [{column: 1, dataType: 'currency', currencySymbol: AppHelper.settings.currencySymbol, conversionRate: <?php echo $conversion_rate; ?>}],
            printColumns: [0, 1],
            xlsColumns: [0, 1]
        });
    });
</script>