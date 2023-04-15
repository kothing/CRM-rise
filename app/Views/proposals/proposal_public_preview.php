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

        <div id="proposal-preview-scrollbar">

            <div id="page-content" class="page-wrapper clearfix">
                <?php
                load_css(array(
                    "assets/css/invoice.css",
                ));

                load_js(array(
                    "assets/js/signature/signature_pad.min.js",
                ));

                $print_button = "<div class='float-end'>" . js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print'), array('id' => 'print-proposal-btn', "class" => "btn btn-default round mr10 mt4")) . "</div>";
                ?>

                <div class="invoice-preview proposal-preview">
                    <div class = "card  p15 no-border">
                        <div class="clearfix">
                            <?php if ($proposal_info->status === "accepted" || $proposal_info->status === "declined" || $proposal_info->status === "rejected") { ?>
                                <img class="dashboard-image float-start" src="<?php echo get_logo_url(); ?>" />
                                <?php echo $print_button; ?>
                                <div class="float-end mt10 mr15">
                                    <?php if ($proposal_info->status === "accepted") { ?>
                                        <i data-feather="check-circle" class="icon-16 text-success"></i> <?php echo app_lang("proposal_accepted"); ?>
                                    <?php } else { ?>
                                        <i data-feather="x-circle" class="icon-16 text-danger"></i> <?php echo app_lang("proposal_rejected"); ?>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <img class="dashboard-image float-start" src="<?php echo get_logo_url(); ?>" />
                                <div class="strong float-end mt4">
                                    <?php echo ajax_anchor(get_uri("offer/update_proposal_status/$proposal_info->id/$proposal_info->public_key/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('reject'), array("class" => "btn btn-danger mr10", "title" => app_lang('reject_proposal'), "data-reload-on-success" => "1")); ?>
                                    <?php echo modal_anchor(get_uri("offer/accept_proposal_modal_form/$proposal_info->id/$proposal_info->public_key"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('accept'), array("class" => "btn btn-success mr5", "title" => app_lang('accept_proposal'))); ?>
                                </div>
                                <?php echo $print_button; ?>
                            <?php } ?>
                        </div>
                    </div>

                    <div id="proposal-preview" class="invoice-preview-container proposal-preview-container bg-white">
                        <?php
                        echo $proposal_preview;
                        ?>
                    </div>

                </div>
            </div>

            <?php echo view("proposals/print_proposal_helper_js"); ?>
            <?php echo view('modal/index'); ?>

        </div>

        <script>
            $(document).ready(function () {
                initScrollbar('#proposal-preview-scrollbar', {
                    setHeight: $(window).height()
                });

                $("#custom-theme-color").remove();
            });
        </script>
    </body>
</html>










