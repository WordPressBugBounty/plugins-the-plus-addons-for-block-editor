<?php
/* Block : Flip Box
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_flipbox_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	
	$pattern = '/\btpgb-block-'.esc_attr($block_id).'/';
	if (preg_match($pattern, $content)) {
		if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attributes,$content);
        }
		return $content;
	}

	$layoutType = (!empty($attributes['layoutType'])) ? $attributes['layoutType'] : 'listing';
	$flipType = (!empty($attributes['flipType'])) ? $attributes['flipType'] : 'horizontal';
	$iconType = (!empty($attributes['iconType'])) ? $attributes['iconType'] : 'icon';
	$iconStore = (!empty($attributes['iconStore'])) ? $attributes['iconStore'] : '';
	$iconStyle = (!empty($attributes['iconStyle'])) ? $attributes['iconStyle'] : 'none';
	$svgIcon = (!empty($attributes['svgIcon'])) ? $attributes['svgIcon'] : '';
	$imagestore = (!empty($attributes['imagestore'])) ? $attributes['imagestore'] : '';
	$imageSize = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] : 'thumbnail';
	$titleTag = (!empty($attributes['titleTag'])) ? $attributes['titleTag'] : 'div';
	$title = (!empty($attributes['title'])) ? $attributes['title'] : '';
	$description = (!empty($attributes['description'])) ? $attributes['description'] : '';
	
	$backBtn = (!empty($attributes['backBtn'])) ? $attributes['backBtn'] : false;
	$backCarouselBtn = (!empty($attributes['backCarouselBtn'])) ? $attributes['backCarouselBtn'] : false;
	
	$flipcarousel = (!empty($attributes['flipcarousel'])) ? $attributes['flipcarousel'] : [];
	$backAlign = (!empty($attributes['backAlign'])) ? $attributes['backAlign'] : 'center';
	
	$svgDraw = (!empty($attributes['svgDraw'])) ? $attributes['svgDraw'] : 'delayed';
	$svgstroColor = (!empty($attributes['svgstroColor'])) ? $attributes['svgstroColor'] : '';
	$svgfillColor = (!empty($attributes['svgfillColor'])) ? $attributes['svgfillColor'] : 'none';
    $svgstroHov = (!empty($attributes['svgstroHov'])) ? $attributes['svgstroHov'] : '';
    $svgfillHov = (!empty($attributes['svgfillHov'])) ? $attributes['svgfillHov'] : '';
	$svgDura = (!empty($attributes['svgDura'])) ? $attributes['svgDura'] : 90;
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$count = '';
	$Sliderclass = $arrowCss = '';
	$carousel_settings = [];
	if($layoutType=='carousel'){
		
		$carousel_settings = Tp_Blocks_Helper::carousel_settings( $attributes );
		
		$Sliderclass .= 'tpgb-carousel splide';
		
		$showDots = (!empty($attributes['showDots'])) ? $attributes['showDots'] : [ 'md' => false ];
		$dotsStyle = (!empty($attributes['dotsStyle'])) ? $attributes['dotsStyle'] : false;
		if( ( isset($showDots['md']) && !empty($showDots['md']) ) || ( isset($showDots['sm']) && !empty($showDots['sm']) ) || ( isset($showDots['xs']) && !empty($showDots['xs']) ) ){
			$Sliderclass .= ' dots-'.esc_attr($dotsStyle);
		}
		
		$showArrows = (!empty($attributes['showArrows'])) ? $attributes['showArrows'] : [ 'md' => false ];
		$arrowsStyle = (!empty($attributes['arrowsStyle'])) ? $attributes['arrowsStyle'] : 'style-1';
		
		$arrowCss = Tp_Blocks_Helper::tpgb_carousel_arrow_css( $showArrows , $block_id );
	}
	
	//img src
	$imgSrc ='';
	$altText = (isset($imagestore['alt']) && !empty($imagestore['alt'])) ? esc_attr($imagestore['alt']) : ((!empty($imagestore['title'])) ? esc_attr($imagestore['title']) : esc_attr__('FlipBox','the-plus-addons-for-block-editor'));
	if(!empty($imagestore) && !empty($imagestore['id'])){
		$counter_img = $imagestore['id'];
		$imgSrc = wp_get_attachment_image($counter_img , $imageSize, false, ['class' => 'service-img', 'alt'=> $altText]);
	}else if(!empty($imagestore['url'])){
		$imgSrc = '<img src="'.esc_url($imagestore['url']).'" class="service-img" alt="'.$altText.'"/>';
	}
			
	$output = '';
    $output .= '<div class="tpgb-flipbox tpgb-relative-block '.esc_attr($Sliderclass).' list-'.esc_attr($layoutType).' flip-box-style-1 tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'" data-splide=\'' . json_encode($carousel_settings) . '\'>';
		if($layoutType=='listing'){
			$output .= '<div class="flip-box-inner content_hover_effect ">';
				$output .= '<div class="flip-box-bg-box">';
					$output .= '<div class="service-flipbox flip-'.esc_attr($flipType).' height-full">';
						$output .= '<div class="service-flipbox-holder height-full text-center perspective bezier-1">';
							$output .= '<div class="service-flipbox-front bezier-1 no-backface origin-center">';
								$output .= '<div class="service-flipbox-content width-full">';
									if($iconType=='icon'){
										$output .= '<span class="service-icon tpgb-trans-linear icon-'.esc_attr($iconStyle).'">';
											$output .= '<i class="'.esc_attr($iconStore).'"></i>';
										$output .= '</span>';
									}
									if($iconType=='img' && !empty($imagestore)){
										$output .= $imgSrc;
									}
									if($iconType=='svg' && !empty($svgIcon) && !empty($svgIcon['url']) ){
										$output .= '<div class="tpgb-draw-svg" data-id="service-svg-'.esc_attr($block_id).'" data-type="'.esc_attr($svgDraw).'" data-duration="'.esc_attr($svgDura).'" data-stroke="'.esc_attr($svgstroColor).'" data-fillColor="'.esc_attr($svgfillColor).'" data-strokeColorHover="'.esc_attr($svgstroHov).'" data-fillColorHover="'.esc_attr($svgfillHov).'" data-fillEnable="yes">';
											$output .= '<object id="service-svg-'.esc_attr($block_id).'" type="image/svg+xml" data="'.esc_url($svgIcon['url']).'" aria-label="'.esc_attr__('icon','the-plus-addons-for-block-editor').'"></object>';
										$output .= '</div>';
									}
									$output .= '<div class="service-content">';
										$output .= '<'.Tp_Blocks_Helper::validate_html_tag($titleTag).' class="service-title tpgb-trans-linear">'.wp_kses_post($title).'</'.Tp_Blocks_Helper::validate_html_tag($titleTag).'>';
									$output .= '</div>';
								$output .= '</div>';
								$output .= '<div class="flipbox-front-overlay tpgb-trans-linear"></div>';
							$output .= '</div>';
							$output .= '<div class="service-flipbox-back fold-back-'.esc_attr($flipType).' no-backface bezier-1 origin-center text-'.esc_attr($backAlign).'">';
								$output .= '<div class="service-flipbox-content width-full">';
									$output .= '<div class="service-desc tpgb-trans-linear">'.wp_kses_post($description).'</div>';
									if(!empty($backBtn)){
										$output .= tpgb_getButtonRender($attributes);
									}
								$output .= '</div>';
								$output .= '<div class="flipbox-back-overlay tpgb-trans-linear"></div>';
							$output .= '</div>';
						$output .= '</div>';
					$output .= '</div>';
				$output .= '</div>';
			$output .= '</div>';
		}
		if($layoutType=='carousel'){
			if( ( isset($showArrows['md']) && !empty($showArrows['md']) ) || ( isset($showArrows['sm']) && !empty($showArrows['sm']) ) || ( isset($showArrows['xs']) && !empty($showArrows['xs']) ) ){
				$output .= Tp_Blocks_Helper::tpgb_carousel_arrow($arrowsStyle, '');
			}
			$output .= '<div class="post-loop-inner splide__track">';
				$output .= '<div class="splide__list">';
				if(!empty($flipcarousel)){
					foreach ( $flipcarousel as $index => $item ) {
						$count++;
						$output .= '<div class="splide__slide flip-box-inner content_hover_effect tp-repeater-item-'.esc_attr($item['_key']).'" data-index="'.esc_attr($count).'">';
							$output .= '<div class="flip-box-bg-box">';
								$output .= '<div class="service-flipbox flip-'.esc_attr($flipType).' height-full">';
									$output .= '<div class="service-flipbox-holder height-full text-center perspective bezier-1">';
										$output .= '<div class="service-flipbox-front bezier-1 no-backface origin-center">';
											$output .= '<div class="service-flipbox-content width-full">';
												if($item['iconType']=='icon'){
													$output .= '<span class="service-icon tpgb-trans-linear icon-'.esc_attr($iconStyle).'"></i>';
														$output .= '<i class="'.esc_attr($item['iconStore']).'"></i>';
													$output .= '</span>';
												}
												if($item['iconType']=='image' && !empty($item['imagestore'])){
													$imageSize = (!empty($item['imageSize'])) ? $item['imageSize'] : 'thumbnail';
													$altText = (isset($item['imagestore']['alt']) && !empty($item['imagestore']['alt'])) ? esc_attr($item['imagestore']['alt']) : ((!empty($item['imagestore']['title'])) ? esc_attr($item['imagestore']['title']) : esc_attr__('FlipBox','the-plus-addons-for-block-editor'));

													if(!empty($item['imagestore']['id'])){
														$imgSrc = wp_get_attachment_image($item['imagestore']['id'] , $imageSize, false,['class' => 'service-img', 'alt' => $altText]);
													}else if(!empty($item['imagestore']['url'])){
														$imgSrc = $item['imagestore']['url'];
														$imgSrc = '<img src="'.esc_url($imgSrc).'" class="service-img" alt="'.$altText.'"/>';
													}
													$output .= $imgSrc;
												}
												if($item['iconType']=='svg' && isset($item['svgFIcon']) && isset($item['svgFIcon']['url'])){
													$output .= '<div class="tpgb-draw-svg" data-id="service-svg-'.esc_attr($item['_key']).'" data-type="'.esc_attr($svgDraw).'" data-duration="'.esc_attr($svgDura).'" data-stroke="'.esc_attr($svgstroColor).'" data-fillColor="'.esc_attr($svgfillColor).'" data-strokeColorHover="'.esc_attr($svgstroHov).'" data-fillColorHover="'.esc_attr($svgfillHov).'" data-fillEnable="yes">';
														$output .= '<object id="service-svg-'.esc_attr($item['_key']).'" type="image/svg+xml" data="'.esc_url($item['svgFIcon']['url']).'" aria-label="'.esc_attr__('icon','the-plus-addons-for-block-editor').'"></object>';
													$output .= '</div>';
												}
												$output .= '<div class="service-content">';
													$output .= '<'.Tp_Blocks_Helper::validate_html_tag($titleTag).' class="service-title tpgb-trans-linear">'.wp_kses_post($item['title']).'</'.Tp_Blocks_Helper::validate_html_tag($titleTag).'>';
												$output .= '</div>';
											$output .= '</div>';
											$output .= '<div class="flipbox-front-overlay tpgb-trans-linear"></div>';
										$output .= '</div>';
										$output .= '<div class="service-flipbox-back fold-back-'.esc_attr($flipType).' no-backface bezier-1 origin-center text-'.esc_attr($backAlign).'">';
											$output .= '<div class="service-flipbox-content width-full">';
												$output .= '<div class="service-desc tpgb-trans-linear">'.wp_kses_post($item['description']).'</div>';
												if(!empty($backCarouselBtn)){
													$output .=tpgb_getButtonRender($attributes,$item['btnUrl'],$item['btnText']);
												}
											$output .= '</div>';
											$output .= '<div class="flipbox-back-overlay tpgb-trans-linear"></div>';
										$output .= '</div>';
									$output .= '</div>';
								$output .= '</div>';
							$output .= '</div>';
						$output .= '</div>';
					}
				}
				$output .= '</div>';
			$output .= '</div>';
		}
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	if($layoutType=='carousel' && !empty($arrowCss) ){
		$output .= $arrowCss;
	}
    return $output;
}

function tpgb_getButtonRender($attributes,$itemBtnUrl='',$itemBtnText=''){
	$layoutType = (!empty($attributes['layoutType'])) ? $attributes['layoutType'] : 'listing';
	$btnStyle = (!empty($attributes['btnStyle'])) ? $attributes['btnStyle'] : 'style-7';
	$btnCarouselStyle = (!empty($attributes['btnCarouselStyle'])) ? $attributes['btnCarouselStyle'] : 'style-7';
	$btnIconType = (!empty($attributes['btnIconType'])) ? $attributes['btnIconType'] : 'none';
	$btnCarouselIconType = (!empty($attributes['btnCarouselIconType'])) ? $attributes['btnCarouselIconType'] : 'none';
	$btnIconName = (!empty($attributes['btnIconName'])) ? $attributes['btnIconName'] : '';
	$btnCarouselIconName = (!empty($attributes['btnCarouselIconName'])) ? $attributes['btnCarouselIconName'] : '';
	$btnIconPosition = (!empty($attributes['btnIconPosition'])) ? $attributes['btnIconPosition'] : 'after';
	$btnCarouselIconPosition = (!empty($attributes['btnCarouselIconPosition'])) ? $attributes['btnCarouselIconPosition'] : 'after';
	$btnText = (!empty($attributes['btnText'])) ? $attributes['btnText'] : '';
	$btnUrl = (!empty($attributes['btnUrl'])) ? $attributes['btnUrl'] : '';
	
	$NewBtnText = ($layoutType=='carousel') ? $itemBtnText : $btnText;
	$getBtnText = '<div class="btn-text">'.wp_kses_post($NewBtnText).'</div>';
	
	$getbutton = '';
	
	$NewBtnStyle = ($layoutType=='carousel') ? $btnCarouselStyle : $btnStyle;
	$NewBtnType = ($layoutType=='carousel' ) ? $btnCarouselIconType : $btnIconType;
	$NewBtnIconPosition = ($layoutType=='carousel' ) ? $btnCarouselIconPosition : $btnIconPosition;
	$NewBtnIconName = ($layoutType=='carousel' ) ? $btnCarouselIconName : $btnIconName;
	$NewBtnUrl = ($layoutType=='carousel') ? $itemBtnUrl : $btnUrl;
	$target = (!empty($NewBtnUrl['target']) ? ' target="_blank" ' : '' ) ;
	$nofollow = (!empty($NewBtnUrl['nofollow'])) ? ' rel="nofollow" ' : '';
	$btn_attr = Tp_Blocks_Helper::add_link_attributes($NewBtnUrl);
	$getbutton .= '<div class="tpgb-adv-button button-'.esc_attr($NewBtnStyle).'">';
		$getbutton .= '<a href="'.esc_url($NewBtnUrl['url']).'" class="button-link-wrap" role="button" '.$target.' '.$nofollow.' '.$btn_attr.'>';
		if($NewBtnStyle == 'style-8'){
			if($NewBtnIconPosition == 'before'){
				if($NewBtnType == 'icon'){
					$getbutton .= '<span class="btn-icon  button-'.esc_attr($NewBtnIconPosition).'">';
						$getbutton .= '<i class="'.esc_attr($NewBtnIconName).'"></i>';
					$getbutton .= '</span>';
				}
				$getbutton .= $getBtnText;
			}
			if($NewBtnIconPosition == 'after'){
				$getbutton .= $getBtnText;
				if($NewBtnType == 'icon'){
					$getbutton .= '<span class="btn-icon  button-'.esc_attr($NewBtnIconPosition).'">';
						$getbutton .= '<i class="'.esc_attr($NewBtnIconName).'"></i>';
					$getbutton .= '</span>';
				}
			}
		}
		if($NewBtnStyle == 'style-7' || $NewBtnStyle == 'style-9' ){
			$getbutton .= $getBtnText;
			
			$getbutton .= '<span class="button-arrow">';
			if($NewBtnStyle == 'style-7'){
				$getbutton .= '<span class="btn-right-arrow"><i class="fas fa-chevron-right"></i></span>';
			}
			if($NewBtnStyle == 'style-9'){
				$getbutton .= '<i class="btn-show fas fa-chevron-right"></i>';
				$getbutton .= '<i class="btn-hide fas fa-chevron-right"></i>';
			}
			$getbutton .= '</span>';
		}
		$getbutton .= '</a>';
	$getbutton .= '</div>';
	return $getbutton;
}

/**
 * Render for the server-side
 */
function tpgb_flipbox() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_flipbox_render_callback', true, true);
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_flipbox' );