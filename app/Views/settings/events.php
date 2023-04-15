<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "events";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_event_settings"), array("id" => "event-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="card">
                <div class="card-header">
                    <h4><?php echo app_lang("event_settings"); ?></h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <div class="row">
                            <label for="enable_google_calendar_api" class=" col-md-3"><?php echo app_lang('enable_google_calendar_api'); ?> <span class="help" data-bs-toggle="tooltip" title="<?php echo app_lang('cron_job_required'); ?>"><i data-feather='help-circle' class="icon-16"></i></span></label>

                            <div class="col-md-9">
                                <?php
                                echo form_checkbox("enable_google_calendar_api", "1", get_setting("enable_google_calendar_api") ? true : false, "id='enable_google_calendar_api' class='form-check-input'");
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="clearfix integrate-with-google-calendar-details-section <?php echo get_setting("enable_google_calendar_api") ? "" : "hide" ?>">

                        <div class="form-group">
                            <div class="row">
                                <label class=" col-md-12">
                                    <?php echo app_lang("get_your_app_credentials_from_here") . " " . anchor("https://console.developers.google.com", "Google API Console", array("target" => "_blank")); ?>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label for="google_calendar_client_id" class=" col-md-3"><?php echo app_lang('google_client_id'); ?></label>
                                <div class=" col-md-9">
                                    <?php
                                    echo form_input(array(
                                        "id" => "google_calendar_client_id",
                                        "name" => "google_calendar_client_id",
                                        "value" => get_setting('google_calendar_client_id'),
                                        "class" => "form-control",
                                        "placeholder" => app_lang('google_client_id'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label for="google_calendar_client_secret" class=" col-md-3"><?php echo app_lang('google_client_secret'); ?></label>
                                <div class=" col-md-9">
                                    <?php
                                    echo form_input(array(
                                        "id" => "google_calendar_client_secret",
                                        "name" => "google_calendar_client_secret",
                                        "value" => get_setting('google_calendar_client_secret'),
                                        "class" => "form-control",
                                        "placeholder" => app_lang('google_client_secret'),
                                        "data-rule-required" => true,
                                        "data-msg-required" => app_lang("field_required"),
                                    ));
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label for="redirect_uri" class=" col-md-3"><i data-feather="alert-triangle" class="icon-16 text-warning"></i> <?php echo app_lang('remember_to_add_this_urls_in_authorized_redirect_uri'); ?></label>
                                <div class=" col-md-9">
                                    <?php
                                    echo "<pre class='mt5'>" .
                                    get_uri("google_api/save_access_token_of_calendar") . "<br />" .
                                    get_uri("google_api/save_access_token_of_own_calendar") .
                                    "</pre>"
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <label for="status" class=" col-md-3"><?php echo app_lang('status'); ?></label>
                                <div class=" col-md-9">
                                    <?php if (get_setting('google_calendar_authorized')) { ?>
                                        <span class="ml5 badge bg-success"><?php echo app_lang("authorized"); ?></span> 
                                        <span class="ml10"><i data-feather='alert-triangle' class="icon-16 text-warning"></i> <?php echo app_lang("now_every_user_can_integrate_with_their_google_calendar"); ?></span>
                                    <?php } else { ?>
                                        <span class="ml5 badge" style="background:#F9A52D;"><?php echo app_lang("unauthorized"); ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="card-footer">
                    <button id="save-button" type="submit" class="btn btn-primary <?php echo get_setting("enable_google_calendar_api") ? "hide" : "" ?>"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                    <button id="save-and-authorize-button" type="submit" class="btn btn-primary ml5 <?php echo get_setting("enable_google_calendar_api") ? "" : "hide" ?>"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save_and_authorize'); ?></button>
                </div>
            </div>

            <?php echo form_close(); ?>
        </div>

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#event-settings-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                appAlert.success(result.message, {duration: 10000});

                //if google clandar is enabled, redirect to authorization system
                if ($saveBtn.hasClass("hide")) {
                    window.location.href = "<?php echo_uri('google_api/authorize_calendar'); ?>";
                }
            }
        });

        $('[data-bs-toggle="tooltip"]').tooltip();

        var $saveAndAuthorizeBtn = $("#save-and-authorize-button"),
                $saveBtn = $("#save-button"),
                $calendarDetailsArea = $(".integrate-with-google-calendar-details-section");

        //show/hide google calendar details area
        $("#enable_google_calendar_api").click(function () {
            if ($(this).is(":checked")) {
                $saveAndAuthorizeBtn.removeClass("hide");
                $saveBtn.addClass("hide");
                $calendarDetailsArea.removeClass("hide");
            } else {
                $saveAndAuthorizeBtn.addClass("hide");
                $saveBtn.removeClass("hide");
                $calendarDetailsArea.addClass("hide");
            }
        });
    });
</script>