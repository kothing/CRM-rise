<?php echo form_open(get_uri("roles/save_user_role"), array("id" => "user-role-form", "class" => "general-form", "role" => "form")); ?>
<div class="modal-body clearfix">
    <div class="container-fluid">
        <input type="hidden" name="user_id" value="<?php echo $team_members_info->id; ?>" />
        <div class="p10 clearfix b-b">
            <div class="d-flex bg-white">
                <div class="flex-shrink-0">
                    <span class="avatar avatar-sm">
                        <img src="<?php echo get_avatar($team_members_info->image); ?>" alt="..." />
                    </span>
                </div>
                <div class="ps-3 w-100">
                    <div class="m0 strong">
                        <?php echo $team_members_info->first_name . " " . $team_members_info->last_name; ?>
                    </div>
                    <p><span class='badge bg-primary'><?php echo $team_members_info->job_title; ?></span> </p>
                </div>
            </div>
        </div>

        <div class="form-group mt25">
            <div class="row">
                <label for="role" class=" col-md-2"><?php echo app_lang('role'); ?></label>
                <div class=" col-md-10">
                    <?php
                    if ($login_user->is_admin && $login_user->id == $team_members_info->id) {
                        echo "<div class='ml15'>" . app_lang("admin") . "</div>";
                    } else {
                        echo form_dropdown("role", $role_dropdown, array($team_members_info->role_id), "class='select2' id='user-role'");
                        ?>
                        <div id="user-role-help-block" class="help-block ml10 mt-1 <?php echo $team_members_info->role_id === "admin" ? "" : "hide" ?>"><i data-feather="alert-triangle" class="icon-16 text-warning"></i> <?php echo app_lang("admin_user_has_all_power"); ?></div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
    <button type="submit" class="btn btn-primary"><span data-feather="check-circle" class="icon-16"></span> <?php echo app_lang('save'); ?></button>
</div>
<?php echo form_close(); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $("#user-role-form").appForm({
            onSuccess: function (result) {
                if (result.success) {
                    $("#user-roles-table").appTable({newData: result.data, dataId: result.id});
                }
            }
        });

        //show/hide asmin permission help message
        $("#user-role").change(function () {
            if ($(this).val() === "admin") {
                $("#user-role-help-block").removeClass("hide");
            } else {
                $("#user-role-help-block").addClass("hide");
            }
        });

        $("#user-role-form .select2").select2();
    });
</script>