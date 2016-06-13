<?php

class Favewebservicemodel extends CI_Model {

    public function saveUserInformation($user_info) {
        if ($this->db->insert('tbl_users', $user_info)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function save_userdevice($userdevice_info) {
        if ($this->db->insert('tbl_device', $userdevice_info)) {
            return TRUE;
        }
        return FALSE;
    }

    public function validateOtp($user_id, $otp_password, $user_type, $is_contact) {
        if ($is_contact) {
            if ($user_type == 1) {
                $data = array('buddy_status' => 1, 'otp_password' => "", 'contact_update' => 0);
            } else {
                $data = array('customer_status' => 1, 'otp_password' => "", 'contact_update' => 0);
            }
        } else {
            if ($user_type == 1) {
                $data = array('buddy_status' => 2, 'otp_password' => "");
            } else {
                $data = array('customer_status' => 1, 'otp_password' => "");
            }
        }
        $this->db->where(array('otp_password' => $otp_password, 'user_id' => $user_id));
        $this->db->update('tbl_users', $data);
        return TRUE;
    }

    public function login($email = '', $password = '') {
        $this->db->where("email = '$email'  AND password = md5('$password')");
        $query = $this->db->get('tbl_users');
        $result = $query->row_array();
        if (count($result)) {
            return $result;
        }
        return false;
    }

    /*
     * Model to delete Device Id 
     */

    public function deleteDeviceId($device_id) {
        $this->db->delete('tbl_device', array('device_id' => $device_id));
        return TRUE;
    }

    public function updateFbId($user_id, $updatefbId) {
        $this->db->where('user_id', $user_id);
        if ($this->db->update('tbl_users', $updatefbId)) {

            return TRUE;
        }
        return FALSE;
    }

    public function isEmailIdExist($email) {
        $this->db->select('*');
        $this->db->from('tbl_users');
        $this->db->where(array('email' => $email));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function saveProfile($user_id, $profile_array) {
        $this->db->where('user_id', $user_id);
        if ($this->db->update('tbl_users', $profile_array)) {
            return TRUE;
        }
        return FALSE;
    }

    public function logoutUser($device_id) {
        if ($this->db->delete('tbl_device', array('device_id' => $device_id))) {
            return true;
        } else {
            return false;
        }
    }

    public function saveOtp($user_id, $otpsent) {
        $this->db->where('user_id', $user_id);
        if ($this->db->update('tbl_users', array('otp_password' => $otpsent))) {
            return TRUE;
        }
        return FALSE;
    }

    public function updatedContact($user_id, $otpsent, $contactUpdate) {
        $this->db->where('user_id', $user_id);
        if ($this->db->update('tbl_users', array('otp_password' => $otpsent, 'contact_update' => $contactUpdate))) {
            return TRUE;
        }
        return FALSE;
    }

    public function resetPassword($user_id, $new_password) {
        $this->db->where('user_id', $user_id);
        $this->db->update('tbl_users', array('password' => $new_password));
// return $this->db->affected_rows();
        return TRUE;
    }

    public function jobCategoriesListingData() {
        $this->db->select('*');
        $this->db->from('tbl_categories');
        $this->db->where(array('status' => 1));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllCountry() {
        $this->db->select('*');
        $this->db->from('tbl_countries');
        $this->db->where(array('status' => 1));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllState($country_id) {
        $this->db->select('*');
        $this->db->from('tbl_state');
        $this->db->where(array('country_id' => $country_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllCity($state_id) {
        $this->db->select('*');
        $this->db->from('tbl_state');
        $this->db->where(array('state_id' => $state_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function saveFavJobPostData($job) {
        if ($this->db->insert('tbl_jobs', $job)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function getAllJobs($user_id, $zip_code = NULL) {

        $appliedArr = $this->getJobsAppliedByUser($user_id);
        $declinedArr = $this->getJobsDeclinedByUser($user_id);
        $exclude = array_merge($appliedArr, $declinedArr);
        // $this->db->select('tbl_jobs.*,tbl_users.name,tbl_users.profile_pic,tbl_users.working_status,tbl_users.about_me,tbl_jobs_status.name as job_status');
        $this->db->select('tbl_jobs.*, tbl_users.name, tbl_users.profile_pic, tbl_users.about_me, tbl_users.contact, tbl_jobs_status.name as job_status, tbl_categories.name as categories_name');
        $this->db->from("tbl_jobs");
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->join('tbl_jobs_status', 'tbl_jobs.job_status_id = tbl_jobs_status.id', 'left');
        if ($exclude) {
            $this->db->where_not_in('tbl_jobs.id', $exclude);
        }

        $this->db->where('tbl_jobs.posted_by_id != ', $user_id);
        $this->db->where('tbl_jobs.job_status_id', 1);
        if (isset($zip_code) && !empty($zip_code)) {
            $this->db->like(array('tbl_jobs.zip_code' => $zip_code));
        }
        $this->db->order_by("tbl_jobs.created", "desc");
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getJobsAppliedByUser($user_id) {
        $responseArr = (array) null;
        $this->db->select("job_id");
        $this->db->from("tbl_applied_by");
        $this->db->where(array("applied_by_id" => $user_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $item) {
                $responseArr[] = $item['job_id'];
            }
        }
        return $responseArr;
    }

    public function getJobsDeclinedByUser($user_id) {
        $responseArr = (array) null;
        $this->db->select("job_id");
        $this->db->from("tbl_declined_by");
        $this->db->where(array("declined_by_id" => $user_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            foreach ($query->result_array() as $item) {
                $responseArr[] = $item['job_id'];
            }
        }
        return $responseArr;
    }

    public function jobApplied($appledArray) {
        if ($this->db->insert('tbl_applied_by', $appledArray)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function jobDeclined($declined_by_id) {
        if ($this->db->insert('tbl_declined_by', $declined_by_id)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function getAllCustomerJobStatus($posted_by_id) {
        $this->db->select('tbl_jobs.*, tbl_jobs_status.name as job_status, tbl_categories.price, tbl_categories.name as categories_name');
        $this->db->from('tbl_jobs');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->join('tbl_jobs_status', 'tbl_jobs.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->order_by("tbl_jobs.created", "desc");
        $where = "(posted_by_id='$posted_by_id' ) AND (tbl_jobs.job_status_id='2' OR tbl_jobs.job_status_id='10' OR tbl_jobs.job_status_id='1' OR tbl_jobs.job_status_id='7')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllAppliedUserData($job_id) {

        $this->db->select("tbl_applied_by.applied_by_id, tbl_applied_by.job_id, tbl_users.name, tbl_users.profile_pic, tbl_users.about_me, tbl_users.contact");
        $this->db->from("tbl_applied_by");
        $this->db->join('tbl_users', 'tbl_applied_by.applied_by_id = tbl_users.user_id', 'left');
        $this->db->order_by("tbl_applied_by.created", "desc");
        $this->db->where(array("job_status_id" => 1, 'job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllHiredUserData($job_id) {

        $this->db->select("tbl_applied_by.applied_by_id, tbl_applied_by.job_id, tbl_users.name,tbl_users.profile_pic,tbl_users.about_me, tbl_users.contact, tbl_jobs_status.name AS job_status_now");
        $this->db->from("tbl_applied_by");
        $this->db->join('tbl_users', 'tbl_applied_by.applied_by_id = tbl_users.user_id', 'left');
        $this->db->join('tbl_jobs_status', 'tbl_jobs_status.id = tbl_applied_by.job_status_id', 'left');
        $this->db->order_by("tbl_applied_by.created", "desc");
        $where = "(job_id='$job_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4' OR tbl_applied_by.job_status_id='8' OR tbl_applied_by.job_status_id='2' OR tbl_applied_by.job_status_id='10')";
        $this->db->where($where);
        // $this->db->where(array("job_status_id" => 8, 'job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllHiredUserDataClose($job_id) {
        $this->db->select("tbl_applied_by.applied_by_id, tbl_applied_by.job_id, tbl_users.name, tbl_users.profile_pic,tbl_users.about_me");
        $this->db->from("tbl_applied_by");
        $this->db->join('tbl_users', 'tbl_applied_by.applied_by_id = tbl_users.user_id', 'left');
        $this->db->order_by("tbl_applied_by.created", "desc");
        $where = "(job_id='$job_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4' OR tbl_applied_by.job_status_id='2')";
        $this->db->where($where);
        // $this->db->where(array("job_status_id" => 8, 'job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllHiredUserDataTotal($job_id) {
        $this->db->select("tbl_applied_by.applied_by_id,tbl_applied_by.job_id,tbl_users.name,tbl_users.profile_pic,");
        $this->db->from("tbl_applied_by");
        $this->db->join('tbl_users', 'tbl_applied_by.applied_by_id = tbl_users.user_id', 'left');
        $this->db->order_by("tbl_applied_by.created", "desc");
        $where = "(job_id='$job_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4' OR tbl_applied_by.job_status_id='8')";
        $this->db->where($where);
        // $this->db->where(array("job_status_id" => 8, 'job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->num_rows();
        } else {
            return array();
        }
    }

    // function to display hired buddie avg rating
    // $user_id => buddie's id
    public function getRating($user_id) {

        $this->db->select_avg('rating');
        $this->db->from('tbl_buddy_ratings');
        $this->db->where('tbl_buddy_ratings.rating_to', $user_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $rating_array = $query->row_array();
            return $rating_array['rating'];
        } else {
            return "";
        }
    }

    // function to get review
    public function getReview($user_id) {

        $this->db->select('tbl_buddy_ratings.feedback, tbl_users.profile_pic, tbl_users.name');
        $this->db->from('tbl_buddy_ratings');
        $this->db->join('users', 'tbl_buddy_ratings.rating_by = tbl_users.user_id');
        $this->db->where('tbl_buddy_ratings.rating_to', $user_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return "";
    }

    public function getAvgRatingCustomer($user_id) {
        $this->db->select_avg('rating');
        $this->db->from('tbl_customer_ratings');
        $this->db->where(array('tbl_customer_ratings.rating_to' => $user_id));
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $rating_array = $query->row_array();
            return $rating_array['rating'];
        } else {
            return "";
        }
    }

    // new function to get customer review
    public function getReviewCustomer($user_id) {

        $this->db->select('tbl_customer_ratings.feedback, tbl_users.profile_pic, tbl_users.name');
        $this->db->from('tbl_customer_ratings');
        $this->db->join('users', 'tbl_customer_ratings.rating_by = tbl_users.user_id');
        $this->db->where('tbl_customer_ratings.rating_to', $user_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return "";
    }

    public function getRatingtoCustomer($user_id, $job_id) {
        $this->db->select_avg('rating');
        $this->db->from('tbl_customer_ratings');
        $this->db->where(array('tbl_customer_ratings.rating_by' => $user_id, 'tbl_customer_ratings.job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $rating_array = $query->row_array();
            return $rating_array['rating'];
        } else {
            return "";
        }
    }

    public function declinedBuddyJob($applied_by_id, $job_id) {
        $this->db->where(array('applied_by_id' => $applied_by_id, 'job_id' => $job_id));
        if ($this->db->update('tbl_applied_by', array('job_status_id' => 5))) {
            return TRUE;
        }
        return FALSE;
    }

    public function buddyCount($job_id) {
        $this->db->select('how_many_buddy');
        $this->db->from('tbl_jobs');
        $this->db->where('id', $job_id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            $numberCount = $query->row_array();
            return $numberCount['how_many_buddy'];
        }
    }

    public function hiredBuddyCount($job_id) {
        $this->db->select('applied_by_id');
        $this->db->from('tbl_applied_by');
        $this->db->where(array('job_id' => $job_id, 'job_status_id' => 8));
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function hiredBuddyCountforAccept($job_id) {
        $this->db->select('applied_by_id');
        $this->db->from('tbl_applied_by');
        $where = "(job_id='$job_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id = '4' OR tbl_applied_by.job_status_id = '2' OR tbl_applied_by.job_status_id = '10' OR tbl_applied_by.job_status_id = '7' OR tbl_applied_by.job_status_id = '8')";
        $this->db->where($where);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function runningBuddyCount($job_id) {

        $this->db->select('applied_by_id');
        $this->db->from('tbl_applied_by');
        $where = "(job_id = $job_id) AND (job_status_id = 2 OR job_status_id = 10)";
        $this->db->where($where);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function changeBuddyStatus($applied_by_id, $job_id) {
        $this->db->where(array('applied_by_id' => $applied_by_id, 'job_id' => $job_id));
        if ($this->db->update('tbl_applied_by', array('job_status_id' => 8))) {
            return TRUE;
        }
        return FALSE;
    }

//    public function changeBuddyStatusToRunning($applied_by_id, $job_id, $job_status) {
//        $this->db->where(array('applied_by_id' => $applied_by_id, 'job_id' => $job_id));
//        if ($this->db->update('tbl_applied_by', array('job_status_id' => $job_status, 'job_start_time' => date('Y-m-d H:i:s')))) {
//            return TRUE;
//        }
//        return FALSE;
//    }

    public function changeBuddyStatusToRunning($applied_by_id, $job_id, $job_status, $currentTime) {
        $this->db->where(array('applied_by_id' => $applied_by_id, 'job_id' => $job_id));
        if ($this->db->update('tbl_applied_by', array('job_status_id' => $job_status, 'job_start_time' => $currentTime, 'resume_time' => $currentTime))) {
            return TRUE;
        }
        return FALSE;
    }

//    public function changeBuddyStatusToClose($applied_by_id, $job_id, $job_status) {
//        $this->db->where(array('applied_by_id' => $applied_by_id, 'job_id' => $job_id));
//        if ($this->db->update('tbl_applied_by', array('job_status_id' => $job_status, 'job_end_time' => date('Y-m-d H:i:s')))) {
//            return TRUE;
//        }
//        return FALSE;
//    }
    public function changeBuddyStatusToClose($applied_by_id, $job_id, $job_status, $currentTime, $amount) {

        $this->db->where(array('applied_by_id' => $applied_by_id, 'job_id' => $job_id));
        if ($this->db->update('tbl_applied_by', array('job_status_id' => $job_status, 'job_end_time' => $currentTime, 'amount' => $amount))) {
            return TRUE;
        }
        return FALSE;
    }

    public function changejobStatus1($job_id) {

        $this->db->where(array('id' => $job_id));
        if ($this->db->update('tbl_jobs', array('job_status_id' => 7))) {
            return TRUE;
        }
        return FALSE;
    }

    public function changejobStatus($job_id) {
        $this->db->where(array('id' => $job_id));
        if ($this->db->update('tbl_jobs', array('job_status_id' => 2))) {
            return TRUE;
        }
        return FALSE;
    }

    public function freeUser($applied_by_id) {
        $this->db->where(array('user_id' => $applied_by_id));
        if ($this->db->update('tbl_users', array('working_status' => 1))) {
            return TRUE;
        }
        return FALSE;
    }

    public function busyUser($applied_by_id) {
        $this->db->where(array('user_id' => $applied_by_id));
        if ($this->db->update('tbl_users', array('working_status' => 3))) {
            return TRUE;
        }
        return FALSE;
    }

    public function changejobStatusToClose($job_id) {

        $this->db->where(array('id' => $job_id));
        if ($this->db->update('tbl_jobs', array('job_status_id' => 3))) {
            return TRUE;
        }
        return FALSE;
    }

    public function changejobStatusToCancel($job_id) {
        $this->db->where(array('id' => $job_id));
        if ($this->db->update('tbl_jobs', array('job_status_id' => 9))) {
            return TRUE;
        }
        return FALSE;
    }

    public function changejobStatusToRunning($job_id) {
        $this->db->where(array('id' => $job_id));
        if ($this->db->update('tbl_jobs', array('job_status_id' => 2))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getAllCustomerHistoryJob($posted_by_id) {

        $this->db->select('tbl_jobs.*,tbl_jobs_status.name as job_status, tbl_categories.name');
        $this->db->from('tbl_jobs');
        $this->db->join('tbl_jobs_status', 'tbl_jobs.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');

        //  $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');

        $where = "(posted_by_id='$posted_by_id' ) AND (tbl_jobs.job_status_id='3' OR tbl_jobs.job_status_id='4')";
        // $where = "(posted_by_id='$posted_by_id' ) AND (tbl_jobs.job_status_id='2' OR tbl_jobs.job_status_id='3' OR tbl_jobs.job_status_id='4')";
        $this->db->where($where);
        $this->db->order_by("tbl_jobs.job_end_time", "desc");
        // $this->db->where(array("posted_by_id" => $posted_by_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

//    public function totalWorkingHour($applied_by_id, $job_id) {
//        $this->db->select("tbl_applied_by.job_start_time,tbl_applied_by.job_end_time");
//        $this->db->from("tbl_applied_by");
//        $where = "(job_id='$job_id' ) AND (applied_by_id='$applied_by_id' ) AND(tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4' OR tbl_applied_by.job_status_id='8')";
//        $this->db->where($where);
//        $query = $this->db->get();
//        if ($query->num_rows()) {
//            return $query->row_array();
//        } else {
//            return '';
//        }
//    }

    public function totalWorkingHour($applied_by_id, $job_id) {
        $this->db->select("*");
        $this->db->from("tbl_applied_by");
        $where = "(job_id='$job_id' ) AND (applied_by_id='$applied_by_id' ) AND(tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return '';
        }
    }

    // function to get the amount on customer job history
    public function totalAmount($applied_by_id, $job_id) {

        $this->db->select('amount');
        $this->db->from("tbl_applied_by");
        $where = "(job_id='$job_id' ) AND (applied_by_id='$applied_by_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return '';
        }
    }

//    public function totalWorkingHour1($applied_by_id, $job_id) {
//        $this->db->select("tbl_applied_by.job_start_time,tbl_applied_by.job_end_time");
//        $this->db->from("tbl_applied_by");
//        $where = "(job_id='$job_id' ) AND (applied_by_id='$applied_by_id' ) AND(tbl_applied_by.job_status_id='2')";
//        $this->db->where($where);
//        $query = $this->db->get();
//        if ($query->num_rows()) {
//            return $query->row_array();
//        } else {
//            return '';
//        }
//    }

    public function totalWorkingHour1($applied_by_id, $job_id) {

        $this->db->select("*");
        $this->db->from("tbl_applied_by");
        $where = "(job_id='$job_id' ) AND (applied_by_id='$applied_by_id' ) AND(tbl_applied_by.job_status_id='2')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return '';
        }
    }

    public function totalWorkingHourCustomer($applied_by_id, $job_id) {
        $this->db->select("tbl_applied_by.job_start_time,tbl_applied_by.job_end_time");
        $this->db->from("tbl_applied_by");
        $where = "(job_id='$job_id' ) AND (applied_by_id='$applied_by_id' ) AND(tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4' OR tbl_applied_by.job_status_id='8' OR tbl_applied_by.job_status_id='2')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return '';
        }
    }

    public function CategoryPriceRate($job_id) {

        $this->db->select('tbl_categories.price');
        $this->db->from('tbl_jobs');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->where(array("tbl_jobs.id" => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return '';
        }
    }

    public function getAllCustomerHelpyJob($posted_by_id) {
        $this->db->select('tbl_jobs.*,tbl_jobs_status.name as job_status');
        $this->db->from('tbl_jobs');
        $this->db->join('tbl_jobs_status', 'tbl_jobs.job_status_id = tbl_jobs_status.id', 'left');
        //  $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->order_by("tbl_jobs.created", "desc");
        $where = "(posted_by_id='$posted_by_id' ) AND (tbl_jobs.job_status_id='2' OR tbl_jobs.job_status_id='3' OR tbl_jobs.job_status_id='4')";
        $this->db->where($where);
        // $this->db->where(array("posted_by_id" => $posted_by_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return array();
        }
    }

    public function getAllCustomerHelpyJobnew($posted_by_id) {

        $this->db->select('tbl_jobs.*, tbl_jobs_status.name as job_status');
        $this->db->from('tbl_jobs');
        $this->db->join('tbl_jobs_status', 'tbl_jobs.job_status_id = tbl_jobs_status.id', 'left');
        //  $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->order_by("tbl_jobs.created", "desc");
        $where = "(posted_by_id='$posted_by_id' ) AND (tbl_jobs.job_status_id='2' OR tbl_jobs.job_status_id = '10')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

//    public function getAllRunningBuddyJob($applied_by_id) {
//        $this->db->select('tbl_applied_by.applied_by_id, tbl_applied_by.job_start_time, tbl_jobs.posted_by_id, tbl_users.name, tbl_users.profile_pic, tbl_users.about_me, tbl_jobs.*, tbl_jobs_status.name as job_status, tbl_categories.price, tbl_categories.name as categories_name');
//        $this->db->from('tbl_applied_by');
//        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
//        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
//        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
//        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
//        $this->db->where(array('tbl_applied_by.applied_by_id' => $applied_by_id, 'tbl_applied_by.job_status_id' => 2));
//        $query = $this->db->get();
//        if ($query->num_rows()) {
//            return $query->row_array();
//        } else {
//            return array();
//        }
//    }

    public function getAllRunningBuddyJob($applied_by_id) {
        $this->db->select('tbl_applied_by.applied_by_id, tbl_applied_by.working_hour, tbl_applied_by.pau_job_status, tbl_applied_by.pause_time, tbl_applied_by.resume_time, tbl_applied_by.job_start_time, tbl_jobs.posted_by_id, tbl_users.name, tbl_users.profile_pic, tbl_users.about_me, tbl_users.contact, tbl_jobs.*, tbl_jobs_status.name as job_status, tbl_categories.price, tbl_categories.name as categories_name');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $where = "(tbl_applied_by.applied_by_id = $applied_by_id) AND (tbl_applied_by.job_status_id = 2 OR tbl_applied_by.job_status_id = 10)";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return array();
        }
    }

//    public function getAllBuddyHistoryJob($applie_by_id) {
//        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.job_start_time as buddy_start_time,tbl_applied_by.job_end_time as buddy_end_time,tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_categories.price,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me');
//        $this->db->from('tbl_applied_by');
//        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
//        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
//        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
//        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
//        $this->db->order_by("tbl_applied_by.job_end_time", "desc");
//        $where = "(tbl_applied_by.applied_by_id='$applie_by_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4')";
//        $this->db->where($where);
//        // $this->db->where(array("posted_by_id" => $posted_by_id));
//        $query = $this->db->get();
//        if ($query->num_rows()) {
//            return $query->result_array();
//        } else {
//            return array();
//        }
//    }

    public function getAllBuddyHistoryJob($applie_by_id) {

        $this->db->select('tbl_applied_by.applied_by_id, tbl_applied_by.working_hour, tbl_applied_by.pau_job_status, tbl_applied_by.pause_time, tbl_applied_by.resume_time, tbl_applied_by.job_start_time as buddy_start_time, tbl_applied_by.job_end_time as buddy_end_time, tbl_applied_by.amount, tbl_jobs_status.name as job_status, tbl_jobs.*, tbl_categories.price, tbl_users.name, tbl_users.profile_pic, tbl_users.about_me');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->order_by("tbl_applied_by.job_end_time", "desc");
        $where = "(tbl_applied_by.applied_by_id='$applie_by_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getRetingToBuddy($applie_by_id, $job_id) {
        $this->db->select('rating');
        $this->db->from('tbl_buddy_ratings');
        $this->db->where(array('tbl_buddy_ratings.rating_to' => $applie_by_id, 'tbl_buddy_ratings.job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $rating_array = $query->row_array();
            return $rating_array['rating'];
        } else {
            return "";
        }
    }

    public function getAllBuddyHelpJob($applie_by_id) {
        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.job_start_time,tbl_applied_by.job_end_time,tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_categories.price,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->order_by("tbl_applied_by.job_id", "desc");
        $where = "(tbl_applied_by.applied_by_id='$applie_by_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4'OR tbl_applied_by.job_status_id='2')";
        $this->db->where($where);
        // $this->db->where(array("posted_by_id" => $posted_by_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return "";
        }
    }

//    public function getAllBuddyHelpJobrunning($applie_by_id) {
//        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.job_start_time as buddy_start_time,tbl_applied_by.job_end_time as buddy_end_time,tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_categories.price,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me');
//        $this->db->from('tbl_applied_by');
//        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
//        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
//        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
//        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
//        $this->db->order_by("tbl_applied_by.job_start_time", "desc");
//        $this->db->where(array(
//            'tbl_applied_by.applied_by_id' => $applie_by_id,
//            'tbl_applied_by.job_status_id' => 2,
//        ));
//
//
//        // $this->db->where(array("posted_by_id" => $posted_by_id));
//        $query = $this->db->get();
//        if ($query->num_rows()) {
//            return $query->row_array();
//        } else {
//            return "";
//        }
//    }

    public function getAllBuddyHelpJobrunning($applie_by_id) {
        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.working_hour,tbl_applied_by.pau_job_status,tbl_applied_by.pause_time,tbl_applied_by.resume_time,tbl_applied_by.job_start_time as buddy_start_time,tbl_applied_by.job_end_time as buddy_end_time,tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_categories.price,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->order_by("tbl_applied_by.job_start_time", "desc");
        $this->db->where(array(
            'tbl_applied_by.applied_by_id' => $applie_by_id,
            'tbl_applied_by.job_status_id' => 2,
        ));


        // $this->db->where(array("posted_by_id" => $posted_by_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return "";
        }
    }

//    public function getAllBuddyHelpJobClose($applie_by_id) {
//        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.job_start_time as buddy_start_time,tbl_applied_by.job_end_time as buddy_end_time,tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_categories.price,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me');
//        $this->db->from('tbl_applied_by');
//        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
//        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
//        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
//        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
//        $this->db->order_by("tbl_applied_by.job_end_time", "desc");
//        $where = "(tbl_applied_by.applied_by_id='$applie_by_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4')";
//        $this->db->where($where);
////        $this->db->where(array(
////            'tbl_applied_by.applied_by_id' => $applie_by_id,
////            'tbl_applied_by.job_status_id' =>4,
////        ));
//        // $this->db->where(array("posted_by_id" => $posted_by_id));
//        $query = $this->db->get();
//        if ($query->num_rows()) {
//            return $query->row_array();
//        } else {
//            return "";
//        }
//    }

    public function getAllBuddyHelpJobClose($applie_by_id) {
        $this->db->select('tbl_applied_by.applied_by_id, tbl_applied_by.working_hour, tbl_applied_by.pau_job_status, tbl_applied_by.pause_time, tbl_applied_by.resume_time, tbl_applied_by.job_start_time as buddy_start_time, tbl_applied_by.job_end_time as buddy_end_time, tbl_applied_by.amount, tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_categories.price,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->order_by("tbl_applied_by.job_end_time", "desc");
        $where = "(tbl_applied_by.applied_by_id='$applie_by_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4')";
        $this->db->where($where);
//        $this->db->where(array(
//            'tbl_applied_by.applied_by_id' => $applie_by_id,
//            'tbl_applied_by.job_status_id' =>4,
//        ));
        // $this->db->where(array("posted_by_id" => $posted_by_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return "";
        }
    }

    public function getAllBuddyHelpJobComplete($applie_by_id) {
        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.job_start_time,tbl_applied_by.job_end_time,tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_categories.price,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->order_by("tbl_applied_by.job_id", "desc");
        $where = "(tbl_applied_by.applied_by_id='$applie_by_id' ) AND (tbl_applied_by.job_status_id='3' OR tbl_applied_by.job_status_id='4'OR tbl_applied_by.job_status_id='2')";
        $this->db->where($where);
        // $this->db->where(array("posted_by_id" => $posted_by_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return "";
        }
    }

    public function saveRatingToCustomer($ratingData) {
        if ($this->db->insert('tbl_customer_ratings', $ratingData)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function saveRatingToBuddy($ratingData) {
        if ($this->db->insert('tbl_buddy_ratings', $ratingData)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function saveAnswer($anserData, $tablename) {
        if ($this->db->insert($tablename, $anserData)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function buddyAcceptedJobData($user_id) {

        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.job_start_time,tbl_applied_by.job_end_time,tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me, tbl_users.contact, tbl_categories.name as categories_name');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->order_by('tbl_applied_by.created', 'DESC');
        $this->db->where(array('tbl_applied_by.job_status_id' => 8, 'tbl_applied_by.applied_by_id' => $user_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return '';
        }
    }

    public function getAllNotification($user_id, $type, $user_type, $skip, $limit) {

        $this->db->select('*');
        $this->db->from('tbl_notifications');
        $this->db->where(array('tbl_notifications.user_type' => $user_type, 'tbl_notifications.user_id' => $user_id,));
        if (isset($type) && !empty($type)) {
            $this->db->where(array('tbl_notifications.status' => $type));
        }
        $this->db->order_by('tbl_notifications.created', 'DESC');
        $this->db->limit($limit, $skip);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    // function to add bedge count start here

    public function addBedgeCount($user_id, $user_type) {

        $this->db->select('id');
        $this->db->from(DB_PREFIX . 'bedge_count');
        $this->db->where(array('user_id' => $user_id, 'user_type' => $user_type));
        $query = $this->db->get();
        $rows = $query->num_rows();
        if ($rows) {
            $this->db->set('count', 'count+1', FALSE);
            $this->db->where(array('user_id' => $user_id, 'user_type' => $user_type));
            if ($this->db->update(DB_PREFIX . 'bedge_count')) {
                $this->db->select('count as bedge_count');
                $this->db->from(DB_PREFIX . 'bedge_count');
                $this->db->where(array('user_id' => $user_id, 'user_type' => $user_type));
                $query = $this->db->get();
                return $query->row_array();
            }
            return false;
        } else {
            if ($this->db->insert(DB_PREFIX . 'bedge_count', array('user_id' => $user_id, 'user_type' => $user_type, 'count' => 1))) {
                $this->db->select('count as bedge_count');
                $this->db->from(DB_PREFIX . 'bedge_count');
                $this->db->where(array('user_id' => $user_id, 'user_type' => $user_type));
                $query = $this->db->get();
                return $query->row_array();
            }
            return false;
        }
    }

//    public function getAllNotificationCount($user_id, $type, $user_type) {
//        $this->db->select('*');
//        $this->db->from('tbl_notifications');
//        $this->db->order_by('tbl_notifications.created', 'DESC');
//        $this->db->where(array('tbl_notifications.user_type' => $user_type, 'tbl_notifications.user_id' => $user_id,));
//        if (isset($type) && !empty($type)) {
//            $this->db->where(array('tbl_notifications.status' => $type));
//        }
//
//        $query = $this->db->get();
//        if ($query->num_rows()) {
//            return $query->$query->num_rows();
//        } else {
//            return FALSE;
//        }
//    }
//    public function update_allNotification($user_id, $user_type) {
//        $this->db->where(array('tbl_notifications.user_id' => $user_id, 'tbl_notifications.user_type' => $user_type));
//        $this->db->update('tbl_notifications', array('status' => 0));
//        return true;
//    }
    public function update_allNotification($user_id, $user_type) {
        $this->db->where(array('tbl_notifications.user_id' => $user_id, 'tbl_notifications.user_type' => $user_type));
        $this->db->update('tbl_notifications', array('status' => 0));
        return true;
    }

//    public function updateStartTimeJob($job_id) {
//        $this->db->where(array('tbl_jobs.id' => $job_id));
//        $this->db->update('tbl_jobs', array('job_start_time' => date('Y-m-d H:i:s')));
//        return true;
//    }

    public function updateStartTimeJob($job_id, $currentTime) {
        $this->db->where(array('tbl_jobs.id' => $job_id));
        $this->db->update('tbl_jobs', array('job_start_time' => $currentTime));
        return true;
    }

//    public function updateCloseTimeJob($job_id) {
//        $this->db->where(array('tbl_jobs.id' => $job_id));
//        $this->db->update('tbl_jobs', array('job_end_time' => date('Y-m-d H:i:s')));
//        return true;
//    }
    public function updateCloseTimeJob($job_id, $currentTime) {
        $this->db->where(array('tbl_jobs.id' => $job_id));
        $this->db->update('tbl_jobs', array('job_end_time' => $currentTime));
        return true;
    }

    public function jobDetail($job_id) {

        $this->db->select('*');
        $this->db->from('tbl_jobs');
        $this->db->where(array('tbl_jobs.id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return '';
        }
    }

    public function updateLatLng($user_id, $lat, $lng) {
        $this->db->where(array('tbl_users.user_id' => $user_id));
        $this->db->update('tbl_users', array('lat' => $lat, 'lng' => $lng));
        return true;
    }

    public function getUserIdDeviceToken() {
        $this->db->select('tbl_device.*,tbl_users.lat,tbl_users.lng');
        $this->db->from('tbl_device');
        $this->db->join('tbl_users', 'tbl_device.user_id = tbl_users.user_id', 'left');
        $this->db->where(array('tbl_users.buddy_status' => 1));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return '';
        }
    }

    public function saveNotification($notificationArray) {
        if ($this->db->insert('tbl_notifications', $notificationArray)) {
            return TRUE;
        }
        return FALSE;
    }

    public function getUserWorkingStatus($user_id) {
        $this->db->select('working_status');
        $this->db->from('tbl_users');
        $this->db->where(array('tbl_users.user_id' => $user_id));
        // $this->db->where(array("posted_by_id" => $posted_by_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return '';
        }
    }

    public function deletePreviousHiredDetail($job_id) {
        $this->db->delete('tbl_applied_by', array('job_id' => $job_id, 'job_status_id' => 8));
        return TRUE;
    }

    public function cancelBuddyStatus($job_id) {
        $where = "(tbl_applied_by.job_id='$job_id') AND (tbl_applied_by.job_status_id='8' OR tbl_applied_by.job_status_id='1' OR  tbl_applied_by.job_status_id='7')";
        $this->db->where($where);
        $this->db->update('tbl_applied_by', array('job_status_id' => 9));
//        $this->db->delete('tbl_applied_by', array('job_id' => $job_id, 'job_status_id' => 8));
//        return TRUE;
    }

    public function closedBuddyJobData($user_id, $job_id) {

        $this->db->select('tbl_applied_by.applied_by_id, tbl_applied_by.working_hour, tbl_applied_by.job_id, tbl_applied_by.job_start_time, tbl_applied_by.job_end_time, tbl_jobs_status.name as job_status, tbl_jobs.*, tbl_users.name, tbl_users.profile_pic, tbl_users.about_me, tbl_categories.price');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_users', 'tbl_applied_by.applied_by_id = tbl_users.user_id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->order_by('tbl_applied_by.created', 'DESC');
        $this->db->where(array('tbl_applied_by.job_id' => $job_id, 'tbl_applied_by.applied_by_id' => $user_id));

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return FALSE;
    }

//    public function closedBuddyJobDataDetail($user_id, $job_id) {
//        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.job_id,tbl_applied_by.job_start_time as buddy_start_time,tbl_applied_by.job_end_time as buddy_end_time,tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me,tbl_categories.price');
//        $this->db->from('tbl_applied_by');
//        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
//        $this->db->join('tbl_users', 'tbl_applied_by.applied_by_id = tbl_users.user_id', 'left');
//        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
//        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
//        $this->db->order_by('tbl_applied_by.created', 'DESC');
//        $this->db->where(array('tbl_applied_by.job_id' => $job_id, 'tbl_applied_by.applied_by_id' => $user_id));
//
//        $query = $this->db->get();
//        if ($query->num_rows()) {
//            return $query->row_array();
//        } else {
//            return FALSE;
//        }
//    }

    public function closedBuddyJobDataDetail($user_id, $job_id) {

        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.job_id,tbl_applied_by.working_hour,tbl_applied_by.job_start_time as buddy_start_time, tbl_applied_by.job_end_time as buddy_end_time, tbl_applied_by.amount, tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me,tbl_categories.price');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_users', 'tbl_applied_by.applied_by_id = tbl_users.user_id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->order_by('tbl_applied_by.created', 'DESC');
        $this->db->where(array('tbl_applied_by.job_id' => $job_id, 'tbl_applied_by.applied_by_id' => $user_id));

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return FALSE;
        }
    }

    public function closedCustomerJobData($user_id, $job_id) {
        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.job_id,tbl_applied_by.job_start_time,tbl_applied_by.job_end_time,tbl_jobs_status.name as job_status,tbl_jobs.*,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me,tbl_categories.price');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->order_by('tbl_applied_by.created', 'DESC');
        $this->db->where(array('tbl_applied_by.job_id' => $job_id, 'tbl_applied_by.applied_by_id' => $user_id));

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return FALSE;
        }
    }

    public function getAllNotificationCount($user_id, $type, $user_type) {
        $this->db->select('*');
        $this->db->from('tbl_notifications');
        $this->db->order_by('tbl_notifications.created', 'DESC');
        $this->db->where(array('tbl_notifications.user_type' => $user_type, 'tbl_notifications.user_id' => $user_id,));
        if (isset($type) && !empty($type)) {
            $this->db->where(array('tbl_notifications.status' => $type));
        }

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllUserNear($lat, $lng) {
//       $query = $this->db->query("SELECT tbl_users.user_id, tbl_device.device_id, ( 3959 * acos( cos( radians( 37 ) ) * cos( radians( $lat ) ) * cos( radians( $lng) - radians( -122 ) ) + sin( radians( 37 ) ) * sin( radians( $lat ) ) ) ) AS distance
//        FROM tbl_users
//        JOIN tbl_device ON tbl_users.user_id = tbl_device.user_id
//        where is_buddy=1
//        HAVING distance <10000
//        ORDER BY distance ");
//       $query = $this->db->query("SELECT tbl_users.user_id, tbl_device.device_id,((ACOS(SIN($lat * PI() / 180) * SIN(tbl_users.lat * PI() / 180) + COS($lat * PI() / 180) * COS(tbl_users.lat * PI() / 180) * COS(($lng – tbl_users.lng) * PI() / 180)) * 180 / PI()) * 60 * 1.1515) AS `distance` FROM tbl_users JOIN tbl_device ON tbl_users.user_id = tbl_device.user_id where is_buddy=1 HAVING distance < 10000 ORDER BY distance ");
        $query = $this->db->query("SELECT tbl_users.user_id, tbl_device.device_id,(((acos(sin((" . $lat . "*pi()/180)) * sin((`tbl_users`.`lat`*pi()/180))+cos((" . $lat . "*pi()/180)) * cos((`tbl_users`.`lat`*pi()/180)) * cos(((" . $lng . "- `tbl_users`.`lng`)* pi()/180))))*180/pi())*60*1.1515) as distance FROM `tbl_users` JOIN `tbl_device` ON `tbl_users`.`user_id` = `tbl_device`.`user_id` HAVING distance < 25");
//       echo $this->db->last_query();die;
        return $query->result_array();
    }

    public function closeBuddyCount($job_id) {
        $this->db->select('applied_by_id');
        $this->db->from('tbl_applied_by');
        $this->db->where(array('job_id' => $job_id, 'job_status_id' => 3));
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function getAllQuestionToBuddy() {
        $this->db->select('id,question');
        $this->db->from('tbl_question_to_buddy');
        $this->db->where(array('tbl_question_to_buddy.status' => 1));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllQuestionToCustomer() {
        $this->db->select('id,question');
        $this->db->from('tbl_question_to_customer');
        $this->db->where(array('tbl_question_to_customer.status' => 1));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllClosedBuddy($job_id) {

        $this->db->select('tbl_applied_by.applied_by_id, tbl_applied_by.job_id, tbl_applied_by.job_start_time as applied_job_start_time, tbl_applied_by.job_end_time as applied_job_end_time, tbl_applied_by.working_hour, tbl_applied_by.amount, tbl_jobs_status.name as job_status, tbl_jobs.*, tbl_users.name, tbl_users.profile_pic, tbl_users.about_me, tbl_categories.price');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_users', 'tbl_applied_by.applied_by_id = tbl_users.user_id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->order_by('tbl_applied_by.created', 'DESC');
        $this->db->where(array('tbl_applied_by.job_id' => $job_id, 'tbl_applied_by.job_status_id' => 3));

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

//    public function changeAllBuddyStatusToClose($job_id) {
//        $this->db->where(array('tbl_applied_by.job_id' => $job_id, 'job_status_id' => 2));
//        $this->db->update('tbl_applied_by', array('job_status_id' => 3, 'job_end_time' => date('Y-m-d H:i:s')));
//        return true;
//    }
//    
    // changed made dipanshu

    public function amountUpdate($job_id, $applied_by_id, $amount) {

        $where = "(tbl_applied_by.job_id = $job_id) AND (tbl_applied_by.applied_by_id = $applied_by_id) AND (tbl_applied_by.job_status_id = 2 OR tbl_applied_by.job_status_id = 10)";
        $this->db->where($where);
        $this->db->update('tbl_applied_by', array('amount' => $amount));
        return true;
    }

    public function changeAllBuddyStatusToClose($job_id, $currentTime) {

        $where = "(tbl_applied_by.job_id = $job_id) AND (tbl_applied_by.job_status_id = 2 OR tbl_applied_by.job_status_id = 10)";
        $this->db->where($where);
        $this->db->update('tbl_applied_by', array('job_status_id' => 3, 'job_end_time' => $currentTime));
        return true;
    }

    public function getAllCancelUserData($job_id) {

        $this->db->select('tbl_applied_by.applied_by_id');
        $this->db->from('tbl_applied_by');
        $this->db->where(array('tbl_applied_by.job_id' => $job_id, 'tbl_applied_by.job_status_id' => 9));

        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
            //$query->result_array();
        } else {
            return array();
        }
    }

    public function updateIsCustomer($user_id) {

        $this->db->where(array('user_id' => $user_id));
        $this->db->update('tbl_users', array('is_customer' => 1));
        return true;
    }

    public function updateIsCustomerPayment($user_id) {

        $this->db->where(array('user_id' => $user_id));
        $this->db->update('tbl_users', array('is_customer' => 1, 'payment_type' => 2));
        return true;
    }

    public function updateIsBuddie($user_id) {

        $this->db->where(array('user_id' => $user_id));
        $this->db->update('tbl_users', array('is_buddy' => 1));
        return true;
    }

    public function updateBuddyRequiredDetail($user_info, $user_id) {

        $this->db->where(array('user_id' => $user_id));
        $this->db->update('tbl_users', $user_info);
        return true;
    }

    public function getJobStatus($job_id) {

        $this->db->select('job_status_id');
        $this->db->from('tbl_jobs');
        $where = "(tbl_jobs.id='$job_id' ) AND (tbl_jobs.job_status_id='3' OR tbl_jobs.job_status_id='9')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return FALSE;
        }
    }

    public function listAllAppliedJobBuddyJob($user_id) {

        $this->db->select('tbl_applied_by.applied_by_id, tbl_applied_by.job_id, tbl_applied_by.job_start_time as applied_job_start_time, tbl_applied_by.job_end_time as applied_job_end_time, tbl_jobs_status.name as job_status, tbl_jobs.*, tbl_users.name, tbl_users.profile_pic, tbl_users.about_me, tbl_users.contact, tbl_categories.name as categories_name');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_jobs_status', 'tbl_applied_by.job_status_id = tbl_jobs_status.id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $this->db->join('tbl_users', 'tbl_jobs.posted_by_id = tbl_users.user_id', 'left');
        $this->db->where(array('tbl_applied_by.applied_by_id' => $user_id, 'tbl_applied_by.job_status_id' => 1));
        $this->db->order_by('tbl_applied_by.created', 'DESC');
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function jobTableJobStatus($job_id) {
        $this->db->select('job_status_id');
        $this->db->from('tbl_jobs');
        $where = "(tbl_jobs.id='$job_id' ) AND (tbl_jobs.job_status_id='3')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return FALSE;
        }
    }

    public function saveReceipt($receptData) {
        if ($this->db->insert('tbl_payment_receive', $receptData)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function getAppliedCloseJobStatus($job_id) {
        $this->db->select('job_status_id');
        $this->db->from('tbl_applied_by');
        $where = "(tbl_applied_by.id='$job_id' ) AND (tbl_applied_by.job_status_id='3')";
        $this->db->where($where);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function changeBuddyStatusToComplete($job_id, $buddy_id) {
        $this->db->where(array('job_id' => $job_id, 'applied_by_id' => $buddy_id));
        if ($this->db->update('tbl_applied_by', array('job_status_id' => 4))) {
            return TRUE;
        }
        return FALSE;
    }

    public function changeJobStatusToComplete() {
        $this->db->where(array('id' => $job_id));
        if ($this->db->update('tbl_jobs', array('job_status_id' => 4))) {
            return TRUE;
        }
        return FALSE;
    }

    public function deleteNotification($notification_id) {
        $this->db->delete('tbl_notifications', array('id' => $notification_id));
        return TRUE;
    }

    public function getAllJobId($user_id) {

        $this->db->select('*');
        $this->db->from('tbl_jobs');
        $where = "(tbl_jobs.posted_by_id='$user_id' ) AND (tbl_jobs.job_status_id='3' OR tbl_jobs.job_status_id='2' OR tbl_jobs.job_status_id='1')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getAllClosedApplidBuddy($job_id) {
        $this->db->select('tbl_applied_by.applied_by_id,tbl_applied_by.job_id,tbl_applied_by.job_status_id as buddy_job_status_id,tbl_applied_by.job_start_time as buddy_job_start_time,tbl_applied_by.job_end_time as buddy_job_end_time,tbl_users.name,tbl_users.profile_pic,tbl_users.about_me,tbl_categories.price');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_users', 'tbl_applied_by.applied_by_id = tbl_users.user_id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $where = "(tbl_applied_by.job_id='$job_id') AND (tbl_applied_by.job_status_id='3')";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
            //$query->result_array();
        } else {
            return array();
        }
    }

    public function getAllClosedApplidBuddyData($job_id) {
        $usersHavingPayment = $this->getUserHavingPayment($job_id);
        $usersHavingRating = $this->getUserHavingRating($job_id);
        $userHavingRatingPayment = array_intersect($usersHavingPayment, $usersHavingRating);
        $userHavingRatingPaymentUn = array_unique($userHavingRatingPayment);
        $this->db->select('tbl_applied_by.applied_by_id, tbl_applied_by.working_hour, tbl_applied_by.job_id, tbl_applied_by.job_status_id as buddy_job_status_id,tbl_applied_by.job_start_time as buddy_job_start_time, tbl_applied_by.job_end_time as buddy_job_end_time, tbl_applied_by.amount, tbl_users.name,tbl_users.profile_pic,tbl_users.about_me,tbl_categories.price');
        $this->db->from('tbl_applied_by');
        $this->db->join('tbl_users', 'tbl_applied_by.applied_by_id = tbl_users.user_id', 'left');
        $this->db->join('tbl_jobs', 'tbl_applied_by.job_id = tbl_jobs.id', 'left');
        $this->db->join('tbl_categories', 'tbl_jobs.category_id = tbl_categories.id', 'left');
        $where = "(tbl_applied_by.job_id='$job_id') AND (tbl_applied_by.job_status_id='3')";
        $this->db->where($where);
        if ($userHavingRatingPaymentUn) {
            $this->db->where_not_in('tbl_applied_by.applied_by_id', $userHavingRatingPaymentUn);
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        } else {
            return array();
        }
    }

    public function getUserHavingRating($job_id) {
        $this->db->select('rating_to');
        $this->db->from('tbl_buddy_ratings');
        $this->db->where(array('tbl_buddy_ratings.job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            $response = (array) null;
            foreach ($query->result_array() as $item) {
                $response[] = $item['rating_to'];
            }
            return $response;
        } else {
            return array();
        }
    }

    public function getUserHavingPayment($job_id) {
        $this->db->select('payment_to_id');
        $this->db->from('tbl_payment_receive');
        $this->db->where(array('tbl_payment_receive.job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            $response = (array) null;
            foreach ($query->result_array() as $item) {
                $response[] = $item['payment_to_id'];
            }
            return $response;
        } else {
            return array();
        }
    }

    public function switchedToBuddy($user_id, $switch_to_buddy) {
        $this->db->where(array('user_id' => $user_id));
        if ($this->db->update('tbl_users', array('switch_to_buddy' => $switch_to_buddy))) {
            return TRUE;
        }
        return FALSE;
    }

    public function checkPaymentPendingStatus($job_id, $payment_to) {
        $this->db->select('*');
        $this->db->from('tbl_payment_receive');
        $this->db->where(array('payment_to_id' => $payment_to, 'job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->num_rows();
            //$query->result_array();
        } else {
            return FALSE;
        }
    }

    public function checkPayment($job_id, $applied_by_id) {

        $this->db->select('*');
        $this->db->from('tbl_payment_receive');
        $this->db->where(array('payment_to_id' => $applied_by_id, 'job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->num_rows();
        }
        return FALSE;
    }

    public function checkRatingPendingStatus($job_id, $rating_to) {
        $this->db->select('*');
        $this->db->from('tbl_buddy_ratings');
        $this->db->where(array('rating_to' => $rating_to, 'job_id' => $job_id));
        $query = $this->db->get();
//        echo $this->db->last_query();
//        die();
        if ($query->num_rows()) {
            return $query->num_rows();
            //$query->result_array();
        } else {
            return FALSE;
        }
    }

    public function getIsdCode($country) {
        $this->db->select('phonecode');
        $this->db->from('tbl_country');
        $this->db->where(array('name' => $country));
        $query = $this->db->get();
        if ($query->num_rows()) {
            // return $query->num_rows();
            return $query->row_array();
        } else {
            return FALSE;
        }
    }

    // function to get the braintree existing customer
    public function getExistingBraintreeAccount($user_id) {

        $this->db->select('customer_id, maskedNumber');
        $this->db->from(DB_PREFIX . 'braintree_customers');
        $this->db->where(array('from_id' => $user_id));
        $query = $this->db->get();
        return $query->result_array();
    }

    // function to get the screen bedge count
//    public function getBedgeCount($user_id, $user_type) {
//
//        $this->db->select('count as bedge_count');
//        $this->db->from(DB_PREFIX . 'bedge_count');
//        $this->db->where(array('user_id' => $user_id, 'user_type' => $user_type));
//        $query = $this->db->get();
//        return $query->row_array();
//    }

    public function clearBedgeCount($user_id) {

        $this->db->where(array('user_id' => $user_id));
        if ($this->db->update(DB_PREFIX . 'bedge_count', array('count' => 0))) {
            return TRUE;
        }
        return FALSE;
    }

    public function getCategoryPrice($category_id) {

        $this->db->select('price');
        $this->db->from(DB_PREFIX . 'categories');
        $this->db->where(array('id' => $category_id));
        $query = $this->db->get();
        return $query->row_array();
    }

    // function to save braintree user
    public function saveBraintree($data) {

        if ($this->db->insert(DB_PREFIX . 'braintree_customers', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    // function to be use to get the payment token
    public function getBraintreeToken($payment_from) {

        $this->db->select('payment_token');
        $this->db->from(DB_PREFIX . 'braintree_customers');
        $this->db->where(array('from_id' => $payment_from));
        $query = $this->db->get();
        return $query->row_array();
    }

    // function to get submerchat account number to be get paid
    public function getSubMerchantNum($buddie_id) {

        $this->db->select('submerchat_account_id');
        $this->db->from(DB_PREFIX . 'users');
        $this->db->where(array('user_id' => $buddie_id));
        $query = $this->db->get();
        return $query->row_array();
    }

    // function to get admin end commision per category rate
    public function getCommision() {

        $this->db->select('commission');
        $this->db->from(DB_PREFIX . 'payment');
        $query = $this->db->get();
        return $query->row_array();
    }

    // function to save payment received
    public function savePaymentReceive($data) {

        if ($this->db->insert(DB_PREFIX . 'payment_receive', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    // function to check for customer having payment enabled or not on switch to customer section
    public function checkPaymentSelected($customer_id) {

        $this->db->select('payment_type');
        $this->db->from(DB_PREFIX . 'users');
        $this->db->where(array('user_id' => $customer_id));
        $query = $this->db->get();
        return $query->row_array();
    }

    public function getAppliedTableData($user_id, $job_id) {

        $this->db->select('*');
        $this->db->from('tbl_applied_by');
        $this->db->where(array('applied_by_id' => $user_id, 'job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return false;
    }

    public function savePauseTimeInterval($workingTime) {

        if ($this->db->insert('tbl_working_time_interval', $workingTime)) {
            return $this->db->insert_id();
        }
        return FALSE;
    }

    public function updateAppliedPauseTime($totalTime, $user_id, $job_id, $currentTime) {

        $user_info = array('job_status_id' => 10, 'pause_time' => $currentTime, 'working_hour' => $totalTime, 'pau_job_status' => 1);
        $this->db->where(array('applied_by_id' => $user_id, 'job_id' => $job_id));
        $this->db->update('tbl_applied_by', $user_info);
    }

    // function to be run on close or cancel buddie running job
    public function updateAppliedStopJobTime($totalTime, $user_id, $job_id) {

        $user_info = array('working_hour' => $totalTime);
        $this->db->where(array('applied_by_id' => $user_id, 'job_id' => $job_id));
        $this->db->update('tbl_applied_by', $user_info);
    }

    public function updateResumeTime($currentTime, $user_id, $job_id) {

        $user_info = array('job_status_id' => 2, 'resume_time' => $currentTime, 'pau_job_status' => 0);
        $this->db->where(array('applied_by_id' => $user_id, 'job_id' => $job_id));
        $this->db->update('tbl_applied_by', $user_info);
        return true;
    }

    public function getWorkingUser($job_id) {

        $this->db->select('*');
        $this->db->from('tbl_applied_by');
        $where = "(job_id = $job_id) AND (job_status_id = 2 OR job_status_id = 10)";
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return false;
    }

    public function isJobApplied($applied_by_id, $job_id) {

        $this->db->select('id');
        $this->db->from('tbl_applied_by');
        $this->db->where(array('applied_by_id' => $applied_by_id, 'job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return true;
        }
        return false;
    }

    public function getSavedTimeInterval($applied_by_id, $job_id) {

        $this->db->select('pause_job_start_time as start_time, pause_job_end_time as end_time');
        $this->db->from('tbl_working_time_interval');
        $this->db->where(array('user_id' => $applied_by_id, 'job_id' => $job_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->result_array();
        }
        return false;
    }

    // function to get category prices
    public function getCategoryPlan() {

        $this->db->select('name, price');
        $this->db->from('tbl_categories');
        $this->db->where(array('status' => 1));
        $query = $this->db->get();
        return $query->result_array();
    }

    // function to check any job is closed or complete for cancel api
    public function isJobClosed($job_id) {

        $this->db->select('id');
        $this->db->from('tbl_applied_by');
        $where = "(job_id = $job_id) AND (job_status_id = '3' OR job_status_id = '4')";
        $this->db->where($where);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function notificationDelete($id) {

        $this->db->where('id', $id);
        if ($this->db->delete('tbl_notifications')) {
            return true;
        }
        return false;
    }

}

// $this->db->order_by("tbl_jobs.job_end_time", "desc");