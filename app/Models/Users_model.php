<?php

namespace App\Models;

class Users_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'users';
        parent::__construct($this->table);
    }

    function authenticate($email, $password) {

        if ($email) {
            $email = $this->db->escapeString($email);
        }

        $this->db_builder->select("id,user_type,client_id,password");
        $result = $this->db_builder->getWhere(array('email' => $email, 'status' => 'active', 'deleted' => 0, 'disable_login' => 0));

        $result_count = count($result->getResult());
        if (!$result_count) {
            return false;
        }

        if ($result_count === 1) {
            $user_info = $result->getRow();
            return $this->verify_password($user_info, $password);
        } else {
            //same email on multiple client contacts
            //check with the password
            foreach ($result->getResult() as $user_info) {
                if ($this->verify_password($user_info, $password)) {
                    return true;
                }
            }
        }
    }

    private function verify_password($user_info, $password) {
        //there has two password encryption method for legacy (md5) compatibility
        //check if anyone of them is correct
        if ((strlen($user_info->password) === 60 && password_verify($password, $user_info->password)) || $user_info->password === md5($password)) {

            if ($this->_client_can_login($user_info) !== false) {
                $session = \Config\Services::session();
                $session->set('user_id', $user_info->id);

                try {
                    app_hooks()->do_action('app_hook_after_signin');
                } catch (\Exception $ex) {
                    log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
                }

                return true;
            }
        }
    }

    private function _client_can_login($user_info) {
        //check client login settings
        if ($user_info->user_type === "client" && get_setting("disable_client_login")) {
            return false;
        } else if ($user_info->user_type === "client") {
            //user can't be loged in if client has deleted
            $clients_table = $this->db->prefixTable('clients');

            $sql = "SELECT $clients_table.id
                    FROM $clients_table
                    WHERE $clients_table.id = $user_info->client_id AND $clients_table.deleted=0";
            $client_result = $this->db->query($sql);

            if ($client_result->resultID->num_rows !== 1) {
                return false;
            }
        }
    }

    function login_user_id() {
        $session = \Config\Services::session();
        return $session->has("user_id") ? $session->get("user_id") : "";
    }

    function sign_out() {
        try {
            app_hooks()->do_action('app_hook_before_signout');
        } catch (\Exception $ex) {
            log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
        }

        $session = \Config\Services::session();
        $session->destroy();
        app_redirect('signin');
    }

    function get_details($options = array()) {
        $users_table = $this->db->prefixTable('users');
        $team_member_job_info_table = $this->db->prefixTable('team_member_job_info');
        $clients_table = $this->db->prefixTable('clients');
        $roles_table = $this->db->prefixTable('roles');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        $status = $this->_get_clean_value($options, "status");
        $user_type = $this->_get_clean_value($options, "user_type");
        $client_id = $this->_get_clean_value($options, "client_id");
        $exclude_user_id = $this->_get_clean_value($options, "exclude_user_id");
        $first_name = $this->_get_clean_value($options, "first_name");
        $last_name = $this->_get_clean_value($options, "last_name");

        if ($id) {
            $where .= " AND $users_table.id=$id";
        }
        if ($status === "active") {
            $where .= " AND $users_table.status='active'";
        } else if ($status === "inactive") {
            $where .= " AND $users_table.status='inactive'";
        }

        if ($user_type) {
            $where .= " AND $users_table.user_type='$user_type'";
        }

        if ($user_type == 'client') {
            $where .= " AND $clients_table.deleted=0";
        }

        if ($first_name) {
            $where .= " AND $users_table.first_name='$first_name'";
        }

        if ($last_name) {
            $where .= " AND $users_table.last_name='$last_name'";
        }

        if ($client_id) {
            $where .= " AND $users_table.client_id=$client_id";
        }

        if ($exclude_user_id) {
            $where .= " AND $users_table.id!=$exclude_user_id";
        }

        $non_admin_users_only = $this->_get_clean_value($options, "non_admin_users_only");
        if ($non_admin_users_only) {
            $where .= " AND $users_table.is_admin=0";
        }

        $show_own_clients_only_user_id = $this->_get_clean_value($options, "show_own_clients_only_user_id");
        if ($user_type == "client" && $show_own_clients_only_user_id) {
            $where .= " AND $users_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 AND $clients_table.created_by=$show_own_clients_only_user_id)";
        }

        $quick_filter = $this->_get_clean_value($options, "quick_filter");
        if ($quick_filter) {
            $where .= $this->make_quick_filter_query($quick_filter, $users_table);
        }

        $client_groups = $this->_get_clean_value($options, "client_groups");
        if ($client_groups) {
            $client_groups_where = $this->prepare_allowed_client_groups_query($clients_table, $client_groups);
            if ($client_groups_where) {
                $where .= " AND $users_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 $client_groups_where)";
            }
        }

        $custom_field_type = "team_members";
        if ($user_type === "client") {
            $custom_field_type = "client_contacts";
        } else if ($user_type === "lead") {
            $custom_field_type = "lead_contacts";
        }

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $offset = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $offset ";
        }

        $available_order_by_list = array(
            "first_name" => $users_table . ".first_name",
            "company_name" => $clients_table . ".company_name",
            "job_title" => $users_table . ".job_title",
            "email" => $users_table . ".email",
            "phone" => $users_table . ".phone",
            "skype" => $users_table . ".skype",
        );

        $order_by = get_array_value($available_order_by_list, $this->_get_clean_value($options, "order_by"));

        $order = "ORDER BY $users_table.first_name";

        if ($order_by) {
            $order_dir = $this->_get_clean_value($options, "order_dir");
            $order = " ORDER BY $order_by $order_dir ";
        }

        $search_by = get_array_value($options, "search_by");
        if ($search_by) {
            $search_by = $this->db->escapeLikeString($search_by);

            $where .= " AND (";
            $where .= " $users_table.job_title LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $users_table.email LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $users_table.phone LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $users_table.skype LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $clients_table.company_name LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR CONCAT($users_table.first_name, ' ', $users_table.last_name) LIKE '%$search_by%' ESCAPE '!' ";
            $where .= $this->get_custom_field_search_query($users_table, "client_contacts", $search_by);
            $where .= " )";
        }

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string($custom_field_type, $custom_fields, $users_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        //prepare full query string
        $sql = "SELECT SQL_CALC_FOUND_ROWS $users_table.*, $roles_table.title AS role_title,
            $team_member_job_info_table.date_of_hire, $team_member_job_info_table.salary, $team_member_job_info_table.salary_term $select_custom_fieds
        FROM $users_table
        LEFT JOIN $team_member_job_info_table ON $team_member_job_info_table.user_id=$users_table.id
        LEFT JOIN $clients_table ON $clients_table.id=$users_table.client_id
        LEFT JOIN $roles_table ON $roles_table.id=$users_table.role_id
        $join_custom_fieds    
        WHERE $users_table.deleted=0 $where $custom_fields_where
        $order $limit_offset";

        $raw_query = $this->db->query($sql);

        $total_rows = $this->db->query("SELECT FOUND_ROWS() as found_rows")->getRow();

        if ($limit) {
            return array(
                "data" => $raw_query->getResult(),
                "recordsTotal" => $total_rows->found_rows,
                "recordsFiltered" => $total_rows->found_rows,
            );
        } else {
            return $raw_query;
        }
    }

    function is_email_exists($email, $id = 0, $client_id = 0) {
        $users_table = $this->db->prefixTable('users');
        $id = $id ? $this->db->escapeString($id) : $id;
        $client_id = $client_id ? $this->db->escapeString($client_id) : $client_id;

        $where = "";
        if ($client_id) {
            $where .= " AND $users_table.client_id=$client_id ";
        }

        $sql = "SELECT $users_table.* FROM $users_table   
        WHERE $users_table.deleted=0 AND $users_table.email='$email' $where ";

        $result = $this->db->query($sql);

        if ($result->resultID->num_rows && $result->getRow()->id != $id) {
            return $result->getRow();
        } else {
            return false;
        }
    }

    function get_job_info($user_id) {
        parent::use_table("team_member_job_info");
        return parent::get_one_where(array("user_id" => $user_id));
    }

    function save_job_info($data) {
        parent::use_table("team_member_job_info");

        //check if job info already exists
        $where = array("user_id" => $this->_get_clean_value($data, "user_id"));
        $exists = parent::get_one_where($where);
        if ($exists->user_id) {
            //job info found. update the record
            return parent::update_where($data, $where);
        } else {
            //insert new one
            return parent::ci_save($data);
        }
    }

    function get_team_members($member_ids = "") {
        $users_table = $this->db->prefixTable('users');
        $sql = "SELECT $users_table.*
        FROM $users_table
        WHERE $users_table.deleted=0 AND $users_table.user_type='staff' AND FIND_IN_SET($users_table.id, '$member_ids')
        ORDER BY $users_table.first_name";
        return $this->db->query($sql);
    }

    function get_access_info($user_id = 0) {
        $users_table = $this->db->prefixTable('users');
        $roles_table = $this->db->prefixTable('roles');
        $team_table = $this->db->prefixTable('team');

        if (!$user_id) {
            $user_id = 0;
        }

        $sql = "SELECT $users_table.id, $users_table.user_type, $users_table.is_admin, $users_table.role_id, $users_table.email,
            $users_table.first_name, $users_table.last_name, $users_table.image, $users_table.message_checked_at, $users_table.notification_checked_at, $users_table.client_id, $users_table.enable_web_notification,
            $users_table.is_primary_contact, $users_table.sticky_note, $users_table.language,
            $roles_table.title as role_title, $roles_table.permissions,
            (SELECT GROUP_CONCAT(id) team_ids FROM $team_table WHERE FIND_IN_SET('$user_id', `members`)) as team_ids
        FROM $users_table
        LEFT JOIN $roles_table ON $roles_table.id = $users_table.role_id AND $roles_table.deleted = 0
        WHERE $users_table.deleted=0 AND $users_table.id=$user_id";
        return $this->db->query($sql)->getRow();
    }

    function get_team_members_and_clients($user_type = "", $user_ids = "", $exlclude_user = 0) {

        $users_table = $this->db->prefixTable('users');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";
        if ($user_type) {
            $where .= " AND $users_table.user_type='$user_type'";
        } else {
            $where .= " AND $users_table.user_type!='lead'";
        }

        if ($user_ids) {
            $where .= "  AND FIND_IN_SET($users_table.id, '$user_ids')";
        }

        if ($exlclude_user) {
            $where .= " AND $users_table.id !=$exlclude_user";
        }

        $sql = "SELECT $users_table.id,$users_table.client_id, $users_table.user_type, $users_table.first_name, $users_table.last_name, $clients_table.company_name,
            $users_table.image,  $users_table.job_title, $users_table.last_online
        FROM $users_table
        LEFT JOIN $clients_table ON $clients_table.id = $users_table.client_id AND $clients_table.deleted=0
        WHERE $users_table.deleted=0 AND $users_table.status='active' $where
        ORDER BY $users_table.user_type, $users_table.first_name ASC";
        return $this->db->query($sql);
    }

    /* return comma separated list of user names */

    function user_group_names($user_ids = "") {
        $users_table = $this->db->prefixTable('users');

        $sql = "SELECT GROUP_CONCAT(' ', $users_table.first_name, ' ', $users_table.last_name) AS user_group_name
        FROM $users_table
        WHERE FIND_IN_SET($users_table.id, '$user_ids')";
        return $this->db->query($sql)->getRow();
    }

    /* return list of ids of the online users */

    function get_online_user_ids() {
        $users_table = $this->db->prefixTable('users');
        $now = get_current_utc_time();

        $sql = "SELECT $users_table.id 
        FROM $users_table
        WHERE TIMESTAMPDIFF(MINUTE, users.last_online, '$now')<=0";
        return $this->db->query($sql)->getResult();
    }

    function get_active_members_and_clients($options = array()) {
        $users_table = $this->db->prefixTable('users');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";

        $user_type = $this->_get_clean_value($options, "user_type");
        if ($user_type) {
            $where .= " AND $users_table.user_type='$user_type'";
        }

        $exclude_user_id = $this->_get_clean_value($options, "exclude_user_id");
        if ($exclude_user_id) {
            $where .= " AND $users_table.id!=$exclude_user_id";
        }

        $show_own_clients_only_user_id = $this->_get_clean_value($options, "show_own_clients_only_user_id");
        if ($user_type == "client" && $show_own_clients_only_user_id) {
            $where .= " AND $users_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 AND $clients_table.created_by=$show_own_clients_only_user_id)";
        }

        $client_groups = $this->_get_clean_value($options, "client_groups");
        if ($client_groups) {
            $client_groups_where = $this->prepare_allowed_client_groups_query($clients_table, $client_groups);
            if ($client_groups_where) {
                $where .= " AND $users_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 $client_groups_where)";
            }
        }

        $sql = "SELECT CONCAT($users_table.first_name, ' ',$users_table.last_name) AS member_name, $users_table.last_online, $users_table.id, $users_table.image, $users_table.job_title, $users_table.user_type, $clients_table.company_name
        FROM $users_table
        LEFT JOIN $clients_table ON $clients_table.id = $users_table.client_id AND $clients_table.deleted=0
        WHERE $users_table.deleted=0 AND $users_table.status='active' $where
        ORDER BY $users_table.last_online DESC";
        return $this->db->query($sql);
    }

    function count_total_contacts($options = array()) {
        $users_table = $this->db->prefixTable('users');
        $clients_table = $this->db->prefixTable('clients');

        $where = "";
        $show_own_clients_only_user_id = $this->_get_clean_value($options, "show_own_clients_only_user_id");
        if ($show_own_clients_only_user_id) {
            $where .= " AND $users_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 AND $clients_table.created_by=$show_own_clients_only_user_id)";
        }

        $last_online = $this->_get_clean_value($options, "last_online");
        if ($last_online) {
            $where .= " AND DATE($users_table.last_online)>='$last_online'";
        }

        $client_groups = $this->_get_clean_value($options, "client_groups");
        if ($client_groups) {
            $client_groups_where = $this->prepare_allowed_client_groups_query($clients_table, $client_groups);
            if ($client_groups_where) {
                $where .= " AND $users_table.client_id IN(SELECT $clients_table.id FROM $clients_table WHERE $clients_table.deleted=0 $client_groups_where)";
            }
        }

        $sql = "SELECT COUNT($users_table.id) AS total
        FROM $users_table 
        WHERE $users_table.deleted=0 AND $users_table.user_type='client' $where";
        return $this->db->query($sql)->getRow()->total;
    }

    private function make_quick_filter_query($filter, $users_table) {
        $query = "";

        if ($filter == "logged_in_today" || $filter == "logged_in_seven_days") {
            $last_online = get_today_date();
            if ($filter == "logged_in_seven_days") {
                $last_online = subtract_period_from_date(get_today_date(), 7, "days");
            }

            $query = " AND $users_table.id IN(SELECT $users_table.id FROM $users_table WHERE $users_table.deleted=0 AND $users_table.user_type='client' AND DATE($users_table.last_online)>='$last_online') ";
        }

        return $query;
    }

    function get_user_from_full_name($user_full_name = "", $user_type = "") {
        $users_table = $this->db->prefixTable('users');

        $where = "";
        if ($user_type === "staff") {
            $where .= " AND $users_table.user_type='staff' ";
        } else if ($user_type === "client") {
            $where .= " AND $users_table.user_type='client' ";
        }

        $sql = "SELECT $users_table.id 
        FROM $users_table
        WHERE $users_table.deleted=0 AND $users_table.status='active' AND CONCAT(TRIM($users_table.first_name), ' ', TRIM($users_table.last_name))='$user_full_name' $where
        LIMIT 1";

        return $this->db->query($sql)->getRow();
    }

    function get_other_clients_of_this_client_contact($email, $id) {
        $users_table = $this->db->prefixTable('users');
        $clients_table = $this->db->prefixTable('clients');

        $sql = "SELECT $users_table.id AS user_id, $clients_table.company_name 
        FROM $users_table   
        LEFT JOIN $clients_table ON $clients_table.id = $users_table.client_id AND $clients_table.deleted=0
        WHERE $users_table.deleted=0 AND $users_table.email='$email' AND $users_table.status='active' AND $users_table.disable_login=0 AND $users_table.user_type='client' AND $users_table.id!=$id ";

        return $this->db->query($sql);
    }

    function update_password($email, $password) {
        $users_table = $this->db->prefixTable('users');

        $sql = "UPDATE $users_table SET $users_table.password='$password' WHERE $users_table.deleted=0 AND $users_table.email='$email'; ";
        $this->db->query($sql);

        return true;
    }

    function count_total_users() {
        $users_table = $this->db->prefixTable('users');

        $sql = "SELECT COUNT($users_table.id) AS total
        FROM $users_table 
        WHERE $users_table.deleted=0 AND $users_table.user_type='staff' AND $users_table.status='active'";
        return $this->db->query($sql)->getRow()->total;
    }

}
