<div class="card bg-white">
    <div class="card-header clearfix">
        <i data-feather="list" class="icon-16"></i> &nbsp;<?php echo app_lang($type); ?>
    </div>
    <div class="card-body rounded-bottom" id="<?php echo $type; ?>-widget">
        <div class="row">
            <div class="col-md-6">
                <canvas id="all-tasks-overview-chart-<?php echo $type; ?>" style="width: 100%; height: 160px;"></canvas>
            </div>
            <div class="col-md-6 pl20 <?php echo count($task_statuses) > 8 ? "" : "pt-4"; ?>">
                <?php
                foreach ($task_statuses as $task_status) {
                    ?>
                    <a href="<?php echo get_uri('projects/all_tasks/tasks_list/' . $task_status->status_id . "/0/$type"); ?>" class="text-default">
                        <div class="pb-2">
                            <div class="color-tag border-circle me-3 wh10" style="background-color: <?php echo $task_status->color; ?>;"></div><?php echo $task_status->key_name ? app_lang($task_status->key_name) : $task_status->title; ?>
                            <span class="strong float-end" style="color: <?php echo $task_status->color; ?>"><?php echo $task_status->total; ?></span>
                        </div>
                    </a>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="position-absolute" style="bottom: 15px;">
            <?php
            foreach ($task_priorities as $task_priority) {
                ?>
                <span class="me-5">
                    <a href="<?php echo get_uri('projects/all_tasks/tasks_list/0/' . $task_priority->priority_id . "/$type"); ?>" class="text-default">
                        <span title="<?php echo $task_priority->title; ?>"><i data-feather="<?php echo $task_priority->icon; ?>" class="icon-18 me-1" style="color: <?php echo $task_priority->color; ?>"></i><?php echo $task_priority->total; ?></span>
                    </a>
                </span>
                <?php
            }
            ?>
        </div>
    </div>
</div>

<?php
$task_title = array();
$task_data = array();
$task_status_color = array();
foreach ($task_statuses as $task_status) {
    $task_title[] = $task_status->key_name ? app_lang($task_status->key_name) : $task_status->title;
    $task_data[] = $task_status->total;
    $task_status_color[] = $task_status->color;
}
?>
<script type="text/javascript">
    //for task status chart
    var labels = <?php echo json_encode($task_title) ?>;
    var taskData = <?php echo json_encode($task_data) ?>;
    var taskStatuscolor = <?php echo json_encode($task_status_color) ?>;
    var allTasksOverviewChart = document.getElementById("all-tasks-overview-chart-<?php echo $type; ?>");
    new Chart(allTasksOverviewChart, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [
                {
                    data: taskData,
                    backgroundColor: taskStatuscolor,
                    borderWidth: 0
                }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutoutPercentage: 87,
            tooltips: {
                callbacks: {
                    title: function (tooltipItem, data) {
                        return data['labels'][tooltipItem[0]['index']];
                    },
                    label: function (tooltipItem, data) {
                        return "";
                    },
                    afterLabel: function (tooltipItem, data) {
                        var dataset = data['datasets'][0];
                        var percent = Math.round((dataset['data'][tooltipItem['index']] / dataset["_meta"][Object.keys(dataset["_meta"])[0]]['total']) * 100);
                        return '(' + percent + '%)';
                    }
                }
            },
            legend: {
                display: false
            },
            animation: {
                animateScale: true
            }
        }
    });

    $(document).ready(function () {
        initScrollbar('#<?php echo $type; ?>-widget', {
            setHeight: 327
        });
    });

</script>