<?php
/* Block : Progress Tracker
 * @since : 3.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_progress_tracker_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$progressType = (!empty($attributes['progressType'])) ? $attributes['progressType'] : 'horizontal';
	$horizontalPos = (!empty($attributes['horizontalPos'])) ? $attributes['horizontalPos'] : 'top';
	$hzDirection = (!empty($attributes['hzDirection'])) ? $attributes['hzDirection'] : 'ltr';
	$verticalPos = (!empty($attributes['verticalPos'])) ? $attributes['verticalPos'] : 'left';
	$circularPos = (!empty($attributes['circularPos'])) ? $attributes['circularPos'] : 'top-left';
	$percentageText = (!empty($attributes['percentageText'])) ? $attributes['percentageText'] : false;
	$percentageStyle = (!empty($attributes['percentageStyle'])) ? $attributes['percentageStyle'] : 'style-1';
	$circleSize = (!empty($attributes['circleSize'])) ? $attributes['circleSize'] : '50';
	$applyTo = (!empty($attributes['applyTo'])) ? $attributes['applyTo'] : 'entire';
	$unqSelector = (!empty($attributes['unqSelector'])) ? $attributes['unqSelector'] : '';
	$pinPoint = (!empty($attributes['pinPoint'])) ? $attributes['pinPoint'] : false;
	$pinPStyle = (!empty($attributes['pinPStyle'])) ? $attributes['pinPStyle'] : 'style-1';
	$pinPointRep = (!empty($attributes['pinPointRep'])) ? $attributes['pinPointRep'] : [];

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$relTselector = (!empty($attributes['relTselector']) && !empty($unqSelector) && $applyTo=='selector') ? 'tracker-rel-sel' : '';

	$positionClass = $posClass = '';
	$positionClass = 'tpgb-fixed-block';
	if($progressType=='horizontal'){
		$posClass = 'pos-'.$horizontalPos.' direction-'.$hzDirection;
	}else if($progressType=='vertical'){
		$posClass = 'pos-'.$verticalPos;
	}else{
		$posClass = 'pos-'.$circularPos;
	}

	$pinPointEnable = '';
	if(!empty($pinPoint)){
		$pinPointEnable = 'container-pinpoint-yes';
	}

	$data_attr=[];
	$data_attr['apply_to'] = $applyTo;
	if($applyTo=='selector' && !empty($unqSelector)){
		$data_attr['selector'] = $unqSelector;
	}
	$data_attr = 'data-attr="'.htmlspecialchars(json_encode($data_attr, true), ENT_QUOTES, 'UTF-8').'"';
		
	$output = '';
	$getPinItem = '';
    $output .= '<div class="tpgb-progress-tracker tpgb-relative-block type-'.esc_attr($progressType).' '.esc_attr($relTselector).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' '.esc_attr($pinPointEnable).'" '.$data_attr.'>';
		$output .= '<div class="tpgb-progress-track '.esc_attr($positionClass).' '.esc_attr($posClass).'">';
			if($progressType!='circular'){
				$output .= '<div class="progress-track-fill">';
					if(!empty($percentageText)){
						$output .= '<div class="progress-track-percentage '.esc_attr($percentageStyle).'"></div>';
					}
				$output .= '</div>';

				if(!empty($pinPoint) && $applyTo=='entire' && $progressType!='circular'){
					if(!empty($pinPointRep)){
						$getPinItem .= '<div class="tracker-pin-point-wrap pin-'.esc_attr($pinPStyle).'">';
						foreach ( $pinPointRep as $index => $item ) :
							if(!empty($item['conID']) && !empty($item['Title'])){
								$getPinItem .= '<div class="tracker-pin" data-id="'.esc_attr($item['conID']).'">';
									$getPinItem .= '<span class="tracker-pin-text">'.wp_kses_post($item['Title']).'</span>';
								$getPinItem .= '</div>';
							}
						endforeach;
						$getPinItem .= '</div>';
					}
				}
				$output .= $getPinItem;
			}else{
				$output .='<svg class="tpgb-pt-svg-circle" width="200" height="200" viewport="0 0 100 100" xmlns="https://www.w3.org/2000/svg">
				<circle class="tpgb-pt-circle-st" cx="100" cy="100" r="'.esc_attr($circleSize).'"></circle>
				<circle class="tpgb-pt-circle-st1" cx="100" cy="100" r="'.esc_attr($circleSize).'"></circle>
				<circle class="tpgb-pt-circle-st2" cx="100" cy="100" r="'.esc_attr($circleSize).'"></circle></svg>';
				if(!empty($percentageText)){
					$output .= '<div class="progress-track-percentage"></div>';
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
function tpgb_progress_tracker() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_progress_tracker_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_progress_tracker' );