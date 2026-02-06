<?php
/* Block : Countdown
 * @since : 1.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_countdown_callback( $attributes, $content) {
	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$style = $attributes['style'];
    $countdownSelection = $attributes['countdownSelection'];
    $offset_time = get_option('gmt_offset');
    $offsetTime = wp_timezone_string();
    $now    = new DateTime('NOW', new DateTimeZone($offsetTime));
    $flipTheme = (!empty($attributes['flipTheme'])) ? $attributes['flipTheme'] : 'dark' ;

	$future = '';
    if(!empty($attributes['datetime']) && $attributes['datetime'] != 'Invalid date') {
        $future = new DateTime($attributes['datetime'], new DateTimeZone($offsetTime));
    }
    $now    = $now->modify("+1 second");

    if(!empty($attributes['datetime'])) {
        $datetime = $attributes['datetime'];
        $datetime = gmdate('m/d/Y H:i:s', strtotime($datetime) );
    } else {
        $curr_date = gmdate("m/d/Y H:i:s");
		$datetime = gmdate('m/d/Y H:i:s', strtotime($curr_date . ' +1 month'));
    }

    $countData = [];
    $countData['style'] = $style;
    $countData['blockId'] = 'tpgb-block-'.esc_attr($block_id).'';
    $countData['type'] = $countdownSelection;
    $countData['expiry']= $attributes['countdownExpiry'];

    if($attributes['countdownExpiry'] == 'redirect') {
        $encodedUrl = $attributes['expiryRedirect']['url'];
        $countData['redirect'] = $encodedUrl;
    }
    
    if($attributes['countdownExpiry'] == 'showmsg') {
        $countData['expiryMsg'] = wp_kses_post($attributes['expiryMsg']);
    }
    
    $dataAttr = '';
    $showLabels = (!empty($attributes['showLabels'])) ? $attributes['showLabels'] : '' ;
    $daysText = (!empty($attributes['daysText'])) ? $attributes['daysText'] : esc_html__('Days','the-plus-addons-for-block-editor');
    $hoursText = (!empty($attributes['hoursText'])) ? $attributes['hoursText'] : esc_html__('Hours','the-plus-addons-for-block-editor');
    $minutesText = (!empty($attributes['minutesText'])) ? $attributes['minutesText'] : esc_html__('Minutes','the-plus-addons-for-block-editor');
    $secondsText = (!empty($attributes['secondsText'])) ? $attributes['secondsText'] : esc_html__('Seconds','the-plus-addons-for-block-editor');
    
    if(  $countdownSelection == 'normal' && ( !empty($showLabels) && $showLabels == true )) {
        $dataAttr .= ' data-day="'.wp_kses_post($daysText).'" data-hour="'.wp_kses_post($hoursText).'" data-min="'.wp_kses_post($minutesText).'" data-sec="'.wp_kses_post($secondsText).'"';
    }
    
    if($style == 'style-2'){
        $dataAttr .= ' data-filptheme = "'.esc_attr($flipTheme).'"';
    }
    $dataAttr .= ' data-countdata= \'' .esc_attr(wp_json_encode($countData)). '\'';

    $output .= '<div class="tp-countdown tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' countdown-'.esc_attr($style).'"  data-offset="'.esc_attr($offset_time).'"  '.$dataAttr.'>';
        if($countdownSelection == 'normal') {
            if($future >= $now && isset($future)) {
                if($style == 'style-1') {
                    $inline_style = (!empty($attributes["inlineStyle"])) ? 'count-inline-style' : '';
                    
                    $output .= '<div class="tpgb-countdown-counter '.esc_attr($inline_style).'" data-time = "'.esc_attr($datetime).'">';
                        $output .= '<div class="count_1">';
                            $output .= '<span class="days">'.esc_html__('00','the-plus-addons-for-block-editor').'</span>';
                            if(!empty($showLabels) && $showLabels==true) {
                                $output .= '<h6 class="days_ref">'.esc_html($daysText).'</h6>';
                            }
                        $output .= '</div>';
                        $output .= '<div class="count_2">';
                            $output .= '<span class="hours">'.esc_html__('00','the-plus-addons-for-block-editor').'</span>';
                            if(!empty($showLabels) && $showLabels==true) {
                                $output .= '<h6 class="hours_ref">'.esc_html($hoursText).'</h6>';
                            }
                        $output .= '</div>';
                        $output .= '<div class="count_3">';
                            $output .= '<span class="minutes">'.esc_html__('00','the-plus-addons-for-block-editor').'</span>';
                            if(!empty($showLabels) && $showLabels==true) {
                                $output .= '<h6 class="minutes_ref">'.esc_html($minutesText).'</h6>';
                            }
                        $output .= '</div>';
                        $output .= '<div class="count_4">';
                            $output .= '<span class="seconds last">'.esc_html__('00','the-plus-addons-for-block-editor').'</span>';
                            if(!empty($showLabels) && $showLabels==true) {
                                $output .= '<h6 class="seconds_ref">'.esc_html($secondsText).'</h6>';
                            }
                        $output .= '</div>';
                    $output .= '</div>';
                } elseif($style == 'style-2') {
                    $output .= '<div id="tpgb-block-'.esc_attr($block_id).'" class="flipdown tpgb-countdown-counter" data-time='.esc_attr(strtotime($datetime)).'></div>';
                } elseif($style == 'style-3') {
                    if(!empty($attributes['datetime'])) {
                        $datetime= $future->format('c');
                    } else {
                        $datetime = '2020-08-10T16:42:00';
                    }
                    
                    $output .= '<div class="tpgb-countdown-counter" data-time="'.esc_attr($datetime).'">';
                        $output .= '<div class="counter-part"><div class="day-'.esc_attr($block_id).' day" id="dtpgb-block-'.esc_attr($block_id).'"></div></div>';
                        $output .= '<div class="counter-part"><div class="hour-'.esc_attr($block_id).' hour" id="htpgb-block-'.esc_attr($block_id).'"></div></div>';
                        $output .= '<div class="counter-part"><div class="min-'.esc_attr($block_id).' min" id="mtpgb-block-'.esc_attr($block_id).'"></div></div>';
                        $output .= '<div class="counter-part"><div class="sec-'.esc_attr($block_id).' sec" id="stpgb-block-'.esc_attr($block_id).'"></div></div>';
                    $output .= '</div>';
                }
            } else {
                if($attributes['countdownExpiry'] == 'showmsg') {
                    $output .= '<div class="tpgb-countdown-expiry">'.esc_html($attributes['expiryMsg']).'</div>';
                }
            }
        }
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

function tpgb_tp_countdown_render() {

    $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_countdown_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action('init', 'tpgb_tp_countdown_render');