<div class="bg-white  p15 no-border m0 rounded-bottom">
    <span><?php echo app_lang("status") . ": " . $contract_status_label; ?></span>
    <span class="ml15">
        <?php
        if ($contract_info->is_lead) {
            echo app_lang("lead") . ": ";
            echo (anchor(get_uri("leads/view/" . $contract_info->client_id), $contract_info->company_name));
        } else {
            echo app_lang("client") . ": ";
            echo (anchor(get_uri("clients/view/" . $contract_info->client_id), $contract_info->company_name));
        }
        ?>
    </span>
    <?php if ($contract_info->project_id) { ?>
        <span class="ml15"><?php echo app_lang("project") . ": " . anchor(get_uri("projects/view/" . $contract_info->project_id), $contract_info->project_title); ?></span>
    <?php } ?>
    <span class="ml15"><?php
        echo app_lang("last_email_sent") . ": ";
        echo (is_date_exists($contract_info->last_email_sent_date)) ? format_to_date($contract_info->last_email_sent_date, FALSE) : app_lang("never");
        ?>
    </span>
</div>