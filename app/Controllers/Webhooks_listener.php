<?php

namespace App\Controllers;

use App\Libraries\Stripe;

class Webhooks_listener extends App_Controller {

    function bitbucket($key) {
        //save bitbucket commit as a activity log of tasks by bitbucket webhook
        //the commit message should be ending with #task_id. ex: Added webhook #233
        $payloads = json_decode(file_get_contents('php://input'));
        if (!$this->_is_valid_payloads_of_bitbucket($payloads, $key)) {
            app_redirect("forbidden");
        }

        $final_commits_array = $this->_get_final_commits_of_bitbucket($payloads);

        if ($final_commits_array) {
            foreach ($final_commits_array as $commit) {
                $task_id = get_array_value($commit, "task_id");
                $task_info = $this->Tasks_model->get_one($task_id);
                if ($task_info->id) {
                    $log_data = array(
                        "action" => "bitbucket_notification_received",
                        "log_type" => "task",
                        "log_type_title" => $task_info->title,
                        "log_type_id" => $task_id,
                        "changes" => serialize(array("bitbucket" => array("from" => "", "to" => $commit))),
                        "log_for" => "project",
                        "log_for_id" => $task_info->project_id,
                    );

                    $save_id = $this->Activity_logs_model->ci_save($log_data, true);

                    if ($save_id) {
                        //send notification
                        $notification_options = array("project_id" => $task_info->project_id, "task_id" => $task_id, "activity_log_id" => $save_id, "user_id" => "999999998");
                        log_notification("bitbucket_push_received", $notification_options);
                    }
                }
            }
        }
    }

    private function _is_valid_payloads_of_bitbucket($payloads, $key) {
        $settings_key = get_setting("enable_bitbucket_commit_logs_in_tasks");
        if ($settings_key && $settings_key == $key && $payloads && $payloads->push) {
            return true;
        } else {
            return false;
        }
    }

    private function _get_final_commits_of_bitbucket($payloads) {
        $changes = get_array_value($payloads->push->changes, 0);
        if ($changes) {
            $repository_name = $payloads->repository->name;
            $branch_name = $changes->new->name;
            $author_name = $changes->new->target->author->user->display_name;
            $author_link = $changes->new->target->author->user->links->html->href;
            $commits = $changes->commits;

            $commits_description = array();
            foreach ($commits as $commit) {
                $commit_url = $commit->links->html->href;
                $commit_message = $commit->message;

                //get the task id 
                $position = strpos($commit_message, "#");
                $task_id = (int) substr($commit_message, $position + 1, strlen($commit_message));

                if (is_int($task_id) && $task_id) {
                    array_push($commits_description, array(
                        "task_id" => $task_id,
                        "commit_url" => $commit_url,
                        "commit_message" => $commit_message
                    ));
                }
            }

            $final_commits_array = array();
            foreach ($commits_description as $key => $value) {
                $task_id = (int) get_array_value($value, "task_id");
                if (is_int($task_id) && $task_id) {
                    if (!in_array($task_id, array_column($final_commits_array, 'task_id'))) {
                        array_push($final_commits_array, array(
                            "repository_name" => $repository_name,
                            "branch_name" => $branch_name,
                            "author_name" => $author_name,
                            "author_link" => $author_link,
                            "task_id" => $task_id,
                            "commits" => array(
                                array(
                                    "commit_url" => get_array_value($value, "commit_url"),
                                    "commit_message" => get_array_value($value, "commit_message")
                                )
                            )
                        ));
                    } else {
                        $commit = array_search($task_id, array_column($final_commits_array, 'task_id'));
                        array_push($final_commits_array[$commit]["commits"], array(
                            "commit_url" => get_array_value($value, "commit_url"),
                            "commit_message" => get_array_value($value, "commit_message")
                        ));
                    }
                }
            }

            return $final_commits_array;
        }
    }

    function github($key) {
        //save github commit as a activity log of tasks by github webhook
        //the commit message should be ending with #task_id. ex: Added webhook #233
        $payloads = json_decode(file_get_contents('php://input'));
        if (!$this->_is_valid_payloads_of_github($payloads, $key)) {
            app_redirect("forbidden");
        }

        $final_commits_array = $this->_get_final_commits_of_github($payloads);

        if ($final_commits_array) {
            foreach ($final_commits_array as $commit) {
                $task_id = get_array_value($commit, "task_id");
                $task_info = $this->Tasks_model->get_one($task_id);
                if ($task_info->id) {
                    $log_data = array(
                        "action" => "github_notification_received",
                        "log_type" => "task",
                        "log_type_title" => $task_info->title,
                        "log_type_id" => $task_id,
                        "changes" => serialize(array("github" => array("from" => "", "to" => $commit))),
                        "log_for" => "project",
                        "log_for_id" => $task_info->project_id,
                    );

                    $save_id = $this->Activity_logs_model->ci_save($log_data, true);

                    if ($save_id) {
                        //send notification
                        $notification_options = array("project_id" => $task_info->project_id, "task_id" => $task_id, "activity_log_id" => $save_id, "user_id" => "999999997");
                        log_notification("github_push_received", $notification_options);
                    }
                }
            }
        }
    }

    private function _is_valid_payloads_of_github($payloads, $key) {
        $settings_key = get_setting("enable_github_commit_logs_in_tasks");
        if ($settings_key && $settings_key == $key && $payloads) {
            return true;
        } else {
            return false;
        }
    }

    private function _get_final_commits_of_github($payloads) {
        $changes = $payloads->commits;
        if ($changes) {
            $repository_name = $payloads->repository->name;

            $branch_name = $payloads->ref;
            $branch_name = explode('/', $branch_name);
            $branch_name = end($branch_name);

            $first_commit = get_array_value($payloads->commits, 0);
            $author_name = $first_commit->author->name;
            $author_link = $payloads->sender->html_url;
            $commits = $changes;

            $commits_description = array();
            foreach ($commits as $commit) {
                $commit_url = $commit->url;
                $commit_message = $commit->message;

                //get the task id 
                $position = strpos($commit_message, "#");
                $task_id = (int) substr($commit_message, $position + 1, strlen($commit_message));

                if (is_int($task_id) && $task_id) {
                    array_push($commits_description, array(
                        "task_id" => $task_id,
                        "commit_url" => $commit_url,
                        "commit_message" => $commit_message
                    ));
                }
            }

            $final_commits_array = array();
            foreach ($commits_description as $key => $value) {
                $task_id = (int) get_array_value($value, "task_id");
                if (is_int($task_id) && $task_id) {
                    if (!in_array($task_id, array_column($final_commits_array, 'task_id'))) {
                        array_push($final_commits_array, array(
                            "repository_name" => $repository_name,
                            "branch_name" => $branch_name,
                            "author_name" => $author_name,
                            "author_link" => $author_link,
                            "task_id" => $task_id,
                            "commits" => array(
                                array(
                                    "commit_url" => get_array_value($value, "commit_url"),
                                    "commit_message" => get_array_value($value, "commit_message")
                                )
                            )
                        ));
                    } else {
                        $commit = array_search($task_id, array_column($final_commits_array, 'task_id'));
                        array_push($final_commits_array[$commit]["commits"], array(
                            "commit_url" => get_array_value($value, "commit_url"),
                            "commit_message" => get_array_value($value, "commit_message")
                        ));
                    }
                }
            }

            return $final_commits_array;
        }
    }

    function stripe_subscription() {
        try {
            $payloads = json_decode(file_get_contents('php://input'));
        } catch (\Exception $ex) {
            log_message('error', '[ERROR] {exception}', ['exception' => $ex]);
            exit();
        }

        if ($payloads->type === "invoice.payment_succeeded") {
            $this->subscription_payment_succeeded($payloads);
        }

        if ($payloads->type === "invoice.payment_failed") {
            $this->subscription_payment_failed($payloads);
        }
    }

    private function subscription_payment_succeeded($payloads) {
        $payloads_data = $payloads->data->object;
        if ($payloads_data->status !== "paid") {
            show_404();
        }

        $stripe_subscription_id = $payloads_data->subscription;
        if (!$stripe_subscription_id) {
            show_404();
        }

        $subscription_info = $this->Subscriptions_model->get_one_where(array("stripe_subscription_id" => $stripe_subscription_id));
        if (!$subscription_info->id) {
            show_404();
        }

        //get metadata
        $Stripe = new Stripe();
        $stripe_subscription_info = $Stripe->retrieve_subscription($stripe_subscription_id);
        $metadata = $stripe_subscription_info->metadata;

        //save payment
        $login_user = new \stdClass();
        $login_user->id = $metadata->contact_user_id;
        $login_user->user_type = "client";

        $invoice_id = create_invoice_from_subscription($subscription_info->id);

        $invoice_payment_data = array(
            "invoice_id" => $invoice_id,
            "payment_date" => get_current_utc_time(),
            "payment_method_id" => $metadata->payment_method_id,
            "note" => "",
            "amount" => $payloads_data->amount_paid / 100,
            "transaction_id" => $payloads_data->payment_intent,
            "created_at" => get_current_utc_time(),
            "created_by" => $login_user->id,
        );

        //check if already a payment done with this transaction
        $existing = $this->Invoice_payments_model->get_one_where(array("transaction_id" => $payloads_data->payment_intent));
        if ($existing->id) {
            show_404();
        }

        $invoice_payment_id = $this->Invoice_payments_model->ci_save($invoice_payment_data);
        if (!$invoice_payment_id) {
            show_404();
        }

        //as receiving payment for the invoice, we'll remove the 'draft' status from the invoice 
        $this->Invoices_model->update_invoice_status($invoice_id);

        log_notification("invoice_payment_confirmation", array("invoice_payment_id" => $invoice_payment_id, "invoice_id" => $invoice_id), "0");

        log_notification("invoice_online_payment_received", array("invoice_payment_id" => $invoice_payment_id, "invoice_id" => $invoice_id), $login_user->id);
    }

    private function subscription_payment_failed($payloads) {
        $payloads_data = $payloads->data->object;

        $stripe_subscription_id = $payloads_data->subscription;
        if (!$stripe_subscription_id) {
            show_404();
        }

        $subscription_info = $this->Subscriptions_model->get_one_where(array("stripe_subscription_id" => $stripe_subscription_id));
        if (!$subscription_info->id) {
            show_404();
        }

        $subscription_data = array(
            "payment_status" => "failed",
        );

        $this->Subscriptions_model->ci_save($subscription_data, $subscription_info->id);
    }

}

/* End of file Webhooks_listener.php */
/* Location: ./app/Controllers/Webhooks_listener.php */    