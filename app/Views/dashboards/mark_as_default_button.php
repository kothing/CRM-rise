<?php
if ($login_user->is_admin) {
    if (($dashboard_type === "default" && get_setting("staff_default_dashboard")) || ($dashboard_type !== "default" && get_setting("staff_default_dashboard") === $dashboard_info->id)) {
        ?>
        <li role="presentation"><?php echo ajax_anchor(get_uri("dashboard/mark_as_default"), "<i data-feather='x-square' class='icon-16'></i> " . app_lang('remove_as_default'), array("class" => "delete dropdown-item", "data-post-id" => "", "data-reload-on-success" => true)); ?></li>
    <?php } else if ($dashboard_type !== "default") { ?>
        <li role="presentation"><?php echo ajax_anchor(get_uri("dashboard/mark_as_default"), "<i data-feather='monitor' class='icon-16'></i> " . app_lang('mark_as_default'), array('title' => app_lang('staff_default_dashboard_help_message'), "class" => "delete dropdown-item", "data-post-id" => $dashboard_info->id, "data-reload-on-success" => true, "data-bs-toggle" => "tooltip", "data-placement" => "left")); ?> </li>
    <?php } ?>
<?php } ?>

<script>
    $(document).ready(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>