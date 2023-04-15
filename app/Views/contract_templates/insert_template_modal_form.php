<div class="modal-body clearfix" id="insert-template-section">
    <div class="container-fluid">
        <div class="form-group">
            <div class="row">
                <div class="col-md-12 text-off text-danger"> <?php echo app_lang('contract_template_inserting_instruction'); ?></div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="contract-template-table" class="display no-thead clickable" cellspacing="0" width="100%">            
            </table>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button id="close-template-modal-btn" type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#contract-template-table").appTable({
            source: '<?php echo_uri("contract_templates/list_data/modal") ?>',
            order: [[0, 'desc']],
            columns: [
                {title: '<?php echo app_lang("title"); ?>'}

            ]
        });

        $("#insert-template-section .toolbar-left-top").remove();
        var $customToolbar = $("#insert-template-section .custom-toolbar");
        $customToolbar.removeClass("col-md-10").addClass("col-md-12");
        $customToolbar.find(".dataTables_filter").addClass("float-none");
        $customToolbar.find("label").addClass("contract-template-label");
        $customToolbar.find("input").addClass("contract-template-search");
    });
</script>