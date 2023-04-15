<div class="table-responsive">
    <table id="file-category-table" class="display" cellspacing="0" width="100%">         
    </table>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        var optionVisibility = false;
        if ("<?php echo $can_add_files; ?>") {
            optionVisibility = true;
        }

        $("#file-category-table").appTable({
            source: '<?php echo_uri("projects/file_category_list_data/" . $project_id) ?>',
            order: [[0, "desc"]],
            columns: [
                {title: '<?php echo app_lang("name") ?>'},
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w100", visible: optionVisibility}
            ],
            printColumns: [0],
            xlsColumns: [0]
        });
    });
</script>