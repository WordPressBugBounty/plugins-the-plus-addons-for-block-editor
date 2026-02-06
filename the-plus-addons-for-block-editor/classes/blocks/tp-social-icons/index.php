<?php
/* Block : Social Icons
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_social_icons_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
	$hoverStyle = (!empty($attributes['hoverStyle'])) ? $attributes['hoverStyle'] : 'faded';
	$socialIcon = (!empty($attributes['socialIcon'])) ? $attributes['socialIcon'] : [];
	$Alignment = (!empty($attributes['Alignment'])) ? $attributes['Alignment'] : 'text-center';
	
	$alignattr ='';
	if($Alignment!==''){
		$alignattr .= (!empty($Alignment['md'])) ? ' text-'.esc_attr($Alignment['md']) : ' text-center';
		$alignattr .= (!empty($Alignment['sm'])) ? ' tsocialtext-'.esc_attr($Alignment['sm']) : '';
		$alignattr .= (!empty($Alignment['xs'])) ? ' msocialtext-'.esc_attr($Alignment['xs']) : '';
	}
	
	$social_animation ='';
	if($style=='style-14' || $style=='style-15'){
		if($hoverStyle == 'faded'){
			$social_animation ='social-faded';
		}else if($hoverStyle == 'chaffal'){
			$social_animation ='social-chaffal';
		}
	}
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$i =0;
	$output = ''; 
    $output .= '<div class="tpgb-social-icons '.esc_attr($style).' '.esc_attr($alignattr).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		if(!empty($socialIcon)){
		$output .='<div class="tpgb-social-list '.esc_attr($social_animation).'">';
			
				foreach ( $socialIcon as $index => $network ) :
					//Tooltip
					$i++;
						$itemtooltip =$tooltip_trigger=$tooltipdata='';
						$contentItem =[];
						$uniqid=uniqid("tooltip");
						if(!empty($network['itemTooltip'])){
							$itemtooltip .= ' data-tippy=""';
							$itemtooltip .= ' data-tippy-interactive="'.(!empty($attributes['tipInteractive']) ? 'true' : 'false').'"';
							$itemtooltip .= ' data-tippy-placement="'.(!empty($attributes['tipPlacement']) ? $attributes['tipPlacement'] : 'top').'"';
							$itemtooltip .= ' data-tippy-theme="'.esc_attr($attributes['tipTheme']).'"';
							$itemtooltip .= ' data-tippy-arrow="'.(!empty($attributes['tipArrow']) ? 'true' : 'false').'"';
							
							$itemtooltip .= ' data-tippy-animation="'.(!empty($attributes['tipAnimation']) ? $attributes['tipAnimation'] : 'fade').'"';
							$itemtooltip .= ' data-tippy-offset="['.(!empty($attributes['tipOffset']) ? (int)$attributes['tipOffset'] : 0 ).','.(!empty($attributes['tipDistance']) ? (int)$attributes['tipDistance'] : 0 ).']"';
							$itemtooltip .= ' data-tippy-duration="['.(!empty($attributes['tipDurationIn']) ? (int)$attributes['tipDurationIn'] : '1').','.(!empty($attributes['tipDurationOut']) ? (int)$attributes['tipDurationOut'] : '1').']"';

							if(class_exists('Tpgbp_Pro_Blocks_Helper')){
								$contentItem['content'] = (!empty($network['tooltipText'])  ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($network['tooltipText']) : '');
							}else{
								$contentItem['content'] = (!empty($network['tooltipText'])  ? $network['tooltipText'] : '');
							}
							
							$contentItem['trigger'] = (!empty($attributes['tipTriggers'])  ? $attributes['tipTriggers'] : 'mouseenter');
							$contentItem['MaxWidth'] = (!empty($attributes['tipMaxWidth']) ? (int)$attributes['tipMaxWidth'] : 'none');
							$contentItem = htmlspecialchars(json_encode($contentItem), ENT_QUOTES, 'UTF-8');
							$tooltipdata = 'data-tooltip-opt= \'' .$contentItem. '\'';
						}
						
						
					$output .= '<div id="'.esc_attr($uniqid).'" class=" social-icon-tooltip tp-repeater-item-'.( isset( $network['_key'] ) ? esc_attr($network['_key']) : '').' '.esc_attr($style).' '.$itemtooltip.'" '.$tooltipdata.' >';
						if(!empty($network['linkUrl']['url']) && !empty($network['socialNtwk'])){
							$socialUrl = (class_exists('Tpgbp_Pro_Blocks_Helper') && isset($network['linkUrl']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($network['linkUrl']) : (!empty($network['linkUrl']['url']) ? $network['linkUrl']['url'] : '');
							$target = (!empty($network['linkUrl']['target'])) ? ' target="_blank" ' : '';
							$nofollow = (!empty($network['linkUrl']['nofollow'])) ? ' rel="nofollow" ' : '';
							$link_attr = Tp_Blocks_Helper::add_link_attributes($network['linkUrl']);
							$output .= '<div class="tpgb-social-loop-inner '.($style=='style-14' ? 'tpgb-rel-flex' : '').'">';
								$output .= '<a class="tpgb-icon-link '.(($style=='style-14' || $style=='style-15') ? 'tpgb-rel-flex' : '').'" href="'.esc_url($socialUrl).'" aria-label="'.esc_attr($network['title']).'" '.$target.' '.$nofollow.' '.$link_attr.'>';
									if($network['socialNtwk']=='custom' && $network['customType']=='icon' && !empty($network['customIcons'])) {
										$output .= '<span class="tpgb-social-icn '.($style=='style-12' ? 'tpgb-abs-flex' : '').'">';
											$output .= '<i class="'.esc_attr($network['customIcons']).'"></i>';
										$output .= '</span>';
									}else if($network['socialNtwk']=='custom' && $network['customType']=='image' && !empty($network['imgField']) && !empty($network['imgField']['url'])) {
										$imgSrc='';
										$altText = (isset($network['imgField']['alt']) && !empty($network['imgField']['alt'])) ? esc_attr($network['imgField']['alt']) : ((!empty($network['imgField']['title'])) ? esc_attr($network['imgField']['title']) : esc_attr__('Custom Icon','the-plus-addons-for-block-editor'));
										if(!empty($network['imgField']) && !empty($network['imgField']['id'])){
											$imgSrc = wp_get_attachment_image($network['imgField']['id'] , 'full', false, ['alt'=> $altText]);
										}else if(!empty($network['imgField']['url'])){
											if(class_exists('Tpgbp_Pro_Blocks_Helper')){
												$imgUrl = Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($network['imgField']);
											}else{
												$imgUrl = $network['imgField']['url'];
											}

											$imgSrc = '<img src="'.esc_url($imgUrl).'" alt="'.$altText.'" />';
										}
										$output .= '<span class="tpgb-social-icn social-img '.($style=='style-7' ? 'tpgb-rel-flex' : '').' '.($style=='style-12' ? 'tpgb-abs-flex' : '').'">';
											$output .= $imgSrc;
										$output .= '</span>';
									}else if($network['socialNtwk']!='custom'){
										$output .= '<span class="tpgb-social-icn '.($style=='style-12' ? 'tpgb-abs-flex' : '').'">';
											$output .= '<i class="'.esc_attr($network['socialNtwk']).'"></i>';
										$output .= '</span>';
									}
										if($style=='style-6'){
											$output .= '<i class="social-hover-style"></i>';
										}
									if(!empty($network['title']) && $style=='style-1' || $style=='style-2' || $style=='style-4' || $style=='style-10' || $style=='style-12' || $style=='style-14' || $style=='style-15'){
										$output .= '<span class="tpgb-social-title '.(($style=='style-10' || $style=='style-12') ? 'tpgb-abs-flex' : '').'" data-lang="en">'.wp_kses_post($network['title']).'</span>';
									}
									if($style=='style-9'){
										$output .= '<span class="tpgb-line-blink line-top-left "></span>';
										$output .= '<span class="tpgb-line-blink line-top-center "></span>';
										$output .= '<span class="tpgb-line-blink line-top-right "></span>';
										$output .= '<span class="tpgb-line-blink line-bottom-left "></span>';
										$output .= '<span class="tpgb-line-blink line-bottom-center "></span>';
										$output .= '<span class="tpgb-line-blink line-bottom-right "></span>';
									}
								$output .= '</a>';
							$output .= '</div>';
						}
					$output .= '</div>';
					
					
						endforeach;
				$output .='</div>';
			}
			
		
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}
/**
 * Render for the server-side
 */
function tpgb_social_icons() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_social_icons_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_social_icons' );