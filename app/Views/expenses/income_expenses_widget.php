<?php
$today = explode('-', get_today_date());
$current_year = get_array_value($today, 0);
?>

<div class="card bg-white <?php echo $custom_class; ?>">
    <div class="card-header clearfix">
        <i data-feather="pie-chart" class="icon-16"></i> &nbsp;<?php echo app_lang("income_vs_expenses"); ?>
    </div>
    <div class="card-body rounded-bottom">
        <div class="row">
            <div class="col-md-7">
                <canvas id="income-expense-chart" style="width: 100%; height: 165px;"></canvas>
            </div>
            <div class="col-md-5">
                <div class="mb-2"><?php echo app_lang("this_year"); ?></div>
                <div class="mb-1">
                    <div class="color-tag border-circle me-3 wh10" style="background-color: #32A483;"></div>
                    <span class="strong"><?php echo to_currency($current_year_info->income ? $current_year_info->income : 0); ?></span>
                </div>
                <div>
                    <div class="color-tag border-circle me-3 wh10" style="background-color: #E60050;"></div>
                    <span class="strong"><?php echo to_currency($current_year_info->expneses ? $current_year_info->expneses : 0); ?></span>
                </div>
                <div class="mt-4 mb-2"><?php echo app_lang("last_year"); ?></div>
                <div class="mb-1">
                    <div class="color-tag border-circle me-3 wh10" style="background-color: #32A483;"></div>
                    <span class="strong"><?php echo to_currency($previous_year_info->income ? $previous_year_info->income : 0); ?></span>
                </div>
                <div>
                    <div class="color-tag border-circle me-3 wh10" style="background-color: #E60050;"></div>
                    <span class="strong"><?php echo to_currency($previous_year_info->expneses ? $previous_year_info->expneses : 0); ?></span>
                </div>
            </div>
        </div>
        <div class="pt35 ps-3">
            <div class="pt-2"><?php echo app_lang("this_year"); ?></div>
            <canvas id="dashboard-income-vs-expenses-chart" style="width: 100%; height: 60px; margin-left: -10px;"></canvas>
        </div>
    </div>
</div>

<script type="text/javascript">

<?php if ($income || $expenses) { ?>
        var incomeExpenseChart = document.getElementById("income-expense-chart");
        new Chart(incomeExpenseChart, {
            type: 'doughnut',
            data: {
                labels: ["<?php echo app_lang("income"); ?>", "<?php echo app_lang("expenses"); ?>"],
                datasets: [
                    {
                        data: ["<?php echo $income ?>" * 1, "<?php echo $expenses ?>" * 1],
                        backgroundColor: ["#32A483", "#E60050"],
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
<?php } ?>

    //this year income vs expense chart
    var incomeExpensesChartContent;

    var initIncomeExpenseChart = function (income, expense) {
        var incomeExpensesChart = document.getElementById("dashboard-income-vs-expenses-chart");
        if (incomeExpensesChartContent) {
            incomeExpensesChartContent.destroy();
        }

        incomeExpensesChartContent = new Chart(incomeExpensesChart, {
            type: 'line',
            data: {
                labels: ["<?php echo app_lang('short_january'); ?>", "<?php echo app_lang('short_february'); ?>", "<?php echo app_lang('short_march'); ?>", "<?php echo app_lang('short_april'); ?>", "<?php echo app_lang('short_may'); ?>", "<?php echo app_lang('short_june'); ?>", "<?php echo app_lang('short_july'); ?>", "<?php echo app_lang('short_august'); ?>", "<?php echo app_lang('short_september'); ?>", "<?php echo app_lang('short_october'); ?>", "<?php echo app_lang('short_november'); ?>", "<?php echo app_lang('short_december'); ?>"],
                datasets: [{
                        label: "<?php echo app_lang('income'); ?>",
                        borderColor: '#32A483',
                        backgroundColor: 'rgba(50, 164, 131, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        data: income,
                        pointRadius: 0
                    }, {
                        label: "<?php echo app_lang('expense'); ?>",
                        borderColor: '#E60050',
                        backgroundColor: 'rgba(230, 0, 80, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        data: expense,
                        pointRadius: 0
                    }]
            },
            options: {
                responsive: true,
                tooltips: {
                    intersect: false,
                    enabled: true,
                    callbacks: {
                        title: function (tooltipItems, data) {
                            return "";
                        },
                        label: function (tooltipItems, data) {
                            if (tooltipItems) {
                                return tooltipItems.xLabel + " " + toCurrency(tooltipItems.yLabel);
                            } else {
                                return false;
                            }
                        }
                    }
                },
                legend: {
                    display: false
                },
                scales: {
                    xAxes: [{
                            display: false
                        }],
                    yAxes: [{
                            gridLines: {
                                display: false
                            },
                            ticks: {
                                display: false
                            }
                        }]
                }
            }
        });
    };

    var prepareExpensesChart = function (data) {
        var data = {};
        var project_id = "0";
        data.project_id = project_id;
        data.year = "<?php echo $current_year; ?>";

        $.ajax({
            url: "<?php echo_uri("expenses/income_vs_expenses_chart_data") ?>",
            data: data,
            cache: false,
            type: 'POST',
            dataType: "json",
            success: function (response) {
                appLoader.hide();
                initIncomeExpenseChart(response.income, response.expenses);
            }
        });
    };

    $(document).ready(function () {
        prepareExpensesChart();
    });

</script>