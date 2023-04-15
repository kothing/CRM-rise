<div id="page-content" class="page-wrapper clearfix">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <?php
            $tab_view['active_tab'] = "payment_methods";
            echo view("settings/tabs", $tab_view);
            ?>
        </div>

        <div class="col-sm-9 col-lg-10">
            <div class="card">
                <div class="page-title clearfix">
                    <h4> <?php echo app_lang('payment_methods'); ?></h4>
                    <div class="title-button-group">
                        <?php echo modal_anchor(get_uri("payment_methods/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_payment_method'), array("class" => "btn btn-default", "title" => app_lang('add_payment_method'))); ?>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="payment-method-table" class="display" cellspacing="0" width="100%">            
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#payment-method-table").appTable({
            source: '<?php echo_uri("payment_methods/list_data") ?>',
            columns: [
                {visible: false, searchable: false},
                {title: '<?php echo app_lang("title"); ?>', 'bSortable': false},
                {title: '<?php echo app_lang("description"); ?>', 'bSortable': false},
                {title: '<?php echo app_lang("available_on_invoice"); ?>', 'bSortable': false},
                {title: '<?php echo app_lang("minimum_payment_amount"); ?>', 'bSortable': false},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100", 'bSortable': false}
            ],

            onInitComplete: function () {
                //apply sortable
                $("#payment-method-table").find("tbody").attr("id", "payment-method-table-sortable");
                var $selector = $("#payment-method-table-sortable");

                Sortable.create($selector[0], {
                    animation: 150,
                    chosenClass: "sortable-chosen",
                    ghostClass: "sortable-ghost",
                    onUpdate: function (e) {
                        appLoader.show();
                        //prepare sort indexes
                        var data = "";
                        $.each($selector.find(".item-row"), function (index, ele) {
                            if (data) {
                                data += ",";
                            }

                            data += $(ele).attr("data-id") + "-" + index;
                        });

                        //update sort indexes
                        $.ajax({
                            url: '<?php echo_uri("payment_methods/update_payment_method_sort_values") ?>',
                            type: "POST",
                            data: {sort_values: data},
                            success: function () {
                                appLoader.hide();
                            }
                        });
                    }
                });
            }
        });
    });
</script>