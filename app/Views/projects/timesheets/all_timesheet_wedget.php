<div id="all-timesheet-widget-container">
    <div class="card bg-white">
        <div class="card-header">
            <i data-feather="file-text" class="icon-16"></i>&nbsp; <?php echo app_lang("all_timesheets"); ?>

            <div class="float-end">
                <span id="date-range-selector-all_timesheet_statistics" class="float-end"></span>
            </div>
        </div>

        <div class="card-body rounded-bottom">
            <canvas id="timesheet-statistics-chart-all_timesheet_statistics" style="width: 100%; height: 221px;"></canvas>
        </div>
        <div id="all-timesheet-users-summary" class="avatar-group">
            <?php
            foreach ($timesheet_users_result AS $user) {
                $time = convert_seconds_to_time_format($user->total_sec);
                ?>
                <div class="user-avatar avatar-30 avatar-circle" data-bs-toggle='tooltip' title='<?php echo $user->user_name . " - " . $time; ?>'>
                    <img alt="" src="<?php echo get_avatar($user->user_avatar); ?>">
                </div>
                <?php
            }
            ?>
        </div>

    </div>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        var date = {};

        //prepare timesheet statistics Chart
        prepareDashboardTimesheetChart = function () {
            appLoader.show();

            $.ajax({
                url: "<?php echo_uri("projects/timesheet_chart_data") ?>",
                data: {start_date: date.start_date, end_date: date.end_date},
                cache: false,
                type: 'POST',
                dataType: "json",
                success: function (response) {
                    appLoader.hide();
                    initDashboardTimesheetChart(response.timesheets, response.ticks);
                    $("#all-timesheet-users-summary").html(response.timesheet_users_result);
                    setTimeout(function () {
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }, 300);
                }
            });
        };

        $("#date-range-selector-all_timesheet_statistics").appDateRange({
            dateRangeType: "monthly",
            onChange: function (dateRange) {
                date = dateRange;
                prepareDashboardTimesheetChart();
            },
            onInit: function (dateRange) {
                date = dateRange;
                prepareDashboardTimesheetChart();
            }
        });

        setTimeout(function () {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }, 300);

    });

    var dashboardTimesheetStatisticsContent;

    initDashboardTimesheetChart = function (timesheets, ticks) {
        var timesheetStatisticsChart = document.getElementById("timesheet-statistics-chart-all_timesheet_statistics");

        if (dashboardTimesheetStatisticsContent) {
            dashboardTimesheetStatisticsContent.destroy();
        }

        dashboardTimesheetStatisticsContent = new Chart(timesheetStatisticsChart, {
            type: 'bar',
            data: {
                labels: ticks,
                datasets: [{
                        label: '<?php echo app_lang("timesheet_statistics"); ?>',
                        data: timesheets,
                        fill: true,
                        categoryPercentage: 0.3,
                        borderColor: '#00B493',
                        backgroundColor: '#00B493',
                        borderWidth: 2
                    }]},
            options: {
                responsive: true,
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        title: function (tooltipItem, data) {
                            return data['labels'][tooltipItem[0]['index']];
                        },
                        label: function (tooltipItem, data) {
                            return secondsToTimeFormat(data['datasets'][0]['data'][tooltipItem['index']] * 60 * 60);
                        }
                    }
                },
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                display: true
                            }
                        }],
                    yAxes: [{
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                display: true
                            }
                        }]
                }
            }
        });
    }
</script>

