<?php
$progress_members_clocked_in = 0;
$progress_members_clocked_out = 0;

if ($total_team_members) {
    $progress_members_clocked_in = round($members_clocked_in / $total_team_members * 100);
    $progress_members_clocked_out = round($members_clocked_out / $total_team_members * 100);
}
?>
<div class="card bg-white">
    <div class="card-header">
        <i data-feather="users" class="icon-16"></i> &nbsp;<?php echo app_lang("team_members_overview"); ?>
    </div>
    <div class="rounded-bottom">
        <div class="box pt-3">

            <div class="box-content">
                <a href="<?php echo get_uri('team_members/index'); ?>" class="text-default">
                    <div class="pt-3 pb-3 text-center">
                        <div class=" b-r">
                            <h3 class="mt-0 strong mb5"><?php echo $total_team_members; ?></h3>
                            <div><?php echo app_lang("team_members"); ?></div>
                        </div>
                    </div>
                </a>
            </div>


            <div class="box-content">
                <a href="<?php echo get_uri('leaves/index/all_applications'); ?>" class="text-default">
                    <div class="p-3 text-center">
                        <h3 class="mt-0 strong mb5 text-warning"><?php echo $on_leave_today; ?></h3>
                        <div><?php echo app_lang("on_leave_today"); ?></div>
                    </div>
                </a>
            </div>
        </div>
        <div class="box pb-3">
            <div class="box-content">
                <a href="<?php echo get_uri('attendance/index/members_clocked_in'); ?>" class="text-default">
                    <div class="pt-3 pb-3 text-center">
                        <div class="b-r">
                            <h3 class="mt-0 mb-1 strong text-danger"><?php echo $members_clocked_in; ?></h3>
                            <div class="progress h7 w-50 m-auto mb-1" title='<?php echo $progress_members_clocked_in; ?>%'>
                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $progress_members_clocked_in; ?>%;" aria-valuenow="<?php echo $progress_members_clocked_in; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div><?php echo app_lang("members_clocked_in"); ?></div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="box-content">
                <a href="<?php echo get_uri('attendance/index/clock_in_out'); ?>" class="text-default">
                    <div class="pt-3 pb-3 text-center">
                        <h3 class="mt-0 mb-1 strong text-primary"><?php echo $members_clocked_out; ?></h3>
                        <div class="progress h7 w-50 m-auto mb-1" title='<?php echo $progress_members_clocked_out; ?>%'>
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $progress_members_clocked_out; ?>%;" aria-valuenow="<?php echo $progress_members_clocked_out; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div><?php echo app_lang("members_clocked_out"); ?></div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>