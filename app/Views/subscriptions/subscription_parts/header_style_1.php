<table class="header-style">
    <tr class="subscription-preview-header-row">
        <td style="width: 45%; vertical-align: top;">
        </td>
        <td class="hidden-subscription-preview-row" style="width: 20%;"></td>
        <td class="subscription-info-container subscription-header-style-one" style="width: 35%; vertical-align: top; text-align: right"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "subscription_info" => $subscription_info
            );
            echo view('subscriptions/subscription_parts/subscription_info', $data);
            ?>
        </td>
    </tr>
</table>