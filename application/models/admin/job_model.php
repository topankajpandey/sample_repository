<?php

class Job_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function countJob($user_type, $filter = false, $search_query = false) {
        if ($user_type == 2) {
            $this->db->select(DB_PREFIX . 'users.user_id, ' . DB_PREFIX . 'users.name, ' . DB_PREFIX . 'jobs.id, ' . DB_PREFIX . 'jobs.title, ' . DB_PREFIX . 'jobs.job_detail, ' . DB_PREFIX . 'jobs.job_status_id, ' . DB_PREFIX . 'jobs_status.name AS job_status_name, ' . DB_PREFIX . 'categories.name AS category_name');
            $this->db->from(DB_PREFIX . 'users');
            $this->db->join(DB_PREFIX . 'jobs', DB_PREFIX . 'users.user_id = ' . DB_PREFIX . 'jobs.posted_by_id');
            $this->db->join(DB_PREFIX . 'jobs_status', DB_PREFIX . 'jobs_status.id = ' . DB_PREFIX . 'jobs.job_status_id');
            $this->db->join(DB_PREFIX . 'categories', DB_PREFIX . 'jobs.category_id = ' . DB_PREFIX . 'categories.id');
//            $this->db->where(DB_PREFIX . 'users.customer_status', 1);
            $this->db->where(DB_PREFIX . 'users.customer_status = 1 OR '.DB_PREFIX.'users.is_customer = 1');
 
            if (isset($filter) && !empty($filter) && $filter != 'all') {
                $this->db->where(DB_PREFIX . 'jobs.job_status_id', $filter);
            }
            if (isset($search_query) && !empty($search_query)) {
                $where = '(' . DB_PREFIX . 'users.name LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'jobs.title LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'jobs.job_detail LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'categories.name LIKE "%' . mysql_real_escape_string($search_query) . '%")';
                $this->db->where($where);
            } 
 
            $this->db->order_by(DB_PREFIX . 'jobs.created', 'DESC');
            $query = $this->db->get(); 
            return $query->num_rows();
        } else {
            $this->db->select(DB_PREFIX . 'users.user_id, ' . DB_PREFIX . 'users.name, ' . DB_PREFIX . 'jobs.id, ' . DB_PREFIX . 'jobs.title, ' . DB_PREFIX . 'jobs.job_detail, ' . DB_PREFIX . 'jobs_status.name AS buddy_status_name, ' . DB_PREFIX . 'categories.name AS category_name');
            $this->db->from(DB_PREFIX . 'users');
            $this->db->join(DB_PREFIX . 'applied_by', DB_PREFIX . 'users.user_id = ' . DB_PREFIX . 'applied_by.applied_by_id');
            $this->db->join(DB_PREFIX . 'jobs', DB_PREFIX . 'jobs.id = ' . DB_PREFIX . 'applied_by.job_id');
            $this->db->join(DB_PREFIX . 'jobs_status', DB_PREFIX . 'jobs_status.id= ' . DB_PREFIX . 'applied_by.job_status_id');
            $this->db->join(DB_PREFIX . 'categories', DB_PREFIX . 'jobs.category_id = ' . DB_PREFIX . 'categories.id');
            $this->db->where(DB_PREFIX . 'users.buddy_status', 1);

            if (isset($filter) && !empty($filter) && $filter != 'all') {
                $this->db->where(DB_PREFIX . 'applied_by.job_status_id', $filter);
            }
            if (isset($search_query) && !empty($search_query)) {
                $where = '(' . DB_PREFIX . 'users.name LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'jobs.title LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'jobs.job_detail LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'categories.name LIKE "%' . mysql_real_escape_string($search_query) . '%")';
                $this->db->where($where);
            }

            $this->db->order_by(DB_PREFIX . 'applied_by.created', 'DESC');
            $query = $this->db->get();
            return $query->num_rows();
        }
    }

    public function getJobs($user_type, $limit = 20, $start = 1, $filter = false, $search_query = false) {

        $offset = $limit * ($start - 1);
        if ($user_type == 2) {
            $this->db->select(DB_PREFIX . 'users.user_id, ' . DB_PREFIX . 'users.name, ' . DB_PREFIX . 'jobs.id, ' . DB_PREFIX . 'jobs.title, ' . DB_PREFIX . 'jobs.job_detail, ' . DB_PREFIX . 'jobs.job_status_id, ' . DB_PREFIX . 'jobs_status.name AS job_status_name, ' . DB_PREFIX . 'categories.name AS category_name');
            $this->db->from(DB_PREFIX . 'users');
            $this->db->join(DB_PREFIX . 'jobs', DB_PREFIX . 'users.user_id = ' . DB_PREFIX . 'jobs.posted_by_id');
            $this->db->join(DB_PREFIX . 'jobs_status', DB_PREFIX . 'jobs_status.id = ' . DB_PREFIX . 'jobs.job_status_id');
            $this->db->join(DB_PREFIX . 'categories', DB_PREFIX . 'jobs.category_id = ' . DB_PREFIX . 'categories.id');
            $this->db->where(DB_PREFIX . 'users.customer_status = 1 OR '.DB_PREFIX.'users.is_customer = 1');

            if (isset($filter) && !empty($filter) && $filter != 'all') {
                $this->db->where(DB_PREFIX . 'jobs.job_status_id', $filter);
            }

            if (isset($search_query) && !empty($search_query)) {
                $where = '(' . DB_PREFIX . 'users.name LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'jobs.title LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'jobs.job_detail LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'categories.name LIKE "%' . mysql_real_escape_string($search_query) . '%")';
                $this->db->where($where);
            }

            $this->db->order_by(DB_PREFIX . 'jobs.created', 'DESC');
            $this->db->limit($limit, $offset);
            $query = $this->db->get();

            return $query->result_array();
        } else {
            $this->db->select(DB_PREFIX . 'users.user_id, ' . DB_PREFIX . 'users.name, ' . DB_PREFIX . 'jobs.id, ' . DB_PREFIX . 'jobs.title, ' . DB_PREFIX . 'jobs.job_detail, ' . DB_PREFIX . 'jobs_status.name AS buddy_status_name, ' . DB_PREFIX . 'categories.name AS category_name');
            $this->db->from(DB_PREFIX . 'users');
            $this->db->join(DB_PREFIX . 'applied_by', DB_PREFIX . 'users.user_id = ' . DB_PREFIX . 'applied_by.applied_by_id');
            $this->db->join(DB_PREFIX . 'jobs', DB_PREFIX . 'jobs.id = ' . DB_PREFIX . 'applied_by.job_id');
            $this->db->join(DB_PREFIX . 'jobs_status', DB_PREFIX . 'jobs_status.id = ' . DB_PREFIX . 'applied_by.job_status_id');
            $this->db->join(DB_PREFIX . 'categories', DB_PREFIX . 'jobs.category_id = ' . DB_PREFIX . 'categories.id');
            $this->db->where(DB_PREFIX . 'users.buddy_status', 1);

            if (isset($filter) && !empty($filter) && $filter != 'all') {
                $this->db->where(DB_PREFIX . 'applied_by.job_status_id', $filter);
            }

            if (isset($search_query) && !empty($search_query)) {
                $where = '(' . DB_PREFIX . 'users.name LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'jobs.title LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'jobs.job_detail LIKE "%' . mysql_real_escape_string($search_query) . '%" OR ' . DB_PREFIX . 'categories.name LIKE "%' . mysql_real_escape_string($search_query) . '%")';
                $this->db->where($where);
            }

            $this->db->order_by(DB_PREFIX . 'applied_by.created', 'DESC  ');
            $this->db->limit($limit, $offset);
            $query = $this->db->get();
            return $query->result_array();
        }
    }
    
    

    public function ViewJobDetail($user_type, $job_id, $user_id) {

        if ($user_type == 1) {
            $this->db->select(DB_PREFIX . 'users.name, ' . DB_PREFIX . 'users.profile_pic, ' . DB_PREFIX . 'jobs.id, ' . DB_PREFIX . 'jobs.title, ' . DB_PREFIX . 'jobs.job_detail, ' . DB_PREFIX . 'categories.name AS category_name, ' . DB_PREFIX . 'jobs_status.name AS job_status_name, ' . DB_PREFIX . 'jobs.created AS job_created, ' . DB_PREFIX . 'applied_by.job_start_time AS start_date, ' . DB_PREFIX . 'applied_by.job_end_time  AS  end_date, ' . DB_PREFIX . 'applied_by.created AS job_applied');
            $this->db->from(DB_PREFIX . 'users');
            $this->db->join(DB_PREFIX . 'applied_by', DB_PREFIX . 'users.user_id = ' . DB_PREFIX . 'applied_by.applied_by_id');
            
            $this->db->join(DB_PREFIX . 'jobs', DB_PREFIX . 'jobs.id = ' . DB_PREFIX . 'applied_by.job_id');
            $this->db->join(DB_PREFIX . 'categories', DB_PREFIX . 'categories.id = ' . DB_PREFIX . 'jobs.category_id');
            $this->db->join(DB_PREFIX . 'jobs_status', DB_PREFIX . 'jobs_status.id = ' . DB_PREFIX . 'applied_by.job_status_id');
            $this->db->where(array(DB_PREFIX . 'applied_by.job_id' => $job_id, DB_PREFIX . 'applied_by.applied_by_id' => $user_id));
            $query = $this->db->get();
           //pr($query->result_array());die;
            return $query->result_array();
        } else {
            $this->db->select(DB_PREFIX . 'users.name, ' . DB_PREFIX . 'users.profile_pic, ' . DB_PREFIX . 'jobs.id, ' . DB_PREFIX . 'jobs.title, ' . DB_PREFIX . 'jobs.job_detail, ' . DB_PREFIX . 'categories.name AS category_name, ' . DB_PREFIX . 'jobs_status.name AS job_status_name ,' . DB_PREFIX . 'jobs.created AS job_created, ' . DB_PREFIX . 'jobs.job_start_time as start_date, ' . DB_PREFIX . 'jobs.job_end_time AS end_date');
            $this->db->from(DB_PREFIX . 'users');
            $this->db->join(DB_PREFIX . 'jobs', DB_PREFIX . 'users.user_id = ' . DB_PREFIX . 'jobs.posted_by_id');
            $this->db->join(DB_PREFIX . 'applied_by', DB_PREFIX . 'jobs.id = ' . DB_PREFIX . 'applied_by.job_id', 'left');
            $this->db->join(DB_PREFIX . 'categories', DB_PREFIX . 'categories.id = ' . DB_PREFIX . 'jobs.category_id');
            $this->db->join(DB_PREFIX . 'jobs_status', DB_PREFIX . 'jobs_status.id = ' . DB_PREFIX . 'jobs.job_status_id');
            $this->db->where(DB_PREFIX . 'jobs.id', $job_id);
            $query = $this->db->get();
            return $query->row_array();
        }
    }

    public function assignedBuddy($job_id) {

        $this->db->select(DB_PREFIX . 'users.user_id, ' . DB_PREFIX . 'users.name');
        $this->db->from(DB_PREFIX . 'users');
        $this->db->join(DB_PREFIX . 'applied_by', DB_PREFIX . 'users.user_id = ' . DB_PREFIX . 'applied_by.applied_by_id');
        $this->db->where(DB_PREFIX . 'applied_by.job_id', $job_id);
        $this->db->where_in(DB_PREFIX . 'applied_by.job_status_id', array(2, 3, 4, 7, 8,10));
        $query = $this->db->get();
        //echo $this->db->last_query();
        return $query->result_array();
    }
 
    public function getNotificationCronStatus($job_id, $customer_id, $buddie_id){
        $query = $this->db->query("SELECT  `tbl_cron_status`.`status` FROM `tbl_cron_status` where `job_id` = '$job_id' and `customer_id` = '$customer_id' and `buddie_id` = '$buddie_id'");
        if($query->num_rows()>0){
            return $query->row()->status;
        }     
        return false;
    }  
    
    public function updateNotificationCronStatus($job_id, $status, $customer_id, $buddie_id){
        $query = $this->db->query("update `tbl_cron_status` set `tbl_cron_status`.`status` = '$status' where `job_id` = '$job_id'  and `customer_id` = '$customer_id' and `buddie_id` = '$buddie_id' ");
        return $query;
    }
    
     
    public function getReminderForJobNotification(){
        $query = $this->db->query("SELECT  `tbl_users`.`name` ,  `tbl_jobs`.`id` AS  `job_id` ,  `tbl_users`.`user_type` ,  `tbl_users`.`user_id` AS  `customer_id` ,  `tbl_applied_by`.`applied_by_id` AS  `buddie_id` ,  `tbl_jobs`.`title` ,  `tbl_jobs`.`job_start_time` FROM ( `tbl_users` ) LEFT JOIN  `tbl_jobs` ON  `tbl_users`.`user_id` =  `tbl_jobs`.`posted_by_id`  LEFT JOIN  `tbl_applied_by` ON  `tbl_jobs`.`id` =  `tbl_applied_by`.`job_id`  WHERE  `tbl_jobs`.`job_status_id` = 2 LIMIT 0 , 30");
       
        if($query->num_rows()>0){
            return  $query->result();
        }     
        return false;
    }
    
} 
 