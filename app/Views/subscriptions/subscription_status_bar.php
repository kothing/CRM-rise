<div class="bg-white  p15 no-border m0 rounded-bottom">
    <span class="mr10"><?php echo $subscription_status_label; ?></span>
    <span><?php echo $subscription_type_label; ?></span>

    <?php echo make_labels_view_data($subscription_info->labels_list, "", true); ?>

    <?php if ($subscription_info->payment_status === "failed") { ?>
        <span class="ml15"><?php
            echo app_lang("payment_status") . ": " . "<span class='mt0 badge bg-danger large'>" . app_lang("failed") . "</span>";
            ?>
        </span> 
    <?php } ?>

    <span class="ml10"><?php
        echo app_lang("client") . ": ";
        echo (anchor(get_uri("clients/view/" . $subscription_info->client_id), $subscription_info->company_name));
        ?>
    </span>

    <?php if ($subscription_info->cancelled_at) { ?>
        <span class="ml15"><?php echo app_lang("cancelled_at") . ": " . format_to_relative_time($subscription_info->cancelled_at); ?></span>
    <?php } ?>

    <?php if ($subscription_info->cancelled_by) { ?>
        <span class="ml15"><?php echo app_lang("cancelled_by") . ": " . get_team_member_profile_link($subscription_info->cancelled_by, $subscription_info->cancelled_by_user); ?></span>
    <?php } ?>

</div>