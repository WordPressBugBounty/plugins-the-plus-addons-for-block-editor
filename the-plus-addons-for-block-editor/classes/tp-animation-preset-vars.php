<?php // phpcs:ignore WordPress.Files.FileName -- filename mirrors its JS counterpart src/helpers/animation-preset-vars.js.
/**
 * Animation Preset → CSS Variables (PHP mirror)
 * -----------------------------------------------------------------------------
 * Mirrors src/helpers/animation-preset-vars.js. Emits one :root {} declaration
 * per animation from the global preset card's "GSAP Scroll" repeater, using
 * the format:
 *
 *   --tpgb-anim-{slug}-{field}: value;
 *
 * Any change to ANIMATION_FIELDS or the slug scheme must be made in BOTH
 * this file and the JS emitter. The animation data itself is consumed by
 * GSAP at runtime from `data-nxt-gsap-scroll` — these CSS vars exist only
 * so the nexter-global-color viewer can list saved animations.
 *
 * @package TPGB
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Tpgb_Animation_Preset_Vars' ) ) {

	/**
	 * Animation Preset → CSS Variables (PHP mirror).
	 *
	 * Mirrors the animation-preset-vars.js emitter so the editor and the
	 * frontend produce identical `--tpgb-anim-*` custom properties.
	 */
	class Tpgb_Animation_Preset_Vars {

		/**
		 * Animation fields emitted as CSS variables.
		 *
		 * Keep in sync with ANIMATION_FIELDS in animation-preset-vars.js.
		 *
		 * @var array
		 */
		public static $animation_fields = array(
			'animLoad',
			'animDelay',
			'animDuration',
			'animOffset',
			'animDirection',
			'animRepeat',
			'animStagger',
			'animEase',
			'animStyle',
			'animOnce',
			'animScrub',
			'animOnMobile',
			'animPerformance',
		);

		/**
		 * Request-scoped memoization of the resolved animRep array.
		 *
		 * @var array|null
		 */
		private static $animrep_cache = null;

		/**
		 * Read the active animation list.
		 *
		 * Resolution order mirrors the JS helper: prefer presets[active].animRep,
		 * fall back to the flat top-level animRep for pre-rework data.
		 * `tpgb_global_options` is autoloaded, so repeated calls within a request
		 * don't hit SQL.
		 *
		 * @return array Active animation list.
		 */
		public static function get_active_animrep() {
			if ( null !== self::$animrep_cache ) {
				return self::$animrep_cache;
			}

			$raw = get_option( 'tpgb_global_options', '' );
			if ( is_array( $raw ) ) {
				$dec = $raw;
			} elseif ( is_string( $raw ) && '' !== $raw ) {
				$decoded = json_decode( $raw, true );
				$dec     = is_array( $decoded ) ? $decoded : array();
			} else {
				$dec = array();
			}

			if ( is_array( $dec ) && isset( $dec['active'] ) && isset( $dec['presets'][ $dec['active'] ]['animRep'] ) && is_array( $dec['presets'][ $dec['active'] ]['animRep'] ) ) {
				self::$animrep_cache = $dec['presets'][ $dec['active'] ]['animRep'];
				return self::$animrep_cache;
			}

			if ( is_array( $dec ) && isset( $dec['animRep'] ) && is_array( $dec['animRep'] ) ) {
				self::$animrep_cache = $dec['animRep'];
				return self::$animrep_cache;
			}

			self::$animrep_cache = array();
			return self::$animrep_cache;
		}

		/**
		 * Clear the cache.
		 *
		 * Use after the option is written within the same PHP request so
		 * subsequent reads see fresh data.
		 *
		 * @return void
		 */
		public static function invalidate_cache() {
			self::$animrep_cache = null;
		}

		/**
		 * Convert an animation name to its slug.
		 *
		 * PHP mirror of animSlug() in the JS helper. Returns '' when the
		 * slug would be empty so callers can skip emission.
		 *
		 * @param string $anim_name Animation display name.
		 * @return string Slug prefixed with 'anim-', or '' when empty.
		 */
		public static function anim_slug( $anim_name ) {
			if ( empty( $anim_name ) ) {
				return '';
			}
			$slug = strtolower( trim( (string) $anim_name ) );
			$slug = preg_replace( '/[^a-z0-9]+/', '-', $slug );
			$slug = trim( (string) $slug, '-' );
			return ( '' === $slug ) ? '' : ( 'anim-' . $slug );
		}

		/**
		 * Public: build the :root {} stylesheet from an animRep array.
		 * Booleans are stringified to "true" / "false" so they round-trip
		 * through the compiled-CSS viewer the same way the JS emitter does.
		 *
		 * @param array $animrep List of animation objects.
		 * @return string Rendered :root {} stylesheet, or '' when empty.
		 */
		public static function build_css( $animrep ) {
			if ( ! is_array( $animrep ) || empty( $animrep ) ) {
				return '';
			}

			$lines = array();
			foreach ( $animrep as $anim ) {
				if ( is_object( $anim ) ) {
					$anim = json_decode( wp_json_encode( $anim ), true );
				}
				if ( ! is_array( $anim ) ) {
					continue;
				}
				$ns = self::anim_slug( isset( $anim['animName'] ) ? $anim['animName'] : '' );
				if ( '' === $ns ) {
					continue;
				}
				foreach ( self::$animation_fields as $field ) {
					if ( ! array_key_exists( $field, $anim ) ) {
						continue;
					}
					$raw = $anim[ $field ];
					if ( null === $raw || '' === $raw ) {
						continue;
					}
					if ( is_bool( $raw ) ) {
						$val = $raw ? 'true' : 'false';
					} else {
						$val = (string) $raw;
					}
					$lines[] = '  --tpgb-' . $ns . '-' . $field . ': ' . $val . ';';
				}
			}

			if ( empty( $lines ) ) {
				return '';
			}
			return ":root {\n" . implode( "\n", $lines ) . "\n}\n";
		}

		/** One-shot: read active animation list from DB and return rendered CSS. */
		public static function build_active_css() {
			return self::build_css( self::get_active_animrep() );
		}
	}
}
