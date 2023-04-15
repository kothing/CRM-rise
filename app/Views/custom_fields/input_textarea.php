<?php
echo form_textarea(array(
    "id" => "custom_field_" . $field_info->id,
    "name" => "custom_field_" . $field_info->id,
    "value" => isset($field_info->value) ? process_images_from_content($field_info->value, false) : "",
    "class" => "form-control",
    "placeholder" => $placeholder,
    "data-rule-required" => $field_info->required ? true : "false",
    "data-msg-required" => app_lang("field_required"),
    "data-rich-text-editor" => true,
    "data-keep-rich-text-editor-after-submit" => true
));
?>

<script type="text/javascript">
    $(document).ready(function () {
        setSummernoteToAll(true);
    });
</script>