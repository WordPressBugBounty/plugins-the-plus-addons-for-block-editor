<?php
/**
 * Form Submit Button.
 *
 * @package ThePluginAddonsForBlockEditor
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nxt form submit callback.
 *
 * @param mixed $attr The attr.
 * @param mixed $content The content.
 * @return mixed The result.
 */
function nxt_form_submit_callback( $attr, $content ) {
	$pattern = '/\btpgb-wrap-/';

	$form_id = ( isset( $attr['parentFormId'] ) && ! empty( $attr['parentFormId'] ) ? $attr['parentFormId'] : '' );
	ob_start();
	do_action( 'nexter_form_integrate' . esc_attr( $form_id ) );
	$output = ob_get_clean();

	if ( preg_match( $pattern, $content ) ) {
		$output .= $content;
		return $output;
	}

	$output .= $content;
	return Tpgb_Blocks_Global_Options::block_Wrap_Render( $attr, $output );
}

/**
 * Nxt form submit render.
 */
function nxt_form_submit_render() {

	$block_data = Tpgb_Blocks_Global_Options::merge_options_json( __DIR__, 'nxt_form_submit_callback' );
	/*
	 * B12-1: register_block_type() reads the snake_case 'api_version'; block.json
	 * declares camelCase 'apiVersion', which only register_block_type_from_metadata()
	 * maps. Scoped to this block deliberately - do NOT move this into
	 * merge_options_json() without a full editor regression pass on all 180 blocks.
	 */
	if ( isset( $block_data['apiVersion'] ) ) {
		$block_data['api_version'] = (int) $block_data['apiVersion'];
	}

	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'nxt_form_submit_render' );
