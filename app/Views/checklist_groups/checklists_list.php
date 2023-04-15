<div class="modal-body clearfix">
    <div class="container-fluid">
        <?php foreach ($checklists as $checklist) { ?>
            <div class='list-group-item checklist-item-row b-a rounded text-break mb10'>
                <?php echo $checklist->title ?>
            </div>
        <?php } ?>

    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span data-feather="x" class="icon-16"></span> <?php echo app_lang('close'); ?></button>
</div>
