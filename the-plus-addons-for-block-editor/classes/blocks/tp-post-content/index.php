<?php
/* Tp Block : Post Content
 * @since	: 2.0.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * Common function to limit content by words or letters, with optional dots.
 */
function tpgb_limit_content($content, $limitCountType, $titleLimit, $chideDots = false) {
    if ($limitCountType === 'words' && !empty($content)) {
        return wp_trim_words($content, $titleLimit);
    } else if ($limitCountType === 'letters' && !empty($content)) {
        return substr(wp_trim_words($content), 0, $titleLimit) . (!empty($chideDots) ? '' : '...');
    } else {
        return $content;
    }
}

function tpgb_tp_post_content_render_callback( $attr, $content, $block) {
	$output = '';
	$post_id = get_the_ID();
    $post = get_queried_object();
    $block_id = (!empty($attr['block_id'])) ? $attr['block_id'] : uniqid("title");
	$types = (!empty($attr['types'])) ? $attr['types'] : 'singular';
	$contentType = (!empty($attr['contentType'])) ? $attr['contentType'] :'';
	$limitCountType = (!empty($attr['limitCountType'])) ? $attr['limitCountType'] :'';
	$titleLimit = (!empty($attr['titleLimit'])) ? $attr['titleLimit'] :'';
    $chideDots = (!empty($attr['chideDots'])) ? $attr['chideDots'] : false ;
    $archiveCnt = (!empty($attr['archiveCnt'])) ? $attr['archiveCnt'] :'';
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attr );
	
	$content = '';
	if($types == 'archive'){
       
		if ( is_category() || is_tag() || is_tax() || is_archive() ) {
            if($archiveCnt == 'postExcerpt'){
                $excerpt = get_post_field('post_excerpt', $post_id, 'display');
                if (!empty($excerpt)) {
                    $content = tpgb_limit_content($excerpt, $limitCountType, $titleLimit, $chideDots);
                } else {
                    $content = get_the_excerpt();
                    $content = tpgb_limit_content($content, $limitCountType, $titleLimit, $chideDots);
                }
            }else{
                $content = term_description();
            }
		}
	}else{
		if($contentType == 'postContent'){
			
			static $views_ids = array();
			if ( ! isset( $post_id ) ) {
				return '';
			}
			if ( isset( $views_ids[ $post_id ] ) ) {
				$is_debug = defined( 'WP_DEBUG' ) && WP_DEBUG &&
					defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

				return $is_debug ?
					esc_html__( 'Block Re-rendering halted', 'the-plus-addons-for-block-editor') :
					'';
			}

			$views_ids[ $post_id ] = true;

			global $current_screen;
			if ( isset($current_screen) && method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor() ) {
				$content = wp_strip_all_tags(get_the_content( '',true, $post));
			} else {
				$post = get_post($post_id);
				if ( ! $post || 'nxt_builder' == $post->post_type) {
					return '';
				}
				
				if ( ('publish' !== $post->post_status && 'draft' !== $post->post_status && 'private' !== $post->post_status) || ! empty( $post->post_password ) ) {
					return '';
				}
				
				$content = apply_filters( 'the_content', $post->post_content );
			}
			unset( $views_ids[ $post_id ] );
		}else{
			$excerpt = get_post_field('post_excerpt', $post_id, 'display');
			if (!empty($excerpt)) {
				$content = tpgb_limit_content($excerpt, $limitCountType, $titleLimit, $chideDots);
			} else {
				$content = get_the_excerpt();
				$content = tpgb_limit_content($content, $limitCountType, $titleLimit, $chideDots);
			}
		}
	}

    $output .= '<div class="tpgb-post-content tpgb-block-'.esc_attr($block_id ).' '.esc_attr($blockClass).'" >';
		$output .= '<div class="tpgb-entry-content tpgb-trans-linear">';
			$output .= $content;
		$output .= '</div>';
    $output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attr, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_post_content() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_post_content_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_post_content' );