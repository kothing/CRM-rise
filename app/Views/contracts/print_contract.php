<div id="page-content" class="clearfix">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>

    <div class="invoice-preview print-invoice contract-preview print-contract">
        <div class="invoice-preview-container contract-preview-container bg-white mt15">
            <?php echo $contract_preview; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("html, body").addClass("dt-print-view");
    });
</script>