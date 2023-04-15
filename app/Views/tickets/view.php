<?php $user_id = $login_user->id; ?>

<?php if ($view_type != "modal_view") { ?>
    <div id="page-content" class="page-wrapper clearfix grid-button">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="page-title clearfix ticket-view-title">
                        <h1><?php echo get_ticket_id($ticket_info->id) . " - " . $ticket_info->title ?></h1>
                        <div class="title-button-group p10">

                            <?php
                            if (can_access_reminders_module()) {
                                echo modal_anchor(get_uri("events/reminders"), "<i data-feather='clock' class='icon-16'></i> " . app_lang('reminders'), array("class" => "btn btn-default m5", "id" => "reminder-icon", "data-post-ticket_id" => $ticket_info->id, "title" => app_lang('reminders') . " (" . app_lang('private') . ")"));
                            }
                            ?>

                            <span class="dropdown inline-block">
                                <button class="btn btn-default dropdown-toggle caret mt0 mb0" type="button" data-bs-toggle="dropdown" aria-expanded="true">
                                    <i data-feather='settings' class='icon-16'></i> <?php echo app_lang('actions'); ?>
                                </button>
                                <ul class="dropdown-menu float-end" role="menu">
                                    <?php if ($login_user->user_type == "staff") { ?>
                                        <li role="presentation"><?php echo modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='edit-2' class='icon-16'></i> " . app_lang('edit'), array("title" => app_lang('ticket'), "data-post-view" => "details", "data-post-id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>
                                        <?php if ($can_create_tasks && !$ticket_info->task_id) { ?> 
                                            <li role="presentation"><?php echo modal_anchor(get_uri("projects/task_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('create_new_task'), array("title" => app_lang('create_new_task'), "data-post-project_id" => $ticket_info->project_id, "data-post-ticket_id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>
                                        <?php } ?>

                                    <?php } ?>

                                    <?php if ($ticket_info->status === "closed") { ?>
                                        <li role="presentation"><?php echo ajax_anchor(get_uri("tickets/save_ticket_status/$ticket_info->id/open"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_open'), array("class" => "dropdown-item", "title" => app_lang('mark_as_open'), "data-reload-on-success" => "1")); ?> </li>
                                    <?php } else { ?>
                                        <li role="presentation"><?php echo ajax_anchor(get_uri("tickets/save_ticket_status/$ticket_info->id/closed"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_closed'), array("class" => "dropdown-item", "title" => app_lang('mark_as_closed'), "data-reload-on-success" => "1")); ?> </li>
                                        <li role="presentation"><?php echo modal_anchor(get_uri("tickets/merge_ticket_modal_form"), "<i data-feather='git-merge' class='icon-16'></i> " . app_lang('merge'), array("title" => app_lang('merge'), "data-post-ticket_id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>
                                    <?php } ?>
                                    <?php if ($ticket_info->assigned_to === "0" && $login_user->user_type == "staff") { ?>
                                        <li role="presentation"><?php echo ajax_anchor(get_uri("tickets/assign_to_me/$ticket_info->id"), "<i data-feather='user' class='icon-16'></i> " . app_lang('assign_to_me'), array("class" => "dropdown-item", "title" => app_lang('assign_myself_in_this_ticket'), "data-reload-on-success" => "1")); ?></li>
                                    <?php } ?>
                                    <?php if ($ticket_info->client_id === "0" && $login_user->user_type == "staff") { ?>
                                        <?php if ($can_create_client) { ?>
                                            <li role="presentation"><?php echo modal_anchor(get_uri("clients/modal_form"), "<i data-feather='plus' class='icon-16'></i> " . app_lang('link_to_new_client'), array("title" => app_lang('link_to_new_client'), "data-post-ticket_id" => $ticket_info->id, "class" => "dropdown-item")); ?></li>
                                        <?php } ?>
                                        <li role="presentation"><?php echo modal_anchor(get_uri("tickets/add_client_modal_form/$ticket_info->id"), "<i data-feather='link' class='icon-16'></i> " . app_lang('link_to_existing_client'), array("title" => app_lang('link_to_existing_client'), "class" => "dropdown-item")); ?></li>
                                    <?php } ?>
                                </ul>
                            </span>
                        </div>
                    </div>
                    <div class="card-body ticket-card">
                        <?php echo view("tickets/view_data"); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="modal-body clearfix general-form">
            <?php echo view("tickets/view_data"); ?>
        </div>

        <div class="modal-footer">
            <?php if ($ticket_info->assigned_to === "0" && $login_user->user_type == "staff") { ?>
                <?php echo ajax_anchor(get_uri("tickets/assign_to_me/$ticket_info->id"), "<i data-feather='user' class='icon-16'></i> " . app_lang('assign_to_me'), array("class" => "btn btn-info text-white", "title" => app_lang('assign_myself_in_this_ticket'), "data-reload-on-success" => "1")); ?>
            <?php } ?>
            <?php if ($ticket_info->status === "closed") { ?>
                <?php echo ajax_anchor(get_uri("tickets/save_ticket_status/$ticket_info->id/open"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_open'), array("class" => "btn btn-danger", "title" => app_lang('mark_as_open'), "data-reload-on-success" => "1")); ?>
            <?php } else { ?>
                <?php echo ajax_anchor(get_uri("tickets/save_ticket_status/$ticket_info->id/closed"), "<i data-feather='check-circle' class='icon-16'></i> " . app_lang('mark_as_closed'), array("class" => "btn btn-success", "title" => app_lang('mark_as_closed'), "data-reload-on-success" => "1")); ?>
            <?php } ?>
            <?php if ($login_user->user_type == "staff") { ?>
                <?php if ($can_create_tasks && !$ticket_info->task_id) { ?> 
                    <?php echo modal_anchor(get_uri("projects/task_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('create_new_task'), array("title" => app_lang('create_new_task'), "data-post-project_id" => $ticket_info->project_id, "data-post-ticket_id" => $ticket_info->id, "class" => "btn btn-default")); ?>
                <?php } ?>
                <?php echo modal_anchor(get_uri("tickets/modal_form"), "<i data-feather='edit-2' class='icon-16'></i> " . app_lang('edit'), array("title" => app_lang('ticket'), "data-post-view" => "details", "data-post-id" => $ticket_info->id, "class" => "btn btn-default")); ?>
            <?php } ?>

            <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
        </div>
    <?php } ?>

    <script type="text/javascript">
        $(document).ready(function () {
            var uploadUrl = "<?php echo get_uri("tickets/upload_file"); ?>";
            var validationUrl = "<?php echo get_uri("tickets/validate_ticket_file"); ?>";

            var decending = "<?php echo $sort_as_decending; ?>";

            var dropzone = attachDropzoneWithForm("#ticket-comment-dropzone", uploadUrl, validationUrl);

            $("#comment-form").appForm({
                isModal: false,
                onSuccess: function (result) {
                    $("#description").val("");

                    if (decending) {
                        $(result.data).insertAfter("#comment-form-container");
                    } else {
                        $(result.data).insertBefore("#comment-form-container");
                    }

                    appAlert.success(result.message, {duration: 10000});

                    dropzone.removeAllFiles();
                }
            });

            if ("<?php echo!get_setting('user_' . $user_id . '_signature') == '' ?>") {
                $("#description").text("\n" + $("#description").text());
                $("#description").focus();
            }

            window.refreshAfterAddTask = true;

            var $inputField = $("#description"), $lastFocused;

            function saveCursorPositionOfRichEditor() {
                $inputField.summernote('saveRange');
                $lastFocused = "rich-editor";
            }

            //store the cursor position
            if (AppHelper.settings.enableRichTextEditor === "1") {
                $inputField.on("summernote.change", function (e) {
                    saveCursorPositionOfRichEditor();
                });

                //it'll grab only cursor clicks
                $("body").on("click", ".note-editable", function () {
                    saveCursorPositionOfRichEditor();
                });
            } else {
                $inputField.focus(function () {
                    $lastFocused = document.activeElement;
                });
            }

            function insertTemplate(text) {
                if ($lastFocused === undefined) {
                    return;
                }

                if (AppHelper.settings.enableRichTextEditor === "1") {
                    $inputField.summernote('restoreRange');
                    $inputField.summernote('focus');
                    $inputField.summernote('pasteHTML', text);
                } else {
                    var scrollPos = $lastFocused.scrollTop;
                    var pos = 0;
                    var browser = (($lastFocused.selectionStart || $lastFocused.selectionStart === "0") ? "ff" : (document.selection ? "ie" : false));

                    if (browser === "ff") {
                        pos = $lastFocused.selectionStart;
                    }

                    var front = ($lastFocused.value).substring(0, pos);
                    var back = ($lastFocused.value).substring(pos, $lastFocused.value.length);
                    $lastFocused.value = front + text + back;
                    pos = pos + text.length;

                    $lastFocused.scrollTop = scrollPos;
                }

                //close the modal
                $("#close-template-modal-btn").trigger("click");
            }

            //init uninitialized rich editor to insert template 
            $("#insert-template-btn").click(function () {
                setSummernote($("#description"));
            });

            //insert ticket template
            $("body").on("click", "#ticket-template-table tr", function () {
                var template = $(this).find(".js-description").html();
                if (AppHelper.settings.enableRichTextEditor !== "1") {
                    //insert only text when rich editor isn't enabled
                    var template = $(this).find(".js-description").text();
                }

                if ($lastFocused === undefined) {
                    if (AppHelper.settings.enableRichTextEditor === "1") {
                        $("#description").summernote("code", template);
                    } else {
                        $("#description").text(template);
                    }

                    //close the modal
                    $("#close-template-modal-btn").trigger("click");
                } else {
                    insertTemplate(template);
                }

            });

            //set value 1, when click save as button
            $("#save-as-note-button").click(function () {
                $("#is-note").val('1');
                $(this).trigger("submit");
            });

            //set value 0, when click post comment button
            $("#save-ticket-comment-button").click(function () {
                $("#is-note").val('0');
            });

            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
