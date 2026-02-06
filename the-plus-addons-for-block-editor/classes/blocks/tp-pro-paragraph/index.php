<?php
/**
 * Block : TP Pro Paragraph
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_pro_paragraph_render_callback( $attributes, $content ) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");

	$pattern = '/\btpgb-block-'.esc_attr($block_id).'/';
	
	if (preg_match($pattern, $content)) {
		if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attributes,$content);
        }
		return $content;
	}
	
    $title = (!empty($attributes['title'])) ? $attributes['title'] : '';
    $Showtitle = (!empty($attributes['Showtitle'])) ? $attributes['Showtitle'] : false;
    $titleTag = (!empty($attributes['titleTag'])) ? $attributes['titleTag'] : 'h3';
	$content = (!empty($attributes['content'])) ? $attributes['content'] : '';
	$descTag = (!empty($attributes['descTag'])) ? $attributes['descTag'] : 'p';
	$dropCap = (!empty($attributes['dropCap'])) ? $attributes['dropCap'] : false;
	$dcapView = (!empty($attributes['dcapView'])) ? $attributes['dcapView'] : '';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
    $output .= '<div class="tpgb-pro-paragraph tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		if(!empty($Showtitle) && !empty($title)){
			$output .= '<'.Tp_Blocks_Helper::validate_html_tag($titleTag).' class="pro-heading-inner">';
				$output .= wp_kses_post($title);
			$output .= '</'.Tp_Blocks_Helper::validate_html_tag($titleTag).'>';
		}
		if(!empty($content)){

			if(class_exists('Tpgbp_Pro_Blocks_Helper')){
				$content = (!empty($attributes['content'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($content,['blockName' => 'tpgb/tp-pro-paragraph']) : '';
			}

			$output .= '<div class="pro-paragraph-inner '.( !empty($dropCap) ? ' tpgb-drop-cap tpgb-drop-'.esc_attr($dcapView).'' : '' ).'">';
				$output .= '<'.Tp_Blocks_Helper::validate_html_tag($descTag).' class="tpgb-propara-txt">'.wp_kses_post($content).'</'.Tp_Blocks_Helper::validate_html_tag($descTag).'>';
			$output .= '</div>';
		}
	$output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_pro_paragraph() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_pro_paragraph_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_pro_paragraph' );