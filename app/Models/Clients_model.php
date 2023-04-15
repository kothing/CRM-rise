<?php

namespace App\Models;

class Clients_model extends Crud_model {

    protected $table = null;

    function __construct() {
        $this->table = 'clients';
        parent::__construct($this->table);
    }

    function get_details($options = array()) {
        $clients_table = $this->db->prefixTable('clients');
        $projects_table = $this->db->prefixTable('projects');
        $users_table = $this->db->prefixTable('users');
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $client_groups_table = $this->db->prefixTable('client_groups');
        $lead_status_table = $this->db->prefixTable('lead_status');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');
        $tickets_table = $this->db->prefixTable('tickets');
        $orders_table = $this->db->prefixTable('orders');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";
        $id = $this->_get_clean_value($options, "id");
        if ($id) {
            $where .= " AND $clients_table.id=$id";
        }

        $custom_field_type = "clients";

        $leads_only = $this->_get_clean_value($options, "leads_only");
        if ($leads_only) {
            $custom_field_type = "leads";
            $where .= " AND $clients_table.is_lead=1";
        }

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND $clients_table.lead_status_id='$status'";
        }

        $source = $this->_get_clean_value($options, "source");
        if ($source) {
            $where .= " AND $clients_table.lead_source_id='$source'";
        }

        $owner_id = $this->_get_clean_value($options, "owner_id");
        if ($owner_id) {
            $where .= " AND $clients_table.owner_id=$owner_id";
        }

        $created_by = $this->_get_clean_value($options, "created_by");
        if ($created_by) {
            $where .= " AND $clients_table.created_by=$created_by";
        }

        $show_own_clients_only_user_id = $this->_get_clean_value($options, "show_own_clients_only_user_id");
        if ($show_own_clients_only_user_id) {
            $where .= " AND ($clients_table.created_by=$show_own_clients_only_user_id OR $clients_table.owner_id=$show_own_clients_only_user_id)";
        }

        if (!$id && !$leads_only) {
            //only clients
            $where .= " AND $clients_table.is_lead=0";
        }

        $group_id = $this->_get_clean_value($options, "group_id");
        if ($group_id) {
            $where .= " AND FIND_IN_SET('$group_id', $clients_table.group_ids)";
        }

        $quick_filter = $this->_get_clean_value($options, "quick_filter");
        if ($quick_filter) {
            $where .= $this->make_quick_filter_query($quick_filter, $clients_table, $projects_table, $invoices_table, $taxes_table, $invoice_payments_table, $invoice_items_table, $estimates_table, $estimate_requests_table, $tickets_table, $orders_table, $proposals_table);
        }

        $start_date = $this->_get_clean_value($options, "start_date");
        if ($start_date) {
            $where .= " AND DATE($clients_table.created_date)>='$start_date'";
        }
        $end_date = $this->_get_clean_value($options, "end_date");
        if ($end_date) {
            $where .= " AND DATE($clients_table.created_date)<='$end_date'";
        }

        $client_groups = $this->_get_clean_value($options, "client_groups");
        $where .= $this->prepare_allowed_client_groups_query($clients_table, $client_groups);

        //prepare custom fild binding query
        $custom_fields = get_array_value($options, "custom_fields");
        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string($custom_field_type, $custom_fields, $clients_table, $custom_field_filter);
        $select_custom_fieds = get_array_value($custom_field_query_info, "select_string");
        $join_custom_fieds = get_array_value($custom_field_query_info, "join_string");
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $invoice_value_calculation_query = "(SUM" . $this->_get_invoice_value_calculation_query($invoices_table) . ")";

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $invoice_value_select = "IFNULL(invoice_details.invoice_value,0)";
        $payment_value_select = "IFNULL(invoice_details.payment_received,0)";

        $limit_offset = "";
        $limit = $this->_get_clean_value($options, "limit");
        if ($limit) {
            $skip = $this->_get_clean_value($options, "skip");
            $offset = $skip ? $skip : 0;
            $limit_offset = " LIMIT $limit OFFSET $offset ";
        }


        $available_order_by_list = array(
            "id" => $clients_table . ".id",
            "company_name" => $clients_table . ".company_name",
            "created_date" => $clients_table . ".created_date",
            "primary_contact" => $users_table . ".first_name",
            "status" => "lead_status_title",
            "owner_name" => "owner_details.owner_name",
            "primary_contact" => "primary_contact",
            "client_groups" => "client_groups"
        );

        $order_by = get_array_value($available_order_by_list, $this->_get_clean_value($options, "order_by"));

        $order = "";

        if ($order_by) {
            $order_dir = $this->_get_clean_value($options, "order_dir");
            $order = " ORDER BY $order_by $order_dir ";
        }


        $search_by = get_array_value($options, "search_by");
        if ($search_by) {
            $search_by = $this->db->escapeLikeString($search_by);

            $where .= " AND (";
            $where .= " $clients_table.id LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR $clients_table.company_name LIKE '%$search_by%' ESCAPE '!' ";
            $where .= " OR CONCAT($users_table.first_name, ' ', $users_table.last_name) LIKE '%$search_by%' ESCAPE '!' ";

            if ($leads_only) {
                $where .= " OR owner_details.owner_name LIKE '%$search_by%' ESCAPE '!' ";
                $where .= " OR $lead_status_table.title LIKE '%$search_by%' ESCAPE '!' ";
                $where .= $this->get_custom_field_search_query($clients_table, "leads", $search_by);
            } else {
                $where .= $this->get_custom_field_search_query($clients_table, "clients", $search_by);
            }

            $where .= " )";
        }


        $sql = "SELECT SQL_CALC_FOUND_ROWS $clients_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS primary_contact, $users_table.id AS primary_contact_id, $users_table.image AS contact_avatar,  project_table.total_projects, $payment_value_select AS payment_received $select_custom_fieds,
                IF((($invoice_value_select > $payment_value_select) AND ($invoice_value_select - $payment_value_select) <0.05), $payment_value_select, $invoice_value_select) AS invoice_value,
                (SELECT GROUP_CONCAT($client_groups_table.title) FROM $client_groups_table WHERE FIND_IN_SET($client_groups_table.id, $clients_table.group_ids)) AS client_groups, $lead_status_table.title AS lead_status_title,  $lead_status_table.color AS lead_status_color,
                owner_details.owner_name, owner_details.owner_avatar
        FROM $clients_table
        LEFT JOIN $users_table ON $users_table.client_id = $clients_table.id AND $users_table.deleted=0 AND $users_table.is_primary_contact=1 
        LEFT JOIN (SELECT client_id, COUNT(id) AS total_projects FROM $projects_table WHERE deleted=0 AND project_type='client_project' GROUP BY client_id) AS project_table ON project_table.client_id= $clients_table.id
        LEFT JOIN (SELECT client_id, SUM(payments_table.payment_received) as payment_received, $invoice_value_calculation_query as invoice_value FROM $invoices_table
                   LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $invoices_table.tax_id
                   LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $invoices_table.tax_id2 
                   LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table3 ON tax_table3.id = $invoices_table.tax_id3 
                   LEFT JOIN (SELECT invoice_id, SUM(amount) AS payment_received FROM $invoice_payments_table WHERE deleted=0 GROUP BY invoice_id) AS payments_table ON payments_table.invoice_id=$invoices_table.id AND $invoices_table.deleted=0 AND $invoices_table.status='not_paid'
                   LEFT JOIN (SELECT invoice_id, SUM(total) AS invoice_value FROM $invoice_items_table WHERE deleted=0 GROUP BY invoice_id) AS items_table ON items_table.invoice_id=$invoices_table.id AND $invoices_table.deleted=0 AND $invoices_table.status='not_paid'
                   WHERE $invoices_table.deleted=0 AND $invoices_table.status='not_paid'
                   GROUP BY $invoices_table.client_id    
                   ) AS invoice_details ON invoice_details.client_id= $clients_table.id 
        LEFT JOIN $lead_status_table ON $clients_table.lead_status_id = $lead_status_table.id 
        LEFT JOIN (SELECT $users_table.id, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS owner_name, $users_table.image AS owner_avatar FROM $users_table WHERE $users_table.deleted=0 AND $users_table.user_type='staff') AS owner_details ON owner_details.id=$clients_table.owner_id
        $join_custom_fieds               
        WHERE $clients_table.deleted=0 $where $custom_fields_where  
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

    private function make_quick_filter_query($filter, $clients_table, $projects_table, $invoices_table, $taxes_table, $invoice_payments_table, $invoice_items_table, $estimates_table, $estimate_requests_table, $tickets_table, $orders_table, $proposals_table) {
        $query = "";

        if ($filter == "has_open_projects" || $filter == "has_completed_projects" || $filter == "has_any_hold_projects" || $filter == "has_canceled_projects") {
            $status = "open";
            if ($filter == "has_completed_projects") {
                $status = "completed";
            } else if ($filter == "has_any_hold_projects") {
                $status = "hold";
            } else if ($filter == "has_canceled_projects") {
                $status = "canceled";
            }

            $query = " AND $clients_table.id IN(SELECT $projects_table.client_id FROM $projects_table WHERE $projects_table.deleted=0 AND $projects_table.project_type='client_project' AND $projects_table.status='$status') ";
        } else if ($filter == "has_unpaid_invoices" || $filter == "has_overdue_invoices" || $filter == "has_partially_paid_invoices") {
            $now = get_my_local_time("Y-m-d");
            $invoice_value_calculation_query = $this->_get_invoice_value_calculation_query($invoices_table);
            $invoice_value_calculation = "TRUNCATE($invoice_value_calculation_query,2)";

            $invoice_where = " AND $invoices_table.status !='draft' AND $invoices_table.status!='cancelled' AND IFNULL(payments_table.payment_received,0)<=0";
            if ($filter == "has_overdue_invoices") {
                $invoice_where = " AND $invoices_table.status !='draft' AND $invoices_table.status!='cancelled' AND $invoices_table.due_date<'$now' AND TRUNCATE(IFNULL(payments_table.payment_received,0),2)<$invoice_value_calculation";
            } else if ($filter == "has_partially_paid_invoices") {
                $invoice_where = " AND IFNULL(payments_table.payment_received,0)>0 AND IFNULL(payments_table.payment_received,0)<$invoice_value_calculation";
            }

            $query = " AND $clients_table.id IN(
                            SELECT $invoices_table.client_id FROM $invoices_table 
                            LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table ON tax_table.id = $invoices_table.tax_id
                            LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table2 ON tax_table2.id = $invoices_table.tax_id2
                            LEFT JOIN (SELECT $taxes_table.* FROM $taxes_table) AS tax_table3 ON tax_table3.id = $invoices_table.tax_id3
                            LEFT JOIN (SELECT invoice_id, SUM(amount) AS payment_received FROM $invoice_payments_table WHERE deleted=0 GROUP BY invoice_id) AS payments_table ON payments_table.invoice_id = $invoices_table.id 
                            LEFT JOIN (SELECT invoice_id, SUM(total) AS invoice_value FROM $invoice_items_table WHERE deleted=0 GROUP BY invoice_id) AS items_table ON items_table.invoice_id = $invoices_table.id 
                            WHERE $invoices_table.deleted=0 $invoice_where
                    ) ";
        } else if ($filter == "has_open_estimates" || $filter == "has_accepted_estimates") {
            $status = "sent";
            if ($filter == "has_accepted_estimates") {
                $status = "accepted";
            }

            $query = " AND $clients_table.id IN(SELECT $estimates_table.client_id FROM $estimates_table WHERE $estimates_table.deleted=0 AND $estimates_table.status='$status') ";
        } else if ($filter == "has_new_estimate_requests" || $filter == "has_estimate_requests_in_progress") {
            $status = "new";
            if ($filter == "has_estimate_requests_in_progress") {
                $status = "processing";
            }

            $query = " AND $clients_table.id IN(SELECT $estimate_requests_table.client_id FROM $estimate_requests_table WHERE $estimate_requests_table.deleted=0 AND $estimate_requests_table.status='$status') ";
        } else if ($filter == "has_open_tickets") {
            $query = " AND $clients_table.id IN(SELECT $tickets_table.client_id FROM $tickets_table WHERE $tickets_table.deleted=0 AND $tickets_table.status!='closed') ";
        } else if ($filter == "has_new_orders") {
            $query = " AND $clients_table.id IN(SELECT $orders_table.client_id FROM $orders_table WHERE $orders_table.deleted=0 AND $orders_table.status_id='1') ";
        } else if ($filter == "has_open_proposals" || $filter == "has_accepted_proposals" || $filter == "has_rejected_proposals") {
            $status = "sent";
            if ($filter == "has_accepted_proposals") {
                $status = "accepted";
            } else if ($filter == "has_rejected_proposals") {
                $status = "declined";
            }

            $query = " AND $clients_table.id IN(SELECT $proposals_table.client_id FROM $proposals_table WHERE $proposals_table.deleted=0 AND $proposals_table.status='$status') ";
        }

        return $query;
    }

    function get_primary_contact($client_id = 0, $info = false) {
        $users_table = $this->db->prefixTable('users');

        $sql = "SELECT $users_table.id, $users_table.first_name, $users_table.last_name
        FROM $users_table
        WHERE $users_table.deleted=0 AND $users_table.client_id=$client_id AND $users_table.is_primary_contact=1";
        $result = $this->db->query($sql);
        if ($result->resultID->num_rows) {
            if ($info) {
                return $result->getRow();
            } else {
                return $result->getRow()->id;
            }
        }
    }

    function add_remove_star($client_id, $user_id, $type = "add") {
        $clients_table = $this->db->prefixTable('clients');
        $client_id = $client_id ? $this->db->escapeString($client_id) : $client_id;

        $action = " CONCAT($clients_table.starred_by,',',':$user_id:') ";
        $where = " AND FIND_IN_SET(':$user_id:',$clients_table.starred_by) = 0"; //don't add duplicate

        if ($type != "add") {
            $action = " REPLACE($clients_table.starred_by, ',:$user_id:', '') ";
            $where = "";
        }

        $sql = "UPDATE $clients_table SET $clients_table.starred_by = $action
        WHERE $clients_table.id=$client_id $where";
        return $this->db->query($sql);
    }

    function get_starred_clients($user_id, $client_groups = "") {
        $clients_table = $this->db->prefixTable('clients');

        $where = $this->prepare_allowed_client_groups_query($clients_table, $client_groups);

        $sql = "SELECT $clients_table.id,  $clients_table.company_name
        FROM $clients_table
        WHERE $clients_table.deleted=0 AND FIND_IN_SET(':$user_id:',$clients_table.starred_by) $where
        ORDER BY $clients_table.company_name ASC";
        return $this->db->query($sql);
    }

    function delete_client_and_sub_items($client_id) {
        $clients_table = $this->db->prefixTable('clients');
        $general_files_table = $this->db->prefixTable('general_files');
        $users_table = $this->db->prefixTable('users');

        //get client files info to delete the files from directory 
        $client_files_sql = "SELECT * FROM $general_files_table WHERE $general_files_table.deleted=0 AND $general_files_table.client_id=$client_id; ";
        $client_files = $this->db->query($client_files_sql)->getResult();

        //delete the client and sub items
        //delete client
        $delete_client_sql = "UPDATE $clients_table SET $clients_table.deleted=1 WHERE $clients_table.id=$client_id; ";
        $this->db->query($delete_client_sql);

        //delete contacts
        $delete_contacts_sql = "UPDATE $users_table SET $users_table.deleted=1 WHERE $users_table.client_id=$client_id; ";
        $this->db->query($delete_contacts_sql);

        //delete the project files from directory
        $file_path = get_general_file_path("client", $client_id);
        foreach ($client_files as $file) {
            delete_app_files($file_path, array(make_array_of_file($file)));
        }

        return true;
    }

    function is_duplicate_company_name($company_name, $id = 0) {

        $result = $this->get_all_where(array("company_name" => $company_name, "is_lead" => 0, "deleted" => 0));
        if (count($result->getResult()) && $result->getRow()->id != $id) {
            return $result->getRow();
        } else {
            return false;
        }
    }

    function get_leads_kanban_details($options = array()) {
        $clients_table = $this->db->prefixTable('clients');
        $lead_source_table = $this->db->prefixTable('lead_source');
        $users_table = $this->db->prefixTable('users');
        $events_table = $this->db->prefixTable('events');
        $notes_table = $this->db->prefixTable('notes');
        $estimates_table = $this->db->prefixTable('estimates');
        $general_files_table = $this->db->prefixTable('general_files');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');

        $where = "";

        $status = $this->_get_clean_value($options, "status");
        if ($status) {
            $where .= " AND $clients_table.lead_status_id='$status'";
        }

        $owner_id = $this->_get_clean_value($options, "owner_id");
        if ($owner_id) {
            $where .= " AND $clients_table.owner_id='$owner_id'";
        }

        $source = $this->_get_clean_value($options, "source");
        if ($source) {
            $where .= " AND $clients_table.lead_source_id='$source'";
        }

        $search = get_array_value($options, "search");
        if ($search) {
            $search = $this->db->escapeLikeString($search);
            $where .= " AND $clients_table.company_name LIKE '%$search%' ESCAPE '!'";
        }

        $custom_field_filter = get_array_value($options, "custom_field_filter");
        $custom_field_query_info = $this->prepare_custom_field_query_string("leads", "", $clients_table, $custom_field_filter);
        $custom_fields_where = get_array_value($custom_field_query_info, "where_string");

        $users_where = "$users_table.client_id=$clients_table.id AND $users_table.deleted=0 AND $users_table.user_type='lead'";

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $sql = "SELECT $clients_table.id, $clients_table.company_name, $clients_table.sort, IF($clients_table.sort!=0, $clients_table.sort, $clients_table.id) AS new_sort, $clients_table.lead_status_id, $clients_table.owner_id,
                (SELECT $users_table.image FROM $users_table WHERE $users_where AND $users_table.is_primary_contact=1) AS primary_contact_avatar,
                (SELECT COUNT($users_table.id) FROM $users_table WHERE $users_where) AS total_contacts_count,
                (SELECT COUNT($events_table.id) FROM $events_table WHERE $events_table.deleted=0 AND $events_table.client_id=$clients_table.id) AS total_events_count,
                (SELECT COUNT($notes_table.id) FROM $notes_table WHERE $notes_table.deleted=0 AND $notes_table.client_id=$clients_table.id) AS total_notes_count,
                (SELECT COUNT($estimates_table.id) FROM $estimates_table WHERE $estimates_table.deleted=0 AND $estimates_table.client_id=$clients_table.id) AS total_estimates_count,
                (SELECT COUNT($general_files_table.id) FROM $general_files_table WHERE $general_files_table.deleted=0 AND $general_files_table.client_id=$clients_table.id) AS total_files_count,
                (SELECT COUNT($estimate_requests_table.id) FROM $estimate_requests_table WHERE $estimate_requests_table.deleted=0 AND $estimate_requests_table.client_id=$clients_table.id) AS total_estimate_requests_count,
                $lead_source_table.title AS lead_source_title,
                CONCAT($users_table.first_name, ' ', $users_table.last_name) AS owner_name
        FROM $clients_table 
        LEFT JOIN $lead_source_table ON $clients_table.lead_source_id = $lead_source_table.id 
        LEFT JOIN $users_table ON $users_table.id = $clients_table.owner_id AND $users_table.deleted=0 AND $users_table.user_type='staff' 
        WHERE $clients_table.deleted=0 AND $clients_table.is_lead=1 $where $custom_fields_where
        ORDER BY new_sort ASC";

        return $this->db->query($sql);
    }

    function get_search_suggestion($search = "", $options = array()) {
        $clients_table = $this->db->prefixTable('clients');

        $where = "";
        $show_own_clients_only_user_id = $this->_get_clean_value($options, "show_own_clients_only_user_id");
        if ($show_own_clients_only_user_id) {
            $where .= " AND ($clients_table.created_by=$show_own_clients_only_user_id OR $clients_table.owner_id=$show_own_clients_only_user_id)";
        }

        if ($search) {
            $search = $this->db->escapeLikeString($search);
        }

        $client_groups = $this->_get_clean_value($options, "client_groups");
        $where .= $this->prepare_allowed_client_groups_query($clients_table, $client_groups);

        $sql = "SELECT $clients_table.id, $clients_table.company_name AS title
        FROM $clients_table  
        WHERE $clients_table.deleted=0 AND $clients_table.is_lead=0 AND $clients_table.company_name LIKE '%$search%' ESCAPE '!' $where
        ORDER BY $clients_table.company_name ASC
        LIMIT 0, 10";

        return $this->db->query($sql);
    }

    function count_total_clients($options = array()) {
        $clients_table = $this->db->prefixTable('clients');
        $tickets_table = $this->db->prefixTable('tickets');
        $invoices_table = $this->db->prefixTable('invoices');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $invoice_items_table = $this->db->prefixTable('invoice_items');
        $taxes_table = $this->db->prefixTable('taxes');
        $projects_table = $this->db->prefixTable('projects');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_requests_table = $this->db->prefixTable('estimate_requests');
        $orders_table = $this->db->prefixTable('orders');
        $proposals_table = $this->db->prefixTable('proposals');

        $where = "";

        $show_own_clients_only_user_id = $this->_get_clean_value($options, "show_own_clients_only_user_id");
        if ($show_own_clients_only_user_id) {
            $where .= " AND $clients_table.created_by=$show_own_clients_only_user_id";
        }

        $filter = $this->_get_clean_value($options, "filter");
        if ($filter) {
            $where .= $this->make_quick_filter_query($filter, $clients_table, $projects_table, $invoices_table, $taxes_table, $invoice_payments_table, $invoice_items_table, $estimates_table, $estimate_requests_table, $tickets_table, $orders_table, $proposals_table);
        }

        $client_groups = $this->_get_clean_value($options, "client_groups");
        $where .= $this->prepare_allowed_client_groups_query($clients_table, $client_groups);

        $sql = "SELECT COUNT($clients_table.id) AS total
        FROM $clients_table 
        WHERE $clients_table.deleted=0 AND $clients_table.is_lead=0 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function get_conversion_rate_with_currency_symbol() {
        $clients_table = $this->db->prefixTable('clients');

        $sql = "SELECT $clients_table.currency_symbol, $clients_table.currency
        FROM $clients_table 
        WHERE $clients_table.deleted=0 AND $clients_table.currency!='' AND $clients_table.currency IS NOT NULL
        GROUP BY $clients_table.currency";
        return $this->db->query($sql);
    }

    function count_total_leads($options = array()) {
        $clients_table = $this->db->prefixTable('clients');

        $where = "";
        $show_own_leads_only_user_id = $this->_get_clean_value($options, "show_own_leads_only_user_id");
        if ($show_own_leads_only_user_id) {
            $where .= " AND $clients_table.owner_id=$show_own_leads_only_user_id";
        }

        $sql = "SELECT COUNT($clients_table.id) AS total
        FROM $clients_table 
        WHERE $clients_table.deleted=0 AND $clients_table.is_lead=1 $where";
        return $this->db->query($sql)->getRow()->total;
    }

    function get_lead_statistics($options = array()) {
        $clients_table = $this->db->prefixTable('clients');
        $lead_status_table = $this->db->prefixTable('lead_status');

        try {
            $this->db->query("SET sql_mode = ''");
        } catch (\Exception $e) {
            
        }
        $where = "";

        $show_own_leads_only_user_id = $this->_get_clean_value($options, "show_own_leads_only_user_id");
        if ($show_own_leads_only_user_id) {
            $where .= " AND ($clients_table.owner_id=$show_own_leads_only_user_id)";
        }
        
        $converted_to_client = "SELECT COUNT($clients_table.id) AS total
        FROM $clients_table
        WHERE $clients_table.deleted=0 AND $clients_table.is_lead=0 AND $clients_table.lead_status_id!=0 $where";

        $lead_statuses = "SELECT COUNT($clients_table.id) AS total, $clients_table.lead_status_id, $lead_status_table.title, $lead_status_table.color
        FROM $clients_table
        LEFT JOIN $lead_status_table ON $lead_status_table.id = $clients_table.lead_status_id
        WHERE $clients_table.deleted=0 AND $clients_table.is_lead=1 $where
        GROUP BY $clients_table.lead_status_id
        ORDER BY $lead_status_table.sort ASC";

        $info = new \stdClass();
        $info->converted_to_client = $this->db->query($converted_to_client)->getRow()->total;
        $info->lead_statuses = $this->db->query($lead_statuses)->getResult();

        return $info;
    }

}
