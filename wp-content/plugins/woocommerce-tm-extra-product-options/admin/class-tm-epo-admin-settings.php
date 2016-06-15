<?php
/*
 * Settings class.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (class_exists('WC_Settings_Page')){

	TM_EPO_ADMIN_GLOBAL()->tm_load_scripts();

	class TM_EPO_ADMIN_SETTINGS extends WC_Settings_Page {
		
		var $other_settings=0;
		var $settings_options=array();
		var $settings_array=array();

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id    			= TM_EPO_ADMIN_SETTINGS_ID;
			$this->label 			= __('Extra Product Options', TM_EPO_TRANSLATION);
			$this->tab_count 		= 0;
			$this->settings_options = array(
				"general" 	=> __( 'General', TM_EPO_TRANSLATION ),
				"display" 	=> __( 'Display', TM_EPO_TRANSLATION ),
				"cart" 		=> __( 'Cart', TM_EPO_TRANSLATION ),
				"string" 	=> __( 'Strings', TM_EPO_TRANSLATION ),
				"style" 	=> __( 'Style', TM_EPO_TRANSLATION ),
				"global" 	=> __( 'Global', TM_EPO_TRANSLATION ),
				"other" 	=> "other",
				"license" 	=> __( 'License', TM_EPO_TRANSLATION ),
				"upload" 	=> __('Upload manager', TM_EPO_TRANSLATION)
				);

			foreach ($this->settings_options as $key => $value) {
				$this->settings_array[$key] = $this->get_setting_array($key,$value);
			}

			add_filter( 'woocommerce_settings_tabs_array', 							array( $this, 'add_settings_page' ), 20 );
			add_action( 'woocommerce_settings_' . $this->id, 						array( $this, 'output' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, 					array( $this, 'save' ) );

			add_action( 'woocommerce_admin_field_tm_tabs_header', 					array( $this, 'tm_tabs_header_setting' ) );
			add_action( 'woocommerce_admin_field_tm_title', 						array( $this, 'tm_title_setting' ) );
			add_action( 'woocommerce_admin_field_tm_html', 							array( $this, 'tm_html_setting' ) );
			add_action( 'woocommerce_admin_field_tm_sectionend', 					array( $this, 'tm_sectionend_setting' ) );

			add_action( 'tm_woocommerce_settings_' . 'epo_page_options' , 			array( $this, 'tm_settings_hook' ) );
			add_action( 'tm_woocommerce_settings_' . 'epo_page_options' . '_end', 	array( $this, 'tm_settings_hook_end' ) );
			
			add_action( 'woocommerce_settings_' . $this->id, 						array( $this, 'tm_settings_hook_all_end' ) );
		}

		public function tm_echo_header($counter=0,$label="") {
			echo '<div class="tm-box">'
				. '<h4 class="tab-header '.($counter == 1?'open':'closed').'" data-id="tmsettings'.$counter.'-tab">'
				. $label
				. '<span class="tcfa tm-arrow2 tcfa-angle-down2"></span></h4>'
				. '</div>';
		}

		public function tm_title_setting($value) {
			if ( ! empty( $value['id'] ) ) {
				do_action( 'tm_woocommerce_settings_' . sanitize_title( $value['id'] ) );
			}
			if ( ! empty( $value['title'] ) ) {
				echo '<h3 class="tm-section-title">' . esc_html( $value['title'] ) . '</h3>';
			}
			if ( ! empty( $value['desc'] ) ) {
				echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
			}
			echo '<table class="form-table">'. "\n\n";
		}

		public function tm_html_setting($value) {
			if ( ! isset( $value['id'] ) ) {
				$value['id'] = '';
			}
			if ( ! isset( $value['title'] ) ) {
				$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
			}

			if ( ! empty( $value['id'] ) ) {
				do_action( 'tm_woocommerce_settings_' . sanitize_title( $value['id'] ) );
			}?>
			<tr valign="top">
						<td colspan="2" class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
							<?php 
								if ( ! empty( $value['html'] ) ) {
									echo  $value['html'] ;
								} 
							?>
						</td>
					</tr>
			<?php
		}

		public function tm_sectionend_setting($value) {
			echo '</table>';
			if ( ! empty( $value['id'] ) ) {
				do_action( 'tm_woocommerce_settings_' . sanitize_title( $value['id'] ) . '_end' );
			}
		}

		public function tm_tabs_header_setting() {
			
			echo '<div class="tm-settings-wrap tm_wrapper">';
			
				echo '<div class="header"><h3>'.__( 'Extra Product Options Settings', TM_EPO_TRANSLATION ).'</h3></div>';
					
				echo '<div class="transition tm-tabs">';
						
					echo '<div class="transition tm-tab-headers tmsettings-tab">';

					$counter = 1;
					foreach ($this->settings_options as $key => $label) {
						if ($key=="other"){
							$_other_settings = $this->get_other_settings_headers();
							foreach ($_other_settings as $h_key => $h_label) {
								$this->tm_echo_header($counter,$h_label);
								$counter++;
							}
						}else{
							$this->tm_echo_header($counter,$label);
							$counter++;							
						}
					}
				
					echo '</div>';
			
		}

		public function tm_settings_hook() {
			$this->tab_count++;
			echo '<div class="transition tm-tab tmsettings'.$this->tab_count.'-tab">';
		}

		public function tm_settings_hook_end() {
			echo '</div>';
		}

		public function tm_settings_hook_all_end() {
			echo '</div></div>'; // close .transition.tm-tabs , .tm-settings-wrap
		}

		public function get_other_settings_headers(){
			$headers=array();
			if(class_exists('RP_WCDPD')){
				$headers["dpd"] = __( 'Dynamic Pricing & Discounts', TM_EPO_TRANSLATION );
			}
			if(class_exists('WC_Bookings')){
				$headers["bookings"] = __( 'WooCommerce Bookings', TM_EPO_TRANSLATION );
			}
			return apply_filters('tm_epo_settings_headers',$headers);
		}

		public function get_other_settings(){
			$settings=array();
			$_other_settings = $this->get_other_settings_headers();
			if(class_exists('RP_WCDPD')){
				$label = $_other_settings["dpd"];
				$settings["dpd"] =array(
					array(  
						'type' => 'tm_title', 				
						'id' => 'epo_page_options',
						'title' => $label 
					),
					
					array(
						'title' => __( 'Enable discounts on extra options', TM_EPO_TRANSLATION ),
						'desc' 		=> '<span>'.__( 'Enabling this will apply the product discounts to the extra options as well.', TM_EPO_TRANSLATION ).'</span>',
						'id' 		=> 'tm_epo_dpd_enable',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'no',
						'type' 		=> 'select',
						'options' 	=> array(
							'no' 	=> __( 'Disable', TM_EPO_TRANSLATION ),
							'yes' 	=> __( 'Enable', TM_EPO_TRANSLATION )						
						),
						'desc_tip'	=>  false,
					),
					array(
						'title' => __( 'Prefix label', TM_EPO_TRANSLATION ),
						'desc' 		=> '<span>'.__( 'Display a prefix label on product page.', TM_EPO_TRANSLATION ).'</span>',
						'id' 		=> 'tm_epo_dpd_prefix',
						'default'	=> '',
						'type' 		=> 'text',					
						'desc_tip'	=>  false,
					),
					array(
						'title' => __( 'Suffix label', TM_EPO_TRANSLATION ),
						'desc' 		=> '<span>'.__( 'Display a suffix label on product page.', TM_EPO_TRANSLATION ).'</span>',
						'id' 		=> 'tm_epo_dpd_suffix',
						'default'	=> '',
						'type' 		=> 'text',					
						'desc_tip'	=>  false,
					),

					array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),

				);
			}

			if(class_exists('WC_Bookings')){
				$label = $_other_settings["bookings"];
				$settings["bookings"] =array(
					array(  
						'type' => 'tm_title', 				
						'id' => 'epo_page_options',
						'title' => $label 
					),
					
					array(
						'title' => __( 'Multiply cost by person count', TM_EPO_TRANSLATION ),
						'desc' 		=> '<span>'.__( 'Enabling this will multiply the options price by the person count.', TM_EPO_TRANSLATION ).'</span>',
						'id' 		=> 'tm_epo_bookings_person',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'yes',
						'type' 		=> 'select',
						'options' 	=> array(
							'no' 	=> __( 'Disable', TM_EPO_TRANSLATION ),
							'yes' 	=> __( 'Enable', TM_EPO_TRANSLATION )						
						),
						'desc_tip'	=>  false,
					),
					array(
						'title' => __( 'Multiply cost by block count', TM_EPO_TRANSLATION ),
						'desc' 		=> '<span>'.__( 'Enabling this will multiply the options price by the block count.', TM_EPO_TRANSLATION ).'</span>',
						'id' 		=> 'tm_epo_bookings_block',
						'class'		=> 'chosen_select',
						'css' 		=> 'min-width:300px;',
						'default'	=> 'yes',
						'type' 		=> 'select',
						'options' 	=> array(
							'no' 	=> __( 'Disable', TM_EPO_TRANSLATION ),
							'yes' 	=> __( 'Enable', TM_EPO_TRANSLATION )						
						),
						'desc_tip'	=>  false,
					),

					array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),

				);
			}

			return apply_filters('tm_epo_settings_settings',$settings);
		}

		public function get_setting_array($setting,$label){
			$method="_get_setting_".$setting;
			return $this->$method($setting,$label);
		}

		private function _get_setting_general($setting,$label){
			return array(
					array( 
						'type' 	=> 'tm_title',				
						'id' 	=> 'epo_page_options',
						'title' => $label
						),
					array(
							'title' 	=> __( 'Enable front-end for roles', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select the roles that will have access to the extra options.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_roles_enabled',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> '@everyone',
							'type' 		=> 'multiselect',
							'options' 	=> tm_get_roles(),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Disable front-end for roles', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select the roles that will not have access to the extra options.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_roles_disabled',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> '',
							'type' 		=> 'multiselect',
							'options' 	=> tm_get_roles(),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Final total box', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select when to show the final total box', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_final_total_box',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'normal',
							'type' 		=> 'select',
							'options' 	=> array(
								'normal' 				=> __( 'Show Both Final and Options total box', TM_EPO_TRANSLATION ),
								'final' 				=> __( 'Show only Final total', TM_EPO_TRANSLATION ),
								'hideoptionsifzero' 	=> __( 'Show Final total and hide Options total if zero', TM_EPO_TRANSLATION ),
								'hideifoptionsiszero' 	=> __( 'Hide Final total box if Options total is zero', TM_EPO_TRANSLATION ),
								'hide' 					=> __( 'Hide Final total box', TM_EPO_TRANSLATION ),
								'pxq' 					=> __( 'Always show only Final total (Price x Quantity)', TM_EPO_TRANSLATION ),
								'disable' 				=> __( 'Disable', TM_EPO_TRANSLATION ),
							),
							'desc_tip'	=>  false,
						),		
					array(
							'title' 	=> __( 'Enable Final total box for all products', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check to enable Final total box even when product has no extra options', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_enable_final_total_box_all',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Strip html from emails', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check to strip the html tags from emails', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_strip_html_from_emails',
							'default' 	=> 'yes',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Disable lazy load images', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check to disable lazy loading images.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_no_lazy_load',
							'default' 	=> 'yes',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),				
					array(
							'title' 	=> __( 'Enable plugin for WooCommerce shortcodes', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enabling this will load the plugin files to all WordPress pages. Use with caution.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_enable_shortcodes',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),				
					array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),
			);
		}

		private function _get_setting_display($setting,$label){
			return array(
					array(
						'type' 	=> 'tm_title', 
						'id' 	=> 'epo_page_options',
						'title' => $label
					),
					array(
							'title' 	=> __( 'Display', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'This controls how your fields are displayed on the front-end.<br />If you choose "Show using action hooks" you have to manually write the code to your theme or plugin to display the fields and the placement settings below will not work. <br />If you use Composite Products extension you must leave this setting to "Normal" otherwise the extra options cannot be displayed on the composite product bundles.<br />See more at the documentation.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_display',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'normal',
							'type' 		=> 'select',
							'options' 	=> array(
								'normal' => __( 'Normal', TM_EPO_TRANSLATION ),
								'action' => __( 'Show using action hooks', TM_EPO_TRANSLATION ),
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Extra Options placement', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select where you want the extra options to appear.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_options_placement',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'woocommerce_before_add_to_cart_button',
							'type' 		=> 'select',
							'options' 	=> array(
								'woocommerce_before_add_to_cart_button' 		=> __( 'Before add to cart button', TM_EPO_TRANSLATION ),
								'woocommerce_after_add_to_cart_button' 			=> __( 'After add to cart button', TM_EPO_TRANSLATION ),
								
								'woocommerce_before_add_to_cart_form' 			=> __( 'Before cart form', TM_EPO_TRANSLATION ),
								'woocommerce_after_add_to_cart_form' 			=> __( 'After cart form', TM_EPO_TRANSLATION ),
								
								'woocommerce_before_single_product' 			=> __( 'Before product', TM_EPO_TRANSLATION ),
								'woocommerce_after_single_product' 				=> __( 'After product', TM_EPO_TRANSLATION ),
								
								'woocommerce_before_single_product_summary' 	=> __( 'Before product summary', TM_EPO_TRANSLATION ),
								'woocommerce_after_single_product_summary' 		=> __( 'After product summary', TM_EPO_TRANSLATION ),
								
								'woocommerce_product_thumbnails' 				=> __( 'After product image', TM_EPO_TRANSLATION ),

								'custom' 										=> __( 'Custom hook', TM_EPO_TRANSLATION ),
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Extra Options placement custom hook', TM_EPO_TRANSLATION ),
							'desc' 		=> '',
							'id' 		=> 'tm_epo_options_placement_custom_hook',
							'default'	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Totals box placement', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select where you want the Totals box to appear.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_totals_box_placement',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'woocommerce_before_add_to_cart_button',
							'type' 		=> 'select',
							'options' 	=> array(
								'woocommerce_before_add_to_cart_button' 		=> __( 'Before add to cart button', TM_EPO_TRANSLATION ),
								'woocommerce_after_add_to_cart_button' 			=> __( 'After add to cart button', TM_EPO_TRANSLATION ),
								
								'woocommerce_before_add_to_cart_form' 			=> __( 'Before cart form', TM_EPO_TRANSLATION ),
								'woocommerce_after_add_to_cart_form' 			=> __( 'After cart form', TM_EPO_TRANSLATION ),
								
								'woocommerce_before_single_product' 			=> __( 'Before product', TM_EPO_TRANSLATION ),
								'woocommerce_after_single_product' 				=> __( 'After product', TM_EPO_TRANSLATION ),
								
								'woocommerce_before_single_product_summary' 	=> __( 'Before product summary', TM_EPO_TRANSLATION ),
								'woocommerce_after_single_product_summary' 		=> __( 'After product summary', TM_EPO_TRANSLATION ),
								
								'woocommerce_product_thumbnails' 				=> __( 'After product image', TM_EPO_TRANSLATION ),

								'custom' 										=> __( 'Custom hook', TM_EPO_TRANSLATION ),
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Totals box placement custom hook', TM_EPO_TRANSLATION ),
							'desc' 		=> '',
							'id' 		=> 'tm_epo_totals_box_placement_custom_hook',
							'default'	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Floating Totals box', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'This will enable a floating box to display your totals box.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_floating_totals_box',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'disable',
							'type' 		=> 'select',
							'options' 	=> array(
								'disable' 		=> __( 'Disable', TM_EPO_TRANSLATION ),
								'bottom right' 	=> __( 'Bottom right', TM_EPO_TRANSLATION ),
								'bottom left' 	=> __( 'Bottom left', TM_EPO_TRANSLATION ),
								'top right' 	=> __( 'Top right', TM_EPO_TRANSLATION ),
								'top left' 		=> __( 'Top left', TM_EPO_TRANSLATION ),
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Force Select Options', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'This changes the add to cart button to display select options when the product has extra product options.<br />Enabling this will remove the ajax functionality.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_force_select_options',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'normal',
							'type' 		=> 'select',
							'options' 	=> array(
								'normal' 	=> __( 'Disable', TM_EPO_TRANSLATION ),
								'display' 	=> __( 'Enable', TM_EPO_TRANSLATION ),
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Enable extra options in shop and category view', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check to enable the display of extra options on the shop page and category view. This setting is theme dependent and some aspect may not work as expected.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_enable_in_shop',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Remove Free price label', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check to remove Free price label when product has extra options', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_remove_free_price_label',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title'		=> __( 'Hide uploaded file path', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check to hide the uploaded file path from users.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_hide_upload_file_path',
							'default' 	=> 'yes',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Show quantity selector only for elements with a value', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check show quantity selector only for elements with a value.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_show_only_active_quantities',
							'default' 	=> 'yes',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Hide add-to-cart button until an option is chosen', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check this to show the add to cart button only when at least one option is filled.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_hide_add_cart_button',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Auto hide price if zero', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check this to globally hide the price display if it is zero.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_auto_hide_price_if_zero',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					(TM_EPO_WPML()->is_active())
					?
					array(
							'title' 	=> __( 'Use translated values when possible on admin Order', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Please note that if the options on the Order change or get deleted you will get wrong results by enabling this!', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_wpml_order_translate',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						)
					:array(),
					array(
							'title' 	=> __( 'Use the "From" string on displayed product prices', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check this to alter the price display of a product when it has extra options with prices.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_use_from_on_price',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),
			);
		}

		private function _get_setting_cart($setting,$label){
			return array(
					array(  
						'type' 	=> 'tm_title', 				
						'id' 	=> 'epo_page_options',
						'title' => $label 
						),
					array(
							'title' 	=> __( 'Clear cart button', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enables or disables the clear cart button', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_clear_cart_button',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'normal',
							'type' 		=> 'select',
							'options' 	=> array(
								'normal' 	=> __( 'Hide', TM_EPO_TRANSLATION ),
								'show' 		=> __( 'Show', TM_EPO_TRANSLATION )
								
							),
							'desc_tip'	=>  false,
						),	
					array(
							'title' 	=> __( 'Cart Field Display', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select how to display your fields in the cart', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_cart_field_display',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'normal',
							'type' 		=> 'select',
							'options' 	=> array(
								'normal' 	=> __( 'Normal display', TM_EPO_TRANSLATION ),
								'link' 		=> __( 'Display a pop-up link', TM_EPO_TRANSLATION ),
								'advanced' 	=> __( 'Advanced display', TM_EPO_TRANSLATION )
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Hide extra options in cart', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enables or disables the display of options in the cart.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_hide_options_in_cart',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'normal',
							'type' 		=> 'select',
							'options' 	=> array(
								'normal' 	=> __( 'Show', TM_EPO_TRANSLATION ),
								'hide' 		=> __( 'Hide', TM_EPO_TRANSLATION )
								
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Hide extra options prices in cart', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enables or disables the display of prices of options in the cart.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_hide_options_prices_in_cart',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'normal',
							'type' 		=> 'select',
							'options' 	=> array(
								'normal' 	=> __( 'Show', TM_EPO_TRANSLATION ),
								'hide' 		=> __( 'Hide', TM_EPO_TRANSLATION )
								
							),
							'desc_tip'	=>  false,
						),
					version_compare( get_option( 'woocommerce_db_version' ), '2.3', '<' )?
					array():
					array(
							'title' 	=> __( 'Prevent negative priced products', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Prevent adding to the cart negative priced products.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_no_negative_priced_products',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Show image replacement in cart and checkout', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enabling this will show the images of elements that have an image replacement.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_show_image_replacement',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),

			);			
		}

		private function _get_setting_string($setting,$label){
			return array(
					array(  
						'type' 	=> 'tm_title', 				
						'id' 	=> 'epo_page_options',
						'title' => $label 
						),
					array(
							'title' 	=> __( 'Cart field/value separator', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter the field/value separator for the cart.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_separator_cart_text',
							'default'	=> ':',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Final total text', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter the Final total text or leave blank for default.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_final_total_text',
							'default'	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),

					array(
							'title' 	=> __( 'Options total text', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter the Options total text or leave blank for default.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_options_total_text',
							'default'	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),

					(tm_woocommerce_subscriptions_check())?
					array(
							'title' 	=> __( 'Subscription fee text', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter the Subscription fee text or leave blank for default.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_subscription_fee_text',
							'default'	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						):
					array(),

					array(
							'title'	 	=> __( 'Free Price text replacement', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the Free price label when product has extra options.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_replacement_free_price_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Reset Options text replacement', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the Reset options text when using custom variations.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_reset_variation_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Edit Options text replacement', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the Edit options text on the cart.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_edit_options_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title'	 	=> __( 'Additional Options text replacement', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the Additional options text when using the pop up setting on the cart.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_additional_options_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Close button text replacement', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the Close button text when using the pop up setting on the cart.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_close_button_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Calendar close button text replacement', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the Close button text on the calendar.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_closetext',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Calendar today button text replacement', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the Today button text on the calendar.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_currenttext',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Slider previous text', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the previous button text for slider.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_slider_prev_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Slider next text', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the next button text for slider.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_slider_next_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Force Select options text', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the add to cart button text when using the Force select option.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_force_select_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Empty cart text', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the empty cart button text.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_empty_cart_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'This field is required text', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text indicate that a field is required.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_this_field_is_required_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Characters remaining text', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a text to replace the Characters remaining when using maximum characters on a text field or a textarea.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_characters_remaining_text',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),

					array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),
			);			
		}

		private function _get_setting_style($setting,$label){
			return array(
					array(  
						'type' 	=> 'tm_title', 				
						'id' 	=> 'epo_page_options',
						'title' => $label 
						),
					
					array(
							'title' 	=> __( 'Enable checkbox and radio styles', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enables or disables extra styling for checkboxes and radio buttons.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_css_styles',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> '',
							'type' 		=> 'select',
							'options' 	=> array(
								'' 			=> __( 'Disable', TM_EPO_TRANSLATION ),
								'on' 		=> __( 'Enable', TM_EPO_TRANSLATION )
								
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Style', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select a style.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_css_styles_style',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'round',
							'type' 		=> 'select',
							'options' 	=> array(
								'round' 	=> __( 'Round', TM_EPO_TRANSLATION ),
								'square' 	=> __( 'Square', TM_EPO_TRANSLATION )
								
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Select item border type', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select a style for the selected border when using image replacements or swatches.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_css_selected_border',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> '',
							'type' 		=> 'select',
							'options' 	=> array(
								'' 			=> __( 'Default', TM_EPO_TRANSLATION ),
								'square' 	=> __( 'Square', TM_EPO_TRANSLATION ),
								'round' 	=> __( 'Round', TM_EPO_TRANSLATION ),
								'shadow' 	=> __( 'Shadow', TM_EPO_TRANSLATION ),
								'thinline' 	=> __( 'Thin line', TM_EPO_TRANSLATION ),								
							),
							'desc_tip'	=>  false,
						),
			
					array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),

			);			
		}

		private function _get_setting_global($setting,$label){
			return array(
					array(  
						'type' 	=> 'tm_title', 				
						'id' 	=> 'epo_page_options',
						'title' => $label 
						),
					array(
							'title' 	=> __( 'Enable validation', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check to enable validation feature for builder elements', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_enable_validation',
							'default' 	=> 'yes',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Prevent options from being sent to emails', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Check to disable options from being sent to emails.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_prevent_options_from_emails',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Reset option values after the product is added to the cart', TM_EPO_TRANSLATION ),
							'desc' 		=> '',
							'id' 		=> 'tm_epo_global_reset_options_after_add',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Input decimal separator', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( ' Choose how to determine the decimal separator for user inputs', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_input_decimal_separator',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> '',
							'type' 		=> 'select',
							'options' 	=> array(
								'' 			=> __( 'Use WooCommerce value', TM_EPO_TRANSLATION ),
								'browser' 	=> __( 'Determine by browser local', TM_EPO_TRANSLATION ),
								
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Displayed decimal separator', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( ' Choose which decimal separator to display on currency prices', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_displayed_decimal_separator',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> '',
							'type' 		=> 'select',
							'options' 	=> array(
								'' 			=> __( 'Use WooCommerce value', TM_EPO_TRANSLATION ),
								'browser' 	=> __( 'Determine by browser local', TM_EPO_TRANSLATION ),
								
							),
							'desc_tip'	=>  false,
						),

					array(
							'title' 	=> __( 'Radio button undo button', TM_EPO_TRANSLATION ),
							//'desc' 		=> '<span>'.__( '', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_radio_undo_button',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> '',
							'type' 		=> 'select',
							'options' 	=> array(
								'' 			=> __( 'Use field value', TM_EPO_TRANSLATION ),
								'enable' 	=> __( 'Enable', TM_EPO_TRANSLATION ),
								'disable' 	=> __( 'Disable', TM_EPO_TRANSLATION )
								
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Required state indicator', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Enter a string to indicate the required state of a field.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_required_indicator',
							'default'	=> '*',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Required state indicator position', TM_EPO_TRANSLATION ),
							//'desc' 		=> '<span>'.__( '', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_required_indicator_position',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> 'left',
							'type' 		=> 'select',
							'options' 	=> array(
								'left' 		=> __( 'Left of the label', TM_EPO_TRANSLATION ),
								'right' 	=> __( 'Right of the label', TM_EPO_TRANSLATION )
								
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Include tax string suffix on totals box', TM_EPO_TRANSLATION ),
							'id' 		=> 'tm_epo_global_tax_string_suffix',
							'default' 	=> 'no',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Load generated styles inline', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'This will prevent some load flickering but it will produce invalid html.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_load_generated_styles_inline',
							'default' 	=> 'yes',
							'type' 		=> 'checkbox',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Datepicker theme', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select the theme for the datepicker.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_datepicker_theme',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> '',
							'type' 		=> 'select',
							'options' 	=> array(
								''			=> __( 'Use field value', TM_EPO_TRANSLATION ),
								'epo' 		=> __( 'Epo White', TM_EPO_TRANSLATION ),
								'epo-black' => __( 'Epo Black', TM_EPO_TRANSLATION ),
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Datepicker size', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select the size of the datepicker.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_datepicker_size',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> '',
							'type' 		=> 'select',
							'options' 	=> array(
								''			=> __( 'Use field value', TM_EPO_TRANSLATION ),
								'small' 	=> __( 'Small', TM_EPO_TRANSLATION ),
								'medium' 	=> __( 'Medium', TM_EPO_TRANSLATION ),
								'large' 	=> __( 'Large', TM_EPO_TRANSLATION ),
							),
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Datepicker position', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Select the position of the datepicker.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_global_datepicker_position',
							'class'		=> 'chosen_select',
							'css' 		=> 'min-width:300px;',
							'default'	=> '',
							'type' 		=> 'select',
							'options' 	=> array(
								''			=> __( 'Use field value', TM_EPO_TRANSLATION ),
								'normal' 	=> __( 'Normal', TM_EPO_TRANSLATION ),
								'top' 		=> __( 'Top of screen', TM_EPO_TRANSLATION ),
								'bottom' 	=> __( 'Bottom of screen', TM_EPO_TRANSLATION ),
							),
							'desc_tip'	=>  false,
						),							
					array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),

			);			
		}

		private function _get_setting_other($setting,$label){
			$settings = array();
			$other = $this->get_other_settings();
			foreach ($other as $key => $setting) {
				$settings = array_merge($settings,$setting);
			}
			return $settings;
		}

		private function _get_setting_license($setting,$label){
			$_license_settings=(!defined('TM_DISABLE_LICENSE'))?
				array(				
					array( 
						'type' 	=> 'tm_title', 
						'id' 	=> 'epo_page_options',
						'title' => $label 
						),
					array(
							'title' 	=> __( 'Username', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Your Envato username.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_envato_username',
							'default'	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
							
							//'custom_attributes'=>(TM_EPO_LICENSE()->get_license())?array('disabled'=>'disabled'):""
						),
					array(
							'title' 	=> __( 'Envato API Key', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'You can find your API key by visiting your Account page then clicking the My Settings tab. At the bottom of the page you’ll find your account’s API key and a button to regenerate it as needed.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_envato_apikey',
							'default'	=> '',
							'type' 		=> 'password',					
							'desc_tip'	=>  false,
						),
					array(
							'title' 	=> __( 'Purchase code', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span><p>'.__( 'Please enter your <strong>CodeCanyon WooCommerce Extra Product Options purchase code</strong>.', TM_EPO_TRANSLATION ).'</p><p>'.__( 'To access your Purchase Code for an item:', TM_EPO_TRANSLATION ).'</p>'
											.'<ol>'
											.'<li>'.__('Log into your Marketplace account', TM_EPO_TRANSLATION ).'</li>'
											.'<li>'.__('From your account dropdown links, select "Downloads"', TM_EPO_TRANSLATION ).'</li>'
											.'<li>'.__('Click the "Download" button that corresponds with your purchase', TM_EPO_TRANSLATION ).'</li>'
											.'<li>'.__('Select the "License certificate &amp; purchase code" download link. Your Purchase Code will be displayed within the License Certificate.', TM_EPO_TRANSLATION ).'</li>'
											.'</ol>'
											.'<p><img alt="Purchase Code Location" src="'.TM_EPO_PLUGIN_URL.'/assets/images/download_button.gif" title="Purchase Code Location" style="vertical-align: middle;"></p>'
											.'<span class="tm-license-button">'
											
											.'<a href="#" class="'.(TM_EPO_LICENSE()->get_license()?"":"tm-hidden ").'tm-button button button-primary button-large tm-deactivate-license" id="tm_deactivate_license">'.__('Deactivate License', TM_EPO_TRANSLATION ).'</a>'
											.'<a href="#" class="'.(TM_EPO_LICENSE()->get_license()?"tm-hidden ":"").'tm-button button button-primary button-large tm-activate-license" id="tm_activate_license">'.__('Activate License', TM_EPO_TRANSLATION ).'</a>'
											
											.'</span>'
											.'<span class="tm-license-result">'
											.((TM_EPO_LICENSE()->get_license())?
											"<span class='activated'><p>".__("License activated.",TM_EPO_TRANSLATION)."</p></span>"
											:""
											)
											.'</span>'
											.'</span>',
							'id' 		=> 'tm_epo_envato_purchasecode',
							'default' 	=> '',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
							//'custom_attributes'=>(TM_EPO_LICENSE()->get_license())?array('disabled'=>'disabled'):""
						),
					array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),		
				):array();
			
			return $_license_settings;
		}

		private function _get_setting_upload($setting,$label){
			$html=TM_EPO_HELPER()->file_manager(TM_EPO()->upload_dir,'');

			$_upload_settings=
				array(				
					array( 
						'type' 	=> 'tm_title', 
						'id' 	=> 'epo_page_options',
						'title' => $label 
						),
					array(
							'title' 	=> __( 'Upload folder', TM_EPO_TRANSLATION ),
							'desc' 		=> '<span>'.__( 'Changing this will only affect future uploads.', TM_EPO_TRANSLATION ).'</span>',
							'id' 		=> 'tm_epo_upload_folder',
							'default'	=> 'extra_product_options',
							'type' 		=> 'text',					
							'desc_tip'	=>  false,
							
							//'custom_attributes'=>(TM_EPO_LICENSE()->get_license())?array('disabled'=>'disabled'):""
						),
					array( 
						'type' 	=> 'tm_html', 
						'id' 	=> 'epo_page_options_html',
						'title' => __( 'File manager', TM_EPO_TRANSLATION ),
						'html' 	=> $html 
						),
					array( 'type' => 'tm_sectionend', 'id' => 'epo_page_options' ),		
				);
			
			return $_upload_settings;
		}

		/**
		 * Get settings array
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = array();
			$settings = array_merge($settings, array(array( 'type' => 'tm_tabs_header' )) );

			foreach ($this->settings_array as $key => $value) {
				$settings = array_merge($settings, $value );
			}

			return apply_filters( 'tm_' . $this->id . '_settings', 
				$settings
			); // End pages settings
		}
	}

}
?>