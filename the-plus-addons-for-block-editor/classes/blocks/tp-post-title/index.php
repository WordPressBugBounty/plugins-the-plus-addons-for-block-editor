<?php
/* Tp Block : Post Title
 * @since	: 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_post_title_render_callback( $attr, $content) {
	$output = '';
	$post_id = get_the_ID();
    $post = get_queried_object();

    $block_id = (!empty($attr['block_id'])) ? $attr['block_id'] : uniqid("title");
	$types = (!empty($attr['types'])) ? $attr['types'] : 'singular';
	$titlePrefix = (!empty($attr['titlePrefix'])) ? $attr['titlePrefix'] : '';
	$titlePostfix = (!empty($attr['titlePostfix'])) ? $attr['titlePostfix'] : false;
	$postLink = (!empty($attr['postLink'])) ? $attr['postLink'] : false;
	$titleTag = (!empty($attr['titleTag'])) ? $attr['titleTag'] : 'h1';
	$limitCountType = (!empty($attr['limitCountType'])) ? $attr['limitCountType'] : '';
    $titleLimit = (!empty($attr['titleLimit'])) ? $attr['titleLimit'] : '';
	$hideDots = (!empty($attr['hideDots'])) ? $attr['hideDots'] : false;
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attr );
	
	if( $types == 'archive' ){
		
		$is_archive = is_archive();
		if ( ! $is_archive && !is_search()) {
			return '';
		}

		$title = '';
		if ( $is_archive || is_search()) {
			add_filter( 'get_the_archive_title', function ($title) {    
				if ( is_category() ) {    
					$title = single_cat_title( '', false );    
				} else if ( is_tag() ) {    
					$title = single_tag_title( '', false );    
				} else if ( is_author() ) {    
					$title = '<span class="vcard">' . get_the_author() . '</span>' ;    
				} else if ( is_tax() ) {
					$title = single_term_title( '', false );
				} else if (is_post_type_archive()) {
					$title = post_type_archive_title( '', false );
				} else if (is_search()) {
					$title = get_search_query();
				}
				return $title;    
			});
			
			$title = get_the_archive_title();
		}

		if( $limitCountType == 'words' && !empty($title) ){
			$title = wp_trim_words( $title, (int) $titleLimit);
		} else if( $limitCountType == 'letters' && !empty($title) ){
			$title = substr(wp_trim_words($title),0, (int) $titleLimit) . (!empty($hideDots) ? '' : '...' );
		}
	}else if($types =='singular'){
		$title = get_the_title($post_id);
		if( $limitCountType == 'words' && !empty($title) ){
			$title = wp_trim_words($title, (int) $titleLimit);
		} else if( $limitCountType == 'letters' && !empty($title) ){
			$title = substr(wp_trim_words($title),0, (int) $titleLimit) . (!empty($hideDots) ? '' : '...' );
		}
	}
	
	$prefixOutput = '';
	if(!empty($titlePrefix)){
		$prefixOutput = '<span class="tp-prepost-title tp-prefix-title">'.esc_html($titlePrefix).'</span>';
	}
	$postfixOutput = '';
	if(!empty($titlePostfix)){
		$postfixOutput = '<span class="tp-prepost-title tp-postfix-title">'.esc_html($titlePostfix).'</span>';
	}
	
    $output .= '<div class="tpgb-post-title tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'" >';
		if(!empty($postLink)){
			$output .= '<a href="'.esc_url(get_the_permalink()).'" >'; 
		}
			$output .= '<'.Tp_Blocks_Helper::validate_html_tag($titleTag).' class="tpgb-entry-title" >';
				$output .= $prefixOutput . wp_kses_post($title) . $postfixOutput;	
			$output .= '</'.Tp_Blocks_Helper::validate_html_tag($titleTag).'>';
		if(!empty($postLink)){
			$output .= '</a>';
		}
    $output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attr, $output);

    return $output;
	}

/**
 * Render for the server-side
 */
function tpgb_post_title_content() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_post_title_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_post_title_content' );