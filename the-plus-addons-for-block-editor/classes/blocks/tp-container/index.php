<?php
/* Block : Container(Section)
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_container_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $height = (!empty($attributes['height'])) ? $attributes['height'] : '';
    $customClass = (!empty($attributes['customClass'])) ? $attributes['customClass'] : '';
	
	$customId = (!empty($attributes['customId'])) ? 'id="'.esc_attr($attributes['customId']).'"' : ( isset($attributes['anchor']) && !empty($attributes['anchor']) ? 'id="'.esc_attr($attributes['anchor']).'"'  : '' ) ;

	$wrapLink = (!empty($attributes['wrapLink'])) ? $attributes['wrapLink'] : false;

	$showchild = (!empty($attributes['showchild'])) ? $attributes['showchild'] : false;
	$contentWidth = (!empty($attributes['contentWidth'])) ? $attributes['contentWidth'] : 'wide';
	$colDir = (!empty($attributes['colDir'])) ? $attributes['colDir'] : '';
	$tagName = (!empty($attributes['tagName'])) ? $attributes['tagName'] : '';
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$selectedLayout = (!empty($attributes['selectedLayout'])) ? $attributes['selectedLayout'] : '';
    $nxtcontType = (!empty($attributes['nxtcontType'])) ? $attributes['nxtcontType'] : false;
    $contwidFull = (!empty($attributes['contwidFull'])) ? $attributes['contwidFull'] : '';

    //Equal Height
    $equalHeightAttr = Tp_Blocks_Helper::global_equal_height( $attributes );

	$sectionClass = '';
	if( !empty( $height ) ){
		$sectionClass .= ' tpgb-section-height-'.esc_attr($height);
	}

    if( defined('NXT_VERSION') && $contentWidth !== 'full' && !empty($nxtcontType) ){
        $sectionClass .= ' tpgb-nxtcont-type';
    }

    if(!empty($equalHeightAttr)){
        $sectionClass .= ' tpgb-equal-height';
    }
	// Toogle Class For wrapper Link

	$linkdata = '';
	if(!empty($wrapLink)){
		$rowUrl = (!empty($attributes['rowUrl'])) ? $attributes['rowUrl'] : '';
		$sectionClass .= ' tpgb-row-link';
		
		if( !empty($rowUrl) && isset($rowUrl['url']) && !empty($rowUrl['url']) ){
			$linkdata .= 'data-tpgb-row-link="'.esc_url($rowUrl['url']).'" ';
		}
		if(!empty($rowUrl) && isset($rowUrl['target']) && !empty($rowUrl['target'])){
			$linkdata .= 'data-target="_blank" ';
		}else{
			$linkdata .= 'data-target="_self" ';
		}
		$linkdata .= Tp_Blocks_Helper::add_link_attributes($attributes['rowUrl']);
	}

	$output .= '<'.Tp_Blocks_Helper::validate_html_tag($tagName).' '.$customId.' class="tpgb-container-row tpgb-block-'.esc_attr($block_id).' '.esc_attr($sectionClass).' '.esc_attr($customClass).' '.esc_attr($blockClass).' '.($colDir == 'c100' || $colDir == 'r100' ? ' tpgb-container-inline' : '').'  tpgb-container-'.esc_attr($contentWidth).' '.($selectedLayout == 'grid' ? 'tpgb-grid' : '').'" data-id="'.esc_attr($block_id).' " '.$linkdata.' '.$equalHeightAttr.' >';
    
    //top layer Div 
    if( isset($attributes['topOption']) && $attributes['topOption'] != '' ){
        $output .= '<div class="tpgb-top-layer"></div>';
    }
    
	if($contentWidth=='wide'){
		$output .= '<div class="tpgb-cont-in">';
	}
		$output .= $content;

	if($contentWidth=='wide'){
		$output .= '</div>';
	}

	$output .= "</".Tp_Blocks_Helper::validate_html_tag($tagName).">";
	
	
	if ( class_exists( 'Tpgb_Blocks_Global_Options' ) ) {
		$global_block = Tpgb_Blocks_Global_Options::get_instance();
		if ( !empty($global_block) && is_callable( array( $global_block, 'block_row_conditional_render' ) ) ) {
			$output = Tpgb_Blocks_Global_Options::block_row_conditional_render($attributes, $output);
		}
	}
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_container_row() {
    $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_container_render_callback' , true, false, false, true);
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_container_row' );