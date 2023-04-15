<?php

namespace App\Models;

class Custom_field_values_model extends Crud_model {

    protected $table = null;
    private $Custom_fields_model;

    function __construct() {
        $this->Custom_fields_model = model("App\Models\Custom_fields_model");
        $this->table = 'custom_field_values';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $custom_field_values_table = $this->db->prefixTable('custom_field_values');
        $custom_fields_table = $this->db->prefixTable('custom_fields');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $custom_fields_table.id=$id";
        }


        $related_to_type = $this->_get_clean_value($options, "related_to_type");
        if ($related_to_type) {
            $where .= " AND $custom_field_values_table.related_to_type='$related_to_type'";
        }

        $related_to_id = $this->_get_clean_value($options, "related_to_id");
        if ($related_to_id) {
            $where .= " AND $custom_field_values_table.related_to_id='$related_to_id'";
        }

        $show_in_invoice = $this->_get_clean_value($options, "show_in_invoice");
        if ($show_in_invoice) {
            $where .= " AND $custom_fields_table.show_in_invoice=1";
        }

        $show_in_estimate = $this->_get_clean_value($options, "show_in_estimate");
        if ($show_in_estimate) {
            $where .= " AND $custom_fields_table.show_in_estimate=1";
        }

        $show_in_contract = $this->_get_clean_value($options, "show_in_contract");
        if ($show_in_contract) {
            $where .= " AND $custom_fields_table.show_in_contract=1";
        }

        $show_in_proposal = $this->_get_clean_value($options, "show_in_proposal");
        if ($show_in_proposal) {
            $where .= " AND $custom_fields_table.show_in_proposal=1";
        }

        $show_in_order = $this->_get_clean_value($options, "show_in_order");
        if ($show_in_order) {
            $where .= " AND $custom_fields_table.show_in_order=1";
        }

        $is_admin = $this->_get_clean_value($options, "is_admin");
        $check_admin_restriction = $this->_get_clean_value($options, "check_admin_restriction");
        if ($check_admin_restriction && !$is_admin) {
            $where .= " AND $custom_fields_table.visible_to_admins_only=0";
        }

        $sql = "SELECT $custom_field_values_table.*,
                $custom_fields_table.title AS custom_field_title, $custom_fields_table.field_type AS custom_field_type, $custom_fields_table.sort, $custom_fields_table.example_variable_name, $custom_fields_table.show_on_kanban_card, $custom_fields_table.hide_from_clients
        FROM $custom_field_values_table
        LEFT JOIN $custom_fields_table ON $custom_fields_table.id= $custom_field_values_table.custom_field_id
        WHERE $custom_field_values_table.deleted=0 $where 
        ORDER by $custom_fields_table.sort ASC";
        return $this->db->query($sql);
    }

    private function upsert_custom_field($data, $save_to_related_type = "") {
        $custom_field_info = $this->Custom_fields_model->get_one($this->_get_clean_value($data, "custom_field_id"));

        $data = array(
            "title" => $custom_field_info->title,
            "placeholder" => $custom_field_info->placeholder,
            "options" => $custom_field_info->options,
            "field_type" => $custom_field_info->field_type,
            "related_to" => $save_to_related_type,
            "required" => $custom_field_info->required,
            "add_filter" => $custom_field_info->add_filter,
            "show_in_table" => $custom_field_info->show_in_table,
            "show_in_invoice" => $custom_field_info->show_in_invoice,
            "show_in_estimate" => $custom_field_info->show_in_estimate,
            "show_in_contract" => $custom_field_info->show_in_contract,
            "show_in_proposal" => $custom_field_info->show_in_proposal,
            "show_in_order" => $custom_field_info->show_in_order,
            "visible_to_admins_only" => $custom_field_info->visible_to_admins_only,
            "hide_from_clients" => $custom_field_info->hide_from_clients,
            "deleted" => 0
        );

        $existing = $this->Custom_fields_model->get_one_where($data);

        if ($existing->id) {
            //similar field exists, return the id
            return $existing->id;
        } else {
            //similar field not exists, create a new one and return the id
            return $this->Custom_fields_model->ci_save($data);
        }
    }

    function upsert($data, $save_to_related_type = "") {
        //check if any similar field exists for migration
        if ($save_to_related_type) {
            $new_custom_field_id = $this->upsert_custom_field($data, $save_to_related_type);
            $data["custom_field_id"] = $new_custom_field_id;
        }

        $existing = $this->get_one_where(
                array("related_to_type" => $this->_get_clean_value($data, "related_to_type"),
                    "related_to_id" => $this->_get_clean_value($data, "related_to_id"),
                    "custom_field_id" => $this->_get_clean_value($data, "custom_field_id"),
                    "deleted" => 0)
        );

        $custom_field_info = $this->Custom_fields_model->get_one($this->_get_clean_value($data, "custom_field_id"));

        $changes = array(
            "field_type" => $custom_field_info->field_type,
            "title" => $custom_field_info->title,
            "visible_to_admins_only" => $custom_field_info->visible_to_admins_only,
            "hide_from_clients" => $custom_field_info->hide_from_clients
        );

        if ($existing) {
            //update
            //return changes of existing custom field
            $save_id = $this->ci_save($data, $existing->id); //update

            if ($save_id) {
                if ($existing->value != $this->_get_clean_value($data, "value")) {
                    //updated, but has changed values
                    $changes["from"] = $existing->value;
                    $changes["to"] = $this->_get_clean_value($data, "value");
                    return array("operation" => "update", "save_id" => $save_id, "changes" => $changes);
                } else {
                    //updated but changed the default input fields for first time
                    return array("save_id" => $save_id, "changes" => $changes);
                }
            }
        } else {
            //insert
            $save_id = $this->ci_save($data); //insert
            return array("operation" => "insert", "save_id" => $save_id, "changes" => $changes);
        }
    }

}
