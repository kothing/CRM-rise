<?php

namespace App\Models;

class Help_articles_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'help_articles';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $help_categories_table = $this->db->prefixTable('help_categories');
        $help_articles_table = $this->db->prefixTable('help_articles');
        $article_helpful_status_table = $this->db->prefixTable('article_helpful_status');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $help_articles_table.id=$id";
        }

        $type = $this->_get_clean_value($options, "type");
        if ($type) {
            $where .= " AND $help_categories_table.type='$type'";
        }


        $only_active_categories = $this->_get_clean_value($options, "only_active_categories");
        if ($only_active_categories) {
            $where .= " AND $help_categories_table.status='active'";
        }

        $extra_select = "";
        $login_user_id = $this->_get_clean_value($options, "login_user_id");
        if ($login_user_id) {
            $extra_select = ", (SELECT count($article_helpful_status_table.id) FROM $article_helpful_status_table WHERE $article_helpful_status_table.article_id=$help_articles_table.id AND $article_helpful_status_table.deleted=0 AND $article_helpful_status_table.created_by=$login_user_id) as article_helpful_status,
                    (SELECT count($article_helpful_status_table.id) FROM $article_helpful_status_table WHERE $article_helpful_status_table.article_id=$help_articles_table.id AND $article_helpful_status_table.deleted=0 AND $article_helpful_status_table.status='yes') as helpful_status_yes,
                    (SELECT count($article_helpful_status_table.id) FROM $article_helpful_status_table WHERE $article_helpful_status_table.article_id=$help_articles_table.id AND $article_helpful_status_table.deleted=0 AND $article_helpful_status_table.status='no') as helpful_status_no";
        }

        $sql = "SELECT $help_articles_table.*, $help_categories_table.title AS category_title, $help_categories_table.type $extra_select
        FROM $help_articles_table
        LEFT JOIN $help_categories_table ON $help_categories_table.id=$help_articles_table.category_id
        WHERE $help_articles_table.deleted=0 AND $help_categories_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function get_articles_of_a_category($category_id) {
        $help_articles_table = $this->db->prefixTable('help_articles');

        $sql = "SELECT $help_articles_table.id, $help_articles_table.title
        FROM $help_articles_table
     
        WHERE $help_articles_table.deleted=0 AND $help_articles_table.status='active' AND $help_articles_table.category_id=$category_id
        ORDER BY $help_articles_table.sort";

        return $this->db->query($sql);
    }

    function increas_page_view($id) {
        $help_articles_table = $this->db->prefixTable('help_articles');

        $sql = "UPDATE $help_articles_table
        SET total_views = total_views+1 
        WHERE $help_articles_table.id=$id";

        return $this->db->query($sql);
    }

    function get_suggestions($type, $search) {
        $help_articles_table = $this->db->prefixTable('help_articles');
        $help_categories_table = $this->db->prefixTable('help_categories');

        if ($search) {
            $search = $this->db->escapeLikeString($search);
        }

        $sql = "SELECT $help_articles_table.id, $help_articles_table.title
        FROM $help_articles_table
        LEFT JOIN $help_categories_table ON $help_categories_table.id=$help_articles_table.category_id   
        WHERE $help_articles_table.deleted=0 AND $help_articles_table.status='active' AND $help_categories_table.deleted=0 AND $help_categories_table.status='active' AND $help_categories_table.type='$type'
            AND $help_articles_table.title LIKE '%$search%' ESCAPE '!'
        ORDER BY $help_articles_table.title ASC
        LIMIT 0, 10";

        $result = $this->db->query($sql)->getResult();

        $result_array = array();
        foreach ($result as $value) {
            $result_array[] = array("value" => $value->id, "label" => $value->title);
        }

        return $result_array;
    }

}
