<div style="line-height: 10px;"></div><?php
if (isset($contract_info->custom_fields) && $contract_info->custom_fields) {
    foreach ($contract_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value)) . "</span><br />";
        }
    }
}
?>
<span><?php echo app_lang("contract_date") . ": " . format_to_date($contract_info->contract_date, false); ?></span><br />
<span><?php echo app_lang("valid_until") . ": " . format_to_date($contract_info->valid_until, false); ?></span>