<?php
/* Block : Heading Title
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_limit_words($string, $word_limit){
	$words = explode(" ",$string);
	return implode(" ",array_splice($words,0,$word_limit));
}
function tpgb_tp_heading_title_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
	$headingType = (!empty($attributes['headingType'])) ? $attributes['headingType'] : 'default';
	$Title = (!empty($attributes['Title'])) ? $attributes['Title'] : '';
	$titleType = (!empty($attributes['titleType'])) ? $attributes['titleType'] : 'h3';
	$subTitle = (!empty($attributes['subTitle'])) ? $attributes['subTitle'] : '';
	$subTitleType = (!empty($attributes['subTitleType'])) ? $attributes['subTitleType'] : 'h3';
	$extraTitle = (!empty($attributes['extraTitle'])) ? $attributes['extraTitle'] : '';
	$ETPosition = (!empty($attributes['ETPosition'])) ? $attributes['ETPosition'] : 'afterTitle';
	$subTitlePosition = (!empty($attributes['subTitlePosition'])) ? $attributes['subTitlePosition'] : 'onBottonTitle';
	
	$limitTgl = (!empty($attributes['limitTgl'])) ? $attributes['limitTgl'] : false;
	$titleLimit = (!empty($attributes['titleLimit'])) ? $attributes['titleLimit'] : false;
	$titleLimitOn = (!empty($attributes['titleLimitOn'])) ? $attributes['titleLimitOn'] : 'char';
	$titleCount = (!empty($attributes['titleCount'])) ? $attributes['titleCount'] : '3';
	$titleDots = (!empty($attributes['titleDots'])) ? $attributes['titleDots'] : false;
	
	$subTitleLimit = (!empty($attributes['subTitleLimit'])) ? $attributes['subTitleLimit'] : false;
	$subTitleLimitOn = (!empty($attributes['subTitleLimitOn'])) ? $attributes['subTitleLimitOn'] : 'char';
	$subTitleCount = (!empty($attributes['subTitleCount'])) ? $attributes['subTitleCount'] : '3';
	$subTitleDots = (!empty($attributes['subTitleDots'])) ? $attributes['subTitleDots'] : false;

	$splitType = (!empty($attributes['splitType'])) ? $attributes['splitType'] : 'words';
	$aniEffect = (!empty($attributes['aniEffect'])) ? $attributes['aniEffect'] : 'default';
	$aniPosition = (!empty($attributes['aniPosition'])) ? $attributes['aniPosition'] : [];
	$animationScale = (!empty($attributes['animationScale'])) ? $attributes['animationScale'] : [];
	$animationRotate = (!empty($attributes['animationRotate'])) ? $attributes['animationRotate'] : [];
	$extrOpt = (!empty($attributes['extrOpt'])) ? $attributes['extrOpt'] : [];

	$advHeadingLink = (!empty($attributes['advHeadingLink']['url'])) ? $attributes['advHeadingLink']['url'] : '';
	$target = (!empty($attributes['advHeadingLink']['target'])) ? ' target="_blank" ' : '';
	$nofollow = (!empty($attributes['advHeadingLink']['nofollow'])) ? ' rel="nofollow" ' : '';
    $link_attr = Tp_Blocks_Helper::add_link_attributes($attributes['advHeadingLink']);

	$anchor = ( isset($attributes['anchor']) && !empty($attributes['anchor'])) ? 'id="'.esc_attr($attributes['anchor']).'"' : '';

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

    if(class_exists('Tpgbp_Pro_Blocks_Helper')){
		$advHeadingLink = (isset($attributes['advHeadingLink']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['advHeadingLink']) : (!empty($attributes['advHeadingLink']['url']) ? $attributes['advHeadingLink']['url'] : '');
	}
	
	$getExtraTitle = '';
	if(!empty($extraTitle)){
		$getExtraTitle .='<span class="title-s ">'.wp_kses_post($extraTitle).'</span>';
	}
	
	$getTitle = '';
	if($headingType=='page'){
		$Title = get_the_title();
	}
	$getTitle .='<div class="head-title ">';
		$getTitle .='<'.Tp_Blocks_Helper::validate_html_tag($titleType).' class="heading-title">';
			if($style=='style-1' && $ETPosition=='beforeTitle'){
				$getTitle .= $getExtraTitle;
			}
				if(!empty($limitTgl) && !empty($titleLimit)){
					
                    if (class_exists('Tpgbp_Pro_Blocks_Helper')) {
                        global $repeater_index;
                        $rep_Index = $repeater_index ?? 0;
    
                        if (strpos($Title, 'acf|') !== false || strpos($Title, 'jetengine|') !== false) {
                            if (preg_match('/<span[^>]*data-tpgb-dynamic=(["\'])([^"\']+)\1[^>]*><\/span>/', $Title, $matches) && !empty($matches[2])) {
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
    
                                        $Title = preg_replace(
                                            '/<span[^>]+data-tpgb-dynamic=(["\'])(.*?)\1[^>]*><\/span>/',
                                            esc_html($replacement),
                                            $Title
                                        );
                                    }
                                }
                            }
                        } else {
                            $Title = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($Title);
                        }
                    } else {
                        $Title = $Title;
                    }

					if($titleLimitOn=='char'){												
						$getTitle .= substr($Title,0,$titleCount);
						if(!empty($titleDots) && strlen($Title) > $titleCount){
							$getTitle .= '...';
						}
					}else if($titleLimitOn=='word'){
						$getTitle .= tpgb_limit_words($Title,$titleCount);
						if(!empty($titleDots) && str_word_count($Title) > $titleCount){
							$getTitle .= '...';
						}
					}
				}else{
					$getTitle .= wp_kses_post($Title);
				}
			if($style=='style-1' && $ETPosition=='afterTitle'){
				$getTitle .= $getExtraTitle;
			}
		$getTitle .='</'.Tp_Blocks_Helper::validate_html_tag($titleType).'>';
	$getTitle .='</div>';
	
	$style_8_sep = '';
	$style_8_sep .='<div class="seprator sep-l">';
		$style_8_sep .='<span class="title-sep sep-l"></span>';
		$style_8_sep .='<div class="sep-dot">.</div>';
		$style_8_sep .='<span class="title-sep sep-r"></span>';
	$style_8_sep .='</div>';
	
	$style_3_sep = '';
	$style_3_sep .='<div class="seprator sep-l">';
		$style_3_sep .='<span class="title-sep sep-l"></span>';
		if(isset($attributes['imgName']) && isset($attributes['imgName']['url']) && $attributes['imgName']['url']!=''){
			$imgSrc ='';
			$altText = (isset($attributes['imgName']['alt']) && !empty($attributes['imgName']['alt'])) ? esc_attr($attributes['imgName']['alt']) : ((!empty($attributes['imgName']['title'])) ? esc_attr($attributes['imgName']['title']) : esc_attr__('Image Separator','the-plus-addons-for-block-editor'));
			if(!empty($attributes['imgName']['id'])){
				$imgSrc = wp_get_attachment_image( $attributes['imgName']['id'] , 'full',false, ['alt' => $altText] );
			}else if(!empty($attributes['imgName']['url'])){
				$imgSrc = '<img src="'.esc_url($attributes['imgName']['url']).'"  alt="'.$altText.'" />';
			}
			$style_3_sep .='<div class="sep-mg">';
				$style_3_sep .= $imgSrc;
			$style_3_sep .='</div>';
		}
		$style_3_sep .='<span class="title-sep sep-r"></span>';
	$style_3_sep .='</div>';
	
	$getSubTitle = '';
	if(!empty($subTitle)){
		$getSubTitle .= '<div class="sub-heading ">';
			$getSubTitle .= '<'.Tp_Blocks_Helper::validate_html_tag($subTitleType).' class="heading-sub-title">';
				if(!empty($limitTgl) && !empty($subTitleLimit)){
					$subTitle = (class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($subTitle) : $subTitle;
					if($subTitleLimitOn=='char'){												
						$getSubTitle .= substr($subTitle,0,$subTitleCount);
						if(!empty($subTitleDots) && strlen($subTitle) > $subTitleCount){
							$getSubTitle .= '...';
						}
					}else if($subTitleLimitOn=='word'){
						$getSubTitle .= tpgb_limit_words($subTitle,$subTitleCount);
						if(!empty($subTitleDots) && str_word_count($subTitle) > $subTitleCount){
							$getSubTitle .= '...';
						}
					}
				}else{
					$getSubTitle .= wp_kses_post($subTitle);
				}
			$getSubTitle .= '</'.Tp_Blocks_Helper::validate_html_tag($subTitleType).'>';
			$getSubTitle .= '</div>';
	}
	
    $output .= '<div '.$anchor.' class="tpgb-heading-title tpgb-relative-block heading_style tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' heading-'.esc_attr($style).'">';
    if($advHeadingLink && ( $advHeadingLink != '' )){   
        $output .= '<a '.$link_attr.' href="'.esc_url($advHeadingLink).'" '.$target.' '.$nofollow.'>';
		if($style!='style-9'){
			$output .='<div class="sub-style">';
				if($style=='style-5'){
					$output .='<div class="vertical-divider top"></div>';
				}
				if($subTitlePosition=='onBottonTitle'){
					if(!empty($Title)){
						$output .=$getTitle;
					}
					if($style=='style-3' && !empty($Title)){
						$output .=$style_3_sep;
					}
					if($style=='style-8' && !empty($Title)){
						$output .=$style_8_sep;
					}
				}
				if($subTitlePosition=='onTopTitle'){
					$output .=$getSubTitle;
				}
				
				if($subTitlePosition=='onBottonTitle'){
					$output .=$getSubTitle;
				}
				if($subTitlePosition=='onTopTitle'){
					if(!empty($Title)){
						$output .=$getTitle;
					}
					if($style=='style-3' && !empty($Title)){
						$output .=$style_3_sep;
					}
					if($style=='style-8' && !empty($Title)){
						$output .=$style_8_sep;
					}
				}
				if($style=='style-5'){
					$output .='<div class="vertical-divider bottom"></div>';
				}
			$output .= '</div>';
		}else{
			$splitClass = 'tpgb-split-'.esc_attr($splitType);
			$nSplitType = ($splitType=='lines') ? 'lines,chars' : esc_attr($splitType);
			$annimtypedtaattr = ' data-animsplit-type="'.$nSplitType.'"';
			$htaattr =[
				'effect' => $aniEffect,
				'x' => (!empty($aniPosition) && !empty($aniPosition['tpgbReset']) && !empty($aniPosition['aniPositionX'])) ? (int)$aniPosition['aniPositionX'] : 0,
				'y' => (!empty($aniPosition) && !empty($aniPosition['tpgbReset']) && !empty($aniPosition['aniPositionY'])) ? (int)$aniPosition['aniPositionY'] : 0,

				'scaleX' => (!empty($animationScale) && !empty($animationScale['tpgbReset']) && !empty($animationScale['animationScaleX'])) ? (int)$animationScale['animationScaleX'] : 0,
				'scaleY' => (!empty($animationScale) && !empty($animationScale['tpgbReset']) && !empty($animationScale['animationScaleY'])) ? (int)$animationScale['animationScaleY'] : 0,
				'scaleZ' => (!empty($animationScale) && !empty($animationScale['tpgbReset']) && !empty($animationScale['animationScaleZ'])) ? (int)$animationScale['animationScaleZ'] : 0,
				'rotationX' => (!empty($animationRotate) && !empty($animationRotate['tpgbReset']) && !empty($animationRotate['animationRotateX'])) ? (int)$animationRotate['animationRotateX'] : 0,
				'rotationY' => (!empty($animationRotate) && !empty($animationRotate['tpgbReset']) && !empty($animationRotate['animationRotateY'])) ? (int)$animationRotate['animationRotateY'] : 0,
				'rotationZ' => (!empty($animationRotate) && !empty($animationRotate['tpgbReset']) && !empty($animationRotate['animationRotateZ'])) ? (int)$animationRotate['animationRotateZ'] : 0,

				'opacity' => (!empty($extrOpt) && !empty($extrOpt['tpgbReset']) && !empty($extrOpt['animationOpacity'])) ? (float)$extrOpt['animationOpacity'] : 0,
				'speed' => (!empty($extrOpt) && !empty($extrOpt['tpgbReset']) && !empty($extrOpt['animationSpeed'])) ? (float)$extrOpt['animationSpeed'] : 1,
				'delay' => (!empty($extrOpt) && !empty($extrOpt['tpgbReset']) && !empty($extrOpt['animationDelay'])) ? (float)$extrOpt['animationDelay'] : 0.02,
			];
			$htaattrbunch= 'data-aniattrht = '.json_encode($htaattr);
			$output .='<'.Tp_Blocks_Helper::validate_html_tag($titleType).' class="sub-style '.esc_attr($splitClass).'" '.$annimtypedtaattr.' '.$htaattrbunch.'>';
				$Title = (class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($Title) : $Title;
				$output .= wp_kses_post($Title);
			$output .='</'.Tp_Blocks_Helper::validate_html_tag($titleType).'>';
		}
        $output .= '</a>';
    }else{
        if($style!='style-9'){
			$output .='<div class="sub-style">';
				if($style=='style-5'){
					$output .='<div class="vertical-divider top"></div>';
				}
				if($subTitlePosition=='onBottonTitle'){
					if(!empty($Title)){
						$output .=$getTitle;
					}
					if($style=='style-3' && !empty($Title)){
						$output .=$style_3_sep;
					}
					if($style=='style-8' && !empty($Title)){
						$output .=$style_8_sep;
					}
				}
				if($subTitlePosition=='onTopTitle'){
					$output .=$getSubTitle;
				}
				
				if($subTitlePosition=='onBottonTitle'){
					$output .=$getSubTitle;
				}
				if($subTitlePosition=='onTopTitle'){
					if(!empty($Title)){
						$output .=$getTitle;
					}
					if($style=='style-3' && !empty($Title)){
						$output .=$style_3_sep;
					}
					if($style=='style-8' && !empty($Title)){
						$output .=$style_8_sep;
					}
				}
				if($style=='style-5'){
					$output .='<div class="vertical-divider bottom"></div>';
				}
			$output .= '</div>';
		}else{
			$splitClass = 'tpgb-split-'.esc_attr($splitType);
			$nSplitType = ($splitType=='lines') ? 'lines,chars' : esc_attr($splitType);
			$annimtypedtaattr = ' data-animsplit-type="'.$nSplitType.'"';
			$htaattr =[
				'effect' => $aniEffect,
				'x' => (!empty($aniPosition) && !empty($aniPosition['tpgbReset']) && !empty($aniPosition['aniPositionX'])) ? (int)$aniPosition['aniPositionX'] : 0,
				'y' => (!empty($aniPosition) && !empty($aniPosition['tpgbReset']) && !empty($aniPosition['aniPositionY'])) ? (int)$aniPosition['aniPositionY'] : 0,

				'scaleX' => (!empty($animationScale) && !empty($animationScale['tpgbReset']) && !empty($animationScale['animationScaleX'])) ? (int)$animationScale['animationScaleX'] : 0,
				'scaleY' => (!empty($animationScale) && !empty($animationScale['tpgbReset']) && !empty($animationScale['animationScaleY'])) ? (int)$animationScale['animationScaleY'] : 0,
				'scaleZ' => (!empty($animationScale) && !empty($animationScale['tpgbReset']) && !empty($animationScale['animationScaleZ'])) ? (int)$animationScale['animationScaleZ'] : 0,
				'rotationX' => (!empty($animationRotate) && !empty($animationRotate['tpgbReset']) && !empty($animationRotate['animationRotateX'])) ? (int)$animationRotate['animationRotateX'] : 0,
				'rotationY' => (!empty($animationRotate) && !empty($animationRotate['tpgbReset']) && !empty($animationRotate['animationRotateY'])) ? (int)$animationRotate['animationRotateY'] : 0,
				'rotationZ' => (!empty($animationRotate) && !empty($animationRotate['tpgbReset']) && !empty($animationRotate['animationRotateZ'])) ? (int)$animationRotate['animationRotateZ'] : 0,

				'opacity' => (!empty($extrOpt) && !empty($extrOpt['tpgbReset']) && !empty($extrOpt['animationOpacity'])) ? (float)$extrOpt['animationOpacity'] : 0,
				'speed' => (!empty($extrOpt) && !empty($extrOpt['tpgbReset']) && !empty($extrOpt['animationSpeed'])) ? (float)$extrOpt['animationSpeed'] : 1,
				'delay' => (!empty($extrOpt) && !empty($extrOpt['tpgbReset']) && !empty($extrOpt['animationDelay'])) ? (float)$extrOpt['animationDelay'] : 0.02,
			];
			$htaattrbunch= 'data-aniattrht = '.json_encode($htaattr);
			$output .='<'.Tp_Blocks_Helper::validate_html_tag($titleType).' class="sub-style '.esc_attr($splitClass).'" '.$annimtypedtaattr.' '.$htaattrbunch.'>';
				$Title = (class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($Title) : $Title;
				$output .= wp_kses_post($Title);
			$output .='</'.Tp_Blocks_Helper::validate_html_tag($titleType).'>';
		}
    }
	$output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_heading_title() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_heading_title_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_heading_title' );