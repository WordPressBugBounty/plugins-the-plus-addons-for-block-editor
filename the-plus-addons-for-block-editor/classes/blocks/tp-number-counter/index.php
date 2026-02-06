<?php
/* Block : Number Counter
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_number_counter_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
	$title = (!empty($attributes['title'])) ? $attributes['title'] : '';
	$style1Align = (!empty($attributes['style1Align'])) ? $attributes['style1Align'] : 'text-center';
	$style2Align = (!empty($attributes['style2Align'])) ? $attributes['style2Align'] : 'text-left';
	$numValue = (!empty($attributes['numValue'])) ? $attributes['numValue'] : '1000';
	$startValue = (!empty($attributes['startValue'])) ? $attributes['startValue'] : '0';
	$timeDelay = (!empty($attributes['timeDelay'])) ? $attributes['timeDelay'] : '5';
	$numeration = (!empty($attributes['numeration'])) ? $attributes['numeration'] : 'default';
	$numGap = (!empty($attributes['numGap'])) ? $attributes['numGap'] : '5';
	$symbol = (!empty($attributes['symbol'])) ? $attributes['symbol'] : '';
	$symbolPos = (!empty($attributes['symbolPos'])) ? $attributes['symbolPos'] : 'after';
	$iconType = (!empty($attributes['iconType'])) ? $attributes['iconType'] : 'icon';
	$iconStyle = (!empty($attributes['iconStyle'])) ? $attributes['iconStyle'] : 'square';
	$iconStore = (!empty($attributes['iconStore'])) ? $attributes['iconStore'] : '';
	$linkURL = (!empty($attributes['linkURL']['url'])) ? $attributes['linkURL']['url'] : '';
	$imagestore = (!empty($attributes['imagestore'])) ? $attributes['imagestore'] : TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg';
	$imageSize = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] : 'thumbnail';
	$target = (!empty($attributes['linkURL']['target'])) ? ' target="_blank" ': '';
	$nofollow = (!empty($attributes['linkURL']['nofollow'])) ? ' rel="nofollow" ' : '';
	$verticalCenter = (!empty($attributes['verticalCenter'])) ? $attributes['verticalCenter'] : false;
	$preSymbol = (!empty($attributes['preSymbol'])) ? $attributes['preSymbol'] : '';
	
	$svgIcon = (!empty($attributes['svgIcon'])) ? $attributes['svgIcon'] : '';
	$svgDraw = (!empty($attributes['svgDraw'])) ? $attributes['svgDraw'] : 'delayed';
	$svgstroColor = (!empty($attributes['svgstroColor'])) ? $attributes['svgstroColor'] : '';
	$svgfillColor = (!empty($attributes['svgfillColor'])) ? $attributes['svgfillColor'] : 'none';
    $svgstroHov = (!empty($attributes['svgstroHov'])) ? $attributes['svgstroHov'] : '';
    $svgfillHov = (!empty($attributes['svgfillHov'])) ? $attributes['svgfillHov'] : '';
	$svgDura = (!empty($attributes['svgDura'])) ? $attributes['svgDura'] : 90;
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$altText = (isset($imagestore['alt']) && !empty($imagestore['alt'])) ? esc_attr($imagestore['alt']) : ((!empty($imagestore['title'])) ? esc_attr($imagestore['title']) : esc_attr__('Counter Number','the-plus-addons-for-block-editor'));

	if(!empty($imagestore) && !empty($imagestore['id'])){
		$imgSrc = wp_get_attachment_image($imagestore['id'] , $imageSize, false, ['class' => 'counter-icon-image', 'alt'=> $altText]);
	}else if(!empty($imagestore['url'])){

		$imgUrl = (isset($imagestore['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($imagestore) : $imagestore['url'];
		$imgSrc = '<img class="counter-icon-image" src='.esc_url($imgUrl).' alt="'.$altText.'"/>';
	}else if(!is_array($imagestore)){
		$imgSrc = '<img class="counter-icon-image" src='.esc_url($imagestore).' alt="'.$altText.'"/>';
	}else {
		$imgSrc = '<img class="counter-icon-image" src='.esc_url(TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg').' alt="'.$altText.'"/>';
	}
	
	$vCenter = '';
	if(!empty($verticalCenter)){
		$vCenter='vertical-center';
	}
	
	$alignment = '';
	if($style=='style-1'){
		$alignment=$style1Align;
	}
	if($style=='style-2'){
		$alignment=$style2Align;
	}
	$tranease = 'tpgb-trans-ease';
		
	$getCounterNo = '';
	$getCounterNo .= '<h5 class="nc-counter-number '.esc_attr($tranease).'">';
		if( (!empty($symbol) && $symbolPos=='before') || (!empty($preSymbol) && $symbolPos=='both') ){
			$getCounterNo .= '<span class="counter-symbol-text">'.( (!empty($preSymbol) && $symbolPos=='both') ? wp_kses_post($preSymbol) : wp_kses_post($symbol)).'</span>';
		}

		//Get Dynamic Value
		$numValue = (!empty($numValue) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($numValue) : $numValue;
		$startValue = (!empty($startValue) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($startValue) : $startValue;

		$getCounterNo .= '<span class="counter-number-inner numscroller" data-min="'.esc_attr($startValue).'" data-max="'.esc_attr($numValue).'" data-delay="'.esc_attr($timeDelay).'" data-increment="'.esc_attr($numGap).'" data-numeration="'.esc_attr($numeration).'">';
			$getCounterNo .= tpgb_formatNumber($startValue, $numeration);
		$getCounterNo .= '</span>';
		if( (!empty($symbol) && $symbolPos=='after') || $symbolPos=='both' ){
			$getCounterNo .= '<span class="counter-symbol-text">'.wp_kses_post($symbol).'</span>';
		}
	$getCounterNo .= '</h5>';
        
    // Set Dynamic URL For Title Link
    if(class_exists('Tpgbp_Pro_Blocks_Helper')){
		$linkURL = (isset($attributes['linkURL']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['linkURL']) : (!empty($attributes['linkURL']['url']) ? $attributes['linkURL']['url'] : '');
	}

	$getTitle = '';
	$link_attr = Tp_Blocks_Helper::add_link_attributes($attributes['linkURL']);
	$ariaLabel = (!empty($attributes['ariaLabel'])) ? esc_attr($attributes['ariaLabel']) : ((!empty($title)) ? esc_attr($title) : esc_attr__("Number Counter", 'the-plus-addons-for-block-editor'));
	if(!empty($linkURL)){
		$getTitle .='<a href="'.esc_url($linkURL).'" '.$target.' '.$nofollow.' '.$link_attr.' aria-label="'.esc_attr($title).'">';
	}
	$getTitle .= '<h6 class="counter-title '.esc_attr($tranease).'">'.wp_kses_post($title).'</h6>';
	if(!empty($linkURL)){
		$getTitle .= '</a>';
	}
	
	$getIcon = '';
	if(!empty($linkURL)){
		$getIcon .='<a href="'.esc_url($linkURL).'" '.$target.' '.$nofollow.' '.$link_attr.' aria-label="'.$ariaLabel.'">';
	}
			$getIcon .= '<div class="counter-icon-inner shape-icon-'.esc_attr($iconStyle).' '.esc_attr($tranease).'">';
				$getIcon .= '<span class="counter-icon '.esc_attr($tranease).'">';
					$getIcon .= '<i class="'.esc_attr($iconStore).'"></i>';
				$getIcon .= '</span>';
			$getIcon .= '</div>';
	if(!empty($linkURL)){
		$getIcon .= '</a>';
	}
	
	$getImg = '';
	if(!empty($linkURL)){
		$getImg .= '<a href="'.esc_url($linkURL).'" '.$target.' '.$nofollow.' '.$link_attr.' aria-label="'.$ariaLabel.'">';
	}
			$getImg .= '<div class="counter-image-inner '.esc_attr($tranease).'">';
				$getImg .= $imgSrc;
			$getImg .= '</div>';
	if(!empty($linkURL)){
		$getImg .= '</a>';
	}
	
	$getsvg = '';
	$getsvg .= '<div class="tpgb-draw-svg" data-id="service-svg-'.esc_attr($block_id).'" data-type="'.esc_attr($svgDraw).'" data-duration="'.esc_attr($svgDura).'" data-stroke="'.esc_attr($svgstroColor).'" data-fillColor="'.esc_attr($svgfillColor).'" data-strokeColorHover="'.esc_attr($svgstroHov).'" data-fillColorHover="'.esc_attr($svgfillHov).'" data-fillEnable="yes">';
	if(!empty($linkURL)){
		$getsvg .= '<a href="'.esc_url($linkURL).'" '.$target.' '.$nofollow.' '.$link_attr.' aria-label="'.$ariaLabel.'">';
	}
		
		$getsvg .= '<object id="service-svg-'.esc_attr($block_id).'" type="image/svg+xml" data="'.esc_url($svgIcon['url']).'" aria-label="'.esc_attr__('icon','the-plus-addons-for-block-editor').'"></object>';

	if(!empty($linkURL)){
		$getsvg .= '</a>';
	}
	$getsvg .= '</div>';
	$output = '';
    $output .= '<div class="tpgb-number-counter tpgb-relative-block counter-'.esc_attr($style).' '.esc_attr($alignment).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		$output .= '<div class="number-counter-inner tpgb-relative-block '.esc_attr($tranease).' '.esc_attr($vCenter).'">';
			if($style=='style-1'){
				$output .= '<div class="counter-wrap-content">';
					if($iconType=='icon'){
						$output .= $getIcon;
					}
					if($iconType=='img'){
						$output .= $getImg;
					}
					if($iconType=='svg'){
						$output .= $getsvg;
					}
					$output .= $getCounterNo;
					if(!empty($title)){
						$output .= $getTitle;
					}
				$output .= '</div>';
			}
			if($style=='style-2'){
				$output .= '<div class="icn-header">';
					if($iconType=='icon'){
						$output .= $getIcon;
					}
					if($iconType=='img'){
						$output .= $getImg;
					}
					if($iconType=='svg'){
						$output .= $getsvg;
					}
				$output .= '</div>';
				$output .= '<div class="counter-content">';
					$output .= $getCounterNo;
					if(!empty($title)){
						$output .= $getTitle;
					}
				$output .= '</div>';
			}
		$output .= '</div>';
    $output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

function tpgb_formatNumber($number, $numeration) {
	if($numeration == 'indian'){
		$x = strval($number);
		$lastThree = substr($x, -3);
		$otherNumbers = substr($x, 0, -3);
		if ($otherNumbers != '')
			$lastThree = ',' . $lastThree;
		$res = preg_replace('/\B(?=(\d{2})+(?!\d))/','-', $otherNumbers) . $lastThree;
		return $res;
	}else if($numeration == 'international'){
		return number_format($number);
	}else{
		return $number;
	}
}

/**
 * Render for the server-side
 */
function tpgb_number_counter() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_number_counter_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_number_counter' );