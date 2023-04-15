<div class="table-responsive">
    <table id="task-checklist-group-table" class="display no-hover" cellspacing="0" width="100%">         
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $("#task-checklist-group-table").appTable({
            source: '<?php echo_uri("checklist_groups/list_data") ?>',
            columns: [
                {title: "<?php echo app_lang("title"); ?>"},
                {title: "<?php echo app_lang("checklists"); ?>"},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100"}
            ]
        });
    });
</script>