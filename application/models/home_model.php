<?php

class Home_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function userRegister($data) {
        
        if ($this->db->insert(DB_PREFIX . 'users', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function isEmailUnique($table_name, $email) {
        
        $this->db->where('email', $email);
        $query = $this->db->get($table_name);
        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
    }

    public function isContactUnique($table_name, $contact) {
        
        $this->db->where('contact', $contact);
        $query = $this->db->get($table_name);
        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
    }

    public function getSingleContent($id) {
        
        $this->db->select('*');
        $this->db->from(DB_PREFIX . 'cms');
        $this->db->where('id', $id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row_array();
        } else {
            return false;
        }
    }

    public function Savesubscribedemail($data) {
        
        $this->db->insert(DB_PREFIX . 'newsletter', $data);
        return $this->db->insert_id();
    }

    public function checkNewsletterByIdToken($id, $token) {
        
        $this->db->select('*');
        $this->db->from(DB_PREFIX . 'newsletter');
        $this->db->where(array('id' => $id, 'activation_string' => $token));
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function Unsubscribedemail($id, $token) {
        
        $this->db->where(array('id' => $id, 'activation_string' => $token));
        if ($this->db->delete(DB_PREFIX . 'newsletter')) {
            return true;
        }
        return false;
    }

    public function Savecontactquery($data) {
        
        $this->db->insert(DB_PREFIX . 'query', $data);
        return $this->db->insert_id();
    }

    public function getadmin() {
        
        $this->db->select('*');
        $this->db->from(DB_PREFIX . 'admin');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function matchfbid($data = array()) {
        
        $this->db->select('*');
        $this->db->from(DB_PREFIX . 'users');
        $this->db->where(array('fb_id' => $data['fbid']));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return false;
    }

    public function matchfbemail($data = array()) {
        
        $this->db->select('*');
        $this->db->from(DB_PREFIX . 'users');
        $this->db->where(array('email' => $data['email']));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return false;
    }

    public function updatefbid($data = array()) {

        $this->db->where('email', $data['email']);
        $datafb = array(
            'fb_id' => $data['fbid'],
            'profile_pic' => $data['profile_pic']
        );

        if ($this->db->update(DB_PREFIX . 'users', $datafb)) {
            return true;
        }
        return false;
    }

    public function Checktweetid($twitterid) {
        
        $this->db->select('*');
        $this->db->from(DB_PREFIX . 'users');
        $this->db->where('twitter_id', $twitterid);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return false;
    }

    public function Matchgoogleid($id, $email) {
        
        $this->db->select('*');
        $this->db->from(DB_PREFIX . 'users');
        $this->db->where('google_id', $id);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return false;
    }

    public function Matchgoogleemail($id, $email) {
        
        $this->db->select('*');
        $this->db->from(DB_PREFIX . 'users');
        $this->db->where('email', $email);
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        }
        return false;
    }

    public function Updategoogleid($id, $email) {
        
        $this->db->where('email', $email);
        $datagoogle = array(
            'google_id' => $id,
        );
        if ($this->db->update(DB_PREFIX . 'users', $datagoogle)) {
            echo $this->db->last_query();
            return true;
        }
        return false;
    }

    public function login($email, $password) {
        
        $this->db->select('*');
        $this->db->from(DB_PREFIX . 'users');
        $this->db->where(array('email' => $email, 'password' => $password));
        $query = $this->db->get();
        return $query->row_array();
    }

    public function Loggedinuserdata($user_id) {
        
        $this->db->select('*');
        $this->db->from(DB_PREFIX . 'users');
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function userUpdate($id, $data) {
        
        $this->db->where('user_id', $id);
        if ($this->db->update(DB_PREFIX . 'users', $data)) {
            return true;
        } return false;
    }

    public function getIsdCode($country) {
        
        $this->db->select('phonecode');
        $this->db->from(DB_PREFIX . 'country');
        $this->db->where(array('name' => $country));
        $query = $this->db->get();
        if ($query->num_rows()) {
            return $query->row_array();
        } else {
            return FALSE;
        }
    }

    public function getCountry($country_id) {
        
        $this->db->select('name');
        $this->db->from(DB_PREFIX . 'countries');
        $this->db->where('country_id', $country_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    // funcion to get single city name

    public function getSingleCity($city_id) {
        
        $this->db->select('name');
        $this->db->from(DB_PREFIX . 'cities');
        $this->db->where('id', $city_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    // function to get single state name

    public function getSingleState($state_id) {
        
        $this->db->select('name');
        $this->db->from(DB_PREFIX . 'state');
        $this->db->where('zone_id', $state_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    // function to get multiple states
    public function getState($country_id) {
        
        $this->db->select('zone_id, name');
        $this->db->from(DB_PREFIX . 'state');
        $this->db->where('country_id', $country_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function getCity($state_id) {
        
        $this->db->select('id, name');
        $this->db->from(DB_PREFIX . 'cities');
        $this->db->where('state_id', $state_id);
        $query = $this->db->get();
        return $query->result_array();
    }

}
