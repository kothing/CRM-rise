<?php
$label_column = isset($label_column) ? $label_column : "col-md-3";
$field_column = isset($field_column) ? $field_column : "col-md-9";

foreach ($custom_fields as $field) {

    $title = "";
    if ($field->title_language_key) {
        $title = app_lang($field->title_language_key);
    } else {
        $title = $field->title;
    }

    $placeholder = "";
    if ($field->placeholder_language_key) {
        $placeholder = app_lang($field->placeholder_language_key);
    } else {
        $placeholder = $field->placeholder;
    }
    ?>
    <div class="form-group " data-field-type="<?php echo $field->field_type; ?>">
        <div class="row">
            <label for="custom_field_<?php echo $field->id ?>" class="<?php echo $label_column; ?>"><?php echo $title; ?></label>

            <div class="<?php echo $field_column; ?>">
                <?php
                if ($field->disable_editing_by_clients && (!isset($login_user->user_type) || $login_user->user_type == "client")) {
                    //for clients, if the 'Disable editing by clients' setting is enabled
                    //show the output instead of input
                    echo view("custom_fields/output_" . $field->field_type, array("value" => $field->value));
                } else {
                    echo view("custom_fields/input_" . $field->field_type, array("field_info" => $field, "placeholder" => $placeholder));
                }
                ?> 
            </div>
        </div>
    </div>
<?php } ?>