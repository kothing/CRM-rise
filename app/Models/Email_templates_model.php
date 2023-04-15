<?php

namespace App\Models;

class Email_templates_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'email_templates';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $email_templates_table = $this->db->prefixTable('email_templates');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $email_templates_table.id=$id";
        }

        $template_name = $this->_get_clean_value($options, "template_name");
        if ($template_name) {
            $where .= " AND $email_templates_table.template_name='$template_name'";
        }

        $template_type = $this->_get_clean_value($options, "template_type");
        if ($template_type) {
            $where .= " AND $email_templates_table.template_type='$template_type'";
        }

        $sql = "SELECT $email_templates_table.*
        FROM $email_templates_table
        WHERE $email_templates_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_final_template($template_name = "", $return_all = false) {
        $email_templates_table = $this->db->prefixTable('email_templates');

        $where = "";
        if (!$return_all) {
            //get default template only
            $where = " AND $email_templates_table.template_type='default' ";
        }

        $sql = "SELECT $email_templates_table.default_message, $email_templates_table.custom_message, $email_templates_table.email_subject, $email_templates_table.language,
            signature_template.custom_message AS signature_custom_message, signature_template.default_message AS signature_default_message
        FROM $email_templates_table
        LEFT JOIN $email_templates_table AS signature_template ON signature_template.template_name='signature' AND signature_template.language=$email_templates_table.language
        WHERE $email_templates_table.deleted=0 AND $email_templates_table.template_name='$template_name' $where ";
        $templates = $this->db->query($sql)->getResult();

        if ($return_all) {
            $info = array();

            foreach ($templates as $template) {

                $language = "default";
                if ($template->language) {
                    $language = $template->language;
                }

                $info["subject_" . $language] = $template->email_subject;
                $info["message_" . $language] = $template->custom_message ? $template->custom_message : $template->default_message;
                $info["signature_" . $language] = $template->signature_custom_message ? $template->signature_custom_message : $template->signature_default_message;
            }

            return $info;
        } else {
            $result = $this->db->query($sql)->getRow();

            $info = new \stdClass();
            $info->subject = $result->email_subject;
            $info->message = $result->custom_message ? $result->custom_message : $result->default_message;
            $info->signature = $result->signature_custom_message ? $result->signature_custom_message : $result->signature_default_message;
            
            return $info;
        }
    }

}
