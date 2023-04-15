<div id="page-content" class="page-wrapper clearfix grid-button">
    <div class="card clearfix">
        <ul id="invoices-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li class="title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("invoices"); ?></h4></li>
            <li><a id="monthly-expenses-button"  role="presentation" data-bs-toggle="tab"  href="javascript:;" data-bs-target="#monthly-invoices"><?php echo app_lang("monthly"); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("invoices/yearly/"); ?>" data-bs-target="#yearly-invoices"><?php echo app_lang('yearly'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("invoices/custom/"); ?>" data-bs-target="#custom-invoices"><?php echo app_lang('custom'); ?></a></li>
            <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("invoices/recurring/"); ?>" data-bs-target="#recurring-invoices"><?php echo app_lang('recurring'); ?></a></li>
            <div class="tab-title clearfix no-border invoices-view">
                <div class="title-button-group">
                    <?php if ($can_edit_invoices) { ?>
                        <?php echo modal_anchor(get_uri("labels/modal_form"), "<i data-feather='tag' class='icon-16'></i> " . app_lang('manage_labels'), array("class" => "btn btn-default mb0", "title" => app_lang('manage_labels'), "data-post-type" => "invoice")); ?>
                        <?php echo modal_anchor(get_uri("invoice_payments/payment_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_payment'), array("class" => "btn btn-default mb0", "title" => app_lang('add_payment'))); ?>
                        <?php echo modal_anchor(get_uri("invoices/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_invoice'), array("class" => "btn btn-default mb0", "title" => app_lang('add_invoice'))); ?>
                    <?php } ?>
                </div>
            </div>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="monthly-invoices">
                <div class="table-responsive">
                    <table id="monthly-invoice-table" class="display" cellspacing="0" width="100%">   
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="yearly-invoices"></div>
            <div role="tabpanel" class="tab-pane fade" id="custom-invoices"></div>
            <div role="tabpanel" class="tab-pane fade" id="recurring-invoices"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    loadInvoicesTable = function (selector, dateRange) {
    var customDatePicker = "";
    if (dateRange === "custom") {
        if(window.selectedInvoiceFilter){
            customDatePicker = [{startDate: "", endDate: "", showClearButton: true}];
        }else{
            customDatePicker = [{startDate: {name: "start_date", value: moment().format("YYYY-MM-DD")}, endDate: {name: "end_date", value: moment().format("YYYY-MM-DD")}, showClearButton: true}];
        }
    
    dateRange = "";
    }

    var optionVisibility = false;
    if ("<?php echo $can_edit_invoices ?>") {
    optionVisibility = true;
    }
    window.selectedInvoiceFilter = window.location.hash.substring(1);
    var invoice_statuses_dropdown = <?php echo view("invoices/invoice_statuses_dropdown"); ?>;
    if (window.selectedInvoiceFilter){
    var filterIndex = invoice_statuses_dropdown.findIndex(x => x.id === window.selectedInvoiceFilter);
    if ([filterIndex] > - 1){
    //match found
    invoice_statuses_dropdown[filterIndex].isSelected = true;
    }
    }

    $(selector).appTable({
    source: '<?php echo_uri("invoices/list_data") ?>',
            dateRangeType: dateRange,
            order: [[0, "desc"]],
            filterDropdown: [
            {name: "status", class: "w150", options: invoice_statuses_dropdown}
<?php if ($currencies_dropdown) { ?>
                , {name: "currency", class: "w150", options: <?php echo $currencies_dropdown; ?>}
<?php } ?>
            , <?php echo $custom_field_filters; ?>
            ],
            rangeDatepicker: customDatePicker,
            columns: [
            {visible: false, searchable: false},
            {title: "<?php echo app_lang("invoice_id") ?>", "class": "w10p all", "iDataSort": 0},
                     
            {title: "<?php echo app_lang("client") ?>", "class": ""},
            {title: "<?php echo app_lang("project") ?>", "class": "w15p"},
            {visible: false, searchable: false},
            {title: "<?php echo app_lang("bill_date") ?>", "class": "w10p", "iDataSort": 4},
            {visible: false, searchable: false},
            {title: "<?php echo app_lang("due_date") ?>", "class": "w10p", "iDataSort": 6},
            {title: "<?php echo app_lang("total_invoiced") ?>", "class": "w10p text-right"},
            {title: "<?php echo app_lang("payment_received") ?>", "class": "w10p text-right"},
            {title: "<?php echo app_lang("due") ?>", "class": "w10p text-right"},
            {title: "<?php echo app_lang("status") ?>", "class": "w10p text-center"}
<?php echo $custom_field_headers; ?>,
            {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center dropdown-option w100", visible: optionVisibility}
            ],
            printColumns: combineCustomFieldsColumns([1, 2, 3, 4, 7, 8, 9, 10, 11], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 2, 3, 4, 7, 8, 9, 10, 11], '<?php echo $custom_field_headers; ?>'),
            summation: [
            {column: 8, dataType: 'currency', conversionRate: <?php echo $conversion_rate; ?>},
            {column: 9, dataType: 'currency', conversionRate: <?php echo $conversion_rate; ?>},
            {column: 10, dataType: 'currency', conversionRate: <?php echo $conversion_rate; ?>}
            ]
    });
    };
    $(document).ready(function () {
    loadInvoicesTable("#monthly-invoice-table", "monthly");
    
    setTimeout(function () {
            var tab = "<?php echo $tab; ?>";
            if (tab === "custom") {
                $("[data-bs-target='#custom-invoices']").trigger("click");
            }
        }, 210);
    });
</script>