<span style="font-size:20px; font-weight: bold;background-color: <?php echo $color; ?>; color: #fff;">&nbsp;<?php echo get_proposal_id($proposal_info->id); ?>&nbsp;</span>
<div style="line-height: 10px;"></div><?php
if (isset($proposal_info->custom_fields) && $proposal_info->custom_fields) {
    foreach ($proposal_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value)) . "</span><br />";
        }
    }
}
?>
<span><?php echo app_lang("proposal_date") . ": " . format_to_date($proposal_info->proposal_date, false); ?></span><br />
<span><?php echo app_lang("valid_until") . ": " . format_to_date($proposal_info->valid_until, false); ?></span>