<?php
/* Block : Info Box
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_infobox_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	
	// $pattern = '/\btpgb-block-'.esc_attr($block_id).'/';
	// if (preg_match($pattern, $content)) {
	// 	if( class_exists('Tpgb_Blocks_Global_Options') ){
    //         $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
    //         $content = $global_blocks::block_row_conditional_render($attributes,$content);
    //     }
	// 	return $content;
	// }

	$layoutType = (!empty($attributes['layoutType'])) ? $attributes['layoutType'] : 'listing';
	$styleType = (!empty($attributes['styleType'])) ? $attributes['styleType'] : 'style-1';
	$extBtnshow = (!empty($attributes['extBtnshow'])) ? $attributes['extBtnshow'] : false ;
	$verticalCenter = (!empty($attributes['verticalCenter'])) ? $attributes['verticalCenter'] : false;
	$sideImgBorder = (!empty($attributes['sideImgBorder'])) ? $attributes['sideImgBorder'] : false;
	$displayBorder = (!empty($attributes['displayBorder'])) ? $attributes['displayBorder'] : false;
	$dispPinText = (!empty($attributes['dispPinText'])) ? $attributes['dispPinText'] : false;
	$pinText = (!empty($attributes['pinText'])) ? $attributes['pinText'] : 'New';
	$IBoxLinkTgl = (!empty($attributes['IBoxLinkTgl'])) ? $attributes['IBoxLinkTgl'] : false;
	$IBoxLink = (!empty($attributes['IBoxLink']['url'])) ? $attributes['IBoxLink']['url'] : '';
	$target = (!empty($attributes['IBoxLink']['target'])) ? ' target="_blank" ' : '';
	$nofollow = (!empty($attributes['IBoxLink']['nofollow'])) ? ' rel="nofollow" ' : '';
	$iconType = (!empty($attributes['iconType'])) ? $attributes['iconType'] : 'icon';
	$iconOverlay = (!empty($attributes['iconOverlay'])) ? $attributes['iconOverlay'] : false;
	$imgOverlay = (!empty($attributes['imgOverlay'])) ? $attributes['imgOverlay'] : false;
	$iconShine = (!empty($attributes['iconShine'])) ? $attributes['iconShine'] : false;
	$IconName = (!empty($attributes['IconName'])) ? $attributes['IconName'] : '';
    $TextIcon = (!empty($attributes['textIcon'])) ? $attributes['textIcon'] : '';
	$imageName = (!empty($attributes['imageName']['url'])) ? $attributes['imageName'] : '';
	$imageSize = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] : 'full';
	$Title = (!empty($attributes['Title'])) ? $attributes['Title'] : '';
	$Description = (!empty($attributes['Description'])) ? $attributes['Description'] : '';
	$iconstyleType = (!empty($attributes['iconstyleType'])) ? $attributes['iconstyleType'] : 'none';
	$contenthoverEffect = (!empty($attributes['contenthoverEffect'])) ? $attributes['contenthoverEffect'] : '';
	$carouselId = (!empty($attributes['carouselId'])) ? $attributes['carouselId'] : '';
	
	$svgIcon = (!empty($attributes['svgIcon'])) ? $attributes['svgIcon'] : '';
	$svgDraw = (!empty($attributes['svgDraw'])) ? $attributes['svgDraw'] : 'delayed';
	$svgstroColor = (!empty($attributes['svgstroColor'])) ? $attributes['svgstroColor'] : '';
	$svgfillColor = (!empty($attributes['svgfillColor'])) ? $attributes['svgfillColor'] : 'none';
	$svgstroHoverColor = (!empty($attributes['svgstroHoverColor'])) ? $attributes['svgstroHoverColor'] : '';
	$svgfillHoverColor = (!empty($attributes['svgfillHoverColor'])) ? $attributes['svgfillHoverColor'] : 'none';
	$svgDura = (!empty($attributes['svgDura'])) ? $attributes['svgDura'] : 90;

	$titleType = (!empty($attributes['titleType'])) ? $attributes['titleType'] : 'div';
	$descType = (!empty($attributes['descType'])) ? $attributes['descType'] : 'div';

	$showArrows = (!empty($attributes['showArrows'])) ? $attributes['showArrows'] : [ 'md' => false ];
	$arrowsStyle = (!empty($attributes['arrowsStyle'])) ? $attributes['arrowsStyle'] : 'style-1';
	$arrowsPosition = (!empty($attributes['arrowsPosition'])) ? $attributes['arrowsPosition'] : 'top-right';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$count = 0;
	$Sliderclass = $arrowCss = '';
	$carousel_settings = '';
	if($layoutType=='carousel'){
		$carousel_settings = Tp_Blocks_Helper::carousel_settings( $attributes );
		$carousel_settings = 'data-splide=\'' . json_encode($carousel_settings) . '\'';
				
		$Sliderclass .= 'tpgb-carousel splide';
		$Sliderclass .= Tp_Blocks_Helper::tpgb_carousel_arrowdot_class($attributes);
		$arrowCss = Tp_Blocks_Helper::tpgb_carousel_arrow_css( $showArrows , $block_id );

		if(!empty($carouselId)){
			$carousel_settings .=' id="tpca-'.esc_attr($carouselId).'"';
			$carousel_settings .=' data-id="tpca-'.esc_attr($carouselId).'"';
			$carousel_settings .=' data-connection="tptab_'.esc_attr($carouselId).'"';
		}
	}
	
	$imgSrc ='';
	$altText = (isset($imageName['alt']) && !empty($imageName['alt'])) ? esc_attr($imageName['alt']) : ((!empty($imageName['title'])) ? esc_attr($imageName['title']) : esc_attr__('Info Box','the-plus-addons-for-block-editor'));
	if(!empty($imageName) && !empty($imageName['id'])){
		$imgSrc = wp_get_attachment_image($imageName['id'] , $imageSize, false, ['class' => 'service-icon tpgb-trans-linear', 'alt'=> $altText]);
	}else if(!empty($imageName['url'])){
		$imgSrc = '<img src="'.esc_url($imageName['url']).'" class="service-icon tpgb-trans-linear" alt='.$altText.' />';
	}
	if(class_exists('Tpgbp_Pro_Blocks_Helper')){
		$IBoxLink = (isset($attributes['IBoxLink']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['IBoxLink']) : (!empty($attributes['IBoxLink']['url']) ? $attributes['IBoxLink']['url'] : '');
	}
	
	$vcenter='';
	if(!empty($verticalCenter)){
		$vcenter = 'vertical-center';
	}
	
	$sib='';
	if($styleType=='style-1' || $styleType=='style-2'){
		if($iconType!='none' && !empty($sideImgBorder)){
			$sib = 'service-img-border';
		}
	}
	
	$icnOvrlay='';
	if(($styleType=='style-1' || $styleType=='style-2' || $styleType=='style-3') && (!empty($iconOverlay) || !empty($imgOverlay))){
		$icnOvrlay='icon-overlay';
	}
	
	$iconShineShow='';
	if(!empty($iconShine)){
		$iconShineShow='icon-shine-show';
	}
	
	$mlr16='';
	if($styleType=='style-1' && $iconType!='none'){ 
			$mlr16 = 'm-r-16 style-1 '; 
	}else if($styleType=='style-2' && $iconType!='none'){ 
			$mlr16 = 'm-l-16 style-2 ';
	}else if($styleType=='style-4' && $iconType!='none'){ 
			$mlr16 = 'm-r-16';
	}else if($styleType=='style-5' && $iconType!='none'){ 
			$mlr16 = 'service-bg-5';
	}else if($styleType=='style-6' && $iconType!='none'){ 
			$mlr16 = '';
	}
	
	$getIcon = '';
	if(!empty($iconType)){
			$getIcon .='<div class="info-icon-content">';
				if($iconType!='none' && !empty($dispPinText)){
					$getIcon .='<div class="info-pin-text tpgb-trans-easeinout">'.wp_kses_post($pinText).'</div>';
				}
				$getIcon .='<div class="service-icon-wrap tpgb-trans-linear">';
				if($iconType=='icon'){
					$getIcon .='<span class="service-icon tpgb-trans-linear '.esc_attr($iconShineShow).' icon-'.esc_attr($iconstyleType).'">';
					$getIcon .='<i class="'.esc_attr($IconName).'"></i>';
					$getIcon .='</span>';
				}else if($iconType=='image'){
					$getIcon .= $imgSrc;
				}else if($iconType=='svg' && !empty($svgIcon) && !empty($svgIcon['url'])){
					$getIcon .= '<div class="tpgb-draw-svg tpgb-trans-linear" data-id="service-svg-'.esc_attr($block_id).'" data-type="'.esc_attr($svgDraw).'" data-duration="'.esc_attr($svgDura).'" data-stroke="'.esc_attr($svgstroColor).'" data-fillColor="'.esc_attr($svgfillColor).'" data-strokeColorHover="'.esc_attr($svgstroHoverColor).'" data-fillColorHover="'.esc_attr($svgfillHoverColor).'" data-fillEnable="yes">';
						$getIcon .= '<object class="info-box-svg" id="service-svg-'.esc_attr($block_id).'" type="image/svg+xml" data="'.esc_url($svgIcon['url']).'" aria-label="'.esc_attr__('icon','the-plus-addons-for-block-editor').'"></object>';
					$getIcon .= '</div>';
				}else if($iconType=='text' && !empty($TextIcon)){
					$getIcon .='<span class="tpgb-icon-wrap-text">'.esc_attr($TextIcon).'</span>';
				}
				$getIcon .='</div>';
			$getIcon .='</div>';
	}
	
	$getTitle = '';
	if(!empty($Title)){
		if(!$IBoxLinkTgl && !empty($IBoxLink)){
			$link_attr = Tp_Blocks_Helper::add_link_attributes($attributes['IBoxLink']);
			$getTitle .='<a href="'.esc_url($IBoxLink).'" class="service-title tpgb-trans-linear" '.$target.' '.$nofollow.' '.$link_attr.'>'.wp_kses_post($Title).'</a>';
		}else{
			$getTitle .='<'.Tp_Blocks_Helper::validate_html_tag($titleType).' class="service-title tpgb-trans-linear">';
				$getTitle .= wp_kses_post($Title);
			$getTitle .='</'.Tp_Blocks_Helper::validate_html_tag($titleType).'>';
		}
	}
	
	$getDesc = '';
	$getDesc .='<'.Tp_Blocks_Helper::validate_html_tag($descType).' class="service-desc tpgb-trans-linear">';
		$getDesc .= wp_kses_post($Description);
	$getDesc .='</'.Tp_Blocks_Helper::validate_html_tag($descType).'>';
	
	$getBorder='';
	$getBorder .='<div class="service-border"></div>';
	
	$getbutton = '';
	$getbutton .= Tpgb_Blocks_Global_Options::load_plusButton_saves($attributes);

	$cnt_hvr_class = $contenthoverEffect;
		
	if($contenthoverEffect == 'bounce_in'){
		$cnt_hvr_class = 'bounce-in';
	}
	if($contenthoverEffect == 'radial'){
		$cnt_hvr_class = 'shadow_radial';
	}
	
	$getInfoBox='';
	$getInfoBox .='<div class="info-box-inner tpgb-trans-linear tpgb_cnt_hvr_effect tpgb-relative-block tp-info-nc cnt_hvr_'.esc_attr($cnt_hvr_class).'">';
				if(!empty($IBoxLinkTgl) && !empty($IBoxLink)){
					$link_attr = Tp_Blocks_Helper::add_link_attributes($attributes['IBoxLink']);
					$getInfoBox .='<a href="'.esc_url($IBoxLink).'" class="info-box-bg-box tpgb-trans-linear '.esc_attr($icnOvrlay).'" '.$target.' '.$nofollow.' '.$link_attr.'>';
				}else{
					$getInfoBox .='<div class="info-box-bg-box tpgb-trans-linear '.esc_attr($icnOvrlay).'">';
				}
					if($styleType=='style-1'){
						$getInfoBox .='<div class="service-media text-left '.esc_attr($vcenter).'">';
							if($iconType!='none'){
								$getInfoBox .='<div class="'.esc_attr($mlr16).' '.esc_attr($sib).'">';
									$getInfoBox .=$getIcon;
								$getInfoBox .='</div>';
									
							}
							$getInfoBox .='<div class="service-content">';
								$getInfoBox .=$getTitle;
									if(!empty($displayBorder)){
										$getInfoBox .=$getBorder;
									}
								$getInfoBox .=$getDesc;
									if(!empty($extBtnshow)){
										$getInfoBox .='<div class="infobox-btn-block ">'.$getbutton.'</div>';
									}
							$getInfoBox .= '</div>';
						$getInfoBox .= '</div>';
					}
					if($styleType=='style-2'){
						$getInfoBox .='<div class="service-media text-right '.esc_attr($vcenter).'">';
							$getInfoBox .='<div class="service-content">';
								$getInfoBox .=$getTitle;
									if(!empty($displayBorder)){
										$getInfoBox .=$getBorder;
									}
								$getInfoBox .=$getDesc;
									if(!empty($extBtnshow)){
										$getInfoBox .='<div class="infobox-btn-block ">'.$getbutton.'</div>';
									}
							$getInfoBox .= '</div>';
							if($iconType!='none'){
								$getInfoBox .='<div class="'.esc_attr($mlr16).' '.esc_attr($sib).'">';
									$getInfoBox .=$getIcon;
								$getInfoBox .='</div>';
							}
						$getInfoBox .= '</div>';
					}
					if($styleType=='style-3'){
						$getInfoBox .='<div class="text-alignment">';
							$getInfoBox .='<div class="style-3">';
								if($iconType!='none'){
									$getInfoBox .=$getIcon;
								}
								$getInfoBox .=$getTitle;
								if(!empty($displayBorder)){
									$getInfoBox .=$getBorder;
								}
								$getInfoBox .=$getDesc;
								if(!empty($extBtnshow)){
									$getInfoBox .='<div class="infobox-btn-block ">'.$getbutton.'</div>';
								}
							$getInfoBox .= '</div>';
						$getInfoBox .= '</div>';
					}
					if($styleType=='style-4'){
						$getInfoBox .='<div class="service-media text-left '.esc_attr($vcenter).'">';
							if($iconType!='none'){
								$getInfoBox .='<div class="'.esc_attr($mlr16).' '.esc_attr($sib).'">';
									$getInfoBox .=$getIcon;
								$getInfoBox .='</div>';
							}
							$getInfoBox .='<div class="service-content">'.$getTitle.'</div>';
						$getInfoBox .= '</div>';
							if(!empty($displayBorder)){
								$getInfoBox .=$getBorder;
							}
							$getInfoBox .=$getDesc;
							if(!empty($extBtnshow)){
								$getInfoBox .='<div class="infobox-btn-block ">'.$getbutton.'</div>';
							}
					}
					if($styleType=='style-5'){
						$getInfoBox .='<div class="service-media  text-left">';
							if($iconType!='none'){
								$getInfoBox .='<div class="'.esc_attr($mlr16).' '.esc_attr($sib).'">';
									$getInfoBox .=$getIcon;
								$getInfoBox .='</div>';
							}
							$getInfoBox .='<div class="style-5-service-content">';
								$getInfoBox .=$getTitle;
								if(!empty($displayBorder)){
									$getInfoBox .=$getBorder;
								}
								$getInfoBox .=$getDesc;
								if(!empty($extBtnshow)){
									$getInfoBox .='<div class="infobox-btn-block ">'.$getbutton.'</div>';
								}
							$getInfoBox .= '</div>';
						$getInfoBox .= '</div>';
					}
					if($styleType=='style-6'){
						$getInfoBox .='<div class="style-6 text-center">';
							$getInfoBox .='<div class="info-box-all">';
								$getInfoBox .='<div class="info-box-wrapper">';
									$getInfoBox .='<div class="info-box-content">';
										$getInfoBox .='<div class="info-box-icon-img">';
										if($iconType!='none'){
											$getInfoBox .='<div class="'.esc_attr($mlr16).' '.esc_attr($sib).'">';
												$getInfoBox .=$getIcon;
											$getInfoBox .='</div>';
										}
										$getInfoBox .='</div>';
										$getInfoBox .=$getTitle;
										$getInfoBox .='<div class="info-box-title-hide">'.wp_kses_post($Title).'</div>';
											if(!empty($displayBorder)){
												$getInfoBox .=$getBorder;
											}
											$getInfoBox .=$getDesc;
											if(!empty($extBtnshow)){
												$getInfoBox .='<div class="infobox-btn-block ">'.$getbutton.'</div>';
											}
									$getInfoBox .= '</div>';
								$getInfoBox .= '</div>';
							$getInfoBox .= '</div>';
						$getInfoBox .= '</div>';
					}
				
				if(!empty($IBoxLinkTgl) && !empty($IBoxLink)){
					$getInfoBox .= '</a>';
				}else{
					$getInfoBox .= '</div>';
				}
				
				$getInfoBox .= '<div class="infobox-overlay-color tpgb-trans-linear"></div>';
				
			$getInfoBox .= '</div>';
	
    $output .= '<div class="tpgb-infobox tpgb-relative-block tpgb-trans-linear tpgb-block-'.esc_attr($block_id).' '.esc_attr($Sliderclass).' info-box-'.esc_attr($styleType).' '.esc_attr($blockClass).'" '.$carousel_settings.'>';
		if($layoutType == 'carousel'){
			if( ( isset($showArrows['md']) && !empty($showArrows['md']) ) || ( isset($showArrows['sm']) && !empty($showArrows['sm']) ) || ( isset($showArrows['xs']) && !empty($showArrows['xs']) ) ){
				$output .= Tp_Blocks_Helper::tpgb_carousel_arrow($arrowsStyle, $arrowsPosition);
			}
			$output .= '<div class="post-loop-inner splide__track">';
				$output .= '<div class="splide__list">';
					$output .= tpgb_getCInfobox($attributes);
				$output .= '</div>';
			$output .= '</div>';
		}else{
			$output .='<div class="post-inner-loop ">';
				$output .=$getInfoBox;
			$output .= '</div>';
		}
    $output .= '</div>';
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	if($layoutType=='carousel' && !empty($arrowCss)){
		$output .= $arrowCss;
	}
    return $output;
}

function tpgb_getCInfobox($attributes){
	$styleType = (!empty($attributes['styleType'])) ? $attributes['styleType'] : 'style-1';
	$iboxcarousel = (!empty($attributes['iboxcarousel'])) ? $attributes['iboxcarousel'] : [];
	$carouselBtn = (!empty($attributes['carouselBtn'])) ? $attributes['carouselBtn'] : false;
	$carBtnStyle = (!empty($attributes['carBtnStyle'])) ? $attributes['carBtnStyle'] : 'style-7';
	$carBtnIconType = (!empty($attributes['carBtnIconType'])) ? $attributes['carBtnIconType'] : 'none';
	$carBtnIconName = (!empty($attributes['carBtnIconName'])) ? $attributes['carBtnIconName'] : '';
	$carBtnIconPosition = (!empty($attributes['carBtnIconPosition'])) ? $attributes['carBtnIconPosition'] : 'after';

	$verticalCenter = (!empty($attributes['verticalCenter'])) ? $attributes['verticalCenter'] : false;
	$sideImgBorder = (!empty($attributes['sideImgBorder'])) ? $attributes['sideImgBorder'] : false;
	$displayBorder = (!empty($attributes['displayBorder'])) ? $attributes['displayBorder'] : false;

	$iconOverlay = (!empty($attributes['iconOverlay'])) ? $attributes['iconOverlay'] : false;
	$imgOverlay = (!empty($attributes['imgOverlay'])) ? $attributes['imgOverlay'] : false;
	$iconShine = (!empty($attributes['iconShine'])) ? $attributes['iconShine'] : false;

	$svgDraw = (!empty($attributes['svgDraw'])) ? $attributes['svgDraw'] : 'delayed';
	$svgstroColor = (!empty($attributes['svgstroColor'])) ? $attributes['svgstroColor'] : '';
	$svgfillColor = (!empty($attributes['svgfillColor'])) ? $attributes['svgfillColor'] : 'none';
	$svgDura = (!empty($attributes['svgDura'])) ? $attributes['svgDura'] : 90;

	$titleType = (!empty($attributes['titleType'])) ? $attributes['titleType'] : 'div';
	$descType = (!empty($attributes['descType'])) ? $attributes['descType'] : 'div';

	$iconstyleType = (!empty($attributes['iconstyleType'])) ? $attributes['iconstyleType'] : 'none';
	$contenthoverEffect = (!empty($attributes['contenthoverEffect'])) ? $attributes['contenthoverEffect'] : '';

	$vcenter='';
	if(!empty($verticalCenter)){
		$vcenter = 'vertical-center';
	}
	
	$icnOvrlay='';
	if(($styleType=='style-1' || $styleType=='style-2' || $styleType=='style-3') && (!empty($iconOverlay) || !empty($imgOverlay))){
		$icnOvrlay='icon-overlay';
	}
	
	$iconShineShow='';
	if(!empty($iconShine)){
		$iconShineShow='icon-shine-show';
	}

	$cnt_hvr_class = $contenthoverEffect;
		
	if($contenthoverEffect == 'bounce_in'){
		$cnt_hvr_class = 'bounce-in';
	}
	if($contenthoverEffect == 'radial'){
		$cnt_hvr_class = 'shadow_radial';
	}
	$count = 0;

	$getCInfobox = '';
	if(!empty($iboxcarousel)){
		foreach ( $iboxcarousel as $index => $item ) :

			$count++;

			$mlr16='';
			if($styleType=='style-1' && $item['iconType']!='none'){ 
				$mlr16 = 'm-r-16 style-1 '; 
			}else if($styleType=='style-2' && $item['iconType']!='none'){ 
				$mlr16 = 'm-l-16 style-2 ';
			}else if($styleType=='style-4' && $item['iconType']!='none'){ 
				$mlr16 = 'm-r-16';
			}else if($styleType=='style-5' && $item['iconType']!='none'){ 
				$mlr16 = 'service-bg-5';
			}else if($styleType=='style-6' && $item['iconType']!='none'){ 
				$mlr16 = '';
			}

			$sib='';
			if($styleType=='style-1' || $styleType=='style-2'){
				if($item['iconType']!='none' && !empty($sideImgBorder)){
					$sib = 'service-img-border';
				}
			}

			$getCTitle = '';
			$gttTitle = (!empty($item['Title'])) ? $item['Title'] : '';
			if(!empty($item['Title'])){
				$getCTitle .='<'.Tp_Blocks_Helper::validate_html_tag($titleType).' class="service-title tpgb-trans-linear">';
					$getCTitle .= wp_kses_post($item['Title']);
				$getCTitle .='</'.Tp_Blocks_Helper::validate_html_tag($titleType).'>';
			}
			
			$getCDesc = '';
			if(!empty($item['Description'])){
				$getCDesc .='<'.Tp_Blocks_Helper::validate_html_tag($descType).' class="service-desc tpgb-trans-linear">';
					$getCDesc .= wp_kses_post($item['Description']);
				$getCDesc .='</'.Tp_Blocks_Helper::validate_html_tag($descType).'>';
			}

			$getCBorder='';
			$getCBorder .='<div class="service-border"></div>';

			$imgCSrc ='';
			$imageName = (!empty($item['imageName']['url'])) ? $item['imageName'] : '';
			$imageSize = (!empty($item['imageSize'])) ? $item['imageSize'] : 'full';
			$altText = (isset($imageName['alt']) && !empty($imageName['alt'])) ? esc_attr($imageName['alt']) : ((!empty($imageName['title'])) ? esc_attr($imageName['title']) : esc_attr__('Info Box','the-plus-addons-for-block-editor'));
			if(!empty($imageName) && !empty($imageName['id'])){
				$imgCSrc = wp_get_attachment_image($imageName['id'] , $imageSize, false, ['class' => 'service-icon tpgb-trans-linear', 'alt'=> $altText]);
			}else if(!empty($imageName['url'])){
				$imgCSrc = '<img src="'.esc_url($imageName['url']).'" class="service-icon tpgb-trans-linear" alt="'.$altText.'"/>';
			}
			$getCIcon = '';
			if(!empty($item['iconType'])){
				$getCIcon .='<div class="info-icon-content">';
					if($item['iconType']!='none' && !empty($item['dispPinText'])){
						$getCIcon .='<div class="info-pin-text tpgb-trans-easeinout">'.wp_kses_post($item['pinText']).'</div>';
					}
					$getCIcon .='<div class="service-icon-wrap tpgb-trans-linear">';
						if($item['iconType']=='icon'){
							$getCIcon .='<span class="service-icon tpgb-trans-linear '.esc_attr($iconShineShow).' icon-'.esc_attr($iconstyleType).'">';
							$getCIcon .='<i class="'.esc_attr($item['IconName']).'"></i>';
							$getCIcon .='</span>';
						}else if($item['iconType']=='image'){
							$getCIcon .= $imgCSrc;
						}else if($item['iconType']=='svg' && !empty($item['svgIcon']) && !empty($item['svgIcon']['url'])){
							$getCIcon .= '<div class="tpgb-draw-svg tpgb-trans-linear" data-id="service-svg-'.esc_attr($item['_key']).'" data-type="'.esc_attr($svgDraw).'" data-duration="'.esc_attr($svgDura).'" data-stroke="'.esc_attr($svgstroColor).'" data-fillColor="'.esc_attr($svgfillColor).'" data-strokeColorHover="'.esc_attr($svgstroHoverColor).'" data-fillColorHover="'.esc_attr($svgfillHoverColor).'" data-fillEnable="yes">';      
								$getCIcon .= '<object id="service-svg-'.esc_attr($item['_key']).'" class="info-box-svg" type="image/svg+xml" data="'.esc_url($item['svgIcon']['url']).'" aria-label="'.esc_attr__('icon','the-plus-addons-for-block-editor').'"></object>';
							$getCIcon .= '</div>';
						}else if($item['iconType']=='text' && !empty($item['textIcon'])){
                            $getCIcon .='<span class="tpgb-icon-wrap-text">'.esc_attr($item['textIcon']).'</span>';    
                        }
					$getCIcon .='</div>';
				$getCIcon .='</div>';
			}

			$getCbutton = '';
			if(!empty($carouselBtn)){
				$btn_attr = Tp_Blocks_Helper::add_link_attributes($item['btnUrl']);
				$btnText = (!empty($item['btnText'])) ? $item['btnText'] : '';

				$btnUrl = (!empty($item['btnUrl'])) ? $item['btnUrl'] : '';
				$target = (!empty($btnUrl['target'])) ? ' target="_blank" ' : '';
				$nofollow = (!empty($btnUrl['nofollow'])) ? ' rel="nofollow" ' : '';

				$getBtnText = '<div class="btn-text">'.wp_kses_post($btnText).'</div>';
				
				$getCbutton .= '<div class="tpgb-adv-button button-'.esc_attr($carBtnStyle).'">';
					$getCbutton .= '<a href="'.esc_url($btnUrl['url']).'" class="button-link-wrap" role="button" '.$target.' '.$nofollow.' '.$btn_attr.'>';
					if($carBtnStyle == 'style-8'){
						if($carBtnIconPosition == 'before'){
							if($carBtnIconType == 'icon'){
								$getCbutton .= '<span class="btn-icon  button-'.esc_attr($carBtnIconPosition).'">';
									$getCbutton .= '<i class="'.esc_attr($carBtnIconName).'"></i>';
								$getCbutton .= '</span>';
							}
							$getCbutton .= $getBtnText;
						}
						if($carBtnIconPosition == 'after'){
							$getCbutton .= $getBtnText;
							if($carBtnIconType == 'icon'){
								$getCbutton .= '<span class="btn-icon  button-'.esc_attr($carBtnIconPosition).'">';
									$getCbutton .= '<i class="'.esc_attr($carBtnIconName).'"></i>';
								$getCbutton .= '</span>';
							}
						}
					}
					if($carBtnStyle == 'style-7' || $carBtnStyle == 'style-9' ){
						$getCbutton .= $getBtnText;
						
						$getCbutton .= '<span class="button-arrow">';
						if($carBtnStyle == 'style-7'){
							$getCbutton .= '<span class="btn-right-arrow"><i class="fas fa-chevron-right"></i></span>';
						}
						if($carBtnStyle == 'style-9'){
							$getCbutton .= '<i class="btn-show fas fa-chevron-right"></i>';
							$getCbutton .= '<i class="btn-hide fas fa-chevron-right"></i>';
						}
						$getCbutton .= '</span>';
					}
					$getCbutton .= '</a>';
				$getCbutton .= '</div>';
			}

			$getCInfobox .='<div class="splide__slide info-box-inner tpgb-trans-linear tpgb_cnt_hvr_effect tpgb-relative-block tp-info-nc cnt_hvr_'.esc_attr($cnt_hvr_class).' tp-repeater-item-'.esc_attr($item['_key']).'" data-index="'.esc_attr($count).'">';
				
				$getCInfobox .='<div class="info-box-bg-box tpgb-trans-linear '.esc_attr($icnOvrlay).'">';
					if($styleType=='style-1'){
						$getCInfobox .='<div class="service-media text-left '.esc_attr($vcenter).'">';
							if($item['iconType']!='none'){
								$getCInfobox .='<div class="'.esc_attr($mlr16).' '.esc_attr($sib).'">';
									$getCInfobox .=$getCIcon;
								$getCInfobox .='</div>';
									
							}
							$getCInfobox .='<div class="service-content">';
								$getCInfobox .=$getCTitle;
									if(!empty($displayBorder)){
										$getCInfobox .=$getCBorder;
									}
								$getCInfobox .=$getCDesc;
									if(!empty($carouselBtn)){
										$getCInfobox .='<div class="infobox-btn-block ">'.$getCbutton.'</div>';
									}
							$getCInfobox .= '</div>';
						$getCInfobox .= '</div>';
					}
					if($styleType=='style-2'){
						$getCInfobox .='<div class="service-media text-right '.esc_attr($vcenter).'">';
							$getCInfobox .='<div class="service-content">';
								$getCInfobox .=$getCTitle;
									if(!empty($displayBorder)){
										$getCInfobox .=$getCBorder;
									}
								$getCInfobox .=$getCDesc;
									if(!empty($carouselBtn)){
										$getCInfobox .='<div class="infobox-btn-block ">'.$getCbutton.'</div>';
									}
							$getCInfobox .= '</div>';
							if($item['iconType']!='none'){
								$getCInfobox .='<div class="'.esc_attr($mlr16).' '.esc_attr($sib).'">';
									$getCInfobox .=$getCIcon;
								$getCInfobox .='</div>';
							}
						$getCInfobox .= '</div>';
					}
					if($styleType=='style-3'){
						$getCInfobox .='<div class="text-alignment">';
							$getCInfobox .='<div class="style-3">';
								if($item['iconType']!='none'){
									$getCInfobox .=$getCIcon;
								}
								$getCInfobox .=$getCTitle;
								if(!empty($displayBorder)){
									$getCInfobox .=$getCBorder;
								}
								$getCInfobox .=$getCDesc;
								if(!empty($carouselBtn)){
									$getCInfobox .='<div class="infobox-btn-block ">'.$getCbutton.'</div>';
								}
							$getCInfobox .= '</div>';
						$getCInfobox .= '</div>';
					}
					if($styleType=='style-4'){
						$getCInfobox .='<div class="service-media text-left '.esc_attr($vcenter).'">';
							if($item['iconType']!='none'){
								$getCInfobox .='<div class="'.esc_attr($mlr16).' '.esc_attr($sib).'">';
									$getCInfobox .=$getCIcon;
								$getCInfobox .='</div>';
							}
							$getCInfobox .='<div class="service-content">'.$getCTitle.'</div>';
						$getCInfobox .= '</div>';
							if(!empty($displayBorder)){
								$getCInfobox .=$getCBorder;
							}
							$getCInfobox .=$getCDesc;
							if(!empty($carouselBtn)){
								$getCInfobox .='<div class="infobox-btn-block ">'.$getCbutton.'</div>';
							}
					}
					if($styleType=='style-5'){
						$getCInfobox .='<div class="service-media  text-left">';
							if($item['iconType']!='none'){
								$getCInfobox .='<div class="'.esc_attr($mlr16).' '.esc_attr($sib).'">';
									$getCInfobox .=$getCIcon;
								$getCInfobox .='</div>';
							}
							$getCInfobox .='<div class="style-5-service-content">';
								$getCInfobox .=$getCTitle;
								if(!empty($displayBorder)){
									$getCInfobox .=$getCBorder;
								}
								$getCInfobox .=$getCDesc;
								if(!empty($carouselBtn)){
									$getCInfobox .='<div class="infobox-btn-block ">'.$getCbutton.'</div>';
								}
							$getCInfobox .= '</div>';
						$getCInfobox .= '</div>';
					}
					if($styleType=='style-6'){
						$getCInfobox .='<div class="style-6 text-center">';
							$getCInfobox .='<div class="info-box-all">';
								$getCInfobox .='<div class="info-box-wrapper">';
									$getCInfobox .='<div class="info-box-content">';
										$getCInfobox .='<div class="info-box-icon-img">';
										if($item['iconType']!='none'){
											$getCInfobox .='<div class="'.esc_attr($mlr16).' '.esc_attr($sib).'">';
												$getCInfobox .=$getCIcon;
											$getCInfobox .='</div>';
										}
										$getCInfobox .='</div>';
										$getCInfobox .=$getCTitle;
										$getCInfobox .='<div class="info-box-title-hide">'.wp_kses_post($gttTitle).'</div>';
											if(!empty($displayBorder)){
												$getCInfobox .=$getCBorder;
											}
											$getCInfobox .=$getCDesc;
											if(!empty($carouselBtn)){
												$getCInfobox .='<div class="infobox-btn-block ">'.$getCbutton.'</div>';
											}
									$getCInfobox .= '</div>';
								$getCInfobox .= '</div>';
							$getCInfobox .= '</div>';
						$getCInfobox .= '</div>';
					}
				
				$getCInfobox .= '</div>';
				
				$getCInfobox .= '<div class="infobox-overlay-color tpgb-trans-linear"></div>';
				
			$getCInfobox .= '</div>';

		endforeach;
	}

	return $getCInfobox;
}

/**
 * Render for the server-side
 */
function tpgb_tp_infobox() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_infobox_render_callback', true, true, true);
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_infobox' );