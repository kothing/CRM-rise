<?php
$card = "";
$widget_title = "";
$link = "";
$filter = "";
if ($widget_type == "logged_in_today") {
    $card = "bg-primary";
    $widget_title = app_lang("contacts_logged_in_today");
    $link = "contacts_logged_in_today";
    $filter = "logged_in_today";
} else if ($widget_type == "logged_in_seven_days") {
    $card = "bg-info";
    $widget_title = app_lang("contacts_logged_in_last_seven_days");
    $link = "contacts_logged_in_last_seven_days";
    $filter = "logged_in_seven_days";
}
?>

<a class="contact-widget-link" data-filter="<?php echo $filter; ?>" href="<?php echo get_uri("clients/index/clients_list#$filter"); ?>">
    <div class="card dashboard-icon-widget">
        <div class="card-body">
            <div class="widget-icon <?php echo $card; ?>">
                <i data-feather="check-square" class="icon"></i>
            </div>
            <div class="widget-details">
                <h1><?php echo $contacts_count; ?></h1>
                <span class="bg-transparent-white"><?php echo $widget_title; ?></span>
            </div>
        </div>
    </div>
</a>