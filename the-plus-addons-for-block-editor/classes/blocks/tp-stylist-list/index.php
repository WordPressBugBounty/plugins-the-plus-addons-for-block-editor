<?php
/* Block : Stylist List
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_stylist_list_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $alignment = (!empty($attributes['alignment'])) ? $attributes['alignment'] : 'align-left';
    $iconAlignment = (!empty($attributes['iconAlignment'])) ? $attributes['iconAlignment'] : false;
    $listsRepeater = (!empty($attributes['listsRepeater'])) ? $attributes['listsRepeater'] : [];
    $hover_bg_style = (!empty($attributes['hover_bg_style'])) ? $attributes['hover_bg_style'] : false;
    $pinAlignment = (!empty($attributes['pinAlignment'])) ? $attributes['pinAlignment'] : 'right';
    $hoverInverseEffect = (!empty($attributes['hoverInverseEffect'])) ? $attributes['hoverInverseEffect'] : false;
	
    $readMoreToggle = (!empty($attributes['readMoreToggle'])) ? $attributes['readMoreToggle'] : false;
    $showListToggle = (!empty($attributes['showListToggle'])) ? (int)$attributes['showListToggle'] : 3;
    $readMoreText = (!empty($attributes['readMoreText'])) ? $attributes['readMoreText'] : '';
	$readLessText = (!empty($attributes['readLessText'])) ? $attributes['readLessText'] : '';
	$effectArea = (!empty($attributes['effectArea'])) ? $attributes['effectArea'] : 'individual';
	$globalId = (!empty($attributes['globalId'])) ? $attributes['globalId'] : '';
	
    $headSvgColor = (!empty($attributes['headSvgColor'])) ? $attributes['headSvgColor'] : '';
    $headSvgfill = (!empty($attributes['headSvgfill'])) ? $attributes['headSvgfill'] : '';
    $headSvgHoverColor = (!empty($attributes['svgstroHov'])) ? $attributes['svgstroHov'] : '';
    $headSvgHoverFill = (!empty($attributes['svgfillHov'])) ? $attributes['svgfillHov'] : '';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	
	$alignattr ='';
	if($alignment!==''){
		$alignattr .= (!empty($alignment['md'])) ? ' align-'.$alignment['md'] : ' align-left';
		$alignattr .= (!empty($alignment['sm'])) ? ' tablet-align-'.$alignment['sm'] : '';
		$alignattr .= (!empty($alignment['xs'])) ? ' mobile-align-'.$alignment['xs'] : '';
	}
	
	$iconalignattr = (!empty($iconAlignment)) ? ' d-flex-center' : ' d-flex-top';
	
	$hoverInvertClass = $inverAttr ='';
	if( $hoverInverseEffect ){
		$hoverInvertClass .= ($effectArea == 'global') ? ' hover-inverse-effect-global' : ' hover-inverse-effect';
		$hoverInvertClass .= ($effectArea == 'global' && !empty($globalId) ) ? ' hover-'.$globalId : '';
		$inverAttr .= ($effectArea == 'global' && !empty($globalId) ) ? 'data-hover-inverse = hover-'.esc_attr($globalId).'' : '';
	}
	
	$i=0;$j=0;
	
    $output .= '<div class="tpgb-stylist-list tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr($alignattr).' '.esc_attr($blockClass).' '.esc_attr($hoverInvertClass).'" '.$inverAttr.'>';
		if(!empty($listsRepeater)){
		
			if($hover_bg_style){
			
				$output .= '<div class="tpgb-bg-hover-effect">';
					foreach ( $listsRepeater as $index => $item ) :
						$active='';
						if($j==0){
							$active=' active';
						}
						$output .= '<div class="hover-item-content tp-repeater-item-'.esc_attr($item['_key']).esc_attr($active).'"></div>';
						$j++;
					endforeach;
				$output .= "</div>";
			}
			
			$output .= '<div class="tpgb-icon-list-items'.esc_attr($iconalignattr).'">';
				foreach ( $listsRepeater as $index => $item ) :
					
					$i++;
					$active_class=$descurl_open=$descurl_close='';
					if($i==1){
						$active_class='active';
					}
					//Url
					if(!empty($item['descurl']) && !empty($item['descurl']['url'])){
						$descurl = (isset($item['descurl']['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper') ) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['descurl']) : (!empty($item['descurl']['url']) ? $item['descurl']['url'] : '');
						$target = ($item['descurl']['target']!='') ? 'target="_blank"' : '';
						$nofollow = ($item['descurl']['nofollow']!='') ? 'rel="nofollow"' : '';
						$link_attr = Tp_Blocks_Helper::add_link_attributes($item['descurl']);
						$descurl_open ='<a href="'.esc_url($descurl).'" '.$target.' '.$nofollow.' '.$link_attr.'>';
						$descurl_close ='</a>';
					}
					
					//Icon
					$icons ='';
					if(!empty($item['selectIcon'])){
						$icons .= '<div class="tpgb-icon-list-icon">';
							if($item['selectIcon']=='fontawesome' && !empty($item['iconFontawesome'])){ 
								$icons .='<i class="list-icon '.esc_attr($item['iconFontawesome']).'" aria-hidden="true"></i>';
							}else if($item['selectIcon'] == 'img' && !empty($item['iconImg']['url'])){
								$imgSrc = '';
								$altText = (isset($item['iconImg']['alt']) && !empty($item['iconImg']['alt'])) ? esc_attr($item['iconImg']['alt']) : ((!empty($item['iconImg']['title'])) ? esc_attr($item['iconImg']['title']) : esc_attr__('Icon Image','the-plus-addons-for-block-editor'));
								if(!empty($item['iconImg']) && !empty($item['iconImg']['id'])){
									$imgSrc = wp_get_attachment_image($item['iconImg']['id'] , 'full', false, ['alt'=> $altText]);
								}else if( !empty($item['iconImg']['url']) ){
									$imgurl = ( class_exists('Tpgbp_Pro_Blocks_Helper') ) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($item['iconImg']) : '';
									$imgSrc = '<img src="'.esc_url($imgurl).'"  alt="'.$altText.'" />';
								}
								$icons .= $imgSrc;
							} else if($item['selectIcon'] == 'svg' && !empty($item['svgIcon']['url'])){
                                $svgSrc = '';
                                $altText = (isset($item['svgIcon']['alt']) && !empty($item['svgIcon']['alt'])) ? esc_attr($item['svgIcon']['alt']) : ((!empty($item['svgIcon']['title'])) ? esc_attr($item['svgIcon']['title']) : esc_attr__('Icon Image','the-plus-addons-for-block-editor'));
                            
                                $svg_attachment_id = !empty($item['svgIcon']['id']) ? $item['svgIcon']['id'] : '';
                                $item_key = !empty($item['_key']) ? $item['_key'] : $index;
                                $unique_svg_id = 'wp-svg-' . $svg_attachment_id . '-' . $item_key;

                                if ( ! empty( $item['svgIcon'] ) && ! empty( $item['svgIcon']['id'] ) ) {
                                    $svgurl = wp_get_attachment_url( $item['svgIcon']['id'] );
                            
                                    $svgSrc = '<object id="'.esc_attr($unique_svg_id).'" class="tpgb-stylist-list-svg" type="image/svg+xml" data="' . esc_url( $svgurl ) . '" aria-label="' . esc_attr( $altText ) . '"></object>';
                            
                                } else if ( ! empty( $item['svgIcon']['url'] ) ) {
                                    $svgurl = ( class_exists( 'Tpgbp_Pro_Blocks_Helper' ) ) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url( $item['svgIcon'] ): '';
                            
                                    $svgSrc = '<object id="'.esc_attr($unique_svg_id).'" class="tpgb-stylist-list-svg" type="image/svg+xml" data="' . esc_url( $svgurl ) . '" aria-label="' . esc_attr( $altText ) . '"></object>';
                                }
                                $icons .= '<div class="tpgb-draw-svg" data-id="'.esc_attr($unique_svg_id).'" data-type="delayed" data-duration="1" data-stroke="'.esc_attr($headSvgColor).'" data-fillColor="'.esc_attr($headSvgfill).'" data-strokeColorHover="'.esc_attr($headSvgHoverColor).'" data-fillColorHover="'.esc_attr($headSvgHoverFill).'" data-fillEnable="yes">';
                                    $icons .= $svgSrc;
                                $icons .= '</div>';
                            }   
						$icons .= '</div>';
					}
					
					//Description and Pin
					$itemdesc = '';
					if(!empty($item['description'])){
						$pinHint = (!empty($item['pinHint']) && !empty($item['hintText'])) ? ' pin-hint-inline' : '';
						$itemdesc .= '<div class="tpgb-icon-list-text'.esc_attr($pinHint).'"><p>'.wp_kses_post($item['description']).'</p>';
						if(!empty($item['pinHint']) && !empty($item['hintText'])){ 
							$itemdesc .='<span class="tpgb-hint-text '.esc_attr($pinAlignment).'">'.wp_kses_post($item['hintText']).'</span>';
						}
						$itemdesc .= '</div>';
					}
					
					$tooltipdata = '';
					$contentItem =[];
					if(!empty($item['itemTooltip'])){

						if(class_exists('Tpgbp_Pro_Blocks_Helper')){
							$contentItem['content'] = (!empty($item['tooltipText'])  ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($item['tooltipText']) : '');
						}else{
							$contentItem['content'] = (!empty($item['tooltipText'])  ? $item['tooltipText'] : '');
						}

						$contentItem['trigger'] = (!empty($attributes['tipTriggers'])  ? $attributes['tipTriggers'] : 'mouseenter');
						$contentItem['MaxWidth'] = (!empty($attributes['tipMaxWidth']) ? (int)$attributes['tipMaxWidth'] : 'none');
						$contentItem = htmlspecialchars(wp_json_encode($contentItem), ENT_QUOTES, 'UTF-8');
						$tooltipdata = 'data-tooltip-opt= \'' .$contentItem. '\'';
					}
					
					//Tooltip
					$itemtooltip =$tooltip_trigger='';
					$uniqid=uniqid("tooltip");
					if(!empty($item['itemTooltip'])){
						$itemtooltip .= ' data-tippy=""';
						$itemtooltip .= ' data-tippy-interactive="'.(!empty($attributes['tipInteractive']) ? 'true' : 'false').'"';
						$itemtooltip .= ' data-tippy-placement="'.(!empty($attributes['tipPlacement']) ? $attributes['tipPlacement'] : 'top').'"';
						$itemtooltip .= ' data-tippy-theme="'.$attributes['tipTheme'].'"';
						$itemtooltip .= ' data-tippy-arrow="'.(!empty($attributes['tipArrow']) ? 'true' : 'false').'"';
						$itemtooltip .= ' data-tippy-followCursor="'.(!empty($attributes['followCursor']) ? 'true' : 'false').'" ';
						$itemtooltip .= ' data-tippy-animation="'.(!empty($attributes['tipAnimation']) ? $attributes['tipAnimation'] : 'fade').'"';
						$itemtooltip .= ' data-tippy-offset="['.(!empty($attributes['tipOffset']) ? (int)$attributes['tipOffset'] : 0 ).','.(!empty($attributes['tipDistance']) ? (int)$attributes['tipDistance'] : 0).']"';
						$itemtooltip .= ' data-tippy-duration="['.(!empty($attributes['tipDurationIn']) ? (int)$attributes['tipDurationIn'] : '1').','.(!empty($attributes['tipDurationOut']) ? (int)$attributes['tipDurationOut'] : '1').']"';
					}
					//Item Content
					$output .= '<div id="'.$uniqid.'" class="tpgb-icon-list-item tp-repeater-item-'.esc_attr($item['_key']).' '.esc_attr($active_class).'" '.$itemtooltip.' '.$tooltipdata.'>';
						$output .= $descurl_open;
						$output .= $icons;
						$output .= $itemdesc;
						$output .= $descurl_close;
					$output .= "</div>";
				endforeach;
			$output .= "</div>";
			
			if(!empty($readMoreToggle) && $i > $showListToggle){
				$output .= '<a href="#" class="read-more-options more" data-default-load="'.(int)$showListToggle.'" data-more-text="'.esc_attr($readMoreText).'" data-less-text="'.esc_attr($readLessText).'">'.wp_kses_post($readMoreText).'</a>';
			}
		}
    $output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_stylist_list() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_stylist_list_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_stylist_list' );