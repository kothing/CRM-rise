<li class="nav-item dropdown hidden-xs">
    <?php echo js_anchor($login_user_company_name . " <i data-feather='chevron-down' class='icon'></i>", array("class" => "nav-link dropdown-toggle", "data-bs-toggle" => "dropdown")); ?>

    <ul class="dropdown-menu dropdown-menu-start">
        <li>
            <?php
            foreach ($clients as $client) {
                echo anchor(get_uri("clients/switch_account/$client->user_id"), $client->company_name, array("class" => "dropdown-item clearfix"));
            }
            ?>
        </li>
    </ul>
</li>