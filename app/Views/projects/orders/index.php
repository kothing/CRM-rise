<div class="bg-white p10 mb20">
    <i data-feather="link" class="icon-16"></i>
    <?php echo (anchor(get_uri("orders/view/" . $project_info->order_id), get_order_id($project_info->order_id))); ?>
</div>