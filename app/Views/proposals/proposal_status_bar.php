<div class="bg-white  p15 no-border m0 rounded-bottom">
    <span><?php echo app_lang("status") . ": " . $proposal_status_label; ?></span>
    <span class="ml15">
        <?php
        if ($proposal_info->is_lead) {
            echo app_lang("lead") . ": ";
            echo (anchor(get_uri("leads/view/" . $proposal_info->client_id), $proposal_info->company_name));
        } else {
            echo app_lang("client") . ": ";
            echo (anchor(get_uri("clients/view/" . $proposal_info->client_id), $proposal_info->company_name));
        }
        ?>
    </span>
    <span class="ml15"><?php
        echo app_lang("last_email_sent") . ": ";
        echo (is_date_exists($proposal_info->last_email_sent_date)) ? format_to_date($proposal_info->last_email_sent_date, FALSE) : app_lang("never");
        ?>
    </span>
</div>