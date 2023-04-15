<div class="card bg-white">
    <span class="p-4"><?php echo app_lang("projects"); ?></span>

    <div class="card-body pt0 rounded-bottom" id="projects-container">
        <ul class="list-group list-group-flush">
            <a class="client-widget-link" data-filter="has_open_projects" href="<?php echo get_uri("clients/index/clients_list#has_open_projects"); ?>">
                <li class="list-group-item text-default">
                    <i data-feather="grid" class="icon-18 me-2"></i><?php echo app_lang("clients_has_open_projects"); ?> <span class="float-end text-primary"><?php echo $clients_has_open_projects; ?></span>
                </li>
            </a>
            <a class="client-widget-link" data-filter="has_completed_projects" href="<?php echo get_uri("clients/index/clients_list#has_completed_projects"); ?>">
                <li class="list-group-item border-top text-default">
                    <i data-feather="check-circle" class="icon-18 me-2"></i><?php echo app_lang("clients_has_completed_projects"); ?> <span class="float-end text-success"><?php echo $clients_has_completed_projects; ?></span>
                </li>
            </a>
            <a class="client-widget-link" data-filter="has_any_hold_projects" href="<?php echo get_uri("clients/index/clients_list#has_any_hold_projects"); ?>">
                <li class="list-group-item border-top text-default">
                    <i data-feather="pause-circle" class="icon-18 me-2"></i><?php echo app_lang("clients_has_hold_projects"); ?> <span class="float-end text-warning"><?php echo $clients_has_any_hold_projects; ?></span>
                </li>
            </a>
            <a class="client-widget-link" data-filter="has_canceled_projects" href="<?php echo get_uri("clients/index/clients_list#has_canceled_projects"); ?>">
                <li class="list-group-item border-top text-default">
                    <i data-feather="x-circle" class="icon-18 me-2"></i><?php echo app_lang("clients_has_canceled_projects"); ?> <span class="float-end text-danger"><?php echo $clients_has_canceled_projects; ?></span>
                </li>
            </a>
        </ul>
    </div>
</div>

<script>
    $(document).ready(function () {
        initScrollbar('#projects-container', {
            setHeight: 182
        });
    });
</script>