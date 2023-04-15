<div id="page-content" class="page-wrapper clearfix">
    <div class="card view-container">
        <div id="announcement-dropzone" class="post-dropzone">
            <?php echo form_open(get_uri("announcements/save"), array("id" => "announcement-form", "class" => "general-form", "role" => "form")); ?>

            <div>

                <div class="page-title clearfix">
                    <?php if ($model_info->id) { ?>
                        <h1><?php echo app_lang('edit_announcement'); ?></h1>
                        <div class="title-button-group">
                            <?php echo anchor(get_uri("announcements/view/" . $model_info->id), "<i data-feather='external-link' class='icon-16'></i> " . app_lang('view'), array("class" => "btn btn-default", "title" => app_lang('view'))); ?>
                        </div>
                    <?php } else { ?>
                        <h1><?php echo app_lang('add_announcement'); ?></h1>
                    <?php } ?>
                </div>

                <div class="card-body">
                    <div class="container-fluid">
                        <input type="hidden" name="id" value="<?php echo $model_info->id; ?>" />
                        <div class="form-group">
                            <div class="row">
                                <label for="title" class="col-md-12"><?php echo app_lang('title'); ?></label>
                                <div class=" col-md-12">
                                    <?php
                                    echo form_input(array(
                                        "id" => "title",
                                        "name" => "title",
                                        "value" => $model_info->title,
                                        "class" => "form-control",
                                        "placeholder" => app_lang('title'),
                                        "autofocus" => true,
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class=" col-md-12">
                                    <?php
                                    echo form_textarea(array(
                                        "id" => "description",
                                        "name" => "description",
                                        "value" => process_images_from_content($model_info->description, false),
                                        "placeholder" => app_lang('description'),
                                        "class" => "form-control"
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix">
                            <div class="row">
                                <label for="start_date" class="col-md-2"><?php echo app_lang('start_date'); ?></label>
                                <div class="form-group col-md-4">
                                    <?php
                                    echo form_input(array(
                                        "id" => "start_date",
                                        "name" => "start_date",
                                        "value" => $model_info->start_date,
                                        "class" => "form-control",
                                        "placeholder" => "YYYY-MM-DD",
                                        "autocomplete" => "off",
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required")
                                    ));
                                    ?>
                                </div>

                                <label for="end_date" class="col-md-2"><?php echo app_lang('end_date'); ?></label>
                                <div class="form-group col-md-4">
                                    <?php
                                    echo form_input(array(
                                        "id" => "end_date",
                                        "name" => "end_date",
                                        "value" => $model_info->end_date,
                                        "class" => "form-control",
                                        "placeholder" => "YYYY-MM-DD",
                                        "autocomplete" => "off",
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                        "data-rule-greaterThanOrEqual" => "#start_date",
                                        "data-msg-greaterThanOrEqual" => app_lang("end_date_must_be_equal_or_greater_than_start_date")
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="share_with" class=" col-md-2"><?php echo app_lang('share_with'); ?></label>
                                <div class="col-md-10">
                                    <div>
                                        <?php
                                        echo form_checkbox(array(
                                            "id" => "share_with_members",
                                            "name" => "share_with_all_members",
                                            "value" => "all_members",
                                            "class" => "form-check-input",
                                                ), $model_info->share_with, (in_array("all_members", $share_with)) ? true : false);
                                        ?>
                                        <label for="share_with_members"><?php echo app_lang("all_team_members"); ?> </label>
                                    </div>

                                    <?php
                                    $has_client_group = false;
                                    $client_groups_value = "";

                                    foreach ($share_with as $share) {
                                        if (strpos($share, 'cg') !== false) {
                                            $has_client_group = true;

                                            if ($client_groups_value) {
                                                $client_groups_value .= ",";
                                            }

                                            $client_groups_value .= $share;
                                        }
                                    }
                                    ?>

                                    <div id="share_with_clients_area" class="<?php echo $has_client_group ? "hide" : ""; ?>">
                                        <div>
                                            <?php
                                            echo form_checkbox(array(
                                                "id" => "share_with_clients",
                                                "name" => "share_with_all_clients",
                                                "value" => "all_clients",
                                                "class" => "form-check-input"
                                                    ), $model_info->share_with, (in_array("all_clients", $share_with)) ? true : false);
                                            ?>

                                            <label for="share_with_clients"><?php echo app_lang("all_team_clients"); ?></label>

                                        </div>

                                    </div>


                                    <div id="share_with_specific_area" class="form-group <?php echo (in_array("all_clients", $share_with)) ? "hide" : ""; ?>">
                                        <div>
                                            <?php
                                            echo form_checkbox("share_with_specific_checkbox", "1", $has_client_group ? true : false, "id='share_with_specific_checkbox' class='form-check-input'");
                                            ?>
                                            <label for="share_with_specific_checkbox"><?php echo app_lang("specific_client_groups"); ?></label>

                                            <div class="specific_dropdown">
                                                <input type="text" value="<?php echo $client_groups_value; ?>" name="share_with_specific_client_groups" id="share_with_specific_dropdown" class="w100p validate-hidden"  data-rule-required="true" data-msg-required="<?php echo app_lang('field_required'); ?>" placeholder="<?php echo app_lang('choose_client_groups'); ?>"  />    

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label class=" col-md-2"></label>
                                    <div class="col-md-10">
                                        <?php
                                        echo view("includes/file_list", array("files" => $model_info->files));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php echo view("includes/dropzone_preview"); ?>    

                </div>
                <div class="card-footer clearfix">
                    <button class="btn btn-default upload-file-button float-start round" type="button" style="color:#7988a2"><i data-feather="camera" class='icon-16'></i> <?php echo app_lang("upload_file"); ?></button>
                    <button type="submit" class="btn btn-primary float-end"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                </div>

                <?php echo form_close(); ?>
            </div> 
        </div> 
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#announcement-form").appForm({
            isModal: false,
            onSuccess: function (response) {
                appAlert.success(response.message, {duration: 10000});
                setTimeout(function () {
                    window.location.href = response.recirect_to;
                }, 1000)

            }
        });

        setTimeout(function () {
            $("#title").focus();
        }, 200);

        initWYSIWYGEditor("#description", {
            height: 250,
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

        setDatePicker("#start_date");
        setDatePicker("#end_date");


        var uploadUrl = "<?php echo get_uri("announcements/upload_file"); ?>";
        var validationUrl = "<?php echo get_uri("announcements/validate_announcement_file"); ?>";

        var dropzone = attachDropzoneWithForm("#announcement-dropzone", uploadUrl, validationUrl);

        $("#share_with_specific_dropdown").select2({
            multiple: true,
            data: <?php echo ($groups_dropdown); ?>
        });

        toggle_specific_dropdown();

        function toggle_specific_dropdown() {
            var $element = $("#share_with_specific_checkbox:checked");
            if ($element.is(":checked") && !$("#share_with_specific_area").hasClass("hide")) {
                $("#share_with_specific_checkbox").closest("div.form-group").find(".specific_dropdown").show().find("input").addClass("validate-hidden");
            } else {
                $("#share_with_specific_checkbox").closest("div.form-group").find(".specific_dropdown").hide().find("input").removeClass("validate-hidden");
            }
        }

        //show/hide client groups area
        $("#share_with_clients").click(function () {
            if ($(this).is(":checked")) {
                $("#share_with_specific_area").addClass("hide");
            } else {
                $("#share_with_specific_area").removeClass("hide");
            }

            toggle_specific_dropdown();
        });


        //show/hide clients area
        $("#share_with_specific_checkbox").click(function () {
            if ($(this).is(":checked")) {
                $("#share_with_clients_area").addClass("hide");
            } else {
                $("#share_with_clients_area").removeClass("hide");
            }

            toggle_specific_dropdown();
        });

    });
</script>    