<?php
/* Block : Testimonials
 * @since : 1.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_testimonials_render_callback( $attributes, $content) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
	$styleLayout = (!empty($attributes['styleLayout'])) ? $attributes['styleLayout'] : 'style-1';
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	$showDots = (!empty($attributes['showDots'])) ? $attributes['showDots'] : [ 'md' => false ];
	$dotsStyle = (!empty($attributes['dotsStyle'])) ? $attributes['dotsStyle'] : false;
	$showArrows = (!empty($attributes['showArrows'])) ? $attributes['showArrows'] : [ 'md' => false ];
	$arrowsStyle = (!empty($attributes['arrowsStyle'])) ? $attributes['arrowsStyle'] : 'style-1';
	$arrowsPosition = (!empty($attributes['arrowsPosition'])) ? $attributes['arrowsPosition'] : 'top-right';
	$ItemRepeater = (!empty($attributes['ItemRepeater'])) ? $attributes['ItemRepeater'] : [];
	
	$telayout = (!empty($attributes['telayout'])) ? $attributes['telayout'] : '';

	$descByLimit	= isset($attributes['descByLimit']) ? $attributes['descByLimit'] : 'default';
	$descLimit = isset($attributes['descLimit']) ? $attributes['descLimit'] : 30;
	$cntscrollOn = (!empty($attributes['cntscrollOn'])) ? $attributes['cntscrollOn'] : '';
	$caroByheight = (!empty($attributes['caroByheight'])) ? $attributes['caroByheight'] : '';

	$redmorTxt = (!empty($attributes['redmorTxt'])) ? $attributes['redmorTxt'] : '';
	$redlesTxt = (!empty($attributes['redlesTxt'])) ? $attributes['redlesTxt'] : '';

	$Style3Layout ='';
	if($style=='style-3' && !empty($styleLayout)){
		$Style3Layout ='layout-'.$styleLayout;
	} 

    //Equal Height
    $equalHeightAttr = Tp_Blocks_Helper::global_equal_height( $attributes );
    if(!empty($equalHeightAttr)){
            $blockClass .= ' tpgb-equal-height';
    }

	//Carousel Options
	
	$dataAttr = '';
	$Sliderclass ='';
	if($telayout == 'carousel'){
		if( ( isset($showDots['md']) && !empty($showDots['md']) ) || ( isset($showDots['sm']) && !empty($showDots['sm']) ) || ( isset($showDots['xs']) && !empty($showDots['xs']) ) ){
			$Sliderclass .= ' dots-'.esc_attr($dotsStyle);
		}

		$carousel_settings = Tp_Blocks_Helper::carousel_settings($attributes);

        if( isset($carousel_settings['pauseOnHover']) && $carousel_settings['pauseOnHover'] === true ){
            $carousel_settings['pauseOnHover'] = false;
        }

        if( isset($carousel_settings['sliderMode']) && $carousel_settings['sliderMode'] === 'vertical' ){
            $carousel_settings['sliderMode'] = 'horizontal';
        }

		$dataAttr = 'data-splide=\'' . json_encode($carousel_settings) . '\'';
	}

	
	$readAttr = [];
	$attr = '';
	if($telayout == 'masonry' || ( $telayout == 'carousel' && $caroByheight == 'text-limit' )){
		
		$readAttr['readMore'] = $redmorTxt;
		$readAttr['readLess'] = $redlesTxt;
		
		$readAttr = htmlspecialchars(json_encode($readAttr), ENT_QUOTES, 'UTF-8');

		$attr = 'data-readData = \'' .$readAttr. '\'';
	}

	$list_layout = '';
	if($telayout=='grid' || $telayout=='masonry'){
		$list_layout = 'tpgb-isotope';
	}else if($telayout=='carousel'){
		$list_layout = 'tpgb-carousel splide';
	}

	$column_class = ' tpgb-col';
	if( $telayout!='carousel' && !empty($attributes['columns']) && is_array($attributes['columns'])){
		$column_class .= isset($attributes['columns']['md']) ? " tpgb-col-lg-".$attributes['columns']['md'] : ' tpgb-col-lg-3';
		$column_class .= isset($attributes['columns']['sm']) ? " tpgb-col-md-".$attributes['columns']['sm'] : ' tpgb-col-md-4';
		$column_class .= isset($attributes['columns']['xs']) ? " tpgb-col-sm-".$attributes['columns']['xs'] : ' tpgb-col-sm-6';
		$column_class .= isset($attributes['columns']['xs']) ? " tpgb-col-".$attributes['columns']['xs'] : ' tpgb-col-6';
	}

    $output .= '<div class="tpgb-testimonials tpgb-relative-block testimonial-'.esc_attr($style).' '.esc_attr($Style3Layout).' tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' '.esc_attr($Sliderclass).' '.esc_attr($list_layout).' " '.$dataAttr.' data-layout="'.esc_attr($telayout).'" data-id="'.esc_attr($block_id).'" '.$equalHeightAttr.' >';

		if( $telayout == 'carousel' && ( isset($showArrows['md']) && !empty($showArrows['md']) ) || ( isset($showArrows['sm']) && !empty($showArrows['sm']) ) || ( isset($showArrows['xs']) && !empty($showArrows['xs']) ) ){
			$output .= Tp_Blocks_Helper::tpgb_carousel_arrow($arrowsStyle,$arrowsPosition);
		}
		$output .= '<div class="post-loop-inner '.($telayout == 'carousel' ? 'splide__track' : 'tpgb-row').'">';
			if($telayout == 'carousel'){
				$output .= '<div class="splide__list">';
			}
				if( !empty( $ItemRepeater ) ){
					foreach ( $ItemRepeater as $index => $item ) :
						if(is_array($item)){
						
							$itemContent = '';
							if( !empty($item['content']) ){
								if($descByLimit == 'default' || ($telayout == 'carousel' && ($caroByheight == '' || $caroByheight == 'height' )) ){
									$itemContent .= '<div class="entry-content scroll-'.esc_attr($cntscrollOn).'">';
										$itemContent .= wp_kses_post($item['content']);
									$itemContent .= '</div>';
								}else{
									if( $descByLimit === 'words' ){
										$total = explode(' ', $item['content']);
										$limit_words = explode(' ', $item['content'], $descLimit);
										$ltn = count($limit_words);
										$remaining_words = implode(" " , array_slice($total, $descLimit-1));
										if (count($limit_words)>=$descLimit) {
											array_pop($limit_words);
											$excerpt = implode(" ",$limit_words).' <span class="testi-more-text" style = "display: none" >'.wp_kses_post($remaining_words).'</span><a '.$attr.' class="testi-readbtn"> '.esc_attr($redmorTxt).' </a>';
										} else {
											$excerpt = implode(" ",$limit_words);
										}
										
									}else if( $descByLimit === 'letters' ){
										$ltn = strlen($item['content']);
										$limit_words = substr($item['content'],0,$descLimit); 
										$remaining_words = substr($item['content'], $descLimit, $ltn);
										if(strlen($item['content'])>$descLimit){
											$excerpt = $limit_words.'<span class="testi-more-text" style = "display:none" >'.wp_kses_post($remaining_words).'</span><a '.$attr.' class="testi-readbtn"> '.esc_attr($redmorTxt).' </a>';
										}else{
											$excerpt = $limit_words;
										}
									}

									$itemContent .= '<div class="entry-content">';
										$itemContent .= $excerpt;
									$itemContent .= '</div>';
								}
							}
							
							$itemAuthorTitle = '';
							if( !empty($item['authorTitle']) ){
								$itemAuthorTitle .= '<h3 class="testi-author-title title-scroll-'.esc_attr($cntscrollOn).'">'.esc_html($item['authorTitle']).'</h3>';
							}
							
							$itemTitle ='';
							if(!empty($item['testiTitle'])){
								$itemTitle .= '<div class="testi-post-title">'.esc_html($item['testiTitle']).'</div>';
							}
							
							$itemDesignation ='';
							if(!empty($item['designation'])){
								$itemDesignation .= '<div class="testi-post-designation">'.esc_html($item['designation']).'</div>';
							}
							
							$imgUrl ='';
							$altText = (isset($item['avatar']['alt']) && !empty($item['avatar']['alt'])) ? esc_attr($item['avatar']['alt']) : ((!empty($item['avatar']['title'])) ? esc_attr($item['avatar']['title']) : esc_attr__('Author Avatar','the-plus-addons-for-block-editor'));

							if(!empty($item['avatar']) && !empty($item['avatar']['id'])){
								$imgUrl = wp_get_attachment_image($item['avatar']['id'],'medium', false, ['alt'=> $altText]);
							}else if(!empty($item['avatar']) && !empty($item['avatar']['url'])){
								$imgUrl = '<img src="'.esc_url($item['avatar']['url']).'" alt="'.$altText.'"/>';
							}else{
								$imgUrl ='<img src="'.esc_url(TPGB_URL.'assets/images/tpgb-placeholder-grid.jpg').'" alt="'.esc_html__('Author Avatar','the-plus-addons-for-block-editor').'"/>';
							}
							
							$output .= '<div class="grid-item '.($telayout=='carousel' ? 'splide__slide' : $column_class).' tp-repeater-item-'. ( isset($item['_key']) ? esc_attr($item['_key']) : '' ) .'" >';
								$output .= '<div class="testimonial-list-content" >';
									
									if($style!='style-4'){
										$output .= '<div class="testimonial-content-text">';
											if($style=="style-1" || $style=="style-2"){
												$output .= $itemContent;
												$output .= $itemAuthorTitle;
											}
											if($style=="style-3"){
                                                $output .= $itemAuthorTitle;
                                                $output .= $itemContent;
                                            }
										$output .= '</div>';
									}
									
									$output .= '<div class="post-content-image">';
										$output .= '<div class="author-thumb">';
											$output .= $imgUrl;
										$output .= '</div>';
										if($style=="style-1" || $style=="style-2"){
											$output .= $itemTitle;
											$output .= $itemDesignation;
										}
										if($style=="style-3"){
											$output .= '<div class="author-left-text">';
												$output .= $itemTitle;
												$output .= $itemDesignation;
											$output .= '</div>';
										}
									$output .= '</div>';
									
									
								$output .= "</div>";
							$output .= "</div>";
						}
					endforeach;
				}
			if($telayout == 'carousel'){
				$output .= '</div>';
			}
		$output .= "</div>";
    $output .= "</div>";
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
	$arrowCss = Tp_Blocks_Helper::tpgb_carousel_arrow_css( $showArrows , $block_id );
	if( !empty($arrowCss) ){
		$output .= $arrowCss;
	}
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_testimonials() {
    $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_testimonials_render_callback', true, true, false, true);
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_testimonials' );