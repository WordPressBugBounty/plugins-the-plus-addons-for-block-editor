<?php
/* Block : Accordion
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_accordion_render_callback( $attributes, $content) {
	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$pattern = '/\btpgb-block-'.esc_attr($block_id).'/';
   
	if (preg_match($pattern, $content)) {
		if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attributes,$content);
        }
		return $content;
	}
	$accordianList = (!empty($attributes['accordianList'])) ? $attributes['accordianList'] : [];
	$titleAlign = (!empty($attributes['titleAlign'])) ? $attributes['titleAlign'] :'text-left';
	$toggleIcon = (!empty($attributes['toggleIcon'])) ? $attributes['toggleIcon'] :false;
	$iconFont = (!empty($attributes['iconFont'])) ? $attributes['iconFont'] : 'font_awesome';
	$iconName = (!empty($attributes['iconName'])) ? $attributes['iconName'] : 'fas fa-plus';
	$ActiconName = (!empty($attributes['ActiconName'])) ? $attributes['ActiconName'] : 'fas fa-minus';
	$iconAlign = (!empty($attributes['iconAlign'])) ? $attributes['iconAlign'] : 'end';
	$titleTag = (!empty($attributes['titleTag'])) ? $attributes['titleTag'] : 'h3';
	$accorType = (!empty($attributes['accorType'])) ? $attributes['accorType'] : '';
	$descAlign = (!empty($attributes['descAlign'])) ? $attributes['descAlign'] :'';
	
	$i=0;
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	//Get Toogle icon
	$tgicon = '';
	if(!empty($toggleIcon)){	
		$tgicon .= '<div class="accordion-toggle-icon">';
			$tgicon .= '<span class="close-toggle-icon toggle-icon">';
				if($iconFont == 'font_awesome'){
					$tgicon .=  '<i class="'.esc_attr($iconName).'"> </i>' ;
				}
			$tgicon .= '</span>';
			$tgicon .= '<span class="open-toggle-icon toggle-icon">';
				if($iconFont == 'font_awesome'){
					$tgicon .= '<i class="'.esc_attr($ActiconName).'"> </i>' ; 
				}
			$tgicon .= '</span>';
		$tgicon .= '</div>';
	}
	
	$loop_content = '';
	if(!empty($accordianList)){
		foreach ( $accordianList as $index => $item ) :
			$i++;
			
			//set active class
			$active = '';
			if($i==0){
				$active = 'active';
			}

			$loop_content .= '<div class="tpgb-accor-item tpgb-relative-block '.esc_attr($active).'">';
				$loop_content .= '<div id="'.(!empty($item['UniqueId']) ? esc_attr($item['UniqueId']) : 'tpag-tab-title-'.esc_attr($block_id).esc_attr($i) ).'" class="tpgb-accordion-header tpgb-trans-linear-before '.esc_attr($titleAlign).' '.esc_attr($active).'" role="tab" data-tab="'.esc_attr($i).'" aria-controls="'.(!empty($item['UniqueId']) ? esc_attr($item['UniqueId']) : 'tpag-tab-title-'.esc_attr($block_id).esc_attr($i) ).'">';
					if($iconAlign == 'start'){
						$loop_content .= $tgicon;
					}
					$loop_content .= '<span class="accordion-title-icon-wrap">';
						if(!empty($item['innerIcon'])){
							$loop_content .= '<span class="accordion-tab-icon">';
								if($item['iconFonts'] == 'font_awesome'){
									$loop_content .= '<i class="'.esc_attr($item['innericonName']).'"></i>';
								}
							$loop_content .= '</span>';
						}
						$loop_content .= '<'.Tp_Blocks_Helper::validate_html_tag($titleTag).' class="accordion-title"> '.wp_kses_post($item['title']).'</'.Tp_Blocks_Helper::validate_html_tag($titleTag).'>';
					$loop_content .= '</span>';

					if($iconAlign == 'end'){
						$loop_content .= $tgicon;
					}
				$loop_content .= '</div>';

				$loop_content .= '<div id="tpag-tab-content-'.esc_attr($block_id).esc_attr($i).'" class="tpgb-accordion-content '.esc_attr($active).'" role="tabpanel" data-tab="'.esc_attr($i).'" aria-labelledby="'.(!empty($item['UniqueId']) ? esc_attr($item['UniqueId']) : 'tpag-tab-title-'.esc_attr($block_id).esc_attr($i) ).'">';
					$loop_content .= '<div class="tpgb-content-editor '.esc_attr($descAlign).'">';
						if( !empty($item['contentType']) && $item['contentType'] == 'content'){
							$loop_content .= wp_kses_post($item['desc']);
						}
					$loop_content .= '</div>';
				$loop_content .= '</div>';
			$loop_content .= '</div>';
		endforeach;
	}
	
	$output .= '<div class="tpgb-accordion tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		$output .= '<div class="tpgb-accor-wrap" data-type="accordion" role="tablist">';
			if( $accorType == 'editor' ){
				$output .= $content;
			}else{
				$output .= $loop_content;
			}
		$output .= '</div>';
    $output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
	return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_accordion() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_accordion_render_callback');
	register_block_type( $block_data['name'], $block_data );
}


add_action( 'init', 'tpgb_tp_accordion' );