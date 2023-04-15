<div class="card bg-white">
    <div class="row">
        <div class="col-md-5 col-5">
            <div class="pl30 pt30">
                <div class="b-r">
                    <h3 class="mt-0 mb-1 strong text-danger"><?php echo $reminders_of_today; ?></h3>
                    <div class="text-truncate"><?php echo (($reminders_of_today > 1) ? app_lang("reminders") : app_lang("reminder")) . " " . app_lang("today"); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-7 col-7">
            <div class="pl0 mt5 p30">
                <div class="mt-0 mb-1 text-truncate"><i data-feather='bell' class='icon text-danger'></i> <span class="ml5"><?php echo app_lang("next_reminder"); ?></span></div>
                <div class="text-truncate">
                    <?php
                    if ($next_reminder) {

                        $today = get_today_date();
                        $tomorrow = get_tomorrow_date();

                        if ($next_reminder->start_date === $today) {
                            echo format_to_time($next_reminder->start_date . " " . $next_reminder->start_time, false); //If reminder is today, then show only time.
                        } else if ($next_reminder->start_date === $tomorrow) {
                            echo app_lang("tomorrow"); //If reminder is tomorrow, show only tomorrow.
                        } else {
                            echo format_to_date($next_reminder->start_date, false); //Otherwise, show the date only.
                        }

                        //show reminder
                        $context_info = get_reminder_context_info($next_reminder);
                        $context_url = get_array_value($context_info, "context_url");
                        echo " - <span title='" . $next_reminder->title . "'>" . ($context_url ? anchor($context_url, $next_reminder->title) : link_it($next_reminder->title)) . "</span>";
                    } else {
                        echo "<span class='text-off'>" . app_lang("no") . " " . strtolower(app_lang("reminder")) . "</span>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>