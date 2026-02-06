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
    protected $setting_logo = '';
	
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

            add_action('init', [$this, 'nexter_block_init']);
		
			add_action( 'wp_ajax_tpgb_blocks_opts_save', array( $this,'tpgb_blocks_opts_save_action') );
			add_action( 'wp_ajax_tpgb_connection_data_save', array( $this,'tpgb_connection_data_save_action') );
			add_action( 'wp_ajax_tpgb_custom_css_js_save', array( $this,'tpgb_custom_css_js_save_action') );
			add_action( 'wp_ajax_tpgb_is_block_used_not', array( $this, 'tpgb_is_block_used_not_fun' ) );
			add_action( 'wp_ajax_tpgb_unused_disable_block', array( $this, 'tpgb_disable_unsed_block_fun' ) );
			add_action( 'wp_ajax_tpgb_performance_opt_cache', array( $this,'tpgb_performance_opt_cache_save_action') );
			
            if(!defined('NEXTER_EXT_VER')){
			    add_action( 'admin_enqueue_scripts', array( $this, 'tpgb_dash_admin_scripts' ), 10, 1 );
            }
			// Install WdesignKit
			add_action( 'wp_ajax_nexter_ext_plugin_install', [ $this, 'nexter_ext_plugin_install_ajax']);

			// Remove All Notice From Dashboard Screnn
			add_action( 'admin_head', array( $this,'nxt_remove_admin_notices_page') );


			// Install Nexter Theme
			add_action( 'wp_ajax_nexter_ext_theme_install', array( $this,'nexter_ext_theme_install_ajax') );

			// add Filter to Enable All Block 
			add_filter( 'tpgb_blocks_enable_all', array( $this, 'tpgb_blocks_enable_all_filter' ) );

			// Scan Unused Block & Disabled it filter function
			add_filter( 'tpgb_disable_unsed_block_filter', array( $this, 'tpgb_disable_unsed_block_filter_fun' ) );

            // Wdesignkit Block Enable Ajax
            add_action( 'wp_ajax_nxt_wdk_widget_ajax_call', array( $this,'nxt_wdk_widget_ajax_callback') );

            // WDesignKit Template Block List Merge hook
            add_filter( 'nexter_block_list_merge', [$this, 'nexter_block_list_merge_action'] , 10, 1 );

            // Store User Data in Database From Onborading
            add_action('wp_ajax_nxt_boarding_store', array($this, 'nxt_block_boarding_store'));

            add_filter( 'nxt_dashboard_localize_data', array( $this, 'localize_data' ),10,1 );

            add_filter( 'admin_body_class', function( $classes ) {
                if ( isset($_GET['page']) && $_GET['page'] === 'nxt_builder' ) {
                    $classes .= ' post-type-nxt_builder nxt-page-nexter-builder ';
                }
                return $classes;
            }, 11);

            add_action( 'wp_ajax_nexter_temp_api_call', [ $this, 'nexter_temp_api_call' ] );
		}
		
    }

    /**
     * Initiate Nexter Block
     * @since 4.2.0
     */

     public function nexter_block_init(){
        if(defined('TPGBP_VERSION')){
            $options = get_option( 'tpgb_white_label' );
            $this->setting_name = ( isset($options['brand_name']) && !empty($options['brand_name']) ) ? $options['brand_name'] : ( !empty($options['tpgb_plugin_name'])  ? $options['tpgb_plugin_name'] : __('Nexter','the-plus-addons-for-block-editor') ) ;
			$this->setting_logo = ( isset($options['theme_logo']) && !empty($options['theme_logo']) ) ? $options['theme_logo'] : ( isset($options['tpgb_plus_logo']) && !empty($options['tpgb_plus_logo'])  ? $options['tpgb_plus_logo'] : esc_url(TPGB_URL . 'dashboard/assets/svg/nexter-logo.svg') );
        }else{
            $this->setting_name = esc_html__('Nexter', 'the-plus-addons-for-block-editor');
			$this->setting_logo = esc_url(TPGB_URL . 'dashboard/assets/svg/nexter-logo.svg');
        }

        $this->block_listout();
    }

	/**
     * Initiate our hooks
     * @since 1.0.0
     */
	public function hooks() {
		if( is_admin() ){
            add_action( 'nxt_new_update_notice' , array( $this, 'nxt_new_update_notice_callback' ) );
            if( !defined('NEXTER_EXT_VER') ){
			    add_action('admin_menu', array( $this, 'add_options_page' ));
            }
		}
    }

    /**
     * Replace Textdomain Recursive
     * @since 4.6.0
     */
    public function nxt_replace_blockcate_domain( $array ) {

        foreach ( $array as $key => $item ) {
            if ( isset( $item['block_cate'] ) ) {
    
                // Replace ONLY the text-domain inside esc_html__()
                $array[$key]['block_cate'] = str_replace(
                    "the-plus-addons-for-block-editor",
                    "nexter-extension",
                    $item['block_cate']
                );
            }
        }
    
        return $array;
    }

    /**
     * Localize Data
     * @since 4.6.0
     */
    public function localize_data( $data ) {
		$default_load=get_option( 'tpgb_normal_blocks_opts' );
		//Apply Filters
        $nxt_format_widget = [];
        if ( has_filter( 'nxt_wdk_widget_ajax_call' ) ) {
            $wdk_widget_data = apply_filters('nxt_wdk_widget_ajax_call', 'nxt_wdk_get_widget_ajax');
            foreach ($wdk_widget_data as $block) {
                $uniqueKey = isset($block['title']) ? $block['title'] : 'block_' . $block['id'];
            
                $nxt_format_widget[$uniqueKey] = [
                    'label'      => esc_html($block['title']),
                    'demoUrl'    => esc_url($block['live_demo']),
                    'docUrl'     => '',
                    'videoUrl'   => '',
                    'tag'        => $block['free_pro'] === 'pro' ? 'pro' : 'free',
                    'block_cate' => esc_html__('WDesignKit', 'nexter-extension'),
                    'keyword'    => [],
                    'w_unique'   => $block['id'],
                    'uniqueId'   => $block['w_unique'],
                ];
                if (isset($block['w_type']) && !empty($block['w_type'])) {
                    if( $block['w_type'] === 'Publish' ){
                        $nxt_format_widget[$uniqueKey]['w_type'] = 'Publish';
                    }else if( $block['w_type'] === 'Draft' ){
                        $nxt_format_widget[$uniqueKey]['w_type'] = 'Draft';
                    }
                    
                }
            }
        }
		
        $ext_option = get_option('tpgb_connection_data');

        if ( is_array($ext_option) && isset($ext_option['nxtAiSettings']) && ! empty($ext_option['nxtAiSettings']) ) {
            $decrypted = Tp_Blocks_Helper::tpgb_simple_decrypt( $ext_option['nxtAiSettings'], 'dy' );

            // Convert JSON string → PHP array
            $decoded = json_decode($decrypted, true);

            // Safety check
            if (json_last_error() === JSON_ERROR_NONE) {
                $ext_option['nxtAiSettings'] = $decoded;
            } else {
                // Fallback: keep decrypted string if JSON fails
                $ext_option['nxtAiSettings'] = $decrypted;
            }
        }

		//Merge WDesignKit Block List
		$data['tpgb_nonce'] = wp_create_nonce( 'nexter_admin_nonce' );
		$data['tpgb_url'] = TPGB_URL.'dashboard/';
		$data['tpgb_pro'] = defined('TPGBP_VERSION');
		$data['dashData']['blockList'] = array_merge($this->nxt_replace_blockcate_domain($this->block_lists),$this->nxt_replace_blockcate_domain($this->block_extra),(array) $nxt_format_widget);
		$data['dashData']['avtiveBlock'] = ( isset( $default_load['enable_normal_blocks'] )  && is_array( $default_load['enable_normal_blocks'] ) ) ? count( array_filter( $default_load['enable_normal_blocks'], function( $block ) { return strpos( $block, 'tp-' ) === 0; }) ) : 0;
		$data['dashData']['enableBlock'] = array_merge( is_array($default_load['enable_normal_blocks']) ? $default_load['enable_normal_blocks'] : [], isset($default_load['tp_extra_option']) && is_array($default_load['tp_extra_option']) ? $default_load['tp_extra_option'] : [] );
		$data['dashData']['extOption'] = $ext_option;
		$data['dashData']['rollbacVer'] = Tpgb_Rollback::get_rollback_versions();
		$data['dashData']['tpgb_keyActmsg'] = class_exists('Tpgb_Pro_Library') ? Tpgb_Pro_Library::tpgb_pro_activate_msg() : '';
		//$data['dashData']['nxtactivateKey'] = get_option('tpgb_activate');
		$data['dashData']['tpgb_activePlan'] = ( class_exists('Tpgb_Pro_Library') && method_exists('Tpgb_Pro_Library', 'tpgb_get_activate_plan') ) ? Tpgb_Pro_Library::tpgb_get_activate_plan() : '';
		$data['dashData']['cacheData'] = [ get_option('tpgb_performance_cache') , get_option('tpgb_delay_css_js') , get_option('tpgb_defer_css_js') ];
		$data['dashData']['customCode'] = get_option('tpgb_custom_css_js');
        $data['dashData']['nxt_onboarding'] = get_option('nxt_onboarding_done');
        $data['dashData']['nxt_wdkit_url'] = 'https://wdesignkit.com/';
        $data['dashData']['extensionPro'] = defined('NXT_PRO_EXT_VER');
        $data['dashData']['tpgbRollbackUrl'] = wp_nonce_url(admin_url('admin-post.php?action=tpgb_rollback&version=TPGB_VERSION'), 'tpgb_rollback');
		return $data;
	}

    public function nxt_new_update_notice_callback(){
        $data = get_option( 'nxt_ext_menu_notice_count', [] );
        if ( ! is_array( $data ) ) {
            $data = [];
        }
        $flag = isset( $data['notice_flag'] ) ? intval( $data['notice_flag'] ) : 1;
        $data['menu_notice_count'] = $flag;
        update_option( 'nxt_ext_menu_notice_count', $data );

    }

    public function nxt_notice_should_show(){
        $data = get_option( 'nxt_ext_menu_notice_count', [] );
        if ( ! is_array( $data ) ) {
            return false;
        }
        $menu_count = isset( $data['menu_notice_count'] ) ? intval( $data['menu_notice_count'] ) : 0;
        $flag       = isset( $data['notice_flag'] ) ? intval( $data['notice_flag'] ) : 1;
        return $menu_count < $flag;
    }

	/**
     * Add menu options page
     * @since 1.0.0
     */
	public function add_options_page(){
		global $submenu;
		unset($submenu['themes.php'][20]);
		unset($submenu['themes.php'][15]);

        $options = get_option( 'tpgb_white_label' );
        
        // Set default or dynamic icon
        $menu_icon = 'dashicons-nxt-builder-groups';
        if ( isset( $options['theme_logo'] ) && !empty( $options['theme_logo'] ) ) {
            $menu_icon = esc_url( $options['theme_logo'] );
        }else if( isset( $options['tpgb_plus_logo'] ) && !empty( $options['tpgb_plus_logo'] ) ) {
            $menu_icon = esc_url( $options['tpgb_plus_logo'] );
        }

		add_menu_page( 
			esc_html( $this->setting_name ),
			esc_html( $this->setting_name ),
			'manage_options',
			'nexter_welcome',
			array( $this, 'nexter_ext_dashboard' ),
			$menu_icon,
			58
		);

		add_submenu_page(
			'nexter_welcome',
			__( 'Dashboard', 'the-plus-addons-for-block-editor' ),
			__( 'Dashboard', 'the-plus-addons-for-block-editor' ),
			'manage_options',
			'nexter_welcome',
		);

        if( !isset($options['nxt_template_tab']) || ( isset($options['nxt_template_tab']) && $options['nxt_template_tab'] !== 'on' )  ) {
            add_submenu_page(
                'nexter_welcome',
                __( 'Templates', 'the-plus-addons-for-block-editor' ),
                __( 'Templates', 'the-plus-addons-for-block-editor' ),
                'manage_options',
                'nexter_welcome#/templates',
                array( $this, 'nexter_ext_dashboard' ),
            );
        }
		add_submenu_page(
			'nexter_welcome',
			__( 'Blocks', 'the-plus-addons-for-block-editor' ),
			__( 'Blocks', 'the-plus-addons-for-block-editor' ),
			'manage_options',
			'nexter_welcome#/blocks',
			array( $this, 'nexter_ext_dashboard' ),
		);
        add_submenu_page(
            'nexter_welcome',
            __( 'Theme Builder', 'the-plus-addons-for-block-editor' ),
            __( 'Theme Builder', 'the-plus-addons-for-block-editor' ),
            'manage_options',
            'nxt_builder',
            array($this, 'nexter_blocks_builder_display')
        );
		add_submenu_page(
			'nexter_welcome',
			__( 'Code Snippets', 'the-plus-addons-for-block-editor' ),
			__( 'Code Snippets', 'the-plus-addons-for-block-editor' ),
			'manage_options',
			'nxt_code_snippets',
			array($this, 'nexter_code_snippet_display'),
		);
		add_submenu_page(
			'nexter_welcome',
			__( 'Extensions', 'the-plus-addons-for-block-editor' ),
			__( 'Extensions', 'the-plus-addons-for-block-editor' ),
			'manage_options',
			'nexter_welcome#/utilities',
			array( $this, 'nexter_ext_dashboard' ),
		);
		add_submenu_page(
			'nexter_welcome',
			__( 'Theme Customizer', 'the-plus-addons-for-block-editor' ),
			__( 'Theme Customizer', 'the-plus-addons-for-block-editor' ),
			'manage_options',
			'nexter_welcome#/theme_customizer',
			array( $this, 'nexter_ext_dashboard' ),
		);
        
        add_submenu_page( 'nexter_welcome', esc_html__( 'Patterns', 'the-plus-addons-for-block-editor' ), esc_html__( 'Patterns', 'the-plus-addons-for-block-editor' ), 'manage_options', esc_url( admin_url('edit.php?post_type=wp_block') ));

		if( !defined('TPGBP_VERSION') ){
			add_submenu_page( 
				'nexter_welcome', 
				esc_html__( 'Get Pro Nexter', 'the-plus-addons-for-block-editor' ), 
				esc_html__( 'Get Pro Nexter', 'the-plus-addons-for-block-editor' ), 
				'manage_options', 
				esc_url('https://nexterwp.com/pricing/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings')
			);
		}
        
		add_action('admin_footer', array($this, 'nxt_link_in_new_tab'));

		// Hook to modify the submenu head title
		//add_action('admin_menu', array($this, 'nxt_submenu_head_title') , 101);
	}

	/**
     * Parent Page Rename in Sub menu
     * @since 2.0.0
     */
	public function nxt_submenu_head_title() {
		global $submenu;
		if ( isset($submenu['nexter_welcome'] )) {
			$submenu['nexter_welcome'][0][0] = esc_html__( 'Dashboard', 'the-plus-addons-for-block-editor' );
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
                <?php if( $this->nxt_notice_should_show() ) { ?> 
                    var menuItem = document.querySelector('.toplevel_page_nexter_welcome.menu-top');
                    if (menuItem) {
                        menuItem.classList.add('nxt-admin-notice-active');
                    } 
                <?php } ?>

                // const menuLinks = document.querySelectorAll( "#toplevel_page_nexter_welcome_page .wp-submenu a" );

                // menuLinks.forEach((link) => {
                //     if (link.href.includes("nexter_welcome_page#")) {
                //     link.addEventListener("click", (e) => {
                //         e.preventDefault();
                //         const url = new URL(link.href);
                //         window.location.hash = url.hash; // update hash only
                //     });
                //     }
                // });
			});
		</script>
		<?php
	}

	/*
	 * Enqueue DashBoard Scripts admin area.
	 * @since 2.0.0
	 */
	/*
	 * Enqueue DashBoard Scripts admin area.
	 * @since 2.0.0
	 */
	public function tpgb_dash_admin_scripts( $page ) {
        
        $wdadded = false;
		$nxtextension = false;

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$pluginslist = get_plugins();
		if ( isset( $pluginslist[ 'wdesignkit/wdesignkit.php' ] ) && !empty( $pluginslist[ 'wdesignkit/wdesignkit.php' ] ) ) {
			if( is_plugin_active('wdesignkit/wdesignkit.php') ){
				$wdadded = true;
			}
		}

        $extensionactivate = false;
		if ( isset( $pluginslist[ 'nexter-extension/nexter-extension.php' ] ) && !empty( $pluginslist[ 'nexter-extension/nexter-extension.php' ] ) ) {
			if( is_plugin_active('nexter-extension/nexter-extension.php') ){
				$nxtextension = true;
			}else{
                $extensionactivate = true;
            }
		}

		if ( isset($_GET['page']) && $_GET['page'] === 'nexter_welcome') {
			$this->tpgb_dash_enqueue_style();
		    $this->tpgb_dash_enqueue_scripts();
		}else if( isset($_GET['page']) && $_GET['page'] === 'nxt_builder' ){
            // Theme Builder JS Enqueue
            wp_enqueue_style( 'nexter-theme-builder', TPGB_URL . 'dashboard/theme-builder/build/index.css', array(), TPGB_VERSION, 'all' );

            wp_enqueue_script( 'nexter-theme-builder', TPGB_URL . 'dashboard/theme-builder/build/index.js', array('react', 'react-dom', 'wp-dom-ready', 'wp-i18n'), TPGB_VERSION, true );

            $nexter_theme_builder_config = array(
                'adminUrl' => admin_url(),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('nexter_admin_nonce'),
                'assets' => TPGB_URL . 'dashboard/theme-builder/assets/',
                'is_pro' => (defined('NXT_PRO_EXT')) ? true : false,
                'dashboard_url' => admin_url( 'admin.php?page=nexter_welcome' ),
                'version' => TPGB_VERSION,
                'import_temp_nonce' => wp_create_nonce('nxt_ajax'),
                'wdkPlugin' => $wdadded,
                'extensioninstall' => $nxtextension,
                'extensionactivate' => $extensionactivate,
            );

            wp_localize_script( 'nexter-theme-builder', 'nexter_theme_builder_config', $nexter_theme_builder_config );

            wp_set_script_translations( 'nexter-theme-builder', 'the-plus-addons-for-block-editor', TPGB_PATH . '/languages/');
            
        }else if( isset($_GET['page']) && $_GET['page'] === 'nxt_code_snippets' ){
           
            wp_enqueue_style( 'nxt-code-snippet-style', TPGB_URL . 'dashboard/code-snippets/index.css', array(), TPGB_VERSION, 'all' );

            wp_enqueue_script( 'nxt-code-snippet', TPGB_URL . 'dashboard/code-snippets/index.js', array(), TPGB_VERSION, true );
            
            // Attach JavaScript translations
            wp_set_script_translations(
                'nxt-code-snippet',
                'the-plus-addons-for-block-editor',
            );
            wp_localize_script(
                'nxt-code-snippet',
                'nxt_code_snippet_data',
                array(
                    'ajax_url'    => admin_url( 'admin-ajax.php' ),
                    'ajax_nonce' => wp_create_nonce('nexter_admin_nonce'),
                    'extensioninstall' => $nxtextension,
                    'extensionactivate' => $extensionactivate,
                )
            );
        }
		
	}

	/*
	 * Enqueue Styles admin area.
	 * @since 2.0.0
	 */
	public function tpgb_dash_enqueue_style() {
		wp_enqueue_style( 'tpgb-dash-style', TPGB_URL . 'dashboard/build/index.css', array(), TPGB_VERSION, 'all' );
	}

	/**
	 * Enqueue script admin area.
	 *
	 * @since 2.0.0
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
        $plusAddons = false ;

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$pluginslist = get_plugins();

        $wdkactive = false;
		if ( isset( $pluginslist[ 'wdesignkit/wdesignkit.php' ] ) && !empty( $pluginslist[ 'wdesignkit/wdesignkit.php' ] ) ) {
			if( is_plugin_active('wdesignkit/wdesignkit.php') ){
				$wdadded = true;
			}else{
                $wdkactive = true;
            }
		}

        $extensionactivate = false;
		if ( isset( $pluginslist[ 'nexter-extension/nexter-extension.php' ] ) && !empty( $pluginslist[ 'nexter-extension/nexter-extension.php' ] ) ) {
			if( is_plugin_active('nexter-extension/nexter-extension.php') ){
				$nxtextension = true;
			}else{
                $extensionactivate = true;
            }
		}

        $nxtPlugin = false;
        $tpgbactivate = false;
        if ( isset( $pluginslist[ 'the-plus-addons-for-block-editor/the-plus-addons-for-block-editor.php' ] ) && !empty( $pluginslist[ 'the-plus-addons-for-block-editor/the-plus-addons-for-block-editor.php' ] ) ) {
            if( is_plugin_active('the-plus-addons-for-block-editor/the-plus-addons-for-block-editor.php') ){
                $nxtPlugin = true;
            }else{
                $tpgbactivate = true;
            }
        }

        $uichemyactive = false;
		if ( isset( $pluginslist[ 'uichemy/uichemy.php' ] ) && !empty( $pluginslist[ 'uichemy/uichemy.php' ] ) ) {
			if( is_plugin_active('uichemy/uichemy.php') ){
				$uichemy = true;
			}else{
                $uichemyactive = true;
			}
		}

		$active_theme = wp_get_theme();
		$theme_name = $active_theme->get('Name');
		if( isset($theme_name) && !empty($theme_name) && $theme_name == 'Nexter' ){
			$nxtheme = true;
		}else if ( file_exists( WP_CONTENT_DIR.'/themes/'.'nexter') && $theme_name != 'Nexter' ) {
			$nxtheme = 'available';
		}

        $tpaeactive = false;
        if ( isset( $pluginslist[ 'the-plus-addons-for-elementor-page-builder/theplus_elementor_addon.php' ] ) && !empty( $pluginslist[ 'the-plus-addons-for-elementor-page-builder/theplus_elementor_addon.php' ] ) ) {
            if( is_plugin_active('the-plus-addons-for-elementor-page-builder/theplus_elementor_addon.php') ){
                $plusAddons = true;
            }else{
                $tpaeactive = true;
            }
        }

        //Apply Filters
        $nxt_format_widget = [];
        if ( has_filter( 'nxt_wdk_widget_ajax_call' ) ) {
            $wdk_widget_data = apply_filters('nxt_wdk_widget_ajax_call', 'nxt_wdk_get_widget_ajax');
            foreach ($wdk_widget_data as $block) {
                $uniqueKey = isset($block['title']) ? $block['title'] : 'block_' . $block['id'];
            
                $nxt_format_widget[$uniqueKey] = [
                    'label'      => esc_html($block['title']),
                    'demoUrl'    => esc_url($block['live_demo']),
                    'docUrl'     => '',
                    'videoUrl'   => '',
                    'tag'        => $block['free_pro'] === 'pro' ? 'pro' : 'free',
                    'block_cate' => esc_html__('WDesignKit', 'the-plus-addons-for-block-editor'),
                    'keyword'    => [],
                    'w_unique'   => $block['id'],
                    'uniqueId'   => $block['w_unique'],
                ];

                if (isset($block['w_type']) && !empty($block['w_type'])) {
                    if( $block['w_type'] === 'Publish' ){
                        $nxt_format_widget[$uniqueKey]['w_type'] = 'Publish';
                    }else if( $block['w_type'] === 'Draft' ){
                        $nxt_format_widget[$uniqueKey]['w_type'] = 'Draft';
                    }
                    
                }
            }
        }
        
        if ( is_multisite() ) {
            $main_site_id = get_main_site_id();
            $licence_key = get_blog_option( $main_site_id, 'tpgb_activate', [] );
            if(empty($licence_key)){
                    $licence_key = get_option('tpgb_activate');
            }
        }else{
                $licence_key = get_option('tpgb_activate');
        }

		if ( $user ){

            $ext_option = get_option('tpgb_connection_data');

            if ( is_array($ext_option) && isset($ext_option['nxtAiSettings']) && ! empty($ext_option['nxtAiSettings']) ) {
                $decrypted = Tp_Blocks_Helper::tpgb_simple_decrypt( $ext_option['nxtAiSettings'], 'dy' );

                // Convert JSON string → PHP array
                $decoded = json_decode($decrypted, true);

                // Safety check
                if (json_last_error() === JSON_ERROR_NONE) {
                    $ext_option['nxtAiSettings'] = $decoded;
                } else {
                    // Fallback: keep decrypted string if JSON fails
                    $ext_option['nxtAiSettings'] = $decrypted;
                }
            }

			$dashData = [
				'userData' => [
					'userName' => esc_html($user->display_name),
					'profileLink' => esc_url( get_avatar_url( $user->ID ) ),
                    'userEmail' => get_option('admin_email'),
                    'siteUrl' => get_option('siteurl'),
				],
                'whiteLabelData' => [
                        'brandname' => $this->setting_name,
                        'brandlogo' => $this->setting_logo
                    ],
				'blockList'   => array_merge( $this->block_extra, $this->block_lists,(array) $nxt_format_widget),
				'avtiveBlock' => ( isset( $default_load['enable_normal_blocks'] )  && is_array( $default_load['enable_normal_blocks'] ) ) ? count( array_filter( $default_load['enable_normal_blocks'], function( $block ) { return strpos( $block, 'tp-' ) === 0; }) ) : 0,
				'enableBlock' => array_merge( is_array($default_load['enable_normal_blocks']) ? $default_load['enable_normal_blocks'] : [], isset($default_load['tp_extra_option']) && is_array($default_load['tp_extra_option']) ? $default_load['tp_extra_option'] : [] ),
				'extOption' => $ext_option,
				'cacheData' => [ get_option('tpgb_performance_cache') , get_option('tpgb_delay_css_js') , get_option('tpgb_defer_css_js') ],
				'customCode' => get_option('tpgb_custom_css_js'),
				'rollbacVer' => Tpgb_Rollback::get_rollback_versions(),
				'tpgbRollbackUrl' => $rollback_url,
				'wdadded' => $wdadded,
                'nexterBlock' => $nxtPlugin,
                'tpgbinstall' => $nxtPlugin,
                'nexterThemeActive' => (defined('NXT_VERSION')) ? true : false,
				'wdTemplates' => [],
				'nexterext' => $nxtextension,
                'extensioninstall'=>$nxtextension,
                'extensionPro' =>  defined('NXT_PRO_EXT_VER'),
				'wpVersion' => get_bloginfo('version'),
				'pluginVer' => TPGB_VERSION,
				'uichemy' => $uichemy,
				'nextheme' => $nxtheme,
                'nexterThemeIntall' =>  $nxtheme,
                'nexterCustLink' => admin_url('customize.php'),
				'whiteLabel' => get_option('tpgb_white_label'),
				'keyActmsg' => class_exists('Tpgb_Pro_Library') ? Tpgb_Pro_Library::tpgb_pro_activate_msg() : '',
				'nxtactivateKey' => $licence_key,
				'activePlan' => ( class_exists('Tpgb_Pro_Library') && method_exists('Tpgb_Pro_Library', 'tpgb_get_activate_plan') ) ? Tpgb_Pro_Library::tpgb_get_activate_plan() : '',
                'showSidebar' => $this->nxt_notice_should_show(),
                'nxt_onboarding' => get_option('nxt_onboarding_done'),
                'tpaeAddon'=>$plusAddons,
                'nxtThemeSetting' => (array) get_option( 'nexter_settings_opts', [] ),
                'nxt_wdkit_url'=> 'https://wdesignkit.com/',
                'extensionactivate' => $extensionactivate,
                'tpgbactivate' => $tpgbactivate,
                'tpaeactive' => $tpaeactive,
                'wdkactive' => $wdkactive,
                'uichemyactive' => $uichemyactive,
                'nexterThemeDet' => admin_url('themes.php').'?theme=nexter'
			];
		}

		wp_enqueue_script( 'nexter-dashscript', TPGB_URL . 'dashboard/build/index.js', array( 'react', 'react-dom','wp-i18n', 'wp-dom-ready', 'wp-element','wp-components', 'wp-block-editor', 'wp-editor' ), TPGB_VERSION, true );
        
		wp_localize_script(
			'nexter-dashscript',
			'nxtext_ajax_object',
			array(
				'adminUrl' => admin_url(),
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'ajax_nonce'       => wp_create_nonce( 'nexter_admin_nonce' ),
                'nxtex_url' => TPGB_URL . 'dashboard/',
				'tpgb_url' => TPGB_URL.'dashboard/',
				'pro' => defined('TPGBP_VERSION'),
				'dashData' => $dashData
			)
		);

        wp_set_script_translations( 'nexter-dashscript', 'the-plus-addons-for-block-editor' , TPGB_PATH . '/languages/' );
	}

	/*
	 * Install Wdesignkit Plugin
	 * @since 1.4.0
	 */
    public function nexter_ext_plugin_install_ajax(){

        check_ajax_referer( 'nexter_admin_nonce', 'nexter_nonce' );

        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'content' => __( 'Insufficient permissions.', 'the-plus-addons-for-block-editor' ) ) );
        }

        $plu_slug = ( isset( $_POST['slug'] ) && !empty( $_POST['slug'] ) ) ? sanitize_text_field( wp_unslash($_POST['slug']) ) : '';


        $phpFileName = $plu_slug;
        if(!empty($plu_slug) && $plu_slug == 'the-plus-addons-for-elementor-page-builder'){
            $phpFileName = 'theplus_elementor_addon';
        }

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
            wp_send_json_error( array( 'content' => __( 'Failed to retrieve plugin information.', 'the-plus-addons-for-block-editor' ) ) );
        }

        $skin     = new \Automatic_Upgrader_Skin();
        $upgrader = new \Plugin_Upgrader( $skin );

        $plugin_basename = ''.$plu_slug.'/'.$phpFileName.'.php';

        if ( ! isset( $installed_plugins[ $plugin_basename ] ) && empty( $installed_plugins[ $plugin_basename ] ) ) {
            $installed = $upgrader->install( $plugin_info->download_link );
            $activation_result = activate_plugin( $plugin_basename );
            if(!empty($plu_slug) && $plu_slug == 'wdesignkit'){
                $this->wdk_installed_settings_enable();
            }
            $success = null === $activation_result;
            wp_send_json(['Sucees' => true]);
        } elseif ( isset( $installed_plugins[ $plugin_basename ] ) ) {
            $activation_result = activate_plugin( $plugin_basename );
            if(!empty($plu_slug) && $plu_slug == 'wdesignkit'){
                $this->wdk_installed_settings_enable();
            }
            $success = null === $activation_result;
            wp_send_json(['Sucees' => true]);
        }

    }

    public function wdk_installed_settings_enable(){

        if( defined( 'TPGB_VERSION' ) ){
            $settings = array('gutenberg_builder' => true,'gutenberg_template' => true);
            $builder = array( 'elementor' );
            do_action( 'wdkit_active_settings', $settings, $builder );
        }else if( defined('ELEMENTOR_VERSION') ){
            $settings = array('elementor_builder' => true,'elementor_template' => true);
            $builder = array( 'nexter-blocks');
            do_action( 'wdkit_active_settings', $settings, $builder );
        }

    }

	/*
	 * Install Nexter Theme
	 * @since 2.0.0
	 */
    public function nexter_ext_theme_install_ajax(){
        check_ajax_referer( 'nexter_admin_nonce', 'nexter_nonce' ); 

        if ( !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'You are not allowed to do this action', 'the-plus-addons-for-block-editor' ) );
        }
        
        $theme_slug = (!empty($_POST['slug'])) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : 'nexter';
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
		check_ajax_referer('nexter_admin_nonce', 'security');
		
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
			wp_safe_redirect( esc_url( admin_url('admin.php?page=nexter_welcome') ) );
		}
		if(isset($_POST["submit-key"]) && !empty($_POST["submit-key"]) && $_POST["submit-key"]=='Save'){
			
			if ( ! isset( $_POST['nonce_tpgb_normal_blocks_opts'] ) || ! wp_verify_nonce( sanitize_key($_POST['nonce_tpgb_normal_blocks_opts']), 'nexter_admin_nonce' ) ) { //nonce_tpgb_normal_blocks_action
			   wp_safe_redirect( esc_url(admin_url('admin.php?page=nexter_welcome')) );
			} else {
				Tpgb_Library()->remove_backend_dir_files();
				if ( FALSE === get_option($action_page) ){
					$default_value = array('enable_normal_blocks' => '' , 'tp_extra_option' => '');
					add_option($action_page,$default_value);
					wp_safe_redirect( esc_url(admin_url('admin.php?page=nexter_welcome')) );
				}
				else{
					$update_value = array('enable_normal_blocks' => '');
					if(isset($_POST['enable_normal_blocks']) && !empty($_POST['enable_normal_blocks'])){
						$blockList = map_deep(wp_unslash(json_decode(stripslashes($_POST['enable_normal_blocks']), true)), 'sanitize_text_field');

						if(is_array($blockList)){
							$update_value = array('enable_normal_blocks' => $blockList );
						}else{
							$update_value = array('enable_normal_blocks' => sanitize_text_field($blockList) );
						}
					}
					
					$update_extra_val = array('tp_extra_option' => '');
					if(isset($_POST['tp_extra_option']) && !empty($_POST['tp_extra_option'])){
						$extraList = map_deep(wp_unslash(json_decode(stripslashes($_POST['tp_extra_option']), true)), 'sanitize_text_field');
    
						if(is_array($extraList)){
							$update_extra_val = array('tp_extra_option' =>  $extraList);
						}else{
							$update_extra_val = array('tp_extra_option' => sanitize_text_field($extraList) );
						}
					}
					
					$block_value = array_merge( $update_value , $update_extra_val );
					
					$updated = update_option($action_page, $block_value);
                    $response = '';
                    if ( has_filter( 'nxt_wdk_widget_ajax_call' ) ) {
                        $response = apply_filters( 'nxt_wdk_widget_ajax_call', 'wdk_update_widget' );
                    }

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
        if (class_exists('Tp_Blocks_Helper')) {
            error_log("Tp_Blocks_Helper class exists.");
        }
		$action_page = 'tpgb_connection_data';
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( esc_url( admin_url('admin.php?page=nexter_welcome') ) );
		}
		if (isset($_POST["submit-key"]) && !empty($_POST["submit-key"]) && $_POST["submit-key"] === 'Save') {
        // Verify nonce
        if (!isset($_POST['nonce_tpgb_connection_data']) || !wp_verify_nonce(sanitize_key($_POST['nonce_tpgb_connection_data']), 'nexter_admin_nonce')) {
            wp_send_json([
                'success' => false,
                'message' => 'Security check failed.'
            ], 403);
            exit;
        }

        // Validate connection data exists
        if (!isset($_POST['tpgb_connection_data']) || empty($_POST['tpgb_connection_data'])) {
            wp_send_json([
                'success' => false,
                'message' => 'No data provided.'
            ], 400);
            exit;
        }

        // Decode and validate JSON
        $getArr = json_decode(stripslashes(sanitize_text_field($_POST['tpgb_connection_data'])), true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($getArr)) {
            wp_send_json([
                'success' => false,
                'message' => 'Invalid data format.'
            ], 400);
            exit;
        }

        // Encrypt AI settings if they exist
        if (class_exists('Tp_Blocks_Helper') && isset($getArr['nxtAiSettings']) && is_array($getArr['nxtAiSettings'])) {
            $getArr['nxtAiSettings'] = Tp_Blocks_Helper::tpgb_simple_decrypt(
                json_encode($getArr['nxtAiSettings']),
                'ey'
            );
        }

        // Save or update option
        $option_exists = get_option($action_page);
        
        if (false === $option_exists) {
            // Option doesn't exist, add it
            $result = add_option($action_page, $getArr);
        } else {
            // Option exists, update it
            $result = update_option($action_page, $getArr);
        }

        // Send JSON response
        if ($result) {
            wp_send_json([
                'success' => true,
                'message' => 'Settings saved successfully.'
            ]);
        } else {
            wp_send_json([
                'success' => false,
                'message' => 'Failed to save settings. No changes were made.'
            ]);
        }
        exit;
        } else {
            // Invalid request, redirect to admin page
            wp_safe_redirect(esc_url(admin_url('admin.php?page=nexter_welcome')));
            exit;
        }

	}

	public function tpgb_custom_css_js_save_action(){
		$action_page = 'tpgb_custom_css_js';
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(['Success' => false]);
		}
		if(isset($_POST["submit-key"]) && !empty($_POST["submit-key"]) && $_POST["submit-key"]=='Save'){
			if ( ! isset( $_POST['nonce_tpgb_custom_css_js'] ) || ! wp_verify_nonce( sanitize_key($_POST['nonce_tpgb_custom_css_js']), 'nexter_admin_nonce' ) ) {
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
				'label' => esc_html__('Accordion','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-accordion-toggle/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-accordion-toggle/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Tabbed', 'the-plus-addons-for-block-editor'),
				'keyword' => [ 'accordion', 'FAQ', 'content accordion', 'collapsible content', 'expandable content', 'hover-accordion', 'SEO schema accordion', 'foldable content', 'video accordion'],
			],
			'tp-advanced-buttons' => [
				'label' => esc_html__('Pro Buttons', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-pro-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-pro-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['pro buttons', 'animated CTA button', 'animated download button', 'interactive button', 'hover-effect button', 'call-to-action button', 'CTA download button', 'stylish download button'],
			],
			'tp-advanced-chart' => [
				'label' => esc_html__('Advanced Chart', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-advanced-charts-and-graph/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/advanced-charts/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['chart', 'line chart', 'bar chart', 'radar chart', 'doughnut chart', 'pie chart', 'polar area chart', 'bubble chart', 'animated chart', 'chart tooltip', 'responsive chart', 'interactive chart'],
			],
			'tp-adv-typo' => [
				'label' => esc_html__('Advanced Typography', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-advanced-typography/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/advanced-typography/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['advanced typography', 'text effects', 'knockout text', 'circular text', 'marquee text', 'vertical text', 'text stroke', 'outline text', 'blend-mode text', 'text blend', 'image reveal text', 'vertical typography', 'text mask', 'arc text', 'underline animation'],
			],
			'tp-animated-service-boxes' => [
				'label' => esc_html__('Animated Service Boxes', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-animated-service-boxes/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/animated-service-boxes/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
                'keyword' => ['animated service boxes', 'image accordion', 'vertical image accordion', 'horizontal image accordion', 'info banner', 'service element', 'Lottie animated box', 'expandable service box', 'interactive service boxes', 'animated info banner', 'animated service element', 'animated Lottie box', 'animated expandable service box', 'animated interactive service box']
			],
			'tp-audio-player' => [
				'label' => esc_html__('Audio Player','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-audio-music-player/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/audio-player/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['audio player', 'music playlist', 'streaming audio', 'self-hosted audio', 'external audio source', 'audio playback', 'audio block', 'embed audio', 'sound player'],
			],
			'tp-before-after' => [
				'label' => esc_html__('Before After', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-before-after-image-comparison-slider/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/before-after/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
                'keyword' => ['before-after slider', 'image comparison', 'before after image', 'horizontal image compare', 'vertical image compare', 'opacity image slider', 'photo comparison', 'before after photo']
			],
			'tp-blockquote' => [
				'label' => esc_html__('Blockquote','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-blockquote-block/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/blockquote/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
                'keyword' => ['blockquote', 'quote box', 'author quote', 'text quote', 'quote', 'content quote', 'text highlighting'],
			],
			'tp-breadcrumbs' => [
				'label' => esc_html__('Breadcrumbs','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-breadcrumb-bar/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/breadcrumb/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['breadcrumb', 'breadcrumb navigation', 'breadcrumb trail', 'SEO breadcrumb', 'full-width breadcrumb', 'home icon breadcrumb', 'responsive breadcrumb', 'navigation path']
			],
			'tp-button' => [
				'label' => esc_html__('Advanced Button','the-plus-addons-for-block-editor'),				
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-advanced-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/advance-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => [  'advanced button', 'button', 'CTA button', 'hover-text button', 'icon button', 'call-to-action button', 'button with icon', 'gradient button', 'popup button interactions', 'eye-catching button designs']
			],
			'tp-button-core' => [
				'label' => esc_html__('Button','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['button', 'custom button', 'call-to-action button', 'CTA button', 'icon button', 'image button']
			],
			'tp-anything-carousel' => [
				'label' => esc_html__('Carousel Anything','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-advanced-carousel-sliders/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-advanced-carousel-sliders/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['carousel anything', 'vertical carousel', 'infinite loop slider', 'multi-column carousel', 'autoplay carousel', 'draggable slider', 'slider', 'autoplay slideshow', 'slideshow', 'mousewheel slider'],
			],
			'tp-carousel-remote' => [
				'label' => esc_html__('Carousel Remote','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-remote-sync/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-remote-sync/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
                'keyword' => ['carousel navigation', 'carousel remote', 'synced carousel', 'remote-control slider', 'remote navigation', 'next/prev controller', 'dot pagination controller', 'switcher remote', 'remote sync'],
			],
			'tp-circle-menu' => [
				'label' => esc_html__('Circle Menu', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-circle-menu/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/circle-menu/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['circle menu', 'bubble menu', 'circular nav', 'circular navigation', 'radial menu', 'toggle menu', 'icon menu']
			],
			'tp-code-highlighter' => [
				'label' => esc_html__('Code Highlighter', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-source-code-syntax-highlighter/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['syntax highlighter', 'code highlighter', 'code syntax highlighter', 'highlight code']
			],
			'tp-countdown' => [
				'label' => esc_html__('Countdown','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-countdown-timer/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/countdown/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['countdown timer', 'scarcity countdown', 'evergreen countdown', 'fake-number counter', 'content swap timer', 'FOMO countdown', 'inline countdown', 'marketing countdown timer', 'urgency countdown', 'event countdown', 'offer countdown']
			],
			'tp-container' => [
				'label' => esc_html__('Container','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-container/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/container/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['container', 'flexbox container', 'grid container', 'full-width container', 'boxed container', 'responsive container', 'sticky container', 'clickable container', 'parallax container', 'video container', 'animated container', 'multi-column container', 'nested container']
			],
			'tp-coupon-code' => [
				'label' => esc_html__('Coupon Code', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-coupon-code/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/coupon-code/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['coupon code', 'peel coupon', 'scratch coupon', 'slide-out coupon', 'masked link coupon', 'popup coupon', 'redirect coupon', 'discount code', 'promotional coupon', 'discount coupon']
			],
			'tp-creative-image' => [
				'label' => esc_html__('Advanced Image','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-advanced-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/advanced-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#video',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['advanced image', 'creative image', 'scroll reveal image', 'image mask', 'parallax image', 'animated image', 'scrolling image']
			],
			'tp-cta-banner' => [
				'label' => esc_html__('CTA Banner','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-cta-banner/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/cta-banner/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['advertisement banner', 'interactive banner', 'hover effects banner', 'parallax ad banner', 'CTA banner', 'promotional banner', 'ad banner', 'image banner', 'marketing banner']
			],
			'tp-data-table' => [
				'label' => esc_html__('Data Table','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-data-table/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-data-table/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => [ 'table', 'data table', 'sort table', 'search table', 'CSV import table', 'Google Sheet table', 'responsive table']
			],
			'tp-dark-mode' => [
				'label' => esc_html__('Dark Mode','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-dark-mode-switcher/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/dark-mode/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['dark mode toggle', 'dark mode button', 'night mode switcher', 'dark theme', 'night theme', 'night mode']
			],
			// 'tp-design-tool' => [
			// 	'label' => esc_html__('Design Tool','the-plus-addons-for-block-editor'),
			// 	'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-design-grid-tool/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
			// 	'docUrl' => '',
			// 	'videoUrl' => '',
			// 	'tag' => 'pro',
			// 	'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
			// 	'keyword' => ['design','tool']
			// ],
			'tp-draw-svg' => [
				'label' => esc_html__('Draw SVG','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-draw-animated-svg-icon/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/draw-svg/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['draw SVG', 'SVG animation', 'custom SVG animation', 'SVG path animation']
			],
			'tp-dynamic-device' => [
				'label' => esc_html__('Dynamic Device','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-device-mockups/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-device-mockups/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['dynamic device', 'device mockup', 'responsive screen mockup', 'iframe mockup', 'manual scroll device', 'auto-scroll device', 'device slider', 'custom device mockup', 'mockup carousel', 'carousel mockup', 'scrolling device preview', 'live website mockup']
			],
			'tp-empty-space' => [
				'label' => esc_html__('Spacer','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-spacer/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/spacer/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['blank space', 'spacer', 'vertical space', 'empty block', 'adjustable spacer', 'white-space', 'page spacer', 'content gap']
			],
			'tp-external-form-styler' => [
				'label' => esc_html__('External Form Styler','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-contact-form-stylers/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-contact-form-stylers/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['external form styling', 'form customization', 'form design', 'responsive form design', 'CSS form styling']
			],
			'tp-expand' => [
				'label' => esc_html__('Expand','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-unfold-read-more-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-unfold-read-more-button/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['expandable content', 'collapsible section', 'unfold section', 'content toggle', 'collapsible panel', 'expand button', 'read more section', 'load more section']
			],
			'tp-flipbox' => [
				'label' => esc_html__('Flipbox','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-flipbox/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/flipbox/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['flipbox', 'horizontal flipbox', 'vertical flipbox', 'flipbox carousel', 'flipbox grid', 'interactive flipbox', 'flipbox with button', 'animated flipbox', 'responsive flipbox',],
			],
            'tp-form-block' => [
                'label' => esc_html__('Form', 'the-plus-addons-for-block-editor'),
               'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-form-builder/',
                'docUrl' => '',
                'videoUrl' => '',
                'tag' => 'freemium',
                'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
                'keyword' => ['form builder', 'contact form', 'newsletter form', 'free form', 'CAPTCHA form', 'Google reCAPTCHA form', 'Cloudflare Turnstile form', 'email notification form', 'database entry form', 'redirect form', 'Brevo form', 'Mailchimp form', 'GetResponse form', 'ConvertKit form', 'Slack form', 'Discord form', 'WebHook form', 'Drip form']
            ],
			'tp-google-map' => [
				'label' => esc_html__('Google Map','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-google-maps/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-google-maps/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['google map', 'multiple locations map', 'custom map marker', 'multiple map markers', 'map overlay content', 'map terrain map', 'hybrid map', 'satellite map', 'roadmap']
			],
			'tp-heading-animation' => [
				'label' => esc_html__('Heading Animation','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-heading-animation/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/heading-animation/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['animated heading', 'text animation', 'heading animation', 'text animation effects', 'typing heading', 'flipping text', 'zoom-in heading', 'underline heading animation', 'slide-in heading', 'bounce letter heading', 'prefix-postfix heading', 'split text animation', 'looping animated title', 'heading highlight animation']
			],
			'tp-heading' => [
				'label' => esc_html__('Heading','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-heading/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-heading/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['heading', 'heading text', 'heading block', 'custom heading']
			],
			'tp-heading-title' => [
				'label' => esc_html__('Advanced Heading','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-title-block/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/advance-heading/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['advanced heading', 'styled heading', 'animated heading', 'heading with subtitle', 'subtitle', 'extra title', 'heading separator']
			],
			'tp-hotspot' => [
				'label' => esc_html__('Hotspot','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-hotspot-pinpoint-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/hotspot/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => [ 'hotspot image', 'interactive hotspot', 'image pin tooltip', 'icon hotspot', 'text hotspot', 'hotspot tooltip', 'animated hotspot', 'custom hotspot' ],
			],
			'tp-hovercard' => [
				'label' => esc_html__('Hover Card','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/hover-card-animations-wordpress/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/hover-card/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['hover card', 'interactive card', 'animated hover card', 'custom hover layout', 'responsive hover card', 'customizable card'],
			],
			'tp-icon-box' => [
				'label' => esc_html__('Icon','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-icon/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-icon/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['icon', 'custom icon', 'SVG icon', 'font icon', 'icon hover effect']
			],
			'tp-image' => [
				'label' => esc_html__('Image','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['image', 'image block', 'insert image', 'custom image']
			],
			'tp-infobox' => [
				'label' => esc_html__('Infobox','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-infobox/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/infobox/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['infobox', 'infobox layout', 'infobox carousel', 'infobox listing', 'animated info box', 'svg infobox', 'linked infobox', 'icon infobox', 'image infobox', 'responsive infobox', 'infobox button']
			],
			'tp-interactive-circle-info' => [
				'label' => esc_html__('Interactive Circle Info','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-interactive-circle-infographic/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/interactive-circle-info/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Tabbed', 'the-plus-addons-for-block-editor'),
				'keyword' => ['interactive circle', 'circle infographic', 'interactive circle info', 'circular CTA', 'circle navigation']
			],
			'tp-login-register' => [
				'label' => __('Login & Signup', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-login-form/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-login-and-registration-form/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['login form', 'registration form', 'signup form', 'wp login form', 'wp registration form', 'login register form', 'social login', 'password reset', 'login redirect', 'recaptcha signup', 'facebook login', 'google login', 'mailchimp subscribe', 'user registration form', 'user login form']
			],
			'tp-lottiefiles' => [
				'label' => esc_html__('LottieFiles Animation','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-lottiefiles-animations/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/lottie-animations-nexter-blocks/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['Lottie animation', 'Lottie autoplay', 'Lottie hover animation', 'Lottie scroll animation', 'Lottie parallax effect']
			],
			'tp-mailchimp' => [
				'label' => esc_html__('Mailchimp','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-mailchimp-form/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['Mailchimp signup form', 'email opt-in form', 'newsletter subscription form', 'GDPR-ready form', 'double opt-in signup', 'lead capture form', 'subscription form', 'email marketing form']
			],
			'tp-media-listing' => [
				'label' => esc_html__('Media Listing','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-image-gallery/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Listing', 'the-plus-addons-for-block-editor'),
				'keyword' => ['gallery listing', 'media listing', 'image gallery', 'video gallery', 'grid gallery', 'masonry gallery', 'metro gallery', 'carousel gallery', 'filterable gallery', 'repeater gallery']
			],
			'tp-messagebox' => [
				'label' => esc_html__('Message box','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-message-box/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/message-box/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['message box', 'alert box', 'notification box', 'dismissible message', 'closeable alert', 'CTA notification', 'warning message', 'user alert', 'pop-up message']
			],
			'tp-mobile-menu' => [
				'label' => esc_html__('Mobile Menu','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-mobile-menu/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
				'keyword' => ['mobile menu', 'off-canvas menu', 'swiper menu', 'split mobile menu', 'fixed bottom menu', 'sticky mobile nav', 'extra toggle bar', 'slide-out menu']
			],
			'tp-mouse-cursor' => [
				'label' => esc_html__('Mouse Cursor','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-custom-cursors/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/mouse-cursor/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['custom cursor', 'mouse cursor', 'mouse pointer', 'follow image cursor', 'follow text cursor', 'follow circle cursor', 'blend cursor', 'cursor effects', 'custom pointer', 'animated mouse pointer']
			],
			'tp-navigation-builder' => [
				'label' => esc_html__('Navigation Menu','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-navigation-menu/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
				'keyword' => ['navigation menu', 'horizontal menu', 'vertical menu', 'mega menu', 'mobile navigation', 'toggle menu', 'menu hover effect', 'repeater menu', 'responsive menu', 'swiper menu', 'hamburger menu', 'dropdown menu', 'custom menu', 'mobile-friendly menu']
			],
			'tp-number-counter' => [
				'label' => esc_html__('Number Counter','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-number-counter/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/number-counter/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['number counter', 'animated counter', 'milestone counter', 'counter block', 'number animation', 'statistics counter', 'counter animation', 'count-up block', 'KPI counter', 'live counter']
			],
			'tp-popup-builder' => [
				'label' => esc_html__('Popup Builder','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-popup-builder/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
				'keyword' => ['popup', 'popup builder', 'modal popup', 'off-canvas popup', 'slide popup', 'corner popup', 'exit-intent popup', 'page scroll popup', 'timed popup']
			],
			'tp-post-author' => [
				'label' => esc_html__('Post Author', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-author-box/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
				'keyword' => ['post author box', 'author bio', 'author information', 'post author profile', 'blog author details', 'author image', 'author name', 'author social links', 'author details', 'customized author box', 'author info', 'blog post author', 'author box']
			],
			'tp-post-comment' => [
				'label' => esc_html__('Post Comments', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-comment-form/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
                'keyword' => ['post comment', 'comment section', 'blog comments', 'user feedback', 'comment form']
			],
			'tp-post-content' => [
				'label' => esc_html__('Post Content', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-content/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
				'keyword' => ['post content', 'blog content', 'dynamic post content', 'post excerpt']
			],
			'tp-post-image' => [
				'label' => esc_html__('Post Image', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-featured-image/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
				'keyword' => ['post featured image', 'blog featured image', 'featured image', 'dynamic post image', 'post thumbnail']
			],
			'tp-post-listing' => [
				'label' => esc_html__('Post Listing', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-post-listing/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Listing', 'the-plus-addons-for-block-editor'),
				'keyword' => ['blog listing', 'post listing', 'post grid', 'masonry blog', 'metro blog layout', 'post carousel', 'post filter', 'load more posts', 'post pagination', 'lazy load posts', 'order posts']
			],
			'tp-post-meta' => [
				'label' => esc_html__('Post Meta Info', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-meta-info/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
				'keyword' => [ 'post meta', 'meta data', 'post meta information', 'date meta', 'author name meta', 'taxonomy meta', 'comment count meta', 'post metadata']
			],
			'tp-post-navigation' => [
				'label' => esc_html__('Post Navigation', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-navigation/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
				'keyword' => ['post navigation', 'blog post nav', 'prev next buttons', 'post prev next', 'blog post navigation', 'next post button', 'previous post button', 'blog navigation buttons']
			],
			'tp-post-title' => [
				'label' => esc_html__('Post Title', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-post-title/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
				'keyword' => ['post title', 'dynamic title', 'post heading', 'blog title']
			],
			'tp-pricing-list' => [
				'label' => esc_html__('Pricing List','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-pricing-list/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/pricing-list/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['pricing list', 'menu price list', 'modern pricing list', 'food menu', 'menu list', 'flip-box pricing']
			],
			'tp-pricing-table' => [
				'label' => esc_html__('Pricing Table','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-pricing-table/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/pricing-table/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['pricing table', 'pricing plan comparison', 'pricing comparison', 'responsive pricing table', 'highlighted pricing']
			],
			'tp-preloader' => [
				'label' => esc_html__('Pre Loader','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-preloader-animation-and-page-transitions/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-preloader-animation-and-page-transitions/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => [ 'preloader', 'page loader', 'page loader animation', 'Lottie preloader', 'image preloader', 'icon preloader', 'text preloader', 'progress bar preloader', 'multiple preloaders', 'page transition', 'custom preloader', 'loading animations' ],
			],
			'tp-pro-paragraph' => [
				'label' => esc_html__('Paragraph','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-pro-paragraph/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-pro-paragraph/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#video',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['paragraph', 'styled paragraph', 'advanced paragraph'],
			],
			'tp-process-steps' => [
				'label' => esc_html__('Process Steps','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-process-steps/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/process-steps/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['process steps', 'step-by-step flow', 'horizontal steps', 'vertical steps', 'numbered steps', 'icon steps', 'image steps', 'Lottie steps', 'interactive process steps', 'custom process steps']
			],
			'tp-product-listing' => [
				'label' => esc_html__('Product Listing','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-product-listing/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Listing', 'the-plus-addons-for-block-editor'),
				'keyword' => ['product listing', 'WooCommerce products', 'product grid', 'product carousel', 'product metro style', 'product masonry style', 'product filtering', 'product pagination', 'load-more products']
			],
			'tp-progress-bar' => [
				'label' => esc_html__('Progress Bar','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-progress-bar/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/progress-bar/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['progress bar', 'circular progress', 'circle progress bar', 'linear progress bar', 'animated progress bar', 'percentage counter', 'progress indicator', 'skill meter', 'percentage progress indicator', 'pie progress', 'bar chart progress']
			],
			'tp-progress-tracker' => [
				'label' => esc_html__('Progress Tracker','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-reading-scroll-progress-bar/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/progress-tracker/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => [ 'reading progress tracker', 'reading progress bar', 'scroll progress bar', 'reading indicator', 'page scroll meter', 'content scroll indicator', 'reading time bar', 'reading-meter' ]
			],
            'tp-repeater-block' => [
				'label' => esc_html__('Repeater', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-repeater/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/docs/display-dynamic-repeater-field-data-in-wordpress/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Listing', 'the-plus-addons-for-block-editor'),
				'keyword' => ['repeater field', 'dynamic repeater', 'custom field listing', 'ACF repeater', 'JetEngine repeater', 'SCF repeater', 'repeater block']
			],
			// 'tp-row' => [
			// 	'label' => esc_html__('Row','the-plus-addons-for-block-editor'),
			// 	'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-container/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
			// 	'docUrl' => '',
			// 	'videoUrl' => '',
			// 	'tag' => 'free',
			// 	'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
			// 	'keyword' => ['Row', 'layout'],
			// ],
			'tp-site-logo' => [
				'label' => esc_html__('Site Logo','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-site-logo/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '#video',
				'tag' => 'free',
				'block_cate' => esc_html__('Builder', 'the-plus-addons-for-block-editor'),
				'keyword' => ['site logo', 'site logo uploader', 'custom logo', 'sticky header logo', 'logo change on hover', 'interactive logo', 'custom logo link', 'dual logo'],
			],
			'tp-stylist-list' => [
				'label' => esc_html__('Stylish List','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-stylish-list/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/stylist-list/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#video',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['stylish list', 'icon list', 'stylish icon list', 'vertical icon list', 'horizontal icon list', 'interactive icon list', 'tooltip icon list', 'hover background list', 'pin hint list', 'read more list'],
			],
			'tp-scroll-navigation' => [
				'label' => esc_html__('Scroll Navigation','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-one-page-scroll-navigation/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/scroll-navigation/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#video',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['scroll navigation', 'one page navigation', 'single page scrolling', 'section-based navigation', 'smooth scroll navigation'],
			],
			'tp-scroll-sequence' => [
				'label' => esc_html__('Scroll Sequence','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-image-scroll-sequence/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/scroll-sequence/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#video',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['scroll animation', 'scroll sequence', 'image gallery animation', 'dynamic scroll effects', 'interactive image sequences', 'image sequence animation', 'sequence scrolling'],
			],
			'tp-search-bar' => [
				'label' => esc_html__('Search Bar', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-ajax-search-bar/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/search-bar/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => [ 'ajax search bar', 'live search bar', 'ajax live search', 'search autocomplete', 'taxonomy search', 'cpt search', 'product search', 'advanced search', 'real-time search', 'instant search', 'custom post type search', 'search suggestions', 'site search', 'custom field search'],
			],
			'tp-social-icons' => [
				'label' => esc_html__('Social Icon','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-social-icons/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/social-icon/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['social icons', 'tooltip social icon', 'animated social icon', 'custom social links', 'vertical icon layout', 'horizontal icon layout']
			],
			'tp-social-embed' => [
				'label' => esc_html__('Social Embed','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-social-embed/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Social', 'the-plus-addons-for-block-editor'),
				'keyword' => ['social media embed', 'facebook embed', 'X (twitter) embed', 'instagram embed', 'youtube embed', 'vimeo embed', 'google maps embed', 'social feed', 'facebook comments', 'facebook post', 'facebook vVideo', 'facebook page', 'facebook like button', 'twitter profile', 'instagram reels', 'youtube playlist', 'google maps', 'vimeo video', 'tweets', 'retweet button', 'twitter follow button', 'twitter likes timeline']
			],
			'tp-social-feed' => [
				'label' => esc_html__('Social Feed','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-social-feed/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Social', 'the-plus-addons-for-block-editor'),
				'keyword' => ['social feed', 'live social feed', 'instagram feed', 'facebook feed', 'X (twitter) feed', 'youtube feed', 'vimeo feed', 'multi-platform social feed', 'social feed carousel', 'masonry social feed', 'grid social feed', 'social media wall']
			],
			'tp-social-sharing' => [
				'label' => esc_html__('Social Sharing','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-social-sharing-icons/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/social-sharing/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#',
				'tag' => 'pro',
				'block_cate' => esc_html__('Advanced', 'the-plus-addons-for-block-editor'),
				'keyword' => ['social sharing', 'share buttons', 'share counter', 'social share toggle', 'horizontal social share', 'vertical social share', 'fake share count']
			],
			'tp-social-reviews' => [
				'label' => esc_html__('Social Reviews','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-social-reviews/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Social', 'the-plus-addons-for-block-editor'),
				'keyword' => ['social reviews', 'facebook reviews', 'google reviews', 'manual reviews', 'review badge', 'review carousel', 'review grid layout', 'facebook badge', 'google badge', 'social proof block', 'customer reviews', 'user reviews', 'social validation']
			],
			'tp-spline-3d-viewer' => [
				'label' => esc_html__('Spline 3D Viewer','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-spline-3d-viewer/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/spline-3d-viewer/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
				'keyword' => ['3D viewer', 'interactive 3D objects', 'Spline 3D viewer', 'embedded 3D model', '3D embed', '3D object viewer', 'web-3D model', '3D model embed', 'responsive 3D model']
			],
			'tp-smooth-scroll' => [
				'label' => esc_html__('Smooth Scroll','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-smooth-scroll/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/smooth-scroll/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#',
				'tag' => 'free',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
                'keyword' => ['smooth scroll', 'smooth scroll effect', 'smooth scrolling', 'page smooth scroll', 'scroll navigation', 'infinite scroll', 'scrolling effects', 'scrolling animation']
			],
			'tp-switcher' => [
				'label' => esc_html__('Switcher','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-content-switcher/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-content-switcher/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#video',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Tabbed', 'the-plus-addons-for-block-editor'),
				'keyword' => ['switcher', 'content toggle', 'dual content switch', 'dual content', 'comparison toggle', 'plan switcher']
			],
			'tp-table-content' => [
				'label' => esc_html__('Table of Contents','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-table-of-contents/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/table-of-content/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => [ 'table of contents', 'TOC', 'collapsible table', 'sticky table of contents', 'collapsible TOC', 'sticky TOC', 'smooth-scroll TOC', 'fixed table of contents', 'article navigation', 'content outline' ]
			],
			'tp-tabs-tours' => [
				'label' => esc_html__('Tabs Tours', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-tab-content/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/wordpress-tab-content/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '#video',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Tabbed', 'the-plus-addons-for-block-editor'),
				'keyword' => [ 'tabs block', 'tabs tours', 'content tabs', 'tab navigation', 'tab layout', 'vertical tab', 'horizontal tab' ]
			],
			'tp-dynamic-category' => [
				'label' => esc_html__('Taxonomy Listing','the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/builder/wordpress-taxonomy-listing/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Listing', 'the-plus-addons-for-block-editor'),
				'keyword' => ['dynamic category', 'dynamic tags', 'dynamic taxonomy', 'dynamic term', 'dynamic category grid', 'dynamic product category', 'dynamic post category', 'dynamic CPT category', 'dynamic WooCommerce category', 'dynamic product tags']
			],
			'tp-team-listing' => [
				'label' => esc_html__('Team Member', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-team-members/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Listing', 'the-plus-addons-for-block-editor'),
				'keyword' => ['team member listing', 'team listing', 'team profile', 'team carousel', 'team grid', 'team masonry', 'team profile filter', 'team bio']
			],
			'tp-testimonials' => [
				'label' => esc_html__('Testimonials', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/listing/wordpress-testimonial-reviews/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => '',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Listing', 'the-plus-addons-for-block-editor'),
				'keyword' => ['testimonial', 'client review', 'testimonial carousel', 'review grid', 'masonry testimonials', 'testimonial grid', 'customer feedback', 'client testimonials']
			],
			'tp-timeline' => [
				'label' => esc_html__('Timeline', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-timeline/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/timeline/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Creative', 'the-plus-addons-for-block-editor'),
                'keyword' => ['timeline', 'event timeline', 'milestone timeline', 'vertical timeline', 'masonry timeline', 'responsive timeline']
			],
			'tp-video' => [
				'label' => esc_html__('Video', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/blocks/wordpress-video-player/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-blocks/video/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Essential', 'the-plus-addons-for-block-editor'),
				'keyword' => ['video embed', 'video player', 'YouTube embed', 'Vimeo video', 'self-hosted video', 'video popup', 'responsive video player', 'video embedding']
			],
		];
	
		$this->block_extra = [
            'tp-global-block-style' => [
				'label' => esc_html__('Global Block Style', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-global-block-style/',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
			'tp-advanced-border-radius' => [
				'label' => esc_html__('Advanced Border Radius', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-advanced-border-radius/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
			'tp-content-hover-effect' => [
				'label' => esc_html__('Content Hover Effect', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/mouse-hover-animation-for-wordpress/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
			'tp-continuous-animation' => [
				'label' => esc_html__('Continuous Animation', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-floating-effect/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
			'tp-display-rules' => [
				'label' => esc_html__('Display Rules', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-display-conditional-rules/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-extras/wordpress-display-conditional-rules/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
			'tp-equal-height' => [
				'label' => esc_html__('Equal Column Height', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-same-equal-height/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'docUrl' => 'https://nexterwp.com/help/nexter-extras/wordpress-same-equal-height/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'free',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
			'tp-global-tooltip' => [
				'label' => esc_html__('Global Tooltip', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-global-tooltip/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
			'tp-magic-scroll' => [
				'label' => esc_html__('Magic Scroll', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/magic-scroll-effect-for-wordpress/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
			'tp-mouse-parallax' => [
				'label' => esc_html__('Mouse Parallax', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/mouse-hover-animation-for-wordpress/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
			'tp-scoll-animation' => [
				'label' => esc_html__('On Scroll Animation', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/wordpress-on-scroll-content-animations/?utm_source=wpbackend&utm_medium=blocks&utm_campaign=nextersettings',
				'videoUrl' => '',
				'tag' => 'freemium',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
			'tp-3d-tilt' => [
				'label' => esc_html__('3D Tilt', 'the-plus-addons-for-block-editor'),
				'demoUrl' => 'https://nexterwp.com/nexter-blocks/extras/mouse-hover-animation-for-wordpress/#tilt-3d',
				'videoUrl' => '',
				'tag' => 'pro',
				'block_cate' => esc_html__('Extras', 'the-plus-addons-for-block-editor'),
			],
		];
	}
	
    /**
     * Render Dashboard Root Div
     * @since 1.0.0 nxtext
     */
    public function nexter_ext_dashboard() {
        echo '<div id="nexter-dash"></div>';
        do_action('nxt_new_update_notice');
    }
	
    /**
     * Code Snippet Render html
     * @since  1.0.0
     */
    public function nexter_code_snippet_display() {
        $features = [
            __('Replace multiple small plugins with a single, unified snippet manager', 'the-plus-addons-for-block-editor'),
            __('Add PHP, HTML, CSS safely without editing theme or core files', 'the-plus-addons-for-block-editor'),
            __('Apply snippets site-wide or on specific pages, posts, or conditions', 'the-plus-addons-for-block-editor'),
            __('Stay protected with built-in validation, version control & rollback', 'the-plus-addons-for-block-editor'),
            __('Keep your site fast, only active snippets are loaded', 'the-plus-addons-for-block-editor'),
            __('Import or Organise snippets from our 1000+ Cloud Snippets Library', 'the-plus-addons-for-block-editor'),
            __('Preview, test, and toggle snippets without affecting live visitors', 'the-plus-addons-for-block-editor'),
        ];

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $pluginslist = get_plugins();

        $extensioninstall = false;
        $extensionactivate = false;
        if ( isset( $pluginslist[ 'nexter-extension/nexter-extension.php' ] ) && !empty( $pluginslist[ 'nexter-extension/nexter-extension.php' ] ) ) {
            if( is_plugin_active('nexter-extension/nexter-extension.php') ){
                $extensioninstall = true;
            }else{
                $extensionactivate = true;
            }
        }

        echo '<div id="nexter-code-snippets">
            <div class="nxt-code-heading-title">'.esc_html__('Code Snippets','the-plus-addons-for-block-editor').'</div>
            <section class="nxt-install-ext-sec">
                <div class="nxt-ins-ext-cover">
                    <div class="nxt-ins-ext-inner">
                        <span class="badge">' . esc_html__('Code Snippets', 'the-plus-addons-for-block-editor') . '</span>
                        <h1 class="nxt-ins-heading">' . esc_html__('Add Custom PHP, HTML, CSS & JS Code to WordPress - No Child Theme or Extra Plugins Needed', 'the-plus-addons-for-block-editor') . '</h1>
                        <p class="nxt-ins-desc">' . esc_html__('Skip the pain of editing core files or juggling dozens of mini plugins. Nexter Code Snippets lets you safely add and manage custom PHP, HTML, CSS right inside your dashboard, no child theme required. Build smarter, cleaner sites with total control and zero risk to performance.', 'the-plus-addons-for-block-editor') . '</p>
                        <div class="nxt-ins-btns">
                            <button class="nxt-inst-ext-btn ins-btn-primary">' . (!empty($extensionactivate) ? esc_html__('Activate Nexter Extensions', 'the-plus-addons-for-block-editor') : esc_html__('Install Nexter Extensions', 'the-plus-addons-for-block-editor')) . '</button>
                            <a href="https://nexterwp.com/nexter-extension/features/#code%20snippets?utm_source=wpbackend&utm_medium=dashboard&utm_campaign=nextersettings" rel="noopener noreferrer" target="_blank" class="ins-btn-outline">' . esc_html__('Explore All Features', 'the-plus-addons-for-block-editor') . '</a>
                        </div>
                        <ul class="nxt-feature-list">';

                        foreach ($features as $feature) {
                            echo '<li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" fill="none" viewBox="0 0 14 15">
                                    <path fill="#1717cc" d="M6.373.505a7.13 7.13 0 0 0-3.7 1.474c-.535.41-1.228 1.183-1.628 1.813a9.6 9.6 0 0 0-.63 1.29c-.135.367-.28.931-.351 1.367-.085.534-.085 1.547 0 2.081a6.9 6.9 0 0 0 .65 2.04c.361.738.736 1.26 1.34 1.866a6.5 6.5 0 0 0 1.866 1.34 7 7 0 0 0 2.042.649c.534.084 1.548.084 2.083 0a7 7 0 0 0 2.041-.65 6.5 6.5 0 0 0 1.867-1.339 6.5 6.5 0 0 0 1.34-1.865c.241-.49.362-.81.493-1.304C13.96 8.61 14 8.251 14 7.49c0-.762-.041-1.12-.214-1.778a6.3 6.3 0 0 0-.493-1.304 6.5 6.5 0 0 0-1.34-1.865 6.5 6.5 0 0 0-1.867-1.34A6.8 6.8 0 0 0 8.058.56C7.655.497 6.756.467 6.373.505"/>
                                    <path fill="#fff" d="M10.673 4.482c.137.044.348.249.406.4a.76.76 0 0 1-.017.561c-.088.178-4.599 4.654-4.802 4.766a.66.66 0 0 1-.624-.003c-.116-.06-2.193-1.92-2.547-2.279a.68.68 0 0 1-.205-.506c.005-.414.31-.693.72-.666.1.008.22.033.267.06.074.036 1.458 1.282 1.907 1.712l.129.126 2.05-2.046c1.126-1.123 2.086-2.062 2.135-2.087a.7.7 0 0 1 .58-.038"/>
                                </svg>
                                ' . esc_html($feature) . '
                            </li>';
                        }

                echo'</ul>
                    </div>
                    <div class="nxt-ins-ext-img">
                        <img src="' . esc_url(TPGB_URL . 'dashboard/assets/images/install-nexter-extension.png') . '" alt="' . esc_attr__('Nexter Extension', 'the-plus-addons-for-block-editor') . '" />
                    </div>
                </div>
            </section>
        </div>';
    }

    /**
     * Theme Builder Render html
     * @since  1.0.0
     */
    public function nexter_blocks_builder_display() {
        echo '<div id="nexter-theme-builder"></div>';
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
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && 
			isset( $_POST['nonce'] ) && 
			!empty( $_POST['nonce'] ) && 
			wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce']) ), 'nexter_admin_nonce' ) 
		) {
			global $wpdb;
			$block_scan =[];
			
			if(isset($_POST['default_block']) && $_POST['default_block']=='false'){
				$this->block_listout();
				if(!empty($this->block_lists)){
					foreach($this->block_lists as $key => $block){
						$sql_posts = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_status IN ( {$this->get_post_statuses_sql()} ) AND post_content LIKE %s LIMIT 1", '%<!-- wp:tpgb/' . $wpdb->esc_like($key) . '%' );
                        $found_in_posts = $wpdb->get_var($sql_posts);
						
						$block_scan[$key]= $found_in_posts ? 1 : 0;
						if( ! $found_in_posts ){
							$sql_widgets = $wpdb->prepare( "SELECT option_id FROM {$wpdb->options} WHERE option_name = %s AND option_value LIKE %s LIMIT 1", 'widget_block','%<!-- wp:tpgb/' . $wpdb->esc_like($key) . '%');
                            $found_in_widgets = $wpdb->get_var($sql_widgets);
							
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
								$sql_posts = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_status IN ( {$this->get_post_statuses_sql()} ) AND post_content LIKE %s LIMIT 1", '%<!-- wp:' . $wpdb->esc_like($core_key) . '%');
                                $found_in_posts = $wpdb->get_var($sql_posts);
								
								$block_scan[$pass_key]= $found_in_posts ? 1 : 0;
								if( ! $found_in_posts ){
									$sql_widgets = $wpdb->prepare( "SELECT option_id FROM {$wpdb->options} WHERE option_name = %s AND option_value LIKE %s LIMIT 1", 'widget_block', '%<!-- wp:' . $wpdb->esc_like($core_key) .'%' );
                                    $found_in_widgets = $wpdb->get_var($sql_widgets);
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
		if( defined('DOING_AJAX') && DOING_AJAX && isset( $_POST['nonce'] ) && !empty($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['nonce'] ) ), 'nexter_admin_nonce') ){
			
			if(!isset($_POST['blocks']) || empty($_POST['blocks'])){
				echo 0;
				exit;
			}
			$default_block = (isset($_POST['default_block']) && $_POST['default_block']!='') ? sanitize_text_field( wp_unslash ($_POST['default_block'] ) ) : '';
			if(isset($default_block) && $default_block!='' && $default_block=='false'){
				$blocks = map_deep(wp_unslash(json_decode(stripslashes($_POST['blocks']), true)), 'sanitize_text_field');
				
				$action_page = 'tpgb_normal_blocks_opts';
				$all_block = get_option($action_page);
				$update_block = [];
				if(is_array($blocks)){
					foreach($blocks as $key => $val){
						if($val==1){
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

	/*
	 * Filter to Enable All Block For WDesignkit
	 * @since 4.0.14
	 */

	public function tpgb_blocks_enable_all_filter() {
		$this->block_listout();
		$default_value = array(
			'enable_normal_blocks' => array(),
			'tp_extra_option' => array()
		);
	
		$free_enable = !defined('TPGBP_VERSION');
	
		$process_blocks = function ($blocks, $key) use (&$default_value, $free_enable) {
			foreach ($blocks as $block_key => $block) {
				if (class_exists('Tpgb_Library') && method_exists('Tpgb_Library', 'remove_backend_dir_files')) {
					Tpgb_Library()->remove_backend_dir_files();
				}

				if (!$free_enable || ( isset($block['tag']) && $block['tag'] === 'free' )) {
					$default_value[$key][] = $block_key;
				}
			}
		};
	
		if (!empty($this->block_lists)) {
			$process_blocks($this->block_lists, 'enable_normal_blocks');
		}
		if (!empty($this->block_extra)) {
			$process_blocks($this->block_extra, 'tp_extra_option');
		}

		$option_exists = get_option('tpgb_normal_blocks_opts');
		return $option_exists === false ? add_option('tpgb_normal_blocks_opts', $default_value) : update_option('tpgb_normal_blocks_opts', $default_value);
	}

	/*
	 * Unused Disable Blocks Filter Function For WDesignkit
	 * @since 4.0.1
	 */

	public function tpgb_disable_unsed_block_filter_fun() {
		global $wpdb;
	
		$this->block_listout();
		$block_scan = [];
	
		if (!empty($this->block_lists)) {
			foreach ($this->block_lists as $key => $block) {
				$post_statuses = $this->get_post_statuses_sql();
				$like_key = '%<!-- wp:tpgb/' . $wpdb->esc_like($key) . '%';
	
				$sql_posts = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_status IN ($post_statuses) AND post_content LIKE %s LIMIT 1", $like_key );
				$found_in_posts = $wpdb->get_var($sql_posts);
	
				$block_scan[$key] = $found_in_posts ? 1 : 0;
	
				if (!$found_in_posts) {
					$sql_widgets = $wpdb->prepare( "SELECT option_id FROM {$wpdb->options} WHERE option_name = %s AND option_value LIKE %s LIMIT 1", 'widget_block', $like_key );
					$found_in_widgets = $wpdb->get_var($sql_widgets);
	
					$block_scan[$key] = $found_in_widgets ? 1 : 0;
				}
			}
	
			$action_page = 'tpgb_normal_blocks_opts';
			$all_block = get_option($action_page);
	
			if (is_array($block_scan)) {
				foreach ($block_scan as $key => $val) {
					if ($val == 1) {
						$update_block[] = $key;
					}
				}
				$all_block['enable_normal_blocks'] = map_deep(wp_unslash($update_block), 'sanitize_text_field');
				update_option($action_page, $all_block);
	
				if (class_exists('Tpgb_Library') && method_exists('Tpgb_Library', 'remove_backend_dir_files')) {
					Tpgb_Library()->remove_backend_dir_files();
				}
			}
		}
	}

    /*
	 * Wdesignkit Widgets Enable / Disabled Ajax
	 * @since 4.0.7
	 */
    public function nxt_wdk_widget_ajax_callback(){

        $response = apply_filters( 'nxt_wdk_widget_ajax_call', 'wdk_update_widget' );

        wp_send_json( $response );
        wp_die();
    }

    /*
	 * Wdesignkit Import Template Block List Merge
	 * @since 4.2.4
	 */

    public function nexter_block_list_merge_action( $blockList ){

        if( empty( $blockList ) ){
            return [ 'success' => true , 'message'  => 'Block Name Not Found.' , 'description' => 'Block Name Not Found.' ];
        }

        $option_key = 'tpgb_normal_blocks_opts';
        $all_block  = get_option( $option_key );

        if ( ! is_array( $all_block ) ) {
            $all_block = [];
        }

        if ( ! isset( $all_block['enable_normal_blocks'] ) || ! is_array( $all_block['enable_normal_blocks'] ) ) {
            $all_block['enable_normal_blocks'] = [];
        }

        $sani_blockList = map_deep( wp_unslash( $blockList ), 'sanitize_text_field' );

        $all_block['enable_normal_blocks'] = array_unique( array_merge( $all_block['enable_normal_blocks'], $sani_blockList ) );
        
        if ( in_array( 'tp-google-map', $sani_blockList, true ) ) {
			$connection_key  = 'tpgb_connection_data';
			$connection_opts = get_option( $connection_key );

			if ( ! is_array( $connection_opts ) ) {
				$connection_opts = [];
			}

			// Check if not set OR explicitly "disable"
			if ( ! isset( $connection_opts['gmap_api_switch'] ) || $connection_opts['gmap_api_switch'] == "disable" ) {
				$connection_opts['gmap_api_switch'] = 'enable';
				update_option( $connection_key, $connection_opts );
			}
		}

        update_option( $option_key, $all_block );
        return [ 'success' => true , 'message'  => 'success' , 'description' => 'success' ];
    }

    /*
    * Store User Data in Database From Onboarding Process
    * @since 4.2.4
    */
    public function nxt_block_boarding_store(){

		check_ajax_referer('nexter_admin_nonce', 'security');

		$tponbData = ( isset($_POST['boardingData']) && !empty($_POST['boardingData']) ) ? wp_unslash(json_decode(stripslashes($_POST['boardingData']), true)) : [];
    
		$userData = [];
		if( isset($tponbData) && !empty($tponbData) ){
            
			// $tpoUpdate = update_option('tpgb_onboarding_data' , $tponbData);
			if(isset($tponbData['tpgb_onboarding']) && $tponbData['tpgb_onboarding'] == true ){
                
				$userData['web_server'] = $_SERVER['SERVER_SOFTWARE'];

				// Memory Limit
				$userData['memory_limit'] = ini_get('memory_limit');

				// Memory Limit
				$userData['max_execution_time'] = ini_get('max_execution_time');

				// Php Version
				$userData['php_version'] = phpversion();

				// Wordpress Version
				$userData['wp_version'] = get_bloginfo( 'version' );

				// Active Theme
				$acthemeobj = wp_get_theme();
				if(  $acthemeobj->get( 'Name' ) !== null && !empty( $acthemeobj->get( 'Name' ) ) ){
					$userData['theme'] = $acthemeobj->get( 'Name' );
				}

				// Active Plugin Name
				$actPlugin = [];
				$actplu = get_option('active_plugins');
				if ( ! function_exists( 'get_plugins' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				$plugins=get_plugins();
				foreach ($actplu as $p){
					if(isset($plugins[$p])){
						$actPlugin[] = $plugins[$p]['Name'];
					}
				}

				$userData['plugin'] = json_encode($actPlugin);

                // No Of TPAG Block Used
				$get_blocks_list = get_option('tpgb_normal_blocks_opts');

				if(isset($get_blocks_list) && !empty($get_blocks_list) && isset($get_blocks_list['enable_normal_blocks']) && !empty($get_blocks_list['enable_normal_blocks']) ){
					$userData['no_block']  = count($get_blocks_list['enable_normal_blocks']);
					$userData['used_blocks'] = json_encode($get_blocks_list['enable_normal_blocks']);
 				}

				// User Email
				$userData['email'] = get_option('admin_email');

				// Site Url
				$userData['site_url'] = get_option('siteurl');

				// Site Url
				$userData['site_language'] = get_bloginfo("language");

                // Nexter Block Version
                $userData['nexter_block_version'] = TPGB_VERSION;

				$response = wp_remote_post('https://api.posimyth.com/wp-json/tpgb/v2/tpgb_store_user_data' , array(
					'method' => 'POST',
					'body' => json_encode($userData)
				) );

				if (is_wp_error($response)) {
					wp_send_json([ 'onBoarding' => false ]);
				} else {
					$StatusOne = wp_remote_retrieve_response_code($response);
					if($StatusOne == 200){
						$GetDataOne = wp_remote_retrieve_body($response);
						$GetDataOne = (array) json_decode(json_decode($GetDataOne, true));
						if(isset($GetDataOne['success']) && !empty($GetDataOne['success']) ){
                            add_option( 'nxt_onboarding_done' , true );
                            wp_send_json([ 'onBoarding' => true ]);
						}
					}
				}
			}else{
                add_option( 'nxt_onboarding_done' , true );
                wp_send_json([ 'onBoarding' => true ]);
            }
		}
		exit;
	}

    /*
    * Template Get ajax call
    * @since 4.6.0
    */
    public function nexter_temp_api_call() {

        check_ajax_referer( 'nexter_admin_nonce', 'nexter_nonce' );

        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'content' => __( 'Insufficient permissions.', 'the-plus-addons-for-block-editor' ) ) );
            wp_die();
        }

        $method  = isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : 'POST';
        $api_url = isset( $_POST['api_url'] ) ? esc_url_raw( wp_unslash( $_POST['api_url'] ) ) : '';
        $body    = isset( $_POST['url_body'] ) ? json_decode( wp_unslash( $_POST['url_body'] ), true ) : array();

        $args = array(
            'method'  => $method,
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        );

        if ( ! empty( $body ) ) {
            $args['body'] = wp_json_encode( $body );
        }

        // Make the request based on method
        if ( 'POST' === $method ) {
            $response = wp_remote_post( $api_url, $args );
        } elseif ( 'GET' === $method ) {
            $response = wp_remote_get( $api_url, $args );
        } else {
            wp_send_json_error( array(
                'HTTP_CODE' => 400,
                'error' => 'Invalid HTTP method'
            ) );
            wp_die();
        }

        $statuscode = wp_remote_retrieve_response_code( $response );
        $getdataone = wp_remote_retrieve_body( $response );
        
        $response_data = json_decode( $getdataone, true );

        $statuscode_array = array( 'HTTP_CODE' => $statuscode );

        // Merge status code with response data
        if ( is_array( $response_data ) ) {
            $final = array_merge( $statuscode_array, $response_data );
        } else {
            $final = array_merge( $statuscode_array, array( 'data' => $response_data ) );
        }

        wp_send_json( $final );
        wp_die();
    }
}
// Get it started
$Tpgb_Gutenberg_Settings_Options = new Tpgb_Gutenberg_Settings_Options();
$Tpgb_Gutenberg_Settings_Options->hooks();