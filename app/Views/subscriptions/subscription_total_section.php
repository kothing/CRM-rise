<table id="subscription-item-table" class="table display dataTable text-right strong table-responsive">
    <tr>
        <td><?php echo app_lang("sub_total"); ?></td>
        <td style="width: 120px;"><?php echo to_currency($subscription_total_summary->subscription_subtotal, $subscription_total_summary->currency_symbol); ?></td>
        <?php if ($can_edit_subscriptions) { ?>
            <td style="width: 100px;"> </td>
        <?php } ?>
    </tr>

    <?php if ($subscription_total_summary->tax) { ?>
        <tr>
            <td><?php echo $subscription_total_summary->tax_name; ?></td>
            <td><?php echo to_currency($subscription_total_summary->tax, $subscription_total_summary->currency_symbol); ?></td>
            <?php if ($can_edit_subscriptions) { ?>
                <td></td>
            <?php } ?>
        </tr>
    <?php } ?>
    <?php if ($subscription_total_summary->tax2) { ?>
        <tr>
            <td><?php echo $subscription_total_summary->tax_name2; ?></td>
            <td><?php echo to_currency($subscription_total_summary->tax2, $subscription_total_summary->currency_symbol); ?></td>
            <?php if ($can_edit_subscriptions) { ?>
                <td></td>
            <?php } ?>
        </tr>
    <?php } ?>
    <tr>
        <td><?php echo app_lang("total"); ?></td>
        <td><?php echo to_currency($subscription_total_summary->balance_due, $subscription_total_summary->currency_symbol); ?></td>
        <?php if ($can_edit_subscriptions) { ?>
            <td></td>
        <?php } ?>
    </tr>
</table>