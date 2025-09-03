<?php
/**
 * Nexter Blocks Loader.
 * @since 1.0.0
 * @package Tpgb_Gutenberg_Loader
 */
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( !class_exists( 'Tpgb_Gutenberg_Loader' ) ) {
    
    /**
     * Class Tpgb_Gutenberg_Loader.
     */
    final class Tpgb_Gutenberg_Loader {
        
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
            
            // Show admin notice after 2 days
            if ( ! defined('TPGBP_VERSION') ) {
                add_action( 'admin_notices', array( $this , 'nexter_block_show_pro_notice' )  );
            }

            add_action( 'plugins_loaded', array( $this, 'tp_plugin_loaded' ) );

            $whitedata = get_option('tpgb_white_label');
            if( is_admin() && ( empty($whitedata) || ( !empty($whitedata['nxt_help_link']) && $whitedata['nxt_help_link'] !== 'on') ) ) {
                add_filter( 'plugin_action_links_' . TPGB_BASENAME, array( $this, 'tpgb_settings_pro_link' ) );
            }
            if( is_admin() ){
                add_filter( 'plugin_row_meta', array( $this, 'tpbg_extra_links_plugin_row_meta' ), 10, 2 );
            }
            add_action( 'wp_ajax_nxt_dismiss_plugin_rebranding', array( $this,'nxt_dismiss_plugin_rebranding_callback' ), 10, 1 );

            add_action( 'wp_ajax_nexter_dismiss_notice', array( $this, 'nexter_dismiss_notice' ) );
        }
        
        /**
         * Dismiss Notice Ajax
         * @since 4.5.5
         */
        public function nexter_dismiss_notice() {
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tpgb-addons' ) ) {
                wp_send_json_error( array( 'message' => esc_html__('Invalid nonce. Unauthorized request.', 'tpgbp') ) );
            }

            if ( ! empty($_POST['notice_id']) ) {
                $notice_id = sanitize_text_field($_POST['notice_id']);
                update_option($notice_id . '_dismissed', true);
                wp_send_json_success(['dismissed' => $notice_id]);
            } else {
                wp_send_json_error('Invalid Notice ID');
            }
        }
        
        /**
         * Pro Block Notice 
         * @since 4.5.6
         */
        public function nexter_block_show_pro_notice(){
            
            if ( get_option( 'nexter_block_show_pro_dismissed' ) ) {
                return;
            }
           
            // Only show for admins
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }
           
            $nxtData = get_option( 'nexter-installed-data' );
            
            if ( ! is_array( $nxtData ) || empty( $nxtData['install-date'] ) ) {
                return;
            }
        
            // Convert from d-m-Y to timestamp
            $inTime = strtotime( $nxtData['install-date'] );
           
            if ( ! $inTime ) {
                return;
            }
            
            $dayCount = floor( ( current_time( 'timestamp' ) - $inTime ) / DAY_IN_SECONDS );
            // Show after 2 days
            if ( $dayCount >= 2 ) {

                echo '<div class="notice notice-info is-dismissible nxt-notice-wrap" data-notice-id="nexter_block_show_pro">';
                    echo '<div class="nexter-license-activate">';
                        echo '<div class="nexter-license-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"><rect width="24" height="24" fill="#1717CC" rx="5"/><path fill="#fff" d="M12.605 17.374c.026 0 .038.013.039.038.102 0 .192.014.27.04.025 0 .05.012.076.037.128.077.23.167.307.27v.038c0 .026.013.051.039.077v.038a.63.63 0 0 1 .038.193l-.038 1.882h-2.652v-2.613h1.921Zm.308-13.414c.128 0 .23.038.308.115a.259.259 0 0 1 .115.23V15.26c.025.153-.052.295-.23.423a.872.872 0 0 1-.578.192h-1.844V3.96h2.23Z"/></svg></div>';
                        echo '<div class="nexter-license-content">';
                            echo '<h2>' . esc_html__( 'Upgrade to Nexter Blocks Pro', 'the-plus-addons-for-block-editor' ) . '</h2>';
                            echo '<p>' . esc_html__( 'Nexter Blocks free features are just the tip of the iceberg. Unlock powerful tools like Login Form, Form Builder, Mega Menu Builder, Popup Builder, and many more.', 'the-plus-addons-for-block-editor' ) . '</p>';
                            echo '<a href="' . esc_url('https://nexterwp.com/pricing/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage') . '" target="_blank" rel="noopener noreferrer" class="nxt-nobtn-primary">' . esc_html__( 'Upgrade Now', 'the-plus-addons-for-block-editor' ) . '</a>';
                            echo '<a href="' . esc_url('https://nexterwp.com/free-vs/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage') . '" target="_blank" rel="noopener noreferrer" class="nxt-nobtn-secondary">' . esc_html__( 'Compare Free vs Pro', 'the-plus-addons-for-block-editor' ) . '</a>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            }
        }

        /**
         * Rebranding Notice disable
         * @since 4.0.2
         */
        public function nxt_dismiss_plugin_rebranding_callback() {
            // Verify nonce for security
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tpgb-addons' ) ) {
                wp_send_json_error( array( 'message' => esc_html__('Invalid nonce. Unauthorized request.', 'the-plus-addons-for-block-editor') ) );
            }
        
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => esc_html__('Insufficient permissions.', 'the-plus-addons-for-block-editor') ) );
            }
        
            $option_key = 'nxt_rebranding_dismissed';
            update_option( $option_key, true );
        
            wp_send_json_success( array( 'message' => esc_html__('Notice dismissed successfully.', 'the-plus-addons-for-block-editor') ) );
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
                    
                    $tpgb_normal_blocks_opts['tp_extra_option']= ['tp-global-block-style','tp-advanced-border-radius','tp-display-rules','tp-equal-height','tp-event-tracking','tp-magic-scroll','tp-global-tooltip','tp-continuous-animation','tp-content-hover-effect','tp-mouse-parallax','tp-3d-tilt','tp-scoll-animation'];
					
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

                    // Add option For Init Nexter Block Version
                    $nxtData = [
                        "install-version" => TPGB_VERSION , 
                        "install-date" => date('d-m-Y') 
                    ];
                    add_option( 'nexter-installed-data', $nxtData );

				}
			}
			
			//Load Conditions Rules
			require_once TPGB_PATH . 'classes/extras/tpgb-conditions-rules.php';
			require TPGB_PATH . 'includes/rollback.php';

            require TPGB_PATH . 'classes/extras/nexter-block-whats-new.php';
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
            
            if(defined('AGNI_PLUGIN_URL') || class_exists( 'AgniBuilder' )){
                require_once TPGB_PATH . 'classes/extras/compatibility/class-tpag-cartify.php';
            }

            // wdkit widget api
            require_once TPGB_PATH . 'classes/extras/tpgb-wdk-widgets-api.php';
        }
        
        /**
         * Load Nexter Blocks Text Domain.
         * Text Domain : the-plus-addons-for-block-editor
         * @since  1.0.0
         * @return void
         */
        public function load_textdomain() {
            load_plugin_textdomain( 'the-plus-addons-for-block-editor', false, TPGB_BDNAME . '/languages/' );
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
            $nxtlink = [];
            if ( current_user_can( 'manage_options' ) ) {
                $nxtlinks[] = sprintf( '<a href="%s" rel="noopener noreferrer">%s</a>', admin_url( 'admin.php?page=nexter_welcome_page'), __( 'Settings', 'the-plus-addons-for-block-editor' ) );
                $nxtlinks[] = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url('https://nexterwp.com/free-vs-pro/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage'), __( 'Free vs Pro', 'the-plus-addons-for-block-editor' ) );
                $nxtlinks[] = sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', esc_url('https://store.posimyth.com/get-support-nexterwp/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage'), __( 'Need Help?', 'the-plus-addons-for-block-editor' ) );
            }

            // Upgrade PRO link.
            if ( ! defined('TPGBP_VERSION') ) {
                $nxtlinks[] = sprintf( '<a href="%s" target="_blank" style="color: #cc0000;font-weight: 700;" rel="noopener noreferrer">%s</a>', esc_url('https://nexterwp.com/pricing/'), __( 'Upgrade PRO', 'the-plus-addons-for-block-editor' ) );
            }

            return array_merge( $nxtlinks, $links );
        }

        /*
         * Adds Extra Links to the plugins row meta.
         * @since 2.0.0
         */
        public function tpbg_extra_links_plugin_row_meta( $plugin_meta = [], $plugin_file =''){
            
            $whitedata = get_option('tpgb_white_label');
            if ( strpos( $plugin_file, TPGB_BASENAME ) !== false && current_user_can( 'manage_options' ) && ( empty($whitedata) || ( !empty($whitedata['nxt_help_link']) && $whitedata['nxt_help_link'] !== 'on') ) ) {
				$new_links = array(
						'official-site' => '<a href="'.esc_url('https://nexterwp.com/nexter-blocks/?utm_source=wpbackend&utm_medium=pluginpage&utm_campaign=links').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Visit Plugin site', 'the-plus-addons-for-block-editor' ).'</a>',
						'docs' => '<a href="'.esc_url('https://nexterwp.com/docs/?utm_source=wpbackend&utm_medium=admin&utm_campaign=pluginpage').'" target="_blank" rel="noopener noreferrer" style="color:green;">'.esc_html__( 'Docs', 'the-plus-addons-for-block-editor' ).'</a>',
						'video-tutorials' => '<a href="'.esc_url('https://www.youtube.com/c/POSIMYTHInnovations/?sub_confirmation=1').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Video Tutorials', 'the-plus-addons-for-block-editor' ).'</a>',
						'join-community' => '<a href="'.esc_url('https://www.facebook.com/groups/nexterwpcommunity/').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Join Community', 'the-plus-addons-for-block-editor' ).'</a>',
						'whats-new' => '<a href="'.esc_url('https://roadmap.nexterwp.com/updates?filter=Nexter+Blocks+-+FREE').'" target="_blank" rel="noopener noreferrer" style="color: orange;">'.esc_html__( 'What\'s New?', 'the-plus-addons-for-block-editor' ).'</a>',
						'req-feature' => '<a href="'.esc_url('https://roadmap.nexterwp.com/boards/feature-requests/').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Request Feature', 'the-plus-addons-for-block-editor' ).'</a>',
						'rate-plugin-star' => '<a href="'.esc_url('https://wordpress.org/support/plugin/the-plus-addons-for-block-editor/reviews/?filter=5').'" target="_blank" rel="noopener noreferrer">'.esc_html__( 'Rate 5 Stars', 'the-plus-addons-for-block-editor' ).'</a>'
						);
				 
				$plugin_meta = array_merge( $plugin_meta, $new_links );
			}
			
            if( !empty($whitedata['nxt_help_link']) ){
                foreach ( $plugin_meta as $key => $meta ) {
					if ( stripos( $meta, 'View details' ) !== false ) {
						unset( $plugin_meta[ $key ] );
					}
				}
            }

			return $plugin_meta;
        }
    }
    
    Tpgb_Gutenberg_Loader::get_instance();

    function tpgb_load_data() {
        return Tpgb_Gutenberg_Loader::get_instance();
    }
}