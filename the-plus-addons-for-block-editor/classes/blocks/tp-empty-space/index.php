<?php
/* Block : Empty Space
 * @since : 1.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_empty_space_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
    $output .= '<div class="tpgb-empty-space tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
    $output .= '</div>';
  
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_empty_space() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_empty_space_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_empty_space' );