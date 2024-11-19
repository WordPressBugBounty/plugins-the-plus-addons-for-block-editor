<?php
/**
 * Nexter Blocks Loader.
 * @since 1.0.0
 * @package TP_Gutenberg_Loader
 */
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( !class_exists( 'TP_Gutenberg_Loader' ) ) {
    
    /**
     * Class TP_Gutenberg_Loader.
     */
    final class TP_Gutenberg_Loader {
        
        /**
         * Member Variable
         *
         * @var instance
         */
        private static $instance;
        
        public $post_assets_objects = array();

        /**
         *  Initiator
         */
        public static function get_instance() {
            if ( !isset( self::$instance ) ) {
                self::$instance = new self;
            } 
            return self::$instance;
        }
        
        /**
         * Constructor
         */
        public function __construct() {
            
            $this->loader_helper();
            
            add_action( 'plugins_loaded', array( $this, 'tp_plugin_loaded' ) );
            $show_notice = true;
            if(defined('TPGBP_VERSION')){
                $white_label = get_option( 'tpgb_white_label' );
                if(!empty($white_label) && isset($white_label['tpgb_hidden_label']) && !empty($white_label['tpgb_hidden_label'])){
                    $show_notice = false;
                }
                $activate_pro = get_option( 'tpgbp_license_status' );
                if(!empty($activate_pro) && isset($activate_pro['status']) && $activate_pro['status']=='valid' && isset($activate_pro['expired']) && $activate_pro['expired'] == 'lifetime'){
                    $show_notice = false;
                }
            }
            if ( !empty($show_notice) ) {
                add_action( 'admin_notices', array( $this, 'nxt_halloween_offer' ) );
            }
            if ( is_admin() ) {
                add_filter( 'plugin_action_links_' . TPGB_BASENAME, array( $this, 'tpgb_settings_pro_link' ) );
                add_filter( 'plugin_row_meta', array( $this, 'tpbg_extra_links_plugin_row_meta' ), 10, 2 );
                add_action( 'after_plugin_row', array( $this, 'nxt_plugins_page_rebranding_banner' ), 10, 1 );
            }
            add_action( 'wp_ajax_nxt_dismiss_plugin_rebranding', array( $this,'nxt_dismiss_plugin_rebranding_callback' ), 10, 1 );
            add_action( 'wp_ajax_nxt_dismiss_plugin_halloween', array( $this,'nxt_dismiss_plugin_halloween' ), 10, 1 );
        }
        
        /**
         * Black Friday Sale Admin Notice
         *
         * @param $plugin_file
         *
         * @since 4.0.3
         */
        public function nxt_halloween_offer() {

            if ( ! get_option('nxt_blackfriday_dismissed')) {
                echo '<div class="nxt-plugin-halloween notice">
                        
                        <div class="inline nxt-plugin-halloween-notice" style="display: flex;column-gap: 12px;align-items: center;padding: 15px;position: relative;    margin-left: 0px;">
                            <img style="max-width: 110px;max-height: 110px;" src="'.esc_url( TPGB_URL.'/assets/images/black-friday-admin.png' ).'" />
                            <div style="margin: .7rem .8rem .8rem;">  
                                <h3 style="margin-top:10px;margin-bottom:7px;">' . esc_html__( "Best Time to Upgrade to Nexter Blocks Pro – Save $300!", "tpgb" ) . '</h3>
                                <p> '. esc_html__( "Our Black Friday Sale is live! Upgrade now and save $300 on the pro version.", "tpgb" ) .' </p>
                                <p style="display: flex;column-gap: 12px;">  <span> • '. esc_html__("1,000+ WordPress Templates").'</span>  <span> • '. esc_html__("90+ WordPress Blocks").'</span>  <span> • '. esc_html__("Trusted by 10K+ Users").'</span> </p>
                                <a href="'.esc_url('https://nexterwp.com/pricing/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage').'" class="button" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Claim Your Offer', 'tpgb') . '</a>
                            </div>
                            <span class="nxt-halloween-notice-dismiss"></span>
                        </div></div>';
            }
        }

         /**
         * Adds a small banner to the plugins.php admin page
         *
         * @param $plugin_file
         *
         * @since 4.0.2
         */
        public function nxt_plugins_page_rebranding_banner( $plugin_file ) {
            if ( ! get_option('nxt_rebranding_dismissed') ) {
                
                $plugin_file_array = explode( '/', $plugin_file );
                if ( end( $plugin_file_array ) === 'the-plus-addons-for-block-editor.php' ) {
                    echo '<tr class="nxt-plugin-rebranding-update">
                        <td colspan="4" style="padding: 20px 40px; background: #f0f6fc; border-left: 4px solid #72aee6; box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.1);">
                        <div class="nxt-plugin-update-notice inline notice notice-alt notice-warning">
                            <h4 style="margin-top:10px;margin-bottom:7px;font-size:14px;">' . esc_html__( "The Plus Blocks for Gutenberg is now Nexter Blocks : Better UI, Faster Performance & Improved Features", "tpgb" ) . '</h4>
                            <a target="_blank" rel="noopener noreferrer" href="'.esc_url('https://nexterwp.com/blog/all-new-nexter-experience-unified-solution-wordpress-website-building?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings').'" style="text-decoration:underline;margin-bottom:10px;display:inline-block;">' . esc_html__( 'Read What\'s New & What Changed?', 'tpgb') . '</a>
                            <span class="nxt-plugin-notice-dismiss"></span>
                        </div>
                        </td></tr>';
                }
            }
        }

        /**
         * Halloween Notice disable
         * @since 4.0.3
         */
        public function nxt_dismiss_plugin_halloween() {
            // Verify nonce for security
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tpgb-addons' ) ) {
                wp_send_json_error( array( 'message' => esc_html__('Invalid nonce. Unauthorized request.', 'tpgb') ) );
            }
        
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => esc_html__('Insufficient permissions.', 'tpgb') ) );
            }
        
            $option_key = 'nxt_blackfriday_dismissed';
            update_option( $option_key, true );

            if ( get_option( 'nxt_halloween_dismissed' ) !== false ) {
                delete_option( 'nxt_halloween_dismissed' );
            }
        
            wp_send_json_success( array( 'message' => esc_html__('Notice dismissed successfully.', 'tpgb') ) );
        }

        /**
         * Rebranding Notice disable
         * @since 4.0.2
         */
        public function nxt_dismiss_plugin_rebranding_callback() {
            // Verify nonce for security
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tpgb-addons' ) ) {
                wp_send_json_error( array( 'message' => esc_html__('Invalid nonce. Unauthorized request.', 'tpgb') ) );
            }
        
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => esc_html__('Insufficient permissions.', 'tpgb') ) );
            }
        
            $option_key = 'nxt_rebranding_dismissed';
            update_option( $option_key, true );
        
            wp_send_json_success( array( 'message' => esc_html__('Notice dismissed successfully.', 'tpgb') ) );
        }
        
        /**
         * Loads Helper/Other files.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function loader_helper() {			
			$option_name='default_tpgb_load_opt';
			$value='1';
			if ( is_admin() && get_option( $option_name ) !== false ) {
			} else if( is_admin() ){
				$default_load=get_option( 'tpgb_normal_blocks_opts' );
				if ( $default_load !== false && $default_load!='') {
					$autoload = 'no';
					add_option( $option_name,$value, '', $autoload );
				}else{
					$tpgb_normal_blocks_opts=get_option( 'tpgb_normal_blocks_opts' );
                    if($tpgb_normal_blocks_opts === false){
                        $tpgb_normal_blocks_opts = [];
                    }
					$tpgb_normal_blocks_opts['enable_normal_blocks']= array("tp-accordion","tp-breadcrumbs","tp-blockquote","tp-button-core","tp-button","tp-countdown","tp-container","tp-creative-image","tp-data-table","tp-draw-svg","tp-empty-space","tp-flipbox","tp-google-map","tp-heading","tp-heading-title","tp-hovercard","tp-icon-box","tp-infobox","tp-image","tp-messagebox","tp-number-counter","tp-pricing-list","tp-pricing-table","tp-pro-paragraph","tp-progress-bar","tp-row","tp-stylist-list","tp-social-icons","tp-tabs-tours","tp-testimonials","tp-video","tp-login-register");
                    
                    $tpgb_normal_blocks_opts['tp_extra_option']= ['tp-advanced-border-radius','tp-display-rules','tp-equal-height','tp-event-tracking','tp-magic-scroll','tp-global-tooltip','tp-continuous-animation','tp-content-hover-effect','tp-mouse-parallax','tp-3d-tilt','tp-scoll-animation'];
					
					$autoload = 'no';
					add_option( 'tpgb_normal_blocks_opts',$tpgb_normal_blocks_opts, '', $autoload );
					add_option( $option_name,$value, '', $autoload );
                    $action_delay = 'tpgb_delay_css_js';
                    if ( false === get_option($action_delay) ){
                        add_option( $action_delay, 'true' );
                    }
                    $action_defer = 'tpgb_defer_css_js';
                    if ( false === get_option($action_defer) ){
                        add_option( $action_defer, 'true' );
                    }
				}
			}
			
			//Load Conditions Rules
			require_once TPGB_PATH . 'classes/extras/tpgb-conditions-rules.php';
			require TPGB_PATH . 'includes/rollback.php';
            require TPGB_PATH . 'includes/plus-settings-options.php';
            
            // Reusable Short code
            require_once TPGB_PATH . 'classes/extras/tpag-reusable-shortcode.php';

            // Plugin Deactive Popup
            require_once TPGB_PATH . 'classes/extras/tpag-deactive.php';

            require_once TPGB_PATH . 'classes/tp-block-helper.php';
        }
        
        /*
         * Files load plugin loaded.
         *
         * @since 1.1.3
         *
         * @return void
         */
        public function tp_plugin_loaded() {
            $this->load_textdomain();
            require_once TPGB_PATH . 'classes/tp-generate-block-css.php';

            require_once TPGB_PATH . 'classes/tp-get-blocks.php';
            require_once TPGB_PATH . 'classes/tp-core-init-blocks.php';
        }
        
        /**
         * Load Nexter Blocks Text Domain.
         * Text Domain : tpgb
         * @since  1.0.0
         * @return void
         */
        public function load_textdomain() {
            load_plugin_textdomain( 'tpgb', false, TPGB_BDNAME . '/lang' );
        }
        
        /**
         * If Check Gutenberg is installed
         *
         * @since 1.0.0
         *
         * @param string $plugin_url Plugin path.
         * @return boolean true | false
         * @access public
         */
        public function check_gutenberg_installed( $plugin_url ) {
            $get_plugins = get_plugins();
            return isset( $get_plugins[ $plugin_url ] );
        }

        /**
		 * Adds Links to the plugins page.
		 * @since 2.0.0
		 */
        public function tpgb_settings_pro_link( $links ){
            // Settings link.
            if ( current_user_can( 'manage_options' ) ) {
                $free_vs_pro = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url('https://nexterwp.com/free-vs-pro/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage'), __( 'FREE vs Pro', 'tpgb' ) );
                $links = (array) $links;
                $links[] = $free_vs_pro;
                $need_help = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url('https://store.posimyth.com/get-support-nexterwp/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage'), __( 'Need Help?', 'tpgb' ) );
                $links = (array) $links;
                $links[] = $need_help;
            }

            // Upgrade PRO link.
            if ( ! defined('TPGBP_VERSION') ) {
                $pro_link = sprintf( '<a href="%s" target="_blank" style="color: #cc0000;font-weight: 700;" rel="noopener noreferrer">%s</a>', esc_url('https://nexterwp.com/pricing/'), __( 'Upgrade PRO', 'tpgb' ) );
                $links = (array) $links;
                $links[] = $pro_link;
            }

            return $links;
        }

        /*
         * Adds Extra Links to the plugins row meta.
         * @since 2.0.0
         */
        public function tpbg_extra_links_plugin_row_meta( $plugin_meta = [], $plugin_file =''){

            if ( strpos( $plugin_file, TPGB_BASENAME ) !== false && current_user_can( 'manage_options' ) ) {
				$new_links = array(
						'official-site' => '<a href="'.esc_url('https://nexterwp.com/nexter-blocks/?utm_source=wpbackend&utm_medium=pluginpage&utm_campaign=links').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Visit Plugin site', 'tpgb' ).'</a>',
						'docs' => '<a href="'.esc_url('https://nexterwp.com/docs/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage').'" target="_blank" rel="noopener noreferrer" style="color:green;">'.esc_html__( 'Docs', 'tpgb' ).'</a>',
						'video-tutorials' => '<a href="'.esc_url('https://www.youtube.com/c/POSIMYTHInnovations/?sub_confirmation=1').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Video Tutorials', 'tpgb' ).'</a>',
						'join-community' => '<a href="'.esc_url('https://www.facebook.com/groups/nexterwpcommunity/').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Join Community', 'tpgb' ).'</a>',
						'whats-new' => '<a href="'.esc_url('https://roadmap.nexterwp.com/updates').'" target="_blank" rel="noopener noreferrer" style="color: orange;">'.esc_html__( 'What\'s New?', 'tpgb' ).'</a>',
						'req-feature' => '<a href="'.esc_url('https://roadmap.nexterwp.com/boards/feature-requests/').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Request Feature', 'tpgb' ).'</a>',
						'rate-plugin-star' => '<a href="'.esc_url('https://wordpress.org/support/plugin/the-plus-addons-for-block-editor/reviews/?filter=5').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Rate 5 Stars', 'tpgb' ).'</a>'
						);
				 
				$plugin_meta = array_merge( $plugin_meta, $new_links );
			}
			 
			return $plugin_meta;
        }
    }
    
    TP_Gutenberg_Loader::get_instance();

    function tpgb_load_data() {
        return TP_Gutenberg_Loader::get_instance();
    }
}