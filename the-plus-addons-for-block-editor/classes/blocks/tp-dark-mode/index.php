<?php
/* Tp Block : Dark Mode
 * @since	: 1.2.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function tpgb_tp_dark_mode_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$dmStyle = (!empty($attributes['dmStyle'])) ? $attributes['dmStyle'] : 'style-1';
	$dmPosition = (!empty($attributes['dmPosition'])) ? $attributes['dmPosition'] : 'relative';
	$fixedPos = (!empty($attributes['fixedPos'])) ? $attributes['fixedPos'] : 'left-top';
	$S2IconType = (!empty($attributes['S2IconType'])) ? $attributes['S2IconType'] : 'icon';
	$IconName = (!empty($attributes['IconName'])) ? $attributes['IconName'] : '';
	$darkIconEn = (!empty($attributes['darkIconEn'])) ? $attributes['darkIconEn'] : false;
	$darkIcon = (!empty($attributes['darkIcon'])) ? $attributes['darkIcon'] : '';
	$saveCookies = (!empty($attributes['saveCookies'])) ? $attributes['saveCookies'] : false;
	$matchOsTheme = (!empty($attributes['matchOsTheme'])) ? $attributes['matchOsTheme'] : false;
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$output = $hideNmlIcon = $fixPosClass = '';
	
	if(!empty($darkIconEn)) {
		$hideNmlIcon = ' hide-normal-icon';
	}
	if($dmPosition=='fixed') {
		$fixPosClass = 'fix-'.$fixedPos;
	}
	
	$output .= '<div class="tpgb-dark-mode tpgb-relative-block dark-pos-'.esc_attr($dmPosition).' darkmode-'.esc_attr($dmStyle).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'" data-id="tpgb-block-'.esc_attr($block_id).'" data-save-cookies="'.esc_attr($saveCookies).'" data-match-os="'.esc_attr($matchOsTheme).'">';
		$output .= '<div class="tpgb-dark-mode-wrap">';
			
			$output .= '<div class="tpgb-darkmode-toggle '.esc_attr($fixPosClass).'">';
				if($dmStyle=='style-1' || $dmStyle=='style-2'){
					$output .= '<span class="tpgb-dark-mode-slider"></span>';
				}else{
					if($S2IconType=='icon'){
						$output .= '<span class="tpgb-normal-icon'.esc_attr($hideNmlIcon).'">';
							$output .= '<i class="'.esc_attr($IconName).'"></i>';
						$output .= '</span>';
						if(!empty($darkIconEn)) {
							$output .= '<span class="tpgb-dark-icon">';
								$output .= '<i class="'.esc_attr($darkIcon).'"></i>';
							$output .= '</span>';
						}
					}
				}
			$output .= '</div>';
		$output .= '</div>';
	$output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);

    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_dark_mod() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_dark_mode_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_dark_mod' );