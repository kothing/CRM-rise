<div id="estimate-sent-statistics-container">
    <?php echo view("estimates/estimate_sent_statistics_widget/widget_data"); ?>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $(".load-currency-wise-estimate-data").click(function () {
            var currencyValue = $(this).attr("data-value");

            $.ajax({
                url: "<?php echo get_uri('estimates/load_statistics_of_selected_currency') ?>" + "/" + currencyValue,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        $("#estimate-sent-statistics-container").html(result.statistics);
                    }
                }
            });
        });
    });
</script>