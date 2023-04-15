<?php if (get_setting("invoice_style") == "style_3") { ?>
<div style="font-size: 25px; color: #666; margin-bottom: 10px;"><?php echo app_lang("estimate"); ?></div>
<div style="line-height: 5px;"></div>
<span class="invoice-meta text-default" style="font-size: 90%; color: #666;"><?php echo app_lang("estimate_number") . ": " . get_estimate_id($estimate_info->id); ?></span><br />
<?php } else { ?>
<span style="font-size:20px; font-weight: bold;background-color: <?php echo $color; ?>; color: #fff;">&nbsp;<?php echo get_estimate_id($estimate_info->id); ?>&nbsp;</span>
<div style="line-height: 10px;"></div>
<?php } ?>

<span class="invoice-meta text-default" style="font-size: 90%; color: #666;"><?php 
if (isset($estimate_info->custom_fields) && $estimate_info->custom_fields) {
    foreach ($estimate_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value)) . "</span><br />";
        }
    }
}

echo app_lang("estimate_date") . ": " . format_to_date($estimate_info->estimate_date, false); ?><br /><?php 
echo app_lang("valid_until") . ": " . format_to_date($estimate_info->valid_until, false); ?>
</span>