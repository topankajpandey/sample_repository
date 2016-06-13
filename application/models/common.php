<?php

class Common extends CI_Model {

    public function isEmailExist($email) {
        $this->db->select('*');
        $this->db->from('tbl_users');
        $this->db->where(array('email' => $email));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return 0;
    }

    public function isContactExist($contact, $user_id = '') {
        $this->db->select('*');
        $this->db->from('tbl_users');
        $this->db->where(array('contact' => $contact));
        if (!empty($user_id)) {
            $this->db->where(array('user_id !=' => $user_id));
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return false;
    }

    public function isW9idExist($w9formid, $user_id = '') {
        $this->db->select('*');
        $this->db->from('tbl_users');
        $this->db->where(array('contact' => $w9formid));
        if (!empty($user_id)) {
            $this->db->where(array('user_id !=' => $user_id));
        }
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return false;
    }

    public function getUserInfoById($user_id) {
        $this->db->select('*');
        $this->db->from('tbl_users');
        $this->db->where(array('user_id' => $user_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return false;
    }

    public function updateUserStatus($user_id, $data) {
        $this->db->where('tbl_users.user_id', $user_id);
        if ($this->db->update('tbl_users', $data)) {
            return true;
        }
        return false;
    }

    public function isFacebookIdExist($fb_id) {
        $this->db->select('*');
        $this->db->from('tbl_users');
        $this->db->where(array('fb_id' => $fb_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function isGoogleIdExist($google_id) {
        $this->db->select('*');
        $this->db->from('tbl_users');
        $this->db->where(array('google_id' => $google_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function isTwitterExist($twitter_id) {
        $this->db->select('*');
        $this->db->from('tbl_users');
        $this->db->where(array('twitter_id' => $twitter_id));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function updatePassword($email, $otp_password) {
        $this->db->where(array('email' => $email));
        $this->db->update('tbl_users', array('password' => md5($otp_password)));
        return TRUE;
    }

    public function checkpassword($user_id, $old_password) {
        $this->db->where("(user_id = '$user_id') AND password = md5('$old_password')");
        $query = $this->db->get('tbl_users');
        $result = $query->row_array();
        if (count($result)) {
            return TRUE;
        }
        return false;
    }

    public function updateLastLogin($user_id) {
        $data = array('last_login' => date('Y-m-d H:i:s'));
        $this->db->where('tbl_users.user_id', $user_id);
        if ($this->db->update('tbl_users', $data)) {
            return true;
        }
        return false;
    }

}

?>
