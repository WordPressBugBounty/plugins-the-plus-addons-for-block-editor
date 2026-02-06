<?php
/* Block : Price List
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_pricing_list( $attributes, $content) {
	$block_id = isset($attributes['block_id']) ? $attributes['block_id'] : '';
	$style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
	$boxAlign = (!empty($attributes['boxAlign'])) ? $attributes['boxAlign'] : 'top-left';
	$hoverEffect = (!empty($attributes['hoverEffect'])) ? $attributes['hoverEffect'] : 'horizontal';
	$tagField = (!empty($attributes['tagField'])) ? $attributes['tagField'] : '';
	$title = (!empty($attributes['title'])) ? $attributes['title'] : '';
	$description = (!empty($attributes['description'])) ? $attributes['description'] : '';
	$price = (!empty($attributes['price'])) ? $attributes['price'] : '';
	$imageField = (!empty($attributes['imageField'])) ? $attributes['imageField'] : '';
	$imgShape = (!empty($attributes['imgShape'])) ? $attributes['imgShape'] : 'none';
	$maskImg = (!empty($attributes['maskImg'])) ? $attributes['maskImg'] : '';
	$imageSize = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] : 'thumbnail';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$imgSrc = '';
	$altText = (isset($imageField['alt']) && !empty($imageField['alt'])) ? esc_attr($imageField['alt']) : ((!empty($imageField['title'])) ? esc_attr($imageField['title']) : esc_attr__('Food Item','the-plus-addons-for-block-editor'));

	if(!empty($imageField) && !empty($imageField['id'])){
		$imgSrc = wp_get_attachment_image($imageField['id'] , $imageSize, false, ['alt'=> $altText]);
	}else if(!empty($imageField['url'])){
		$imgUrl = (isset($imageField['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($imageField) : $imageField['url'];
		$imgSrc = '<img src="'.esc_url($imgUrl).'" alt="'.$altText.'" />';
	}
	
	$getMenuTag = '';
	
    if (class_exists('Tpgbp_Pro_Blocks_Helper')) {
    	global $repeater_index;
    	$rep_Index = $repeater_index ?? 0;

    	if (strpos($tagField, 'acf|') !== false || strpos($tagField, 'jetengine|') !== false) {
    		if (preg_match('/<span[^>]*data-tpgb-dynamic=(["\'])([^"\']+)\1[^>]*><\/span>/', $tagField, $matches) && !empty($matches    [2])) {
    			$dataArray = json_decode(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5), true);

    			if (json_last_error() === JSON_ERROR_NONE && !empty($dataArray['dynamicField'])) {
    				$parts = explode('|', $dataArray['dynamicField']);

    				if (count($parts) === 5 || count($parts) === 7) {
    					$fieldName = $parts[1] ?? 'Unknown Field';
                        $repData = apply_filters('tp_get_repeater_data', $parts);
                        if (is_wp_error($repData)) {
                            $replacement = '';
                        } else {
                            $replacement = $repData['repeater_data'][$rep_Index][$fieldName] ?? '';
                        }

    					$tagField = preg_replace(
    						'/<span[^>]+data-tpgb-dynamic=(["\'])(.*?)\1[^>]*><\/span>/',
    						esc_html($replacement),
    						$tagField
    					);
    				}
    			}
    		}
    	} else {
    	    $tagField = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($tagField);
        }
    } else {
        $tagField = $tagField;
    }

	$array=explode("|",$tagField);
	if(!empty($array[1])){
		foreach($array as $value){
			$getMenuTag .='<h5 class="food-menu-tag">'.esc_html($value).'</h5>';
		}
	}
	else{
		$getMenuTag .='<h5 class="food-menu-tag">'.esc_html($tagField).'</h5>';
	}
		
	$getTitle = '';
	if(!empty($title)){
		$getTitle .='<h3 class="food-menu-title">'.wp_kses_post($title).'</h3>';
	}
	$getDesc = '';
	if(!empty($description)){
		$getDesc .='<div class="food-desc">'.wp_kses_post($description).'</div>';
	}
	$getPrice = '';
	if(!empty($price)){
		$getPrice .='<h4 class="food-menu-price">'.wp_kses_post($price).'</h4>';
	}
	$box_Align='';
	$hover_effect='';
	if($style=='style-2'){
		$box_Align=$boxAlign;
		$hover_effect=$hoverEffect;
	}

	$output = '';
    	$output .= '<div class="tpgb-pricing-list tpgb-relative-block tpgb-block-'.esc_attr($block_id).' food-menu-'.esc_attr($style).' '.esc_attr($blockClass).'">';
			$output .='<div class="food-menu-box '.esc_attr($box_Align).'">';
				if($style=='style-1'){
					if(!empty($tagField)){
						$output .=$getMenuTag;
					}
					$output .=$getTitle;
					$output .=$getDesc;
					$output .=$getPrice;
				}
				if($style=='style-2'){
					$output .='<div class="food-flipbox flip-'.esc_attr($hover_effect).' height-full">';
						$output .='<div class="food-flipbox-holder height-full perspective bezier-1">';
							$output .='<div class="food-flipbox-front bezier-1 no-backface origin-center">';
								$output .='<div class="food-flipbox-content width-full">';
									if(!empty($tagField)){
										$output .='<div class="food-menu-block">'.$getMenuTag.'</div>';
									}
									$output .='<div class="food-menu-block">'.$getTitle.'</div>';
									$output .=$getPrice;
								$output .='</div>';
							$output .='</div>';
							$output .='<div class="food-flipbox-back fold-back-'.esc_attr($hover_effect).' no-backface bezier-1 origin-center">';
								$output .='<div class="food-flipbox-content width-full ">';
									$output .='<div class="text-center">'.$getDesc.'</div>';
					$output .='</div></div></div></div>';
				}
				if($style=='style-3'){
					$output .='<div class="food-menu-flex tpgb-relative-block">';
						$output .='<div class="food-flex-line ">';
						if(!empty($imgSrc)){
							$output .='<div class="food-flex-imgs food-flex-img tpgb-relative-block">';
								$output .='<div class="food-img img-'.esc_attr($imgShape).'">'; 
									$output .= $imgSrc;
								$output .='</div>';
							$output .='</div>';
						}
							$output .='<div class="food-flex-content">';
							if(!empty($tagField)){
								$output .='<div class="food-menu-block">'.$getMenuTag.'</div>';
							}
								$output .='<div class="food-title-price">';
									$output .=$getTitle;
									$output .='<div class="food-menu-divider"><div class="menu-divider no"></div></div>';
									$output .=$getPrice;
								$output .='</div>';
									$output .=$getDesc;
					$output .='</div></div></div>';
				}
			$output .='</div>';
		$output .='</div>';
		
		$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
		
  	return $output;
}
/**
 * Render for the server-side
 */
function tpgb_tp_pricing_list() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_pricing_list');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_pricing_list' );