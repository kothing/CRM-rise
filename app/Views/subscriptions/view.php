<div id="page-content" class="clearfix">
    <div style="max-width: 1000px; margin: auto;">
        <div class="page-title clearfix mt25">
            <h1><?php echo get_subscription_id($subscription_info->id) . ": " . $subscription_info->title; ?></h1>
            <div class="title-button-group">
                <span class="dropdown inline-block mt10">
                    <button class="btn btn-info text-white dropdown-toggle caret mt0 mb0" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                        <i data-feather="tool" class="icon-16"></i> <?php echo app_lang('actions'); ?>
                    </button>
                    <ul class="dropdown-menu" role="menu">



                        <?php if ($can_edit_subscriptions) { ?>
                            <?php if ($subscription_status !== "cancelled" && $subscription_status !== "active" && !$subscription_info->stripe_subscription_id && get_setting("enable_stripe_subscription")) { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("subscriptions/activate_as_stripe_subscription_modal_form/" . $subscription_info->id), "<i data-feather='credit-card' class='icon-16'></i> " . app_lang('activate_as_stripe_subscription'), array("title" => app_lang('activate_as_stripe_subscription'), "data-post-id" => $subscription_info->id, "class" => "dropdown-item")); ?> </li>
                            <?php } ?>

                            <?php if ($subscription_status == "draft" && $subscription_status !== "cancelled" && $subscription_info->type === "app") { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("subscriptions/activate_as_internal_subscription_modal_form/" . $subscription_info->id), "<i data-feather='check' class='icon-16'></i> " . app_lang('activate_as_internal_subscription'), array("title" => app_lang("activate_as_internal_subscription"), "data-post-id" => $subscription_info->id, "class" => "dropdown-item")); ?> </li>
                            <?php } else if ($subscription_status == "pending" || $subscription_status == "active") { ?>
                                <li role="presentation"><?php echo js_anchor("<i data-feather='x' class='icon-16'></i> " . app_lang('cancel_subscription'), array('title' => app_lang('cancel_subscription'), "data-action-url" => get_uri("subscriptions/update_subscription_status/" . $subscription_info->id . "/cancelled"), "data-action" => "delete-confirmation", "data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <?php } ?>

                            <?php if ($subscription_status !== "active" && $subscription_status !== "cancelled") { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("subscriptions/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_subscription'), array("title" => app_lang('edit_subscription'), "data-post-id" => $subscription_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                        <?php } ?>

                    </ul>
                </span>
            </div>
        </div>

        <div id="subscription-status-bar">
            <?php echo view("subscriptions/subscription_status_bar"); ?>
        </div>

        <?php echo view("subscriptions/subscription_recurring_info_bar"); ?>

        <div class="mt15">
            <div class="card p15 b-t">
                <div class="clearfix p20">
                    <!-- small font size is required to generate the pdf, overwrite that for screen -->
                    <style type="text/css">
                        .subscription-meta {
                            font-size: 100% !important;
                        }
                    </style>

                    <?php
                    $color = get_setting("invoice_color");
                    if (!$color) {
                        $color = "#2AA384";
                    }

                    $data = array(
                        "client_info" => $client_info,
                        "color" => $color,
                        "subscription_info" => $subscription_info
                    );

                    echo view('subscriptions/subscription_parts/header_style_1.php', $data);
                    ?>
                </div>

                <div class="table-responsive mt15 pl15 pr15">
                    <table id="subscription-item-table" class="display" width="100%">            
                    </table>
                </div>

                <div class="clearfix">
                    <?php if (!$has_item_in_this_subscription && $subscription_info->status != "active") { ?>
                        <div class="float-start mt20 ml15" id="subscription-add-item-btn">
                            <?php echo modal_anchor(get_uri("subscriptions/item_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_item'), array("class" => "btn btn-info text-white", "title" => app_lang('add_item'), "data-post-subscription_id" => $subscription_info->id)); ?>
                        </div>
                    <?php } ?>
                    <div class="float-end pr15" id="subscription-total-section">
                        <?php echo view("subscriptions/subscription_total_section", array("subscription_id" => $subscription_info->id, "can_edit_subscriptions" => $can_edit_subscriptions)); ?>
                    </div>
                </div>

                <?php
                $files = @unserialize($subscription_info->files);
                if ($files && is_array($files) && count($files)) {
                    ?>
                    <div class="clearfix">
                        <div class="col-md-12 mt20">
                            <p class="b-t"></p>
                            <div class="mb5 strong"><?php echo app_lang("files"); ?></div>
                            <?php
                            foreach ($files as $key => $value) {
                                $file_name = get_array_value($value, "file_name");
                                echo "<div>";
                                echo js_anchor(remove_file_prefix($file_name), array("data-toggle" => "app-modal", "data-sidebar" => "0", "data-url" => get_uri("subscriptions/file_preview/" . $subscription_info->id . "/" . $key)));
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>
                <?php } ?>

                <p class="b-t b-info pt10 m15"><?php echo nl2br($subscription_info->note); ?></p>

            </div>
        </div>

        <div class="card">
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
        var optionVisibility = false;
<?php if ($can_edit_subscriptions && $subscription_status !== "active") { ?>
            optionVisibility = true;
<?php } ?>

        $("#subscription-item-table").appTable({
            source: '<?php echo_uri("subscriptions/item_list_data/" . $subscription_info->id . "/") ?>',
            order: [[0, "asc"]],
            hideTools: true,
            displayLength: 100,
            columns: [
                {title: '<?php echo app_lang("item") ?> ', "bSortable": false},
                {title: '<?php echo app_lang("quantity") ?>', "class": "text-right w15p", "bSortable": false},
                {title: '<?php echo app_lang("rate") ?>', "class": "text-right w15p", "bSortable": false},
                {title: '<?php echo app_lang("total") ?>', "class": "text-right w15p", "bSortable": false},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100", "bSortable": false, visible: optionVisibility}
            ]
        });

        var currencySymbol = "<?php echo $client_info->currency_symbol; ?>";
        $("#subscription-invoices-table").appTable({
            source: '<?php echo_uri("invoices/invoice_list_data_of_subscription/" . $subscription_info->id) ?>',
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

        //modify the delete confirmation texts
        $("#confirmationModalTitle").html("<?php echo app_lang('cancel') . "?"; ?>");
        $("#confirmDeleteButton").html("<i data-feather='x' class='icon-16'></i> <?php echo app_lang("cancel"); ?>");
    });
</script>