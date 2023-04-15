<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "orders";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <?php echo form_open(get_uri("settings/save_order_settings"), array("id" => "order-settings-form", "class" => "general-form dashed-row", "role" => "form")); ?>
            <div class="card">

                <ul data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#order-settings-tab"> <?php echo app_lang('order_settings'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("order_status"); ?>" data-bs-target="#order-status-settings-tab"><?php echo app_lang('order_status'); ?></a></li>
                    <div class="tab-title clearfix no-border">
                        <div class="title-button-group">
                            <?php echo modal_anchor(get_uri("order_status/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_order_status'), array("class" => "btn btn-default hide", "title" => app_lang('add_order_status'), "id" => "order-status-add-btn")); ?>
                        </div>
                    </div>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="order-settings-tab">
                        <div class="card-body">
                            <div class="form-group">
                                <div class="row">
                                    <label for="order_prefix" class=" col-md-2"><?php echo app_lang('order_prefix'); ?></label>
                                    <div class=" col-md-10">
                                        <?php
                                        echo form_input(array(
                                            "id" => "order_prefix",
                                            "name" => "order_prefix",
                                            "value" => get_setting("order_prefix"),
                                            "class" => "form-control",
                                            "placeholder" => strtoupper(app_lang("order")) . " #"
                                        ));
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label for="order_color" class=" col-md-2"><?php echo app_lang('order_color'); ?></label>
                                    <div class=" col-md-10">
                                        <input type="color" id="order_color" name="order_color" value="<?php echo get_setting("order_color"); ?>" />
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label for="initial_number_of_the_order" class="col-md-2"><?php echo app_lang('initial_number_of_the_order'); ?></label>
                                    <div class="col-md-3">
                                        <input type="hidden" id="last_order_id" name="last_order_id" value="<?php echo $last_id; ?>" />
                                        <?php
                                        echo form_input(array(
                                            "id" => "initial_number_of_the_order",
                                            "name" => "initial_number_of_the_order",
                                            "type" => "number",
                                            "value" => (get_setting("initial_number_of_the_order") > ($last_id + 1)) ? get_setting("initial_number_of_the_order") : ($last_id + 1),
                                            "class" => "form-control mini",
                                            "data-rule-greaterThan" => "#last_order_id",
                                            "data-msg-greaterThan" => app_lang("the_orders_id_must_be_larger_then_last_order_id")
                                        ));
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label for="order_tax" class=" col-md-2"><?php echo app_lang('tax'); ?></label>
                                    <div class="col-md-10">
                                        <?php
                                        echo form_dropdown("order_tax_id", $taxes_dropdown, array(get_setting('order_tax_id')), "class='select2 tax-select2 mini'");
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label for="order_tax" class=" col-md-2"><?php echo app_lang('second_tax'); ?></label>
                                    <div class="col-md-10">
                                        <?php
                                        echo form_dropdown("order_tax_id2", $taxes_dropdown, array(get_setting('order_tax_id2')), "class='select2 tax-select2 mini'");
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <label for="order_footer" class="col-md-2"><?php echo app_lang('order_footer') ?></label>
                                    <div class="col-md-10">
                                        <?php
                                        echo form_textarea(array(
                                            "id" => "order_footer",
                                            "name" => "order_footer",
                                            "value" => process_images_from_content(get_setting('order_footer'), false),
                                            "class" => "form-control"
                                        ));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="order-status-settings-tab"></div>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php echo view("includes/cropbox"); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#order-settings-form").appForm({
            isModal: false,
            beforeAjaxSubmit: function (data) {
                $.each(data, function (index, obj) {
                    if (obj.name === "order_footer") {
                        data[index]["value"] = encodeAjaxPostData(getWYSIWYGEditorHTML("#order_footer"));
                    }
                });
            },
            onSuccess: function (result) {
                if (result.success) {
                    appAlert.success(result.message, {duration: 10000});
                } else {
                    appAlert.error(result.message);
                }
            }
        });

        $("#order-settings-form .select2").select2();

        initWYSIWYGEditor("#order_footer", {height: 100});

        $(".cropbox-upload").change(function () {
            showCropBox(this);
        });

        //show add order status button
        $("a[data-bs-target='#order-status-settings-tab']").click(function () {
            $("#order-status-add-btn").removeClass("hide");
        });
    });
</script>