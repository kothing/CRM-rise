<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "contracts";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="no-border clearfix ">

                <ul data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#contract-settings"><?php echo app_lang("contract_settings"); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("contract_templates"); ?>" data-bs-target="#contract-templates"><?php echo app_lang("contract_templates"); ?></a></li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="contract-settings">

                        <div class="card no-border clearfix mb0">

                            <?php echo form_open(get_uri("settings/save_contract_settings"), array("id" => "contract-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>

                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row">
                                        <label for="contract_prefix" class=" col-md-2"><?php echo app_lang('contract_prefix'); ?></label>
                                        <div class=" col-md-10">
                                            <?php
                                            echo form_input(array(
                                                "id" => "contract_prefix",
                                                "name" => "contract_prefix",
                                                "value" => get_setting("contract_prefix"),
                                                "class" => "form-control",
                                                "placeholder" => strtoupper(app_lang("contract")) . " #"
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="contract_color" class=" col-md-2"><?php echo app_lang('contract_color'); ?></label>
                                        <div class=" col-md-10">
                                            <input type="color" id="contract_color" name="contract_color" value="<?php echo get_setting("contract_color"); ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="send_contract_bcc_to" class=" col-md-2"><?php echo app_lang('send_contract_bcc_to'); ?></label>
                                        <div class=" col-md-10">
                                            <?php
                                            echo form_input(array(
                                                "id" => "send_contract_bcc_to",
                                                "name" => "send_contract_bcc_to",
                                                "value" => get_setting("send_contract_bcc_to"),
                                                "class" => "form-control",
                                                "placeholder" => app_lang("email")
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="initial_number_of_the_contract" class="col-md-2"><?php echo app_lang('initial_number_of_the_contract'); ?></label>
                                        <input type="hidden" id="last_contract_id" name="last_contract_id" value="<?php echo $last_id; ?>" />
                                        <div class="col-md-3">
                                            <?php
                                            echo form_input(array(
                                                "id" => "initial_number_of_the_contract",
                                                "name" => "initial_number_of_the_contract",
                                                "type" => "number",
                                                "value" => (get_setting("initial_number_of_the_contract") > ($last_id + 1)) ? get_setting("initial_number_of_the_contract") : ($last_id + 1),
                                                "class" => "form-control mini",
                                                "data-rule-greaterThan" => "#last_contract_id",
                                                "data-msg-greaterThan" => app_lang("the_contracts_id_must_be_larger_then_last_contract_id")
                                            ));
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="default_contract_template" class=" col-md-2"><?php echo app_lang('default_contract_template'); ?></label>
                                        <div class=" col-md-10">
                                            <?php
                                            echo form_dropdown("default_contract_template", $contract_templates_dropdown, get_setting("default_contract_template"), "class='select2 mini' id='default_contract_template'");
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="add_signature_option_on_accepting_contract" class="col-md-2"><?php echo app_lang("add_signature_option_on_accepting_contract"); ?></label>
                                        <div class="col-md-10">
                                            <?php
                                            echo form_checkbox("add_signature_option_on_accepting_contract", "1", get_setting("add_signature_option_on_accepting_contract") ? true : false, "id='add_signature_option_on_accepting_contract' class='form-check-input'");
                                            ?> 
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <label for="add_signature_option_for_team_members" class="col-md-2"><?php echo app_lang("add_signature_option_for_team_members"); ?></label>
                                        <div class="col-md-10">
                                            <?php
                                            echo form_checkbox("add_signature_option_for_team_members", "1", get_setting("add_signature_option_for_team_members") ? true : false, "id='add_signature_option_for_team_members' class='form-check-input'");
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

                    <div role="tabpanel" class="tab-pane fade" id="contract-templates"></div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php echo view("includes/cropbox"); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#contract-settings-form").appForm({
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
        $("#contract-settings-form .select2").select2();

        $(".cropbox-upload").change(function () {
            showCropBox(this);
        });
    });
</script>