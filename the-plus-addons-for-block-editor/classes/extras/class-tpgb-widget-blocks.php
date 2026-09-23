<?php
/**
 * Nexter Blocks placed in block based Widget Areas.
 *
 * The asset pipeline is keyed to a post id, but a block widget lives in the
 * `widget_block` option and has no post of its own. This class makes widget
 * blocks a second contributor to the existing per page element pipeline and
 * gives them one shared dynamic css file.
 *
 * Front end cost when nothing is relevant: one autoloaded option read plus a
 * single preg_match per block widget. Everything heavier (sidebar walk, block
 * parsing, css generation, the non autoloaded option read) only runs when a
 * rendered widget area really holds Nexter markup.
 *
 * @since   5.0.7
 * @package TPGB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Tpgb_Widget_Blocks.
 *
 * @since 5.0.7
 */
class Tpgb_Widget_Blocks {

	/**
	 * Option holding the cached widget asset payload, not autoloaded.
	 */
	const OPTION = 'tpgb_widget_blocks_assets';

	/**
	 * Dynamic css file. The `plus-css-` prefix makes "clear all cache" keep it
	 * and "clear dynamic styles" drop it, same as plus-css-{post_id}.css.
	 */
	const CSS = 'plus-css-widgets.css';

	/**
	 * Library bundle used on the first request after a widget save, while the
	 * page's own combined bundle is still being rebuilt in the footer.
	 */
	const BUNDLE = 'theplus-preload-widgets.min.css';

	/**
	 * Widget markup worth parsing: our own blocks, or a wrapper that can resolve
	 * to one. Anything else is skipped before a single block is parsed.
	 */
	const RELEVANT = '~<!--\s+wp:(?:tpgb/|block\s|pattern\s|template-part\s)~';

	/**
	 * A reference to an instance of this class.
	 *
	 * @var Tpgb_Widget_Blocks|null
	 */
	private static $instance = null;

	/**
	 * Relevant block widget instance ids, null until scanned.
	 *
	 * @var array|null
	 */
	private $ids = null;

	/**
	 * Sidebar ids that hold those widgets, as a lookup.
	 *
	 * @var array
	 */
	private $sidebars = array();

	/**
	 * Whether one of those sidebars was actually rendered this request.
	 *
	 * @var bool
	 */
	private $rendered = false;

	/**
	 * Concatenated markup of those widgets.
	 *
	 * @var string
	 */
	private $markup = '';

	/**
	 * Parsed widget blocks, memoised.
	 *
	 * @var array|null
	 */
	private $parsed = null;

	/**
	 * Resolved asset payload, memoised.
	 *
	 * @var array|null
	 */
	private $assets = null;

	/**
	 * Whether the widget styles were already enqueued this request.
	 *
	 * @var bool
	 */
	private $enqueued = false;

	/**
	 * Guards the flush from repeating within one request.
	 *
	 * @var bool
	 */
	private static $flushed = false;

	/**
	 * Initiator
	 *
	 * @return Tpgb_Widget_Blocks
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		// Widget saves from the admin, the customizer, the REST controller and
		// wp-cli all write through update_option().
		add_action( 'update_option_widget_block', array( $this, 'flush' ) );
		add_action( 'update_option_sidebars_widgets', array( $this, 'flush' ) );

		if ( ! is_admin() ) {
			// Priority 11, init_post_request_data() and header_init_css_js() both
			// run on `wp` at priority 10 and must have set plus_uid first.
			add_action( 'wp', array( $this, 'discover' ), 11 );
			add_action( 'dynamic_sidebar_before', array( $this, 'sidebar_rendered' ), 10, 2 );
			add_action( 'wp_footer', array( $this, 'remember_sidebar' ), 999 );
		}
	}

	/**
	 * Collect the markup of every rendered widget area that holds Nexter blocks.
	 *
	 * Ordered cheapest guard first so an irrelevant request stops at the top.
	 */
	private function scan() {
		if ( null !== $this->ids ) {
			return;
		}

		$this->ids = array();

		// Autoloaded, so this costs nothing on a site without block widgets.
		$widgets = get_option( 'widget_block' );
		if ( empty( $widgets ) || ! is_array( $widgets ) ) {
			return;
		}

		/*
		 * Under a block theme core re-registers the previous classic theme's
		 * sidebars (_wp_block_theme_register_classic_sidebars) purely to keep the
		 * widgets editable, without enabling "widgets" theme support and without
		 * any template rendering them. Missing support is therefore the signal
		 * that nothing on the front end calls dynamic_sidebar(). A block theme
		 * where a plugin really did register_sidebar() gains the support and
		 * still goes through the walk below.
		 */
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() && ! current_theme_supports( 'widgets' ) ) {
			return;
		}

		global $wp_registered_sidebars;

		foreach ( (array) wp_get_sidebars_widgets() as $sidebar => $assigned ) {
			// Empty means inactive, an unregistered sidebar is not rendered by the
			// current theme, and the inactive drawer must never load assets.
			if ( 'wp_inactive_widgets' === $sidebar || empty( $assigned ) || ! is_array( $assigned ) || empty( $wp_registered_sidebars[ $sidebar ] ) ) {
				continue;
			}

			foreach ( $assigned as $widget_id ) {
				if ( ! preg_match( '/^block-(\d+)$/', (string) $widget_id, $match ) ) {
					continue;
				}

				$content = isset( $widgets[ $match[1] ]['content'] ) ? $widgets[ $match[1] ]['content'] : '';
				if ( '' === $content || ! preg_match( self::RELEVANT, $content ) ) {
					continue;
				}

				$this->ids[]                = (int) $match[1];
				$this->markup              .= $content;
				$this->sidebars[ $sidebar ] = true;
			}
		}
	}

	/**
	 * Whether the current template is expected to render one of our sidebars.
	 *
	 * Themes decide the layout per page (Blocksy alone has a global setting plus
	 * a per post override) and dynamic_sidebar() only runs long after wp_head,
	 * so the answer is learned from the previous view of this same object and
	 * stored next to the _block_css data that is already loaded for it. Unknown
	 * means yes, so a page is never unstyled on its first view, and a wrong
	 * answer self corrects within one request through sidebar_rendered().
	 *
	 * @return bool
	 */
	private function expects_sidebar() {
		$post_id = $this->current_id();
		if ( '' === $post_id ) {
			return true;
		}

		// Same meta row enqueue_frontend_load() already reads, so this is free.
		$flag = tpgb_library()->get_posts_metadata( $post_id, '_block_css', 'sidebar', tpgb_library()->plus_uid . '_sidebar' );

		return ( '0' !== (string) $flag );
	}

	/**
	 * Post or term id of the current request, as the library resolved it.
	 *
	 * Derived from the public plus_uid, which init_post_request_data() builds as
	 * "theplus-{post|term}-{id}" from get_post_type_post_id(). Reusing it keeps
	 * the queried object logic in one place and lands the flag in the same
	 * _block_css bucket the request already loaded.
	 *
	 * @return string The id, or an empty string when the request has none.
	 */
	private function current_id() {
		if ( ! function_exists( 'tpgb_library' ) ) {
			return '';
		}

		$library = tpgb_library();
		if ( empty( $library ) || empty( $library->plus_uid ) ) {
			return '';
		}

		// Both prefixes are the same length, so one offset covers them.
		foreach ( array( 'theplus-post-', 'theplus-term-' ) as $prefix ) {
			if ( 0 === strpos( $library->plus_uid, $prefix ) ) {
				return (string) substr( $library->plus_uid, strlen( $prefix ) );
			}
		}

		return '';
	}

	/**
	 * Core reached one of our sidebars, so this template does render it.
	 *
	 * @param string $index       The sidebar id.
	 * @param bool   $has_widgets Whether the sidebar has widgets.
	 */
	public function sidebar_rendered( $index, $has_widgets ) {
		if ( $this->rendered || ! $has_widgets ) {
			return;
		}

		$this->scan();
		if ( empty( $this->sidebars[ $index ] ) ) {
			return;
		}

		$this->rendered = true;

		// A stale "no sidebar here" answer would have skipped the styles in the
		// head, so load them now rather than leave the widget unstyled.
		$this->enqueue_widget_css( true );
	}

	/**
	 * Store what this request observed, so the next view can decide in the head.
	 */
	public function remember_sidebar() {
		if ( empty( $this->ids ) ) {
			return;
		}

		$post_id = $this->current_id();
		if ( '' === $post_id || tpgb_library()->is_preview_mode() ) {
			return;
		}

		$library = tpgb_library();
		$flag    = $this->rendered ? '1' : '0';
		$stored  = (string) $library->get_posts_metadata( $post_id, '_block_css', 'sidebar', $library->plus_uid . '_sidebar' );

		if ( $stored !== $flag ) {
			$library->update_posts_metadata( $post_id, '_block_css', 'sidebar', $flag );
		}
	}

	/**
	 * Widget element list for the page pipeline, empty when this template does
	 * not render a sidebar holding Nexter blocks.
	 *
	 * @return array
	 */
	public function get_elements() {
		$this->scan();
		if ( empty( $this->ids ) || ! $this->expects_sidebar() ) {
			return array();
		}

		$assets = $this->get_assets();

		return ! empty( $assets['elements'] ) ? $assets['elements'] : array();
	}

	/**
	 * Cached asset payload for the current widget state.
	 *
	 * Rebuilt only when the signature changes, so a warm request costs one extra
	 * option read and one md5().
	 *
	 * @return array
	 */
	public function get_assets() {
		if ( null !== $this->assets ) {
			return $this->assets;
		}

		$this->assets = array(
			'version'   => 0,
			'elements'  => array(),
			'refs'      => array(),
			'font_link' => '',
			'has_css'   => false,
		);

		$this->scan();
		if ( empty( $this->ids ) ) {
			return $this->assets;
		}

		$signature = md5( $this->markup . '|' . implode( ',', $this->ids ) . '|' . (string) get_option( 'tpgb_save_updated_at' ) );
		$cached    = get_option( self::OPTION );

		if ( ! empty( $cached['signature'] ) && $cached['signature'] === $signature ) {
			$this->assets = wp_parse_args( $cached, $this->assets );
		} else {
			$this->assets = $this->build( $signature );
		}

		return $this->assets;
	}

	/**
	 * Parse the widget markup, resolve the element list and write both files.
	 *
	 * @param string $signature The current widget signature.
	 * @return array
	 */
	private function build( $signature ) {
		$assets = $this->assets;

		$assets['signature'] = $signature;
		$assets['version']   = time();

		if ( ! class_exists( 'Tpgb_Get_Blocks' ) ) {
			return $assets;
		}

		$this->parsed = parse_blocks( $this->markup );

		// Reuse the existing walker, it recurses innerBlocks (so core/widget-group
		// is covered) and resolves core/block, core/template-part and core/pattern.
		$get_blocks = new Tpgb_Get_Blocks();
		$refs       = $get_blocks->block_reference_id( $this->parsed );

		$assets['elements'] = array_values( $get_blocks->get_blocks_elements( $get_blocks->transient_blocks ) );
		$assets['refs']     = ! empty( $refs ) ? array_values( array_unique( (array) $refs, SORT_REGULAR ) ) : array();

		$this->write_bundle( $assets['elements'] );

		$css = $this->write_css();
		if ( ! empty( $css ) ) {
			$assets['has_css']   = true;
			$assets['font_link'] = isset( $css['font_link'] ) ? $css['font_link'] : '';
		}

		update_option( self::OPTION, $assets, false );

		return $assets;
	}

	/**
	 * Generate plus-css-widgets.css.
	 *
	 * @return array|false The split css payload, or false when there is none.
	 */
	private function write_css() {
		if ( '' === $this->markup || ! class_exists( 'Tpgb_Generate_Blocks_Css' ) || ! class_exists( 'Tpgb_Core_Init_Blocks' ) ) {
			return false;
		}

		// Parsed only during a rebuild, so the heal path has to fill it in.
		if ( null === $this->parsed ) {
			$this->parsed = parse_blocks( $this->markup );
		}

		if ( empty( $this->parsed ) ) {
			return false;
		}

		$generate  = new Tpgb_Generate_Blocks_Css();
		$block_css = $generate->generate_dynamic_css_blocks( $this->parsed, false );

		$import_css = ! empty( $block_css ) ? Tpgb_Core_Init_Blocks::get_instance()->exclude_gfont_block_css( $block_css ) : array();

		if ( empty( $import_css['css'] ) ) {
			$this->delete( self::CSS );
			return false;
		}

		global $wp_filesystem;
		if ( ! $wp_filesystem && ! function_exists( 'WP_Filesystem' ) ) {
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
		}

		$upload_dir = wp_get_upload_dir();
		WP_Filesystem( false, $upload_dir['basedir'], true );

		if ( ! $wp_filesystem ) {
			return false;
		}

		$dir = TPGB_ASSET_PATH . DIRECTORY_SEPARATOR;
		if ( ! $wp_filesystem->is_dir( $dir ) ) {
			$wp_filesystem->mkdir( $dir );
		}

		return $wp_filesystem->put_contents( $dir . self::CSS, $import_css['css'] ) ? $import_css : false;
	}

	/**
	 * Generate the widget library bundle, combined caching mode only.
	 *
	 * @param array $elements The widget element list.
	 */
	private function write_bundle( $elements ) {
		if ( empty( $elements ) || ! function_exists( 'tpgb_library' ) ) {
			return;
		}

		$library = tpgb_library();
		if ( empty( $library ) || false !== $library->get_caching_option() ) {
			return;
		}

		$this->delete( self::BUNDLE );

		// Same call the post preload uses, "false" keeps the shared common css out.
		$library->plus_generate_scripts( $elements, 'theplus-preload-widgets', array( 'css' ), false );
	}

	/**
	 * Absolute path of a generated widget file.
	 *
	 * @param string $file The file name.
	 * @return string
	 */
	private function path( $file ) {
		return TPGB_ASSET_PATH . DIRECTORY_SEPARATOR . $file;
	}

	/**
	 * Remove a generated widget file.
	 *
	 * @param string $file The file name.
	 */
	private function delete( $file ) {
		if ( file_exists( $this->path( $file ) ) ) {
			wp_delete_file( $this->path( $file ) );
		}
	}

	/**
	 * Feed the widget elements into the page pipeline.
	 *
	 * Runs on `wp`, i.e. before wp_enqueue_scripts, so the assets it triggers
	 * still land in the head instead of the footer.
	 */
	public function discover() {
		if ( is_feed() || is_embed() || is_trackback() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ! function_exists( 'tpgb_library' ) ) {
			return;
		}

		$this->scan();
		if ( empty( $this->ids ) ) {
			return;
		}

		$library = tpgb_library();
		if ( empty( $library ) || $library->is_background_running() || $library->is_preview_mode() ) {
			return;
		}

		// No sidebar on this template means no widget elements in its bundle.
		if ( ! $this->expects_sidebar() ) {
			return;
		}

		$assets = $this->get_assets();

		if ( ! empty( $assets['elements'] ) ) {
			$library->transient_blocks = array_merge( $library->transient_blocks, $assets['elements'] );
		}

		if ( ! empty( $assets['refs'] ) ) {
			$post_type = ( is_singular() ? 'post' : 'term' );
			foreach ( $assets['refs'] as $ref ) {
				foreach ( (array) $ref as $ref_id ) {
					if ( empty( $ref_id ) || ! is_numeric( $ref_id ) ) {
						continue;
					}
					$library->header_init_css_js( $post_type, $ref_id );
					$library->enqueue_dynamic_css_post_id( $ref_id );
				}
			}
		}
	}

	/**
	 * Enqueue the widget styles.
	 *
	 * Idempotent, it is called from both enqueue_load_block_css_js() and
	 * enqueue_frontend_load() because either one can be short circuited.
	 *
	 * @param bool $force Skip the "does this template render a sidebar" gate,
	 *                    used once the sidebar has provably been reached.
	 */
	public function enqueue_widget_css( $force = false ) {
		if ( $this->enqueued || is_admin() ) {
			return;
		}

		$this->scan();
		if ( empty( $this->ids ) ) {
			return;
		}

		if ( ! $force && ! $this->expects_sidebar() ) {
			return;
		}

		$assets = $this->get_assets();
		if ( empty( $assets['elements'] ) ) {
			return;
		}

		$this->enqueued = true;

		$this->enqueue_bundle( $assets['elements'] );

		if ( empty( $assets['has_css'] ) ) {
			return;
		}

		if ( ! file_exists( $this->path( self::CSS ) ) && ! $this->write_css() ) {
			// Heals a "clear dynamic styles" run.
			return;
		}

		if ( ! empty( $assets['font_link'] ) && class_exists( 'Tpgb_Core_Init_Blocks' ) ) {
			Tpgb_Core_Init_Blocks::get_instance()->tpgb_load_google_fonts( 'widgets', $assets['font_link'] );
		}

		// wp_enqueue_style() silently drops a style whose dependency is not
		// registered, and on a page with no Nexter blocks of its own the front
		// css handle does not exist yet.
		$dependency = ( wp_style_is( 'tpgb-plus-block-front-css', 'registered' ) || wp_style_is( 'tpgb-plus-block-front-css', 'enqueued' ) )
			? array( 'tpgb-plus-block-front-css' )
			: array();

		wp_enqueue_style( 'plus-post-widgets', esc_url( TPGB_ASSET_URL . '/' . self::CSS ), $dependency, $assets['version'] );
	}

	/**
	 * Enqueue the widget library bundle while the page's own combined bundle is
	 * still stale. Once that bundle is current it already carries the widget
	 * elements, because enqueue_frontend_load() merges them in before the
	 * rebuild, so this file is not loaded any more.
	 *
	 * @param array $elements The widget element list.
	 */
	private function enqueue_bundle( $elements ) {
		$library = tpgb_library();
		if ( empty( $library ) || false !== $library->get_caching_option() || empty( $library->plus_uid ) ) {
			return;
		}

		if ( empty( $library->requires_update ) && file_exists( $this->path( $library->plus_uid . '.min.css' ) ) ) {
			return;
		}

		if ( ! is_readable( $this->path( self::BUNDLE ) ) ) {
			// Heals a "clear all cache" run.
			$this->write_bundle( $elements );
			if ( ! is_readable( $this->path( self::BUNDLE ) ) ) {
				return;
			}
		}

		$css_url = TPGB_ASSET_URL . '/' . self::BUNDLE;

		if ( wp_style_is( 'tpgb-plus-block-front-css', 'registered' ) || wp_style_is( 'tpgb-plus-block-front-css', 'enqueued' ) ) {
			wp_enqueue_style( 'tpgb-plus-widgets', esc_url( $css_url ), array( 'tpgb-plus-block-front-css' ), TPGB_VERSION );
			return;
		}

		// Nothing else claimed the primary handle on this request, so this file
		// becomes the base stylesheet, same as the post preload does.
		$global_css = get_option( '_tpgb_global_css' );
		$dependency = ( false !== $global_css ) ? array( 'plus-global' ) : array();

		wp_enqueue_style( 'tpgb-common', esc_url( TPGB_URL . 'assets/css/main/general/tpgb-common.css' ), $dependency, TPGB_VERSION );
		wp_enqueue_style( 'tpgb-plus-block-front-css', esc_url( $css_url ), array( 'tpgb-common' ), TPGB_VERSION );
	}

	/**
	 * Invalidate every cached widget asset after a widget area is saved.
	 *
	 * The global stamp bump is what makes each page rebuild its own bundle with
	 * the widget elements included, same as a non post/page save already does.
	 */
	public function flush() {
		if ( self::$flushed ) {
			return;
		}
		self::$flushed = true;

		$this->ids    = null;
		$this->markup = '';
		$this->parsed = null;
		$this->assets = null;

		delete_option( self::OPTION );
		$this->delete( self::CSS );
		$this->delete( self::BUNDLE );

		if ( function_exists( 'tpgb_library' ) ) {
			$library = tpgb_library();
			if ( ! empty( $library ) && is_callable( array( $library, 'update_save_updated_at' ) ) ) {
				$library->update_save_updated_at();
			}
		}
	}
}

Tpgb_Widget_Blocks::instance();
