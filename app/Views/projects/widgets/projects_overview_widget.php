<?php
$total_points = $projects_info->total_points;
$completed_points = $projects_info->completed_points;

$progress = $total_points ? round(($completed_points / $total_points) * 100) : 0;
?>
<div class="card bg-white">
    <div class="card-header">
        <i data-feather="grid" class="icon-16"></i> &nbsp;<?php echo app_lang("projects_overview"); ?>
    </div>
    <div class="rounded-bottom pt-2">
        <div class="box">
            <div class="box-content">
                <a href="<?php echo get_uri('projects/all_projects/open'); ?>" class="text-default">
                    <div class="pt-3 pb10 text-center">
                        <div class="b-r">
                            <h4 class="strong mb-1 mt-0" style="color: #01B393;"><?php echo $count_project_status->open; ?></h4>
                            <span><?php echo app_lang("open"); ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="box-content">
                <a href="<?php echo get_uri('projects/all_projects/completed'); ?>" class="text-default">
                    <div class="pt-3 pb10 text-center">
                        <div class="b-r">
                            <h4 class="strong mb-1 mt-0 text-danger"><?php echo $count_project_status->completed; ?></h4>
                            <span><?php echo app_lang("completed"); ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="box-content">
                <a href="<?php echo get_uri('projects/all_projects/hold'); ?>" class="text-default">
                    <div class="pt-3 pb10 text-center">
                        <div>
                            <h4 class="strong mb-1 mt-0 text-warning"><?php echo $count_project_status->hold; ?></h4>
                            <span><?php echo app_lang("hold"); ?></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="container project-overview-widget">
            <div class="progress-outline">
                <div class="progress mt5 m-auto position-relative">
                    <div class="progress-bar bg-orange text-default" role="progressbar" style="width:<?php echo $progress; ?>%;" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                        <span class="justify-content-center d-flex position-absolute w-100"><?php echo app_lang("progression"); ?> <?php echo $progress; ?>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>