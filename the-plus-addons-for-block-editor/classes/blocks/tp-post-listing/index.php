<?php
/* Tp Block : Post Listing
 * @since	: 1.1.3
 */
defined( 'ABSPATH' ) || exit;

function tpgbp_metro_class($col = '1', $metroCol = '3', $metrosty = 'style-1') {
    return (!empty($metroCol) && $metroCol == '3' && $metrosty == 'style-1' && $col > 10) ? ($col % 10) : $col;
}

function tpgb_tp_post_listing_render_callback( $attributes ) {
	$output = '';
	$query_args = tpgb_post_query($attributes);
	$query = new \WP_Query( $query_args );

	$block_id	= isset($attributes['block_id']) ? $attributes['block_id'] : '';
	$postType = isset($attributes['postType']) ? $attributes['postType'] : '';
	$style		= isset($attributes['style']) ? $attributes['style'] : 'style-1';
	$layout		= isset($attributes['layout']) ? $attributes['layout'] : 'grid';
	$style2Alignment	= isset($attributes['style2Alignment']) ? $attributes['style2Alignment'] : 'center';
	$styleLayout		= isset($attributes['styleLayout']) ? $attributes['styleLayout'] : 'style-1';
	
	$imageHoverStyle	= isset($attributes['imageHoverStyle']) ? 'hover-image-'.esc_attr($attributes['imageHoverStyle']) : 'hover-image-style-1';
	//Title
	$ShowTitle	= !empty($attributes['ShowTitle']) ? 'yes' : '';
	$titleTag	= isset($attributes['titleTag']) ? $attributes['titleTag'] : 'h3';
	$titleByLimit = isset($attributes['titleByLimit']) ? $attributes['titleByLimit'] : 'default';
	
	//Excerpt
	$showExcerpt	= !empty($attributes['ShowExcerpt']) ? 'yes' : '';
	$excerptByLimit	= isset($attributes['excerptByLimit']) ? $attributes['excerptByLimit'] : 'default';
	$excerptLimit	= isset($attributes['excerptLimit']) ? $attributes['excerptLimit'] : 30;
	
	$showPostMeta	= !empty($attributes['ShowPostMeta']) ? 'yes' : '';
	$postMetaStyle	= isset($attributes['postMetaStyle']) ? $attributes['postMetaStyle'] : 'style-1';
	$ShowDate = !empty($attributes['ShowDate']) ? 'yes' : '';
	$ShowAuthor = !empty($attributes['ShowAuthor']) ? 'yes' : '';
	$ShowAuthorImg = !empty($attributes['ShowAuthorImg']) ? 'yes' : '';
	$taxonomySlug	= !empty($attributes['taxonomySlug']) ? $attributes['taxonomySlug'] : 'category';

	$postListing = isset($attributes['postListing']) ? $attributes['postListing'] : '';

	$showPostCategory	= !empty($attributes['showPostCategory']) ? 'yes' : '';
	$postCategoryStyle	= isset($attributes['postCategoryStyle']) ? $attributes['postCategoryStyle'] : 'style-1';
	$postCategory =  isset($attributes['postCategory']) ? $attributes['postCategory'] : '';
	$postTag =  isset($attributes['postTag']) ? $attributes['postTag'] : '';
	$excludeCategory =  isset($attributes['excludeCategory']) ? $attributes['excludeCategory'] : '';
	$excludeTag =  isset($attributes['excludeTag']) ? $attributes['excludeTag'] : '';
	
	$displayPosts		= isset($attributes['displayPosts']) ? $attributes['displayPosts'] : 6;
	$offsetPosts		= isset($attributes['offsetPosts']) ? $attributes['offsetPosts'] : 0;
	$orderBy		= isset($attributes['orderBy']) ? $attributes['orderBy'] : 'date';
	$order		= isset($attributes['order']) ? $attributes['order'] : 'desc';
	$postLodop = isset($attributes['postLodop']) ? $attributes['postLodop'] : '';
	$authorTxt = !empty($attributes['authorTxt']) ? $attributes['authorTxt'] : '';
	$metrocolumns = isset($attributes['metrocolumns']) ? $attributes['metrocolumns'] : [ 'md' => '3' ] ;
	$metroStyle = isset($attributes['metroStyle']) ? $attributes['metroStyle'] : '';

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
    $ShowButton = !empty($attributes['ShowButton']) ? 'yes' : '';
    $postBtnsty = isset($attributes['postBtnsty']) ? $attributes['postBtnsty'] : 'style-7';
    $postbtntext = isset($attributes['postbtntext']) ? $attributes['postbtntext'] : '';
    $pobtnIconType = isset($attributes['pobtnIconType']) ? $attributes['pobtnIconType'] : '';
    $pobtnIconName = isset($attributes['pobtnIconName']) ? $attributes['pobtnIconName'] : '';
    $btnIconPosi = isset($attributes['btnIconPosi']) ? $attributes['btnIconPosi'] : '';
    
	//Columns
	$column_class = '';
	if($layout!='carousel' && !empty($attributes['columns']) && is_array($attributes['columns'])){
		$column_class .= isset($attributes['columns']['md']) ? " tpgb-col-lg-".$attributes['columns']['md'] : ' tpgb-col-lg-3';
		$column_class .= isset($attributes['columns']['sm']) ? " tpgb-col-md-".$attributes['columns']['sm'] : ' tpgb-col-md-4';
		$column_class .= isset($attributes['columns']['xs']) ? " tpgb-col-sm-".$attributes['columns']['xs'] : ' tpgb-col-sm-6';
		$column_class .= isset($attributes['columns']['xs']) ? " tpgb-col-".$attributes['columns']['xs'] : ' tpgb-col-6';
	}
	
	//Classes
	$list_style = ($style) ? 'dynamic-'.esc_attr($style) : 'dynamic-style-1';
	
	$list_layout = '';
	if($layout=='grid' || $layout=='masonry'){
		$list_layout = 'tpgb-isotope';
	}else if($layout=='metro'){
        $list_layout = 'tpgb-metro';
    }else{
		$list_layout = 'tpgb-isotope';
	}
	
	$styleLayoutclass ='';
	if(($style=='style-2') && $styleLayout){
		$styleLayoutclass .= 'layout-'.$styleLayout;
	}

	$classattr = '';
	$classattr .= ' tpgb-block-'.$block_id;
	$classattr .= ' '.$list_style;
	$classattr .= ' '.$list_layout;
	$classattr .= ' '.$styleLayoutclass;

	//Equal Height
	$equalHeightAttr = Tp_Blocks_Helper::global_equal_height( $attributes );

	if(!empty($equalHeightAttr)){
		$classattr .= ' tpgb-equal-height';
	}

	if ($layout == 'metro') {
		// Desktop columns
		if (isset($metrocolumns['md']) && !empty($metrocolumns['md'])) {
			$metroAttr['metro_col'] = (int)$metrocolumns['md'];
		}
		
		// Tablet columns
		if (isset($metrocolumns['sm']) && !empty($metrocolumns['sm'])) {
			$metroAttr['tab_metro_col'] = (int)$metrocolumns['sm'];
		} else if (isset($metrocolumns['md']) && !empty($metrocolumns['md'])) {
			$metroAttr['tab_metro_col'] = (int)$metrocolumns['md'];
		}
		
		// Mobile columns
		if (isset($metrocolumns['xs']) && !empty($metrocolumns['xs'])) {
			$metroAttr['mobile_metro_col'] = (int)$metrocolumns['xs'];
		} else if (isset($metrocolumns['sm']) && !empty($metrocolumns['sm'])) {
			$metroAttr['mobile_metro_col'] = (int)$metrocolumns['sm'];
		} else if (isset($metrocolumns['md']) && !empty($metrocolumns['md'])) {
			$metroAttr['mobile_metro_col'] = (int)$metrocolumns['md'];
		}
		
		// Desktop style
		if (isset($metroStyle['md']) && !empty($metroStyle['md'])) {
			$metroAttr['metro_style'] = (string)$metroStyle['md'];
		}
		
		// Tablet style
		if (isset($metroStyle['sm']) && !empty($metroStyle['sm'])) {
			$metroAttr['tab_metro_style'] = (string)$metroStyle['sm'];
		} else if (isset($metroStyle['md']) && !empty($metroStyle['md'])) {
			$metroAttr['tab_metro_style'] = (string)$metroStyle['md'];
		}
		
		// Mobile style
		if (isset($metroStyle['xs']) && !empty($metroStyle['xs'])) {
			$metroAttr['mobile_metro_style'] = (string)$metroStyle['xs'];
		} else if (isset($metroStyle['sm']) && !empty($metroStyle['sm'])) {
			$metroAttr['mobile_metro_style'] = (string)$metroStyle['sm'];
		} else if (isset($metroStyle['md']) && !empty($metroStyle['md'])) {
			$metroAttr['mobile_metro_style'] = (string)$metroStyle['md'];
		}
		
		// Properly encode the JSON and create the data attribute
		$metroAttrJson = htmlspecialchars(json_encode($metroAttr), ENT_QUOTES, 'UTF-8');
		$metroDataAttr = 'data-metroAttr="' . $metroAttrJson . '"';
	} else {
	    $metroDataAttr = '';
	}

	if($query->found_posts !=''){
		$total_posts=$query->found_posts;
		$post_offset = (isset($offsetPosts)) ? $offsetPosts : 0;
		$display_posts = (isset($displayPosts)) ? $displayPosts : 0;
		$offset_posts= intval((int)$display_posts + (int)$post_offset);
		$total_posts= intval($total_posts - $offset_posts);	
		
		$load_page=1;
		
		$load_page=$load_page+1;
	}else{
		$load_page=1;
	}
	$ji=1;$col=$tabCol=$moCol='';
	if ( ! $query->have_posts() ) {
		$output .='<h3 class="tpgb-no-posts-found">'.esc_html__( "No Posts found", 'the-plus-addons-for-block-editor' ).'</h3>';
	}else{
		$output .= '<div id="'.esc_attr($block_id).'" class="tpgb-post-listing tpgb-relative-block  '.esc_attr($blockClass).' '.esc_attr($classattr).' " data-id="'.esc_attr($block_id).'" data-style="'.esc_attr($list_style).'" '.( $layout == 'metro' ? $metroDataAttr : '' ).'  data-layout="'.esc_attr($layout).'"  data-connection="tpgb_search"  '.$equalHeightAttr.' >';
			
			$output .= '<div class="tpgb-row post-loop-inner" >';
				while ( $query->have_posts() ) {
					
					$query->the_post();
					$post = $query->post;
					
					if( $layout == 'metro' ){
						if( ( isset($metrocolumns['md']) && !empty($metrocolumns['md']) ) && ( isset($metroStyle['md']) && !empty($metroStyle['md']) ) ){
							$col= tpgbp_metro_class($ji , $metrocolumns['md'] , $metroStyle['md']  );
						}
						if( ( isset($metrocolumns['sm']) && !empty($metrocolumns['sm']) ) && ( isset($metroStyle['sm']) && !empty($metroStyle['sm']) ) ){
							$tabCol = tpgbp_metro_class($ji, $metrocolumns['sm'] , $metroStyle['sm']  );
						}
						if( ( isset($metrocolumns['xs']) && !empty($metrocolumns['xs']) ) && ( isset($metroStyle['xs']) && !empty($metroStyle['xs']) ) ){
							$moCol = tpgbp_metro_class($ji , $metrocolumns['xs'] , $metroStyle['xs'] );
						}
					}

					$output .= '<div class="grid-item tpgb-col ' . esc_attr($column_class) . ( $layout=='metro' ? ' tpgb-metro-'.esc_attr($col).' '.( !empty($tabCol) ? ' tpgb-tab-metro-'.esc_attr($tabCol).''  : '' ).' '.( !empty($moCol) ? ' tpgb-mobile-metro-'.esc_attr($moCol).''  : '' ).' ' : '' ) . '">';
					if(!empty($style) && $style!=='custom'){
						ob_start();
						if(file_exists(TPGB_PATH. 'includes/blog/'.sanitize_file_name('blog-'.$style.'.php'))){
							include TPGB_PATH. 'includes/blog/'.sanitize_file_name('blog-'.$style.'.php');
						}
						$output .= ob_get_contents();
						ob_end_clean();
					}else if($style=='custom' && $attributes['blockTemplate']!=''){
						ob_start();
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped inside plus_do_block().
							echo Tpgb_Library()->plus_do_block($attributes['blockTemplate']);
						$output .= ob_get_contents();
						ob_end_clean();
					}
					$output .= '</div>';
					$ji++;
				}
			$output .= '</div>';

			if($postLodop=='pagination' && $layout!='carousel'){
				$output .= tpgb_pagination($query->max_num_pages,'2');
			}
		$output .= "</div>";
	}

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	wp_reset_postdata();
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_post_listing() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_post_listing_render_callback' , true, false, true, true);
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_post_listing' );

function tpgb_post_query($attr){
	
	$include_posts = ($attr['includePosts']) ? explode(',', $attr['includePosts']) : '';
	$exclude_posts = ($attr['excludePosts']) ? explode(',', $attr['excludePosts']) : '';
	
	$query_args = array(
		'post_type'           => $attr['postType'],
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'posts_per_page'      => ( $attr['displayPosts'] ) ? intval($attr['displayPosts']) : -1,
		'orderby'      =>  ($attr['orderBy']) ? $attr['orderBy'] : 'date',
		'order'      => ($attr['order']) ? $attr['order'] : 'desc',
		'post__not_in'  => $exclude_posts,
		'post__in'   => $include_posts,
	);

	global $paged;
	if ( get_query_var('paged') ) {
		$paged = get_query_var('paged');
	}elseif ( get_query_var('page') ) {
		$paged = get_query_var('page');
	}else {
		$paged = 1;
	}
	$query_args['paged'] = $paged;
	
	
	$offset = !empty( $attr['offsetPosts'] ) ? absint( $attr['offsetPosts'] ) : 0;
	if ( $offset  && $attr['postLodop']!='pagination') {
		$query_args['offset'] = $offset;
	}else if($offset && $attr['postLodop']=='pagination'){
		$page = max( 1, $paged );
		$offset = ( $page - 1 ) * intval( $attr['displayPosts'] ) + $offset;
		$query_args['offset'] = $offset;
	}
	
	if ( '' !== $attr['postCategory'] ) {
		$cat_arr = array();
		if ( is_string($attr['postCategory'] )) {
			$attr['postCategory'] = json_decode($attr['postCategory']);
			if (is_array($attr['postCategory']) || is_object($attr['postCategory'])) {
				foreach ($attr['postCategory'] as $value) {
					$cat_arr[] = $value->value;
				}
			}
		}
		if($attr['postType'] == 'post'){
			$query_args['category__in'] = $cat_arr;
		}else if(!empty($attr['taxonomySlug']) && !empty($cat_arr)){
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => $attr['taxonomySlug'],
					'field' => 'term_id',
					'terms' => $cat_arr,
				)
			);
		}
	}
	if ( '' !== $attr['postTag'] ) {
		$tag_arr = array();
		if ( is_string($attr['postTag'] )) {
			$attr['postTag'] = json_decode($attr['postTag']);
			if (is_array($attr['postTag']) || is_object($attr['postTag'])) {
				foreach ($attr['postTag'] as $value) {
					$tag_arr[] = $value->value;
				}
			}
		}
		if($attr['postType'] == 'post'){
			$query_args['tag__in'] = $tag_arr;
		}
	}


	//Archive Posts
	if(!empty($attr["postListing"]) && $attr["postListing"]=='archive_listing'){
		global $wp_query;
		$query_var = $wp_query->query_vars;
		if(isset($query_var['cat'])){
			$query_args['category__in'] = $query_var['cat'];
		}
		if(isset($query_var[$attr["taxonomySlug"]]) && $attr['postType']!=='post'){		
					
			$query_args['tax_query'] = array(						
			  array(		
				'taxonomy' => $attr["taxonomySlug"],		
				'field' => 'slug',		
				'terms' => $query_var[$attr["taxonomySlug"]],		
			  ),		
			);		
		}else if( $attr['postType'] == 'post'  ){
			if( isset( $query_var['taxonomy'] ) && !empty($query_var['taxonomy']) ){
				$query_args['tax_query'] = array(						 
					array(		
					'taxonomy' =>  $query_var['taxonomy'] ,		
					'field' => 'slug',		
					'terms' => $query_var[ $query_var['taxonomy'] ],		
					),		
				);	
			}
		}
		
		if(isset($query_var['tag_id'])){
			$query_args['tag__in'] = $query_var['tag_id'];
		}
		if(isset($query_var["author"])){
			$query_args['author'] = $query_var["author"];
		}
		if(is_search()){
			$search = get_query_var('s');
			$query_args['s'] = $search;
			$query_args['exact'] = false;
		}
	}

	//Related Posts
	if(!empty($attr["postListing"]) && $attr["postListing"]=='related_post'){
		global $post;
		
		if(isset($post->post_type) && $post->post_type =='post'){
			$tag_slug = 'term_id';
			$tags = wp_get_post_tags($post->ID);
		}else{
			$tag_slug = 'slug';
			$tags = isset($post->ID) ? wp_get_post_terms($post->ID,$attr['taxonomySlug']) : [];
		}
		if ($tags && !empty($attr["postListing"]) && ($attr["relatedPost"]=='both' || $attr["relatedPost"]=='tags')) {	
			$tag_ids = array();
			
			foreach($tags as $individual_tag) $tag_ids[] = $individual_tag->$tag_slug;
			
			$query_args['post__not_in'] = array($post->ID);
			if(isset($post->post_type) && $post->post_type =='post'){
				$query_args['tag__in'] = $tag_ids;
			}else{
				$query_args['tax_query'] = array(						
				  array(		
					'taxonomy' => $attr['taxonomySlug'],		
					'field' => 'slug',		
					'terms' => $tag_ids,		
				  ),		
				);
			}
		}
		if(isset($post->post_type) && $post->post_type =='post'){
			$categories_slug = 'cat_ID';
			$categories = get_the_category($post->ID);
		}else{
			$categories_slug = 'slug';
			$categories = isset($post->ID) ? wp_get_post_terms($post->ID,$attr['taxonomySlug']) : [];
		}

		if ($categories && !empty($attr["relatedPost"]) && ($attr["relatedPost"]=='both' || $attr["relatedPost"]=='category')) {	
			$category_ids = array();
			foreach($categories as $category) $category_ids[] = $category->$categories_slug;
			
			$query_args['post__not_in'] = array($post->ID);

			if(isset($post->post_type) && $post->post_type =='post'){
				$query_args['category__in'] = $category_ids;
			}else{
				$query_args['tax_query'] = array(						
				  array(		
					'taxonomy' => $attr['taxonomySlug'],		
					'field' => 'slug',		
					'terms' => $category_ids,
				  ),		
				);
			}
		}
	}

	return $query_args;
}

function tpgb_pagination($pages = '', $range = 2){  
	$showitems = ($range * 2)+1;  
	
	global $paged;
	if(empty($paged)) $paged = 1;
	
	if($pages == ''){
		global $wp_query;
		if( $wp_query->max_num_pages <= 1 )
		return;
		
		$pages = $wp_query->max_num_pages;
		/*if(!$pages)
		{
			$pages = 1;
		}*/
		$pages = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
	}
	
	if(1 != $pages){
		$paginate ="<div class=\"tpgb-pagination\">";
		if ( get_previous_posts_link() ){
			$paginate .= '<div class="paginate-prev">'.get_previous_posts_link('<i class="fa fa-long-arrow-left" aria-hidden="true"></i> PREV').'</div>';
		}
		
		for ($i=1; $i <= $pages; $i++){
			if (1 != $pages && ( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
			{
				$paginate .= ($paged == $i)? "<span class=\"current\">".esc_html($i)."</span>":"<a href='".get_pagenum_link($i)."' class=\"inactive\">".esc_html($i)."</a>";
			}
		}
		if ( get_next_posts_link() ){
			$paginate .='<div class="paginate-next">'.get_next_posts_link('NEXT <i class="fa fa-long-arrow-right" aria-hidden="true"></i>',1).'</div>';
		}
		
		$paginate .="</div>\n";
		return $paginate;
	}
}