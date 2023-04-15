<?php

$quick_filters_dropdown = array(
    array("id" => "", "text" => "- " . app_lang("quick_filters") . " -"),
    array("id" => "has_open_projects", "text" => app_lang("has_open_projects")),
    array("id" => "has_completed_projects", "text" => app_lang("has_completed_projects")),
    array("id" => "has_any_hold_projects", "text" => app_lang("has_any_hold_projects")),
    array("id" => "has_canceled_projects", "text" => app_lang("has_canceled_projects")),
    array("id" => "has_unpaid_invoices", "text" => app_lang("has_unpaid_invoices")),
    array("id" => "has_overdue_invoices", "text" => app_lang("has_overdue_invoices")),
    array("id" => "has_partially_paid_invoices", "text" => app_lang("has_partially_paid_invoices")),
    array("id" => "has_open_estimates", "text" => app_lang("has_open_estimates")),
    array("id" => "has_accepted_estimates", "text" => app_lang("has_accepted_estimates")),
    array("id" => "has_new_estimate_requests", "text" => app_lang("has_new_estimate_requests")),
    array("id" => "has_estimate_requests_in_progress", "text" => app_lang("has_estimate_requests_in_progress")),
    array("id" => "has_open_tickets", "text" => app_lang("has_open_tickets")),
    array("id" => "has_new_orders", "text" => app_lang("has_new_orders")),
    array("id" => "has_open_proposals", "text" => app_lang("has_open_proposals")),
    array("id" => "has_accepted_proposals", "text" => app_lang("has_accepted_proposals")),
    array("id" => "has_rejected_proposals", "text" => app_lang("has_rejected_proposals"))
);
echo json_encode($quick_filters_dropdown);
?>