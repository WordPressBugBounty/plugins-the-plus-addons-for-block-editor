<?php
/* Tp Block : Smooth Scroll
 * @since	: 1.1.1
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_smooth_scroll_render_callback( $attributes ) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	// $frameRate = (!empty($attributes['frameRate'])) ? (int)$attributes['frameRate'] : 150;
	$aniTime = (!empty($attributes['aniTime'])) ? (int)$attributes['aniTime'] : 400;
	$stepSize = (!empty($attributes['stepSize'])) ? (int)$attributes['stepSize']  : 100;
	$touchSupp = (!empty($attributes['touchSupp'])) ? 1 : 0;
    $tMult = (!empty($attributes['tMult'])) ? (int)$attributes['tMult'] : 2;
    $easing = (!empty($attributes['easing'])) ? $attributes['easing'] : '(t) => 1 - Math.pow(1 - t, 3)';
    $infinite = (!empty($attributes['infinite'])) ? $attributes['infinite'] : false;
    $smNav = (!empty($attributes['smNav'])) ? $attributes['smNav'] : false;
    $custEase = (!empty($attributes['custEase'])) ? $attributes['custEase'] : "";
    $viewport = (!empty($attributes['viewport'])) ? $attributes['viewport'] : "80";

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	//Set Data Attr For Js
    $encodedEase = htmlspecialchars(json_encode($custEase), ENT_QUOTES, 'UTF-8');
	$dataAttr = [
		// 'frameRate' => $frameRate,
		'animationTime' => $aniTime,
		'stepSize' => $stepSize,
        'touchMultiplier'=>$tMult,
        'easing'=>$easing,
        'infiniteScroll'=>$infinite,
        'orientation'=>'vertical',
        'smoothNavigation'=>$smNav,
        'viewport'=>$viewport
	];
    if ($easing === 'custom') {
    	$dataAttr['customEasing'] = $custEase;
    }
	
	$dataAttr = json_encode($dataAttr);

    $output .= '<div class="tpgb-smooth-scroll tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' " data-scrollAttr= \'' . $dataAttr . '\' >';
		
	$output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_smooth_scroll() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_smooth_scroll_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_smooth_scroll' );