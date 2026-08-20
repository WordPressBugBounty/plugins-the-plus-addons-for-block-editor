<?php
/**
 * Nexter Blocks — Abilities API 7.1 enhancements (free + Pro).
 *
 * Attaches to the WordPress 7.1 Abilities API extension points so every
 * nexter-blocks/* and nexter-blocks-pro/* ability is more discoverable,
 * cheaper and more reliable for AI agents — without editing any individual
 * ability definition. Loaded from class-tpgb-mcp-abilities.php during
 * register_abilities(), BEFORE the ability files register, so the
 * wp_register_ability_args filter applies to every Nexter ability.
 *
 *   A. Unified `public` flag       — wp_register_ability_args   (compat: keeps
 *                                     abilities discoverable once the MCP
 *                                     adapter honors the unified flag)
 *   B. Preset packs + RTL default  — wp_ability_normalize_input
 *   C. Semantic input validation   — wp_ability_validate_input
 *   D. Usage telemetry (opt-in)    — wp_ability_invoked
 *   E. Read-result caching         — wp_pre_execute_ability
 *   F. Central Pro gating          — wp_ability_permission_result
 *   G. Auto-verify + enrichment    — wp_ability_execute_result
 *
 * No-op before WordPress 7.1 (WP_Filter_Sentinel and the filters do not exist).
 *
 * @package The_Plus_Addons_For_Block_Editor
 * @since   x.x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'tpgb_ability_is_nexter' ) ) {
	/**
	 * Whether an ability name belongs to the Nexter Blocks families.
	 *
	 * @param string $name Ability name.
	 * @return bool
	 */
	function tpgb_ability_is_nexter( $name ) {
		return is_string( $name ) && ( str_starts_with( $name, 'nexter-blocks/' ) || str_starts_with( $name, 'nexter-blocks-pro/' ) );
	}
}

if ( ! function_exists( 'tpgb_ability_presets' ) ) {
	/**
	 * Preset packs: ability short name => preset name => flat input params.
	 *
	 * These are the ability's documented flat inputs; the ability maps them to
	 * the block's structured attributes (e.g. fontWeight => tTypo.fontFamily.fontWeight).
	 * Filterable so packs can grow without editing this file.
	 *
	 * @return array<string,array<string,array<string,mixed>>>
	 */
	function tpgb_ability_presets() {
		return (array) apply_filters(
			'tpgb_ability_presets',
			array(
				'heading' => array(
					'hero'    => array( 'tTag' => 'h1', 'enableTypography' => true, 'fontWeight' => '700', 'scrollAnimation' => 'fadeInUp' ),
					'section' => array( 'tTag' => 'h2', 'enableTypography' => true, 'fontWeight' => '600' ),
					'subtle'  => array( 'tTag' => 'h4', 'fontWeight' => '400' ),
				),
				'button'  => array(
					'primary' => array( 'enableTypography' => true, 'fontWeight' => '600', 'scrollAnimation' => 'fadeIn' ),
					'ghost'   => array( 'enableTypography' => true, 'fontWeight' => '500' ),
				),
			)
		);
	}
}

if ( ! function_exists( 'tpgb_register_ability_lifecycle' ) ) {
	/**
	 * Registers the Nexter Blocks Abilities API 7.1 enhancement filters.
	 *
	 * @return void
	 */
	function tpgb_register_ability_lifecycle() {
		if ( ! class_exists( 'WP_Filter_Sentinel' ) || ! function_exists( 'wp_get_ability' ) ) {
			return;
		}

		$is_add  = static fn( $n ) => is_string( $n ) && str_starts_with( $n, 'nexter-blocks/add-tpgb-' );
		$is_read = static fn( $n ) => is_string( $n ) && (
			str_starts_with( $n, 'nexter-blocks/get-' )
			|| str_starts_with( $n, 'nexter-blocks-pro/get-' )
			|| 'nexter-blocks/verify-page' === $n
			|| 'nexter-blocks/inspect-page' === $n
		);
		$short = static fn( $n ) => substr( $n, strlen( 'nexter-blocks/add-tpgb-' ) );
		$ckey  = static fn( $n, $i ) => 'tpgb_ab_' . md5( $n . '|' . wp_json_encode( $i ) );
		$ttl   = static fn( $n ) => (int) apply_filters( 'tpgb_ability_cache_ttl', 15 * MINUTE_IN_SECONDS, $n );

		// A. Unified public flag — keep every Nexter ability discoverable under 7.1.
		add_filter(
			'wp_register_ability_args',
			static function ( $args, $name ) {
				if ( tpgb_ability_is_nexter( $name ) ) {
					if ( ! isset( $args['meta'] ) || ! is_array( $args['meta'] ) ) {
						$args['meta'] = array();
					}
					if ( ! isset( $args['meta']['public'] ) ) {
						$args['meta']['public'] = true;
					}
				}
				return $args;
			},
			10,
			2
		);

		// B. Preset expansion + RTL-aware alignment default (agent values always win).
		add_filter(
			'wp_ability_normalize_input',
			static function ( $input, $name ) use ( $is_add, $short ) {
				if ( ! $is_add( $name ) || ! is_array( $input ) ) {
					return $input;
				}
				if ( ! empty( $input['_preset'] ) ) {
					$packs  = tpgb_ability_presets();
					$bundle = $packs[ $short( $name ) ][ $input['_preset'] ] ?? array();
					foreach ( $bundle as $k => $v ) {
						if ( ! array_key_exists( $k, $input ) ) {
							$input[ $k ] = $v;
						}
					}
				}
				unset( $input['_preset'] );
				if ( is_rtl() && array_key_exists( 'tAlign', $input ) && '' === $input['tAlign'] ) {
					$input['tAlign'] = 'right';
				}
				return $input;
			},
			10,
			2
		);

		// C. Semantic input validation — fail fast on a non-existent target post.
		add_filter(
			'wp_ability_validate_input',
			static function ( $valid, $input, $name ) use ( $is_add ) {
				if ( is_wp_error( $valid ) ) {
					return $valid;
				}
				if ( $is_add( $name ) && is_array( $input ) && ! empty( $input['post_id'] ) && null === get_post( (int) $input['post_id'] ) ) {
					return new WP_Error(
						'tpgb_invalid_post',
						/* translators: %d: post ID. */
						sprintf( __( 'No post exists with ID %d; cannot insert the block.', 'the-plus-addons-for-block-editor' ), (int) $input['post_id'] ),
						array( 'status' => 404 )
					);
				}
				return $valid;
			},
			10,
			3
		);

		// D. Usage telemetry (opt-in; default off to avoid per-call writes).
		add_action(
			'wp_ability_invoked',
			static function ( $name, $input, $ability ) {
				unset( $input, $ability );
				if ( ! tpgb_ability_is_nexter( $name ) || ! apply_filters( 'tpgb_ability_telemetry', false, $name ) ) {
					return;
				}
				$stats            = get_option( 'tpgb_ability_usage', array() );
				$stats[ $name ]   = isset( $stats[ $name ] ) ? ( (int) $stats[ $name ] + 1 ) : 1;
				update_option( 'tpgb_ability_usage', $stats, false );
			},
			10,
			3
		);

		// E. Serve read-only abilities from cache (short-circuit).
		add_filter(
			'wp_pre_execute_ability',
			static function ( $pre, $name, $input ) use ( $is_read, $ckey, $ttl ) {
				if ( ! $is_read( $name ) || $ttl( $name ) <= 0 ) {
					return $pre;
				}
				$hit = get_transient( $ckey( $name, $input ) );
				return ( false !== $hit ) ? $hit : $pre;
			},
			10,
			3
		);

		// F. Central Pro gate (opt-in list of ability names).
		add_filter(
			'wp_ability_permission_result',
			static function ( $permission, $name ) {
				$pro = (array) apply_filters( 'tpgb_ability_pro_list', array() );
				if ( tpgb_ability_is_nexter( $name ) && in_array( $name, $pro, true ) && ! defined( 'TPGBP_VERSION' ) ) {
					return new WP_Error( 'tpgb_pro_required', __( 'This Nexter block requires Nexter Blocks Pro.', 'the-plus-addons-for-block-editor' ), array( 'status' => 403 ) );
				}
				return $permission;
			},
			10,
			2
		);

		// G. Cache read results + auto-verify inserts so no follow-up call is needed.
		add_filter(
			'wp_ability_execute_result',
			static function ( $result, $name, $input ) use ( $is_add, $is_read, $ckey, $ttl ) {
				static $verifying = false;
				if ( $is_read( $name ) && ! is_wp_error( $result ) && $ttl( $name ) > 0 ) {
					set_transient( $ckey( $name, $input ), $result, $ttl( $name ) );
				}
				if ( apply_filters( 'tpgb_ability_autoverify', true, $name ) && ! $verifying && $is_add( $name ) && ! is_wp_error( $result ) && is_array( $result ) && ! empty( $input['post_id'] ) ) {
					$verifying = true;
					try {
						$verify = wp_get_ability( 'nexter-blocks/verify-page' );
						if ( $verify ) {
							$check = $verify->execute( array( 'post_id' => (int) $input['post_id'] ) );
							if ( ! is_wp_error( $check ) ) {
								$result['_verified'] = $check;
							}
						}
					} catch ( \Throwable $e ) {
						unset( $e ); // never let enrichment break a successful insert.
					} finally {
						$verifying = false;
					}
				}
				return $result;
			},
			10,
			3
		);
	}
}

tpgb_register_ability_lifecycle();
