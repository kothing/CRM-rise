<div id="kanban-wrapper">
    <ul id="kanban-container" class="kanban-container clearfix">

        <?php foreach ($columns as $column) { ?>
            <li class="kanban-col kanban-<?php
            echo $column->id;
            $tasks_count = get_array_value($column_tasks_count, $column->id);
            if (!$tasks_count) {
                $tasks_count = 0;
            }

            $tasks = get_array_value($tasks_list, $column->id);
            if (!$tasks) {
                $tasks = array();
            }
            ?>" >
                <div class="kanban-col-title" style="border-bottom: 3px solid <?php echo $column->color ? $column->color : "#2e4053"; ?>;"> <?php echo $column->key_name ? app_lang($column->key_name) : $column->title; ?> <span class="kanban-item-count <?php echo $column->id; ?>-task-count float-end"><?php echo $tasks_count; ?> </span></div>

                <div class="kanban-input general-form hide">
                    <?php
                    echo form_input(array(
                        "id" => "title",
                        "name" => "title",
                        "value" => "",
                        "class" => "form-control",
                        "placeholder" => app_lang('add_a_task')
                    ));
                    ?>
                </div>

                <div  id="kanban-item-list-<?php echo $column->id; ?>" class="kanban-item-list" data-status_id="<?php echo $column->id; ?>">
                    <?php
                    echo view("projects/tasks/kanban/kanban_column_items", array(
                        "tasks" => $tasks,
                        "can_edit_tasks" > $can_edit_tasks,
                        "project_id" > $project_id
                    ));
                    ?>
                </div>
            </li>
        <?php } ?>

    </ul>
</div>

<img id="move-icon" class="hide" src="<?php echo get_file_uri("assets/images/move.png"); ?>" alt="..." />

<script type="text/javascript">
    var kanbanContainerWidth = "";

    adjustViewHeightWidth = function () {

        if (!$("#kanban-container").length) {
            return false;
        }


        var totalColumns = "<?php echo $total_columns ?>";
        var columnWidth = (335 * totalColumns) + 5;

        if (columnWidth > kanbanContainerWidth) {
            $("#kanban-container").css({width: columnWidth + "px"});
        } else {
            $("#kanban-container").css({width: "100%"});
        }


        //set wrapper scroll
        if ($("#kanban-wrapper")[0].offsetWidth < $("#kanban-wrapper")[0].scrollWidth) {
            $("#kanban-wrapper").css("overflow-x", "scroll");
        } else {
            $("#kanban-wrapper").css("overflow-x", "hidden");
        }


        //set column scroll

        var columnHeight = $(window).height() - $(".kanban-item-list").offset().top - 57;
        if (isMobile()) {
            columnHeight = $(window).height() - 30;
        }

        $(".kanban-item-list").height(columnHeight);

        $(".kanban-item-list").each(function (index) {

            //set scrollbar on column... if requred
            if ($(this)[0].offsetHeight < $(this)[0].scrollHeight) {
                $(this).css("overflow-y", "scroll");
            } else {
                $(this).css("overflow-y", "hidden");
            }

        });
    };


    saveStatusAndSort = function ($item, status) {
        appLoader.show();
        adjustViewHeightWidth();

        var $prev = $item.prev(),
                $next = $item.next(),
                prevSort = 0, nextSort = 0, newSort = 0,
                step = 100000, stepDiff = 500,
                id = $item.attr("data-id"),
                project_id = $item.attr("data-project_id");

        if ($prev && $prev.attr("data-sort")) {
            prevSort = $prev.attr("data-sort") * 1;
        }

        if ($next && $next.attr("data-sort")) {
            nextSort = $next.attr("data-sort") * 1;
        }


        if (!prevSort && nextSort) {
            //item moved at the top
            newSort = nextSort - stepDiff;

        } else if (!nextSort && prevSort) {
            //item moved at the bottom
            newSort = prevSort + step;

        } else if (prevSort && nextSort) {
            //item moved inside two items
            newSort = (prevSort + nextSort) / 2;

        } else if (!prevSort && !nextSort) {
            //It's the first item of this column
            newSort = step * 100; //set a big value for 1st item
        }

        $item.attr("data-sort", newSort);


        $.ajax({
            url: '<?php echo_uri("projects/save_task_sort_and_status") ?>',
            type: "POST",
            data: {id: id, sort: newSort, status_id: status, project_id: project_id},
            success: function () {
                appLoader.hide();

                if (isMobile()) {
                    adjustViewHeightWidth();
                }
            }
        });

    };


    setLoadmoreButton = function () {
        $(".kanban-item-count").each(function () {
            var count = $(this).html();

            var $columnItems = $(this).closest(".kanban-col").find(".kanban-item-list").find("a.kanban-item");
            if (count > $columnItems.length) {
                $columnItems.closest(".kanban-item-list").addClass("js-load-more-on-scroll");
            } else {
                $columnItems.closest(".kanban-item-list").removeClass("js-load-more-on-scroll");
            }

        });
    };


    $(document).ready(function () {
        kanbanContainerWidth = $("#kanban-container").width();

        if (isMobile() && window.scrollToKanbanContent) {
            window.scrollTo(0, 220); //scroll to the content for mobile devices
            window.scrollToKanbanContent = false;
        }

        var isChrome = !!window.chrome && !!window.chrome.webstore;


<?php if ($login_user->user_type == "staff" || ($login_user->user_type == "client" && $can_edit_tasks)) { ?>
            $(".kanban-item-list").each(function (index) {
                var id = this.id;

                var options = {
                    animation: 150,
                    group: "kanban-item-list",
                    filter: ".disable-dragging",
                    cancel: ".disable-dragging",
                    onAdd: function (e, x) {
                        //moved to another column. update bothe sort and status
                        var status_id = $(e.item).closest(".kanban-item-list").attr("data-status_id");
                        saveStatusAndSort($(e.item), status_id);

                        var $countContainer = $("." + status_id + "-task-count");
                        $countContainer.html($countContainer.html().trim() * 1 + 1);
                        var $item = $(e.item);
                        setTimeout(function () {
                            $item.attr("data-status_id", status_id); //update status id in data.
                        });
                    },
                    onRemove: function (e, x) {
                        var status_id = $(e.item)[0].dataset.status_id;
                        var $countContainer = $("." + status_id + "-task-count");
                        $countContainer.html($countContainer.html().trim() * 1 - 1);
                    },
                    onUpdate: function (e) {
                        //updated sort
                        saveStatusAndSort($(e.item));
                    }
                };

                //apply only on chrome because this feature is not working perfectly in other browsers.
                if (isChrome) {
                    options.setData = function (dataTransfer, dragEl) {
                        var img = document.createElement("img");
                        img.src = $("#move-icon").attr("src");
                        img.style.opacity = 1;
                        dataTransfer.setDragImage(img, 5, 10);
                    };

                    options.ghostClass = "kanban-sortable-ghost";
                    options.chosenClass = "kanban-sortable-chosen";
                }

                Sortable.create($("#" + id)[0], options);
            });
<?php } ?>

        //add activated sub task filter class
        if ($(".custom-filter-search").val().substring(0, 1) === "#") {
            $("#kanban-container").find("[main-task-id='" + $(".custom-filter-search").val() + "']").addClass("sub-task-filter-kanban-active");
        }

        adjustViewHeightWidth();
        setLoadmoreButton();

        $('[data-bs-toggle="tooltip"]').tooltip();


        $(".kanban-item-list").scroll(function () {
            var $instance = $(this);
            var status_id = $instance.data("status_id");
            if ($instance.hasClass("js-load-more-on-scroll")) {
                var scrollTop = $instance.scrollTop();
                var columnHeight = $instance.get(0).scrollHeight - $instance.height();

                if (scrollTop > columnHeight - 200) {
                    //load more item once the scroll reach at to bootom (200px above)

                    if (!$instance.find(".kanban-item-loading").length) {

                        $instance.append("<div class='text-center kanban-item-loading'><div class='inline-loader' ></div></div>");

                        var $lastChild = $instance.find("a.kanban-item").last();

                        var filterParams = window.InstanceCollection["kanban-filters"].filterParams;

                        var postData = $.extend({}, filterParams);
                        postData.max_sort = $lastChild.data("sort") || 0;
                        postData.kanban_column_id = status_id;

                        $.ajax({
                            url: window.InstanceCollection["kanban-filters"].source,
                            type: 'POST',
                            data: postData,
                            success: function (response) {
                                $instance.find(".kanban-item-loading").remove();
                                $instance.append(response);
                                setLoadmoreButton();
                            }
                        });
                    }
                }
            }
        });

    });


    $(window).resize(function () {
        adjustViewHeightWidth();
    });



</script>

<?php echo view("projects/tasks/update_task_read_comments_status_script"); ?>