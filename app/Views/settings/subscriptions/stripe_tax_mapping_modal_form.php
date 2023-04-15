<div class="modal-body clearfix pt20">
    <div class="container-fluid">
        <div class="table-responsive">
            <table id="taxes-table" class="display" cellspacing="0" width="100%">   
            </table>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#taxes-table").appTable({
            source: '<?php echo_uri("taxes/list_data") ?>',
            hideTools: true,
            displayLength: 100,
            columns: [
                {title: '<?php echo app_lang("name"); ?>'},
                {title: '<?php echo app_lang("percentage"); ?>'},
                {title: 'Stripe <?php echo strtolower(app_lang("tax")); ?>'},
                {visible: false, searchable: false},
            ]
        });

        $('body').on('click', '[data-act=update-stripe-tax]', function () {
            $(this).appModifier({
                value: $(this).attr('data-value'),
                actionUrl: '<?php echo_uri("taxes/save_stripe_tax") ?>/' + $(this).attr('data-id'),
                select2Option: {data: <?php echo json_encode($stripe_taxes_dropdown) ?>},
                onSuccess: function (response, newValue) {
                    if (response.success) {
                        $("#taxes-table").appTable({newData: response.data, dataId: response.id});
                    }
                }
            });

            return false;
        });
    });
</script>