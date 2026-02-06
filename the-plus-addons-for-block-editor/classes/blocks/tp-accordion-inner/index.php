<?php
/* Block : TP Accordion Inner
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_accr_inner_render_callback( $attributes, $content) {
	$pattern = '/\btpgb-accor-item/';
    
	if (preg_match($pattern, $content)) {
		if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attributes,$content);
        }
	   return $content;
	}
	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$title = (!empty($attributes['title'])) ? $attributes['title'] : '' ;
	$toggleIcon = (!empty($attributes['toggleIcon'])) ? $attributes['toggleIcon'] :false;
 	$innerIcon = (!empty($attributes['innerIcon'])) ? $attributes['innerIcon'] :false;
	$iniconFonts = (!empty($attributes['iniconFonts'])) ? $attributes['iniconFonts'] : '' ;
	$innericonName = (!empty($attributes['innericonName'])) ? $attributes['innericonName'] : '' ;
	$iconFont = (!empty($attributes['iconFont'])) ? $attributes['iconFont'] : 'font_awesome';
	$iconName = (!empty($attributes['iconName'])) ? $attributes['iconName'] : 'fas fa-plus';
	$ActiconName = (!empty($attributes['ActiconName'])) ? $attributes['ActiconName'] : 'fas fa-minus';
	$iconAlign = (!empty($attributes['iconAlign'])) ? $attributes['iconAlign'] : 'end';
	$titleTag = (!empty($attributes['titleTag'])) ? $attributes['titleTag'] : 'h3';
	$titleAlign = (!empty($attributes['titleAlign'])) ? $attributes['titleAlign'] : '';
	$hrefLink = (!empty($attributes['hrefLink'])) ? $attributes['hrefLink'] : '';
	$index = (!empty($attributes['index'])) ? $attributes['index'] : '';
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	//Get Toogle icon
	$tgicon = '';
	if(!empty($toggleIcon)){	
		$tgicon .= '<div class="accordion-toggle-icon">';
			$tgicon .= '<span class="close-toggle-icon  toggle-icon">';
				if($iconFont == 'font_awesome'){
					$tgicon .=  '<i class="'.esc_attr($iconName).'"> </i>' ; 
				}
			$tgicon .= '</span>';
			$tgicon .= '<span class="open-toggle-icon  toggle-icon">';
				if($iconFont == 'font_awesome'){
					$tgicon .= '<i class="'.esc_attr($ActiconName).'"> </i>' ; 
				}
			$tgicon .= '</span>';
		$tgicon .= '</div>';
	}

	$output .= '<div class="tpgb-accor-item tpgb-relative-block '.esc_attr($blockClass).'">';
		$output .= '<div id="'.esc_attr($hrefLink).'" class="tpgb-accordion-header tpgb-trans-linear-before '.esc_attr($titleAlign).'" role="tab" data-tab="'.esc_attr($index).'" >';
			if($iconAlign == 'start'){
				$output .= $tgicon;
			}
			$output .= '<span class="accordion-title-icon-wrap">';
				if(!empty($innerIcon)){
					$output .= '<span class="accordion-tab-icon">';
						if($iniconFonts == 'font_awesome'){
							$output .= '<i class="'.esc_attr($innericonName).'"></i>';
						}
					$output .= '</span>';
				}
				$output .= '<'.Tp_Blocks_Helper::validate_html_tag($titleTag).' class="accordion-title">'.wp_kses_post($title).'</'.Tp_Blocks_Helper::validate_html_tag($titleTag).' > ';
			$output .= '</span>';
			if($iconAlign == 'end'){
				$output .= $tgicon;
			}
		$output .= '</div>';
		$output .= '<div class="tpgb-accordion-content" role="tabpanel" data-tab="'.esc_attr($index).'">';
			$output .= '<div class="tpgb-content-editor">';
				$output .= $content;
			$output .= '</div>';
		$output .= '</div>';
	$output .= '</div>';
    
	return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_accr_inner() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_accr_inner_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_accr_inner' );