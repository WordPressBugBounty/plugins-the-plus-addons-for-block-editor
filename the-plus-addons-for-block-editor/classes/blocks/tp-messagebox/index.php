<?php
/* Block : Message Box
 * @since : 1.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_messagebox_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$icon = (!empty($attributes['icon'])) ? $attributes['icon'] : false;
	$icnPosition = (!empty($attributes['icnPosition'])) ? $attributes['icnPosition'] : 'prefix';
	$msgArrow = (!empty($attributes['msgArrow'])) ? $attributes['msgArrow'] : false;
	$IconName = (!empty($attributes['IconName'])) ? $attributes['IconName'] : '';
	$dismiss = (!empty($attributes['dismiss'])) ? $attributes['dismiss'] : false;
	$Description = (!empty($attributes['Description'])) ? $attributes['Description'] : false;
	$dismsIcon = (!empty($attributes['dismsIcon'])) ? $attributes['dismsIcon'] : '';
	$Title = (!empty($attributes['Title'])) ? $attributes['Title'] : '';
	$descText = (!empty($attributes['descText'])) ? $attributes['descText'] : '';
	$extBtnshow = (!empty($attributes['extBtnshow'])) ? $attributes['extBtnshow'] : false ;
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$tnslin = 'tpgb-trans-linear';
	$tnslinaftr = 'tpgb-trans-linear-after';
	$disflex = 'tpgb-rel-flex';
	$arrow='';
	if(!empty($msgArrow)){
		$arrow .= 'msg-arrow-'.esc_attr($icnPosition);
	}
	$withBtnCss = '';
	if(!empty($extBtnshow)){
		$withBtnCss .= 'extra-btn-enable';
	}
	$iconPostfixCss = '';
	if(!empty($icnPosition) && $icnPosition=='postfix'){
		$iconPostfixCss .= 'main-icon-postfix';
	}
	$getIcon = '';
		$getIcon .='<div class="msg-icon-content '.esc_attr($iconPostfixCss).' '.esc_attr($tnslin).'">';
			$getIcon .='<span class="msg-icon '.esc_attr($disflex).' '.esc_attr($arrow).' '.esc_attr($tnslin).' '.esc_attr($tnslinaftr).'">';
				$getIcon .='<i class="'.esc_attr($IconName).'"></i>';
			$getIcon .='</span>';
		$getIcon .='</div>';
	
	$getDismiss = '';
		$getDismiss .='<div class="msg-dismiss-content '.esc_attr($tnslin).'">';
			$getDismiss .='<span class="dismiss-icon '.esc_attr($disflex).' '.esc_attr($tnslin).'">';
				$getDismiss .='<i class="'.esc_attr($dismsIcon).'"></i>';
			$getDismiss .='</span>';
		$getDismiss .='</div>';
	
	$getTitle = '';
	if(!empty($Title)){
		$getTitle .='<div class="msg-title '.esc_attr($tnslin).'">'.wp_kses_post($Title).'</div>';
	}
	
	$getDesc = '';
	if(!empty($Description) && !empty($descText)){
		$getDesc .='<div class="msg-desc '.esc_attr($tnslin).'">'.wp_kses_post($descText).'</div>';
	}
	
	$getbutton = '';
	$getbutton .= Tpgb_Blocks_Global_Options::load_plusButton_saves($attributes);
	
    $output .= '<div class="tpgb-messagebox tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		$output .='<div class="messagebox-bg-box tpgb-relative-block '.esc_attr($tnslin).'">';
			$output .='<div class="message-media '.esc_attr($tnslin).'">';
				if(!empty($icon) && $icnPosition=='prefix'){
					$output .=$getIcon;
				}
				$output .='<div class="msg-content '.esc_attr($tnslin).'">';
					$output .= '<div class="msg-inner-body '.esc_attr($withBtnCss).'">';
						$output .= $getTitle;
						if(!empty($extBtnshow)){
							$output .= $getbutton;
						}
					$output .='</div>';
					$output .= $getDesc;
				$output .='</div>';
				if(!empty($dismiss)){
					$output .=$getDismiss;
				}
				if(!empty($icon) && $icnPosition=='postfix'){
					$output .=$getIcon;
				}
			$output .='</div>';
		$output .= '</div>';
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_messagebox() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, '', true, false, true);
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_messagebox' );