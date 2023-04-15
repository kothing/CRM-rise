<div id="page-content" class="clearfix">
    <div style="max-width: 1000px; margin: auto;">
        <div class="page-title clearfix mt25">
            <h1><?php echo get_estimate_id($estimate_info->id); ?></h1>
            <div class="title-button-group">
                <span class="dropdown inline-block mt15">
                    <button class="btn btn-info text-white dropdown-toggle caret mt0 mb0" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                        <i data-feather="tool" class="icon-16"></i> <?php echo app_lang('actions'); ?>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li role="presentation"><?php echo anchor(get_uri("estimates/download_pdf/" . $estimate_info->id), "<i data-feather='download' class='icon-16'></i> " . app_lang('download_pdf'), array("title" => app_lang('download_pdf'), "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo anchor(get_uri("estimates/download_pdf/" . $estimate_info->id . "/view"), "<i data-feather='file-text' class='icon-16'></i> " . app_lang('view_pdf'), array("title" => app_lang('view_pdf'), "target" => "_blank", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo anchor(get_uri("estimates/preview/" . $estimate_info->id . "/1"), "<i data-feather='search' class='icon-16'></i> " . app_lang('estimate_preview'), array("title" => app_lang('estimate_preview'), "target" => "_blank", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo anchor(get_uri("estimate/preview/" . $estimate_info->id . "/" . $estimate_info->public_key), "<i data-feather='external-link' class='icon-16'></i> " . app_lang('estimate') . " " . app_lang("url"), array("target" => "_blank", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo js_anchor("<i data-feather='printer' class='icon-16'></i> " . app_lang('print_estimate'), array('title' => app_lang('print_estimate'), 'id' => 'print-estimate-btn', "class" => "dropdown-item")); ?> </li>
                        <li role="presentation" class="dropdown-divider"></li>
                        <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='edit' class='icon-16'></i> " . app_lang('edit_estimate'), array("title" => app_lang('edit_estimate'), "data-post-id" => $estimate_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                        <li role="presentation"><?php echo modal_anchor(get_uri("estimates/modal_form"), "<i data-feather='copy' class='icon-16'></i> " . app_lang('clone_estimate'), array("data-post-is_clone" => true, "data-post-id" => $estimate_info->id, "title" => app_lang('clone_estimate'), "class" => "dropdown-item")); ?></li>

                        <?php
                        if ($estimate_status == "draft" || $estimate_status == "sent") {
                            ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_declined'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                        <?php } else if ($estimate_status == "accepted") {
                            ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/declined"), "<i data-feather='x-circle' class='icon-16'></i> " . app_lang('mark_as_declined'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <?php
                        } else if ($estimate_status == "declined") {
                            ?>
                            <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/update_estimate_status/" . $estimate_info->id . "/accepted"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_accepted'), array("data-reload-on-success" => "1", "class" => "dropdown-item")); ?> </li>
                            <?php
                        }
                        ?>

                        <?php
                        if ($client_info->is_lead) {
                            if ($estimate_status == "draft" || $estimate_status == "sent") {
                                ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("estimates/send_estimate_modal_form/" . $estimate_info->id), "<i data-feather='send' class='icon-16'></i> " . app_lang('send_to_lead'), array("title" => app_lang('send_to_lead'), "data-post-id" => $estimate_info->id, "data-post-is_lead" => true, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                                <?php
                            }
                        } else {
                            if ($estimate_status == "draft" || $estimate_status == "sent") {
                                ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("estimates/send_estimate_modal_form/" . $estimate_info->id), "<i data-feather='send' class='icon-16'></i> " . app_lang('send_to_client'), array("title" => app_lang('send_to_client'), "data-post-id" => $estimate_info->id, "role" => "menuitem", "tabindex" => "-1", "class" => "dropdown-item")); ?> </li>
                                <?php
                            }
                        }
                        ?>

                        <?php if ($estimate_status == "accepted") { ?>
                            <li role="presentation" class="dropdown-divider"></li>
                            <?php if ($can_create_projects && !$estimate_info->project_id) { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("projects/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('create_project'), array("data-post-estimate_id" => $estimate_info->id, "title" => app_lang('create_project'), "data-post-client_id" => $estimate_info->client_id, "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                            <?php if ($show_invoice_option) { ?>
                                <li role="presentation"><?php echo modal_anchor(get_uri("invoices/modal_form/"), "<i data-feather='refresh-cw' class='icon-16'></i> " . app_lang('create_invoice'), array("title" => app_lang("create_invoice"), "data-post-estimate_id" => $estimate_info->id, "class" => "dropdown-item")); ?> </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </span>
            </div>
        </div>
        <div id="estimate-status-bar">
            <?php echo view("estimates/estimate_status_bar"); ?>
        </div>
        <div class="mt15">
            <div class="card p15 b-t">
                <div class="clearfix p20">
                    <!-- small font size is required to generate the pdf, overwrite that for screen -->
                    <style type="text/css">
                        .invoice-meta {
                            font-size: 100% !important;
                        }
                    </style>

                    <?php
                    $color = get_setting("estimate_color");
                    if (!$color) {
                        $color = get_setting("invoice_color");
                    }
                    $style = get_setting("invoice_style");
                    ?>
                    <?php
                    $data = array(
                        "client_info" => $client_info,
                        "color" => $color ? $color : "#2AA384",
                        "estimate_info" => $estimate_info
                    );
                    
                    if ($style === "style_3") {
                        echo view('estimates/estimate_parts/header_style_3.php', $data);
                    } else if ($style === "style_2") {
                        echo view('estimates/estimate_parts/header_style_2.php', $data);
                    } else {
                        echo view('estimates/estimate_parts/header_style_1.php', $data);
                    }
                    ?>

                </div>

                <div class="table-responsive mt15 pl15 pr15">
                    <table id="estimate-item-table" class="display" width="100%">            
                    </table>
                </div>

                <div class="clearfix">
                    <div class="float-start mt20 ml15">
                        <?php echo modal_anchor(get_uri("estimates/item_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_item'), array("class" => "btn btn-info text-white", "title" => app_lang('add_item'), "data-post-estimate_id" => $estimate_info->id)); ?>
                    </div>
                    <div class="float-end pr15" id="estimate-total-section">
                        <?php echo view("estimates/estimate_total_section"); ?>
                    </div>
                </div>

                <p class="b-t b-info pt10 m15 pb10"><?php echo nl2br($estimate_info->note ? process_images_from_content($estimate_info->note) : ""); ?></p>

                <?php
                if (get_setting("enable_comments_on_estimates") && !($estimate_info->status === "draft")) {
                    echo view("estimates/comment_form");
                }
                ?>

            </div>
        </div>

        <?php
        $signer_info = @unserialize($estimate_info->meta_data);
        if (!($signer_info && is_array($signer_info))) {
            $signer_info = array();
        }
        ?>
        <?php if ($estimate_status === "accepted" && ($signer_info || $estimate_info->accepted_by)) { ?>
            <div class="card mt15">
                <div class="page-title clearfix ">
                    <h1><?php echo app_lang("signer_info"); ?></h1>
                </div>
                <div class="p15">
                    <div><strong><?php echo app_lang("name"); ?>: </strong><?php echo $estimate_info->accepted_by ? get_client_contact_profile_link($estimate_info->accepted_by, $estimate_info->signer_name) : get_array_value($signer_info, "name"); ?></div>
                    <div><strong><?php echo app_lang("email"); ?>: </strong><?php echo $estimate_info->signer_email ? $estimate_info->signer_email : get_array_value($signer_info, "email"); ?></div>
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
        $("#estimate-item-table").appTable({
            source: '<?php echo_uri("estimates/item_list_data/" . $estimate_info->id . "/") ?>',
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
                $("#estimate-item-table").find("tbody").attr("id", "estimate-item-table-sortable");
                var $selector = $("#estimate-item-table-sortable");

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
                            url: '<?php echo_uri("estimates/update_item_sort_values") ?>',
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
                $("#estimate-total-section").html(result.estimate_total_view);
                if (typeof updateInvoiceStatusBar == 'function') {
                    updateInvoiceStatusBar(result.estimate_id);
                }
            },
            onUndoSuccess: function (result) {
                $("#estimate-total-section").html(result.estimate_total_view);
                if (typeof updateInvoiceStatusBar == 'function') {
                    updateInvoiceStatusBar(result.estimate_id);
                }
            }
        });

        //print estimate
        $("#print-estimate-btn").click(function () {
            appLoader.show();

            $.ajax({
                url: "<?php echo get_uri('estimates/print_estimate/' . $estimate_info->id) ?>",
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        document.body.innerHTML = result.print_view; //add estimate's print view to the page
                        $("html").css({"overflow": "visible"});

                        setTimeout(function () {
                            window.print();
                        }, 200);
                    } else {
                        appAlert.error(result.message);
                    }

                    appLoader.hide();
                }
            });
        });

        //reload page after finishing print action
        window.onafterprint = function () {
            location.reload();
        };

    });


    updateInvoiceStatusBar = function (estimateId) {
        $.ajax({
            url: "<?php echo get_uri("estimates/get_estimate_status_bar"); ?>/" + estimateId,
            success: function (result) {
                if (result) {
                    $("#estimate-status-bar").html(result);
                }
            }
        });
    };

</script>

<?php
//required to send email 

load_css(array(
    "assets/js/summernote/summernote.css",
));
load_js(array(
    "assets/js/summernote/summernote.min.js",
));
?>
