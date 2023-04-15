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

        <div id="contract-preview-scrollbar">

            <div id="page-content" class="page-wrapper clearfix">
                <?php
                load_css(array(
                    "assets/css/invoice.css",
                ));

                load_js(array(
                    "assets/js/signature/signature_pad.min.js",
                ));

                $print_button = "<div class='float-end'>" . js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print'), array('id' => 'print-contract-btn', "class" => "btn btn-default round mr10 mt4")) . "</div>";
                ?>

                <div class="invoice-preview contract-preview">
                    <div class = "card  p15 no-border">
                        <div class="clearfix">
                            <?php if ($contract_info->status === "accepted" || $contract_info->status === "declined" || $contract_info->status === "rejected") { ?>
                                <img class="dashboard-image float-start" src="<?php echo get_logo_url(); ?>" />
                                <?php echo $print_button; ?>
                                <div class="float-end mt10 mr15">
                                    <?php if ($contract_info->status === "accepted") { ?>
                                        <i data-feather="check-circle" class="icon-16 text-success"></i> <?php echo app_lang("contract_accepted"); ?>
                                    <?php } else { ?>
                                        <i data-feather="x-circle" class="icon-16 text-danger"></i> <?php echo app_lang("contract_rejected"); ?>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <img class="dashboard-image float-start" src="<?php echo get_logo_url(); ?>" />
                                <div class="strong float-end mt4">
                                    <?php echo ajax_anchor(get_uri("contract/update_contract_status/$contract_info->id/$contract_info->public_key/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('reject'), array("class" => "btn btn-danger mr10", "title" => app_lang('reject_contract'), "data-reload-on-success" => "1")); ?>
                                    <?php echo modal_anchor(get_uri("contract/accept_contract_modal_form/$contract_info->id/$contract_info->public_key"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('accept'), array("class" => "btn btn-success mr5", "title" => app_lang('accept_contract'))); ?>
                                </div>
                                <?php echo $print_button; ?>
                            <?php } ?>
                        </div>
                    </div>

                    <div id="contract-preview" class="invoice-preview-container contract-preview-container bg-white">
                        <?php
                        echo $contract_preview;
                        ?>

                        <?php
                        if ($contract_info->files) {
                            $files = unserialize($contract_info->files);
                            if (count($files)) {
                                foreach ($files as $key => $value) {
                                    $file_name = get_array_value($value, "file_name");
                                    $link = get_file_icon(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)));
                                    echo js_anchor("<i data-feather='$link'></i>", array('title' => "", "data-toggle" => "app-modal", "data-sidebar" => "0", "class" => "float-start mr10 mt-4", "title" => remove_file_prefix($file_name), "data-url" => get_uri("contract/file_preview/" . $contract_info->id . "/" . $key . "/" . $contract_info->public_key)));
                                }
                            }
                        }
                        ?>
                    </div>

                </div>
            </div>

            <?php echo view("contracts/print_contract_helper_js"); ?>
            <?php echo view('modal/index'); ?>

        </div>

        <script>
            $(document).ready(function () {
                initScrollbar('#contract-preview-scrollbar', {
                    setHeight: $(window).height()
                });

                $("#custom-theme-color").remove();
            });
        </script>
    </body>
</html>










