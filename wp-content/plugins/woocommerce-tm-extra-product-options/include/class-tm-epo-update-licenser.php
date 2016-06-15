<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
    die();
}

final class TM_EPO_UPDATE_Licenser {

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {
        add_action( 'wp_ajax_tm_activate_license', array( $this, 'activate' ) );
        add_action( 'wp_ajax_tm_deactivate_license', array( $this, 'deactivate' ) );
    }

    public function init(){

    }

    public static function api_url( $array ) {
        $array1 = array(
            'http://themecomplete.com/api/activation/',
        );
        
        return implode( '', array_merge( $array1, $array ) );
    }

    private function get_ajax_var($param, $default = null){
        return isset( $_POST[$param] ) ? $_POST[$param] : $default;
    }
    
    public function get_license(){
        return get_option( 'tm_license_activation_key');
    }

    public function check_license(){
        $a1=$this->get_license();
        $a2=get_option( 'tm_epo_envato_username');
        $a3=get_option( 'tm_epo_envato_apikey');
        $a4=get_option( 'tm_epo_envato_purchasecode');
        return (
            !empty($a1) &&
            !empty($a2) &&
            !empty($a3) &&
            !empty($a4) 
            );
    }

    public function activate() {
        $this->request('activation');
    }

    public function deactivate() {
        $this->request('deactivation');
    }

    public function request($action='') {
        check_ajax_referer( 'settings-nonce', 'security' );
        $params = array();
        $params['username'] = $this->get_ajax_var( 'username' );
        $params['key'] = $this->get_ajax_var( 'key' );
        $params['api_key'] = $this->get_ajax_var( 'api_key' );
        $params['url'] = get_site_url();
        $params['plugin'] = TM_EPO_PLUGIN_ID;
        $params['ip'] = isset( $_SERVER['SERVER_ADDR'] ) 
        ? $_SERVER['SERVER_ADDR'] 
        : 
        (
            (function_exists('gethostbyname') && isset($_SERVER['SERVER_NAME']) )
                ?gethostbyname($_SERVER['SERVER_NAME'])
                :''
                );
        $params['license'] = $this->get_license();
        $params['action'] = $action;
        $string = 'activation.php?';
        $message_wrap='<div class="%s"><p>%s</p></div>';
        
        $request_url = self::api_url( array( $string, http_build_query( $params, '', '&' ) ) );        
        $response = wp_remote_get( $request_url, array( 'timeout' => 300 ) );        

        if ( is_wp_error( $response ) ) {
            echo json_encode( array( 'result' => 'wp_error', 'message'=>sprintf($message_wrap, 'error', __('Error connecting to server!',TM_EPO_TRANSLATION)) ) );
            die();
        }

        $result = json_decode( $response['body'] );
        if ( ! is_object( $result ) ) {
            echo json_encode( array( 'result' => 'server_error', 'message'=> sprintf($message_wrap, 'error', __('Error getting data from the server!',TM_EPO_TRANSLATION)) ) );
            die();
        }

        if ( (boolean)$result->result === true && $result->key && $result->code && $result->code=="200") {            
            $license=$result->key;
            if ($action=='activation'){
                update_option( 'tm_epo_envato_username', $params['username'] );
                update_option( 'tm_epo_envato_apikey', $params['api_key'] );
                update_option( 'tm_epo_envato_purchasecode', $params['key'] );
                
                update_option( 'tm_license_activation_key', $license );

                delete_site_transient( 'update_plugins' );
                
                echo json_encode( array( 'result' => '4', 'message'=>sprintf($message_wrap, 'updated', __('License activated!',TM_EPO_TRANSLATION)) ) );
            }elseif ($action=='deactivation'){
                delete_option( 'tm_license_activation_key' );
                delete_site_transient( 'update_plugins' );
                echo json_encode( array( 'result' => '4', 'message'=>sprintf($message_wrap, 'updated', __('License deactivated!',TM_EPO_TRANSLATION)) ) );
            }
            die();
        }

        if ( (boolean)$result->result === false) {
            $message=__('Invalid data!',TM_EPO_TRANSLATION);
            $status='error';
            $rs=$result->code;
            if (!empty($rs)){
                switch ($result->code){
                case "1":
                    $message=__('Invalid action.',TM_EPO_TRANSLATION);
                    break;
                case "2":
                    $message=__('Please fill all fields before trying to activate.',TM_EPO_TRANSLATION);
                    break;
                case "3":
                    $message=__('Trying to activate from outside the plugin interface is not allowed!',TM_EPO_TRANSLATION);
                    break;
                case "4":
                    $message=__('Error connecting to Envato API. Please try again later.',TM_EPO_TRANSLATION);
                    break;
                case "5":
                    $message=__('Trying to activate with an invalid purchase code!',TM_EPO_TRANSLATION);
                    break;
                case "6":
                    $message=__('That username is not valid for this item purchase code. Please make sure you entered the correct username (case sensitive).',TM_EPO_TRANSLATION);
                    break;
                case "7":
                    $message=__('Trying to activate from an invalid domain!',TM_EPO_TRANSLATION);
                    break;
                case "8":
                    $message=__('Trying to activate from an invalid IP address!',TM_EPO_TRANSLATION);
                    break;
                case "9":
                    $message=__('The purchase code is already activated!',TM_EPO_TRANSLATION);//by another username
                    break;
                case "10":
                    $message=__('The purchase code is already activated on another domain!',TM_EPO_TRANSLATION);
                    break;
                case "11":
                    $message=__('You have already activated that purchase code on another domain!',TM_EPO_TRANSLATION);
                    break;
                case "12":
                    $message=__('The purchase code is already activated! Please buy a valid license!',TM_EPO_TRANSLATION);
                    break;
                case "13":
                    $status='updated';
                    $message=__('You have already activated your purchase code!',TM_EPO_TRANSLATION);
                    break;
                case "14":
                    $message=__('Deactivated, but the purchase code was not activated for some reason!',TM_EPO_TRANSLATION);
                    delete_option( 'tm_license_activation_key' );
                    delete_site_transient( 'update_plugins' );
                    echo json_encode( array( 'result' => '4', 'message'=>sprintf($message_wrap, 'updated', __('License deactivated!',TM_EPO_TRANSLATION)) ) );die();
                    break;
                case "15":
                    $status='updated';
                    $message=__('Cannot deactivate. Purchase code is not valid for your save license key!',TM_EPO_TRANSLATION);
                    break;
                }
            }
            echo json_encode( array( 'result' => '-2', 'message'=>sprintf($message_wrap, $status, $message) ) ); 
            die();
        }
        echo json_encode( array( 'result' => '-3', 'message'=>sprintf($message_wrap, 'error', __('Could not complete request!',TM_EPO_TRANSLATION)) ) );
        die();
    }

}
 
?>