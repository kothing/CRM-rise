<?php if (get_setting("invoice_style") == "style_3") { ?>
<div style="font-size: 25px; color: #666; margin-bottom: 10px;"><?php echo app_lang("invoice"); ?></div>
<div style="line-height: 5px;"></div>
<span class="invoice-meta text-default" style="font-size: 90%; color: #666;"><?php echo app_lang("invoice_number") . ": " . get_invoice_id($invoice_info->id); ?></span><br />
<?php } else { ?>
<span class="invoice-info-title" style="font-size:20px; font-weight: bold;background-color: <?php echo $color; ?>; color: #fff;">&nbsp;<?php echo get_invoice_id($invoice_info->id); ?>&nbsp;</span><br />
<div style="line-height: 1px;"></div>
<?php } ?>
<span class="invoice-meta text-default" style="font-size: 90%; color: #666;"><?php
if (isset($invoice_info->custom_fields) && $invoice_info->custom_fields) {
    foreach ($invoice_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value)) . "</span><br />";
        }
    }
}

echo app_lang("bill_date") . ": " . format_to_date($invoice_info->bill_date, false); ?><br /><?php 
echo app_lang("due_date") . ": " . format_to_date($invoice_info->due_date, false); ?></span>