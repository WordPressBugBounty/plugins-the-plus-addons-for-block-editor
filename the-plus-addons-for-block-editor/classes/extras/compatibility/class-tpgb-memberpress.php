<?php
/**
 * MemberPress Course Compatibility
 * 
 * @since 4.5.10
 */

 if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

 if( !class_exists('Tpgb_Memberpress_Compat') ){
    
    final class Tpgb_Memberpress_Compat {

        /**
         * Instance
         */
        private static $instance;

        /**
         *  Initiator
         */
        public static function instance() {
            if ( ! isset( self::$instance ) ) {
                self::$instance = new Tpgb_Memberpress_Compat();

                add_filter('mpcs_classroom_style_handles', array( self::$instance,'memberpress_remove_style') );
            }

            return self::$instance;
        }

        /*
        * MemberPress Course Compatibility
        * @since 3.0.5
        * */
        public function memberpress_remove_style( $allowed_handles = [] ){
            global $wp_styles;
            if(!empty($wp_styles)){
                foreach($wp_styles->queue as $style) {
                    $handle = $wp_styles->registered[$style]->handle;
                    if(preg_match('/^tpgb-/i', $handle) || substr( $handle, 0, 13 ) === 'plus-preview-' || substr( $handle, 0, 10 ) === 'plus-post-' || substr( $handle, 0, 7 ) === 'theplus' || $handle === 'plus-global'){
                        $allowed_handles[] = $handle;
                    }
                }
            }
            return $allowed_handles;
        }
    }

    Tpgb_Memberpress_Compat::instance();
 }
