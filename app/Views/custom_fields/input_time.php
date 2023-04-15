<?php
$uid = "_" . uniqid(rand());

$time_value = "";
if (isset($field_info->value)) {
    $time_value = is_date_exists($field_info->value) ? $field_info->value : "";

    if (get_setting("time_format") == "24_hours") {
        $time_value = $time_value ? date("H:i", strtotime($time_value)) : "";
    } else {
        $time_value = $time_value ? convert_time_to_12hours_format(date("H:i:s", strtotime($time_value))) : "";
    }
}

echo form_input(array(
    "id" => "custom_field_" . $field_info->id . $uid,
    "name" => "custom_field_" . $field_info->id,
    "value" => $time_value,
    "class" => "form-control",
    "placeholder" => $placeholder,
    "data-rule-required" => $field_info->required ? true : "false",
    "data-msg-required" => app_lang("field_required")
));
?>

<script type="text/javascript">
    $(document).ready(function () {
        setTimePicker("#<?php echo "custom_field_" . $field_info->id . $uid; ?>");
    });
</script>
