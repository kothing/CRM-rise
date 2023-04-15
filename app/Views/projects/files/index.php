<div class="card">
    <ul id="project-files-tabs" data-bs-toggle="ajax-tab" class="nav nav-tabs bg-white title" role="tablist">
        <li class="nav-item title-tab"><h4 class="pl15 pt10 pr15"><?php echo app_lang("files"); ?></h4></li>

        <li class="nav-item"><a class="nav-link" id="files-button" role="presentation" href="javascript:;" data-bs-target="#files"><?php echo app_lang("files"); ?></a></li>

        <?php if ($login_user->user_type === "staff") { ?>
            <li class="nav-item"><a class="nav-link" role="presentation" href="<?php echo_uri("projects/file_category/$project_id"); ?>" data-bs-target="#files-category"><?php echo app_lang('category'); ?></a></li>
        <?php } ?>

        <div class="tab-title clearfix no-border">
            <div class="title-button-group">

                <?php echo js_anchor("<i data-feather='check-square' class='icon-16'></i> <span id='btn-text-content'>" . app_lang("select_all") . "</span>", array("title" => app_lang("select_all"), "id" => "select-un-select-all-file-btn", "class" => "btn btn-default hide")); ?>
                <?php echo anchor("", "<i data-feather='download' class='icon-16'></i> " . app_lang("download"), array("title" => app_lang("download"), "id" => "download-multiple-file-btn", "class" => "btn btn-default hide")); ?>
                <?php echo anchor("", "<i data-feather='x' class='icon-16'></i> " . app_lang("delete"), array("title" => app_lang("delete"), "id" => "delete-multiple-file-btn", "class" => "btn btn-default hide")); ?>

                <?php
                if ($can_add_files) {
                    echo modal_anchor(get_uri("projects/file_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_files'), array("class" => "btn btn-default", "title" => app_lang('add_files'), "data-post-project_id" => $project_id, "id" => "file_or_category_add_button"));
                }
                ?>
            </div>
        </div>

    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane fade" id="files">
            <div class="table-responsive">
                <table id="project-file-table" class="display" width="100%">            
                </table>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane fade" id="files-category"></div>
    </div>

</div>


<script type="text/javascript">
    $(document).ready(function () {

        //we have to add values of selected files for multiple download
        var fields = [];

        $('body').on('click', '[data-act=download-multiple-file-checkbox]', function () {

            var checkbox = $(this).find("span"),
                    file_id = $(this).attr("data-id");

            checkbox.addClass("inline-loader");

            //there are two operation
            if ($.inArray(file_id, fields) !== -1) {
                //if there is already added the file to download list
                var index = fields.indexOf(file_id);
                fields.splice(index, 1);
                checkbox.removeClass("checkbox-checked");
            } else {
                //if it's new item to add to download list
                fields.push(file_id);
                checkbox.addClass("checkbox-checked");
            }

            checkbox.removeClass("inline-loader");

            var serializeOfArray = fields.join("-");

            $("#download-multiple-file-btn").attr("href", "<?php echo_uri("projects/download_multiple_files/"); ?>" + serializeOfArray);
            $("#delete-multiple-file-btn").attr("href", "<?php echo_uri("projects/delete_multiple_files/"); ?>" + serializeOfArray);

            if (fields.length) {
                $("#download-multiple-file-btn").removeClass("hide");
                $("#delete-multiple-file-btn").removeClass("hide");
                $("#select-un-select-all-file-btn").removeClass("hide");
            } else {
                $("#download-multiple-file-btn").addClass("hide");
                $("#delete-multiple-file-btn").addClass("hide");
                $("#select-un-select-all-file-btn").addClass("hide");
            }

        });

        //trigger download operation for multiple download
        $("#download-multiple-file-btn").click(function () {
            $(this).addClass("hide");
            $("#select-un-select-all-file-btn").addClass("hide");
            $("[data-act=download-multiple-file-checkbox]").find("span").removeClass("checkbox-checked");
            fields = [];
            window.location.href = $(this).attr("href"); //direct link won't work in ajax tab
        });

        //trigger delete operation for multiple delete
        $("#delete-multiple-file-btn").click(function () {
            $(this).addClass("hide");
            $("#select-un-select-all-file-btn").addClass("hide");
            $("#download-multiple-file-btn").addClass("hide");
            $("[data-act=download-multiple-file-checkbox]").find("span").removeClass("checkbox-checked");
            fields = [];
            appLoader.show();

            $.ajax({
                url: $(this).attr("href"),
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        appLoader.hide();
                        appAlert.warning(result.message, {duration: 10000});
                        $("#project-file-table").appTable({reload: true});
                    } else {
                        appAlert.error(result.message);
                    }
                }
            });
        });

        //select/un-select all files
        $("#select-un-select-all-file-btn").click(function () {
            //either it's select/un-select operation
            //removing this first is necessary
            $("[data-act=download-multiple-file-checkbox]").find("span").removeClass("checkbox-checked");
            $("#download-multiple-file-btn").attr("href", "<?php echo_uri("projects/download_multiple_files/"); ?>");
            fields = [];

            if ($(this).attr("is-selected")) {
                //un-select
                $(this).find("#btn-text-content").text("<?php echo app_lang("select_all"); ?>");
                $(this).removeAttr("is-selected");
                $("#download-multiple-file-btn").addClass("hide");
                $("#delete-multiple-file-btn").addClass("hide");
            } else {
                //select
                $(this).find("#btn-text-content").text("<?php echo app_lang("unselect_all"); ?>");
                $(this).attr("is-selected", "1");
                $("#download-multiple-file-btn").removeClass("hide");
                $("#delete-multiple-file-btn").removeClass("hide");
                $("[data-act=download-multiple-file-checkbox]").each(function () {
                    $(this).trigger("click");
                });
            }
        });

        var userType = "<?php echo $login_user->user_type; ?>",
                showUploadeBy = true;
        if (userType == "client") {
            showUploadeBy = false;
        }

        $("#project-file-table").appTable({
            source: '<?php echo_uri("projects/files_list_data/" . $project_id) ?>',
            order: [[0, "desc"]],
            filterDropdown: [
                {name: "category_id", class: "w200", options: <?php echo $file_categories_dropdown; ?>}
                , <?php echo $custom_field_filters; ?>
            ],
            columns: [
                {title: '<?php echo app_lang("id") ?>'},
                {title: '<?php echo app_lang("file") ?>'},
                {title: '<?php echo app_lang("category") ?>'},
                {title: '<?php echo app_lang("size") ?>'},
                {visible: showUploadeBy, title: '<?php echo app_lang("uploaded_by") ?>'},
                {title: '<?php echo app_lang("created_date") ?>'}
<?php echo $custom_field_headers; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w150"}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5], '<?php echo $custom_field_headers; ?>')
        });

        //change the add button attributes on changing tab panel
        var addButton = $("#file_or_category_add_button");
        $(".nav-tabs li").click(function () {
            var activeField = $(this).find("a").attr("data-bs-target");
            if (activeField === "#files") {
                addButton.attr("title", "<?php echo app_lang("add_files"); ?>");
                addButton.attr("data-title", "<?php echo app_lang("add_files"); ?>");
                addButton.attr("data-action-url", "<?php echo get_uri("projects/file_modal_form"); ?>");

                addButton.html("<?php echo "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_files'); ?>");
            } else if (activeField === "#files-category") {
                addButton.attr("title", "<?php echo app_lang("add_category"); ?>");
                addButton.attr("data-title", "<?php echo app_lang("add_category"); ?>");
                addButton.attr("data-action-url", "<?php echo get_uri("projects/file_category_modal_form"); ?>");

                addButton.html("<?php echo "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_category'); ?>");
            }

            feather.replace();
        });

    });
</script>