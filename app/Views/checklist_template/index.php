<div class="table-responsive">
    <table id="task-checklist-template-table" class="display no-hover" cellspacing="0" width="100%">         
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#task-checklist-template-table").appTable({
            source: '<?php echo_uri("checklist_template/list_data") ?>',
            columns: [
                {title: '<?php echo app_lang("title"); ?>'},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ]
        });
    });
</script>