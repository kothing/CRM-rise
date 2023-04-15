<script type="text/javascript">
    var reminders = <?php echo json_encode($reminders); ?>;

    getMissedRemindersCount = function () {
        $.ajax({
            url: "<?php echo get_uri("events/count_missed_reminders") ?>",
            dataType: "json",
            success: function (result) {
                var badge = "";
                if (result.success && result.total_reminders && result.total_reminders * 1) {
                    badge = "<span class='badge bg-danger up'>" + result.total_reminders + "</span>";
                }

                $("#reminder-icon").html("<i data-feather='clock' class='icon'></i> " + badge);
                feather.replace();
            }
        });
    };

    getReminders = function () {
        $.ajax({
            url: "<?php echo get_uri("events/get_reminders_for_current_user") ?>",
            dataType: "json",
            success: function (result) {
                reminders = result.reminders;
            }
        });

        getMissedRemindersCount();
    };

    getMissedRemindersCount();

    $(document).ready(function () {
        function checkReminders() {
            $.each(reminders, function (index, reminder) {
                if ((moment().format("YYYY-MM-DD") === reminder.start_date && moment().format("HH:mm") === moment(reminder.start_time, "HH:mm:ss").format("HH:mm") && !reminder.snoozing_time && !reminder.next_recurring_time) //main reminder time
                        || moment(reminder.snoozing_time, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DD HH:mm") === moment().format("YYYY-MM-DD HH:mm") //snoozing time
                        || moment(reminder.next_recurring_time, "YYYY-MM-DD HH:mm:ss").format("YYYY-MM-DD HH:mm") === moment().format("YYYY-MM-DD HH:mm") //recurring time
                        ) {
                    showReminder(reminder);
                }
            });
        }

        checkReminders(); //on loading a page, on first minute the interval won't work
        window.setInterval(function () {
            checkReminders();
        }, 60000); //check reminders in every 1 minute

        function showReminder(reminder) {
            playNotification();

            if (AppHelper.https === "1") {
                //browser notification
                var data = {
                    message: reminder.title,
                    title: reminder.title,
                    icon: "<?php echo get_avatar("system_bot") ?>",
                    notification_id: reminder.id,
                    url_attributes: "href='#' data-act='ajax-modal' data-title='<?php echo app_lang("reminder_details"); ?>' data-action-url='<?php echo get_uri("events/reminder_view"); ?>' data-post-id='" + reminder.id + "'", //open small modal for action
                    isReminder: true,
                    notificationTimeout: 60000
                };

                showBrowserNotification(data);
            } else {
                //app notification
                var detailsDom = '<a class="color-white" data-act="ajax-modal" data-title="<?php echo app_lang("reminder_details"); ?>" data-action-url="<?php echo get_uri("events/reminder_view"); ?>" data-post-id="' + reminder.id + '" href="javascript:;" ><?php echo app_lang("details"); ?></a>';
                var snoozeDom = '<a class="color-white" data-act="snooze-reminder" href="javascript:;" data-id=' + reminder.id + '><?php echo app_lang("snooze"); ?></a>';
                var dismissDom = '<a class="color-white" data-act="dismiss-reminder" href="javascript:;" data-id=' + reminder.id + '><?php echo app_lang("dismiss"); ?></a>';
                var actionButtonsDom = '<br /> ' + detailsDom + ' &#8226; ' + snoozeDom + ' &#8226; ' + dismissDom;

                if (reminder.share_with) {
                    //don't show snooze button for shared events
                    actionButtonsDom = '<br /> ' + detailsDom + dismissDom;
                }

                appAlert.warning(reminder.title + " " + actionButtonsDom, {duration: 300000}); //show reminder for 5 minutes
            }
        }

        //snooze
        $("body").on("click", "[data-act='snooze-reminder']", function () {
            appLoader.show();
            $.ajax({url: "<?php echo get_uri('events/snooze_reminder') ?>",
                type: 'POST',
                dataType: 'json',
                data: {id: $(this).attr("data-id")},
                success: function (result) {
                    if (result.success) {
                        closeReminder();
                        if (typeof getReminders === 'function') {
                            getReminders();
                        }
                    }
                }
            });
        });

        //refresh reminders on click any action
        $("body").on("click", ".reminder-action", function () {
            setTimeout(function () {
                if (typeof getReminders === 'function') {
                    getReminders();
                }
            }, 5000);
        });

        //dismiss
        $("body").on("click", "[data-act='dismiss-reminder']", function () {
            appLoader.show();
            var detailsUrl = $(this).attr("data-details-url");

            $.ajax({url: "<?php echo get_uri('events/save_reminder_status') ?>/" + $(this).attr('data-id'),
                type: 'POST',
                dataType: 'json',
                data: {value: "shown"},
                success: function (result) {
                    if (result.success) {
                        closeReminder();
                        if (detailsUrl) { //on clicking details url, first dismiss the reminder and open details
                            window.location.href = detailsUrl;
                        }
                    }
                }
            });
        });

        function closeReminder() {
            //close notification and stop sound
            $(".app-alert .btn-close").trigger("click");
            $("#reminder-action-modal").closest("#ajaxModal").modal('hide');
            appLoader.hide();
        }
    });
</script>