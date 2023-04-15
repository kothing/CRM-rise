<?php
$settings_menu = array(
    "app_settings" => array(
        array("name" => "general", "url" => "settings/general"),
        array("name" => "localization", "url" => "settings/localization"),
        array("name" => "email", "url" => "settings/email"),
        array("name" => "email_templates", "url" => "email_templates"),
        array("name" => "modules", "url" => "settings/modules"),
        array("name" => "left_menu", "url" => "left_menus"),
        array("name" => "footer", "url" => "settings/footer"),
        array("name" => "notifications", "url" => "settings/notifications"),
        array("name" => "integration", "url" => "settings/integration"),
        array("name" => "cron_job", "url" => "settings/cron_job"),
        array("name" => "updates", "url" => "Updates"),
    ),
    "access_permission" => array(),
    "client_portal" => array(
        array("name" => "client_permissions", "url" => "settings/client_permissions"),
        array("name" => "dashboard", "url" => "dashboard/client_default_dashboard"),
        array("name" => "client_left_menu", "url" => "left_menus/index/client_default"),
        array("name" => "client_projects", "url" => "settings/client_projects"),
    ),
    "sales_and_prospects" => array(
        array("name" => "company", "url" => "company")
    ),
    "setup" => array(
        array("name" => "custom_fields", "url" => "custom_fields"),
        array("name" => "client_groups", "url" => "client_groups"),
        array("name" => "tasks", "url" => "task_status"),
    ),
);

//restricted settings
if ($login_user->is_admin || (get_array_value($login_user->permissions, "can_manage_all_kinds_of_settings") && get_array_value($login_user->permissions, "can_manage_user_role_and_permissions"))) {
    $settings_menu["access_permission"] = array(
        array("name" => "roles", "url" => "roles"),
        array("name" => "user_roles", "url" => "roles/user_roles")
    );
}

$settings_menu["access_permission"][] = array("name" => "team", "url" => "team");

if (get_setting("module_attendance") == "1") {
    $settings_menu["access_permission"][] = array("name" => "ip_restriction", "url" => "settings/ip_restriction");
}

$settings_menu["sales_and_prospects"][] = array("name" => "item_categories", "url" => "item_categories");

if (get_setting("module_invoice") == "1") {
    $settings_menu["sales_and_prospects"][] = array("name" => "invoices", "url" => "settings/invoices");
}

if (get_setting("module_order") == "1") {
    $settings_menu["sales_and_prospects"][] = array("name" => "orders", "url" => "settings/orders");
}

if (get_setting("module_estimate") == "1") {
    $settings_menu["sales_and_prospects"][] = array("name" => "estimates", "url" => "settings/estimates");
}

if (get_setting("module_proposal") == "1") {
    $settings_menu["sales_and_prospects"][] = array("name" => "proposals", "url" => "settings/proposals");
}

if (get_setting("module_contract") == "1") {
    $settings_menu["sales_and_prospects"][] = array("name" => "contracts", "url" => "settings/contracts");
}

$settings_menu["sales_and_prospects"][] = array("name" => "taxes", "url" => "taxes");

$settings_menu["sales_and_prospects"][] = array("name" => "payment_methods", "url" => "payment_methods");

if (get_setting("module_subscription") == "1") {
    $settings_menu["sales_and_prospects"][] = array("name" => "subscriptions", "url" => "settings/subscriptions");
}

$settings_menu["setup"][] = array("name" => "projects", "url" => "settings/projects");

if (get_setting("module_project_timesheet") == "1") {
    $settings_menu["setup"][] = array("name" => "timesheets", "url" => "settings/timesheets");
}

if (get_setting("module_event") == "1") {
    $settings_menu["setup"][] = array("name" => "events", "url" => "settings/events");
}


if (get_setting("module_expense") == "1") {
    $settings_menu["setup"][] = array("name" => "expense_categories", "url" => "expense_categories");
}

if (get_setting("module_leave") == "1") {
    $settings_menu["setup"][] = array("name" => "leave_types", "url" => "leave_types");
}

if (get_setting("module_ticket") == "1") {
    $settings_menu["setup"][] = array("name" => "tickets", "url" => "ticket_types");
}

if (get_setting("module_lead") == "1") {
    $settings_menu["setup"][] = array("name" => "leads", "url" => "lead_status");
}

$settings_menu["setup"][] = array("name" => "gdpr", "url" => "settings/gdpr");
$settings_menu["setup"][] = array("name" => "pages", "url" => "pages");

if ($login_user->is_admin) {
    $settings_menu["plugins"][] = array("name" => "all_plugins", "url" => "Plugins");
}

$settings_menu = app_hooks()->apply_filters('app_filter_admin_settings_menu', $settings_menu);
?>

<ul class="nav nav-tabs vertical settings d-block" role="tablist">
    <?php
    foreach ($settings_menu as $key => $value) {

        //collapse the selected settings tab panel
        $collapse_in = "";
        $collapsed_class = "collapsed";
        if (in_array($active_tab, array_column($value, "name"))) {
            $collapse_in = "show";
            $collapsed_class = "";
        }
        ?>

        <div class="clearfix settings-anchor <?php echo $collapsed_class; ?>" data-bs-toggle="collapse" data-bs-target="#settings-tab-<?php echo $key; ?>">
            <?php echo app_lang($key); ?>
        </div>

        <?php
        echo "<div id='settings-tab-$key' class='collapse $collapse_in'>";
        echo "<ul class='list-group help-catagory'>";

        foreach ($value as $sub_setting) {
            $active_class = "";
            $setting_name = get_array_value($sub_setting, "name");
            $setting_url = get_array_value($sub_setting, "url");

            if ($active_tab == $setting_name) {
                $active_class = "active";
            }

            echo "<a href='" . get_uri($setting_url) . "' class='list-group-item $active_class'>" . app_lang($setting_name) . "</a>";
        }

        echo "</ul>";
        echo "</div>";
    }
    ?>

</ul>