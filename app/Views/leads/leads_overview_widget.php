<div class="card bg-white">
    <div class="card-header clearfix">
        <i data-feather="layers" class="icon-16"></i> &nbsp;<?php echo app_lang("leads_overview"); ?>
    </div>
    <div class="card-body rounded-bottom widget-box" id="leads-overview-widget">
        <div class="row">
            <div class="col-md-6">
                <canvas id="leads-overview-chart" style="width: 100%; height: 160px;"></canvas>
            </div>
            <div class="col-md-6 pl20 <?php echo count($lead_statuses) > 8 ? "" : "pt-4"; ?>">
                <?php
                foreach ($lead_statuses as $lead_status) {
                    ?>
                    <div class="pb-2">
                        <div class="color-tag border-circle me-3 wh10" style="background-color: <?php echo $lead_status->color; ?>;"></div><?php echo $lead_status->title; ?>
                        <span class="strong float-end" style="color: <?php echo $lead_status->color; ?>"><?php echo $lead_status->total; ?></span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div><?php echo app_lang("total_leads") . ": "; ?> <span class="strong"><?php echo $total_leads; ?></span></div>
            </div>
            <div class="col-md-6">
                <div><?php echo app_lang("converted_to_client") . ": "; ?> <span class="strong"><?php echo $converted_to_client; ?></span></div>
            </div>
        </div>
    </div>
</div>

<?php
$lead_status_title = array();
$lead_status_data = array();
$lead_status_color = array();
foreach ($lead_statuses as $lead_status) {
    $lead_status_title[] = $lead_status->title;
    $lead_status_data[] = $lead_status->total;
    $lead_status_color[] = $lead_status->color;
}
?>
<script>
    //for leads status chart
    var labels = <?php echo json_encode($lead_status_title) ?>;
    var leadStatusData = <?php echo json_encode($lead_status_data) ?>;
    var leadStatusColor = <?php echo json_encode($lead_status_color) ?>;
    var leadsOverviewChart = document.getElementById("leads-overview-chart");
    new Chart(leadsOverviewChart, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [
                {
                    data: leadStatusData,
                    backgroundColor: leadStatusColor,
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
        initScrollbar('#leads-overview-widget', {
            setHeight: 327
        });
    });

</script>