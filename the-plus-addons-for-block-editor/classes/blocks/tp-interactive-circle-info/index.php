<?php
/* Block : Interactive Circle Info
 * @since : 1.3.2
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_interactive_circle_info_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$styleType = (!empty($attributes['styleType'])) ? $attributes['styleType'] : 'style-1';
	$intCircle = (!empty($attributes['intCircle'])) ? $attributes['intCircle'] : [];
	$mouseTrigger = (!empty($attributes['mouseTrigger'])) ? $attributes['mouseTrigger'] : 'hover';
	$autoTime = (!empty($attributes['autoTime'])) ? $attributes['autoTime'] : 1000;
	$defaultActive = (!empty($attributes['defaultActive'])) ? $attributes['defaultActive'] : 1;
	$outAnimation = (!empty($attributes['outAnimation'])) ? $attributes['outAnimation'] : false;
	$selAnimation = (!empty($attributes['selAnimation'])) ? $attributes['selAnimation'] : 'bounce';
	$carouselToggle = (!empty($attributes['carouselToggle'])) ? $attributes['carouselToggle'] : false;
	$extIndicator = (!empty($attributes['extIndicator'])) ? $attributes['extIndicator'] : [];
	$contiRotate = (!empty($attributes['contiRotate'])) ? $attributes['contiRotate'] : [];
	$carouselID = (!empty($attributes['carouselID'])) ? $attributes['carouselID'] : '';

	$disBtn = (!empty($attributes['disBtn'])) ? $attributes['disBtn'] : false;
	$btnStyle = (!empty($attributes['btnStyle'])) ? $attributes['btnStyle'] : 'style-7';
	$btnIconType = (!empty($attributes['btnIconType'])) ? $attributes['btnIconType'] : 'none';
	$btnIconPosition = (!empty($attributes['btnIconPosition'])) ? $attributes['btnIconPosition'] : 'after';
	$btnIconStore = (!empty($attributes['btnIconStore'])) ? $attributes['btnIconStore'] : '';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$totalItems = $animationClass = $indicatClass = $contiRotateClass = '';
	foreach ( $intCircle as $index => $item ):
		$totalItems = 1+(int)$index; 
	endforeach;

	if(!empty($outAnimation)){
		$animationClass = 'ia-circle-animation-'.$selAnimation;
	}
	if(!empty($extIndicator) && !empty($extIndicator['tpgbReset'])){
		$indicatClass = 'indicator-'.$extIndicator['indiStyle'];
	}
	if(!empty($contiRotate) && !empty($contiRotate['tpgbReset']) && $selAnimation == 'bounce'){
		if($contiRotate['animDirection']=='clock-wise'){
			$contiRotateClass = 'circle-continue-rotate';
		}else{
			$contiRotateClass = 'circle-continue-rotate direction-reverse';
		}
	}
	$dAutoTimeAttr = '';
	if($mouseTrigger=='auto'){
		$dAutoTimeAttr = 'data-auto-time="'.esc_attr($autoTime).'"';
	}
	$connect_carousel = $connection_hover_click = $connect_id = '';
	if(!empty($carouselToggle) && !empty($carouselID) && $mouseTrigger!='auto'){
		$connect_carousel = 'tpca-'.$carouselID ;
		$connect_id = 'tptab_'.$carouselID ;
		$connection_hover_click = $mouseTrigger ;
	}
	
	$output = '';
    $output .= '<div class="tpgb-ia-circle-info tpgb-relative-block circle-'.esc_attr($styleType).' '.esc_attr($animationClass).' '.esc_attr($indicatClass).' '.esc_attr($contiRotateClass).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'" data-trigger="'.esc_attr($mouseTrigger).'" '.$dAutoTimeAttr.' id="'.esc_attr($connect_id).'" data-connection="'.esc_attr($connect_carousel).'" data-eventtype="'.esc_attr($connection_hover_click).'">';
		$output .= '<div class="ia-circle-wrap tpgb-rel-flex">';
			$output .= '<div class="ia-circle-inner-wrap" data-total="'.esc_attr($totalItems).'">';
				$output .= '<div class="ia-circle-inner tpgb-trans-linear">';
					foreach ( $intCircle as $index => $item ):
						$itemCount = $defActive = '';
						if(1+(int)$index==$defaultActive){
							$defActive = 'active';
						}
						$imgSrc ='';
						$itemCount = 1+(int)$index;

						$getbutton = '';
						$btnUrl = (isset($item['btnUrl']) && !empty($item['btnUrl']['url'])) ? $item['btnUrl']['url'] : '';
						if(class_exists('Tpgbp_Pro_Blocks_Helper') && isset($item['btnUrl'])){
							$btnUrl = (isset($item['btnUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['btnUrl']) : (!empty($item['btnUrl']['url']) ? $item['btnUrl']['url'] : '');
						}
						$link_attr = '';
						if(isset($item['btnUrl'])){
							$link_attr = Tp_Blocks_Helper::add_link_attributes($item['btnUrl']);
						}
						$target = (!empty($item['btnUrl']['target'])) ? 'target="_blank"' : '';
						$nofollow = (!empty($item['btnUrl']['nofollow'])) ? 'rel="nofollow"' : '';
						$ariaLabelT = (!empty($item['btnText'])) ? esc_attr($item['btnText']) : esc_attr__("Button", 'the-plus-addons-for-block-editor');
						if(!empty($item['btnText'])){
							$getbutton .= '<div class="tpgb-adv-button button-'.esc_attr($btnStyle).'">';
								$getbutton .= '<a href="'.esc_url($btnUrl).'" class="button-link-wrap" role="button" '.$target.' '.$nofollow.' '.$link_attr.' aria-label="'.$ariaLabelT.'">';
								if($btnStyle == 'style-8'){
									if($btnIconPosition == 'before'){
										if($btnIconType == 'icon'){
											$getbutton .= '<span class="btn-icon button-'.esc_attr($btnIconPosition).'">';
												$getbutton .= '<i class="'.esc_attr($btnIconStore).'"></i>';
											$getbutton .= '</span>';
										}
										$getbutton .= wp_kses_post($item['btnText']);
									} 
									if($btnIconPosition == 'after'){
										$getbutton .= wp_kses_post($item['btnText']);
										if($btnIconType == 'icon'){
											$getbutton .= '<span class="btn-icon button-'.esc_attr($btnIconPosition).'">';
												$getbutton .= '<i class="'.esc_attr($btnIconStore).'"></i>';
											$getbutton .= '</span>';
										}
									}
								}
								if($btnStyle == 'style-7' || $btnStyle == 'style-9' ){
									$getbutton .= wp_kses_post($item['btnText']);
									$getbutton .= '<span class="button-arrow">';
									if($btnStyle == 'style-7'){
										$getbutton .= '<span class="btn-right-arrow"><i class="fas fa-chevron-right"></i></span>';
									}
									if($btnStyle == 'style-9'){
										$getbutton .= '<i class="btn-show fas fa-chevron-right"></i>';
										$getbutton .= '<i class="btn-hide fas fa-chevron-right"></i>';
									}
									$getbutton .= '</span>';
								}
								$getbutton .= '</a>';
							$getbutton .= '</div>';
						}

						$output .= '<div class="tpgb-ia-circle-item tpgb-circle-item-'.esc_attr($itemCount).' tp-repeater-item-'.esc_attr($item['_key']).' '.esc_attr($defActive).'" data-index="'.esc_attr($itemCount).'">';
							$output .= '<div class="tpgb-circle-icon-wrap tpgb-trans-linear">';
								$output .= '<div class="circle-icon-inner tpgb-rel-flex tpgb-trans-linear">';
									if($item['iconType']=='icon' && !empty($item['iconStore'])){
										$output .= '<i class="'.esc_attr($item['iconStore']).' tpgb-in-circle-icon" aria-hidden="true"></i>';
									}else if($item['iconType']=='image' && !empty($item['imageName'])){
										$imageSize = (!empty($item['imageSize'])) ? $item['imageSize'] : 'full';
										$altText = (isset($item['imageName']['alt']) && !empty($item['imageName']['alt'])) ? esc_attr($item['imageName']['alt']) : ((!empty($item['imageName']['title'])) ? esc_attr($item['imageName']['title']) : esc_attr__('Circle Info','the-plus-addons-for-block-editor'));

										if(!empty($item['imageName']) && !empty($item['imageName']['id'])){
											$imgSrc = wp_get_attachment_image($item['imageName']['id'] , $imageSize, false, ['class' => 'tpgb-in-circle-image', 'alt'=> $altText]);
										}else if(!empty($item['imageName']['url'])){
											$imgSrc = '<img src="'.esc_url($item['imageName']['url']).'" class="tpgb-in-circle-image " alt="'.$altText.'"/>';
										}
										$output .= $imgSrc;
									}
									if(!empty($item['iconTitle'])){
										$output .= '<div class="circle-icon-title">'.wp_kses_post($item['iconTitle']).'</div>';
									}
								$output .= '</div>';

								if(!empty($extIndicator) && !empty($extIndicator['tpgbReset'])){
									$output .= '<div class="tpgb-circle-ext-indicator">';
										$output .= '<div class="tpgb-circle-shape-wrap"><div class="tpgb-circle-shape-inner"></div></div>';
									$output .= '</div>';
								}
								
							$output .= '</div>';
							$output .= '<div class="tpgb-circle-content-wrap tpgb-abs-flex">';
								$output .= '<div class="circle-content-inner tpgb-rel-flex">';
									if(!empty($item['conTitle'])){
										$output .= '<div class="circle-content-title">'.wp_kses_post($item['conTitle']).'</div>';
									}
									if(!empty($item['conDesc'])){
										$output .= '<div class="circle-content-desc">'.wp_kses_post($item['conDesc']).'</div>';
									}
									if(!empty($disBtn)){
										$output .= $getbutton;
									}
								$output .= '</div>';
							$output .= '</div>';
						$output .= '</div>';

					endforeach;
				$output .= '</div>';
			$output .= '</div>';
		$output .= '</div>';
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}
/**
 * Render for the server-side
 */
function tpgb_tp_interactive_circle_info() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_interactive_circle_info_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_interactive_circle_info' );