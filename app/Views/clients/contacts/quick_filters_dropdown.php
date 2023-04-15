<?php

$quick_filters_dropdown = array(
    array("id" => "", "text" => "- " . app_lang("quick_filters") . " -"),
    array("id" => "logged_in_today", "text" => app_lang("logged_in_today")),
    array("id" => "logged_in_seven_days", "text" => app_lang("logged_in_last_seven_days"))
);
echo json_encode($quick_filters_dropdown);
?>