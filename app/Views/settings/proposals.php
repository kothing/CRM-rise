<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "proposals";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="no-border clearfix ">

                <ul data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#proposal-settings"><?php echo app_lang("proposal_settings"); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("proposal_templates"); ?>" data-bs-target="#proposal-templates"><?php echo app_lang("proposal_templates"); ?></a></li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="proposal-settings">

                        <div class="card no-border clearfix mb0">

                            <?php echo form_open(get_uri("settings/save_proposal_settings"), array("id" => "proposal-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>

                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="proposal_prefix" class=" col-md-2"><?php echo app_lang('proposal_prefix'); ?></label>
                                        <div class=" col-md-10">
                                            <?php
                                            echo form_input(array(
                                                "id" => "proposal_prefix",
                                                "name" => "proposal_prefix",
                                                "value" => get_setting("proposal_prefix"),
                                                "class" => "form-control",
                                                "placeholder" => strtoupper(app_lang("proposal")) . " #"
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="proposal_color" class=" col-md-2"><?php echo app_lang('proposal_color'); ?></label>
                                        <div class=" col-md-10">
                                            <input type="color" id="proposal_color" name="proposal_color" value="<?php echo get_setting("proposal_color"); ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="send_proposal_bcc_to" class=" col-md-2"><?php echo app_lang('send_proposal_bcc_to'); ?></label>
                                        <div class=" col-md-10">
                                            <?php
                                            echo form_input(array(
                                                "id" => "send_proposal_bcc_to",
                                                "name" => "send_proposal_bcc_to",
                                                "value" => get_setting("send_proposal_bcc_to"),
                                                "class" => "form-control",
                                                "placeholder" => app_lang("email")
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="initial_number_of_the_proposal" class="col-md-2"><?php echo app_lang('initial_number_of_the_proposal'); ?></label>
                                        <input type="hidden" id="last_proposal_id" name="last_proposal_id" value="<?php echo $last_id; ?>" />
                                        <div class="col-md-3">
                                            <?php
                                            echo form_input(array(
                                                "id" => "initial_number_of_the_proposal",
                                                "name" => "initial_number_of_the_proposal",
                                                "type" => "number",
                                                "value" => (get_setting("initial_number_of_the_proposal") > ($last_id + 1)) ? get_setting("initial_number_of_the_proposal") : ($last_id + 1),
                                                "class" => "form-control mini",
                                                "data-rule-greaterThan" => "#last_proposal_id",
                                                "data-msg-greaterThan" => app_lang("the_proposals_id_must_be_larger_then_last_proposal_id")
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="default_proposal_template" class=" col-md-2"><?php echo app_lang('default_proposal_template'); ?></label>
                                        <div class=" col-md-10">
                                            <?php
                                            echo form_dropdown("default_proposal_template", $proposal_templates_dropdown, get_setting("default_proposal_template"), "class='select2 mini' id='default_proposal_template'");
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="add_signature_option_on_accepting_proposal" class="col-md-2"><?php echo app_lang("add_signature_option_on_accepting_proposal"); ?></label>
                                        <div class="col-md-10">
                                            <?php
                                            echo form_checkbox("add_signature_option_on_accepting_proposal", "1", get_setting("add_signature_option_on_accepting_proposal") ? true : false, "id='add_signature_option_on_accepting_proposal' class='form-check-input'");
                                            ?> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary"><span data-feather='check-circle' class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                            </div>
                        </div>
                    </div>

                    <div role="tabpanel" class="tab-pane fade" id="proposal-templates"></div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php echo view("includes/cropbox"); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#proposal-settings-form").appForm({
            isModal: false,
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    appAlert.error(result.message);
                }

                if (result.reload_page) {
                    location.reload();
                }
            }
        });
        $("#proposal-settings-form .select2").select2();

        $(".cropbox-upload").change(function () {
            showCropBox(this);
        });
    });
</script>