<?php echo form_open(get_uri("settings/save_lead_settings"), array("id" => "lead-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
<div class="card mb0">

    <div class="card-body">
        <div class="form-group">
            <div class="row">
                <label for="can_create_lead_from_public_form" class="col-md-4"><?php echo app_lang('can_create_lead_from_public_form'); ?></label>
                <div class="col-md-8">
                    <?php
                    echo form_checkbox("can_create_lead_from_public_form", "1", get_setting("can_create_lead_from_public_form") ? true : false, "id='can_create_lead_from_public_form' class='form-check-input ml15'");
                    ?>
                    <span class="ml10 <?php echo get_setting('can_create_lead_from_public_form') ? "" : "hide"; ?>" id="lead_html_form_code">
                        <?php echo modal_anchor(get_uri("collect_leads/lead_html_form_code_modal_form"), "<i data-feather='code' class='icon-16'></i>", array("title" => app_lang('lead_html_form_code'), "class" => "edit external-tickets-embedded-code")) ?>
                    </span>
                </div>
            </div>
            <div class="row mt15 <?php echo get_setting('can_create_lead_from_public_form') ? "" : "hide"; ?>" id="after_submit_details_area">
                <label for="after_submit" class="col-md-4"><?php echo app_lang('after_submit'); ?></label>
                <div class="col-md-8">
                    <div class="ml15">
                        <div>
                            <?php
                            $after_submit_action_of_public_lead_form = get_setting('after_submit_action_of_public_lead_form');
                            echo form_radio(array(
                                "id" => "after_submit_action_of_public_lead_form_json",
                                "name" => "after_submit_action_of_public_lead_form",
                                "value" => "json",
                                "class" => "form-check-input",
                                    ), $after_submit_action_of_public_lead_form, ($after_submit_action_of_public_lead_form === "json") ? true : false);
                            ?>
                            <label for="after_submit_action_of_public_lead_form_json"><?php echo app_lang("return_json_response"); ?></label>
                        </div>
                        <div>
                            <?php
                            echo form_radio(array(
                                "id" => "after_submit_action_of_public_lead_form_text",
                                "name" => "after_submit_action_of_public_lead_form",
                                "value" => "text",
                                "class" => "form-check-input",
                                    ), $after_submit_action_of_public_lead_form, ($after_submit_action_of_public_lead_form === "text") ? true : false);
                            ?>
                            <label for="after_submit_action_of_public_lead_form_text"><?php echo app_lang("show_text_result"); ?></label>
                        </div>
                        <div class="form-group">
                            <?php
                            echo form_radio(array(
                                "id" => "after_submit_action_of_public_lead_form_redirect",
                                "name" => "after_submit_action_of_public_lead_form",
                                "value" => "redirect",
                                "class" => "form-check-input",
                                    ), $after_submit_action_of_public_lead_form, ($after_submit_action_of_public_lead_form === "redirect") ? true : false);
                            ?>
                            <label for="after_submit_action_of_public_lead_form_redirect"><?php echo app_lang("redirect_to_this_url"); ?>:</label>
                            <div class="<?php echo ($after_submit_action_of_public_lead_form === "redirect") ? "" : "hide"; ?>" id="after_submit_redirect_url">
                                <?php
                                echo form_input(array(
                                    "id" => "after_submit_action_of_public_lead_form_redirect_url",
                                    "name" => "after_submit_action_of_public_lead_form_redirect_url",
                                    "value" => get_setting("after_submit_action_of_public_lead_form_redirect_url"),
                                    "class" => "form-control",
                                    "placeholder" => app_lang("url")
                                ));
                                ?>  
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="row">
                <label for="enable_embedded_form_to_get_leads" class="col-md-4"><?php echo app_lang('enable_embedded_form_to_get_leads'); ?></label>
                <div class="col-md-8">
                    <?php
                    echo form_checkbox("enable_embedded_form_to_get_leads", "1", get_setting("enable_embedded_form_to_get_leads") ? true : false, "id='enable_embedded_form_to_get_leads' class='form-check-input ml15'");
                    ?>
                    <span class="ml10 <?php echo get_setting('enable_embedded_form_to_get_leads') ? "" : "hide"; ?>" id="external_form_embedded_url">
                        <?php echo modal_anchor(get_uri("collect_leads/embedded_code_modal_form"), "<i data-feather='code' class='icon-16'></i>", array("title" => app_lang('embed'), "class" => "edit external-tickets-embedded-code")) ?>
                    </span>
                </div>
            </div>
        </div>

    </div>

    <div class="card-footer">
        <button type="submit" class="btn btn-primary"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save'); ?></button>
    </div>

</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#lead-settings-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                appAlert.success(result.message, {duration: 10000});
            }
        });

        //show/hide external leads area
        $("#can_create_lead_from_public_form").click(function () {
            if ($(this).is(":checked")) {
                $("#lead_html_form_code").removeClass("hide");
                $("#after_submit_details_area").removeClass("hide");
            } else {
                $("#lead_html_form_code").addClass("hide");
                $("#after_submit_details_area").addClass("hide");
            }
        });

        //show/hide embedded form area
        $("#enable_embedded_form_to_get_leads").click(function () {
            if ($(this).is(":checked")) {
                $("#external_form_embedded_url").removeClass("hide");
            } else {
                $("#external_form_embedded_url").addClass("hide");
            }
        });

        //show/hide embedded form area
        $("#after_submit_action_of_public_lead_form_redirect").click(function () {
            if ($(this).is(":checked")) {
                $("#after_submit_redirect_url").removeClass("hide");
            } else {
                $("#after_submit_redirect_url").addClass("hide");
            }
        });
    });
</script>