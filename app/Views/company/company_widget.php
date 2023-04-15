<?php
$company_address = nl2br($company_info->address ? $company_info->address : "");
?>
<?php if (get_setting("invoice_style") == "style_3" && ($bill_from == "invoice" || $bill_from == "estimate" || $bill_from == "order")) { ?>
    <div style="font-size: 20px;"><b><?php echo $company_info->name; ?></b></div>
<?php } else { ?>
    <div><b><?php echo $company_info->name; ?></b></div>
<?php } ?>
<div style="line-height: 3px;"> </div>
<span class="invoice-meta text-default" style="font-size: 90%; color: #666;"><?php
    if ($company_address) {
        echo $company_address;
    }
    ?>
    <?php if ($company_info->phone) { ?>
        <br /><?php echo app_lang("phone") . ": " . $company_info->phone; ?>
    <?php } ?>
    <?php if ($company_info->email) { ?>
        <br /><?php echo app_lang("email") . ": " . $company_info->email; ?>
    <?php } ?>
    <?php if ($company_info->website) { ?>
        <br /><?php echo app_lang("website"); ?>: <a style="color:#666; text-decoration: none;" href="<?php echo $company_info->website; ?>"><?php echo $company_info->website; ?></a>
    <?php } ?>
    <?php if ($company_info->vat_number) { ?>
        <br /><?php echo app_lang("vat_number") . ": " . $company_info->vat_number; ?>
    <?php } ?>
</span>