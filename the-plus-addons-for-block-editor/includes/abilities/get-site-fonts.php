<?php
/**
 * Ability: Return the fonts actually available on THIS site — system fonts,
 * active theme.json / Font Library families, and third-party custom fonts —
 * together with each family's front-end render status.
 *
 * Why this exists: MCP clients previously had NO way to discover the fonts a
 * site owner installed. They could only detect Google Fonts referenced on a
 * URL (nexter-blocks/inspect-page) or blindly pass a font name the user typed.
 * Worse, a font installed via the WordPress Font Library does NOT load on the
 * front end unless at least one variant (weight/style) is ACTIVE — an inactive
 * family silently falls back. This ability surfaces the installed inventory
 * AND flags families that will not render, so the model stops applying fonts
 * that can never appear and can tell the user exactly why.
 *
 * CALL THIS RIGHT AFTER nexter-blocks/get-typography-skill — before any
 * nexter-blocks/add-tpgb-* ability — whenever the user asks to "use my fonts",
 * "match my brand font", or otherwise references a site-installed typeface.
 *
 * @package The_Plus_Addons_For_Block_Editor
 * @since   1.3.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_register_ability(
	'nexter-blocks/get-site-fonts',
	array(
		'label'               => __( 'Get Site Fonts (installed font inventory + render status)', 'the-plus-addons-for-block-editor' ),
		'description'         => __( 'Returns every font family available on THIS site — web-safe System fonts, fonts activated via the WordPress Font Library / theme.json, and third-party custom fonts (BSF Custom Fonts, Use Any Font) — with, for each family, how to reference it in a Nexter Blocks block and whether it will actually render on the front end. CALL THIS whenever the user asks to use "my font", a "brand font", or any site-installed typeface, instead of guessing a Google Font. CRITICAL: a Font Library family with zero active variants (weights/styles) does NOT emit any @font-face and will silently fall back — such families are returned with will_render=false and a warning; do not apply them until the user activates a variant in Appearance → Fonts. Usage rule per family (also given inline as the "use" field): System font → pass fontFamily only; installed custom/Font-Library/theme/BSF/Use-Any-Font family → pass BOTH fontFamily AND customFont set to the family name (this makes Nexter use the already-enqueued font instead of trying to load it from Google); a genuine Google Font not installed here (e.g. from nexter-blocks/inspect-page) → pass fontFamily only and leave customFont empty so Nexter @imports it.', 'the-plus-addons-for-block-editor' ),
		'category'            => 'nexter-blocks',

		'input_schema'        => array(
			'type'                 => 'object',
			'properties'           => array(
				'include_system' => array(
					'type'        => 'boolean',
					'description' => 'Include the 8 web-safe System fonts (Arial, Georgia, …) in the result. Default true.',
					'default'     => true,
				),
			),
			'additionalProperties' => false,
		),

		'output_schema'       => array(
			'type'       => 'object',
			'properties' => array(
				'fonts'   => array(
					'type'        => 'array',
					'description' => 'Every font family available on the site, active ones first.',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'name'              => array(
								'type'        => 'string',
								'description' => 'Family name to pass as fontFamily (and customFont, per "use").',
							),
							'group'             => array(
								'type'        => 'string',
								'description' => 'One of: system, font-library, theme, bsf-custom-fonts, use-any-font.',
							),
							'will_render'       => array(
								'type'        => 'boolean',
								'description' => 'True when the family emits a usable @font-face (or is a web-safe/system stack). False when installed but inactive — do NOT apply.',
							),
							'active_variants'   => array(
								'type'        => 'integer',
								'description' => 'Number of activated weight/style variants that load on the front end. 0 means nothing renders. -1 when not applicable (system stacks).',
							),
							'installed_variants' => array(
								'type'        => 'integer',
								'description' => 'Number of variants present in the Font Library for this family (whether active or not). -1 when not applicable.',
							),
							'use'               => array(
								'type'        => 'string',
								'description' => 'How to reference this family in an add-tpgb-* block: "fontFamily" (pass fontFamily only) or "fontFamily+customFont" (pass BOTH set to name).',
							),
							'warning'           => array(
								'type'        => 'string',
								'description' => 'Present only when will_render is false — a human-readable reason and fix.',
							),
						),
					),
				),
				'summary' => array(
					'type'        => 'string',
					'description' => 'One-line human summary of what is installed and what will not render.',
				),
				'notes'   => array(
					'type'        => 'string',
					'description' => 'Guidance the model must follow when choosing a font from this list.',
				),
			),
		),

		'execute_callback'    => 'tpgb_mcp_get_site_fonts',
		'permission_callback' => 'tpgb_mcp_get_site_fonts_permission',
		'meta'                => array(
			'show_in_rest' => true,
			'mcp'          => array(
				'public' => true,
				'type'   => 'tool',
			),
		),
	)
);

/**
 * Permission callback for the get-site-fonts ability.
 *
 * @param array|null $input Ability input arguments (unused; kept for callback signature).
 * @return bool True when the current user may read the font inventory.
 */
function tpgb_mcp_get_site_fonts_permission( ?array $input = null ): bool {
	unset( $input );
	return current_user_can( 'edit_posts' );
}

/**
 * The 8 web-safe System fonts Nexter Blocks always offers. Kept in sync with
 * Tp_Blocks_Helper::tpgb_custom_font() so the model sees the same names the
 * editor dropdown shows. These are excluded from Google-Fonts loading by the
 * CSS generator, so they need fontFamily only (no customFont).
 *
 * @return string[] Web-safe family names.
 */
function tpgb_mcp_site_system_fonts(): array {
	return array( 'Arial', 'Georgia', 'Helvetica', 'Tahoma', 'Times New Roman', 'Trebuchet MS', 'Verdana' );
}

/**
 * Normalise a family name/slug for cross-source matching (Font Library CPT
 * slug vs. theme.json name). Lowercased, non-alphanumerics collapsed to "-".
 *
 * @param string $value Raw name or slug.
 * @return string Normalised key.
 */
function tpgb_mcp_font_norm_key( string $value ): string {
	$value = strtolower( trim( $value ) );
	$value = preg_replace( '/[^a-z0-9]+/', '-', $value );
	return trim( (string) $value, '-' );
}

/**
 * Build the site font inventory with per-family front-end render status.
 *
 * Pure aggregation over WordPress + third-party APIs — no writes. Extracted
 * from the execute callback so it can be unit-tested against injected data.
 *
 * Sources, in priority order:
 *   1. theme.json / Font Library ACTIVE families — wp_get_global_settings()
 *      typography.fontFamilies. These emit @font-face and DO render. A family
 *      here with a non-empty fontFace list is active; one whose entry carries
 *      no fontFace is a font stack (system-style) and still renders.
 *   2. Font Library INSTALLED families — wp_font_family CPTs. Any family whose
 *      normalised slug is not among the active set (step 1) is installed but
 *      INACTIVE: will_render=false with a warning.
 *   3. BSF Custom Fonts + Use Any Font — self-enqueuing third-party fonts.
 *   4. System web-safe fonts (optional).
 *
 * @param bool $include_system Whether to append the web-safe System fonts.
 * @return array{fonts:array<int,array<string,mixed>>,active:int,inactive:int} Inventory.
 */
function tpgb_mcp_build_site_fonts( bool $include_system = true ): array {
	$fonts  = array();
	$active = array(); // normalised key => true, for installed-but-inactive diffing.

	/* ── 1. Active theme.json / Font Library families ─────────────────── */
	if ( function_exists( 'wp_get_global_settings' ) ) {
		$settings = wp_get_global_settings();
		if ( ! empty( $settings['typography']['fontFamilies'] ) && is_array( $settings['typography']['fontFamilies'] ) ) {
			foreach ( $settings['typography']['fontFamilies'] as $origin => $group ) {
				if ( ! is_array( $group ) ) {
					continue;
				}
				foreach ( $group as $font ) {
					if ( empty( $font['name'] ) && empty( $font['fontFamily'] ) ) {
						continue;
					}
					$name  = str_replace( '"', '', (string) ( $font['name'] ?? $font['fontFamily'] ) );
					$faces = ( isset( $font['fontFace'] ) && is_array( $font['fontFace'] ) ) ? $font['fontFace'] : array();
					$key   = tpgb_mcp_font_norm_key( ! empty( $font['slug'] ) ? (string) $font['slug'] : $name );

					$active[ $key ] = true;

					// A webfont family declares fontFace entries; a bare stack does not.
					$is_stack = empty( $faces );

					$fonts[] = array(
						'name'               => $name,
						'group'              => ( 'theme' === $origin ) ? 'theme' : 'font-library',
						'will_render'        => true,
						'active_variants'    => $is_stack ? -1 : count( $faces ),
						'installed_variants' => $is_stack ? -1 : count( $faces ),
						'use'                => $is_stack ? 'fontFamily' : 'fontFamily+customFont',
					);
				}
			}
		}
	}

	/* ── 2. Installed-but-inactive Font Library families ──────────────── */
	if ( post_type_exists( 'wp_font_family' ) ) {
		$installed = get_posts(
			array(
				'post_type'      => 'wp_font_family',
				'post_status'    => 'any',
				'numberposts'    => 200,
				'suppress_filters' => false,
			)
		);
		foreach ( $installed as $family ) {
			$decoded = json_decode( (string) $family->post_content, true );
			$name    = '';
			if ( is_array( $decoded ) ) {
				$name = str_replace( '"', '', (string) ( $decoded['name'] ?? $decoded['fontFamily'] ?? '' ) );
			}
			if ( '' === $name ) {
				$name = (string) $family->post_title;
			}
			$key = tpgb_mcp_font_norm_key( '' !== $family->post_name ? $family->post_name : $name );
			if ( isset( $active[ $key ] ) ) {
				continue; // Already reported as active above.
			}

			$installed_variants = (int) count(
				get_posts(
					array(
						'post_type'   => 'wp_font_face',
						'post_status' => 'any',
						'post_parent' => $family->ID,
						'numberposts' => 200,
						'fields'      => 'ids',
						'suppress_filters' => false,
					)
				)
			);

			$fonts[] = array(
				'name'               => $name,
				'group'              => 'font-library',
				'will_render'        => false,
				'active_variants'    => 0,
				'installed_variants' => $installed_variants,
				'use'                => 'fontFamily+customFont',
				'warning'            => sprintf(
					/* translators: %s: font family name. */
					__( '"%s" is installed but has 0 active variants, so no @font-face is loaded and it will fall back on the front end. Activate at least one weight in Appearance → Fonts and click Update before using it.', 'the-plus-addons-for-block-editor' ),
					$name
				),
			);
		}
	}

	/* ── 3. BSF Custom Fonts (Astra / Spectra) ────────────────────────── */
	if ( class_exists( 'Bsf_Custom_Fonts_Taxonomy' ) ) {
		$bsf = Bsf_Custom_Fonts_Taxonomy::get_fonts();
		if ( is_array( $bsf ) ) {
			foreach ( $bsf as $family => $unused ) {
				$key = tpgb_mcp_font_norm_key( (string) $family );
				if ( isset( $active[ $key ] ) ) {
					continue;
				}
				$active[ $key ] = true;
				$fonts[]        = array(
					'name'               => (string) $family,
					'group'              => 'bsf-custom-fonts',
					'will_render'        => true,
					'active_variants'    => -1,
					'installed_variants' => -1,
					'use'                => 'fontFamily+customFont',
				);
			}
		}
	}

	/* ── 4. Use Any Font ──────────────────────────────────────────────── */
	if ( function_exists( 'uaf_get_font_families' ) ) {
		$uaf = uaf_get_font_families();
		if ( is_array( $uaf ) ) {
			foreach ( $uaf as $family ) {
				$name = is_string( $family ) ? $family : '';
				if ( '' === $name ) {
					continue;
				}
				$key = tpgb_mcp_font_norm_key( $name );
				if ( isset( $active[ $key ] ) ) {
					continue;
				}
				$active[ $key ] = true;
				$fonts[]        = array(
					'name'               => $name,
					'group'              => 'use-any-font',
					'will_render'        => true,
					'active_variants'    => -1,
					'installed_variants' => -1,
					'use'                => 'fontFamily+customFont',
				);
			}
		}
	}

	/* ── 5. Web-safe System fonts ─────────────────────────────────────── */
	if ( $include_system ) {
		foreach ( tpgb_mcp_site_system_fonts() as $name ) {
			$fonts[] = array(
				'name'               => $name,
				'group'              => 'system',
				'will_render'        => true,
				'active_variants'    => -1,
				'installed_variants' => -1,
				'use'                => 'fontFamily',
			);
		}
	}

	// Active/renderable first, then inactive; stable within each bucket.
	usort(
		$fonts,
		static function ( $a, $b ) {
			return (int) $b['will_render'] <=> (int) $a['will_render'];
		}
	);

	$active_count   = 0;
	$inactive_count = 0;
	foreach ( $fonts as $f ) {
		if ( $f['will_render'] ) {
			++$active_count;
		} else {
			++$inactive_count;
		}
	}

	return array(
		'fonts'    => $fonts,
		'active'   => $active_count,
		'inactive' => $inactive_count,
	);
}

/**
 * Execute callback: return the site font inventory with render status.
 *
 * @param array $input Ability input arguments.
 * @return array{fonts:array,summary:string,notes:string} Inventory result.
 */
function tpgb_mcp_get_site_fonts( array $input = array() ) {
	$include_system = ! isset( $input['include_system'] ) || (bool) $input['include_system'];

	$built = tpgb_mcp_build_site_fonts( $include_system );

	$summary = sprintf(
		/* translators: 1: renderable font count, 2: inactive font count. */
		__( '%1$d font(s) will render on the front end; %2$d installed but inactive (0 active variants) and will NOT render until a variant is activated.', 'the-plus-addons-for-block-editor' ),
		(int) $built['active'],
		(int) $built['inactive']
	);

	$notes = __( 'Pick a font whose will_render is true. Follow each entry\'s "use" field: "fontFamily" → pass fontFamily only; "fontFamily+customFont" → pass BOTH fontFamily and customFont set to the exact name (this tells Nexter the font is already enqueued and must not be fetched from Google). Never apply a font with will_render=false — tell the user to activate a variant in Appearance → Fonts first. If the user wants a font not in this list, it is not installed: either install/activate it, or use a Google Font by passing fontFamily only with customFont empty.', 'the-plus-addons-for-block-editor' );

	return array(
		'fonts'   => $built['fonts'],
		'summary' => $summary,
		'notes'   => $notes,
	);
}
