<?php
$task_id = "";

foreach ($comments as $comment) {

    $type = "project";
    $type_id = $comment->project_id;
    $pin_status = "";
    $unpin_status = "";
    if ($comment->file_id) {
        $type = "file";
        $type_id = $comment->file_id;
    } else if ($comment->task_id) {
        $type = "task";
        $type_id = $comment->task_id;
        $task_id = $comment->task_id;
    } else if ($comment->customer_feedback_id) {
        $type = "customer_feedback";
        $type_id = $comment->customer_feedback_id;
    }

    if ($comment->pinned_comment_status) {
        $pin_status = "hide";
        $unpin_status = "";
    } else {
        $pin_status = "";
        $unpin_status = "hide";
    }
    ?>
    <div id="comment-<?php echo $comment->id; ?>" class="comment-highlight-section" >
        <div id="prject-comment-container-<?php echo $type . "-" . $comment->id; ?>"  class="comment-container text-break b-b <?php echo "comment-" . $type; ?>">
            <div class="d-flex">
                <div class="flex-shrink-0 comment-avatar">
                    <span class="avatar <?php echo ($type === "project") ? " avatar-sm" : " avatar-xs"; ?> ">
                        <img src="<?php echo get_avatar($comment->created_by_avatar); ?>" alt="..." />
                    </span>
                </div>
                <div class="w-100 ps-2">
                    <div class="mb5">
                        <?php
                        if ($comment->user_type === "staff") {
                            echo get_team_member_profile_link($comment->created_by, $comment->created_by_user, array("class" => "dark strong"));
                        } else {
                            echo get_client_contact_profile_link($comment->created_by, $comment->created_by_user, array("class" => "dark strong"));
                        }
                        ?>
                        <small><span class="text-off"><?php echo format_to_relative_time($comment->created_at); ?></span></small>

                        <?php if ($type === "task" || ($login_user->is_admin || $comment->created_by == $login_user->id)) { ?>
                            <span class="float-end dropdown comment-dropdown">
                                <div class="text-off dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="true" >
                                    <i data-feather="chevron-down" class="icon-16 clickable"></i>
                                </div>
                                <ul class="dropdown-menu dropdown-menu-end" role="menu">
                                    <?php if ($type === "task") { ?>
                                        <li role="presentation"><a href="javascript:;" title="<?php echo app_lang('copy_comment_link') ?>" class="copy-comment-link-button dropdown-item" data-comment-id="<?php echo $comment->id; ?>" data-task-id="<?php echo $comment->task_id; ?>"><span data-feather="copy" class="icon-16"></span> <?php echo app_lang('copy_link'); ?></a></li>

                                        <li role="presentation"><?php echo ajax_anchor(get_uri("projects/pin_comment/" . $comment->id), "<i data-feather='map-pin' class='icon-16'></i> " . app_lang('unpin_comment'), array("id" => "unpin-comment-button-$comment->id", "class" => "dropdown-item unpin-comment-button $unpin_status", 'title' => app_lang('unpin_comment'), "data-pin-comment-id" => $comment->id, "data-fade-out-on-success" => "#pinned-comment-$comment->id")); ?> </li>
                                        <li role="presentation"><?php echo js_anchor("<i data-feather='map-pin' class='icon-16'></i> " . app_lang('pin_comment'), array("id" => "pin-comment-button-$comment->id", "class" => "dropdown-item pin-comment-button $pin_status", 'title' => app_lang('pin_comment'), "data-action-url" => get_uri("projects/pin_comment/" . $comment->id), "data-pin-comment-id" => $comment->id)); ?> </li>
                                    <?php } ?>

                                    <?php if ($login_user->is_admin || $comment->created_by == $login_user->id) { ?>
                                        <li role="presentation"><?php echo ajax_anchor(get_uri("projects/delete_comment/$comment->id"), "<i data-feather='x' class='icon-16'></i> " . app_lang('delete'), array("class" => "dropdown-item", "title" => app_lang('delete'), "data-fade-out-on-success" => "#prject-comment-container-$type-$comment->id")); ?> </li>
                                    <?php } ?>
                                </ul>
                            </span>
                        <?php } ?>
                        <span class="float-end comment-like-top">
                            <?php
                            if (isset($comment->like_status)) {
                                $like_icon = $comment->like_status ? "thumbs-up" : "thumbs-up";
                                $like_icon_fill = $comment->like_status ? "icon-fill-secondary" : "";
                                echo ajax_anchor(get_uri("projects/like_comment/" . $comment->id), "<i data-feather='$like_icon' class='icon-14 $like_icon_fill'></i> " . app_lang('like') . " ", array("class" => "mr5 like-button", "data-real-target" => "#comment-like-container-$comment->id"));
                            }
                            ?>
                        </span>

                    </div>
                    <p><?php echo convert_mentions(convert_comment_link(process_images_from_content($comment->description))); ?></p>

                    <div class="comment-image-box clearfix">

                        <?php
                        $files = unserialize($comment->files);
                        $total_files = count($files);
                        echo view("includes/timeline_preview", array("files" => $files));
                        ?>

                        <div class="mb15 clearfix">
                            <?php if (isset($comment->like_status)) { ?>
                                <span id="comment-like-container-<?php echo $comment->id; ?>">
                                    <?php echo view("projects/comments/like_comment", array("comment" => $comment)); ?>
                                </span>
                            <?php } ?>

                            <?php
                            $can_reply = false;
                            if ($type === "project" || $type === "customer_feedback") {
                                $can_reply = true;
                                echo ajax_anchor(get_uri("projects/comment_reply_form/" . $comment->id . "/" . $type . "/" . $type_id), "<i data-feather='corner-up-left' class='icon-16'></i> " . app_lang('reply'), array("data-real-target" => "#reply-form-container-" . $comment->id));
                            }
                            ?>
                            <?php
                            $reply_caption = "";
                            if ($comment->total_replies == 1) {
                                $reply_caption = app_lang("reply");
                            } else if (($comment->total_replies > 1)) {
                                $reply_caption = app_lang("replies");
                            }

                            if ($reply_caption) {
                                echo ajax_anchor(get_uri("projects/view_comment_replies/" . $comment->id), "<i data-feather='message-circle' class='icon-16'></i> " . app_lang("view") . " " . $comment->total_replies . " " . $reply_caption, array("class" => "btn btn-default btn-xs view-replies", "id" => "show-replies-" . $comment->id, "data-remove-on-success" => "#show-replies-" . $comment->id, "data-real-target" => "#reply-list-" . $comment->id));
                            }

                            //create link for reply success. trigger this link after submit any reply
                            echo ajax_anchor(get_uri("projects/view_comment_replies/" . $comment->id), "", array("class" => "hide", "id" => "reload-reply-list-button-" . $comment->id, "data-real-target" => "#reply-list-" . $comment->id));

                            if ($total_files) {
                                $download_caption = app_lang('download');
                                if ($total_files > 1) {
                                    $download_caption = sprintf(app_lang('download_files'), $total_files);
                                }
                                if (!$can_reply) {
                                    echo "<i data-feather='paperclip' class='icon-16'></i>";
                                }

                                echo anchor(get_uri("projects/download_comment_files/" . $comment->id), $download_caption, array("class" => "float-end", "title" => $download_caption));
                            }
                            ?>
                        </div>
                    </div>
                    <div id="reply-list-<?php echo $comment->id; ?>"></div>
                    <div id="reply-form-container-<?php echo $comment->id; ?>"></div>

                </div>
            </div>
        </div>
    </div>
<?php } ?>



<script>
    $(document).ready(function () {
        $(".like-button").click(function () {
            var $icon = $(this).find("svg");
            $icon.toggleClass("icon-fill-secondary");
        });

        $(".comment-highlight-link").click(function (e) {
            var commentId = $(this).attr('data-comment-id');
            var taskId = $(this).attr('data-task-id');

            if (taskId === "<?php echo $task_id; ?>") {
                e.preventDefault();
            }

            highlightSpecificComment(commentId);
        });

        //highlight comment from url
        var commentHash = window.location.hash;
        if (commentHash.indexOf('#comment') > -1) {
            var splitCommentId = commentHash.split("-");
            var commentId = splitCommentId[1];
            highlightSpecificComment(commentId);
        }

        function highlightSpecificComment(commentId) {
            $(".comment-highlight-section").removeClass("comment-highlight");
            $("#comment-" + commentId).addClass("comment-highlight");
            window.location.hash = ""; //remove first to scroll with main link
            window.location.hash = "comment-" + commentId;
        }

        $(".pin-comment-button").click(function () {
            var comment_id = $(this).attr('data-pin-comment-id');
            appLoader.show();
            $.ajax({
                url: "<?php echo get_uri("projects/pin_comment/"); ?>/" + comment_id,
                type: 'POST',
                dataType: "json",
                success: function (result) {
                    if (result.success) {
                        $("#pinned-comment").append(result.data);
                        appLoader.hide();
                    } else {
                        appAlert.error(result.message);
                    }

                    if (result.status) {
                        $("#pin-comment-button-" + comment_id).addClass("hide");
                        $("#unpin-comment-button-" + comment_id).removeClass("hide");
                        $("#pinned-comment").removeClass("hide");
                    }
                }
            });
        });

        $(".unpin-comment-button").click(function () {
            var comment_id = $(this).attr('data-pin-comment-id');
            $("#pin-comment-button-" + comment_id).removeClass("hide");
            $("#unpin-comment-button-" + comment_id).addClass("hide");
        });

        $(".pinned-comment-highlight-link").click(function (e) {
            var comment_id = $(this).attr('data-original-comment-link-id');
            $(".comment-highlight-section").removeClass("comment-highlight");
            $("#comment-" + comment_id).addClass("comment-highlight");
            window.location.hash = $(this).attr('data-original-comment-id');
            e.preventDefault();
        });
    });

    $(".copy-comment-link-button").click(function () {
        var commentId = $(this).attr('data-comment-id');
        var taskId = $(this).attr('data-task-id');
        var tempInput = document.createElement("input");
        tempInput.style = "position: absolute; left: -1000px; top: -1000px";
        tempInput.value = "<?php echo get_uri("projects/task_view"); ?>/" + taskId + "/#comment-" + commentId;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
    });
</script>