<?php if (!$view_type == "list_view") { ?>
    <div class="card rounded-bottom">
        <div class="tab-title clearfix">
            <h4><?php echo app_lang('contacts'); ?></h4>
            <div class="title-button-group">
                <?php
                if ($can_edit_clients) {
                    echo modal_anchor(get_uri("clients/invitation_modal"), "<i data-feather='mail' class='icon-16'></i> " . app_lang('send_invitation'), array("class" => "btn btn-default", "title" => app_lang('send_invitation'), "data-post-client_id" => $client_id));

                    echo modal_anchor(get_uri("clients/add_new_contact_modal_form"), "<i data-feather='plus-circle' class='icon-16'></i> " . app_lang('add_contact'), array("class" => "btn btn-default", "title" => app_lang('add_contact'), "data-post-client_id" => $client_id));
                }
                ?>
            </div>
        </div>

        <div class="table-responsive">
            <table id="contact-table" class="display" width="100%">            
            </table>
        </div>
    </div>
<?php } else { ?>
    <div class="card">
        <div class="table-responsive">
            <table id="contact-table" class="display" width="100%">            
            </table>
        </div>
    </div>
    <?php
}
?>



<script type="text/javascript">
    $(document).ready(function () {

        var showCompanyName = true;
        if ("<?php echo $client_id ?>") {
            showCompanyName = false;
        }

        var showOptions = true;
        if (!"<?php echo $can_edit_clients; ?>") {
            showOptions = false;
        }

        var quick_filters_dropdown = <?php echo view("clients/contacts/quick_filters_dropdown"); ?>;
        if (window.selectedContactQuickFilter) {
            var filterIndex = quick_filters_dropdown.findIndex(x => x.id === window.selectedContactQuickFilter);
            if ([filterIndex] > -1) {
                //match found
                quick_filters_dropdown[filterIndex].isSelected = true;
            }
        }

        $("#contact-table").appTable({
            source: '<?php echo_uri("clients/contacts_list_data/" . $client_id) ?>',
            serverSide: true,
            filterDropdown: [{name: "quick_filter", class: "w200", options: quick_filters_dropdown}, <?php echo $custom_field_filters; ?>],
            order: [[1, "asc"]],
            columns: [
                {title: '', "class": "w50 text-center"},
                {title: "<?php echo app_lang("name") ?>", "class": "w150", order_by: "first_name"},
                {visible: showCompanyName, title: "<?php echo app_lang("client_name") ?>", "class": "w150", order_by: "company_name"},
                {title: "<?php echo app_lang("job_title") ?>", "class": "w15p", order_by: "job_title"},
                {title: "<?php echo app_lang("email") ?>", "class": "w20p", order_by: "email"},
                {title: "<?php echo app_lang("phone") ?>", "class": "w100", order_by: "phone"},
                {title: 'Skype', "class": "w15p", order_by: "skype"}
<?php echo $custom_field_headers; ?>,
                {title: '<i data-feather="menu" class="icon-16"></i>', "class": "text-center option w50", visible: showOptions}
            ],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 4, 5, 6], '<?php echo $custom_field_headers; ?>')
        });
    });
</script>