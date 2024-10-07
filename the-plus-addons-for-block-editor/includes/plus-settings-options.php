<?php 
/**
 * TPGB Gutenberg Settings Options
 * @since 1.0.0
 *
 */
if (!defined('ABSPATH')) {
    exit;
}

class Tpgb_Gutenberg_Settings_Options {
	
	/**
     * Setting Name/Title
     * @var string
     */
    protected $setting_name = '';
	
	/**
     * Options Page hook
     * @var string
     */
    protected $block_lists = [];
	protected $block_extra = [];
	
	/**
     * Constructor
     * @since 1.0.0
     */
    public function __construct() {
		if( is_admin() ){
			if(defined('TPGBP_VERSION')){
				$options = get_option( 'tpgb_white_label' );
				$this->setting_name = (!empty($options['tpgb_plugin_name'])) ? $options['tpgb_plugin_name'] : __('Nexter Blocks','tpgb');
			}else{
				$this->setting_name = esc_html__('Nexter Blocks', 'tpgb');
			}
		
			$this->block_listout();
		
			add_action( 'wp_ajax_tpgb_blocks_opts_save', array( $this,'tpgb_blocks_opts_save_action') );
			add_action( 'wp_ajax_tpgb_connection_data_save', array( $this,'tpgb_connection_data_save_action') );
			add_action( 'wp_ajax_tpgb_custom_css_js_save', array( $this,'tpgb_custom_css_js_save_action') );
			
			add_action( 'wp_ajax_tpgb_is_block_used_not', array( $this, 'tpgb_is_block_used_not_fun' ) );
			add_action( 'wp_ajax_tpgb_unused_disable_block', array( $this, 'tpgb_disable_unsed_block_fun' ) );
			add_action( 'wp_ajax_tpgb_performance_opt_cache', array( $this,'tpgb_performance_opt_cache_save_action') );
			
			add_action( 'admin_enqueue_scripts', array( $this, 'tpgb_dash_admin_scripts' ), 10, 1 );

			// Install WdesignKit
			add_action( 'wp_ajax_nxt_install_wdesign', array( $this,'nxt_install_wdesign') );

			// Remove All Notice From Dashboard Screnn
			add_action( 'admin_head', array( $this,'nxt_remove_admin_notices_page') );

				
			// Install Nexter Theme
			add_action( 'wp_ajax_nxt_install_theme', array( $this,'nxt_install_theme') );
		}
		
    }

	/**
     * Initiate our hooks
     * @since 1.0.0
     */
	public function hooks() {
		if( is_admin() ){
			add_action('admin_menu', array( $this, 'add_options_page' ));
		}
    }
	
	/**
     * Add menu options page
     * @since 1.0.0
     */
	public function add_options_page(){
		add_menu_page( $this->setting_name, $this->setting_name, 'manage_options', 'nexter_welcome_page', array( $this, 'admin_page_display' ),'dashicons-tpgb-plus-settings' , 67.2 );

		add_submenu_page( 'nexter_welcome_page', esc_html__( 'Patterns', 'tpgb' ), esc_html__( 'Patterns', 'tpgb' ), 'manage_options', esc_url( admin_url('edit.php?post_type=wp_block') ));

		if( !defined('TPGBP_VERSION') ){
			add_submenu_page( 'nexter_welcome_page', esc_html__( 'Upgrade Now', 'tpgb' ), esc_html__( 'Upgrade Now', 'tpgb' ), 'manage_options', esc_url('https://nexterwp.com/pricing/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings'));
		}

		add_action('admin_footer', array($this, 'nxt_link_in_new_tab'));

		// Hook to modify the submenu head title
		add_action('admin_menu', array($this, 'nxt_submenu_head_title') , 101);
	}

	/**
     * Parent Page Rename in Sub menu
     * @since 2.0.0
     */
	public function nxt_submenu_head_title() {
		global $submenu;
		if ( isset($submenu['nexter_welcome_page'] )) {
			$submenu['nexter_welcome_page'][0][0] = esc_html__( 'Dashboard', 'tpgb' );
		}
	}

	/**
     * Open Link in New Tab Wordpress Menu
     * @since 2.0.0
     */
	public function nxt_link_in_new_tab(){
		?>
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				var upgradeLink = document.querySelector('a[href*="https://nexterwp.com/pricing/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings"]');
				if (upgradeLink) {
					upgradeLink.setAttribute('target', '_blank');
					upgradeLink.setAttribute('rel', 'noopener noreferrer');
				}
			});
		</script>
		<?php
	}

	
	/**
	 * Enqueue DashBoard Scripts admin area.
	 *
	 * @since   1.0.0
	 *
	 * @param string $page use for check page type.
	 */
	public function tpgb_dash_admin_scripts( $page ) {
		
		$slug = array( 'toplevel_page_nexter_welcome_page' );
		if ( ! in_array( $page, $slug, true ) ) {
			return;
		}

		$this->tpgb_dash_enqueue_style();
		$this->tpgb_dash_enqueue_scripts();
	}

	/**
	 * Enqueue Styles admin area.
	 *
	 * @since   1.0.0
	 *
	 * @param string $page use for check page type.
	 */
	public function tpgb_dash_enqueue_style() {
		wp_enqueue_style( 'tpgb-dash-style', TPGB_URL . 'dashboard/build/index.css', array(), TPGB_VERSION, 'all' );
	}

	/**
	 * Enqueue script admin area.
	 *
	 * @since   1.0.0
	 */
	public function tpgb_dash_enqueue_scripts() {
		$user = wp_get_current_user();
		$default_load=get_option( 'tpgb_normal_blocks_opts' );
		$rollback_url = wp_nonce_url(admin_url('admin-post.php?action=tpgb_rollback&version=TPGB_VERSION'), 'tpgb_rollback');
		$dashData = [];
		$wdadded = false;
		$nxtextension = false;
		$uichemy = false;
        $nxtheme = false;

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$pluginslist = get_plugins();
		if ( isset( $pluginslist[ 'wdesignkit/wdesignkit.php' ] ) && !empty( $pluginslist[ 'wdesignkit/wdesignkit.php' ] ) ) {
			if( is_plugin_active('wdesignkit/wdesignkit.php') ){
				$wdadded = true;
			}
		}

		if ( isset( $pluginslist[ 'nexter-extension/nexter-extension.php' ] ) && !empty( $pluginslist[ 'nexter-extension/nexter-extension.php' ] ) ) {
			if( is_plugin_active('nexter-extension/nexter-extension.php') ){
				$nxtextension = true;
			}
		}

		if ( isset( $pluginslist[ 'uichemy/uichemy.php' ] ) && !empty( $pluginslist[ 'uichemy/uichemy.php' ] ) ) {
			if( is_plugin_active('uichemy/uichemy.php') ){
				$uichemy = true;
			}
		}

		$active_theme = wp_get_theme();
		$theme_name = $active_theme->get('Name');
		if( isset($theme_name) && !empty($theme_name) && $theme_name == 'Nexter' ){
				$nxtheme = true;
		}else if ( file_exists( WP_CONTENT_DIR.'/themes/'.'nexter') && $theme_name != 'Nexter' ) {
				$nxtheme = 'available';
		}

		if ( $user ){
			$dashData = [
				'userData' => [
					'userName' => esc_html($user->display_name),
					'profileLink' => esc_url( get_avatar_url( $user->ID ) )
				],
				'blockList' => array_merge($this->block_lists,$this->block_extra),
				'avtiveBlock' => count( $default_load['enable_normal_blocks'] ),
				'enableBlock' => array_merge(
					$default_load['enable_normal_blocks'], 
					isset($default_load['tp_extra_option']) && is_array($default_load['tp_extra_option']) ? $default_load['tp_extra_option'] : []
				),
				'extOption' => get_option('tpgb_connection_data'),
				'cacheData' => [ get_option('tpgb_performance_cache') , get_option('tpgb_delay_css_js') , get_option('tpgb_defer_css_js') ],
				'customCode' => get_option('tpgb_custom_css_js'),
				'rollbacVer' => Tpgb_Rollback::get_rollback_versions(),
				'rollbackUrl' => $rollback_url,
				'wdadded' => $wdadded,
				'wdTemplates' => [], 
				'nexterext' => $nxtextension,
				'wpVersion' => get_bloginfo('version'),
				'pluginVer' => TPGB_VERSION,
				'uichemy' => $uichemy,
				'nextheme' => $nxtheme,
				'whiteLabel' => get_option('tpgb_white_label'),
				'keyActmsg' => class_exists('Tpgb_Pro_Library') ? Tpgb_Pro_Library::tpgb_pro_activate_msg() : '',
				'nxtactivateKey' => get_option('tpgb_activate'),
				'activePlan' => ( class_exists('Tpgb_Pro_Library') && method_exists('Tpgb_Pro_Library', 'tpgb_get_activate_plan') ) ? Tpgb_Pro_Library::tpgb_get_activate_plan() : '',
			];
		}

		wp_enqueue_script( 'tpgb-dashscript', TPGB_URL . 'dashboard/build/index.js', array( 'react', 'react-dom', 'wp-dom-ready', 'wp-element' ), TPGB_VERSION, true );
		wp_localize_script(
			'tpgb-dashscript',
			'tpgb_ajax_object',
			array(
				'adminUrl' => admin_url(),
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'tpgb-dash-ajax-nonce' ),
				'tpgb_url' => TPGB_URL.'dashboard/',
				'pro' => defined('TPGBP_VERSION'),
				'dashData' => $dashData
			)
		);
	}
	
	/*
	 * Install Wdesignkit Plugin
	 * @since 1.4.0
	 */
	public function nxt_install_wdesign(){
		check_ajax_referer('tpgb-dash-ajax-nonce', 'security');

		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'content' => __( 'Insufficient permissions.', 'uichemy' ) ) );
		}

		$plu_slug = ( isset( $_POST['slug'] ) && !empty( $_POST['slug'] ) ) ? $_POST['slug'] : '';

		$installed_plugins = get_plugins();

		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
		include_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		$result   = array();
		$response = wp_remote_post(
			'http://api.wordpress.org/plugins/info/1.0/',
			array(
				'body' => array(
					'action'  => 'plugin_information',
					'request' => serialize(
						(object) array(
							'slug'   => $plu_slug,
							'fields' => array(
								'version' => false,
							),
						)
					),
				),
			)
		);

		$plugin_info = unserialize( wp_remote_retrieve_body( $response ) );

		if ( ! $plugin_info ) {
			wp_send_json_error( array( 'content' => __( 'Failed to retrieve plugin information.', 'tpgb' ) ) );
		}

		$skin     = new \Automatic_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader( $skin );

		$plugin_basename = ''.esc_attr($plu_slug).'/'.esc_attr($plu_slug).'.php';
		
		if ( ! isset( $installed_plugins[ $plugin_basename ] ) && empty( $installed_plugins[ $plugin_basename ] ) ) {
			$installed = $upgrader->install( $plugin_info->download_link );

			$activation_result = activate_plugin( $plugin_basename );

			$success = null === $activation_result;
			wp_send_json(['Sucees' => true]);

		} elseif ( isset( $installed_plugins[ $plugin_basename ] ) ) {
			$activation_result = activate_plugin( $plugin_basename );

			$success = null === $activation_result;
			wp_send_json(['Sucees' => true]);

		}
	}

	/*
	 * Install Nexter Theme
	 * @since 2.0.0
	 */
	public function nxt_install_theme(){
		check_ajax_referer('tpgb-dash-ajax-nonce', 'security');

		if ( !current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not allowed to do this action', 'tpgb' ) );
		}

		$theme_slug = 'nexter';
		$theme_api_url = 'https://api.wordpress.org/themes/info/1.0/';

		// Parameters for the request
		$args = array(
			'body' => array(
				'action' => 'theme_information',
				'request' => serialize((object) array(
					'slug' => 'nexter',
					'fields' => [
						'description' => false,
						'sections' => false,
						'rating' => true,
						'ratings' => false,
						'downloaded' => true,
						'download_link' => true,
						'last_updated' => true,
						'homepage' => true,
                		'tags' => true,
						'template' => true,
						'active_installs' => false,
						'parent' => false,
						'versions' => false,
						'screenshot_url' => true,
						'active_installs' => false
					],
				))),
		);

		// Make the request
		$response = wp_remote_post($theme_api_url, $args);

		// Check for errors
		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();

			wp_send_json(['Sucees' => false]);
		} else {
			$theme_info = unserialize( $response['body'] );
			$theme_name = $theme_info->name;
			$theme_zip_url = $theme_info->download_link;
			global $wp_filesystem;
			// Install the theme
			$theme = wp_remote_get( $theme_zip_url );
			if ( ! function_exists( 'WP_Filesystem' ) ) {
				require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			}

			WP_Filesystem();

			$active_theme = wp_get_theme();
			$theme_name = $active_theme->get('Name');
			
			$wp_filesystem->put_contents( WP_CONTENT_DIR.'/themes/'.$theme_slug . '.zip', $theme['body'] );
			$zip = new ZipArchive();
			if ( $zip->open( WP_CONTENT_DIR . '/themes/' . $theme_slug . '.zip' ) === true ) {
				$zip->extractTo( WP_CONTENT_DIR . '/themes/' );
				$zip->close();
			}
			$wp_filesystem->delete( WP_CONTENT_DIR . '/themes/' . $theme_slug . '.zip' );
			

			wp_send_json(['Sucees' => true]);
		}
		exit;
	}

	/*
	 * Remove All Notice From Dash Board
	 * @since 2.0.0
	 */
	public function nxt_remove_admin_notices_page(){
		$current_screen = get_current_screen();
    
		if ($current_screen->base == 'toplevel_page_nexter_welcome_page') {
			$this->nxt_remove_all_actions('admin_notices');
			$this->nxt_remove_all_actions('all_admin_notices');
		}
	}

	/*
	 * Helper function to remove all actions for a specific hook
	 * @since 2.0.0
	 */
	public function nxt_remove_all_actions($hook_name){
		global $wp_filter;

		if (isset($wp_filter[$hook_name])) {
			unset($wp_filter[$hook_name]);
		}
	}

	/*
	 * Save Performance Cache Option 
	 * @since 1.4.0
	 */
	public function tpgb_performance_opt_cache_save_action(){
		check_ajax_referer('tpgb-dash-ajax-nonce', 'security');
		
		if((isset($_POST['perf_caching']) && !empty($_POST['perf_caching'])) || isset($_POST['delay_js']) || isset($_POST['defer_js'])){
			$action_page = 'tpgb_performance_cache';
			$perf_caching = wp_unslash( sanitize_text_field( $_POST['perf_caching'] ) );
			if ( FALSE === get_option($action_page) ){
				add_option( $action_page, $perf_caching );
			}else{
				update_option( $action_page, $perf_caching );
			}

			$action_page = 'tpgb_delay_css_js';
			$delay_js = wp_unslash( sanitize_text_field( $_POST['delay_js'] ) );
			if ( FALSE === get_option($action_page) ){
				add_option( $action_page, $delay_js );
			}else{
				update_option( $action_page, $delay_js );
			}
			$action_page = 'tpgb_defer_css_js';
			$defer_js = wp_unslash( sanitize_text_field( $_POST['defer_js'] ) );
			if ( FALSE === get_option($action_page) ){
				add_option( $action_page, $defer_js );
			}else{
				update_option( $action_page, $defer_js );
			}
			wp_send_json_success();
		}
		wp_send_json_error();
	}

	public function tpgb_blocks_opts_save_action() {
		$action_page = 'tpgb_normal_blocks_opts';
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_redirect( esc_url( admin_url('admin.php?page='.$action_page) ) );
		}
		if(isset($_POST["submit-key"]) && !empty($_POST["submit-key"]) && $_POST["submit-key"]=='Save'){
			
			if ( ! isset( $_POST['nonce_tpgb_normal_blocks_opts'] ) || ! wp_verify_nonce( sanitize_key($_POST['nonce_tpgb_normal_blocks_opts']), 'tpgb-dash-ajax-nonce' ) ) { //nonce_tpgb_normal_blocks_action
			   wp_redirect( esc_url(admin_url('admin.php?page='.$action_page)) );
			} else {
			Tpgb_Library()->remove_backend_dir_files();
				if ( FALSE === get_option($action_page) ){
					$default_value = array('enable_normal_blocks' => '' , 'tp_extra_option' => '');
					add_option($action_page,$default_value);
					wp_redirect( esc_url(admin_url('admin.php?page=tpgb_normal_blocks_opts')) );
				}
				else{
					$update_value = array('enable_normal_blocks' => '');
					if(isset($_POST['enable_normal_blocks']) && !empty($_POST['enable_normal_blocks'])){
						$blockList = json_decode(stripslashes( $_POST['enable_normal_blocks'] ),true);
						
						if(is_array($blockList)){
							$update_value = array('enable_normal_blocks' => map_deep( wp_unslash( $blockList ), 'sanitize_text_field' ));
						}else{
							$update_value = array('enable_normal_blocks' => sanitize_text_field($blockList) );
						}
					}
					
					$update_extra_val = array('tp_extra_option' => '');
					if(isset($_POST['tp_extra_option']) && !empty($_POST['tp_extra_option'])){
						$extraList = json_decode(stripslashes( $_POST['tp_extra_option'] ),true);
						if(is_array($extraList)){
							$update_extra_val = array('tp_extra_option' => map_deep( wp_unslash( $extraList ), 'sanitize_text_field' ));
						}else{
							$update_extra_val = array('tp_extra_option' => sanitize_text_field($extraList) );
						}
					}
					
					$block_value = array_merge( $update_value , $update_extra_val );
					$updated = update_option($action_page, $block_value);

					if ($updated) {
						wp_send_json(['Success' => true]);
					} else {
						wp_send_json(['Success' => false]);
					}
				}
			}

		}else{
			wp_send_json(['Sucees' => false]);
		}
	}

	public function tpgb_connection_data_save_action(){
		$action_page = 'tpgb_connection_data';
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_redirect( esc_url( admin_url('admin.php?page='.$action_page) ) );
		}
		if(isset($_POST["submit-key"]) && !empty($_POST["submit-key"]) && $_POST["submit-key"]=='Save'){
			if ( ! isset( $_POST['nonce_tpgb_connection_data'] ) || ! wp_verify_nonce( sanitize_key($_POST['nonce_tpgb_connection_data']), 'tpgb-dash-ajax-nonce' ) ) {
				wp_redirect( esc_url(admin_url('admin.php?page='.$action_page)) );
			} else {
				$getArr = $_POST;
				unset($getArr['nonce_tpgb_connection_data']);
				unset($getArr['_wp_http_referer']);
				unset($getArr['action']);
				unset($getArr['submit-key']);
				
				$getArr = json_decode(stripslashes( $getArr['tpgb_connection_data'] ),true);
				if ( FALSE === get_option($action_page) ){
					$added = add_option($action_page,$getArr);
					if ($added) {
						wp_send_json(['Success' => true]);
					} else {
						wp_send_json(['Success' => false]);
					}
				}else{
					$updated = update_option( $action_page, $getArr );
					if ($updated) {
						wp_send_json(['Success' => true]);
					} else {
						wp_send_json(['Success' => false]);
					}
					wp_redirect( esc_url( admin_url('admin.php?page='.$action_page) ) );
				}
			}
		}else{
			wp_redirect( esc_url( admin_url('admin.php?page='.$action_page) ) );
		}

	}

	public function tpgb_custom_css_js_save_action(){
		$action_page = 'tpgb_custom_css_js';
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(['Success' => false]);
		}
		if(isset($_POST["submit-key"]) && !empty($_POST["submit-key"]) && $_POST["submit-key"]=='Save'){
			if ( ! isset( $_POST['nonce_tpgb_custom_css_js'] ) || ! wp_verify_nonce( sanitize_key($_POST['nonce_tpgb_custom_css_js']), 'tpgb-dash-ajax-nonce' ) ) {
				wp_send_json(['Success' => false]);
			} else {
				$getArr = $_POST;
				unset($getArr['nonce_tpgb_custom_css_js']);
				unset($getArr['_wp_http_referer']);
				unset($getArr['action']);
				unset($getArr['submit-key']);

				$getArr['tpgb_custom_js_editor'] = isset($getArr['tpgb_custom_js_editor']) ? stripslashes($getArr['tpgb_custom_js_editor']) : '';
				$getArr['tpgb_custom_css_editor'] = isset($getArr['tpgb_custom_css_editor']) ? stripslashes($getArr['tpgb_custom_css_editor']) : '';
				if ( FALSE === get_option($action_page) ){
					add_option($action_page,$getArr);
					wp_send_json(['Success' => true]);
				}else{
					update_option( $action_page, $getArr );
					wp_send_json(['Success' => true]);
				}
			}
		}else{
			wp_send_json(['Success' => false]);
		}
	}

	public function block_listout(){
		$this->block_lists = [
			'tp-accordion' => [
				'label' => esc_html__('Accordion','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-accordion-toggle/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-accordion-toggle/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Tabbed', 'tpgb'),
				'keyword' => ['accordion', 'tabs', 'toggle', 'faq', 'collapse', 'show hide content', 'Tiles'],
			],
			'tp-advanced-buttons' => [
				'label' => esc_html__('Pro Buttons', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-pro-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-pro-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Button', 'CTA', 'link', 'creative button', 'Call to action', 'Marketing Button'],
			],
			'tp-advanced-chart' => [
				'label' => esc_html__('Advanced Chart', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-advanced-charts-and-graph/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'tpgb'),
				'keyword' => ['chart', 'diagram'],
			],
			'tp-adv-typo' => [
				'label' => esc_html__('Advanced Typography', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-advanced-typography/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['adv','text','typo'],
			],
			'tp-animated-service-boxes' => [
				'label' => esc_html__('Animated Service Boxes', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-animated-service-boxes/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
			],
			'tp-audio-player' => [
				'label' => esc_html__('Audio Player','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-audio-music-player/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['audio player', 'music player'],
			],
			'tp-before-after' => [
				'label' => esc_html__('Before After', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-before-after-image-comparison-slider/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
			],
			'tp-blockquote' => [
				'label' => esc_html__('Blockquote','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-blockquote-block/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['blockquote', 'Block Quotation', 'Citation', 'Pull Quotes'],
			],
			'tp-breadcrumbs' => [
				'label' => esc_html__('Breadcrumbs','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-breadcrumb-bar/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['breadcrumbs bar', 'breadcrumb trail', 'navigation', 'site navigation', 'breadcrumb navigation']
			],
			'tp-button' => [
				'label' => esc_html__('Advanced Button','tpgb'),				
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-advanced-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Button', 'CTA', 'link', 'creative button', 'Call to action', 'Marketing Button']
			],
			'tp-button-core' => [
				'label' => esc_html__('Button','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['core button','Button', 'CTA', 'link', 'creative button', 'Call to action', 'Marketing Button']
			],
			'tp-anything-carousel' => [
				'label' => esc_html__('Carousel Anything','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-advanced-carousel-sliders/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-advanced-carousel-sliders/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['carousel anything', 'slider', 'slideshow'],
			],
			'tp-carousel-remote' => [
				'label' => esc_html__('Carousel Remote','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-remote-sync/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-remote-sync/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['carousel remote', 'slider controller','next prev','dots'],
			],
			'tp-circle-menu' => [
				'label' => esc_html__('Circle Menu', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-circle-menu/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['circle menu', 'compact menu', 'mobile menu']
			],
			'tp-code-highlighter' => [
				'label' => esc_html__('Code Highlighter', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-source-code-syntax-highlighter/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['prism', 'Source code beautifier', 'code Highlighter',  'syntax Highlighter', 'Custom Code', 'CSS', 'JS', 'PHP', 'HTML', 'React']
			],
			'tp-countdown' => [
				'label' => esc_html__('Countdown','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-countdown-timer/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Advanced', 'tpgb'),
				'keyword' => ['Countdown', 'countdown timer', 'timer', 'Scarcity Countdown', 'Urgency Countdown', 'Event countdown', 'Sale Countdown', 'chronometer', 'stopwatch']
			],
			'tp-container' => [
				'label' => esc_html__('Container','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-container/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['container','flex-wrap','flex-based','full-width']
			],
			'tp-coupon-code' => [
				'label' => esc_html__('Coupon Code', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-coupon-code/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Coupon Code', 'Promo Code', 'Offers' , 'Discounts', 'Sales', 'Copy Coupon Code']
			],
			'tp-creative-image' => [
				'label' => esc_html__('Advanced Image','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-advanced-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '#video',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['Creative image', 'Image', 'Animated Image', 'ScrollReveal', 'scrolling image', 'decorative image', 'image effect', 'Photo', 'Visual']
			],
			'tp-cta-banner' => [
				'label' => esc_html__('CTA Banner','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-cta-banner/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'tpgb'),
				'keyword' => ['advertisement', 'banner', 'advertisement banner', 'ad manager', 'announcement', 'announcement banner']
			],
			'tp-data-table' => [
				'label' => esc_html__('Data Table','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-data-table/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-data-table/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['Data table', 'datatable', 'grid', 'csv table', 'table', 'tabular layout', 'Table Showcase']
			],
			'tp-dark-mode' => [
				'label' => esc_html__('Dark Mode','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-dark-mode-switcher/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['dark', 'light', 'darkmode', 'dual']
			],
			'tp-design-tool' => [
				'label' => esc_html__('Design Tool','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-design-grid-tool/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['design','tool']
			],
			'tp-draw-svg' => [
				'label' => esc_html__('Draw SVG','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-draw-animated-svg-icon/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Draw SVG', 'Draw Icon', 'illustration', 'animated svg', 'animated icons', 'Lottie animations', 'Lottie files', 'effects', 'image effect']
			],
			'tp-dynamic-device' => [
				'label' => esc_html__('Dynamic Device','tpgb'),
				'demoUrl' => '',
				'docUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-device-mockups/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['dynamic device', 'website mockups', 'portfolio', 'desktop view', 'tablet view', 'mobile view']
			],
			'tp-empty-space' => [
				'label' => esc_html__('Spacer','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-spacer/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Spacer', 'Divider', 'Spacing','empty space']
			],
			'tp-external-form-styler' => [
				'label' => esc_html__('External Form Styler','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-contact-form-stylers/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-contact-form-stylers/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#',
				'tag' => 'free',
				'block_cate' => esc_html__('Advanced', 'tpgb'),
				'keyword' => ['form', 'contect form', 'everest', 'gravity', 'wpform','Contact Form 7', 'contact form', 'form', 'feedback', 'subscribe', 'newsletter', 'contact us', 'custom form', 'popup form', 'cf7']
			],
			'tp-expand' => [
				'label' => esc_html__('Expand','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-unfold-read-more-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-unfold-read-more-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['Expand', 'read more', 'show hide content', 'Expand tabs', 'show more', 'toggle', 'Excerpt']
			],
			'tp-flipbox' => [
				'label' => esc_html__('Flipbox','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-flipbox/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['flipbox', 'flip', 'flip image', 'flip card', 'action box', 'flipbox 3D', 'card'],
			],
			'tp-google-map' => [
				'label' => esc_html__('Google Map','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-google-maps/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-google-maps/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Map', 'Maps', 'Google Maps', 'g maps', 'location map', 'map iframe', 'embed']
			],
			'tp-heading-animation' => [
				'label' => esc_html__('Heading Animation','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-heading-animation/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['Heading Animation', 'Animated Heading', 'Animation Text', 'Animated Text', 'Text Animation']
			],
			'tp-heading' => [
				'label' => esc_html__('Heading','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-heading/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-heading/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Heading', 'Title', 'Text', 'Heading title', 'Headline']
			],
			'tp-heading-title' => [
				'label' => esc_html__('Advanced Heading','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-title-block/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['Heading', 'Title', 'Text', 'Heading title', 'Headline']
			],
			'tp-hotspot' => [
				'label' => esc_html__('Hotspot','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-hotspot-pinpoint-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => [ 'Image hotspot', 'maps', 'pin' ],
			],
			'tp-hovercard' => [
				'label' => esc_html__('Hover Card','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/hover-card-animations-wordpress/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['Hover Card', 'Card', 'Business Card'],
			],
			'tp-icon-box' => [
				'label' => esc_html__('Icon','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-icon/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-icon/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['iconbox', 'icon box', 'fontawesome']
			],
			'tp-image' => [
				'label' => esc_html__('Image','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['image', 'media']
			],
			'tp-infobox' => [
				'label' => esc_html__('Infobox','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-infobox/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Infobox', 'Information', 'Info box', 'card', 'info']
			],
			'tp-interactive-circle-info' => [
				'label' => esc_html__('Interactive Circle Info','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-interactive-circle-infographic/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Tabbed', 'tpgb'),
				'keyword' => ['interactive circle', 'interactive', 'circle', 'info']
			],
			'tp-login-register' => [
				'label' => esc_html__('Login Register','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-login-form/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-login-and-registration-form/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['login', 'register', 'Sign up','forgot password']
			],
			'tp-lottiefiles' => [
				'label' => esc_html__('LottieFiles Animation','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-lottiefiles-animations/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['animation', 'lottie', 'files']
			],
			'tp-mailchimp' => [
				'label' => esc_html__('Mailchimp','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-mailchimp-form/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'tpgb'),
				'keyword' => ['Mailchimp', 'Mailchimp addon', 'subscribe form']
			],
			'tp-media-listing' => [
				'label' => esc_html__('Media Listing','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-image-gallery/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Listing', 'tpgb'),
				'keyword' => ['Video Gallery', 'Image Gallery', 'Video Carousel', 'Image Carousel', 'Video Listing', 'Image Listing', 'Youtube', 'Vimeo','media gallery']
			],
			'tp-messagebox' => [
				'label' => esc_html__('Message box','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-message-box/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Message box', 'Notification box', 'alert box']
			],
			'tp-mobile-menu' => [
				'label' => esc_html__('Mobile Menu','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-mobile-menu/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['mobile menu', 'menu','toggle menu']
			],
			'tp-mouse-cursor' => [
				'label' => esc_html__('Mouse Cursor','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-custom-cursors/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['mouse', 'cursor', 'animated cursor', 'mouse cursor', 'pointer']
			],
			'tp-navigation-builder' => [
				'label' => esc_html__('Navigation Menu','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-navigation-menu/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['navigation menu', 'mega menu', 'header builder', 'sticky menu', 'navigation bar', 'header menu', 'menu', 'navigation builder','vertical menu', 'swiper menu']
			],
			'tp-number-counter' => [
				'label' => esc_html__('Number Counter','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-number-counter/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['number counter', 'counter', 'animated counter', 'Odometer']
			],
			'tp-popup-builder' => [
				'label' => esc_html__('Popup Builder','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-popup-builder/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['popup', 'pop up', 'alertbox', 'offcanvas', 'modal box', 'modal popup']
			],
			'tp-post-author' => [
				'label' => esc_html__('Post Author', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-author-box/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['post author', 'author','user info']
			],
			'tp-post-comment' => [
				'label' => esc_html__('Post Comments', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-comment-form/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['post comments', 'comments','comments area']
			],
			'tp-post-content' => [
				'label' => esc_html__('Post Content', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-content/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['content', 'post content', 'post excerpt', 'archive description']
			],
			'tp-post-image' => [
				'label' => esc_html__('Post Image', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-featured-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['post featured image', 'post image', 'featured image']
			],
			'tp-post-listing' => [
				'label' => esc_html__('Post Listing', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-post-listing/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Listing', 'tpgb'),
				'keyword' => ['blog listing', 'article listing','custom post listing','blog view','post listing','masonry','carousel','content view','blog item listing','grid', 'post listing', 'related posts', 'archive posts', 'post list', 'post grid', 'post masonry','post carousel', 'post slider']
			],
			'tp-post-meta' => [
				'label' => esc_html__('Post Meta Info', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-meta-info/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['post category', 'post tags', 'post meta info', 'meta info', 'post date', 'post comment', 'post author']
			],
			'tp-post-navigation' => [
				'label' => esc_html__('Post Navigation', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-navigation/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['previous next', 'post previous next', 'post navigation']
			],
			'tp-post-title' => [
				'label' => esc_html__('Post Title', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-title/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['post title', 'page title', 'archive title']
			],
			'tp-pricing-list' => [
				'label' => esc_html__('Pricing List','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-pricing-list/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['Pricing list', 'Item price', 'price card', 'Price Guide', 'price box']
			],
			'tp-pricing-table' => [
				'label' => esc_html__('Pricing Table','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-pricing-table/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Pricing table', 'pricing list', 'price table', 'plans table', 'pricing plans', 'dynamic pricing', 'price comparison', 'Plans & Pricing Table', 'Price Chart']
			],
			'tp-preloader' => [
				'label' => esc_html__('Pre Loader','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-preloader-animation-and-page-transitions/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-preloader-animation-and-page-transitions/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => [ 'pre loader', 'loader', 'loading' ],
			],
			'tp-pro-paragraph' => [
				'label' => esc_html__('Paragraph','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-pro-paragraph/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-pro-paragraph/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#video',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Paragraph', 'wysiwyg', 'editor', 'editor block', 'textarea', 'text area', 'text editor'],
			],
			'tp-process-steps' => [
				'label' => esc_html__('Process Steps','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-process-steps/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['Process steps', 'post timeline', 'step process', 'steps form', 'Steppers', 'timeline', 'Progress Tracker']
			],
			'tp-product-listing' => [
				'label' => esc_html__('Product Listing','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-product-listing/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Listing', 'tpgb'),
				'keyword' => ['Product', 'Woocommerce']
			],
			'tp-progress-bar' => [
				'label' => esc_html__('Progress Bar','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-progress-bar/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Progress bar', 'progressbar', 'status bar', 'progress indicator', 'scroll progress', 'process progress bar', 'Progress Tracker']
			],
			'tp-progress-tracker' => [
				'label' => esc_html__('Progress Tracker','tpgb'),
				'demoUrl' => 'https://theplusblocks.com/plus-blocks/reading-scroll-bar/',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => [ 'Progress bar', 'progressbar', 'status bar', 'progress indicator', 'scroll progress', 'process progress bar', 'Progress Tracker', 'Page scroll tracker','Reading progress indicator','Reading progress bar','Reading position tracker', 'Scroll depth indicator', 'Scroll tracking', 'Scroll Progress Visualizer' ]
			],
			'tp-row' => [
				'label' => esc_html__('Row','tpgb'),
				'demoUrl' => 'https://theplusblocks.com/row/',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Row', 'layout'],
			],
			'tp-site-logo' => [
				'label' => esc_html__('Site Logo','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-site-logo/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '#video',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'tpgb'),
				'keyword' => ['site logo', 'logo','dual logo'],
			],
			'tp-stylist-list' => [
				'label' => esc_html__('Stylish List','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-stylish-list/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '#video',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Stylish list', 'listing', 'item listing'],
			],
			'tp-scroll-navigation' => [
				'label' => esc_html__('Scroll Navigation','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-one-page-scroll-navigation/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '#video',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['Scroll navigation', 'slide show', 'slideshow', 'vertical slider'],
			],
			'tp-scroll-sequence' => [
				'label' => esc_html__('Scroll Sequence','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-image-scroll-sequence/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '#video',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['Cinematic Scroll Image Animation', 'Video Scroll Sequence', 'Image Scroll Sequence'],
			],
			'tp-search-bar' => [
				'label' => esc_html__('Search Bar', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-ajax-search-bar/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['search', 'post search','WordPress Search Bar', 'Find', 'Search Tool', 'SearchWP'],
			],
			'tp-social-icons' => [
				'label' => esc_html__('Social Icon','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-social-icons/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Social Icon', 'Icon', 'link']
			],
			'tp-social-embed' => [
				'label' => esc_html__('Social Embed','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-social-embed/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Social', 'tpgb'),
				'keyword' => ['iframe', 'facebook feed', 'facebook comments', 'facebook like', 'facebook share', 'facebook page' ]
			],
			'tp-social-feed' => [
				'label' => esc_html__('Social Feed','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-social-feed/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Social', 'tpgb'),
				'keyword' => ['feed', 'facebook', 'google', 'youtube', 'social', 'posts', 'instagram','vimeo']
			],
			'tp-social-sharing' => [
				'label' => esc_html__('Social Sharing','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-social-sharing-icons/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '#',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'tpgb'),
				'keyword' => ['Social Sharing', 'Social Media Sharing']
			],
			'tp-social-reviews' => [
				'label' => esc_html__('Social Reviews','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-social-reviews/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Social', 'tpgb'),
				'keyword' => ['social', 'reviews', 'rating', 'stars', 'badges']
			],
			'tp-spline-3d-viewer' => [
				'label' => esc_html__('Spline 3D Viewer','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-spline-3d-viewer/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['canvas animation', 'spline', '3d', 'Spline 3D viewer', 'Spline 3D model embed', 'Spline 3D interactive']
			],
			'tp-smooth-scroll' => [
				'label' => esc_html__('Smooth Scroll','tpgb'),
				'demoUrl' => 'https://theplusblocks.com/plus-blocks/smooth-scroll/',
				'docUrl' => '',
				'videoUrl' => '#',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
			],
			'tp-switcher' => [
				'label' => esc_html__('Switcher','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-content-switcher/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-content-switcher/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#video',
				'tag' => 'pro',
				'block_cate' => esc_html__('Tabbed', 'tpgb'),
				'keyword' => ['Switcher', 'on/off', 'switch control', 'toggle', 'true/false', 'toggle switch', 'state', 'binary']
			],
			'tp-table-content' => [
				'label' => esc_html__('Table of Contents','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-table-of-contents/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => [ 'Table of Contents', 'Contents', 'toc', 'index', 'listing', 'appendix' ]
			],
			'tp-tabs-tours' => [
				'label' => esc_html__('Tabs Tours', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-tab-content/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-tab-content/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#video',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Tabbed', 'tpgb'),
				'keyword' => ['Tabs', 'Tours', 'tab content', 'pills', 'toggle']
			],
			'tp-dynamic-category' => [
				'label' => esc_html__('Taxonomy Listing','tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-taxonomy-listing/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Listing', 'tpgb'),
				'keyword' => ['Category', 'Tags', 'Taxonomy', 'WP Term' , 'Category Grid' , 'product category' , 'Post' , 'CPT' , 'WooCommerce', 'Product Tags']
			],
			'tp-team-listing' => [
				'label' => esc_html__('Team Member', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-team-members/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Listing', 'tpgb'),
				'keyword' => ['Team Member Gallery', 'Team Gallery', 'Team Member Carousel']
			],
			'tp-testimonials' => [
				'label' => esc_html__('Testimonials', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-testimonial-reviews/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Listing', 'tpgb'),
				'keyword' => ['Testimonials', 'testimonial', 'slider', 'client reviews', 'ratings']
			],
			'tp-timeline' => [
				'label' => esc_html__('Timeline', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-timeline/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'tpgb'),
				'keyword' => ['timeline','Schedule','Sequence','History','Events','Timeframe','Historical data']
			],
			'tp-video' => [
				'label' => esc_html__('Video', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-video-player/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'tpgb'),
				'keyword' => ['Video', 'youtube video', 'vimeo video', 'video player', 'mp4 player', 'web player', 'youtube content', 'Youtube embed', 'youtube iframe']
			],
		];
	
		$this->block_extra = [
			'tp-advanced-border-radius' => [
				'label' => esc_html__('Advanced Border Radius', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-advanced-border-radius/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'tpgb'),
			],
			'tp-content-hover-effect' => [
				'label' => esc_html__('Content Hover Effect', 'tpgb'),
				'demoUrl' => 'https://theplusblocks.com/plus-extras/special-effects/',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'tpgb'),
			],
			'tp-continuous-animation' => [
				'label' => esc_html__('Continuous Animation', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-floating-effect/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'tpgb'),
			],
			'tp-display-rules' => [
				'label' => esc_html__('Display Rules', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-display-conditional-rules/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://theplusblocks.com/help/display-rules/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Extras', 'tpgb'),
			],
			'tp-equal-height' => [
				'label' => esc_html__('Equal Column Height', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-same-equal-height/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://theplusblocks.com/help/equal-column-height/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'tpgb'),
			],
			'tp-global-tooltip' => [
				'label' => esc_html__('Global Tooltip', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-global-tooltip/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'tpgb'),
			],
			'tp-magic-scroll' => [
				'label' => esc_html__('Magic Scroll', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/magic-scroll-effect-for-wordpress/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'tpgb'),
			],
			'tp-mouse-parallax' => [
				'label' => esc_html__('Mouse Parallax', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/mouse-hover-parallax/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'tpgb'),
			],
			'tp-scoll-animation' => [
				'label' => esc_html__('On Scroll Animation', 'tpgb'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-on-scroll-content-animations/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Extras', 'tpgb'),
			],
			'tp-3d-tilt' => [
				'label' => esc_html__('3D Tilt', 'tpgb'),
				'demoUrl' => 'https://theplusblocks.com/plus-extras/special-effects/',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'tpgb'),
			],
		];
	}
	
	/**
     * Theplus Gutenberg Display Page
     * @since  1.0.0
     */
    public function admin_page_display() {
		echo '<div id="tpgb-dash"></div>';
	}
	
	public function get_post_statuses_sql(){
		$statuses = array_map(
			function( $item ){
				return esc_sql( $item );
			},
			array( 'publish', 'private', 'pending', 'future', 'draft' )
		);
		return "'" . implode( "', '", $statuses ) . "'";
	}

	/*
	 * Scan Unused Blocks
	 * @since 1.3.1
	 */
	public function tpgb_is_block_used_not_fun(){
		if( defined('DOING_AJAX') && DOING_AJAX && isset( $_POST['nonce'] ) && !empty($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], 'tpgb-dash-ajax-nonce' ) ){
			global $wpdb;
			$block_scan =[];
			
			if(isset($_POST['default_block']) && $_POST['default_block']=='false'){
				$this->block_listout();
				if(!empty($this->block_lists)){
					foreach($this->block_lists as $key => $block){
						$found_in_posts = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_status IN ( {$this->get_post_statuses_sql()} ) AND post_content LIKE '%<!-- wp:tpgb/" . $key . "%' LIMIT 1");
						
						$block_scan[$key]= $found_in_posts ? 1 : 0;
						if( ! $found_in_posts ){
							$found_in_widgets = $wpdb->get_var("SELECT option_id FROM {$wpdb->options} WHERE option_name = 'widget_block' AND option_value LIKE '%<!-- wp:tpgb/" . $key . "%' LIMIT 1");
							$block_scan[$key]= $found_in_widgets ? 1 : 0;
						}
					}
				}
			}else if(isset($_POST['default_block']) && $_POST['default_block']!='' && $_POST['default_block']==true){
				$block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();
				if( !empty($block_types) ){
					foreach($block_types as $key => $block){
						if(str_contains($key, 'core/')){
							if( $key !='core/missing' && $key !='core/block'&& $key !='core/widget-group' && !empty($block->title) ){
								$core_key = str_replace( 'core/', '', $key );
								$core_key = esc_sql( $core_key );
								$pass_key = str_replace( 'core/', 'core-', $key );
								$found_in_posts = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_status IN ( {$this->get_post_statuses_sql()} ) AND post_content LIKE '%<!-- wp:" . $core_key . "%' LIMIT 1");
						
								$block_scan[$pass_key]= $found_in_posts ? 1 : 0;
								if( ! $found_in_posts ){
									$found_in_widgets = $wpdb->get_var("SELECT option_id FROM {$wpdb->options} WHERE option_name = 'widget_block' AND option_value LIKE '%<!-- wp:" . $core_key . "%' LIMIT 1");
									$block_scan[$pass_key]= $found_in_widgets ? 1 : 0;
								}
							}
						}
					}
				}
			}
			wp_send_json($block_scan);
			exit;
		}
		exit;
	}

	/*
	 * Unused Disable Blocks
	 * @since 1.4.4
	 */
	public function tpgb_disable_unsed_block_fun(){
		if( defined('DOING_AJAX') && DOING_AJAX && isset( $_POST['nonce'] ) && !empty($_POST['nonce']) && wp_verify_nonce( $_POST['nonce'], 'tpgb-dash-ajax-nonce') ){
			
			if(!isset($_POST['blocks']) || empty($_POST['blocks'])){
				echo 0;
				exit;
			}
			if(isset($_POST['default_block']) && $_POST['default_block']!='' && $_POST['default_block']=='false'){
				$blocks = json_decode(stripslashes($_POST['blocks']),true);
				$action_page = 'tpgb_normal_blocks_opts';
				$all_block = get_option($action_page);
				$update_block = [];
				if(is_array($blocks)){
					foreach($blocks as $key => $val){
						if($val===1){
							$update_block[] = $key;
						}
					}
					$all_block['enable_normal_blocks'] = map_deep( wp_unslash( $update_block ), 'sanitize_text_field');
					update_option( $action_page, $all_block );
					Tpgb_Library()->remove_backend_dir_files();
					echo 1;
					exit;
				}
			}
			
		}
		echo 0;
		exit;
	}

}

// Get it started
$Tpgb_Gutenberg_Settings_Options = new Tpgb_Gutenberg_Settings_Options();
$Tpgb_Gutenberg_Settings_Options->hooks();