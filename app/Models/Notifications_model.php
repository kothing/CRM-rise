<?php

namespace App\Models;

class Notifications_model extends Crud_model {

    protected $table = null;
    private $Project_comments_model;
    private $Project_settings_model;

    function __construct() {
        $this->Project_comments_model = model("App\Models\Project_comments_model");
        $this->Project_settings_model = model("App\Models\Project_settings_model");
        $this->table = 'notifications';
        parent::__construct($this->table);
    }

    function create_notification($event, $user_id, $options = array()) {
        $notification_settings_table = $this->db->prefixTable('notification_settings');
        $users_table = $this->db->prefixTable('users');
        $team_table = $this->db->prefixTable('team');
        $project_members_table = $this->db->prefixTable('project_members');
        $project_comments_table = $this->db->prefixTable('project_comments');
        $projects_table = $this->db->prefixTable('projects');
        $tasks_table = $this->db->prefixTable('tasks');
        $leave_applications_table = $this->db->prefixTable('leave_applications');
        $tickets_table = $this->db->prefixTable('tickets');
        $estimates_table = $this->db->prefixTable('estimates');
        $estimate_request_table = $this->db->prefixTable('estimate_requests');
        $messages_table = $this->db->prefixTable('messages');
        $invoices_table = $this->db->prefixTable('invoices');
        $roles_table = $this->db->prefixTable('roles');
        $events_table = $this->db->prefixTable('events');
        $announcements_table = $this->db->prefixTable('announcements');
        $clients_table = $this->db->prefixTable('clients');
        $contracts_table = $this->db->prefixTable('contracts');
        $proposals_table = $this->db->prefixTable('proposals');
        $orders_table = $this->db->prefixTable('orders');
        $posts_table = $this->db->prefixTable('posts');
        $subscriptions_table = $this->db->prefixTable('subscriptions');

        $notification_settings = $this->db->query("SELECT * FROM $notification_settings_table WHERE  $notification_settings_table.event='$event' AND ($notification_settings_table.enable_email OR $notification_settings_table.enable_web OR $notification_settings_table.enable_slack)")->getRow();
        if (!$notification_settings) {
            return false; //no notification settings found
        }

        $where = "";
        $notify_to_terms = $notification_settings->notify_to_terms;
        $options = $this->escape_array($options);
        $project_id = get_array_value($options, "project_id");
        $task_id = get_array_value($options, "task_id");
        $leave_id = get_array_value($options, "leave_id");
        $ticket_id = get_array_value($options, "ticket_id");
        $project_comment_id = get_array_value($options, "project_comment_id");
        $ticket_comment_id = get_array_value($options, "ticket_comment_id");
        $project_file_id = get_array_value($options, "project_file_id");
        $post_id = get_array_value($options, "post_id");
        $to_user_id = get_array_value($options, "to_user_id");
        $activity_log_id = get_array_value($options, "activity_log_id");
        $client_id = get_array_value($options, "client_id");
        $invoice_payment_id = get_array_value($options, "invoice_payment_id");
        $invoice_id = get_array_value($options, "invoice_id");
        $estimate_id = get_array_value($options, "estimate_id");
        $order_id = get_array_value($options, "order_id");
        $estimate_request_id = get_array_value($options, "estimate_request_id");
        $actual_message_id = get_array_value($options, "actual_message_id");
        $parent_message_id = get_array_value($options, "parent_message_id");
        $event_id = get_array_value($options, "event_id");
        $announcement_id = get_array_value($options, "announcement_id");
        $exclude_ticket_creator = get_array_value($options, "exclude_ticket_creator");
        $notify_to_admins_only = get_array_value($options, "notify_to_admins_only");
        $notification_multiple_tasks = get_array_value($options, "notification_multiple_tasks");
        $lead_id = get_array_value($options, "lead_id");
        $contract_id = get_array_value($options, "contract_id");
        $proposal_id = get_array_value($options, "proposal_id");
        $estimate_comment_id = get_array_value($options, "estimate_comment_id");
        $subscription_id = get_array_value($options, "subscription_id");

        $extra_data = array();

        //prepare notifiy to terms 
        if ($notify_to_terms) {
            $notify_to_terms = explode(",", $notify_to_terms);
        } else {
            $notify_to_terms = array();
        }

        /*
         * Using following terms:
         * team_members, team,
         * project_members, client_primary_contact, client_all_contacts, task_assignee, task_collaborators, comment_creator, leave_applicant, ticket_creator, ticket_assignee, estimate_request_assignee, post_creator
         */



        //find team members
        if ($notification_settings->notify_to_team_members) {
            $where .= " OR FIND_IN_SET($users_table.id, '$notification_settings->notify_to_team_members') ";
        }

        //find team
        if ($notification_settings->notify_to_team) {
            $where .= " OR FIND_IN_SET($users_table.id, (SELECT GROUP_CONCAT($team_table.members) AS team_users FROM $team_table WHERE $team_table.deleted=0 AND FIND_IN_SET($team_table.id, '$notification_settings->notify_to_team'))) ";
        }

        //find project members
        if (in_array("project_members", $notify_to_terms) && $project_id) {
            $where .= " OR FIND_IN_SET($users_table.id, (SELECT GROUP_CONCAT($project_members_table.user_id) AS proje_users FROM $project_members_table WHERE $project_members_table.deleted=0 AND $project_members_table.project_id=$project_id AND $project_members_table.user_id IN (SELECT $users_table.id AS client_contacts_of_project FROM $users_table WHERE $users_table.deleted=0 AND $users_table.user_type='staff'))) ";
        }

        //find task assignee
        if (in_array("task_assignee", $notify_to_terms) && $task_id) {
            $where .= " OR ($users_table.id=(SELECT $tasks_table.assigned_to FROM $tasks_table WHERE $tasks_table.id=$task_id)) ";
        }

        //find  task_collaborators
        if (in_array("task_collaborators", $notify_to_terms) && $task_id) {
            $where .= " OR (FIND_IN_SET($users_table.id, (SELECT $tasks_table.collaborators FROM $tasks_table WHERE $tasks_table.id=$task_id))) ";
        }


        //find client_all_contacts by project
        if (in_array("client_all_contacts", $notify_to_terms) && $project_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE FIND_IN_SET($users_table.client_id, (SELECT $projects_table.client_id FROM $projects_table WHERE $projects_table.id=$project_id))
                      )
                    ) ";
        }

        //find client_primary_contacts by project
        if (in_array("client_primary_contact", $notify_to_terms) && $project_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE $users_table.is_primary_contact=1 AND FIND_IN_SET($users_table.client_id, (SELECT $projects_table.client_id FROM $projects_table WHERE $projects_table.id=$project_id))
                      )
                    ) ";
        }

        //find client_assigned_contacts by project
        if (in_array("client_assigned_contacts", $notify_to_terms) && $project_id) {
            $where .= " OR FIND_IN_SET($users_table.id, (
                        SELECT GROUP_CONCAT($project_members_table.user_id) AS proje_users FROM $project_members_table WHERE $project_members_table.deleted=0 AND $project_members_table.project_id=$project_id AND $project_members_table.user_id IN (SELECT $users_table.id AS client_contacts_of_project FROM $users_table WHERE $users_table.deleted=0 AND $users_table.user_type='client')
                    )
                  ) ";
        }

        //find client_all_contacts by ticket
        if (in_array("client_all_contacts", $notify_to_terms) && $ticket_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE FIND_IN_SET($users_table.client_id, (SELECT $tickets_table.client_id FROM $tickets_table WHERE $tickets_table.id=$ticket_id))
                      )
                    ) ";
        }

        //find client_primary_contacts by project
        if (in_array("client_primary_contact", $notify_to_terms) && $ticket_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE $users_table.is_primary_contact=1 AND FIND_IN_SET($users_table.client_id, (SELECT $tickets_table.client_id FROM $tickets_table WHERE $tickets_table.id=$ticket_id))
                      )
                    ) ";
        }

        //find ticket creator
        if (in_array("ticket_creator", $notify_to_terms) && $ticket_id) {
            $where .= " OR ($users_table.id=(SELECT $tickets_table.created_by FROM $tickets_table WHERE $tickets_table.id=$ticket_id)) ";
        }

        //find ticket assignee
        if (in_array("ticket_assignee", $notify_to_terms) && $ticket_id) {
            $where .= " OR ($users_table.id=(SELECT $tickets_table.assigned_to FROM $tickets_table WHERE $tickets_table.id=$ticket_id)) ";
        }

        //find estimate request assignee
        if (in_array("estimate_request_assignee", $notify_to_terms) && $estimate_request_id) {
            $where .= " OR ($users_table.id=(SELECT $estimate_request_id.assigned_to FROM $estimate_request_id WHERE $estimate_request_table.id=$estimate_request_id)) ";
        }



        //find client_all_contacts by ticket
        if (in_array("client_all_contacts", $notify_to_terms) && $estimate_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE FIND_IN_SET($users_table.client_id, (SELECT $estimates_table.client_id FROM $estimates_table WHERE $estimates_table.id=$estimate_id))
                      )
                    ) ";
        }

        //find client_all_contacts by contract
        if (in_array("client_all_contacts", $notify_to_terms) && $contract_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE FIND_IN_SET($users_table.client_id, (SELECT $contracts_table.client_id FROM $contracts_table WHERE $contracts_table.id=$contract_id))
                      )
                    ) ";
        }

        //find client_all_contacts by proposal
        if (in_array("client_all_contacts", $notify_to_terms) && $proposal_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE FIND_IN_SET($users_table.client_id, (SELECT $proposals_table.client_id FROM $proposals_table WHERE $proposals_table.id=$proposal_id))
                      )
                    ) ";
        }

        //find client_primary_contacts by project
        if (in_array("client_primary_contact", $notify_to_terms) && $estimate_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE $users_table.is_primary_contact=1 AND FIND_IN_SET($users_table.client_id, (SELECT $estimates_table.client_id FROM $estimates_table WHERE $estimates_table.id=$estimate_id))
                      )
                    ) ";
        }

        //find client_primary_contacts by contract
        if (in_array("client_primary_contact", $notify_to_terms) && $contract_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE $users_table.is_primary_contact=1 AND FIND_IN_SET($users_table.client_id, (SELECT $contracts_table.client_id FROM $contracts_table WHERE $contracts_table.id=$contract_id))
                      )
                    ) ";
        }

        //find client_primary_contacts by proposal
        if (in_array("client_primary_contact", $notify_to_terms) && $proposal_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE $users_table.is_primary_contact=1 AND FIND_IN_SET($users_table.client_id, (SELECT $proposals_table.client_id FROM $proposals_table WHERE $proposals_table.id=$proposal_id))
                      )
                    ) ";
        }

        //find client_all_contacts by order
        if (in_array("client_all_contacts", $notify_to_terms) && $order_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE FIND_IN_SET($users_table.client_id, (SELECT $orders_table.client_id FROM $orders_table WHERE $orders_table.id=$order_id))
                      )
                    ) ";
        }

        //find client_primary_contacts by order
        if (in_array("client_primary_contact", $notify_to_terms) && $order_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE $users_table.is_primary_contact=1 AND FIND_IN_SET($users_table.client_id, (SELECT $orders_table.client_id FROM $orders_table WHERE $orders_table.id=$order_id))
                      )
                    ) ";
        }

        //find order creator contact if the creator is client
        if (in_array("order_creator_contact", $notify_to_terms) && $order_id) {
            $select_order_creator_query = "(SELECT $orders_table.created_by FROM $orders_table WHERE $orders_table.id=$order_id)";

            $where .= " OR ($users_table.id=( 
                        IF( (SELECT $users_table.user_type FROM $users_table WHERE $users_table.deleted=0 AND $users_table.id=$select_order_creator_query)='client',
                            $select_order_creator_query,
                            '' ) 
                        )) ";
        }

        //find project comment creator, comment id is not = id. It should be the the original comment_id
        if (in_array("comment_creator", $notify_to_terms) && $project_comment_id) {
            $where .= " OR ($users_table.id=(SELECT $project_comments_table.created_by FROM $project_comments_table WHERE $project_comments_table.id=$project_comment_id)) ";
        }



        //find leave_applicant
        if (in_array("leave_applicant", $notify_to_terms) && $leave_id) {
            $where .= " OR ($users_table.id=(SELECT $leave_applications_table.applicant_id FROM $leave_applications_table WHERE $leave_applications_table.id=$leave_id)) ";
        }


        //find message recipient
        if (in_array("recipient", $notify_to_terms) && $actual_message_id) {
            $where .= " OR ($users_table.id=(SELECT $messages_table.to_user_id FROM $messages_table WHERE $messages_table.id=$actual_message_id)) ";
        }

        //find mentioned members
        if (in_array("mentioned_members", $notify_to_terms) && $project_comment_id) {
            $comment_info = $this->Project_comments_model->get_one($project_comment_id);
            $mentioned_members = get_members_from_mention($comment_info->description);
            if ($mentioned_members) {
                $string_of_mentioned_members = implode(",", $mentioned_members);
                $where .= " OR FIND_IN_SET($users_table.id, '$string_of_mentioned_members')";
            }
        }

        //find owner by lead
        if (in_array("owner", $notify_to_terms) && $lead_id) {
            $where .= " OR ($users_table.id=(SELECT $clients_table.owner_id FROM $clients_table WHERE $clients_table.id=$lead_id)) ";
        }

        //find event recipient
        if (in_array("recipient", $notify_to_terms) && $event_id) {

            //find the event and check the recipient
            $event_info = $this->db->query("SELECT $events_table.* FROM $events_table WHERE $events_table.id=$event_id")->getRow();

            //we are saving the share with data like this:
            //member:1,member:2,team:1
            //all
            //so, we've to retrive the users 


            if ($event_info->share_with === "all") {
                $where .= " OR $users_table.user_type = 'staff' "; //all team members
            } else {


                $share_with_array = explode(",", $event_info->share_with); // found an array like this array("member:1", "member:2", "team:1")

                $event_users = array();
                $event_team = array();
                $event_contact = array();

                foreach ($share_with_array as $share) {

                    $share_data = explode(":", $share);

                    if (get_array_value($share_data, '0') === "member") {
                        $event_users[] = get_array_value($share_data, '1');
                    } else if (get_array_value($share_data, '0') === "team") {
                        $event_team[] = get_array_value($share_data, '1');
                    } else if (get_array_value($share_data, '0') === "contact") {
                        $event_contact[] = get_array_value($share_data, '1');
                    }
                }

                //find team members
                if (count($event_users)) {
                    $where .= " OR FIND_IN_SET($users_table.id, '" . join(',', $event_users) . "') ";
                }

                //find team
                if (count($event_team)) {
                    $where .= " OR FIND_IN_SET($users_table.id, (SELECT GROUP_CONCAT($team_table.members) AS team_users FROM $team_table WHERE $team_table.deleted=0 AND FIND_IN_SET($team_table.id, '" . join(',', $event_team) . "'))) ";
                }

                //find client contacts
                if (count($event_contact)) {
                    $where .= " OR FIND_IN_SET($users_table.id, '" . join(',', $event_contact) . "') ";
                }
            }
        }


        //find announcement recipient
        if (in_array("recipient", $notify_to_terms) && $announcement_id) {
            $where .= $this->prepare_announcement_receipients_query($announcements_table, $clients_table, $users_table, $announcement_id);
        }


        //find client_all_contacts by invoice
        if (in_array("client_all_contacts", $notify_to_terms) && $invoice_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE FIND_IN_SET($users_table.client_id, (SELECT $invoices_table.client_id FROM $invoices_table WHERE $invoices_table.id=$invoice_id))
                      )
                    ) ";
        }

        //find client_primary_contacts by invoice
        if (in_array("client_primary_contact", $notify_to_terms) && $invoice_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE $users_table.is_primary_contact=1 AND FIND_IN_SET($users_table.client_id, (SELECT $invoices_table.client_id FROM $invoices_table WHERE $invoices_table.id=$invoice_id))
                      )
                    ) ";
        }

        //find post creator
        if (in_array("post_creator", $notify_to_terms) && $post_id) {
            $where .= " OR ($users_table.id=(SELECT $posts_table.created_by FROM $posts_table WHERE $posts_table.id=(IF((SELECT $posts_table.post_id FROM $posts_table WHERE $posts_table.id=$post_id), (SELECT $posts_table.post_id FROM $posts_table WHERE $posts_table.id=$post_id), $post_id)))) ";
        }
        
        //find client_all_contacts by subscription
        if (in_array("client_all_contacts", $notify_to_terms) && $subscription_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE FIND_IN_SET($users_table.client_id, (SELECT $subscriptions_table.client_id FROM $subscriptions_table WHERE $subscriptions_table.id=$subscription_id))
                      )
                    ) ";
        }

        //find client_primary_contacts by subscription
        if (in_array("client_primary_contact", $notify_to_terms) && $subscription_id) {
            $where .= " OR FIND_IN_SET( $users_table.id, (
                        SELECT GROUP_CONCAT($users_table.id) AS contact_users FROM $users_table WHERE $users_table.is_primary_contact=1 AND FIND_IN_SET($users_table.client_id, (SELECT $subscriptions_table.client_id FROM $subscriptions_table WHERE $subscriptions_table.id=$subscription_id))
                      )
                    ) ";
        }


        $extra_where = "";
        if ($notify_to_admins_only) {
            //find only admin users if 'visible to admins only' is enabled
            $extra_where .= "AND $users_table.is_admin=1";
        }

        $notification_multiple_tasks_users = array();

        if ($notification_multiple_tasks) {
            $notification_multiple_tasks_users = get_notification_multiple_tasks_data($notification_multiple_tasks, $event, "user_ids");
            $notification_multiple_tasks_user_ids = get_array_value($notification_multiple_tasks_users, "notify_to_user_ids");
            if ($notification_multiple_tasks_user_ids) {
                $notification_multiple_tasks_user_ids = implode(',', $notification_multiple_tasks_user_ids);
                $extra_where .= " OR FIND_IN_SET( $users_table.id, '$notification_multiple_tasks_user_ids' )";
            }
        }

        $exclude_notification_creator = " AND $users_table.id!=$user_id ";

        //the nofication creator will also get notification for ticket created notification if the option is enabled
        //the notification creator and the ticket creator is same
        if ($event == "ticket_created" && in_array("ticket_creator", $notify_to_terms) && $ticket_id) {
            $exclude_notification_creator = "";
        }

        //find estimate creator
        if (in_array("estimate_creator", $notify_to_terms) && $estimate_id) {
            $where .= " OR ($users_table.id=(SELECT $estimates_table.created_by FROM $estimates_table WHERE $estimates_table.id = $estimate_id)) ";
        }

        //prepare where query from hook if exists
        try {
            $where_queries_from_hook = array();
            $where_queries_from_hook = app_hooks()->apply_filters('app_filter_create_notification_where_query', $where_queries_from_hook, array(
                "event" => $event,
                "user_id" => $user_id,
                "options" => $options,
                "notify_to_terms" => $notify_to_terms,
            ));

            if ($where_queries_from_hook && is_array($where_queries_from_hook)) {
                foreach ($where_queries_from_hook as $where_query_from_hook) {
                    $where .= $where_query_from_hook;
                }
            }
        } catch (\Exception $ex) {
            log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
        }

        $sql = "SELECT $users_table.id, $users_table.email, $users_table.enable_web_notification, $users_table.enable_email_notification, $users_table.user_type, $users_table.is_admin, $users_table.role_id, $users_table.language,
                    $roles_table.permissions
                FROM $users_table
                LEFT JOIN $roles_table ON $roles_table.id = $users_table.role_id AND $roles_table.deleted = 0
                WHERE $users_table.deleted=0 AND $users_table.status='active' $exclude_notification_creator AND ($users_table.enable_web_notification=1 OR $users_table.enable_email_notification =1 )  AND (1=2 $where) $extra_where";

        //echo $sql;
        $notify_to = $this->db->query($sql);

        //if it's a ticket related notification, we'll check the ticket type access permission for team members.
        $ticket_info = NULL;
        if ($ticket_id) {
            $ticket_info = $this->db->query("SELECT $tickets_table.* FROM $tickets_table WHERE $tickets_table.id=$ticket_id")->getRow();
        }

        //if it's a task related notification, we'll check the task access permission for team members.
        $task_info = NULL;
        if ($task_id) {
            $task_info = $this->db->query("SELECT $tasks_table.* FROM $tasks_table WHERE $tasks_table.id=$task_id")->getRow();
        }

        //if it's a project related notification, we'll check the project access permission for team members.
        $project_info = NULL;
        if ($project_id) {
            $project_info = $this->db->query("SELECT $projects_table.* FROM $projects_table WHERE $projects_table.id=$project_id")->getRow();
        }

        //if it's a estimate related notification, we'll check the estimate access permission for team members.
        $estimate_info = NULL;
        if ($estimate_id) {
            $estimate_info = $this->db->query("SELECT $estimates_table.* FROM $estimates_table WHERE $estimates_table.id=$estimate_id")->getRow();
        }

        //if it's a post related notification, we'll check the post access permission for team members.
        $post_info = NULL;
        if ($post_id) {
            $post_info = $this->db->query("SELECT $posts_table.* FROM $posts_table WHERE $posts_table.id=$post_id")->getRow();
        }

        //if it's a client/lead related notification, we'll check the client/lead access permission for team members.
        $client_info = NULL;
        if ($client_id || $lead_id) {
            $client_or_lead_id = $client_id ? $client_id : $lead_id;
            $client_info = $this->db->query("SELECT $clients_table.* FROM $clients_table WHERE $clients_table.id=$client_or_lead_id")->getRow();
        }

        $web_notify_to = "";
        $email_notify_to = array();

        //we've to send email specifically to the unknown client
        if (get_setting("enable_email_piping")) {
            //add creator's email
            //for ticket_commented notification, add creator's email if it's created by app users
            //for ticket_created notification, add creator's email if the option is enabled in notification settings
            if ($ticket_info && !$ticket_info->client_id && $ticket_info->creator_email &&
                    (($event == "ticket_commented" && !$exclude_ticket_creator) || ($event == "ticket_created" && in_array("ticket_creator", $notify_to_terms)))
            ) {
                $email_notify_to[] = $ticket_info->creator_email;
            }
        }

        if ($notify_to->resultID->num_rows) {
            foreach ($notify_to->getResult() as $user) {


                //check ticket type permission for team mebers before preparing the notifcation 
                if ($ticket_info && !$this->notify_to_this_user_for_this_ticket($ticket_info, $user)) {
                    continue; //skip next lines for this loop
                }

                //check task permission for team mebers before preparing the notifcation 
                if ($task_info && !$this->notify_to_this_user_for_this_task($task_info, $user)) {
                    continue; //skip next lines for this loop
                }

                //check project permission for team mebers before preparing the notifcation 
                if ($project_info && !$this->notify_to_this_user_for_this_project($project_info, $user)) {
                    continue; //skip next lines for this loop
                }

                //check estimate permission for team mebers before preparing the notifcation 
                if ($estimate_info && !$this->notify_to_this_user_for_this_estimate($estimate_info, $user)) {
                    continue; //skip next lines for this loop
                }

                //check post permission for team mebers before preparing the notifcation 
                if ($post_info && !$this->notify_to_this_user_for_this_post($post_info, $user)) {
                    continue; //skip next lines for this loop
                }

                //check client/lead permission for team mebers before preparing the notifcation 
                if ($client_info && !$this->notify_to_this_user_for_this_client($client_info, $user)) {
                    continue; //skip next lines for this loop
                }

                //prepare web notify to list
                if ($notification_settings->enable_web && $user->enable_web_notification) {
                    if ($web_notify_to) {
                        $web_notify_to .= ",";
                    }
                    $web_notify_to .= $user->id;
                }


                //prepare email notify to list
                if ($notification_settings->enable_email && $user->enable_email_notification) {

                    $email_notify_to[] = $user;
                }

                //check if email sending to client
                if ($user->enable_email_notification && $user->user_type == "client") {
                    $extra_data["email_sending_to_client"] = true;
                }
            }
        }


        $data = array(
            "user_id" => $user_id,
            "description" => "",
            "created_at" => get_current_utc_time(),
            "notify_to" => $web_notify_to,
            "read_by" => "",
            "event" => $event,
            "project_id" => $project_id ? $project_id : "",
            "task_id" => $task_id ? $task_id : "",
            "project_comment_id" => $project_comment_id ? $project_comment_id : "",
            "ticket_id" => $ticket_id ? $ticket_id : "",
            "ticket_comment_id" => $ticket_comment_id ? $ticket_comment_id : "",
            "project_file_id" => $project_file_id ? $project_file_id : "",
            "leave_id" => $leave_id ? $leave_id : "",
            "post_id" => $post_id ? $post_id : "",
            "to_user_id" => $to_user_id ? $to_user_id : "",
            "activity_log_id" => $activity_log_id ? $activity_log_id : "",
            "client_id" => $client_id ? $client_id : "",
            "invoice_payment_id" => $invoice_payment_id ? $invoice_payment_id : "",
            "invoice_id" => $invoice_id ? $invoice_id : "",
            "estimate_request_id" => $estimate_request_id ? $estimate_request_id : "",
            "estimate_id" => $estimate_id ? $estimate_id : "",
            "contract_id" => $contract_id ? $contract_id : "",
            "proposal_id" => $proposal_id ? $proposal_id : "",
            "order_id" => $order_id ? $order_id : "",
            "actual_message_id" => $actual_message_id ? $actual_message_id : "",
            "parent_message_id" => $parent_message_id ? $parent_message_id : "",
            "event_id" => $event_id ? $event_id : "",
            "announcement_id" => $announcement_id ? $announcement_id : "",
            "lead_id" => $lead_id ? $lead_id : "",
            "estimate_comment_id" => $estimate_comment_id ? $estimate_comment_id : "",
            "subscription_id" => $subscription_id ? $subscription_id : ""
        );

        //get data from plugin by persing 'plugin_'
        foreach ($options as $key => $value) {
            if (strpos($key, 'plugin_') !== false) {
                $data[$key] = $value;
            }
        }

        $notification_id = $this->ci_save($data);

        $extra_data["notify_to_terms"] = $notify_to_terms;

        if ($notification_multiple_tasks_users) {
            $extra_data["notification_multiple_tasks_user_wise"] = get_array_value($notification_multiple_tasks_users, "user_wise_tasks");
        }

        //notification saved. send emails
        if ($notification_id && $email_notify_to) {
            send_notification_emails($notification_id, $email_notify_to, $extra_data);
        }

        //send push notifications
        if ($web_notify_to && get_setting("enable_push_notification")) {
            //send push notifications to all web notifiy to users
            //but in receiving portal, it will be checked if the user disable push notification or not
            send_push_notifications($event, $web_notify_to, $user_id, $notification_id);
        }

        //send slack notifications
        $this->prepare_sending_slack_notification($event, $user_id, $notification_id, $notification_settings, $project_id);

        //send data to plugin hook
        app_hooks()->do_action('app_hook_post_notification', $notification_id);
    }

    private function prepare_announcement_receipients_query($announcements_table, $clients_table, $users_table, $announcement_id = 0) {
        if (!$announcement_id) {
            return false;
        }

        $where = "";

        $announcement_info = $this->db->query("SELECT $announcements_table.* FROM $announcements_table WHERE $announcements_table.id=$announcement_id")->getRow();

        $announcement_share_with = explode(",", $announcement_info->share_with);

        foreach ($announcement_share_with as $share_with) {
            if ($share_with === "all_members") {
                $where .= " OR ($users_table.user_type='staff' AND $users_table.status='active' AND $users_table.deleted=0)";
            }

            if ($share_with === "all_clients") {
                $where .= " OR ($users_table.user_type='client' AND $users_table.status='active' AND $users_table.deleted=0)";
            }

            if (strpos($share_with, 'cg') !== false) {
                $group_id = explode(":", $share_with);
                $group_id = get_array_value($group_id, 1);

                $where .= " OR ( FIND_IN_SET($group_id, (SELECT $clients_table.group_ids FROM $clients_table WHERE $clients_table.deleted=0 AND $clients_table.id=$users_table.client_id)) )";
            }
        }

        return $where;
    }

    private function prepare_sending_slack_notification($event, $user_id, $notification_id, $notification_settings, $project_id) {
        //first, check if the slack is enabled in notification settings
        if ($notification_settings->enable_slack) {
            //check the main settings
            //if it's a project related notification, check if it's restricted from sending projects related notifications
            if (get_setting("enable_slack") && get_setting("slack_webhook_url") && ($notification_settings->category !== "project" || ($notification_settings->category == "project" && !get_setting("slack_dont_send_any_projects")))) {
                send_slack_notification($event, $user_id, $notification_id, get_setting("slack_webhook_url"));
            }

            //for project related notification, check if there is any associated channel enabled for this project
            if ($notification_settings->category == "project" && $this->Project_settings_model->get_setting($project_id, "project_enable_slack") && $this->Project_settings_model->get_setting($project_id, "project_slack_webhook_url")) {
                send_slack_notification($event, $user_id, $notification_id, $this->Project_settings_model->get_setting($project_id, "project_slack_webhook_url"));
            }
        }
    }

    //if the ticket has been assigend to a team member, then only the assignee will get notificaiton
    //if the ticket is not been assigned, all allowed team members will get notification
    //client will always get notification

    private function notify_to_this_user_for_this_ticket($ticket_info, $user) {

        if ($user->user_type === "client") {
            return true; //we'll only check the ticket type access permission for staffs
        }


        //check who has access to this ticket and send notification
        if ($user->is_admin) {
            //user is an admin
            return true;
        } else if ($ticket_info->assigned_to === $user->id) {
            //assigne will get notification for a assigned ticket
            return true;
        } else {

            //check if user has permission to this ticket type
            $permissions = null;
            if ($user->permissions) {
                $permissions = unserialize($user->permissions);
                $permissions = is_array($permissions) ? $permissions : array();

                $ticket_permission = get_array_value($permissions, "ticket");

                if ($ticket_permission === "all") {
                    return true; //user has acces to all tickets
                } else if ($ticket_permission === "specific") {

                    //user has access to specific ticket types
                    $allowed_ticket_types = explode(",", get_array_value($permissions, "ticket_specific"));
                    if (in_array($ticket_info->ticket_type_id, $allowed_ticket_types)) {
                        return true;
                    }
                }
            }
        }
    }

    //if the user has the role to access only assigned tasks, s/he will get notification where s/he is assigned or collaborator
    //client will always get notification

    private function notify_to_this_user_for_this_task($task_info, $user) {
        if ($user->user_type === "staff" && !$user->is_admin) {
            $permissions = $user->permissions ? unserialize($user->permissions) : array();
            $permissions = is_array($permissions) ? $permissions : array();

            //check project permission
            $options = array(
                "id" => $task_info->project_id,
                "user_id" => $user->id
            );

            $Projects_model = model("App\Models\Projects_model");
            $project_info = $Projects_model->get_details($options)->getRow();
            if (!get_array_value($permissions, "can_manage_all_projects") && !$project_info) {
                return false;
            }

            //check if user has restriction to view all tasks
            $show_assigned_tasks_only = get_array_value($permissions, "show_assigned_tasks_only");
            if ($show_assigned_tasks_only) {
                //the user has permission to access only assigned tasks
                $collaborators_array = explode(',', $task_info->collaborators);
                if ($task_info->assigned_to != $user->id && !in_array($user->id, $collaborators_array)) {
                    return false;
                }
            }
        }

        return true; //other users or client will always get notification
    }

    //check if the user can access this project
    private function notify_to_this_user_for_this_project($project_info, $user) {
        if ($user->user_type === "staff" && !$user->is_admin) {
            $permissions = $user->permissions ? unserialize($user->permissions) : array();
            $permissions = is_array($permissions) ? $permissions : array();

            //check project permission
            $options = array(
                "id" => $project_info->id,
                "user_id" => $user->id
            );

            $Projects_model = model("App\Models\Projects_model");
            $project_info = $Projects_model->get_details($options)->getRow();
            if (!get_array_value($permissions, "can_manage_all_projects") && !$project_info) {
                return false;
            }
        }

        return true; //other users or client will always get notification
    }

    //if the user has the role to access only own estimates, s/he will get notification where s/he is the creator
    //client will always get notification
    private function notify_to_this_user_for_this_estimate($estimate_info, $user) {
        if ($user->user_type === "staff" && !$user->is_admin) {
            //check if user has restriction to view all estimates/no estimates at all
            if ($user->permissions) {
                $permissions = unserialize($user->permissions);
                $permissions = is_array($permissions) ? $permissions : array();

                $estimate_permission = get_array_value($permissions, "estimate");
                if (!$estimate_permission || ($estimate_permission === "own" && $estimate_info->created_by !== $user->id)) {
                    return false;
                }
            }
        }

        return true; //other users or client will always get notification
    }

    //check if the user has restriction on timeline
    private function notify_to_this_user_for_this_post($post_info, $user) {
        if ($user->user_type === "staff" && $user->permissions) {
            $permissions = unserialize($user->permissions);
            $permissions = is_array($permissions) ? $permissions : array();

            if (get_array_value($permissions, "timeline_permission") === "no") {
                //user doesn't have permission to access timeline at all
                return false;
            }

            $specific_permission = get_array_value($permissions, "timeline_permission_specific");
            if ($specific_permission) {
                $permissions = explode(",", $specific_permission);
                $allowed_members = prepare_allowed_members_array($permissions, $user->id);
                if ($allowed_members && !in_array($post_info->created_by, $allowed_members)) {
                    //user has partial access on timeline and can't see this post
                    return false;
                }
            }
        }

        return true; //otherwise the user will get notification
    }

    //if the user has the role to access only own clients/leads, s/he will get notification as role settings
    //client will always get notification

    private function notify_to_this_user_for_this_client($client_info, $user) {
        if ($user->user_type !== "staff") {
            return true;
        }

        if (!$user->permissions) {
            return true;
        }

        //check if user has restriction to view all clients/leads
        $permissions = unserialize($user->permissions);
        $permissions = is_array($permissions) ? $permissions : array();

        $client_permission = get_array_value($permissions, "client");
        if ($client_info->is_lead) {
            $client_permission = get_array_value($permissions, "lead");
        }

        if (!($user->is_admin || $client_permission)) {
            //the user isn't an admin and s/he doesn't have access on any client/lead
            return false;
        }

        if ($client_info->is_lead) {
            //check lead roles
            if ($client_permission == "own" && !($client_info->owner_id == $user->id || $client_info->created_by == $user->id)) {
                //have access to own only
                return false;
            }
        } else {
            //check client roles
            //role: 'no' is already checked before
            if ($client_permission == "all") {
                return true; //can access all clients
            } else if ($client_permission == "own") {
                if ($client_info->owner_id == $user->id || $client_info->created_by == $user->id) {
                    return true; //can access only own clients
                }
            } else if ($client_permission == "read_only") {
                return true; //can access notifications 
            } else if ($client_permission == "specific") {
                //user has access to specific client groups
                $allowed_client_groups = explode(",", get_array_value($permissions, "client_specific"));
                $client_group_ids = explode(",", $client_info->group_ids);
                foreach ($allowed_client_groups as $allowed_client_group_id) {
                    if (in_array($allowed_client_group_id, $client_group_ids)) {
                        return true;
                    }
                }
            }

            return false; //didn't match anything
        }

        return true; //other users or client will always get notification
    }

    /* prepare notifications of new events */

    function get_notifications($user_id, $offset = 0, $limit = 20) {
        $notifications_table = $this->db->prefixTable('notifications');
        $users_table = $this->db->prefixTable('users');
        $projects_table = $this->db->prefixTable('projects');
        $project_comments_table = $this->db->prefixTable('project_comments');
        $project_files_table = $this->db->prefixTable('project_files');
        $tasks_table = $this->db->prefixTable('tasks');
        $leave_applications_table = $this->db->prefixTable('leave_applications');
        $tickets_table = $this->db->prefixTable('tickets');
        $ticket_comments_table = $this->db->prefixTable('ticket_comments');
        $activity_logs_table = $this->db->prefixTable('activity_logs');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $posts_table = $this->db->prefixTable('posts');
        $invoices_table = $this->db->prefixTable('invoices');
        $clients_table = $this->db->prefixTable('clients');
        $events_table = $this->db->prefixTable('events');
        $announcements_table = $this->db->prefixTable('announcements');
        $contracts_table = $this->db->prefixTable('contracts');
        $estimates_table = $this->db->prefixTable('estimates');
        $proposals_table = $this->db->prefixTable('proposals');
        $estimate_comments_table = $this->db->prefixTable('estimate_comments');

        $sql = "SELECT SQL_CALC_FOUND_ROWS $notifications_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS user_name, $users_table.image AS user_image,
                 $projects_table.title AS project_title,
                 $project_comments_table.description AS project_comment_title,
                 $project_files_table.file_name AS project_file_title,
                 $contracts_table.title AS contract_title, $contracts_table.meta_data AS contract_meta_data,
                 $estimates_table.meta_data AS estimate_meta_data,
                 $proposals_table.meta_data AS proposal_meta_data,
                 $tasks_table.title AS task_title,
                 $events_table.title AS event_title,    
                 $tickets_table.title AS ticket_title,
                 $ticket_comments_table.description AS ticket_comment_description,
                 $posts_table.description AS posts_title,
                 $announcements_table.title AS announcement_title,
                 $estimate_comments_table.description AS estimate_comment_description,
                 $activity_logs_table.changes AS activity_log_changes, $activity_logs_table.log_type AS activity_log_type,
                 $leave_applications_table.start_date AS leave_start_date, $leave_applications_table.end_date AS leave_end_date,
                 $invoice_payments_table.invoice_id AS payment_invoice_id, $invoice_payments_table.amount AS payment_amount, (SELECT currency_symbol FROM $clients_table WHERE $clients_table.id=$invoices_table.client_id) AS client_currency_symbol,
                 (SELECT CONCAT($users_table.first_name, ' ', $users_table.last_name) FROM $users_table WHERE $users_table.id=$notifications_table.to_user_id) AS to_user_name,
                 FIND_IN_SET($user_id, $notifications_table.read_by) as is_read    
        FROM $notifications_table
        LEFT JOIN $projects_table ON $projects_table.id=$notifications_table.project_id
        LEFT JOIN $project_comments_table ON $project_comments_table.id=$notifications_table.project_comment_id
        LEFT JOIN $project_files_table ON $project_files_table.id=$notifications_table.project_file_id
        LEFT JOIN $tasks_table ON $tasks_table.id=$notifications_table.task_id
        LEFT JOIN $contracts_table ON $contracts_table.id=$notifications_table.contract_id
        LEFT JOIN $estimates_table ON $estimates_table.id=$notifications_table.estimate_id
        LEFT JOIN $proposals_table ON $proposals_table.id=$notifications_table.proposal_id
        LEFT JOIN $leave_applications_table ON $leave_applications_table.id=$notifications_table.leave_id
        LEFT JOIN $tickets_table ON $tickets_table.id=$notifications_table.ticket_id
        LEFT JOIN $ticket_comments_table ON $ticket_comments_table.id=$notifications_table.ticket_comment_id
        LEFT JOIN $posts_table ON $posts_table.id=$notifications_table.post_id
        LEFT JOIN $users_table ON $users_table.id=$notifications_table.user_id
        LEFT JOIN $activity_logs_table ON $activity_logs_table.id=$notifications_table.activity_log_id
        LEFT JOIN $invoice_payments_table ON $invoice_payments_table.id=$notifications_table.invoice_payment_id  
        LEFT JOIN $invoices_table ON $invoices_table.id=$notifications_table.invoice_id
        LEFT JOIN $events_table ON $events_table.id=$notifications_table.event_id
        LEFT JOIN $announcements_table ON $announcements_table.id=$notifications_table.announcement_id
        LEFT JOIN $estimate_comments_table ON $estimate_comments_table.id=$notifications_table.estimate_comment_id
        WHERE $notifications_table.deleted=0 AND FIND_IN_SET($user_id, $notifications_table.notify_to) != 0
        ORDER BY $notifications_table.id DESC LIMIT $offset, $limit";

        $data = new \stdClass();
        $data->result = $this->db->query($sql)->getResult();
        $data->found_rows = $this->db->query("SELECT FOUND_ROWS() as found_rows")->getRow()->found_rows;
        return $data;
    }

    function get_email_notification($notification_id) {
        $notifications_table = $this->db->prefixTable('notifications');
        $users_table = $this->db->prefixTable('users');
        $projects_table = $this->db->prefixTable('projects');
        $project_comments_table = $this->db->prefixTable('project_comments');
        $project_files_table = $this->db->prefixTable('project_files');
        $tasks_table = $this->db->prefixTable('tasks');
        $leave_applications_table = $this->db->prefixTable('leave_applications');
        $tickets_table = $this->db->prefixTable('tickets');
        $ticket_comments_table = $this->db->prefixTable('ticket_comments');
        $activity_logs_table = $this->db->prefixTable('activity_logs');
        $invoice_payments_table = $this->db->prefixTable('invoice_payments');
        $posts_table = $this->db->prefixTable('posts');
        $invoices_table = $this->db->prefixTable('invoices');
        $clients_table = $this->db->prefixTable('clients');
        $events_table = $this->db->prefixTable('events');
        $notification_settings_table = $this->db->prefixTable('notification_settings');
        $announcement_table = $this->db->prefixTable('announcements');
        $contracts_table = $this->db->prefixTable('contracts');
        $estimate_comments_table = $this->db->prefixTable('estimate_comments');
        $proposals_table = $this->db->prefixTable('proposals');

        $sql = "SELECT $notifications_table.*, CONCAT($users_table.first_name, ' ', $users_table.last_name) AS user_name,
                 $projects_table.title AS project_title,
                 $project_comments_table.description AS project_comment_title,
                 $project_files_table.file_name AS project_file_title,
                 $contracts_table.title AS contract_title, $contracts_table.public_key AS contract_public_key,
                 $tasks_table.title AS task_title,
                 $tasks_table.description AS task_description,
                 $events_table.title AS event_title,        
                 $tickets_table.title AS ticket_title,
                 $ticket_comments_table.description AS ticket_comment_description,
                 $posts_table.description AS posts_title,
                 $announcement_table.title AS announcement_title, $announcement_table.description AS announcement_content,
                 $estimate_comments_table.description AS estimate_comment_description,
                 $activity_logs_table.changes AS activity_log_changes, $activity_logs_table.log_type AS activity_log_type,
                 $leave_applications_table.start_date AS leave_start_date, $leave_applications_table.end_date AS leave_end_date,
                 $proposals_table.public_key AS proposal_public_key,
                 $invoice_payments_table.invoice_id AS payment_invoice_id, $invoice_payments_table.amount AS payment_amount, $invoice_payments_table.note AS manual_payment_note, (SELECT currency_symbol FROM $clients_table WHERE $clients_table.id=$invoices_table.client_id) AS client_currency_symbol,
                 (SELECT CONCAT($users_table.first_name, ' ', $users_table.last_name) FROM $users_table WHERE $users_table.id=$notifications_table.to_user_id) AS to_user_name,
                 $notification_settings_table.category 
        FROM $notifications_table
        LEFT JOIN $projects_table ON $projects_table.id=$notifications_table.project_id
        LEFT JOIN $project_comments_table ON $project_comments_table.id=$notifications_table.project_comment_id
        LEFT JOIN $project_files_table ON $project_files_table.id=$notifications_table.project_file_id
        LEFT JOIN $tasks_table ON $tasks_table.id=$notifications_table.task_id
        LEFT JOIN $leave_applications_table ON $leave_applications_table.id=$notifications_table.leave_id
        LEFT JOIN $tickets_table ON $tickets_table.id=$notifications_table.ticket_id
        LEFT JOIN $contracts_table ON $contracts_table.id=$notifications_table.contract_id
        LEFT JOIN $ticket_comments_table ON $ticket_comments_table.id=$notifications_table.ticket_comment_id
        LEFT JOIN $posts_table ON $posts_table.id=$notifications_table.post_id
        LEFT JOIN $users_table ON $users_table.id=$notifications_table.user_id
        LEFT JOIN $activity_logs_table ON $activity_logs_table.id=$notifications_table.activity_log_id
        LEFT JOIN $invoice_payments_table ON $invoice_payments_table.id=$notifications_table.invoice_payment_id 
        LEFT JOIN $invoices_table ON $invoices_table.id=$notifications_table.invoice_id
        LEFT JOIN $notification_settings_table ON $notification_settings_table.event=$notifications_table.event    
        LEFT JOIN $events_table ON $events_table.id=$notifications_table.event_id
        LEFT JOIN $announcement_table ON $announcement_table.id=$notifications_table.announcement_id
        LEFT JOIN $estimate_comments_table ON $estimate_comments_table.id=$notifications_table.estimate_comment_id
        LEFT JOIN $proposals_table ON $proposals_table.id=$notifications_table.proposal_id
        WHERE $notifications_table.id=$notification_id";

        return $this->db->query($sql)->getRow();
    }

    function count_notifications($user_id, $last_notification_checke_at = "0") {
        $notifications_table = $this->db->prefixTable('notifications');

        //we alos update the user's online status
        $users_table = $this->db->prefixTable('users');
        $now = get_current_utc_time();

        $this->db->query("UPDATE $users_table SET $users_table.last_online = '$now' WHERE $users_table.id=$user_id");

        //find notifications
        $sql = "SELECT COUNT($notifications_table.id) AS total_notifications
        FROM $notifications_table
        WHERE $notifications_table.deleted=0 AND FIND_IN_SET($user_id, $notifications_table.notify_to) != 0 AND FIND_IN_SET($user_id, $notifications_table.read_by) = 0
        AND timestamp($notifications_table.created_at)>timestamp('$last_notification_checke_at')";

        $result = $this->db->query($sql);
        if ($result->resultID->num_rows) {
            return $result->getRow()->total_notifications;
        }
    }

    /* update message ustats */

    function set_notification_status_as_read($notification_id, $user_id = 0) {
        $notifications_table = $this->db->prefixTable('notifications');

        $where = "";
        if ($notification_id) {
            $where = " AND $notifications_table.id=$notification_id";
        }

        $sql = "UPDATE $notifications_table SET $notifications_table.read_by = CONCAT($notifications_table.read_by,',',$user_id)
        WHERE FIND_IN_SET($user_id, $notifications_table.read_by) = 0 $where";
        return $this->db->query($sql);
    }

    function get_to_user_name($notification_id = 0) {
        $notifications_table = $this->db->prefixTable('notifications');
        $users_table = $this->db->prefixTable('users');

        $sql = "SELECT (SELECT CONCAT($users_table.first_name, ' ', $users_table.last_name) FROM $users_table WHERE $users_table.id=$notifications_table.to_user_id) AS to_user_name
        FROM $notifications_table
        WHERE $notifications_table.id=$notification_id";

        return $this->db->query($sql)->getRow()->to_user_name;
    }

}
