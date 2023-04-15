<div class="card mb0">
    <div class="page-title clearfix notificatio-plate-title-area">
        <span class="float-start"><strong><?php echo app_lang('messages'); ?></strong></span>
    </div>

    <div class="list-group" id="messages-popup-list">
        <?php
        if (count($notifications)) {
            foreach ($notifications as $notification) {

                //check read/unread class
                $notification_class = "";
                if ($notification->status === "unread") {
                    $notification_class = "unread-notification";
                }
                ?>
                <a class="list-group-item d-flex <?php echo $notification_class; ?>" href="<?php echo get_uri("messages/inbox/" . $notification->main_message_id); ?>">
                    <div class="flex-shrink-0">
                        <span class="avatar avatar-xs">
                            <img src="<?php echo get_avatar($notification->user_image); ?>" alt="..." />
                        </span>
                    </div>
                    <div class="w-100 ps-2 text-wrap-ellipsis">
                        <div class="mb5">
                            <strong><?php echo $notification->user_name; ?></strong>
                            <span class="text-off float-end"><small><?php echo format_to_relative_time($notification->created_at); ?></small></span>
                        </div>
                        <div class="text-wrap-ellipsis">
                            <?php
                            $subject = $notification->subject;
                            if ($notification->reply_subject) {
                                $subject = $notification->reply_subject;
                            }

                            echo $subject;
                            ?>
                        </div>
                    </div>
                </a>
                <?php
            }
        } else {
            ?>
            <span class="list-group-item"><?php echo app_lang("no_new_messages"); ?></span>               
        <?php } ?>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        //don't apply scrollbar for mobile devices
        if ($(window).width() > 640) {
            if ($('#messages-popup-list').height() >= 400) {
                initScrollbar('#messages-popup-list', {
                    setHeight: 400
                });
            } else {
                $('#messages-popup-list').css({"overflow-y": "auto"});
            }
        }
    });
</script>