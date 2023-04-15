<div class="form-group">
    <label for="note" class=" col-md-3"><?php echo app_lang('note'); ?></label>
    <div class=" col-md-9">
        <?php
        echo form_textarea(array(
            "id" => "note",
            "name" => "note",
            "value" => $model_info->note ? process_images_from_content($model_info->note, false) : "",
            "class" => "form-control",
            "placeholder" => app_lang('note'),
            "data-rich-text-editor" => true
        ));
        ?>
    </div>
</div>