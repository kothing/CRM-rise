<?php
load_js(array(
    "assets/js/signature/signature_pad.min.js",
));
?>
<div id="page-content" class="clearfix">
    <div style="max-width: 1000px; margin: auto;">
        <div class="page-title clearfix mt15">
            <h1><?php echo get_contract_id($contract_info->id) . ": " . $contract_info->title; ?></h1>
            <div class="title-button-group">
                <span class="dropdown inline-block mt15">
                    <button class="btn btn-info text-white dropdown-toggle caret mt0 mb0" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                        <i data-feather="tool" class="icon-16"></i> <?php echo app_lang('actions'); ?>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li role="presentation"><?php echo anchor(get_uri("contracts/preview/" . $contract_info->id . "/1"), "<i data-feather='search' class='icon-16'></i> " . app_lang('contract_preview'), array("title" => app_lang('contract_preview'), "target" => "_blank", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print_contract'), array('title' => app_lang('print_contract'), 'id' => 'print-contract-btn', "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo anchor(get_uri("contract/preview/" . $contract_info->id . "/" . $contract_info->public_key), "<i data-feather='external-link' class='icon-16'></i> " . app_lang('contract') . " " . app_lang("url"), array("target" => "_blank", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation" class="dropdown-divider"></li>
                        <li role="presentation"><?php echo modal_anchor(get_uri("contracts/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_contract'), array("title" => app_lang('edit_contract'), "data-post-id" => $contract_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                        <?php if (!$contract_info->staff_signed_by && get_setting("add_signature_option_for_team_members")) { ?>
                            <li role="presentation"><?php echo modal_anchor(get_uri("contract/accept_contract_modal_form/$contract_info->id"), "<i data-feather='edit-3' class='icon-16'></i> " . app_lang('sign_contract'), array("title" => app_lang('sign_contract'), "class" => "dropdown-item")); ?></li>
                        <?php } ?>
                        <li role="presentation"><?php echo modal_anchor(get_uri("contracts/modal_form"), "<i data-feather='copy' class='icon-16'></i> " . app_lang('clone_contract'), array("data-post-is_clone" => true, "data-post-id" => $contract_info->id, "title" => app_lang('clone_contract'), "class" => "dropdown-item")); ?></li>

                        <?php if ($contract_status == "draft" || $contract_status == "sent") { ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("contracts/update_contract_status/" . $contract_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("contracts/update_contract_status/" . $contract_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <?php if ($contract_status == "draft") { ?>
                                <li role="presentation"><?php echo ajax_anchor(get_uri("contracts/update_contract_status/" . $contract_info->id . "/sent"), "<i data-feather='send' class='icon-16'></i> " . app_lang('mark_as_sent'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                        <?php } else if ($contract_status == "accepted") { ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("contracts/update_contract_status/" . $contract_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_rejected'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                        <?php } else if ($contract_status == "declined") { ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("contracts/update_contract_status/" . $contract_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                        <?php } ?>

                        <?php
                        if ($contract_status == "draft" || $contract_status == "sent") {
                            if ($client_info->is_lead) {
                                ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("contracts/send_contract_modal_form/" . $contract_info->id), "<i data-feather='send' class='icon-16'></i> " . app_lang('send_to_lead'), array("title" => app_lang('send_to_lead'), "data-post-id" => $contract_info->id, "data-post-is_lead" => true, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                            <?php } else { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("contracts/send_contract_modal_form/" . $contract_info->id), "<i data-feather='send' class='icon-16'></i> " . app_lang('send_to_client'), array("title" => app_lang('send_to_client'), "data-post-id" => $contract_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                                <?php
                            }
                        }
                        ?>

                        <?php if ($contract_status == "accepted") { ?>
                            <li role="presentation" class="dropdown-divider"></li>
                            <?php if ($show_estimate_option) { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form/"), "<i data-feather='file' class='icon-16'></i> " . app_lang('create_estimate'), array("title" => app_lang("create_estimate"), "data-post-contract_id" => $contract_info->id, "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                            <?php if ($show_invoice_option) { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("invoices/modal_form/"), "<i data-feather='file-text' class='icon-16'></i> " . app_lang('create_invoice'), array("title" => app_lang("create_invoice"), "data-post-contract_id" => $contract_info->id, "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </span>
            </div>
        </div>
        <div id="contract-status-bar">
            <?php echo view("contracts/contract_status_bar"); ?>
        </div>
        <div class="mt15">
            <div class="card no-border clearfix ">
                <ul data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#contract-items"><?php echo app_lang("contract") . " " . app_lang("items"); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("contracts/editor/" . $contract_info->id); ?>" data-bs-target="#contract-editor"><?php echo app_lang("contract_editor"); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("contracts/preview/" . $contract_info->id . "/0/1"); ?>" data-bs-target="#contract-preview" data-reload="true"><?php echo app_lang("preview"); ?></a></li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="contract-items">

                        <div class="p15 b-t mb15">
                            <div class="clearfix p20">
                                <!-- small font size is required to generate the pdf, overwrite that for screen -->
                                <style type="text/css"> .invoice-meta {
                                        font-size: 100% !important;
                                    }</style>

                                <?php
                                $color = get_setting("contract_color");
                                if (!$color) {
                                    $color = get_setting("invoice_color");
                                }
                                $style = get_setting("invoice_style");
                                ?>
                                <?php
                                $data = array(
                                    "client_info" => $client_info,
                                    "color" => $color ? $color : "#2AA384",
                                    "contract_info" => $contract_info
                                );
                                if ($style === "style_2") {
                                    echo view('contracts/contract_parts/header_style_2.php', $data);
                                } else {
                                    echo view('contracts/contract_parts/header_style_1.php', $data);
                                }
                                ?>

                            </div>

                            <div class="table-responsive mt15 pl15 pr15">
                                <table id="contract-item-table" class="display" width="100%">            
                                </table>
                            </div>

                            <div class="clearfix">
                                <div class="col-sm-8">

                                </div>
                                <div class="float-start ml15 mt20 mb20">
                                    <?php echo modal_anchor(get_uri("contracts/item_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_item'), array("class" => "btn btn-info text-white", "title" => app_lang('add_item'), "data-post-contract_id" => $contract_info->id)); ?>
                                </div>
                                <div class="float-end pr15" id="contract-total-section">
                                    <?php echo view("contracts/contract_total_section"); ?>
                                </div>
                            </div>

                            <?php
                            $files = @unserialize($contract_info->files);
                            if ($files && is_array($files) && count($files)) {
                                ?>
                                <div class="clearfix">
                                    <div class="col-md-12 mt20 pl15 pr15">
                                        <p class="b-t"></p>
                                        <div class="mb5 strong"><?php echo app_lang("files"); ?></div>
                                        <?php
                                        foreach ($files as $key => $value) {
                                            $file_name = get_array_value($value, "file_name");
                                            echo "<div>";
                                            echo js_anchor(remove_file_prefix($file_name), array("data-toggle" => "app-modal", "data-sidebar" => "0", "data-url" => get_uri("contract/file_preview/" . $contract_info->id . "/" . $key . "/" . $contract_info->public_key)));
                                            echo "</div>";
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php } ?>

                            <p class="b-t b-info pt10 m15"><?php echo nl2br($contract_info->note ? process_images_from_content($contract_info->note) : ""); ?></p>

                        </div>

                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="contract-editor"></div>
                    <div role="tabpanel" class="tab-pane fade" id="contract-preview"></div>
                </div>
            </div>
        </div>

        <?php
        $signer_info = @unserialize($contract_info->meta_data);
        if (!($signer_info && is_array($signer_info))) {
            $signer_info = array();
        }
        ?>
        <?php if ($contract_status === "accepted" && ($signer_info || $contract_info->accepted_by)) { ?>
            <div class="card mt15">
                <div class="page-title clearfix ">
                    <h1><?php echo app_lang("signer_info") . " (" . app_lang("client") . ")"; ?></h1>
                </div>
                <div class="p15">
                    <div><strong><?php echo app_lang("name"); ?>: </strong><?php echo $contract_info->accepted_by ? get_client_contact_profile_link($contract_info->accepted_by, $contract_info->signer_name) : get_array_value($signer_info, "name"); ?></div>
                    <div><strong><?php echo app_lang("email"); ?>: </strong><?php echo $contract_info->signer_email ? $contract_info->signer_email : get_array_value($signer_info, "email"); ?></div>
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
        <?php if ($contract_info->staff_signed_by) { ?>
            <div class="card mt15">
                <div class="page-title clearfix ">
                    <h1><?php echo app_lang("signer_info") . " (" . app_lang("team_member") . ")"; ?></h1>
                </div>
                <div class="p15">
                    <div><strong><?php echo app_lang("name"); ?>: </strong><?php echo get_team_member_profile_link($contract_info->staff_signed_by, $contract_info->staff_signer_name); ?></div>
                    <?php if (get_array_value($signer_info, "staff_signed_date")) { ?>
                        <div><strong><?php echo app_lang("signed_date"); ?>: </strong><?php echo format_to_relative_time(get_array_value($signer_info, "staff_signed_date")); ?></div>
                    <?php } ?>

                    <?php
                    if (get_array_value($signer_info, "staff_signature")) {
                        $signature_file = @unserialize(get_array_value($signer_info, "staff_signature"));
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
        $("#contract-item-table").appTable({
            source: '<?php echo_uri("contracts/item_list_data/" . $contract_info->id . "/") ?>',
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
                $("#contract-item-table").find("tbody").attr("id", "contract-item-table-sortable");
                var $selector = $("#contract-item-table-sortable");

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
                            url: '<?php echo_uri("contracts/update_item_sort_values") ?>',
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
                $("#contract-total-section").html(result.contract_total_view);
                if (typeof updateContractStatusBar == 'function') {
                    updateContractStatusBar(result.contract_id);
                }
            },
            onUndoSuccess: function (result) {
                $("#contract-total-section").html(result.contract_total_view);
                if (typeof updateContractStatusBar == 'function') {
                    updateContractStatusBar(result.contract_id);
                }
            }
        });

        $("body").on("click", "#contract-save-and-show-btn", function () {
            $(this).trigger("submit");

            setTimeout(function () {
                $("[data-bs-target='#contract-preview']").trigger("click");
            }, 400);
        });
    });

    updateContractStatusBar = function (contractId) {
        $.ajax({
            url: "<?php echo get_uri("contracts/get_contract_status_bar"); ?>/" + contractId,
            success: function (result) {
                if (result) {
                    $("#contract-status-bar").html(result);
                }
            }
        });
    };

</script>

<?php echo view("contracts/print_contract_helper_js"); ?>

