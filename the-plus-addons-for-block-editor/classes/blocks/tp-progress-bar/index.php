<?php
/* Block : Progress Bar
 * @since : 3.2.1
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_progress_bar_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$layoutType = (!empty($attributes['layoutType'])) ? $attributes['layoutType'] : 'progressbar';
    $styleType = (!empty($attributes['styleType'])) ? $attributes['styleType'] : 'style-1';
    $pieStyleType = (!empty($attributes['pieStyleType'])) ? $attributes['pieStyleType'] : 'pieStyle-1';
    $circleStyle = (!empty($attributes['circleStyle'])) ? $attributes['circleStyle'] : 'style-1';
	$heightType = (!empty($attributes['heightType'])) ? $attributes['heightType'] : 'small-height';
	$iconType = (!empty($attributes['iconType'])) ? $attributes['iconType'] : 'iconIcon';
	$iconLibrary = (!empty($attributes['iconLibrary'])) ? $attributes['iconLibrary'] : 'fontawesome';
    $Title = (!empty($attributes['Title'])) ? $attributes['Title'] : '';
	$subTitle = (!empty($attributes['subTitle'])) ? $attributes['subTitle'] : '';
	$IconName = (!empty($attributes['IconName'])) ? $attributes['IconName'] : '';
	$imageName = (!empty($attributes['imageName']['url'])) ? $attributes['imageName'] : '';
	$imageSize = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] : 'thumbnail' ;
	$prepostSymbol = (!empty($attributes['prepostSymbol'])) ? $attributes['prepostSymbol'] : '';
	$sPosition = (!empty($attributes['sPosition'])) ? $attributes['sPosition'] : 'afterNumber';
	$dynamicValue = (!empty($attributes['dynamicValue'])) ? $attributes['dynamicValue'] : '';
	$dynamicPieValue = (!empty($attributes['dynamicPieValue'])) ? $attributes['dynamicPieValue'] : '';
	$dispNumber = (!empty($attributes['dispNumber'])) ? $attributes['dispNumber'] : false;
	$imgPosition = (!empty($attributes['imgPosition'])) ? $attributes['imgPosition'] : 'beforeTitle';
	$emptyColor = (!empty($attributes['emptyColor'])) ? $attributes['emptyColor'] : 'transparent';
	$pieCircleSize = (!empty($attributes['pieCircleSize'])) ? $attributes['pieCircleSize'] : '200';
	$pieThickness = (!empty($attributes['pieThickness'])) ? $attributes['pieThickness'] : '5';
	$pieFillColor = (!empty($attributes['pieFillColor'])) ? $attributes['pieFillColor'] : 'normal';
	$pieColor1 = (!empty($attributes['pieColor1'])) ? $attributes['pieColor1'] : '#FFA500';
	$pieColor2 = (!empty($attributes['pieColor2'])) ? $attributes['pieColor2'] : '#008000';
	$fillReverse = (!empty($attributes['fillReverse'])) ? $attributes['fillReverse'] : false;
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	//image size
	$imgSrc ='';
	$altText = (isset($imageName['alt']) && !empty($imageName['alt'])) ? esc_attr($imageName['alt']) : ((!empty($imageName['title'])) ? esc_attr($imageName['title']) : esc_attr__('Progress Bar','the-plus-addons-for-block-editor'));

	if(!empty($imageName) && !empty($imageName['id'])){
		$imgSrc = wp_get_attachment_image($imageName['id'] , $imageSize, false, ['class' => 'progress-bar-img', 'alt'=> $altText]);
	}else if(!empty($imageName['url'])){
		$imgUrl = (isset($imageName['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($imageName) : $imageName['url'];
		$imgSrc = '<img src="'.esc_url($imgUrl).'" class="progress-bar-img" alt="'.$altText.'"/>';
	}
	
	$data_fill_color='';
	if($pieFillColor =='gradient'){
		$data_fill_color = ' data-fill="{&quot;gradient&quot;: [&quot;' . esc_attr($pieColor1) . '&quot;,&quot;' . esc_attr($pieColor2) . '&quot;]}" ';
	}else{
		$data_fill_color = ' data-fill="{&quot;color&quot;: &quot;'.esc_attr($pieColor1).'&quot;}" ';
	}
	
	$piechartClass=$piechantAttr='';
	if($layoutType=='piechart'){
		$piechartClass='tpgb-piechart';
		$pieval = (!empty($dynamicPieValue) && preg_match( '/data-tpgb-dynamic=(.*?)\}/', $dynamicPieValue, $match ) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($dynamicPieValue) : (!empty($dynamicPieValue) ? $dynamicPieValue : '');
		$piechantAttr = $data_fill_color.' data-emptyfill="'.esc_attr($emptyColor).'" data-value="'.esc_attr($pieval).'"  data-size="'.esc_attr($pieCircleSize).'" data-thickness="'.esc_attr($pieThickness).'" data-animation-start-value="0"  data-reverse="'.esc_attr($fillReverse).'"';
	}
	
	$getTitle ='';
	if(!empty($Title)){
		$before_after = ($imgPosition=='beforeTitle') ? ' before-icon' : ' after-icon';
		$getTitle .= '<span class="progress-bar-title '.($iconType!='iconNone' ? $before_after : '').'">'.wp_kses_post($Title).'</span>';
	}
	$getIcon='';
	if(!empty($IconName)){
		$getIcon .='<span class="progres-ims">';
			if($iconType=='iconIcon' && $iconLibrary=='fontawesome'){
				$getIcon .='<i class="'.esc_attr($IconName).'"></i>';
			}
			elseif($iconType=='iconImage'){
				$getIcon .= $imgSrc;
			}
		$getIcon .='</span>';
			
	}
	$getSubTitle='';
	if(!empty($subTitle)){
		$getSubTitle .='<div class="progress-bar-sub-title">'.wp_kses_post($subTitle).'</div>';
	}
	
	$getCounterNo=$SymbolGet=$NumberGet='';
	if(!empty($prepostSymbol)){
		$SymbolGet .= '<span class="theserivce-milestone-symbol">'.wp_kses_post($prepostSymbol).'</span>';
	}
	if(!empty($dynamicValue) || !empty($dynamicPieValue)){
		$Number ='';
		if($layoutType =='progressbar') {
			$Number = (!empty($dynamicValue) && preg_match( '/data-tpgb-dynamic=(.*?)\}/', $dynamicValue, $match ) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($dynamicValue) : (!empty($dynamicValue) ? (float)$dynamicValue : '');
		}elseif($layoutType =='piechart') {
			$Number = (!empty($dynamicPieValue) && preg_match( '/data-tpgb-dynamic=(.*?)\}/', $dynamicPieValue, $match ) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($dynamicPieValue) : (!empty($dynamicPieValue) ? $dynamicPieValue : '');
			$Number = (float)$Number*100;
		}
		if(!empty($dispNumber)){
			$NumberGet .= '<span class="theserivce-milestone-number icon-milestone">'.wp_kses_post($Number).'</span>';
		}
	}
	
	$getCounterNo .= '<h5 class="counter-number">';
	if(!empty($sPosition=='afterNumber')){
		$getCounterNo .= $NumberGet.$SymbolGet;
	}
	if(!empty($sPosition=='beforeNumber')){
		$getCounterNo .= $SymbolGet.$NumberGet;
	}
	$getCounterNo .= '</h5>';
	
	$htype='';
	$sml='';
		if($heightType=='small-height'){
			$htype = 'small';
			$sml = 'prog-title prog-icon';
		}elseif($heightType=='medium-height'){
			$htype = 'medium';
			$sml = 'prog-title prog-icon';
		}elseif($heightType=='large-height'){
			$htype = 'large';
			$sml = 'prog-title prog-icon large';
		}
	$circleBorder = '' ;
	if($circleStyle=='style-2'){
		$circleBorder = 'pie-circle-border';
	}
		
    $output .= '<div class="tpgb-progress-bar tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr($piechartClass).' '.esc_attr($blockClass).'" '.$piechantAttr.'>';
		//Progrssbar
		if($layoutType =='progressbar'){
			if($styleType=='style-1'){
				if($heightType=='small-height'){
					$output .= '<div class="progress-bar-media"><div class="'.esc_attr($sml).'">';
							if($imgPosition=='beforeTitle'){
								$output .= $getIcon;
								$output .= $getTitle;
							}
							elseif($imgPosition=='afterTitle'){
								$output .= $getTitle;
								$output .= $getIcon;
							}
							$output .=$getSubTitle;
					$output .='</div>'.$getCounterNo.'</div><div class="progress-bar-skill skill-fill '.esc_attr($htype).'"><div class="progress-bar-skill-bar-filled" data-width="'.esc_attr($Number).'%"></div></div>';
				}
				if($heightType=='medium-height'){
					$output .= '<div class="progress-bar-media"><div class="'.esc_attr($sml).'">';
							if($imgPosition=='beforeTitle'){
								$output .= $getIcon;
								$output .= $getTitle;
							}
							elseif($imgPosition=='afterTitle'){
								$output .= $getTitle;
								$output .= $getIcon;
							}
							$output .=$getSubTitle;
						$output .='</div>'.$getCounterNo.'</div><div class="progress-bar-skill skill-fill '.esc_attr($htype).'"><div class="progress-bar-skill-bar-filled " data-width="'.esc_attr($Number).'%"></div></div>';
				}
				if($heightType=='large-height'){
					$output .='<div class="progress-bar-skill skill-fill '.esc_attr($htype).'"><div class="progress-bar-skill-bar-filled " data-width="'.esc_attr($Number).'%"></div><div class="progress-bar-media large" data-width="'.esc_attr($Number).'%"><div class="'.esc_attr($sml).'">';
							if($imgPosition=='beforeTitle'){
								$output .= $getIcon;
								$output .= $getTitle;
							}
							elseif($imgPosition=='afterTitle'){
								$output .= $getTitle;
								$output .= $getIcon;
							}
					$output .='</div>'.$getCounterNo.'</div></div>';
				}
			}
			if($styleType=='style-2'){
				$output .='<div class="progress-bar-media"><div class="'.esc_attr($sml).'">';
						if($imgPosition=='beforeTitle'){
							$output .= $getIcon;
							$output .= $getTitle;
						}
						elseif($imgPosition=='afterTitle'){
							$output .= $getTitle;
							$output .= $getIcon;
						}
						$output .=$getSubTitle;
					$output .='</div>'.$getCounterNo.'</div><div class="progress-bar-skill skill-fill progress-style-2"><div class="progress-bar-skill-bar-filled " data-width="'.esc_attr($Number).'%"></div></div>';
			}
		}
		if($layoutType=='piechart'){
			if($pieStyleType=='pieStyle-1'){
				$output .='<div class = "tpgb-piechart tpgb-relative-block '.esc_attr($circleBorder).'"><div class="tp-pie-circle"><div class="pie-numbers">'.$getCounterNo.'</div></div></div>';
				$output .='<div class = "tpgb-pie-chart">';		 
					$output .= $getTitle;
					$output .=$getSubTitle;
				$output .='</div>';
			}
			if($pieStyleType=='pieStyle-2'){
				$output .='<div class = "tpgb-piechart tpgb-relative-block '.esc_attr($circleBorder).'"><div class="tp-pie-circle"><div class="pie-numbers">'.$getCounterNo.'</div></div></div>';
					$output .='<div class = "tpgb-pie-chart style-2"><div class = "pie-chart">'.$getIcon.'</div>';
					$output .='<div class = "pie-chart-style2">';		 
						$output .= $getTitle;
						$output .=$getSubTitle;
					$output .='</div></div>';
			}
			if($pieStyleType=='pieStyle-3'){
				$output .='<div class = "tpgb-piechart tpgb-relative-block '.esc_attr($circleBorder).'"><div class="tp-pie-circle"><div class="pie-numbers">'.$getIcon.'</div></div></div>';
				$output .='<div class = "tpgb-pie-chart style-3"><div class = "pie-chart">'.$getCounterNo.'</div>';
					$output .='<div class = "pie-chart-style3">';		 
						$output .= $getTitle;
						$output .=$getSubTitle;
				$output .='</div></div>';
			}
		}
    $output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}


/**
 * Render for the server-side
 */
function tpgb_tp_progress_bar() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_progress_bar_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_progress_bar' );