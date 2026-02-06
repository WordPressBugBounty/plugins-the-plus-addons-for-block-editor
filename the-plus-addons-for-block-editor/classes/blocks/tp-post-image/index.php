<?php
/* Tp Block : Post Image
 * @since	: 1.2.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_post_image_render_callback( $attr, $content) {
	$output = '';
	$post_id = get_the_ID();

    $block_id = (!empty($attr['block_id'])) ? $attr['block_id'] : uniqid("title");
	$imageType = (!empty($attr['imageType'])) ? $attr['imageType'] : 'default';
	$bgLocation = (!empty($attr['bgLocation'])) ? $attr['bgLocation'] : 'section';
	$imageSize = (!empty($attr['imageSize'])) ? $attr['imageSize'] : 'full';
    $fancyBox = (!empty($attr['fancyBox'])) ? $attr['fancyBox'] : false;
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attr );
	
	$data_attr = [];
	if(!empty($imageType) && $imageType=='background'){
		$blockClass .= ' post-img-bg';
		$data_attr['id'] = $block_id;
		$data_attr['imgType'] = $imageType;
		$data_attr['imgLocation'] = $bgLocation;
	}
	
	$data_attr = json_encode($data_attr);

    $image_content ='';
	if (has_post_thumbnail( $post_id ) ){
		$image_content = get_the_post_thumbnail_url($post_id,$imageSize);
		$fancy_content = get_the_post_thumbnail_url($post_id, 'full' );
		$image_content = (!empty($image_content)) ? $image_content : TPGB_ASSETS_URL. 'assets/images/tpgb-placeholder.jpg';
		
		// Set Fancy Box Option
		$data_settings = $data_fancy = $href = '';
		if(!empty($fancyBox)){
			$FancyData = (!empty($attr['FancyOption'])) ? json_decode($attr['FancyOption']) : [];

			$button = array();
			if (is_array($FancyData) || is_object($FancyData)) {
				foreach ($FancyData as $value) {
					$buttonOpt = (($value->value == 'zoom') ? 'iterateZoom' : (($value->value == 'fullScreen') ? 'fullscreen' : $value->value));
					if($value->value != 'share'){
						$button[] = $buttonOpt;
					}
				}
			}
			$href = $fancy_content;
			$fancybox = array();
			$fancybox['button'] = $button;
			// $fancybox['animationEffect'] = $attr['AnimationFancy'];
			// $fancybox['animationDuration'] = $attr['DurationFancy'];
			$data_settings .= ' data-fancy-option=\''.json_encode($fancybox).'\'';
			$data_settings .= ' data-id="'.esc_attr($block_id).'" ';
			$data_fancy = 'data-fancybox="postImg-'.esc_attr($block_id).'"';

		}else{
			$href = get_the_permalink();
		}
		
		$output .= '<div class="tpgb-post-image tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' '.(!empty($fancyBox) ? 'tpgb-fancy-add' : '').'" data-setting=\'' . $data_attr . '\' '.$data_settings.'>';
			
				if(!empty($imageType) && $imageType!='background'){
					$output .= '<div class="tpgb-featured-image">';
						$output .= '<a href="'.esc_url($href).'" '.$data_fancy.'>';
							$output .= get_the_post_thumbnail($post_id,$imageSize,[ 'class' => 'tpgb-featured-img']);
						$output .= '</a>';
					$output .= '</div>';
				}else if(!empty($imageType) && $imageType=='background'){
					$output .= '<a href="'.esc_url($href).'" '.$data_fancy.'>';
						$output .= '<div class="tpgb-featured-image" style="background-image: url('.esc_url($image_content).')"></div>';
					$output .= '</a>';
				}
			
		$output .= "</div>";
	}
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attr, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_post_image_content() {
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_post_image_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_post_image_content' );