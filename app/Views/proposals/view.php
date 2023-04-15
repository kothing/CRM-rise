<div id="page-content" class="clearfix">
    <div style="max-width: 1000px; margin: auto;">
        <div class="page-title clearfix mt15">
            <h1><?php echo get_proposal_id($proposal_info->id); ?></h1>
            <div class="title-button-group">
                <span class="dropdown inline-block mt15">
                    <button class="btn btn-info text-white dropdown-toggle caret mt0 mb0" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                        <i data-feather="tool" class="icon-16"></i> <?php echo app_lang('actions'); ?>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li role="presentation"><?php echo anchor(get_uri("proposals/preview/" . $proposal_info->id . "/1"), "<i data-feather='search' class='icon-16'></i> " . app_lang('proposal_preview'), array("title" => app_lang('proposal_preview'), "target" => "_blank", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print_proposal'), array('title' => app_lang('print_proposal'), 'id' => 'print-proposal-btn', "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo anchor(get_uri("offer/preview/" . $proposal_info->id . "/" . $proposal_info->public_key), "<i data-feather='external-link' class='icon-16'></i> " . app_lang('proposal') . " " . app_lang("url"), array("target" => "_blank", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation" class="dropdown-divider"></li>
                        <li role="presentation"><?php echo modal_anchor(get_uri("proposals/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_proposal'), array("title" => app_lang('edit_proposal'), "data-post-id" => $proposal_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo modal_anchor(get_uri("proposals/modal_form"), "<i data-feather='copy' class='icon-16'></i> " . app_lang('clone_proposal'), array("data-post-is_clone" => true, "data-post-id" => $proposal_info->id, "title" => app_lang('clone_proposal'), "class" => "dropdown-item")); ?></li>

                        <?php if ($proposal_status == "draft" || $proposal_status == "sent") { ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("proposals/update_proposal_status/" . $proposal_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("proposals/update_proposal_status/" . $proposal_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <?php if ($proposal_status == "draft") { ?>
                                <li role="presentation"><?php echo ajax_anchor(get_uri("proposals/update_proposal_status/" . $proposal_info->id . "/sent"), "<i data-feather='send' class='icon-16'></i> " . app_lang('mark_as_sent'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                        <?php } else if ($proposal_status == "accepted") { ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("proposals/update_proposal_status/" . $proposal_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                        <?php } else if ($proposal_status == "declined") { ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("proposals/update_proposal_status/" . $proposal_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                        <?php } ?>

                        <?php
                        if ($proposal_status == "draft" || $proposal_status == "sent") {
                            if ($client_info->is_lead) {
                                ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("proposals/send_proposal_modal_form/" . $proposal_info->id), "<i data-feather='send' class='icon-16'></i> " . app_lang('send_to_lead'), array("title" => app_lang('send_to_lead'), "data-post-id" => $proposal_info->id, "data-post-is_lead" => true, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                            <?php } else { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("proposals/send_proposal_modal_form/" . $proposal_info->id), "<i data-feather='send' class='icon-16'></i> " . app_lang('send_to_client'), array("title" => app_lang('send_to_client'), "data-post-id" => $proposal_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                                <?php
                            }
                        }
                        ?>

                        <?php if ($proposal_status == "accepted") { ?>
                            <li role="presentation" class="dropdown-divider"></li>
                            <?php if ($show_estimate_option) { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form/"), "<i data-feather='file' class='icon-16'></i> " . app_lang('create_estimate'), array("title" => app_lang("create_estimate"), "data-post-proposal_id" => $proposal_info->id, "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                            <?php if ($show_invoice_option) { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("invoices/modal_form/"), "<i data-feather='file-text' class='icon-16'></i> " . app_lang('create_invoice'), array("title" => app_lang("create_invoice"), "data-post-proposal_id" => $proposal_info->id, "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </span>
            </div>
        </div>
        <div id="proposal-status-bar">
            <?php echo view("proposals/proposal_status_bar"); ?>
        </div>
        <div class="mt15">
            <div class="card no-border clearfix ">
                <ul data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#proposal-items"><?php echo app_lang("proposal") . " " . app_lang("items"); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("proposals/editor/" . $proposal_info->id); ?>" data-bs-target="#proposal-editor"><?php echo app_lang("proposal_editor"); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("proposals/preview/" . $proposal_info->id . "/0/1"); ?>" data-bs-target="#proposal-preview" data-reload="true"><?php echo app_lang("preview"); ?></a></li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="proposal-items">

                        <div class="p15 b-t mb15">
                            <div class="clearfix p20">
                                <!-- small font size is required to generate the pdf, overwrite that for screen -->
                                <style type="text/css"> .invoice-meta {
                                    font-size: 100% !important;
                                }</style>

                                <?php
                                $color = get_setting("proposal_color");
                                if (!$color) {
                                    $color = get_setting("invoice_color");
                                }
                                $style = get_setting("invoice_style");
                                ?>
                                <?php
                                $data = array(
                                    "client_info" => $client_info,
                                    "color" => $color ? $color : "#2AA384",
                                    "proposal_info" => $proposal_info
                                );
                                ?>

                                <div class="row">
                                    <div class="col-md-5 mb15">
                                        <?php echo view('proposals/proposal_parts/proposal_from', $data); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <?php echo view('proposals/proposal_parts/proposal_to', $data); ?>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <?php echo view('proposals/proposal_parts/proposal_info', $data); ?>
                                    </div>
                                </div>

                            </div>

                            <div class="table-responsive mt15 pl15 pr15">
                                <table id="proposal-item-table" class="display" width="100%">            
                                </table>
                            </div>

                            <div class="clearfix">
                                <div class="col-sm-8">

                                </div>
                                <div class="float-start ml15 mt20 mb20">
                                    <?php echo modal_anchor(get_uri("proposals/item_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_item'), array("class" => "btn btn-info text-white", "title" => app_lang('add_item'), "data-post-proposal_id" => $proposal_info->id)); ?>
                                </div>
                                <div class="float-end pr15" id="proposal-total-section">
                                    <?php echo view("proposals/proposal_total_section"); ?>
                                </div>
                            </div>

                            <p class="b-t b-info pt10 m15"><?php echo nl2br($proposal_info->note ? process_images_from_content($proposal_info->note) : ""); ?></p>

                        </div>

                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="proposal-editor"></div>
                    <div role="tabpanel" class="tab-pane fade" id="proposal-preview"></div>
                </div>
            </div>
        </div>

        <?php
        $signer_info = @unserialize($proposal_info->meta_data);
        if (!($signer_info && is_array($signer_info))) {
            $signer_info = array();
        }
        ?>
        <?php if ($proposal_status === "accepted" && ($signer_info || $proposal_info->accepted_by)) { ?>
            <div class="card mt15">
                <div class="page-title clearfix ">
                    <h1><?php echo app_lang("signer_info"); ?></h1>
                </div>
                <div class="p15">
                    <div><strong><?php echo app_lang("name"); ?>: </strong><?php echo $proposal_info->accepted_by ? get_client_contact_profile_link($proposal_info->accepted_by, $proposal_info->signer_name) : get_array_value($signer_info, "name"); ?></div>
                    <div><strong><?php echo app_lang("email"); ?>: </strong><?php echo $proposal_info->signer_email ? $proposal_info->signer_email : get_array_value($signer_info, "email"); ?></div>
                    <?php if (get_array_value($signer_info, "signed_date")) { ?>
                        <div><strong><?php echo app_lang("signed_date"); ?>: </strong><?php echo format_to_relative_time(get_array_value($signer_info, "signed_date")); ?></div>
                    <?php } ?>

                    <?php
                    if (get_array_value($signer_info, "signature")) {
                        $signature_file = @unserialize(get_array_value($signer_info, "signature"));
                        $signature_file_name = get_array_value($signature_file, "file_name");
                        $signature_file = get_source_url_of_file($signature_file, get_setting("timeline_file_path"), "thumbnail");
                        ?>
                        <div><strong><?php echo app_lang("signature"); ?>: </strong><br /><img class="signature-image" src="<?php echo $signature_file; ?>" alt="<?php echo $signature_file_name; ?>" /></div>
                        <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    //RELOAD_VIEW_AFTER_UPDATE = true;
    $(document).ready(function () {
        $("#proposal-item-table").appTable({
            source: '<?php echo_uri("proposals/item_list_data/" . $proposal_info->id . "/") ?>',
            order: [[0, "asc"]],
            hideTools: true,
            displayLength: 100,
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo app_lang("item") ?> ", "bSortable": false},
                {title: "<?php echo app_lang("quantity") ?>", "class": "text-right w15p", "bSortable": false},
                {title: "<?php echo app_lang("rate") ?>", "class": "text-right w15p", "bSortable": false},
                {title: "<?php echo app_lang("total") ?>", "class": "text-right w15p", "bSortable": false},
                {title: "<i data-feather='menu' class='icon-16'></i>", "class": "text-center option w100", "bSortable": false}
            ],

            onInitComplete: function () {
                //apply sortable
                $("#proposal-item-table").find("tbody").attr("id", "proposal-item-table-sortable");
                var $selector = $("#proposal-item-table-sortable");

                Sortable.create($selector[0], {
                    animation: 150,
                    chosenClass: "sortable-chosen",
                    ghostClass: "sortable-ghost",
                    onUpdate: function (e) {
                        appLoader.show();
                        //prepare sort indexes 
                        var data = "";
                        $.each($selector.find(".item-row"), function (index, ele) {
                            if (data) {
                                data += ",";
                            }

                            data += $(ele).attr("data-id") + "-" + index;
                        });

                        //update sort indexes
                        $.ajax({
                            url: '<?php echo_uri("proposals/update_item_sort_values") ?>',
                            type: "POST",
                            data: {sort_values: data},
                            success: function () {
                                appLoader.hide();
                            }
                        });
                    }
                });

            },

            onDeleteSuccess: function (result) {
                $("#proposal-total-section").html(result.proposal_total_view);
                if (typeof updateInvoiceStatusBar == 'function') {
                    updateInvoiceStatusBar(result.proposal_id);
                }
            },
            onUndoSuccess: function (result) {
                $("#proposal-total-section").html(result.proposal_total_view);
                if (typeof updateInvoiceStatusBar == 'function') {
                    updateInvoiceStatusBar(result.proposal_id);
                }
            }
        });

        $("body").on("click", "#proposal-save-and-show-btn", function () {
            $(this).trigger("submit");

            setTimeout(function () {
                $("[data-bs-target='#proposal-preview']").trigger("click");
            }, 400);
        });
    });

    updateInvoiceStatusBar = function (proposalId) {
        $.ajax({
            url: "<?php echo get_uri("proposals/get_proposal_status_bar"); ?>/" + proposalId,
            success: function (result) {
                if (result) {
                    $("#proposal-status-bar").html(result);
                }
            }
        });
    };

</script>

<?php echo view("proposals/print_proposal_helper_js"); ?>

