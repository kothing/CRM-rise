<div class="mt20">
    <div class="row">
        <div class="col-md-3">
            <?php echo total_clients_widget($show_own_clients_only_user_id, $allowed_client_groups); ?>
        </div>
        <div class="col-md-3">
            <?php echo total_contacts_widget($show_own_clients_only_user_id, $allowed_client_groups); ?>
        </div>
        <div class="col-md-3">
            <?php echo client_contacts_logged_in_widget("logged_in_today", $show_own_clients_only_user_id, $allowed_client_groups); ?>
        </div>
        <div class="col-md-3">
            <?php echo client_contacts_logged_in_widget("logged_in_seven_days", $show_own_clients_only_user_id, $allowed_client_groups); ?>
        </div>
    </div>

    <?php if ($show_invoice_info) { ?>
        <div class="row">
            <div class="col-md-4">
                <?php echo client_invoices_widget("has_unpaid_invoices", $show_own_clients_only_user_id, $allowed_client_groups); ?>
            </div>

            <div class="col-md-4">
                <?php echo client_invoices_widget("has_partially_paid_invoices", $show_own_clients_only_user_id, $allowed_client_groups); ?>
            </div>

            <div class="col-md-4">
                <?php echo client_invoices_widget("has_overdue_invoices", $show_own_clients_only_user_id, $allowed_client_groups); ?>
            </div>
        </div>
    <?php } ?>

    <div class="row">
        <?php if ($show_project_info) { ?>
            <div class="col-md-6">
                <?php echo client_projects_widget($show_own_clients_only_user_id, $allowed_client_groups); ?>
            </div>
        <?php } ?>

        <?php if ($show_estimate_info) { ?>
            <div class="col-md-6">
                <?php echo client_estimates_widget($show_own_clients_only_user_id, $allowed_client_groups); ?>
            </div>
        <?php } ?>

        <div class="col-md-6">
            <?php
            if ($show_ticket_info) {
                echo clients_has_open_tickets_widget($show_own_clients_only_user_id, $allowed_client_groups);
            }
            ?>
            <?php
            if ($show_order_info) {
                echo clients_has_new_orders_widget($show_own_clients_only_user_id, $allowed_client_groups);
            }
            ?>
        </div>
        <div class="col-md-6">
            <?php
            if ($show_proposal_info) {
                echo client_proposals_widget($show_own_clients_only_user_id, $allowed_client_groups);
            }
            ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        //trigger clients tab when it's client overview page
        $('body').on('click', '.client-widget-link', function (e) {
            e.preventDefault();

            var filter = $(this).attr("data-filter");
            if (filter) {
                window.selectedClientQuickFilter = filter;
                $("[data-bs-target='#clients_list']").attr("data-reload", "1").trigger("click");
            }
        });

        //trigger contacts tab when click on contact widget
        $('body').on('click', '.contact-widget-link', function (e) {
            e.preventDefault();

            var filter = $(this).attr("data-filter");
            if (filter) {
                window.selectedContactQuickFilter = filter;
                $("[data-bs-target='#contacts']").attr("data-reload", "1").trigger("click");
            }
        });
    });
</script>