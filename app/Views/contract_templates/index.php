<div class="no-border clearfix mb0">
    <div class="mt15">
        <div class="row">
            <div class="col-md-4">
                <div id="contract_template-list-box" class="card">
                    <div class="page-title clearfix">
                        <h4> <?php echo app_lang('contract_templates'); ?></h4>
                        <div class="title-button-group">
                            <?php echo modal_anchor(get_uri("contract_templates/modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_contract_template'), array("class" => "btn btn-default", "title" => app_lang('add_contract_template'))); ?>
                        </div>
                    </div>
                    <div class="table-responsiv">
                        <table id="contract_template-table" class="display clickable no-thead b-b-only" cellspacing="0" width="100%">            
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div id="contract_template-details-section"> 
                    <div id="empty-contract_template" class="text-center p15 box card " style="min-height: 150px;">
                        <div class="box-content" style="vertical-align: middle; height: 100%"> 
                            <div><?php echo app_lang("select_a_template"); ?></div>
                            <span data-feather="code" width="6rem" height="6rem" style="color:rgba(128, 128, 128, 0.1)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script type="text/javascript">
    $(document).ready(function () {
        $("#contract_template-table").appTable({
            source: '<?php echo_uri("contract_templates/list_data") ?>',
            columns: [
                {title: '<?php echo app_lang("name"); ?>'},
                {title: '', class: 'text-center option w125'}
            ],
            hideTools: true,
            onInitComplete: function () {
                var $contract_template_list = $("#contract_template-list-box"),
                        $empty_contract_template = $("#empty-contract_template");
                if ($empty_contract_template.length && $contract_template_list.length) {
                    $empty_contract_template.height($contract_template_list.height() - 30);
                }
            },
            displayLength: 1000
        });

        /*load a message details*/
        $("body").on("click", "tr", function () {
            //don't load this message if already has selected.
            if (!$(this).hasClass("active")) {
                var contract_template_id = $(this).find(".contract_template-row").attr("data-id");
                if (contract_template_id) {
                    appLoader.show();
                    $("tr.active").removeClass("active");
                    $(this).addClass("active");
                    $.ajax({
                        url: "<?php echo get_uri("contract_templates/form"); ?>/" + contract_template_id,
                        success: function (result) {
                            appLoader.hide();
                            $("#contract_template-details-section").html(result);
                        }
                    });
                }
            }
        });
    });
</script>