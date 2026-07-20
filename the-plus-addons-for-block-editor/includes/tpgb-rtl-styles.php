<?php
/**
 * RTL stylesheet swapping for all Nexter Blocks CSS.
 *
 * Two layers, both active only on RTL locales (LTR sites are never touched):
 *
 *   1. STATIC CSS (editor, frontend, admin, dashboard) — every shipped .css has a
 *      pre-built `-rtl.css` twin generated at build time by scripts/gen-rtl.mjs
 *      (rtlcss flips all physical CSS). This filter serves the twin.
 *
 *   2. DYNAMIC per-post CSS (uploads/theplus_gutenberg/plus-*.css, generated at
 *      runtime from block attributes) — no build-time twin exists, so we flip the
 *      generated CSS with tpgb_rtl_flip_css() and cache an `-rtl.css` twin next to
 *      it, regenerating whenever the source file changes.
 *
 * @package The_Plus_Addons_For_Block_Editor
 */

defined( 'ABSPATH' ) || exit;

/**
 * Flip physical directional CSS for RTL (a focused, conservative rtlcss-lite).
 *
 * Handles the directional declarations the generated block CSS actually uses:
 * margin/padding/border left↔right (incl. -width/-color/-style), border-radius
 * corners, 4-value margin/padding and border-radius shorthands, text-align/float/
 * clear values, and standalone left/right position properties. Anything it does
 * not recognise is left untouched, and function values (rgba/calc/…) are skipped.
 *
 * @param string $css Source CSS.
 * @return string Flipped CSS.
 */
function tpgb_rtl_flip_css( $css ) {
	if ( ! is_string( $css ) || '' === $css ) {
		return $css;
	}

	// 1) Property-name swaps (longest first) via placeholder tokens so chained
	// replacements never double-swap.
	$map    = array(
		'border-top-left-radius'     => 'border-top-right-radius',
		'border-top-right-radius'    => 'border-top-left-radius',
		'border-bottom-left-radius'  => 'border-bottom-right-radius',
		'border-bottom-right-radius' => 'border-bottom-left-radius',
		'margin-left'                => 'margin-right',
		'margin-right'               => 'margin-left',
		'padding-left'               => 'padding-right',
		'padding-right'              => 'padding-left',
		'border-left'                => 'border-right',
		'border-right'               => 'border-left',
	);
	$i      = 0;
	$tokens = array();
	foreach ( $map as $from => $to ) {
		$ph            = "\0T" . ( $i++ ) . "\0";
		$tokens[ $ph ] = $to;
		$css           = preg_replace( '/(?<![a-z-])' . preg_quote( $from, '/' ) . '(?![a-z-])/i', $ph, $css );
	}
	$css = strtr( $css, $tokens );

	// 2) Directional value keywords.
	$css = preg_replace_callback(
		'/\b(text-align|float|clear)\s*:\s*(left|right)/i',
		static function ( $m ) {
			$v = 'left' === strtolower( $m[2] ) ? 'right' : 'left';
			return $m[1] . ':' . $v;
		},
		$css
	);

	// 3) Standalone left:/right: position properties (declaration start).
	$css = preg_replace_callback(
		'/([{;]\s*)(left|right)(\s*:)/i',
		static function ( $m ) {
			$p = 'left' === strtolower( $m[2] ) ? 'right' : 'left';
			return $m[1] . $p . $m[3];
		},
		$css
	);

	// 4) 4-value margin/padding shorthand  T R B L  ->  T L B R  (simple tokens only).
	$css = preg_replace_callback(
		'/\b(margin|padding)\s*:\s*([^\s;(){}]+)\s+([^\s;(){}]+)\s+([^\s;(){}]+)\s+([^\s;(){}]+)\s*;/i',
		static function ( $m ) {
			return $m[1] . ':' . $m[2] . ' ' . $m[5] . ' ' . $m[4] . ' ' . $m[3] . ';';
		},
		$css
	);

	// 5) 4-value border-radius  TL TR BR BL  ->  TR TL BL BR.
	$css = preg_replace_callback(
		'/\bborder-radius\s*:\s*([^\s;(){}]+)\s+([^\s;(){}]+)\s+([^\s;(){}]+)\s+([^\s;(){}]+)\s*;/i',
		static function ( $m ) {
			return 'border-radius:' . $m[2] . ' ' . $m[1] . ' ' . $m[4] . ' ' . $m[3] . ';';
		},
		$css
	);

	return $css;
}

/**
 * Resolve a single stylesheet URL to its RTL twin (uncached worker).
 *
 * Called only from tpgb_rtl_style_src() after the is_rtl() / non-empty guard,
 * so $src is always a non-empty URL on an RTL locale here.
 *
 * @param string $src Stylesheet source URL.
 * @return string Possibly-rewritten source URL.
 */
function tpgb_rtl_resolve_src( $src ) {
	// Uploads directory computed a single time per request.
	static $dyn = null;
	if ( null === $dyn ) {
		$u   = wp_get_upload_dir();
		$dyn = ( ! empty( $u['baseurl'] ) && ! empty( $u['basedir'] ) )
			? array( trailingslashit( $u['baseurl'] ) . 'theplus_gutenberg/', trailingslashit( $u['basedir'] ) . 'theplus_gutenberg/' )
			: false;
	}

	$parts = explode( '?', $src, 2 );
	$url   = $parts[0];
	$query = isset( $parts[1] ) ? '?' . $parts[1] : '';

	if ( '.css' !== substr( $url, -4 ) || '-rtl.css' === substr( $url, -8 ) ) {
		return $src;
	}

	// --- Layer 1: static shipped CSS (pre-built twins). -------------------------
	if ( false !== strpos( $url, TPGB_URL ) ) {
		$rtl_url  = substr( $url, 0, -4 ) . '-rtl.css';
		$rtl_path = str_replace( TPGB_URL, TPGB_PATH, $rtl_url );
		if ( is_string( $rtl_path ) && file_exists( $rtl_path ) ) {
			return $rtl_url . $query;
		}
		return $src;
	}

	// --- Layer 2: dynamic per-post CSS in uploads/theplus_gutenberg/. -----------
	if ( false === $dyn || false === strpos( $url, $dyn[0] ) ) {
		return $src;
	}
	$dyn_url = $dyn[0];
	$dyn_dir = $dyn[1];

	$src_path = str_replace( $dyn_url, $dyn_dir, $url );
	$rtl_url  = substr( $url, 0, -4 ) . '-rtl.css';
	$rtl_path = str_replace( $dyn_url, $dyn_dir, $rtl_url );

	if ( ! file_exists( $src_path ) ) {
		return $src;
	}

	// (Re)build the RTL twin when missing or older than its source.
	if ( ! file_exists( $rtl_path ) || filemtime( $rtl_path ) < filemtime( $src_path ) ) {
		$css = file_get_contents( $src_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false !== $css ) {
			file_put_contents( $rtl_path, tpgb_rtl_flip_css( $css ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}

	if ( file_exists( $rtl_path ) ) {
		return $rtl_url . $query;
	}

	return $src;
}

/**
 * Swap Nexter Blocks stylesheets to their RTL twin on RTL locales.
 *
 * @param string $src    Stylesheet source URL.
 * @param string $handle Stylesheet handle.
 * @return string Possibly-rewritten source URL.
 */
function tpgb_rtl_style_src( $src, $handle ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	if ( empty( $src ) || ! is_rtl() ) {
		return $src;
	}

	// Per-request cache: resolve each stylesheet URL only once.
	static $cache = array();
	if ( ! array_key_exists( $src, $cache ) ) {
		$cache[ $src ] = tpgb_rtl_resolve_src( $src );
	}

	return $cache[ $src ];
}
add_filter( 'style_loader_src', 'tpgb_rtl_style_src', 10, 2 );
