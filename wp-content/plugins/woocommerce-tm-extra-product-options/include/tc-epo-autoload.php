<?php
// Direct access security
if (!defined('TM_EPO_PLUGIN_SECURITY')){
	die();
}

/** Auto-load classes on demand **/
function tc_epo_autoload( $class ) {
    
    $path  = null;
    $original_class = $class;
    $class = strtolower( $class );
    $file = 'class-' . str_replace( '_', '-', $class ) . '.php';

    if ( strpos( $class, 'tm_epo_fields' ) === 0 ) {
        $path = TM_EPO_PLUGIN_PATH . '/include/fields/';
    } elseif ( strpos( $class, 'tm_epo_admin_' ) === 0 ) {
        $path = TM_EPO_PLUGIN_PATH . '/admin/';
    } elseif ( strpos( $class, 'tm_extra_' ) === 0 ) {
        $path = TM_EPO_PLUGIN_PATH . '/include/';
    } elseif ( strpos( $class, 'tm_epo_' ) === 0 ) {
        if (strpos( $class, 'tm_epo_compatibility' ) === 0 ){
            $path = TM_EPO_PLUGIN_PATH . '/include/compatibility/';
        }else{
            $path = TM_EPO_PLUGIN_PATH . '/include/';
        }
        
    } 

    // Composite products sometimes do not load the Discount and Pricing classes
    if ( $original_class=="RP_WCDPD_Pricing" && defined('TM_EPO_INCLUDED') && defined('RP_WCDPD_PLUGIN_PATH') ){
        $path = RP_WCDPD_PLUGIN_PATH . 'includes/classes/';
        $file = 'Pricing.php';
    }                   
                
    if ( $path && is_readable( $path . $file ) ) {
        include_once( $path . $file );
        return;
    }

}

?>