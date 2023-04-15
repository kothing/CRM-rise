<div class="bg-white p15 pt0">
    <?php
    $recurring_stopped = false;
    $recurring_status_class = "text-primary";
    $recurring_cycle_class = "";
    if ($subscription_info->no_of_cycles_completed > 0 && $subscription_info->no_of_cycles_completed == $subscription_info->no_of_cycles) {
        $recurring_status_class = "text-danger";
        $recurring_cycle_class = "text-danger";
        $recurring_stopped = true;
    }
    ?>

    <span class="badge large b-a" title="<?php echo app_lang('recurring'); ?>"><i data-feather="refresh-cw" class="icon-18 <?php echo $recurring_status_class; ?>"></i></span>


    <?php
    $cycles = $subscription_info->no_of_cycles_completed . "/" . $subscription_info->no_of_cycles;
    if (!$subscription_info->no_of_cycles) { //if not no of cycles, so it's infinity
        $cycles = $subscription_info->no_of_cycles_completed . "/&#8734;";
    }
    ?>

    <span class="mr15"><?php echo app_lang("repeat_every") . ": " . $subscription_info->repeat_every . " " . app_lang("interval_" . $subscription_info->repeat_type); ?></span>

    <span class="mr15 <?php echo $recurring_cycle_class ?>"><?php echo app_lang("cycles") . ": " . $cycles; ?></span>

    <?php
    if (($subscription_info->status != "cancelled") && !$recurring_stopped && (int) $subscription_info->next_recurring_date && $subscription_info->type != "stripe") {
        ?>
        <span class="mr15"><?php echo app_lang("next_billing_date") . ": " . format_to_date($subscription_info->next_recurring_date, false); ?></span>
    <?php }; ?>


</div>