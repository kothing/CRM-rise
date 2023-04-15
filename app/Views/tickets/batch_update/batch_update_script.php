<script>

    //we have to add values of selected tickets for batch operation
    batchTicketIds = [];

    //hide change status button and marked-checkboxes
    function hideBatchTicketsBtn() {
        $(".batch-cancel-btn").addClass("hide");

        $(".batch-active-btn").removeClass("hide");

        $(".batch-update-btn").addClass("hide");
        $(".batch-update-btn").removeAttr("data-action-uri");

        $(".batch-update-header").remove();
        $(".td-checkbox").remove();

        batchTicketIds = [];
    }

    $(document).ready(function () {
        var $batchUpdateBtn = $(".batch-update-btn");

        //active batch operation of ticket
        $('body').on('click', '.batch-active-btn', function () {
            var dom = "<td class='td-checkbox' style='border-right: 1px solid #f2f2f2; padding: 0 !important;'><a data-act='batch-operation-ticket-checkbox'><span class='checkbox-blank'></span></a></td>";

            $("#ticket-table thead tr").prepend("<th class='batch-update-header text-center'>-</th>");
            $(".js-ticket").closest("tr").prepend(dom);
            $(this).addClass("hide");
            $(this).closest(".title-button-group").find(".batch-cancel-btn").removeClass("hide");
        });

        //cancel batch operation of ticket
        $('body').on('click', '.batch-cancel-btn', function () {
            hideBatchTicketsBtn();
            batchTicketIds = [];
        });

        $('body').on('click', '[data-act=batch-operation-ticket-checkbox]', function () {

            var checkbox = $(this).find("span"),
                    ticket_id = $(this).closest("tr").find(".js-ticket").attr("data-id"),
                    checkbox_checked_class = "checkbox-checked";

            checkbox.addClass("inline-loader");

            if ($.inArray(ticket_id, batchTicketIds) !== -1) {
                var index = batchTicketIds.indexOf(ticket_id);
                batchTicketIds.splice(index, 1);
                checkbox.removeClass(checkbox_checked_class);
            } else {
                batchTicketIds.push(ticket_id);
                checkbox.addClass(checkbox_checked_class);
            }

            checkbox.removeClass("inline-loader");

            if (batchTicketIds.length) {
                $batchUpdateBtn.removeClass("hide");
            } else {
                $batchUpdateBtn.addClass("hide");
            }

            var serializeOfArray = batchTicketIds.join("-");

            $batchUpdateBtn.attr("data-action-url", "<?php echo_uri("tickets/batch_update_modal_form/"); ?>" + serializeOfArray);
        });
    });
</script>