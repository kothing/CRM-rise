<div id="estimate-comment-container-<?php echo $comment->id; ?>" class="comment-container b-t pt15">
    <div class="d-flex bg-white">
        <div class="flex-shrink-0 pl10 mr10">
            <span class="avatar avatar-xs">
                <img src="<?php echo get_avatar($comment->created_by_avatar); ?>" alt="..." />
            </span>
        </div>
        <div class="w-100">
            <div class="d-flex">
                <div class="w-100">
                    <?php
                    if ($comment->user_type === "staff") {
                        echo get_team_member_profile_link($comment->created_by, $comment->created_by_user, array("class" => "dark strong"));
                    } else {
                        echo get_client_contact_profile_link($comment->created_by, $comment->created_by_user, array("class" => "dark strong"));
                    }
                    ?>
                    <small><span class="text-off"><?php echo format_to_relative_time($comment->created_at); ?></span></small>
                </div>
                <?php if ($login_user->is_admin || $comment->created_by == $login_user->id) { ?>
                    <div class="flex-shrink-0 estimate-comment-dropdown">
                        <span class="float-end dropdown">
                            <div class="text-off dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="true" >
                                <i data-feather="chevron-down" class="icon"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end" role="menu">
                                <li role="presentation"><?php echo ajax_anchor(get_uri("estimates/delete_comment/$comment->id"), "<i data-feather='x' class='icon-16'></i> " . app_lang('delete'), array("class" => "dropdown-item", "title" => app_lang('delete'), "data-fade-out-on-success" => "#estimate-comment-container-$comment->id")); ?> </li>
                            </ul>
                        </span>
                    </div>
                <?php } ?>
            </div>
            <p><?php echo nl2br(link_it(process_images_from_content($comment->description))); ?></p>
            <div class="comment-image-box clearfix w-auto">

                <?php
                $files = unserialize($comment->files);
                $total_files = count($files);
                echo view("includes/timeline_preview", array("files" => $files));


                if ($total_files) {
                    $download_caption = app_lang('download');
                    if ($total_files > 1) {
                        $download_caption = sprintf(app_lang('download_files'), $total_files);
                    }
                    echo "<i data-feather='paperclip' class='icon-16'></i>";
                    echo anchor(get_uri("estimates/download_comment_files/" . $comment->id), $download_caption, array("class" => "float-end", "title" => $download_caption));
                }
                ?>
            </div>
        </div>
    </div>
</div>