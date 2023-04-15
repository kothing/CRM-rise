<div id="page-content" class="clearfix">
    <?php
    load_css(array(
        "assets/css/invoice.css",
    ));
    ?>

    <div class="invoice-preview print-invoice proposal-preview print-proposal">
        <div class="invoice-preview-container proposal-preview-container bg-white mt15">
            <?php echo $proposal_preview; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("html, body").addClass("dt-print-view");
    });
</script>