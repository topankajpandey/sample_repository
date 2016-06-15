<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
	die();
}

final class TM_EPO_COMPATIBILITY_base {

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

        $this->add_compatibility();

        do_action('wc_epo_add_compatibility');

    }

    public function init() {
        
    }

    public function add_compatibility(){
        
        TM_EPO_COMPATIBILITY_WPML::instance()->init();
        TM_EPO_COMPATIBILITY_woothemes_composite_products::instance()->init();
        TM_EPO_COMPATIBILITY_woothemes_subscriptions::instance()->init();
        TM_EPO_COMPATIBILITY_woothemes_bookings::instance()->init();
        TM_EPO_COMPATIBILITY_woocommerce_dynamic_pricing_and_discounts::instance()->init();
        TM_EPO_COMPATIBILITY_woocommerce_currency_switcher::instance()->init();
        TM_EPO_COMPATIBILITY_store_exporter::instance()->init();
        TM_EPO_COMPATIBILITY_q_translate_x::instance()->init();
        TM_EPO_COMPATIBILITY_woodeposits::instance()->init();
        TM_EPO_COMPATIBILITY_woocommerce_add_to_cart_ajax_for_variable_products::instance()->init();

    }

}


?>