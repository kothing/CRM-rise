<!DOCTYPE html>
<html lang="en">
    <head>
        <?php echo view('includes/head'); ?>
    </head>
    <body>

        <style>
            .card {
                transition: all 0s !important;
            }

            .mt4{
                margin-top: 4px;
            }
        </style>

        <div id="estimate-preview-scrollbar">

            <div id="page-content" class="page-wrapper clearfix">
                <?php
                load_css(array(
                    "assets/css/invoice.css",
                ));

                load_js(array(
                    "assets/js/signature/signature_pad.min.js",
                ));
                ?>

                <div class="invoice-preview estimate-preview">
                    <div class = "card  p15 no-border">
                        <div class="clearfix">
                            <?php if ($estimate_info->status === "accepted" || $estimate_info->status === "declined" || $estimate_info->status === "rejected") { ?>
                                <img class="dashboard-image float-start" src="<?php echo get_logo_url(); ?>" />
                                <div class="float-end mt10 mr15">
                                    <?php if ($estimate_info->status === "accepted") { ?>
                                        <i data-feather="check-circle" class="icon-16 text-success"></i> <?php echo app_lang("estimate_accepted"); ?>
                                    <?php } else { ?>
                                        <i data-feather="x-circle" class="icon-16 text-danger"></i> <?php echo app_lang("estimate_rejected"); ?>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <img class="dashboard-image float-start" src="<?php echo get_logo_url(); ?>" />
                                <div class="strong float-end mt4">
                                    <?php echo ajax_anchor(get_uri("estimate/update_estimate_status/$estimate_info->id/$estimate_info->public_key/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('reject'), array("class" => "btn btn-danger mr10", "title" => app_lang('reject_estimate'), "data-reload-on-success" => "1")); ?>
                                    <?php echo modal_anchor(get_uri("estimate/accept_estimate_modal_form/$estimate_info->id/$estimate_info->public_key"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('accept'), array("class" => "btn btn-success mr5", "title" => app_lang('accept_estimate'))); ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="invoice-preview-container estimate-preview-container bg-white">
                        <?php
                        echo $estimate_preview;
                        ?>
                    </div>

                </div>
            </div>

            <?php echo view('modal/index'); ?>

        </div>

        <script>
            $(document).ready(function () {
                initScrollbar('#estimate-preview-scrollbar', {
                    setHeight: $(window).height()
                });

                $("#custom-theme-color").remove();
            });
        </script>
    </body>
</html>










