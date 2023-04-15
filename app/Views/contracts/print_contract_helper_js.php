<script>
    $(document).ready(function () {
        //print contract
        $("#print-contract-btn").click(function () {
            appLoader.show();

            $.ajax({
                url: "<?php echo get_uri("contract/print_contract/$contract_info->id/$contract_info->public_key") ?>",
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        document.body.innerHTML = result.print_view; //add contract's print view to the page
                        $("html").css({"overflow": "visible"});

                        setTimeout(function () {
                            window.print();
                        }, 200);
                    } else {
                        appAlert.error(result.message);
                    }

                    appLoader.hide();
                }
            });
        });

        //reload page after finishing print action
        window.onafterprint = function () {
            location.reload();
        };
    });
</script>