<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
    die();
}

final class TM_EPO_COMPATIBILITY_woothemes_bookings {

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
        /** WooCommerce bookings  (woothemes) support **/
        add_filter( 'booking_form_calculated_booking_cost', array( $this, 'adjust_booking_cost' ), 10, 3 );
        add_filter( 'tm_epo_cart_options_prices', array( $this, 'tm_epo_cart_options_prices' ), 10, 2);
        add_filter( 'wc_epo_adjust_price', array( $this, 'wc_epo_adjust_price' ), 10, 2);
        add_action( 'wp_ajax_tc_epo_bookings_calculate_costs' , array( $this, 'tc_epo_bookings_calculate_costs' ) );
        add_action( 'wp_ajax_nopriv_tc_epo_bookings_calculate_costs' , array( $this, 'tc_epo_bookings_calculate_costs' ) );
        add_filter( 'wc_epo_adjust_cart_item', array( $this, 'wc_epo_adjust_cart_item' ), 10, 1);
    }

    public function wc_epo_adjust_cart_item($cart_item ){
         if ( 
            isset($cart_item['data']) 
            && is_object($cart_item['data']) 
            && property_exists($cart_item['data'], "id") 
            && $cart_item['data']->id){
            if ( $cart_item['data']->is_type( 'booking' )){

                if ( ! empty( $cart_item['tmcartepo'] ) ) {
                    $cart_item['tm_epo_product_original_price']=$cart_item['tm_epo_product_original_price']-$cart_item['tm_epo_options_prices'];
                }
            
            }
        }
        
        return $cart_item;
    }

    public function wc_epo_adjust_price($adjust,$cart_item){
        if ( 
            isset($cart_item['data']) 
            && is_object($cart_item['data']) 
            && property_exists($cart_item['data'], "id") 
            && $cart_item['data']->id){
            if ( $cart_item['data']->is_type( 'booking' )){
                return false;
            }
        }
        return $adjust;
    }
    public function tc_epo_bookings_calculate_costs() {
        $posted = array();
        remove_filter( 'booking_form_calculated_booking_cost', array( $this, 'adjust_booking_cost' ), 10, 3 );
        parse_str( $_POST['form'], $posted );

        $booking_id = $posted['add-to-cart'];
        $product    = get_product( $booking_id );

        if ( ! $product ) {
            die( json_encode( array(
                'result' => 'ERROR',
                 'html'   => '<span class="booking-error">' . __( 'This booking is unavailable.', TM_EPO_TRANSLATION ) . '</span>',
                'product_price'   => 0
            ) ) );
        }

        $booking_form     = new WC_Booking_Form( $product );
        $cost             = $booking_form->calculate_booking_cost( $posted );

        if ( is_wp_error( $cost ) ) {
            die( json_encode( array(
                'result' => 'ERROR',
                'html'   => '<span class="booking-error">' . $cost->get_error_message() . '</span>',
                'product_price'   => 0
            ) ) );
        }

        $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
        $display_price    = $tax_display_mode == 'incl' ? $product->get_price_including_tax( 1, $cost ) : $product->get_price_excluding_tax( 1, $cost );

        die( json_encode( array(
            'result' => 'SUCCESS',
            'product_price'   => $display_price
        ) ) );
    }

    /** Adjust options when adding to cart */
    public function tm_epo_cart_options_prices($price,$cart_data){
        $wc_booking_person_qty_multiplier = (TM_EPO()->tm_epo_bookings_person=="yes")?1:0;
        $wc_booking_block_qty_multiplier = (TM_EPO()->tm_epo_bookings_block=="yes")?1:0;

        if ( 
            (!$wc_booking_person_qty_multiplier && !$wc_booking_block_qty_multiplier) 
            || !isset($cart_data['booking']) 
            || !isset($cart_data['data']) 
            || !is_object($cart_data['data']) 
            || !property_exists($cart_data['data'], "id") 
            || !$cart_data['data']->id){
            return $price;
        }

        $person=(!empty($cart_data['booking']['_persons']) && array_sum($cart_data['booking']['_persons']) )?array_sum($cart_data['booking']['_persons']):0;
        $duration=!empty($cart_data['booking']['_duration'])?$cart_data['booking']['_duration']:0;

        $c=$person+$duration;

        $price = $c * $price;

        return $price;

    }

    /** Adjust the final booking cost */
    public function adjust_booking_cost( $booking_cost, $booking_form, $posted ) {
        $epos           = TM_EPO()->tm_add_cart_item_data( array(), $booking_form->product->id, $posted, true );
        $extra_price    = 0;
        $booking_data   = $booking_form->get_posted_data( $posted );

        $wc_booking_person_qty_multiplier = (TM_EPO()->tm_epo_bookings_person=="yes")?1:0;
        $wc_booking_block_qty_multiplier = (TM_EPO()->tm_epo_bookings_block=="yes")?1:0;
        if ( !empty($epos) && ! empty( $epos['tmcartepo'] ) ) {
            foreach ($epos['tmcartepo'] as $key => $value) {
                if (!empty($value['price'])){

                    $price = floatval($value['price']);
                    $option_price = 0;

                    if ( ! empty( $wc_booking_person_qty_multiplier ) && ! empty( $booking_data['_persons'] ) && array_sum( $booking_data['_persons'] ) ) {
                        $option_price += $price * array_sum( $booking_data['_persons'] );
                    }
                    if ( ! empty( $wc_booking_block_qty_multiplier ) && ! empty( $booking_data['_duration'] ) ) {
                        $option_price += $price * $booking_data['_duration'];
                    }
                    if ( ! $option_price ) {
                        $option_price += $price;
                    }
                    $extra_price += $option_price;
                }
            }
            
        }

        return $booking_cost + $extra_price;
    }
}


?>