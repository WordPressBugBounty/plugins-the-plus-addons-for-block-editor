<?php
/**
 * Tp Image.
 *
 * @package ThePluginAddonsForBlockEditor
 */

defined( 'ABSPATH' ) || exit;

/**
 * Let WordPress core's own loading-optimization heuristic
 * (wp_get_loading_optimization_attributes(), applied to the assembled page
 * via wp_filter_content_tags() on the `the_content` filter right after
 * do_blocks()) decide lazy-vs-eager and fetchpriority for this block's own
 * <img>, instead of tp-image always forcing loading="lazy" regardless of
 * position (#135).
 *
 * Core's heuristic refuses to touch an <img> that already has a `loading`
 * attribute, which is exactly the bug -- so this removes the hard-coded one.
 * But core's heuristic *also* silently no-ops (adds nothing at all) for any
 * <img> missing `width`/`height` (wp-includes/media.php,
 * wp_get_loading_optimization_attributes(): "For any resources, width and
 * height must be provided, to avoid layout shifts"), and tp-image's
 * save.js never wrote those either -- confirmed in the lab: stripping only
 * `loading="lazy"` left every tp-image with no loading attribute at all, so
 * core stayed silent and EVERY image (including ones far below the fold)
 * would start an eager fetch, which is worse than the original bug for
 * image-heavy pages, not just a wash. So this also backfills width/height
 * from the attachment's own metadata (wp_get_attachment_image_src(), the
 * same cheap postmeta lookup core's own image output uses -- no filesystem
 * hit) whenever the block references a real attachment id, which is what
 * lets core's per-request image counter actually run instead of bailing.
 * When there's no attachment id (a dynamic/data-bound image URL with
 * nothing to look up), the hard-coded `loading="lazy"` is deliberately left
 * alone -- unchanged, original behavior -- rather than eager-loading an
 * image core has no dimensions for.
 *
 * save.js is left untouched (no attribute default changed, so no block
 * deprecation is needed): this only edits the render-callback's copy of the
 * markup, and does nothing when the block's own "disable lazy load" toggle
 * already omitted the `loading` attribute.
 *
 * @param string $content Rendered block markup (save()'s own output).
 * @param mixed  $attr    Block attributes, used to look up the attachment id.
 * @return string
 */
function tpgb_tp_image_apply_loading_optimization( $content, $attr ) {
	if ( '' === $content || false === strpos( $content, 'tpgb-img-inner' ) ) {
		return $content;
	}

	// WP < 6.2 has no WP_HTML_Tag_Processor: leave markup untouched rather than regex-edit it.
	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $content;
	}

	$attachment_id = 0;
	if ( is_array( $attr ) && ! empty( $attr['tImg']['id'] ) && is_numeric( $attr['tImg']['id'] ) ) {
		$attachment_id = (int) $attr['tImg']['id'];
	}

	$dimensions = false;
	if ( $attachment_id > 0 ) {
		$dimensions = wp_get_attachment_image_src( $attachment_id, 'full' );
	}

	$processor = new WP_HTML_Tag_Processor( $content );

	while ( $processor->next_tag( array( 'tag_name' => 'img' ) ) ) {
		$class_attr = (string) $processor->get_attribute( 'class' );
		if ( '' === $class_attr || false === strpos( $class_attr, 'tpgb-img-inner' ) ) {
			continue;
		}

		if ( ! is_array( $dimensions ) || empty( $dimensions[1] ) || empty( $dimensions[2] ) ) {
			// No dimensions available for core's heuristic to work with: leave the existing attribute (if any) untouched.
			continue;
		}

		if ( null === $processor->get_attribute( 'width' ) ) {
			$processor->set_attribute( 'width', (string) $dimensions[1] );
		}
		if ( null === $processor->get_attribute( 'height' ) ) {
			$processor->set_attribute( 'height', (string) $dimensions[2] );
		}
		if ( 'lazy' === $processor->get_attribute( 'loading' ) ) {
			$processor->remove_attribute( 'loading' );
		}
	}

	return $processor->get_updated_html();
}

/**
 * Tpgb tp image callback.
 *
 * @param mixed $attr The attr.
 * @param mixed $content The content.
 * @return mixed The result.
 */
function tpgb_tp_image_callback( $attr, $content ) {

	$content = tpgb_tp_image_apply_loading_optimization( $content, $attr );
	$pattern = '/\btpgb-wrap-/';

	if ( preg_match( $pattern, $content ) ) {
		// Check Display Condition.
		if ( class_exists( 'Tpgb_Blocks_Global_Options' ) ) {
			$global_blocks = Tpgb_Blocks_Global_Options::get_instance();
			$content       = $global_blocks::block_row_conditional_render( $attr, $content );
		}
		return $content;
	}
	return Tpgb_Blocks_Global_Options::block_Wrap_Render( $attr, $content );
}

/**
 * Tpgb tp image render.
 */
function tpgb_tp_image_render() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json( __DIR__, 'tpgb_tp_image_callback' );
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_image_render' );
