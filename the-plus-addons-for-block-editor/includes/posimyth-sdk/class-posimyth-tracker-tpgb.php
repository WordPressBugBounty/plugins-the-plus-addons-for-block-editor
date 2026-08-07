<?php
/**
 * POSIMYTH Analytics tracker — Nexter Blocks.
 *
 * Requires class-posimyth-tracker-base.php to be loaded first.
 * Boot from the main plugin file:  Posimyth_Tracker_TPGB::init();
 *
 * Verified option keys (Nexter Blocks 5.0.2):
 *   - version constant : TPGB_VERSION
 *   - pro detection    : TPGBP_VERSION
 *   - license option   : tpgb_activate['tpgb_activate_key']
 *   - enabled blocks   : tpgb_normal_blocks_opts['enable_normal_blocks'] (numeric list of slugs)
 *   - block usage      : scanned from post_content by the shared base ('tpgb/' namespace)
 *
 * Consent is deliberately the SAME option Nexter Extension uses
 * (posimyth_nexter_share_analytics), so one opt-in covers the whole Nexter suite. The data itself
 * stays separate: this subclass reports under its own plugin_slug, so the hub records Nexter Blocks
 * and Nexter Extension as distinct products.
 *
 * @package POSIMYTH\Analytics\SDK
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Self-load the shared base so this subclass defines correctly regardless of require order.
if ( ! class_exists( 'Posimyth_Tracker_Base' ) ) {
	require_once __DIR__ . '/class-posimyth-tracker-base.php';
}

if ( ! class_exists( 'Posimyth_Tracker_TPGB' ) && class_exists( 'Posimyth_Tracker_Base' ) ) {

	/**
	 * Nexter Blocks' tracker: supplies the product-specific identity, version, Pro state and
	 * block usage that the shared base assembles into a payload.
	 */
	class Posimyth_Tracker_TPGB extends Posimyth_Tracker_Base {

		/**
		 * Suite-wide consent option — intentionally identical to Nexter Extension's.
		 * One "Allow" (onboarding checkbox / dashboard toggle / inline notice) enables reporting for
		 * every Nexter product. Do NOT give this plugin its own opt-in key.
		 */
		const OPT_IN_OPTION = 'posimyth_nexter_share_analytics';

		/**
		 * Short internal id used for this product's own options (install time, usage cache).
		 *
		 * @return string
		 */
		protected static function id(): string {
			return 'tpgb';
		}

		/**
		 * Plugin slug reported to the hub.
		 *
		 * @return string
		 */
		protected static function slug(): string {
			return 'the-plus-addons-for-block-editor';
		}

		/**
		 * Name shown in WordPress's Privacy Policy suggestions.
		 *
		 * @return string
		 */
		protected static function display_name(): string {
			return 'Nexter Blocks';
		}

		/**
		 * Option holding the suite-wide sharing consent, shared with Nexter Extension.
		 *
		 * @return string
		 */
		protected static function opt_in_option(): string {
			return self::OPT_IN_OPTION;
		}

		/**
		 * Currently installed version of this plugin.
		 *
		 * @return string
		 */
		protected static function version(): string {
			return defined( 'TPGB_VERSION' ) ? TPGB_VERSION : '';
		}

		/**
		 * Whether the Pro build is present.
		 *
		 * @return bool
		 */
		protected static function is_pro(): bool {
			return defined( 'TPGBP_VERSION' );
		}

		/**
		 * License status/plan for the Pro build.
		 *
		 * Nexter Blocks stores only the key itself in `tpgb_activate`, so the status is derived from
		 * its presence rather than an API response array (unlike Nexter Extension's EDD payload).
		 *
		 * @return array{status:string, plan:string}
		 */
		protected static function license(): array {
			if ( ! self::is_pro() ) {
				return array(
					'status' => '',
					'plan'   => '',
				);
			}

			$raw = get_option( 'tpgb_activate', array() );
			$key = is_array( $raw ) && ! empty( $raw['tpgb_activate_key'] ) ? (string) $raw['tpgb_activate_key'] : '';

			return array(
				// Never send the licence key itself — only whether one is present.
				'status' => '' !== $key ? 'valid' : '',
				'plan'   => '',
			);
		}

		/**
		 * Which Nexter Blocks blocks are ENABLED in settings.
		 *
		 * `tpgb_normal_blocks_opts['enable_normal_blocks']` is a numeric list of enabled block slugs
		 * (e.g. 'tp-accordion'), not a key => bool map, so it is flipped into slug => true here to
		 * match the base class contract (and the hub's enabled_widgets column).
		 *
		 * @return array<string,bool>
		 */
		protected static function enabled_features(): array {
			$opts = get_option( 'tpgb_normal_blocks_opts', array() );
			if ( ! is_array( $opts ) || empty( $opts['enable_normal_blocks'] ) ) {
				return array();
			}

			$enabled = (array) $opts['enable_normal_blocks'];
			$map     = array();
			foreach ( $enabled as $key => $value ) {
				// Numeric list => the value is the block slug. Assoc map => the key is the slug.
				$slug = is_int( $key ) ? $value : $key;
				if ( ! is_string( $slug ) || '' === $slug ) {
					continue;
				}
				$map[ sanitize_key( $slug ) ] = is_int( $key ) ? true : (bool) $value;
			}

			return $map;
		}

		/**
		 * Real block usage counted from published content.
		 *
		 * Uses the shared, bounded scanner in the base class (capped by the
		 * `posimyth_scan_post_cap` filter, batched, and only ever run on the weekly cron —
		 * activate/deactivate read a cached copy so the admin never waits on a scan).
		 *
		 * @return array<string,int>
		 */
		protected static function used_features(): array {
			return self::scan_gutenberg_blocks( 'tpgb/' );
		}
	}
}
