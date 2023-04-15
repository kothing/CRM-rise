<div id="page-content" class="page-wrapper clearfix">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>

    <div class="invoice-preview">
        <?php if ($login_user->user_type === "client" && $subscription_total_summary->balance_due >= 1 && count($payment_methods) && !$client_info->disable_online_payment) { ?>
            <div class="page-title clearfix mt25">
                <h1><?php echo get_subscription_id($subscription_info->id) . ": " . $subscription_info->title; ?></h1>
                <?php if ($subscription_info->status == "pending" || $subscription_info->status == "active") { ?>
                    <div class="title-button-group">
                        <span class="dropdown inline-block mt10">
                            <button class="btn btn-secondary text-white dropdown-toggle caret mt0 mb0" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                                <i data-feather="tool" class="icon-16"></i> <?php echo app_lang('actions'); ?>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li role="presentation"><?php echo ajax_anchor(get_uri("subscriptions/update_subscription_status/" . $subscription_info->id . "/cancelled/$client_info->id"), "<i data-feather='x' class='icon-16'></i> " . app_lang('cancel_subscription'), array("class" => "dropdown-item", "title" => app_lang('cancel_subscription'), "data-reload-on-success" => "1")); ?> </li>
                            </ul>
                        </span>
                    </div>
                <?php } ?>
            </div>

            <div class="pt15 bg-white mb15">
                <?php echo view("subscriptions/subscription_recurring_info_bar"); ?>
            </div>
            <?php if ($subscription_info->status === "pending" && $subscription_info->type !== "app") { ?>
                <div class = "card d-block p15 no-border clearfix invoice-payment-button pb-0">
                    <div class = "inline-block strong float-start pt5 pr15">
                        <?php echo app_lang("start_subscription");
                        ?>:
                    </div>
                    <div class="mr15 strong float-start general-form" style="width: 145px;" >
                        <span class="pt5 inline-block">
                            <?php echo to_currency($subscription_total_summary->balance_due, $subscription_total_summary->currency . " "); ?>
                        </span>
                    </div>

                    <?php
                    foreach ($payment_methods as $payment_method) {

                        $method_type = get_array_value($payment_method, "type");

                        $pass_variables = array(
                            "payment_method" => $payment_method,
                            "balance_due" => $subscription_total_summary->balance_due,
                            "currency" => $subscription_total_summary->currency,
                            "subscription_info" => $subscription_info,
                            "subscription_id" => $subscription_id);

                        if ($subscription_total_summary->balance_due >= get_array_value($payment_method, "minimum_payment_amount")) {
                            if ($method_type == "stripe") {
                                echo view("subscriptions/_stripe_payment_form", $pass_variables);
                            }
                        }
                    }
                    ?>

                </div>
            <?php } ?>
        <?php } ?>

        <div id="invoice-preview" class="invoice-preview-container bg-white mt15">
            <div class="row">
                <div class="col-md-12 position-relative">
                    <div class="ribbon"><?php echo $subscription_status_label; ?></div>
                </div>
            </div>

            <?php
            echo $subscription_preview;
            ?>
        </div>

        <div class="card mt15">
            <div class="tab-title clearfix">
                <h4> <?php echo app_lang('invoices'); ?></h4>
            </div>
            <div class="table-responsive">
                <table id="subscription-invoices-table" class="display" cellspacing="0" width="100%">            
                </table>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#payment-amount").change(function () {
            var value = $(this).val();
            $(".payment-amount-field").each(function () {
                $(this).val(value);
            });
        });

        var currencySymbol = "<?php echo $client_info->currency_symbol; ?>";
        $("#subscription-invoices-table").appTable({
            source: '<?php echo_uri("invoices/invoice_list_data_of_subscription/$subscription_info->id/$client_info->id") ?>',
            order: [[0, "desc"]],
            filterDropdown: [{name: "status", class: "w150", options: <?php echo view("invoices/invoice_statuses_dropdown"); ?>}, <?php echo $custom_field_filters; ?>],
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("invoice_id") ?>", "class": "w10p all", "iDataSort": 0},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("bill_date") ?>", "class": "w10p", "iDataSort": 4},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("due_date") ?>", "class": "w10p", "iDataSort": 6},
                {title: "<?php echo app_lang("total_invoiced") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("payment_received") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("due") ?>", "class": "w10p text-right"},
                {title: "<?php echo app_lang("status") ?>", "class": "w10p text-center"}
<?php echo $custom_field_headers; ?>,
                {visible: false, searchable: false}
            ],
            printColumns: combineCustomFieldsColumns([1, 5, 7, 8, 9, 10, 11], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([1, 5, 7, 8, 9, 10, 11], '<?php echo $custom_field_headers; ?>'),
            summation: [
                {column: 8, dataType: 'currency', currencySymbol: currencySymbol},
                {column: 9, dataType: 'currency', currencySymbol: currencySymbol},
                {column: 10, dataType: 'currency', currencySymbol: currencySymbol}
            ]
        });
    });
</script>
