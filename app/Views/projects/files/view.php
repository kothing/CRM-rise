<div class="app-modal">
    <div class="app-modal-content">
        <?php echo view("includes/file_preview"); ?>
    </div>

    <div class="app-modal-sidebar">

        <div class="mb15 pl15 pr15">
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <span class='avatar avatar-xs mt5'><img src='<?php echo get_avatar($file_info->uploaded_by_user_image); ?>' alt='...'></span>
                </div>
                <div class="w-100 ps-3">
                    <div><?php
                        if ($file_info->uploaded_by_user_type == "staff") {
                            echo get_team_member_profile_link($file_info->uploaded_by, $file_info->uploaded_by_user_name);
                        } else {
                            echo get_client_contact_profile_link($file_info->uploaded_by, $file_info->uploaded_by_user_name);
                        }
                        ?></div>
                    <small><span class="text-off"><?php echo format_to_relative_time($file_info->created_at); ?></span></small>
                </div>
            </div>
            <div class="pt10 pb10 b-b">
                <?php echo $file_info->description; ?>
            </div>
        </div>

        <div class="mr15">
            <div class="pl15 pr15">
                <?php
                if ($can_comment_on_files) {
                    echo view("projects/comments/comment_form");
                }
                ?>
            </div>

            <div id="file-preview-comment-container" class="pt15">
                <?php echo view("projects/comments/comment_list"); ?>
            </div>

            <script>

                function showPreviousFile() {
                    if (window.currentIndex === 0) {
                        x = filesTableData.length - 1;
                    } else {
                        var x = window.currentIndex - 1;
                    }

                    $($("#project-file-table").find("[data-toggle=app-modal]")[x]).trigger("click");
                }

                function showNextFile() {
                    if (window.currentIndex === filesTableData.length - 1) {
                        x = 0;
                    } else {
                        var x = window.currentIndex + 1;
                    }

                    $($("#project-file-table").find("[data-toggle=app-modal]")[x]).trigger("click");
                }

                var currentURL = "<?php echo $current_url; ?>";

                var filesTableData = $('#project-file-table').DataTable().rows({order: 'applied'}).data();
                window.currentIndex = 0;

                for (var i = 0; i < filesTableData.length; i++) {
                    var url = $(filesTableData[i][1]).find("a").data("url");
                    console.log(currentURL);
                    if (currentURL === url) {
                        currentIndex = i;
                    }
                }
                
                //don't show next and previous button if there is one file.
                if (filesTableData.length === 1) {
                    $(".app-modal-files-button").addClass("hide");
                }

                $(document).ready(function () {
                    initScrollbarOnCommentContainer();
                });
            </script>

        </div>
    </div>

</div>