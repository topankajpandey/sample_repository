<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
    die();
}

final class TM_EPO_COMPATIBILITY_woocommerce_currency_switcher {

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

        add_action( 'wc_epo_add_compatibility', array( $this, 'add_compatibility' ) );

    }

    public function init() {
        
    }

    public function add_compatibility(){
        /** WooCommerce Currency Switcher support (realmag777) **/
        add_filter('woocommerce_tm_epo_price', array( $this, 'tm_epo_price' ), 10, 3);
        add_filter('woocommerce_tm_epo_price2', array( $this, 'tm_epo_price2' ), 10, 3);
        add_filter('wc_epo_get_current_currency_price', array( $this, 'tm_epo_price2' ), 10, 3);
        add_filter('wc_epo_get_currency_price', array( $this, 'tm_wc_epo_get_currency_price' ), 10, 4);
        add_filter('woocommerce_tm_epo_price_add_on_cart', array( $this, 'tm_epo_price_add_on_cart' ), 10, 2);
        add_filter('woocommerce_tm_epo_price2_remove', array( $this, 'tm_epo_price2_remove' ), 10, 2);
        add_filter('woocommerce_tm_epo_price_per_currency_diff', array( $this, 'tm_epo_price_per_currency_diff' ), 10, 1);
    }

    /** Check WooCommerce Currency Switcher version **/
    private function _get_woos_price_calculation(){
        
        $oldway=false;
        if (class_exists('WOOCS')){
            global $WOOCS;
            $v=intval($WOOCS->the_plugin_version);
            if($v == 1){
                if (version_compare($WOOCS->the_plugin_version, '1.0.9', '<')){
                    $oldway=true;
                }
            }else{
                if (version_compare($WOOCS->the_plugin_version, '2.0.9', '<')){
                    $oldway=true;
                }
            }
        }

        return $oldway;

    }

    /** WooCommerce Currency Switcher support (realmag777)
     * This filter is currently only used for product prices.
     */
    public function tm_epo_price($price="",$type="",$is_meta_value=true,$currency=false){
        if (class_exists('WOOCS')){
            global $WOOCS;
            if (property_exists($WOOCS, 'the_plugin_version')){
                if ( !$is_meta_value && !$this->_get_woos_price_calculation() ){
                    if($WOOCS->is_multiple_allowed){
                        // no converting needed
                    }else{
                        $price = apply_filters('woocs_exchange_value', $price);
                    }
                }else{
                    $currencies = $WOOCS->get_currencies();
                    if (!$currency){
                        $current_currency=$WOOCS->current_currency;
                    }else{
                        $current_currency=$currency;
                    }
                    if (isset($currencies[$current_currency]) && isset($currencies[$current_currency]['rate'])){
                        $price=(double)$price* (double)$currencies[$current_currency]['rate'];
                    }
                }
            }
        }elseif (class_exists('WooCommerce_All_in_One_Currency_Converter_Main')){
            global $woocommerce_all_in_one_currency_converter;
            $user_currency = $woocommerce_all_in_one_currency_converter->settings->session_currency;
            $currency_data = $woocommerce_all_in_one_currency_converter->settings->get_currency_data();
            $conversion_method = $woocommerce_all_in_one_currency_converter->settings->get_conversion_method();

            if (!$currency){
                $current_currency=$user_currency;
            }else{
                $current_currency=$currency;
            }
            if (isset($currency_data[$current_currency]) && isset($currency_data[$current_currency]['rate'])){
                $price=(double)$price* (double)$currency_data[$current_currency]['rate'];    
            }            
        }
        
        return $price;
    }

    /** WooCommerce Currency Switcher support (realmag777) 
     * Adjusts option prices when using different currency price for versions > 2.0.9
     * MUST BE USED ONLY WHEN IT IS KNOWN THAT THE PRICE IS DIFFERENT !
     */
    public function tm_epo_price_per_currency_diff($price=0){
        if( class_exists('WOOCS') && !$this->_get_woos_price_calculation() ){
            $price = $this->tm_epo_price2_remove($price);
        }
        return $price;
    }

    /** WooCommerce Currency Switcher support (realmag777) **/
    public function tm_epo_price2($price="",$type="",$is_meta_value=true){
        global $woocommerce_wpml;
        if (is_array($type)){
            $type="";
        }
        // Check if the price should be processed only once
        if(in_array((string)$type, array('', 'char', 'step', 'intervalstep', 'charnofirst', 'charnospaces', 'charnon', 'charnonnospaces', 'fee', 'subscriptionfee'))) {

            if(TM_EPO_WPML()->is_active() && $woocommerce_wpml && property_exists($woocommerce_wpml, 'multi_currency') && $woocommerce_wpml->multi_currency){

                $price=apply_filters('wcml_raw_price_amount',$price);

            }elseif (class_exists('WOOCS') || class_exists('WooCommerce_All_in_One_Currency_Converter_Main')){
            
                $price=$this->tm_epo_price($price,$type,$is_meta_value);

            }
            else {

                $price = $this->get_price_in_currency($price);

            }

        }

        return $price;

    }

    public function tm_wc_epo_get_currency_price($currency=false, $price="",$type="",$is_meta_value=true){
        if (!$currency){
            return $this->tm_epo_price2($price,$type,$is_meta_value);
        }
        global $woocommerce_wpml;
        // Check if the price should be processed only once

        if(TM_EPO_WPML()->is_active() && $woocommerce_wpml && property_exists($woocommerce_wpml, 'multi_currency') && $woocommerce_wpml->multi_currency){
            //todo:doesn't work at the moment
            $price=apply_filters('wcml_raw_price_amount',$price);

        }elseif (class_exists('WOOCS') || class_exists('WooCommerce_All_in_One_Currency_Converter_Main')){
            
            $price=$this->tm_epo_price($price,$type,$is_meta_value,$currency);

        }
        else {

            $price = $this->get_price_in_currency($price,$currency);

        }

        return $price;

    }

    /** WooCommerce Currency Switcher support (realmag777) **/
    public function tm_epo_price2_remove($price="",$type=""){
        global $woocommerce_wpml;
        if (class_exists('WOOCS')){
            global $WOOCS;
            $currencies = $WOOCS->get_currencies();
            $current_currency=$WOOCS->current_currency;
            if (!empty($currencies[$current_currency]['rate'])){
                $price=(double)$price/ $currencies[$current_currency]['rate'];  
            }
        }elseif (class_exists('WooCommerce_All_in_One_Currency_Converter_Main')){
            global $woocommerce_all_in_one_currency_converter;
            $user_currency = $woocommerce_all_in_one_currency_converter->settings->session_currency;
            $currency_data = $woocommerce_all_in_one_currency_converter->settings->get_currency_data();
            $conversion_method = $woocommerce_all_in_one_currency_converter->settings->get_conversion_method();

            if (!$currency){
                $current_currency=$user_currency;
            }else{
                $current_currency=$currency;
            }
            if (isset($currency_data[$current_currency]) && !empty($currency_data[$current_currency]['rate'])){
                $price=(double)$price/ (double)$currency_data[$current_currency]['rate'];
            }
        }elseif(TM_EPO_WPML()->is_active() && $woocommerce_wpml && property_exists($woocommerce_wpml, 'multi_currency') && $woocommerce_wpml->multi_currency){

            $price=$woocommerce_wpml->multi_currency->unconvert_price_amount($price);

        }else{
            $from_currency = get_option('woocommerce_currency');
            $to_currency = tc_get_woocommerce_currency();
            $price = $this->get_price_in_currency($price, $from_currency, $to_currency);
        }
        return $price;
    }

    /**
     * Basic integration with WooCommerce Currency Switcher, developed by Aelia
     * (http://aelia.co). This method can be used by any 3rd party plugin to
     * return prices converted to the active currency.
     *
     * @param double price The source price.
     * @param string to_currency The target currency. If empty, the active currency
     * will be taken.
     * @param string from_currency The source currency. If empty, WooCommerce base
     * currency will be taken.
     * @return double The price converted from source to destination currency.
     * @author Aelia <support@aelia.co>
     * @link http://aelia.co
     */
    protected function get_price_in_currency($price, $to_currency = null, $from_currency = null) {
        if(empty($from_currency)) {
            $from_currency = get_option('woocommerce_currency');
        }
        if(empty($to_currency)) {
            $to_currency = tc_get_woocommerce_currency();
        }
        return apply_filters('wc_aelia_cs_convert', $price, $from_currency, $to_currency);
    }

    /** WooCommerce Currency Switcher support (realmag777) **/
    public function tm_epo_price_add_on_cart($price="",$type=""){
        global $woocommerce_wpml;
        if (!class_exists('WOOCS') && !class_exists('WooCommerce_All_in_One_Currency_Converter_Main')){
            $price = apply_filters('woocommerce_tm_epo_price2',$price,$type);
        }
        return $price;
    }

}


?>