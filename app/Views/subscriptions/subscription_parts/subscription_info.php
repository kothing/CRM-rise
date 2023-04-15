<span class="subscription-info-title" style="font-size:20px; font-weight: bold;background-color: <?php echo $color; ?>; color: #fff;">&nbsp;<?php echo get_subscription_id($subscription_info->id); ?>&nbsp;</span>
<div style="line-height: 10px;"></div><?php
if (isset($subscription_info->custom_fields) && $subscription_info->custom_fields) {
    foreach ($subscription_info->custom_fields as $field) {
        if ($field->value) {
            echo "<span>" . $field->custom_field_title . ": " . view("custom_fields/output_" . $field->custom_field_type, array("value" => $field->value)) . "</span><br />";
        }
    }
}
?>

<?php if ($subscription_info->bill_date) { ?>
    <span><?php echo app_lang("first_billing_date") . ": " . format_to_date($subscription_info->bill_date, false); ?></span><br />
<?php } ?>