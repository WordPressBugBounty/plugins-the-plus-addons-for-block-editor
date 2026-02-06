<?php
/* Block : TP Column
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_grid_render_callback( $attributes, $content) {

	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$customClasses = (!empty($attributes['customClasses'])) ? $attributes['customClasses'] : '';
	$wrapLink = (!empty($attributes['wrapLink'])) ? $attributes['wrapLink'] : false;
	$showchild = (!empty($attributes['showchild'])) ? $attributes['showchild'] : false;
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	if(!empty($customClasses)){
		$blockClass .= ' '.esc_attr($customClasses);
	}
    
	// Set Link Data
	$colLink = '';
	if(!empty($wrapLink)){
		$colUrl = (!empty($attributes['colUrl'])) ? $attributes['colUrl'] : '';
		$blockClass .= ' tpgb-col-link';
		
		if( !empty($colUrl) && isset($colUrl['url']) && !empty($colUrl['url']) ){
			$colLink .= ' data-tpgb-col-link= "'.esc_url($colUrl['url']).'" ';
		}
		if(!empty($colUrl) && isset($colUrl['target']) && !empty($colUrl['target'])){
			$colLink .= ' data-target="_blank"';
		}else{
			$colLink .= ' data-target="_self"';
		}
		$colLink .= Tp_Blocks_Helper::add_link_attributes($attributes['colUrl']);
	}

	$output .= '<div class="tpgb-container-col tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'" data-id="'.esc_attr($block_id).'" '.$colLink.' >';
		$output .= $content;
	$output .= '</div>';
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_grid() {
    $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_grid_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_grid' );