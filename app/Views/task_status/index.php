<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "tasks";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="card">

                <ul data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
                    <li><a role="presentation" data-bs-toggle="tab" href="javascript:;" data-bs-target="#task-status-tab"> <?php echo app_lang('task_status'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("task_priority"); ?>" data-bs-target="#task-priority-tab"><?php echo app_lang('task_priority'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("settings/tasks/"); ?>" data-bs-target="#task-settings-tab"><?php echo app_lang('task_settings'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("checklist_template"); ?>" data-bs-target="#task-checklist-template-tab"><?php echo app_lang('checklist_template'); ?></a></li>
                    <li><a role="presentation" data-bs-toggle="tab" href="<?php echo_uri("checklist_groups"); ?>" data-bs-target="#task-checklist-group-tab"><?php echo app_lang('checklist_group'); ?></a></li>

                    <div class="tab-title clearfix no-border">
                        <div class="title-button-group">
                            <?php echo modal_anchor(get_uri("task_status/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task_status'), array("class" => "btn btn-default", "title" => app_lang('add_task_status'), "id" => "task-status-button")); ?>
                        </div>
                    </div>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade" id="task-status-tab">
                        <div class="table-responsive">
                            <table id="task-status-table" class="display no-thead b-t b-b-only no-hover" cellspacing="0" width="100%">         
                            </table>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane fade" id="task-priority-tab"></div>
                    <div role="tabpanel" class="tab-pane fade" id="task-settings-tab"></div>
                    <div role="tabpanel" class="tab-pane fade" id="task-checklist-template-tab"></div>
                    <div role="tabpanel" class="tab-pane fade" id="task-checklist-group-tab"></div>
                </div>

            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        $("#task-status-table").appTable({
            source: '<?php echo_uri("task_status/list_data") ?>',
            order: [[0, "asc"]],
            hideTools: true,
            displayLength: 100,
            columns: [
                {visible: false},
                {title: '<?php echo app_lang("title"); ?>'},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            onInitComplete: function () {
                //apply sortable
                $("#task-status-table").find("tbody").attr("id", "custom-field-table-sortable");
                var $selector = $("#custom-field-table-sortable");

                Sortable.create($selector[0], {
                    animation: 150,
                    chosenClass: "sortable-chosen",
                    ghostClass: "sortable-ghost",
                    onUpdate: function (e) {
                        appLoader.show();
                        //prepare sort indexes 
                        var data = "";
                        $.each($selector.find(".field-row"), function (index, ele) {
                            if (data) {
                                data += ",";
                            }

                            data += $(ele).attr("data-id") + "-" + index;
                        });

                        //update sort indexes
                        $.ajax({
                            url: '<?php echo_uri("task_status/update_field_sort_values") ?>',
                            type: "POST",
                            data: {sort_values: data},
                            success: function () {
                                appLoader.hide();
                            }
                        });
                    }
                });

            }

        });

        //change the add button attributes on changing tab panel
        var addButton = $("#task-status-button");
        $(".nav-tabs li").click(function () {
            var activeField = $(this).find("a").attr("data-bs-target");

            if (activeField === "#task-status-tab" || activeField === "#task-settings-tab") { //task status
                addButton.attr("title", "<?php echo app_lang("add_task_status"); ?>");
                addButton.attr("data-title", "<?php echo app_lang("add_task_status"); ?>");
                addButton.attr("data-action-url", "<?php echo get_uri("task_status/modal_form"); ?>");

                addButton.html("<?php echo "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task_status'); ?>");
                feather.replace();
            } else if (activeField === "#task-checklist-template-tab") { //checklist template
                addButton.attr("title", "<?php echo app_lang("add_checklist_template"); ?>");
                addButton.attr("data-title", "<?php echo app_lang("add_checklist_template"); ?>");
                addButton.attr("data-action-url", "<?php echo get_uri("checklist_template/modal_form"); ?>");

                addButton.html("<?php echo "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_checklist_template'); ?>");
                feather.replace();
            } else if (activeField === "#task-checklist-group-tab") {
                addButton.attr("title", "<?php echo app_lang("add_checklist_group"); ?>");
                addButton.attr("data-title", "<?php echo app_lang("add_checklist_group"); ?>");
                addButton.attr("data-action-url", "<?php echo get_uri("checklist_groups/modal_form"); ?>");

                addButton.html("<?php echo "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_checklist_group'); ?>");
                feather.replace();
            } else if (activeField === "#task-priority-tab") { //tasks priority
                addButton.attr("title", "<?php echo app_lang("add_task_priority"); ?>");
                addButton.attr("data-title", "<?php echo app_lang("add_task_priority"); ?>");
                addButton.attr("data-action-url", "<?php echo get_uri("task_priority/modal_form"); ?>");

                addButton.html("<?php echo "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_task_priority'); ?>");
                feather.replace();
            }
        });
    });
</script>