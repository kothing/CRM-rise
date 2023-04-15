<div id="page-content" class="<?php echo isset($is_editor_preview) ? "bg-all-white" : "page-wrapper"; ?> clearfix">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));

    load_js(array(
        "assets/js/signature/signature_pad.min.js",
    ));
    ?>

    <div class="invoice-preview proposal-preview">
        <?php
        if (!isset($is_editor_preview)) {

            $action_buttons = "<div class='clearfix float-end'>";

            if ($show_close_preview) {
                echo "<div class='text-center'>" . anchor("proposals/view/" . $proposal_info->id, app_lang("close_preview"), array("class" => "btn btn-default round mb20 mr5")) . "</div>";
            }

            $action_buttons .= "<div class='float-start'>" . js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print_proposal'), array('id' => 'print-proposal-btn', "class" => "btn btn-default round mr10")) . "</div>";

            if ($login_user->user_type === "staff") {
                $action_buttons .= "<div class='float-start'>" . anchor(get_uri("offer/preview/" . $proposal_info->id . "/" . $proposal_info->public_key), "<i data-feather='external-link' class='icon-16'></i> " . app_lang('proposal') . " " . app_lang("url"), array("class" => "btn btn-default round mr5")) . "</div>";
            }

            $action_buttons .= "</div>";

            if ($proposal_info->status === "accepted" || $proposal_info->status === "declined" || $proposal_info->status === "rejected") {
                ?>
                <div class = "card  p15 no-border">
                    <div class="clearfix">
                        <div class="float-start mt5">
                            <?php if ($proposal_info->status === "accepted") { ?>
                                <i data-feather="check-circle" class="icon-16 text-success"></i> <?php echo app_lang("proposal_accepted"); ?>
                            <?php } else { ?>
                                <i data-feather="x-circle" class="icon-16 text-danger"></i> <?php echo app_lang("proposal_rejected"); ?>
                            <?php } ?>
                        </div>

                        <?php echo $action_buttons; ?>
                    </div>
                </div>
                <?php
            } else {
                if ($login_user->user_type === "staff" || ($login_user->user_type === "client" && $proposal_info->status == "new")) {
                    ?>

                    <div class = "card  p15 no-border">

                        <div class="clearfix">
                            <div class="mr15 strong float-start">
                                <?php
                                if ($login_user->user_type === "client" && get_setting("add_signature_option_on_accepting_proposal")) {
                                    echo modal_anchor(get_uri("offer/accept_proposal_modal_form/$proposal_info->id"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('accept_proposal'), array("class" => "btn btn-success mr15", "title" => app_lang('accept_proposal')));
                                } else {
                                    echo ajax_anchor(get_uri("proposals/update_proposal_status/$proposal_info->id/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("class" => "btn btn-success mr15", "title" => app_lang('mark_as_accepted'), "data-reload-on-success" => "1"));
                                }
                                ?>

                                <?php echo ajax_anchor(get_uri("proposals/update_proposal_status/$proposal_info->id/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("class" => "btn btn-danger mr15", "title" => app_lang('mark_as_rejected'), "data-reload-on-success" => "1")); ?>
                            </div>

                            <?php echo $action_buttons; ?>
                        </div>
                    </div>

                    <?php
                }
            }
        }
        ?>

        <div id="proposal-preview" class="invoice-preview-container proposal-preview-container bg-white mt15">
            <?php
            echo $proposal_preview;
            ?>
        </div>

    </div>
</div>

<?php echo view("proposals/print_proposal_helper_js"); ?>