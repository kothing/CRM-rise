<div id="page-content" class="clearfix page-wrapper">
    <div class="card clearfix">

        <ul id="client-estimate-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
            <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#esimates-tab"><?php echo app_lang("estimates"); ?></a></li>
            <?php if (isset($can_request_estimate) && $can_request_estimate) { ?>
                <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("estimate_requests/estimate_requests_for_client/" . $client_id); ?>" data-bs-target="#esimate-requests-tab"><?php echo app_lang('estimate_requests'); ?></a></li>
                <div class="tab-title clearfix no-border">

                    <div class="title-button-group">
                        <?php echo modal_anchor(get_uri("estimate_requests/request_an_estimate_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('request_an_estimate'), array("class" => "btn btn-default", "title" => app_lang('request_an_estimate'))); ?>           
                    </div>

                </div>
            <?php } ?>
        </ul>

        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade" id="esimates-tab">
                <div class="table-responsive">
                    <table id="estimate-table" class="display" width="100%">
                    </table>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="esimate-requests-tab"></div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        var showCommentOption = false;
        if ("<?php echo get_setting("enable_comments_on_estimates") == "1" ?>") {
            showCommentOption = true;
        }

        var currencySymbol = "<?php echo $client_info->currency_symbol; ?>";
        $("#estimate-table").appTable({
            source: '<?php echo_uri("estimates/estimate_list_data_of_client/" . $client_id) ?>',
            order: [[0, "desc"]],
            filterDropdown: [<?php echo $custom_field_filters; ?>],
            columns: [
                {title: "<?php echo app_lang("estimate") ?>", "class": "w25p"},
                {visible: false, searchable: false},
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("estimate_date") ?>", "iDataSort": 2},
                {title: "<?php echo app_lang("amount") ?>", "class": "text-right"},
                {title: "<?php echo app_lang("status") ?>", "class": "text-center"},
                {visible: showCommentOption, title: '<i data-feather="message-circle" class="icon-16"></i>', "class": "text-center w50"}
<?php echo $custom_field_headers; ?>,
                {visible: false}
            ],
            summation: [{column: 4, dataType: 'currency', currencySymbol: currencySymbol}]
        });
    });
</script>