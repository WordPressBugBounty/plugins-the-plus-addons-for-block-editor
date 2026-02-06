<?php
/* Block : Pricing Table
 * @since : 1.2.1
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_pricing_table_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
	$contentStyle = (!empty($attributes['contentStyle'])) ? $attributes['contentStyle'] : 'wysiwyg';
	$conListStyle = (!empty($attributes['conListStyle'])) ? $attributes['conListStyle'] : 'style-1';
	$wyStyle = (!empty($attributes['wyStyle'])) ? $attributes['wyStyle'] : 'style-1';
	$wyContent = (!empty($attributes['wyContent'])) ? $attributes['wyContent'] : '';
	
	$disRibbon = (!empty($attributes['disRibbon'])) ? $attributes['disRibbon'] : false;
	
	$titleStyle = (!empty($attributes['titleStyle'])) ? $attributes['titleStyle'] : 'style-1';
	$iconType = (!empty($attributes['iconType'])) ? $attributes['iconType'] : 'none';
	$iconStyle = (!empty($attributes['iconStyle'])) ? $attributes['iconStyle'] : 'square';
	$iconStore = (!empty($attributes['iconStore'])) ? $attributes['iconStore'] : 'fas fa-home';
	$imgStore = (!empty($attributes['imgStore'])) ? $attributes['imgStore'] : '';
	$title = (!empty($attributes['title'])) ? $attributes['title'] : '';
	$subTitle = (!empty($attributes['subTitle'])) ? $attributes['subTitle'] : '';
	
	$priceStyle = (!empty($attributes['priceStyle'])) ? $attributes['priceStyle'] : 'style-1';
	$disPrePrice = (!empty($attributes['disPrePrice'])) ? $attributes['disPrePrice'] : false;
	$prevPreText = (!empty($attributes['prevPreText'])) ? $attributes['prevPreText'] : '';
	$prevPriceValue = (!empty($attributes['prevPriceValue'])) ? $attributes['prevPriceValue'] : '';
	$prevPostText = (!empty($attributes['prevPostText'])) ? $attributes['prevPostText'] : '';
	$preText = (!empty($attributes['preText'])) ? $attributes['preText'] : '';
	$priceValue = (isset($attributes['priceValue'])) ? $attributes['priceValue'] : '';
	$postText = (!empty($attributes['postText'])) ? $attributes['postText'] : '';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$extBtnshow = (!empty($attributes['extBtnshow'])) ? $attributes['extBtnshow'] : false ;
	
	$hoverStyle = (!empty($attributes['hoverStyle'])) ? $attributes['hoverStyle'] : 'hover_normal';
	
	$svgIcon = (!empty($attributes['svgIcon'])) ? $attributes['svgIcon'] : '';
	$svgDraw = (!empty($attributes['svgDraw'])) ? $attributes['svgDraw'] : 'delayed';
	$svgstroColor = (!empty($attributes['svgstroColor'])) ? $attributes['svgstroColor'] : '';
	$svgfillColor = (!empty($attributes['svgfillColor'])) ? $attributes['svgfillColor'] : 'none';
    $svgstroHov = (!empty($attributes['svgstroHov'])) ? $attributes['svgstroHov'] : '';
    $svgfillHov = (!empty($attributes['svgfillHov'])) ? $attributes['svgfillHov'] : '';
	$svgDura = (!empty($attributes['svgDura'])) ? $attributes['svgDura'] : 90;
	$ctaText = (!empty($attributes['ctaText'])) ? $attributes['ctaText'] : '';
	$stylishList = (!empty($attributes['stylishList'])) ? $attributes['stylishList'] : [];
	$readMoreToggle = (!empty($attributes['readMoreToggle'])) ? $attributes['readMoreToggle'] : false;
    $showListToggle = (!empty($attributes['showListToggle'])) ? (int)$attributes['showListToggle'] : 3;
    $readMoreText = (!empty($attributes['readMoreText'])) ? $attributes['readMoreText'] : '';
    $readLessText = (!empty($attributes['readLessText'])) ? $attributes['readLessText'] : '';
	$disRibbon = (!empty($attributes['disRibbon'])) ? $attributes['disRibbon'] : false;
	$ribbonStyle = (!empty($attributes['ribbonStyle'])) ? $attributes['ribbonStyle'] : 'style-1';
	$ribbonText = (!empty($attributes['ribbonText'])) ? $attributes['ribbonText'] : '';
	$extbtnPosition = (!empty($attributes['extbtnPosition'])) ? $attributes['extbtnPosition'] : '';

	$i = 0;
	$contentOverlay = '';
	$contentOverlay .= '<div class="content-overlay-bg-color tpgb-trans-easeinout"></div>';

	//Get Icon
	$getPriceIcon = '';
	$icon_style = '';
    $imgSrc = '';
	$trlinr = 'tpgb-trans-linear';
	if($iconType=='icon'){
		$icon_style = $iconStyle ;
	}
	$getPriceIcon .= '<div class=" '.($iconType=='svg' ? ' tpgb-draw-svg' : ' pricing-icon '.esc_attr($trlinr) ).' icon-'.esc_attr($icon_style).'" '.($iconType=='svg' ? 'data-id="service-svg-'.esc_attr($block_id).'" data-type="'.esc_attr($svgDraw).'" data-duration="'.esc_attr($svgDura).'" data-stroke="'.esc_attr($svgstroColor).'" data-fillColor="'.esc_attr($svgfillColor).'" data-strokeColorHover="'.esc_attr($svgstroHov).'" data-fillColorHover="'.esc_attr($svgfillHov).'" data-fillEnable="yes"': '' ).' >';
		if($iconType=='icon'){
			$getPriceIcon .= '<i class="'.esc_attr($iconStore).'"></i>';
		}
		if($iconType=='img' && !empty($imgStore)){
			$altText = (isset($imgStore['alt']) && !empty($imgStore['alt'])) ? esc_attr($imgStore['alt']) : ((!empty($imgStore['title'])) ? esc_attr($imgStore['title']) : esc_attr__('Price Icon','the-plus-addons-for-block-editor'));

			if(!empty($imgStore['id'])){
				$imgSrc = wp_get_attachment_image($imgStore['id'] , 'full', false, ['class' => 'pricing-icon-img', 'alt'=> $altText]);
			}else if(!empty($imgStore['url'])){
				$imgSrc = '<img src='.esc_url($imgStore['url']).' class="pricing-icon-img" alt="'.$altText.'"/>';
			}
			$getPriceIcon .= $imgSrc;
		}
		if($iconType=='svg' && !empty($svgIcon) && !empty($svgIcon['url'])){
			$getPriceIcon .= '<object id="service-svg-'.esc_attr($block_id).'" type="image/svg+xml" data="'.esc_url($svgIcon['url']).'" aria-label="'.esc_attr__('icon','the-plus-addons-for-block-editor').'"></object>';
		}
	$getPriceIcon .= '</div>';
		
	//Get Title
	$getPriceTitle = '';
	if(!empty($title)){
		$getPriceTitle .= '<div class="pricing-title-wrap">';
			$getPriceTitle .= '<div class="pricing-title '.esc_attr($trlinr).'">'.wp_kses_post($title).'</div>';
		$getPriceTitle .= '</div>';
	}
	
	//Get Sub Title
	$getPriceSubTitle = '';
	if(!empty($subTitle)){
		$getPriceSubTitle .= '<div class="pricing-subtitle-wrap">';
			$getPriceSubTitle .= '<div class="pricing-subtitle '.esc_attr($trlinr).'">'.wp_kses_post($subTitle).'</div>';
		$getPriceSubTitle .= '</div>'; 
	}
	
	$getRibbon = '';
	if(!empty($disRibbon) && !empty($ribbonText)){
		$getRibbon .= '<div class="pricing-ribbon-pin tpgb-relative-block '.esc_attr($ribbonStyle).'">';
			$getRibbon .= '<div class="ribbon-pin-inner '.esc_attr($trlinr).'">'.wp_kses_post($ribbonText).'</div>';
		$getRibbon .= '</div>';
	}

	//Get Title-SubTitle Content
	$getTitleContent = '';
	$getTitleContent .= '<div class="pricing-title-content tpgb-relative-block '.esc_attr($titleStyle).'">';
		if($iconType!='none'){
			$getTitleContent .= $getPriceIcon;
		}
		$getTitleContent .= $getPriceTitle;
		$getTitleContent .= $getPriceSubTitle;
	$getTitleContent .= '</div>';
	
	//Get Price Content
	$getPriceContent = '';
	$getPriceContent .= '<div class="pricing-price-wrap '.esc_attr($priceStyle).'">';
		if(!empty($disPrePrice)){
			$getPriceContent .= '<span class="pricing-previous-price-wrap '.esc_attr($trlinr).'">';
				$getPriceContent .= wp_kses_post($prevPreText);
				$getPriceContent .= wp_kses_post($prevPriceValue);
				$getPriceContent .= wp_kses_post($prevPostText);
			$getPriceContent .='</span>';
		}
		if(!empty($preText)){
			$getPriceContent .= '<span class="price-prefix-text '.esc_attr($trlinr).'">'.wp_kses_post($preText).'</span>';
		}
		if(isset($priceValue) && $priceValue!=''){
			$getPriceContent .= '<span class="pricing-price '.esc_attr($trlinr).'">'.wp_kses_post($priceValue).'</span>'; 
		}
		if(!empty($postText)){
			$getPriceContent .= '<span class="price-postfix-text '.esc_attr($trlinr).'">'.wp_kses_post($postText).'</span>';
		}
	$getPriceContent .= '</div>';
		
	//Get Button & CTA Text
	$getBtnCta = '';
	if(!empty($extBtnshow)){
		$getBtnCta .= '<div class="pricing-table-button">';
			$getBtnCta .= Tpgb_Blocks_Global_Options::load_plusButton_saves($attributes);
		$getBtnCta .= '</div>';
	}
	
	if(!empty($ctaText)){
		$getBtnCta .= '<div class="pricing-cta-text">'.wp_kses_post($ctaText).'</div>';
	}

	$getStylishContent = '';
	if($contentStyle=='stylish'){
		$getStylishContent .= '<div class="pricing-content-wrap listing-content tpgb-relative-block '.esc_attr($conListStyle).'">';
			if(!empty($stylishList)){
				$getStylishContent .= '<div class="tpgb-icon-list-items '.esc_attr($trlinr).'">';
					foreach ( $stylishList as $index => $item ) :
						
						$i++;

						$contentItem =[];
						// Item Content
						$getStylishContent .= '<div class="tpgb-icon-list-item '.esc_attr($trlinr).' tp-repeater-item-'.esc_attr($item['_key']).'">';
						
							//Get Item Icon
							$getItemIcon = '';
							$getItemIcon .= '<span class="tpgb-icon-list-icon '.esc_attr($trlinr).'">'; 
								$getItemIcon .='<i class="'.esc_attr($item['iconStore']).'" aria-hidden="true"></i>';
							$getItemIcon .= '</span>';

							//Get Item Extra Icon
							$getItemExIcon = '';
							if(!empty($item['eIcnToggle']) && !empty($item['eIconStore'])){
								$getItemExIcon .= '<span class="tpgb-extra-list-icon '.esc_attr($trlinr).'">'; 
									$getItemExIcon .='<i class="'.esc_attr($item['eIconStore']).'" aria-hidden="true"></i>';
								$getItemExIcon .= '</span>';
							}
							
							//Get Item Description
							$getItemDesc = '';
							if(!empty($item['listDesc'])){
								$getItemDesc .= '<span class="tpgb-icon-list-text '.esc_attr($trlinr).'">'.wp_kses_post($item['listDesc']).'</span>';
							}
							$getStylishContent .= $getItemIcon;
							$getStylishContent .= $getItemDesc;
							$getStylishContent .= $getItemExIcon;
						$getStylishContent .= "</div>";
						
					endforeach;
				$getStylishContent .= "</div>";
				
				if($conListStyle!='style-2' && !empty($readMoreToggle) && $i > $showListToggle){
					$getStylishContent .= '<a href="#" class="read-more-options tpgb-relative-block '.esc_attr($trlinr).' more" data-default-load="'.(int)$showListToggle.'" data-more-text="'.esc_attr($readMoreText).'" data-less-text="'.esc_attr($readLessText).'">'.wp_kses_post($readMoreText).'</a>';
				}
				if($conListStyle=='style-1'){
					$getStylishContent .= $contentOverlay;
				}
			}
		$getStylishContent .= "</div>";
	}
	

	//Get wysiwyg Content
	$getWysiwygContent = '';
	if($contentStyle=='wysiwyg'){
		$getWysiwygContent .= '<div class="pricing-content-wrap content-desc '.esc_attr($wyStyle).'">';
			if($wyStyle=='style-2'){
				$getWysiwygContent .= '<hr class="border-line"/>';
			}
			$getWysiwygContent .= '<div class="pricing-content">'.wp_kses_post($wyContent).'</div>';
			$getWysiwygContent .= '<div class="content-overlay-bg-color tpgb-trans-easeinout"></div>';
		$getWysiwygContent .= '</div>';
	}
		
	$output = '';
    $output .= '<div class="tpgb-pricing-table tpgb-relative-block '.esc_attr($trlinr).' pricing-'.esc_attr($style).' '.esc_attr($hoverStyle).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		$output .= '<div class="pricing-table-inner '.esc_attr($trlinr).'">';
			if($style=='style-1'){
				if($extbtnPosition==='bottom'){
					$output .= $getRibbon;
					$output .= $getTitleContent;
					$output .= $getPriceContent;
					$output .= $getStylishContent;
					$output .= $getWysiwygContent;
					$output .= $getBtnCta;
					$output .= '<div class="pricing-overlay-color tpgb-trans-easeinout"></div>';
				} else {
					$output .= $getRibbon;
					$output .= $getTitleContent;
					$output .= $getPriceContent;
					$output .= $getBtnCta;
					$output .= $getStylishContent;
					$output .= $getWysiwygContent;
					$output .= '<div class="pricing-overlay-color tpgb-trans-easeinout"></div>';
				}
			}
			
		$output .= '</div>';
	$output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_pricing_table() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_pricing_table_render_callback', true, false, true);
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_pricing_table' );