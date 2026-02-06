<?php
/* Block : External Form Styler
 * @since : 1.1.3
 */
defined( 'ABSPATH' ) || exit;

function tpgb_external_form_styler_render_callback( $attributes, $content) {
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $contactForm = (!empty($attributes['contactForm'])) ? $attributes['contactForm'] : '';
    $formType = (!empty($attributes['formType'])) ? $attributes['formType'] : 'contact-form-7';
    $titleShow = (!empty($attributes['titleShow'])) ? $attributes['titleShow'] : false;
    $outerSecStyle = (!empty($attributes['outerSecStyle'])) ? $attributes['outerSecStyle'] : 'tpgb-cf7-label';

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$titleShowLine = '';
	if($formType=='gravity-form' && !empty($titleShow)){
		$titleShowLine .= 'title=true description=true';
	} else if($formType=='gravity-form' && empty($titleShow)){
		$titleShowLine .= 'title=false description=false';
	}
	$cf7class = '';
	if($formType=='contact-form-7'){
		$cf7class = $outerSecStyle;
	}
	$output = '';
	$output .= '<div class="tpgb-external-form-styler tpgb-relative-block tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
		if($contactForm==''){
			$output .= '<div class="tpgb-select-form-alert">'.esc_html__('Please select Form','the-plus-addons-for-block-editor').'</div>';
		} else {
			$sc = 'id="'.esc_attr($contactForm).'"';
			$shortcode   = [];
			if($formType=='contact-form-7'){
				$shortcode[] = sprintf( '[contact-form-7 %s]', $sc );
			} else if($formType=='everest-form'){
				$shortcode[] = sprintf( '[everest_form %s]', $sc );
			} else if($formType=='gravity-form'){
				$shortcode[] = sprintf( '[gravityform %s '.$titleShowLine.']', $sc );
			} else if($formType=='ninja-form'){
				$shortcode[] = sprintf( '[ninja_form %s]', $sc );
			} else if($formType=='wp-form'){
				$shortcode[] = sprintf( '[wpforms %s]', $sc );
			}

			$shortcode_str = implode("", $shortcode);
			
			$output .='<div class="tpgb-'.esc_attr($formType).' '.esc_attr($cf7class).'">';
				$output .= do_shortcode( $shortcode_str );				
			$output .= '</div>';
		}
	$output .= '</div>';
  
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}
function tpgb_get_form_rendered(){
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'You can not Permission.' );
	}
    $form_id = isset($_POST['form_id']) ? wp_unslash($_POST['form_id']) : '';
    $form_type = isset($_POST['form_type']) ? sanitize_text_field(wp_unslash($_POST['form_type'])) : '';
	
	if (!empty($form_id) && $form_type=='contact-form-7'){
		echo do_shortcode ( "[contact-form-7 id=".esc_attr($form_id)."]" );
	} else if(!empty($form_id) && $form_type=='everest-form'){
		echo do_shortcode ( "[everest_form id=".esc_attr($form_id)."]" );
	} else if(!empty($form_id) && $form_type=='gravity-form'){
		echo do_shortcode ( "[gravityform id=".esc_attr($form_id)." title=false description=false]" );
	} else if(!empty($form_id) && $form_type=='ninja-form'){
		echo do_shortcode ( "[ninja_form id=".esc_attr($form_id)."]" );
	} else if(!empty($form_id) && $form_type=='wp-form'){
		echo do_shortcode ( "[wpforms id=".esc_attr($form_id)."]" );
	}
    exit();
}
add_action('wp_ajax_tpgb_external_form_ajax', 'tpgb_get_form_rendered');
/**
 * Render for the server-side
 */
function tpgb_external_form_styler() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_external_form_styler_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_external_form_styler');