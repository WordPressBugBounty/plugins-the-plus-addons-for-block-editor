<?php
/**
 * Bootstraps Nexter Blocks MCP abilities and their category.
 *
 * @package The_Plus_Addons_For_Block_Editor
 * @since   1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Nexter Blocks ability category and loads each ability file.
 */
class Tpgb_MCP_Abilities {

	/**
	 * Singleton instance.
	 *
	 * @var Tpgb_MCP_Abilities|null
	 */
	private static $instance;

	/**
	 * Retrieve the singleton instance.
	 *
	 * @return Tpgb_MCP_Abilities
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook ability registration onto the WP Abilities API actions.
	 */
	public function __construct() {
		// Only register if the toggle is ON.
		$mcp_option        = get_option( 'tpgb_connection_data', array() );
		$mcp_ability_value = isset( $mcp_option['nxt_enable_mcp_abilities'] ) ? $mcp_option['nxt_enable_mcp_abilities'] : 'enable';
		if ( 'enable' !== $mcp_ability_value ) {
			return;
		}

		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_categories' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Register the "nexter-blocks" ability category.
	 */
	public function register_categories(): void {
		wp_register_ability_category(
			'nexter-blocks',
			array(
				'label'       => __( 'Nexter Blocks', 'the-plus-addons-for-block-editor' ),
				'description' => __( 'Nexter Blocks Gutenberg abilities.', 'the-plus-addons-for-block-editor' ),
			)
		);
	}

	/**
	 * Load every ability file so each can call wp_register_ability().
	 */
	public function register_abilities(): void {
		// Load helpers.
		require_once TPGB_PATH . 'includes/abilities/helpers.php';

		// Skill / guide abilities (procedural workflows for MCP clients).
		// get-performance-skill is loaded FIRST so its ability is registered
		// before everything else and clients are nudged to call it first —
		// nexter-blocks/get-image-to-page-skill's description explicitly tells
		// clients the performance skill must already have run for the session.
		require_once TPGB_PATH . 'includes/abilities/get-performance-skill.php';
		require_once TPGB_PATH . 'includes/abilities/get-image-to-page-skill.php';
		require_once TPGB_PATH . 'includes/abilities/get-doc-creator-skill.php';
		require_once TPGB_PATH . 'includes/abilities/inspect-page.php';

		// Load individual abilities.
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-accordion.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-blockquote.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-heading.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-breadcrumbs.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-button.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-button-core.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-button-preset-crud.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-code-highlighter.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-container.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-container-inner.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-countdown.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-creative-image.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-dark-mode.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-data-table.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-draw-svg.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-flipbox.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-form-block.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-form-fields.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-google-map.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-heading-title.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-hovercard.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-icon-box.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-image.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-infobox.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-interactive-circle-info.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-messagebox.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-navigation-builder.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-number-counter.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-pricing-list.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-pricing-table.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-pro-paragraph.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-progress-bar.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-post-author.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-post-comment.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-post-content.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-post-image.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-post-listing.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-post-meta.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-post-title.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-progress-tracker.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-search-bar.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-site-logo.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-smooth-scroll.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-social-embed.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-social-icons.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-stylist-list.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-switcher.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-tabs-tours.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-team-listing.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-testimonials.php';
		require_once TPGB_PATH . 'includes/abilities/add-tpgb-video.php';
	}
}
