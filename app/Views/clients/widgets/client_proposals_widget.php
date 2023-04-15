<div class="card bg-white">
    <span class="p-4"><?php echo app_lang("proposals"); ?></span>

    <div class="card-body pt0 rounded-bottom" id="proposals-widget-container">
        <ul class="list-group list-group-flush">
            <a class="client-widget-link" data-filter="has_open_proposals" href="<?php echo get_uri("clients/index/clients_list#has_open_proposals"); ?>">
                <li class="list-group-item text-default">
                    <i data-feather="coffee" class="icon-18 me-2"></i><?php echo app_lang("clients_has_open_proposals"); ?> <span class="float-end text-warning"><?php echo $clients_has_open_proposals; ?></span>
                </li>
            </a>
            <a class="client-widget-link" data-filter="has_accepted_proposals" href="<?php echo get_uri("clients/index/clients_list#has_accepted_proposals"); ?>">
                <li class="list-group-item border-top text-default">
                    <i data-feather="check-circle" class="icon-18 me-2"></i><?php echo app_lang("clients_has_accepted_proposals"); ?> <span class="float-end text-success"><?php echo $clients_has_accepted_proposals; ?></span>
                </li>
            </a>
            <a class="client-widget-link" data-filter="has_rejected_proposals" href="<?php echo get_uri("clients/index/clients_list#has_rejected_proposals"); ?>">
                <li class="list-group-item border-top text-default">
                    <i data-feather="x-circle" class="icon-18 me-2"></i><?php echo app_lang("clients_has_rejected_proposals"); ?> <span class="float-end text-danger"><?php echo $clients_has_rejected_proposals; ?></span>
                </li>
            </a>
        </ul>

    </div>
</div>

<script>
    $(document).ready(function () {
        initScrollbar('#proposals-widget-container', {
            setHeight: 182
        });
    });
</script>