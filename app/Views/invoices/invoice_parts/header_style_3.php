<table class="header-style">
    <tr class="invoice-preview-header-row">
        <td class="invoice-info-container invoice-header-style-two" style="width: 40%; vertical-align: top;"><?php
            $data = array(
                "client_info" => $client_info,
                "color" => $color,
                "invoice_info" => $invoice_info
            );
            echo view('invoices/invoice_parts/bill_from', $data);
            ?>
        </td>
        <td class="hidden-invoice-preview-row" style="width: 20%;"></td>
        <td style="width: 40%; vertical-align: top; text-align: right;">
            <?php echo view('invoices/invoice_parts/invoice_info', $data); ?>
        </td>
    </tr>
    <tr>
        <?php if (get_setting("invoice_style") == "style_3") { ?>
            <td style="padding: 10px;"></td>
        <?php } else { ?>
            <td style="padding: 5px;"></td>
        <?php } ?>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td><?php
            echo view('invoices/invoice_parts/bill_to', $data);
            ?>
        </td>
        <td></td>
        <td></td>

    </tr>
</table>