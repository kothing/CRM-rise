<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "user_roles";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="card">
                <div class="page-title clearfix">
                    <h4> <?php echo app_lang('user_roles'); ?></h4>
                </div>
                <div class="table-responsive">
                    <table id="user-roles-table" class="display" cellspacing="0" width="100%">            
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#user-roles-table").appTable({
            source: '<?php echo_uri("roles/user_role_list_data") ?>',
            radioButtons: [{text: '<?php echo app_lang("active_members") ?>', name: "status", value: "active", isChecked: true}, {text: '<?php echo app_lang("inactive_members") ?>', name: "status", value: "inactive", isChecked: false}],
            columns: [
                {title: "<?php echo app_lang("team_members") ?>"},
                {title: "<?php echo app_lang("role"); ?>"},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ],
            printColumns: [0, 1]
        });
    });
</script>