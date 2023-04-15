<div class="card bg-white">
    <div class="card-header">
        <i data-feather="life-buoy" class="icon-16"></i>&nbsp;<?php echo app_lang("ticket_status"); ?>
    </div>
    <div class="card-body rounded-bottom p20" id="ticket-status-widget">
        <div class="row">
            <div class="col-md-6 col b-r-2 ps-4 pe-4">
                <a href="<?php echo get_uri('tickets/index/open'); ?>" class="text-default ">
                    <div class="pb-2">
                        <div class="color-tag border-circle me-3 wh10" style="background-color: #DEA701;"></div><?php echo app_lang("new"); ?>
                        <span class="strong float-end"><?php echo $new; ?></span>
                    </div>
                </a>
                <a href="<?php echo get_uri('tickets/index/open'); ?>" class="text-default ">
                    <div class="pb-2">
                        <div class="color-tag border-circle me-3 wh10" style="background-color: #F4325B;"></div><?php echo app_lang("open"); ?>
                        <span class="strong float-end"><?php echo $open; ?></span>
                    </div>
                </a>
                <a href="<?php echo get_uri('tickets/index/closed'); ?>" class="text-default ">
                    <div class="pb-2">
                        <div class="color-tag border-circle me-3 wh10" style="background-color: #485ABD;"></div><?php echo app_lang("closed"); ?>
                        <span class="strong float-end"><?php echo $closed; ?></span>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col ps-4 pe-4">
                <?php
                foreach ($tickets_info as $ticket_info) {
                    ?>
                    <a href="<?php echo get_uri('tickets/index/open/' . $ticket_info->ticket_type_id); ?>" class="text-default">
                        <div class="pb-2 clearfix">
                            <div class="float-start w-75 text-truncate"><?php echo $ticket_info->ticket_type_title; ?></div>
                            <span class="strong float-end text-danger"><?php echo $ticket_info->total; ?></span>
                        </div>
                    </a>
                    <?php
                }
                ?>
            </div>
        </div>

        <div class="bottom-25 position-absolute w90p">
            <div class="pb-3 ps-3"><?php echo app_lang("new_tickets_in_last_30_days"); ?></div>
            <div>
                <canvas id="ticket-status-chart" style="width: 100%; height: 100px;"></canvas>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var ticketStatusChart = document.getElementById("ticket-status-chart");

    var ticks = <?php echo $ticks; ?>;
    var tickets = <?php echo $total_tickets; ?>;

    new Chart(ticketStatusChart, {
        type: 'bar',
        data: {
            labels: ticks,
            datasets: [
                {
                    data: tickets,
                    backgroundColor: "#38B393",
                    borderWidth: 0
                }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            tooltips: {
                intersect: false,
                enabled: true
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
                            display: false
                        }
                    }]
            }
        }
    });


    $(document).ready(function () {
        initScrollbar('#ticket-status-widget', {
            setHeight: 327
        });

    });
</script>