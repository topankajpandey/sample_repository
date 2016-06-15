<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
    die();
}

final class TM_EPO_COMPATIBILITY_woocommerce_dynamic_pricing_and_discounts {

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {

        add_action( 'init', array( $this, 'init2' ) );
    }

    public function init() {
        
    }
    
    public function init2() {
        $this->add_compatibility();
    }

    public function add_compatibility(){
        /** WooCommerce Dynamic Pricing & Discounts support **/
        add_action( 'woocommerce_cart_loaded_from_session', array($this, 'cart_loaded_from_session_1'), 1 );
        if (TM_EPO()->tm_epo_dpd_enable=="no"){
            add_action( 'woocommerce_cart_loaded_from_session', array($this, 'cart_loaded_from_session_2'), 2 );
            add_action( 'woocommerce_cart_loaded_from_session', array($this, 'cart_loaded_from_session_99999'), 99999 );
        }
        add_filter( 'woocommerce_cart_item_price', array($this, 'cart_item_price'), 101, 3 );       
        add_action( 'woocommerce_update_cart_action_cart_updated', array($this, 'tm_woocommerce_update_cart_action_cart_updated'), 9999,1 );

        add_filter( 'wc_epo_get_RP_WCDPD', array($this, 'get_RP_WCDPD'), 10, 3 ); 
    }

    /** WooCommerce Dynamic Pricing & Discounts support **/
    public function cart_loaded_from_session_1(){
        //WC()->cart->calculate_totals();
        $cart_contents = WC()->cart->cart_contents;

        if (is_array($cart_contents)){
            foreach ($cart_contents as $cart_item_key => $cart_item) {
                WC()->cart->cart_contents[$cart_item_key][TM_EPO()->cart_edit_key_var] = $cart_item_key;
            }
        }

    }

    /** WooCommerce Dynamic Pricing & Discounts support **/
    public function cart_loaded_from_session_2(){
        if(!class_exists('RP_WCDPD')){
            return;
        }

        $cart_contents = WC()->cart->cart_contents;

        if (is_array($cart_contents)){
            foreach ($cart_contents as $cart_item_key => $cart_item) {
                if (isset($cart_item['tm_epo_product_original_price'])){
                    WC()->cart->cart_contents[$cart_item_key]['data']->price = $cart_item['tm_epo_product_original_price'];
                    WC()->cart->cart_contents[$cart_item_key]['tm_epo_doing_adjustment'] = true;
                }
            }
        }

    }

    /** WooCommerce Dynamic Pricing & Discounts support **/
    public function cart_loaded_from_session_99999(){
        if(!class_exists('RP_WCDPD')){
            return;
        }

        $cart_contents = WC()->cart->cart_contents;
        if (is_array($cart_contents)){
            foreach ($cart_contents as $cart_item_key => $cart_item) {
                $current_product_price=WC()->cart->cart_contents[$cart_item_key]['data']->price;

                if (isset($cart_item['tm_epo_options_prices']) && !empty($cart_item['tm_epo_doing_adjustment'])){
                    WC()->cart->cart_contents[$cart_item_key]['tm_epo_product_after_adjustment']=$current_product_price;
                    WC()->cart->cart_contents[$cart_item_key]['data']->adjust_price($cart_item['tm_epo_options_prices']);
                    unset(WC()->cart->cart_contents[$cart_item_key]['tm_epo_doing_adjustment']);
                }
            }
        }

    }

    /** WooCommerce Dynamic Pricing & Discounts support **/
    public function tm_woocommerce_update_cart_action_cart_updated($cart_updated=false){

        $cart_contents = WC()->cart->cart_contents;
        if (is_array($cart_contents)){
            foreach ($cart_contents as $cart_item_key => $cart_item) {
                if (isset($cart_item['tm_epo_options_prices'])){
                    $cart_updated = true;
                }
            }
        }
        return $cart_updated;
    }

    /**
     * Replace cart html prices for WooCommerce Dynamic Pricing & Discounts
     * 
     * @access public
     * @param string $item_price
     * @param array $cart_item
     * @param string $cart_item_key
     * @return string
     */
     public function cart_item_price($item_price="", $cart_item="", $cart_item_key=""){
        if(!class_exists('RP_WCDPD')){
            return $item_price;
        }
        if (!isset($cart_item['tmcartepo'])) {
            return $item_price;
        }
        if (!isset($cart_item['rp_wcdpd'])) {
            return $item_price;
        }

        // Get price to display
        $price = TM_EPO()->get_price_for_cart(false,$cart_item,"");

        // Format price to display
        $price_to_display = $price;
        if (TM_EPO()->tm_epo_cart_field_display=="advanced"){
            $original_price_to_display = TM_EPO()->get_price_for_cart($cart_item['tm_epo_product_original_price'],$cart_item,"");
            if (TM_EPO()->tm_epo_dpd_enable=="yes"){
                $price=$this->get_RP_WCDPD(wc_get_product($cart_item['data']->id),$cart_item['tm_epo_product_original_price'], $cart_item_key);
                $price_to_display = TM_EPO()->get_price_for_cart($price,$cart_item,"");
            }else{
                $price=$cart_item['data']->price;
                $price=$price-$cart_item['tm_epo_options_prices'];
                $price_to_display = TM_EPO()->get_price_for_cart($price,$cart_item,"");
            }
        }else{
            $original_price_to_display = TM_EPO()->get_price_for_cart($cart_item['tm_epo_product_price_with_options'],$cart_item,"");
        }

        $item_price = '<span class="rp_wcdpd_cart_price"><del>' . $original_price_to_display . '</del> <ins>' . $price_to_display . '</ins></span>';

        return $item_price;
    }

    // get WooCommerce Dynamic Pricing & Discounts price for options
    // modified from get version from Pricing class
    private function get_RP_WCDPD_single($field_price,$cart_item_key,$pricing){
        if (TM_EPO()->tm_epo_dpd_enable == 'no' || !isset($pricing->items[$cart_item_key])) {
            return $field_price;
        }

        $price = $field_price;
        $original_price = $price;

        if (in_array($pricing->pricing_settings['apply_multiple'], array('all', 'first'))) {
            foreach ($pricing->apply['global'] as $rule_key => $apply) {
                if ($deduction = $pricing->apply_rule_to_item($rule_key, $apply, $cart_item_key, $pricing->items[$cart_item_key], false, $price)) {

                    if ($apply['if_matched'] == 'other' && isset($pricing->applied) && isset($pricing->applied['global'])) {
                        if (count($pricing->applied['global']) > 1 || !isset($pricing->applied['global'][$rule_key])) {
                            continue;
                        }
                    }

                    $pricing->applied['global'][$rule_key] = 1;
                    $price = $price - $deduction;
                }
            }
        }
        else if ($pricing->pricing_settings['apply_multiple'] == 'biggest') {

            $price_deductions = array();

            foreach ($pricing->apply['global'] as $rule_key => $apply) {

                if ($apply['if_matched'] == 'other' && isset($pricing->applied) && isset($pricing->applied['global'])) {
                    if (count($pricing->applied['global']) > 1 || !isset($pricing->applied['global'][$rule_key])) {
                        continue;
                    }
                }

                if ($deduction = $pricing->apply_rule_to_item($rule_key, $apply, $cart_item_key, $pricing->items[$cart_item_key], false)) {
                    $price_deductions[$rule_key] = $deduction;
                }
            }

            if (!empty($price_deductions)) {
                $max_deduction = max($price_deductions);
                $rule_key = array_search($max_deduction, $price_deductions);
                $pricing->applied['global'][$rule_key] = 1;
                $price = $price - $max_deduction;
            }

        }

        // Make sure price is not negative
        // $price = ($price < 0) ? 0 : $price;

        if ($price != $original_price) {
            return $price;
        }
        else {
            return $field_price;
        }
    }

    // get WooCommerce Dynamic Pricing & Discounts price rules
    public function get_RP_WCDPD($product,$field_price=null,$cart_item_key=null){
        $price = null;
                
        if(class_exists('RP_WCDPD') && class_exists('RP_WCDPD_Pricing') && !empty($GLOBALS['RP_WCDPD'])){
            
            $tm_RP_WCDPD=$GLOBALS['RP_WCDPD'];

            $selected_rule = null;

            if ($field_price!==null && $cart_item_key!==null){
                return $this->get_RP_WCDPD_single($field_price,$cart_item_key,$tm_RP_WCDPD->pricing);
            }
            
            $dpd_version_compare=version_compare( RP_WCDPD_VERSION, '1.0.13', '<' );
            // Iterate over pricing rules and use the first one that has this product in conditions (or does not have if condition "not in list")
            if (isset($tm_RP_WCDPD->opt['pricing']['sets']) 
                && count($tm_RP_WCDPD->opt['pricing']['sets']) ) {
                foreach ($tm_RP_WCDPD->opt['pricing']['sets'] as $rule_key => $rule) {
                    if ($rule['method'] == 'quantity' && $validated_rule = RP_WCDPD_Pricing::validate_rule($rule)) {
                        if ($dpd_version_compare){
                            if ($validated_rule['selection_method'] == 'all' && $tm_RP_WCDPD->user_matches_rule($validated_rule['user_method'], $validated_rule['roles'])) {
                                $selected_rule = $validated_rule;
                                break;
                            }
                            if ($validated_rule['selection_method'] == 'categories_include' && count(array_intersect($tm_RP_WCDPD->get_product_categories($product->id), $validated_rule['categories'])) > 0 && $tm_RP_WCDPD->user_matches_rule($validated_rule['user_method'], $validated_rule['roles'])) {
                                $selected_rule = $validated_rule;
                                break;
                            }
                            if ($validated_rule['selection_method'] == 'categories_exclude' && count(array_intersect($tm_RP_WCDPD->get_product_categories($product->id), $validated_rule['categories'])) == 0 && $tm_RP_WCDPD->user_matches_rule($validated_rule['user_method'], $validated_rule['roles'])) {
                                $selected_rule = $validated_rule;
                                break;
                            }
                            if ($validated_rule['selection_method'] == 'products_include' && in_array($product->id, $validated_rule['products']) && $tm_RP_WCDPD->user_matches_rule($validated_rule['user_method'], $validated_rule['roles'])) {
                                $selected_rule = $validated_rule;
                                break;
                            }
                            if ($validated_rule['selection_method'] == 'products_exclude' && !in_array($product->id, $validated_rule['products']) && $tm_RP_WCDPD->user_matches_rule($validated_rule['user_method'], $validated_rule['roles'])) {
                                $selected_rule = $validated_rule;
                                break;
                            }
                        }else{
                            if ($validated_rule['selection_method'] == 'all' && $tm_RP_WCDPD->user_matches_rule($validated_rule)) {
                                $selected_rule = $validated_rule;
                                break;
                            }
                            if ($validated_rule['selection_method'] == 'categories_include' && count(array_intersect($tm_RP_WCDPD->get_product_categories($product->id), $validated_rule['categories'])) > 0 && $tm_RP_WCDPD->user_matches_rule($validated_rule)) {
                                $selected_rule = $validated_rule;
                                break;
                            }
                            if ($validated_rule['selection_method'] == 'categories_exclude' && count(array_intersect($tm_RP_WCDPD->get_product_categories($product->id), $validated_rule['categories'])) == 0 && $tm_RP_WCDPD->user_matches_rule($validated_rule)) {
                                $selected_rule = $validated_rule;
                                break;
                            }
                            if ($validated_rule['selection_method'] == 'products_include' && in_array($product->id, $validated_rule['products']) && $tm_RP_WCDPD->user_matches_rule($validated_rule)) {
                                $selected_rule = $validated_rule;
                                break;
                            }
                            if ($validated_rule['selection_method'] == 'products_exclude' && !in_array($product->id, $validated_rule['products']) && $tm_RP_WCDPD->user_matches_rule($validated_rule)) {
                                $selected_rule = $validated_rule;
                                break;
                            }
                        }
                    }
                }
            }
            
            if (is_array($selected_rule)) {

                // Quantity
                if ($selected_rule['method'] == 'quantity' && isset($selected_rule['pricing']) && in_array($selected_rule['quantities_based_on'], array('exclusive_product','exclusive_variation','exclusive_configuration')) ) {

                        if ($product->product_type == 'variable' || $product->product_type == 'variable-subscription') {
                            $product_variations = $product->get_available_variations();
                        }

                        // For variable products only - check if prices differ for different variations
                        $multiprice_variable_product = false;

                        if ( ($product->product_type == 'variable' || $product->product_type == 'variable') && !empty($product_variations)) {
                            $last_product_variation = array_slice($product_variations, -1);
                            $last_product_variation_object = new WC_Product_Variable($last_product_variation[0]['variation_id']);
                            $last_product_variation_price = $last_product_variation_object->get_price();

                            foreach ($product_variations as $variation) {
                                $variation_object = new WC_Product_Variable($variation['variation_id']);

                                if ($variation_object->get_price() != $last_product_variation_price) {
                                    $multiprice_variable_product = true;
                                }
                            }
                        }

                        if ($multiprice_variable_product) {
                            $variation_table_data = array();

                            foreach ($product_variations as $variation) {
                                $variation_product = new WC_Product_Variation($variation['variation_id']);
                                $variation_table_data[$variation['variation_id']] = $tm_RP_WCDPD->pricing_table_calculate_adjusted_prices($selected_rule['pricing'], $variation_product->get_price());
                            }
                            $price=array();
                            $price['is_multiprice']=true;
                            $price['rules']=$variation_table_data;
                        }
                        else {
                            if ($product->product_type == 'variable' && !empty($product_variations)) {
                                $variation_product = new WC_Product_Variation($last_product_variation[0]['variation_id']);
                                $table_data = $tm_RP_WCDPD->pricing_table_calculate_adjusted_prices($selected_rule['pricing'], $variation_product->get_price());
                            }
                            else {
                                $table_data = $tm_RP_WCDPD->pricing_table_calculate_adjusted_prices($selected_rule['pricing'], $product->get_price());
                            }
                            $price=array();
                            $price['is_multiprice']=false;
                            $price['rules']=$table_data;
                        }                   
                }

            }
        }
        if ($field_price!==null){
            $price=$field_price;
        }
        return $price;
    }

}


?>