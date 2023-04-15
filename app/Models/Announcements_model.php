<?php

namespace App\Models;

class Announcements_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'announcements';
        parent::__construct($this->table);
    }

    function get_unread_announcements($user_id, $user_type, $client_group_ids = "") {
        $announcements_table = $this->db->prefixTable('announcements');

        $now = get_my_local_time("Y-m-d");
        $where = $this->prepare_share_with_query($announcements_table, $user_type, $client_group_ids);

        $sql = "SELECT $announcements_table.*
        FROM $announcements_table
        WHERE $announcements_table.deleted=0 AND start_date<='$now' AND end_date>='$now' AND FIND_IN_SET($user_id,$announcements_table.read_by) = 0 $where";
        return $this->db->query($sql);
    }

    private function prepare_share_with_query($announcements_table, $user_type, $client_group_ids) {
        $where = "";
        if ($user_type) { //if no user type found, we'll assume the user has permission to access all
            if ($user_type === "staff") {
                $where = " AND FIND_IN_SET('all_members',$announcements_table.share_with)";
            } else {
                $client_groups_where = "";

                $client_group_ids = explode(',', $client_group_ids);
                foreach ($client_group_ids as $group_id) {
                    $client_groups_where .= " OR FIND_IN_SET('cg:$group_id', $announcements_table.share_with)";
                }

                $where = " AND (FIND_IN_SET('all_clients', $announcements_table.share_with) $client_groups_where )";
            }
        }

        return $where;
    }

    function get_details($options = array()) {
        $announcements_table = $this->db->prefixTable('announcements');
        $users_table = $this->db->prefixTable('users');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $announcements_table.id=$id";
        }

        $client_group_ids = $this->_get_clean_value($options, "client_group_ids");
        $user_type = $this->_get_clean_value($options, "user_type");
        $where .= $this->prepare_share_with_query($announcements_table, $user_type, $client_group_ids);

        $sql = "SELECT $announcements_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS created_by_user, $users_table.image AS created_by_avatar
        FROM $announcements_table
        LEFT JOIN $users_table ON $users_table.id= $announcements_table.created_by
        WHERE $announcements_table.deleted=0 $where";
        return $this->db->query($sql);
    }

    function mark_as_read($id, $user_id) {
        $id = $id ? $this->db->escapeString($id) : $id;
        $announcements_table = $this->db->prefixTable('announcements');
        $sql = "UPDATE $announcements_table SET $announcements_table.read_by = CONCAT($announcements_table.read_by,',',$user_id)
        WHERE $announcements_table.id=$id AND FIND_IN_SET($user_id,$announcements_table.read_by) = 0";
        return $this->db->query($sql);
    }

    function get_last_announcement($options = array()) {
        $announcements_table = $this->db->prefixTable('announcements');

        $where = "";
        $client_group_ids = $this->_get_clean_value($options, "client_group_ids");
        $user_type = $this->_get_clean_value($options, "user_type");
        $where .= $this->prepare_share_with_query($announcements_table, $user_type, $client_group_ids);

        $sql = "SELECT $announcements_table.id, $announcements_table.title
        FROM $announcements_table
        WHERE $announcements_table.deleted=0 $where
        ORDER BY $announcements_table.id DESC
        LIMIT 1";
        return $this->db->query($sql)->getRow();
    }

}
