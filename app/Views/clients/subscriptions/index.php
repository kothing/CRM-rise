<?php if (isset($page_type) && $page_type === "full") { ?>
    <div id="page-content" class="page-wrapper clearfix">
    <?php } ?>

    <div class="card rounded-bottom">
        <?php if (isset($page_type) && $page_type === "full") { ?>
            <div class="page-title clearfix">
                <h1><?php echo app_lang('subscriptions'); ?></h1>
            </div>
        <?php } else { ?>
            <div class="tab-title clearfix">
                <h4><?php echo app_lang('subscriptions'); ?></h4>
                <div class="title-button-group">
                    <?php
                    if ($can_edit_subscriptions) {
                        echo modal_anchor(get_uri("subscriptions/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_subscription'), array("class" => "btn btn-default mb0", "data-post-client_id" => $client_id, "title" => app_lang('add_subscription')));
                    }
                    ?>
                </div>
            </div>
        <?php } ?>

        <div class="table-responsive">
            <table id="subscription-table" class="display" width="100%">
            </table>
        </div>
    </div>
    <?php if (isset($page_type) && $page_type === "full") { ?>
    </div>
<?php } ?>
<script type="text/javascript">
    $(document).ready(function () {
        var currencySymbol = "<?php echo $client_info->currency_symbol; ?>";
        $("#subscription-table").appTable({
            source: '<?php echo_uri("subscriptions/subscription_list_data_of_client/" . $client_id) ?>',
            order: [[0, "desc"]],
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("subscription_id") ?>", "class": "w10p"},
                {title: "<?php echo app_lang("title") ?> ", "class": "w15p"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("next_billing_date") ?>", "iDataSort": 4, "class": "w10p"},
                {title: "<?php echo app_lang("repeat_every") ?>", "class": "w10p text-center"},
                {title: "<?php echo app_lang("cycles") ?>", "class": "w10p text-center"},
                {title: "<?php echo app_lang("status") ?>", "class": "w10p text-center"},
                {title: "<?php echo app_lang("amount") ?>", "class": "w10p text-right"},
                {visible: false, searchable: false}
            ],
            printColumns: [1, 2, 6, 7, 8, 9, 10],
            xlsColumns: [1, 2, 6, 7, 8, 9, 10],
            summation: [{column: 10, dataType: 'currency', currencySymbol: currencySymbol}]
        });
    });
</script>