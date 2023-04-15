<div class="card no-border clearfix mb0">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>

    <?php echo form_open(get_uri("proposals/save_view"), array("id" => "proposal-editor-form", "class" => "general-form", "role" => "form")); ?>
    <div class="bg-all-white pt15">

        <input type="hidden" name="id" value="<?php echo $proposal_info->id; ?>" />

        <div class="form-group mb15 pl15 pr15">
            <div class="invoice-preview proposal-preview">
                <div class="proposal-preview-container pt0 pb0">

                    <div class="clearfix pl5 pr5 pb10">
                        <?php echo modal_anchor(get_uri("proposal_templates/insert_template_modal_form"), "<i data-feather='rotate-ccw' class='icon-16'></i> " . app_lang('change_template'), array("class" => "btn btn-default float-start", "title" => app_lang('change_template'))); ?>
                        <button type="button" class="btn btn-primary ml10 float-end" id="proposal-save-and-show-btn"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save_and_show'); ?></button>
                        <button type="submit" class="btn btn-primary float-end"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                    </div>

                    <div class=" col-md-12">
                        <?php
                        echo form_textarea(array(
                            "id" => "proposal-view",
                            "name" => "view",
                            "value" => process_images_from_content($proposal_info->content, false),
                            "placeholder" => app_lang('view'),
                            "class" => "form-control"
                        ));
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="p15 pt0"><strong><?php echo app_lang("avilable_variables"); ?></strong>: <?php
                $avilable_variables = get_available_proposal_variables();
                foreach ($avilable_variables as $variable) {
                    echo "{" . $variable . "}, ";
                }
                ?></div>

    </div>
    <?php echo form_close(); ?>

</div>

<script>
    $(document).ready(function () {
        $("#proposal-editor-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function (data) {
                var view = encodeAjaxPostData(getWYSIWYGEditorHTML("#proposal-view"));
                $.each(data, function (index, obj) {
                    if (obj.name === "view") {
                        data[index]["value"] = view;
                    }
                });
            },
            onSuccess: function (response) {
                appAlert.success(response.message, {duration: 10000});
            }
        });

        initWYSIWYGEditor("#proposal-view", {
            height: 600,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['hr', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview']]
            ],
            lang: "<?php echo app_lang('language_locale_long'); ?>"
        });

        //insert proposal template
        $("body").on("click", "#proposal-template-table tr", function () {
            var id = $(this).find(".proposal_template-row").attr("data-id");
            appLoader.show({container: "#insert-template-section", css: "left:0;"});

            $.ajax({
                url: "<?php echo get_uri('proposal_templates/get_template_data') ?>" + "/" + id,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        $("#proposal-view").summernote("code", result.template);

                        //close the modal
                        $("#close-template-modal-btn").trigger("click");
                    } else {
                        appAlert.error(result.message);
                    }
                }
            });

        });
    });
</script>
