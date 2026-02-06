<?php
/**
 * Block : Code highlighter
 * @since 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_code_highlighter_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$languageType = (!empty($attributes['languageType'])) ? $attributes['languageType'] : 'markup';
	$themeType = (!empty($attributes['themeType'])) ? $attributes['themeType'] : 'prism-default';

	if(class_exists('Tpgbp_Pro_Blocks_Helper')){
		$sourceCode = (!empty($attributes['sourceCode'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['sourceCode'],['blockName' => 'tpgb/tp-code-highlighter']) : '';
		$languageText = (!empty($attributes['languageText'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['languageText']) : '';
		$copyText = (!empty($attributes['copyText'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['copyText']) : '';
	}else{
		$sourceCode = (!empty($attributes['sourceCode'])) ? $attributes['sourceCode'] : '';
		$languageText = (!empty($attributes['languageText'])) ? $attributes['languageText'] : '';
		$copyText = (!empty($attributes['copyText'])) ? $attributes['copyText'] : '';
	}
	
	$copyIcnType = (!empty($attributes['copyIcnType'])) ? $attributes['copyIcnType'] : 'none';
	$copyIconStore = (!empty($attributes['copyIconStore'])) ? $attributes['copyIconStore'] : '';

	if(class_exists('Tpgbp_Pro_Blocks_Helper')){
		$copiedText = (!empty($attributes['copiedText'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['copiedText']) : '';
		$copyErrorText = (!empty($attributes['copyErrorText'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['copyErrorText']) : '';
	}else{
		$copiedText = (!empty($attributes['copiedText'])) ? $attributes['copiedText'] : '';
		$copyErrorText = (!empty($attributes['copyErrorText'])) ? $attributes['copyErrorText'] : '';
	}

	$copiedIcnType = (!empty($attributes['copiedIcnType'])) ? $attributes['copiedIcnType'] : 'none';
	$copiedIconStore = (!empty($attributes['copiedIconStore'])) ? $attributes['copiedIconStore'] : '';
	$lineNumber = (!empty($attributes['lineNumber'])) ? $attributes['lineNumber'] : false;
	
	if(class_exists('Tpgbp_Pro_Blocks_Helper')){
		$lineHighlight = (!empty($attributes['lineHighlight'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['lineHighlight']) : '';
		$dwnldBtnText = (!empty($attributes['dwnldBtnText'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($attributes['dwnldBtnText']) : '';
	}else{
		$lineHighlight = (!empty($attributes['lineHighlight'])) ? $attributes['lineHighlight'] : '';
		$dwnldBtnText = (!empty($attributes['dwnldBtnText'])) ? $attributes['dwnldBtnText'] : '';
	}
	$dnloadBtn = (!empty($attributes['dnloadBtn'])) ? $attributes['dnloadBtn'] : false;
	
	$dwnldIcnType = (!empty($attributes['dwnldIcnType'])) ? $attributes['dwnldIcnType'] : 'none';
	$dwnldIconStore = (!empty($attributes['dwnldIconStore'])) ? $attributes['dwnldIconStore'] : '';

    if(class_exists('Tpgbp_Pro_Blocks_Helper')){
        $fileLink = (isset($attributes['fileLink']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['fileLink']) : (!empty($attributes['fileLink']['url']) ? $attributes['fileLink']['url'] : '');
    }else{
        $fileLink = (!empty($attributes['fileLink']['url'])) ? $attributes['fileLink']['url'] : '';
    }
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$langtext = $lineNumClass = $dwnldBtnClass = $cpybtnicon = $copiedbtnicon = $dwndicon = '';
	if(!empty($languageText)){
		$langtext =  'data-label="'.esc_html($languageText).'"';
	}
	if(!empty($lineNumber)){
		$lineNumClass = 'line-numbers';
	}
	if(!empty($dnloadBtn) && !empty($fileLink)) {
		$dwnldBtnClass = 'data-src="'.esc_url($fileLink).'" data-download-link="'.esc_url($fileLink).'" data-download-link-label="'.esc_attr($dwnldBtnText).'"';
		if($dwnldIcnType=='icon'){
			$dwndicon = $dwnldIconStore;
		}
	}
	if($copyIcnType=='icon'){
		$cpybtnicon = $copyIconStore;
	}
	if($copiedIcnType=='icon'){
		$copiedbtnicon = $copiedIconStore;
	}
	
	// Set Dataattr For Circle Menu
	$codeAttr = [
		'id' => $block_id,
		'copytext' => $copyText,
		'copyicon' => $cpybtnicon,
		'copiedText' => $copiedText,
		'copiedicon' => $copiedbtnicon,
		'downloadtext' => $dwnldBtnText,
		'downloadicon' => $dwndicon
	];
	$codeAttr = htmlspecialchars(json_encode($codeAttr), ENT_QUOTES, 'UTF-8');

	$output = '';
    $output .= '<div class="tpgb-code-highlighter tpgb-relative-block code-'.esc_attr($themeType).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'" data-code-atr= \'' .$codeAttr. '\'>';
		$output .='<pre class="language-'.esc_attr($languageType).' '.esc_attr($lineNumClass).'" data-line="'.esc_attr($lineHighlight).'" '.$dwnldBtnClass.' '.$langtext.'>';
			$output .='<code class="language-'.esc_attr($languageType).'" data-prismjs-copy="'.esc_attr($copyText).'" data-prismjs-copy-error="'.esc_attr($copyErrorText).'" data-prismjs-copy-success="'.esc_attr($copiedText).'">';
				$output .= esc_html($sourceCode);
			$output .='</code>';
		$output .='</pre>';
    $output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_code_highlighter() {

    $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_code_highlighter_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_code_highlighter' );