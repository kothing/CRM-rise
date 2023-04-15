<style type="text/css">
    .lead-info-section  .form-group {margin: 25px 15px;}
    #page-content.page-wrapper{padding: 10px !important}
    #content{margin-top: 15px !important}
</style>

<div id="page-content" class="page-wrapper clearfix">
    <div id="external-lead-form-container">

        <?php echo form_open(get_uri("collect_leads/save"), array("id" => "lead-form", "class" => "general-form", "role" => "form")); ?>
        <div class="card p15 no-border clearfix lead-info-section" style="max-width: 1000px; margin: auto;">
            <input type="hidden" name="is_embedded_form" value="1" />
            <input type="hidden" name="lead_source_id" value="<?php echo $lead_source_id ? $lead_source_id : 0; ?>" />

            <h3 class=" pl15 pr10 pb20 b-b"> <?php echo app_lang("please_submit_the_form"); ?></h3>
            <?php $hidden_fields = explode(",", get_setting("hidden_lead_fields_on_embedded_form")); ?>

            <div class="form-group">
                <label for="company_name"><?php echo app_lang('company_name'); ?>*</label>
                <div >
                    <?php
                    echo form_input(array(
                        "id" => "company_name",
                        "name" => "company_name",
                        "class" => "form-control",
                        "placeholder" => app_lang('company_name'),
                        "data-rule-required" => true,
                        "data-msg-required" => app_lang("field_required"),
                    ));
                    ?>
                </div>
            </div>

            <?php if (!in_array("first_name", $hidden_fields)) { ?>
                <div class="form-group">
                    <label for="first_name"><?php echo app_lang('first_name'); ?>*</label>
                    <div >
                        <?php
                        echo form_input(array(
                            "id" => "first_name",
                            "name" => "first_name",
                            "class" => "form-control",
                            "placeholder" => app_lang('first_name'),
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (!in_array("last_name", $hidden_fields)) { ?>
                <div class="form-group">
                    <label for="last_name"><?php echo app_lang('last_name'); ?>*</label>
                    <div >
                        <?php
                        echo form_input(array(
                            "id" => "last_name",
                            "name" => "last_name",
                            "class" => "form-control",
                            "placeholder" => app_lang('last_name'),
                            "data-rule-required" => true,
                            "data-msg-required" => app_lang("field_required"),
                        ));
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (!in_array("email", $hidden_fields)) { ?>
                <div class="form-group">
                    <label for="email"><?php echo app_lang('email'); ?></label>
                    <div >
                        <?php
                        echo form_input(array(
                            "id" => "email",
                            "name" => "email",
                            "class" => "form-control",
                            "placeholder" => app_lang('email'),
                            "autocomplete" => "off",
                            "data-rule-email" => true,
                            "data-msg-email" => app_lang("enter_valid_email"),
                        ));
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (!in_array("address", $hidden_fields)) { ?>
                <div class="form-group">
                    <label for="address"><?php echo app_lang('address'); ?></label>
                    <div>
                        <?php
                        echo form_textarea(array(
                            "id" => "address",
                            "name" => "address",
                            "class" => "form-control",
                            "placeholder" => app_lang('address')
                        ));
                        ?>

                    </div>
                </div>
            <?php } ?>

            <?php if (!in_array("city", $hidden_fields)) { ?>
                <div class="form-group">
                    <label for="city"><?php echo app_lang('city'); ?></label>
                    <div>
                        <?php
                        echo form_input(array(
                            "id" => "city",
                            "name" => "city",
                            "class" => "form-control",
                            "placeholder" => app_lang('city')
                        ));
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (!in_array("state", $hidden_fields)) { ?>
                <div class="form-group">
                    <label for="state" ><?php echo app_lang('state'); ?></label>
                    <div >
                        <?php
                        echo form_input(array(
                            "id" => "state",
                            "name" => "state",
                            "class" => "form-control",
                            "placeholder" => app_lang('state')
                        ));
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (!in_array("zip", $hidden_fields)) { ?>
                <div class="form-group">
                    <label for="zip" ><?php echo app_lang('zip'); ?></label>
                    <div >
                        <?php
                        echo form_input(array(
                            "id" => "zip",
                            "name" => "zip",
                            "class" => "form-control",
                            "placeholder" => app_lang('zip')
                        ));
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (!in_array("country", $hidden_fields)) { ?>
                <div class="form-group">
                    <label for="country"><?php echo app_lang('country'); ?></label>
                    <div>
                        <?php
                        echo form_input(array(
                            "id" => "country",
                            "name" => "country",
                            "class" => "form-control",
                            "placeholder" => app_lang('country')
                        ));
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php if (!in_array("phone", $hidden_fields)) { ?>
                <div class="form-group">
                    <label for="phone"><?php echo app_lang('phone'); ?></label>
                    <div>
                        <?php
                        echo form_input(array(
                            "id" => "phone",
                            "name" => "phone",
                            "class" => "form-control",
                            "placeholder" => app_lang('phone')
                        ));
                        ?>
                    </div>
                </div>
            <?php } ?>

            <?php echo view("custom_fields/form/prepare_context_fields", array("custom_fields" => $custom_fields, "label_column" => "", "field_column" => "")); ?> 

            <div>
                <?php echo view("signin/re_captcha"); ?>
            </div>

            <div class="p15">
                <button type="submit" class="btn btn-primary"><span data-feather="send" class="icon-16"></span> <?php echo app_lang('submit'); ?></button>
            </div>

        </div>
        <?php echo form_close(); ?>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#lead-form").appForm({
            isModal: false,
            onSubmit: function () {
                appLoader.show();
                $("#lead-form").find('[type="submit"]').attr('disabled', 'disabled');
            },
            onSuccess: function (result) {
                appLoader.hide();
                $("#external-lead-form-container").html("");
                appAlert.success(result.message, {container: "#external-lead-form-container", animate: false});
                $('.scrollable-page').scrollTop(0); //scroll to top
            }
        });

        setTimeout(function () {
            $("#title").focus();
        }, 200);

        $("#lead-form .select2").select2();
    });
</script>