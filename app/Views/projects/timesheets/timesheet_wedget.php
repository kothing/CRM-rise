<div class="card bg-white">
    <div class="card-header">
        <i data-feather="file-text" class="icon-16"></i>&nbsp; <?php echo app_lang("my_timesheet"); ?>
    </div>
    <div class="card-body rounded-bottom">
        <canvas id="timesheet-statistics-chart-my_timesheet_statistics" style="width: 100%; height: 300px;"></canvas>
    </div>
</div>

<script type="text/javascript">
    var timesheetStatisticsChart = document.getElementById("timesheet-statistics-chart-my_timesheet_statistics");

    var timesheets = <?php echo $timesheets; ?>;
    var ticks = <?php echo $ticks; ?>;

    new Chart(timesheetStatisticsChart, {
        type: 'line',
        data: {
            labels: ticks,
            datasets: [{
                    label: '<?php echo app_lang("timesheet_statistics"); ?>',
                    data: timesheets,
                    fill: true,
                    borderColor: '#00B493',
                    backgroundColor: 'rgba(50, 164, 131, 0.2)',
                    borderWidth: 2
                }]},
        options: {
            responsive: true,
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
                display: true,
                position: 'bottom',
                labels: {
                    fontColor: "#898fa9"
                }
            },
            scales: {
                xAxes: [{
                        gridLines: {
                            color: 'rgba(107, 115, 148, 0.1)'
                        },
                        ticks: {
                            fontColor: "#898fa9"
                        }
                    }],
                yAxes: [{
                        gridLines: {
                            color: 'rgba(107, 115, 148, 0.1)'
                        },
                        ticks: {
                            fontColor: "#898fa9"
                        }
                    }]
            }
        }
    });
</script>

