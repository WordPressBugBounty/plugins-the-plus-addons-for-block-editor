<?php
/* Block : Draw Svg
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_draw_svg_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$duration = (!empty($attributes['duration'])) ? $attributes['duration'] : 90;
	$drawType = (!empty($attributes['drawType'])) ? $attributes['drawType'] : 'delayed';
	$selectSvg = (!empty($attributes['selectSvg'])) ? $attributes['selectSvg'] : 'preBuild';
	$svgList = (!empty($attributes['svgList'])) ? $attributes['svgList'] : 'app';
	$hoverDraw = (!empty($attributes['hoverDraw'])) ? $attributes['hoverDraw'] : 'onScroll';
	$strokeColor = (!empty($attributes['strokeColor'])) ? $attributes['strokeColor'] : '';
	$fillToggle = (!empty($attributes['fillToggle'])) ? $attributes['fillToggle'] : false;
	$fillColor = (!empty($attributes['fillColor'])) ? $attributes['fillColor'] : '';
	$svgLink = (!empty($attributes['svgLink']['url'])) ? $attributes['svgLink']['url'] : '';
	$target = (!empty($attributes['svgLink']['target'])) ? ' target="_blank" ' : '';
	$nofollow = (!empty($attributes['svgLink']['nofollow'])) ? ' rel="nofollow" ' : '';
    $link_attr = Tp_Blocks_Helper::add_link_attributes($attributes['svgLink']);
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

    
    if(class_exists('Tpgbp_Pro_Blocks_Helper')){
		$svgLink = (isset($attributes['svgLink']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['svgLink']) : (!empty($attributes['svgLink']['url']) ? $attributes['svgLink']['url'] : '');
	}
	
	$fillEnable=$fill_color = '';
	if(!empty($fillToggle)){
		$fillEnable = 'yes';
		$fill_color = $fillColor;
	}else{
		$fillEnable = 'no';
		$fill_color = 'none';
	}
	
	$draw_hover = '';
	if($hoverDraw=='onHover'){
		$draw_hover = 'tpgb-hover-draw-svg';
	}
	$svgsrc = '';
	if($selectSvg=='custom'){
		$svgsrc = (isset($attributes['customSVG']['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['customSVG']) : (!empty($attributes['customSVG']['url']) ? $attributes['customSVG']['url'] : '');
	}else{
		$svgsrc = TPGB_URL.'assets/images/svg/'.esc_attr($svgList).'.svg';
	}
	$output = '';
	$output .= '<div class="tpgb-draw-svg tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' '.esc_attr($draw_hover).'" data-id="tpgb-block-'.esc_attr($block_id).'" data-type="'.esc_attr($drawType).'" data-duration="'.esc_attr($duration).'" data-stroke="'.esc_attr($strokeColor).'" data-fillcolor="'.esc_attr($fill_color).'" data-fillenable="'.esc_attr($fillEnable).'">';
		if($svgLink && ( $svgLink != '' )){   
			$output .= '<a '.$link_attr.' href="'.esc_url($svgLink).'" '.$target.' '.$nofollow.'>';
                $output .= '<div class="svg-inner-block">';
                    $output .= '<object id="tpgb-block-'.esc_attr($block_id).'" type="image/svg+xml" data="'.esc_url($svgsrc).'" aria-label="'.esc_attr__('icon','the-plus-addons-for-block-editor').'"></object>';
                $output .= '</div>';
            $output .= '</a>';
        }else{
            $output .= '<div class="svg-inner-block">';
                $output .= '<object id="tpgb-block-'.esc_attr($block_id).'" type="image/svg+xml" data="'.esc_url($svgsrc).'" aria-label="'.esc_attr__('icon','the-plus-addons-for-block-editor').'"></object>';
            $output .= '</div>';
        }
	$output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_draw_svg() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_draw_svg_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_draw_svg' );