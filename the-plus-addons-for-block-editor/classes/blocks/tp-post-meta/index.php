<?php
/* Tp Block : Post Meta
 * @since	: 3.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_post_meta_render_callback( $attr, $content) {
	$output = '';
	$post_id = '';

	if( is_archive() ){
		$post_id = get_queried_object_id();
	}else{
		$post_id = get_the_ID();
	}
    

    $block_id = (!empty($attr['block_id'])) ? $attr['block_id'] : uniqid("title");
	$showDate = (!empty($attr['showDate'])) ? $attr['showDate'] : false;
	$showCategory = (!empty($attr['showCategory'])) ? $attr['showCategory'] : false;
	$showAuthor = (!empty($attr['showAuthor'])) ? $attr['showAuthor'] : false;
	$showComment = (!empty($attr['showComment'])) ? $attr['showComment'] : false;
	$metaSort = (!empty($attr['metaSort'])) ? (Array)$attr['metaSort'] :'';
	$metaLayout = (!empty($attr['metaLayout'])) ? $attr['metaLayout'] :'';
	$taxonomySlug = (!empty($attr['taxonomySlug'])) ? $attr['taxonomySlug'] : 'category';
	$metafieldRep = (!empty($attr['metafieldRep'])) ? $attr['metafieldRep'] : [] ;
    $readPrefix = (!empty($attr['readPrefix'])) ? $attr['readPrefix'] : '';
	$showreadTime = (!empty($attr['showreadTime'])) ? $attr['showreadTime'] : false;
	$dateType = (!empty($attr['dateType'])) ? $attr['dateType'] : '';
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attr );
	
	$outputDate='';
	if($showDate){
		$datePrefix = (!empty($attr['datePrefix'])) ? '<span class="tpgb-meta-date-label">'.wp_kses_post($attr['datePrefix']).'</span>' : '';
		$dateIcon = (!empty($attr['dateIcon'])) ? '<i class="meta-date-icon '.esc_attr($attr['dateIcon']).'"></i>' : '';

		if ($dateType === 'modified') {
			$outputDate .= '<span class="tpgb-meta-date">' . $datePrefix . '<a href="' . esc_url(get_the_permalink()) . '">' . $dateIcon . esc_html(get_the_modified_date()) . '</a></span>';
		} else {
			$outputDate .= '<span class="tpgb-meta-date">' . $datePrefix . '<a href="' . esc_url(get_the_permalink()) . '">' . $dateIcon . esc_html(get_the_date()) . '</a></span>';
		}
	}
	
	
	$outputCategory='';
	if( $showCategory ){  //&& !empty(get_the_category($post_id)) 
		$catePrefix = (!empty($attr['catePrefix'])) ? '<span class="tpgb-meta-category-label">'.wp_kses_post($attr['catePrefix']).'</span>' : '';
		$cateDisplayNo = (!empty($attr['cateDisplayNo'])) ? $attr['cateDisplayNo'] : 0;
		$cateStyle = (!empty($attr['cateStyle'])) ? $attr['cateStyle'] : 'style-1';

		$terms = get_the_terms( $post_id, $taxonomySlug, array("hide_empty" => true) );
        if( is_archive() && empty( $terms ) ){
            $post_id = get_the_ID();
            $terms = get_the_terms( $post_id, $taxonomySlug, array("hide_empty" => true) );
        }
        
		$category_list ='';
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$i = 1;
			$category_list .= '<span class="tpgb-meta-category-list">';
			foreach ( $terms as $term ) {
				if($cateDisplayNo >= $i){
					$category_list .= '<a href="' . esc_url( get_term_link( $term ) ) . '" alt="' . esc_attr( $term->name ) . '">' . esc_html($term->name) . '</a>';
				}
				$i++;
			}
			$category_list .= '</span>';
		}
		$outputCategory .='<span class="tpgb-meta-category '.esc_attr($cateStyle).'" >'.$catePrefix . $category_list.'</span>';
	}
	
	$outputAuthor='';
	if($showAuthor){
		global $post;
		$author_id = (!empty($post) && isset($post->post_author)) ? $post->post_author : '';
		$authorPrefix = (!empty($attr['authorPrefix'])) ? '<span class="tpgb-meta-author-label">'.wp_kses_post($attr['authorPrefix']).'</span>' : '';
		$authorIcon = (!empty($attr['authorIcon'])) ? $attr['authorIcon'] : '';
		$iconauthor = '';
		if(!empty($authorIcon) && $authorIcon=='profile'){
			$iconauthor = '<span>'.get_avatar( get_the_author_meta('ID'), 200).'</span>';
		}else if(!empty($authorIcon)){
			$iconauthor = '<i class="meta-author-icon '.esc_attr($authorIcon).'"></i>';
		}
		$outputAuthor .='<span class="tpgb-meta-author" >'.$authorPrefix.'<a href="'.esc_url(get_author_posts_url($author_id)).'" rel="'.esc_attr__('author','the-plus-addons-for-block-editor').'">'.$iconauthor.''.get_the_author_meta( 'display_name', $author_id ).'</a></span>';
	}
	
	$outputComment='';
	if($showComment){
		$commentIcon =(!empty($attr['commentIcon'])) ? '<i class="meta-comment-icon '.wp_kses_post($attr['commentIcon']).'"></i>' : '';
		$comments_count = wp_count_comments($post_id);
		$count=0;
		if(!empty($comments_count)){
			$count = $comments_count->total_comments;
		}
		if($count===0){
			$comment_text = esc_html__('No Comments','the-plus-addons-for-block-editor');
		}else if($count > 0){
			$comment_text = 'Comments('.$count.')';
		}
		$commentPrefix = (!empty($attr['commentPrefix'])) ? '<span class="tpgb-meta-comment-label">'.wp_kses_post($attr['commentPrefix']).'</span>' : '';
		$outputComment .='<span class="tpgb-meta-comment" >'.$commentPrefix.'<a href="'.esc_url(get_the_permalink()).'#respond" rel="'.esc_attr__('comment','the-plus-addons-for-block-editor').'">'.$commentIcon.$comment_text.'</a></span>';
	}
	
	$metaExtra = '';
	// Extra Field 
	if(!empty($metafieldRep)){
		foreach ($metafieldRep as $item ) {
			if(isset( $item['metaDynamic'] ) && !empty( $item['metaDynamic'] ) ){
				$metaExtra .= '<span class="tpgb-meta-extra" >';
					if(isset( $item['metaLabel'] ) && !empty( $item['metaLabel'] ) ){
						$metaExtra .= '<span class="tpgb-meta-extra-label">'.wp_kses_post($item['metaLabel']).'</span>';
					}
					
					$metaExtra .= '<span class="tpgb-meta-value">'.wp_kses_post( $item['metaDynamic'] ).'</span>';

					if(isset( $item['metapostfix'] ) && !empty( $item['metapostfix'] ) ){
						$metaExtra .= '<span class="tpgb-meta-epostfix">'.wp_kses_post($item['metapostfix']).'</span>';
					}
					$metaExtra .= '';
				$metaExtra .= '</span>';
			}
		}
	}

	$postRead = '';
	if($showreadTime){
		$content = get_the_content();
		$average_reading_rate = 189;
		$word_count_type = tpgb_get_word_count_type();
		$minutes_to_read = max( 1, (int) round( tpgb_word_count( $content, $word_count_type ) / $average_reading_rate ) );

		$minutes_to_read_string = sprintf(
            /* translators: %s: the number of minutes to read the post. */
			_n( '%s minute', '%s minutes', $minutes_to_read , 'the-plus-addons-for-block-editor' ),
			$minutes_to_read,
		);

		$postRead .= '<span class="tpgb-meta-read" >';
			if(!empty($readPrefix)){
				$postRead .= '<span class="tpgb-meta-read-label">';
					$postRead .= $readPrefix;
				$postRead .= '</span>';
			}
			$postRead .= $minutes_to_read_string;
		$postRead .= '</span>';
	}
	


    $output .= '<div class="tpgb-post-meta tpgb-block-'.esc_attr($block_id ).' '.esc_attr($blockClass).'" >';
		$output .= '<div class="tpgb-meta-info '.esc_attr($metaLayout).'">';
			foreach($metaSort['sort'] as $item => $value){
				if($value == 'Date') { $output .= $outputDate;  }
				if($value == 'Category') { $output .= $outputCategory;  }
				if($value == 'Author') { $output .= $outputAuthor;  }
				if($value == 'Comments') { $output .= $outputComment;  }
				if($value == 'Post Reading Time') { $output .= $postRead;  }
			}
		$output .= $metaExtra;
		$output .= '</div>';
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attr, $output);
	
    return $output;
	}

/**
 * Render for the server-side
 */
function tpgb_post_meta_content() {

    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
        $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_post_meta_render_callback');
        register_block_type( $block_data['name'], $block_data );
    }
}
add_action( 'init', 'tpgb_post_meta_content' );

if ( ! function_exists( 'tpgb_get_word_count_type' ) ) {
	function tpgb_get_word_count_type() {
		$word_count_type = _x( 'words', 'Word count type. Do not translate!', 'the-plus-addons-for-block-editor');

		if ( 'characters_excluding_spaces' !== $word_count_type && 'characters_including_spaces' !== $word_count_type ) {
			$word_count_type = 'words';
		}
		return $word_count_type;
	}
}

if ( ! function_exists( 'tpgb_word_count' ) ) {
	function tpgb_word_count( $text, $type, $settings = array() ) {
		$defaults = array(
			'html_regexp'                        => '/<\/?[a-z][^>]*?>/i',
			'html_comment_regexp'                => '/<!--[\s\S]*?-->/',
			'space_regexp'                       => '/&nbsp;|&#160;/i',
			'html_entity_regexp'                 => '/&\S+?;/',
			'connector_regexp'                   => "/--|\x{2014}/u",
			'remove_regexp'                      => "/[\x{0021}-\x{0040}\x{005B}-\x{0060}\x{007B}-\x{007E}\x{0080}-\x{00BF}\x{00D7}\x{00F7}\x{2000}-\x{2BFF}\x{2E00}-\x{2E7F}]/u",
			'astral_regexp'                      => "/[\x{010000}-\x{10FFFF}]/u",
			'words_regexp'                       => '/\S\s+/u',
			'characters_excluding_spaces_regexp' => '/\S/u',
			'characters_including_spaces_regexp' => "/[^\f\n\r\t\v\x{00AD}\x{2028}\x{2029}]/u",
			'shortcodes'                         => array(),
		);

		$count = 0;
		if ( ! $text ) {
			return $count;
		}

		$settings = wp_parse_args( $settings, $defaults );

		// If there are any shortcodes, add this as a shortcode regular expression.
		if ( is_array( $settings['shortcodes'] ) && ! empty( $settings['shortcodes'] ) ) {
			$settings['shortcodes_regexp'] = '/\\[\\/?(?:' . implode( '|', $settings['shortcodes'] ) . ')[^\\]]*?\\]/';
		}

		// Sanitize type to one of three possibilities: 'words', 'characters_excluding_spaces' or 'characters_including_spaces'.
		if ( 'characters_excluding_spaces' !== $type && 'characters_including_spaces' !== $type ) {
			$type = 'words';
		}

		$text .= "\n";

		// Replace all HTML with a new-line.
		$text = preg_replace( $settings['html_regexp'], "\n", $text );

		// Remove all HTML comments.
		$text = preg_replace( $settings['html_comment_regexp'], '', $text );

		// If a shortcode regular expression has been provided use it to remove shortcodes.
		if ( ! empty( $settings['shortcodes_regexp'] ) ) {
			$text = preg_replace( $settings['shortcodes_regexp'], "\n", $text );
		}

		// Normalize non-breaking space to a normal space.
		$text = preg_replace( $settings['space_regexp'], ' ', $text );

		if ( 'words' === $type ) {
			// Remove HTML Entities.
			$text = preg_replace( $settings['html_entity_regexp'], '', $text );

			// Convert connectors to spaces to count attached text as words.
			$text = preg_replace( $settings['connector_regexp'], ' ', $text );

			// Remove unwanted characters.
			$text = preg_replace( $settings['remove_regexp'], '', $text );
		} else {
			// Convert HTML Entities to "a".
			$text = preg_replace( $settings['html_entity_regexp'], 'a', $text );

			// Remove surrogate points.
			$text = preg_replace( $settings['astral_regexp'], 'a', $text );
		}

		// Match with the selected type regular expression to count the items.
		preg_match_all( $settings[ $type . '_regexp' ], $text, $matches );

		if ( $matches ) {
			return count( $matches[0] );
		}

		return $count;
	}
}