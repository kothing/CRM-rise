<?php
foreach ($pinned_comments as $comment) {
    ?>
    <a href="#comment-<?php echo $comment->project_comment_id; ?>" id="<?php echo $comment->project_comment_id; ?>" class="pin-comment-preview pinned-comment-highlight-link" data-bs-trigger="hover" data-bs-toggle="popover" data-original-comment-link-id="<?php echo $comment->project_comment_id; ?>" data-original-comment-id="<?php echo "comment-" . $comment->project_comment_id; ?>">    
        <div id="pinned-comment-<?php echo $comment->project_comment_id; ?>" class="d-flex">
            <div class="flex-shrink-0">
                <span class="avatar avatar-xs">
                    <img src="<?php echo get_avatar($comment->pinned_by_avatar); ?>" alt="..." />
                </span>
            </div>
            <div class="w-100 pl10">
                <div class="float-start">
                    <?php echo $comment->pinned_by_user; ?>
                    <small>
                        <p class='text-off'><?php echo $comment->created_at; ?></p>
                    </small>
                </div>
                <div class="float-end pin">
                    <i data-feather="map-pin" class="icon-16 text-warning"></i>
                </div>
            </div>
        </div>
    </a>
<?php }
?>

<script type="text/javascript">
    $(document).ready(function () {
        $(".pin-comment-preview").one('mousemove', function () {
            var messageId = this.id;
            $("#" + messageId).popover({
                placement: 'left',
                container: 'body',
                html: true,
                content: function () {
                    return $('#prject-comment-container-task-' + messageId).html();
                }
            }).popover('show');
        });
    });
</script>