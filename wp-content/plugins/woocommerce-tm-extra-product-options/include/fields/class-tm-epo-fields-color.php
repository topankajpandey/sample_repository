<?php
class TM_EPO_FIELDS_color extends TM_EPO_FIELDS {

	public function display_field( $element=array(), $args=array() ) {
		return array(
				'textbeforeprice' 	=> isset( $element['text_before_price'] )?$element['text_before_price']:"",
				'textafterprice' 	=> isset( $element['text_after_price'] )?$element['text_after_price']:"",
				'hide_amount'  		=> isset( $element['hide_amount'] )?" ".$element['hide_amount']:"",
				'default_value'  	=> isset( $element['default_value'] )?esc_attr(  $element['default_value']  ):'',
				'quantity' 			=> isset( $element['quantity'] )?$element['quantity']:"",
			);
	}

	public function validate() {

		$passed = true;
		$message = array();
		
		$quantity_once = false;
		foreach ( $this->field_names as $attribute ) {
			if (!$quantity_once && isset($this->epo_post_fields[$attribute]) && $this->epo_post_fields[$attribute]!=="" && isset($this->epo_post_fields[$attribute.'_quantity']) && !$this->epo_post_fields[$attribute.'_quantity']>0){
				$passed = false;
				$quantity_once = true;
				$message[] = sprintf( __( 'The quantity for "%s" must be greater than 0', TM_EPO_TRANSLATION ),  $this->element['label'] );
			}
			if($this->element['required']){			
				if ( !isset( $this->epo_post_fields[$attribute] ) ||  $this->epo_post_fields[$attribute]=="" ) {
					$passed = false;
					$message[] = 'required';
					break;
				}										
			}
		}

		return array('passed'=>$passed,'message'=>$message);
	}
	
}