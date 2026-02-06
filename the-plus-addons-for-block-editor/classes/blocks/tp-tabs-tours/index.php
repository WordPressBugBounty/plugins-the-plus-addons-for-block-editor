<?php
/* Block : Tabs And Tours
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_tabs_tours_render_callback( $attributes, $content) {
	
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] :'';
	$pattern = '/\btpgb-block-'.esc_attr($block_id).'/';
    
    if (preg_match($pattern, $content)) {
		if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attributes,$content);
        }
       return $content;
    }
	$tabLayout =  (!empty($attributes['tabLayout'])) ? $attributes['tabLayout'] :'horizontal';
	$navAlign =  (!empty($attributes['navAlign'])) ? $attributes['navAlign'] :'text-center';
	$fullwidthIcon = (!empty($attributes['fullwidthIcon'])) ? $attributes['fullwidthIcon'] :false;
	$navWidth =  (!empty($attributes['navWidth'])) ? $attributes['navWidth'] :false;
	$underline = (!empty($attributes['underline'])) ? $attributes['underline'] :false;
	$tablistRepeater = (!empty($attributes['tablistRepeater'])) ? $attributes['tablistRepeater'] : [];
	$titleShow =  (!empty($attributes['titleShow'])) ? $attributes['titleShow'] : false;
	$navPosition = (!empty($attributes['navPosition'])) ? $attributes['navPosition'] :'top' ;
	$VerticalAlign = (!empty($attributes['VerticalAlign'])) ? $attributes['VerticalAlign'] :'';
	$tabType = (!empty($attributes['tabType'])) ? $attributes['tabType'] :'';
	$tabnavResp =  (!empty($attributes['tabnavResp'])) ? $attributes['tabnavResp'] :'';
	$activeTab = (!empty($attributes['activeTab'])) ? $attributes['activeTab'] :'1';
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	$output = '';
	$tab_nav = '';
	$tab_content = '';


	// Set Full Width Icon Class
	$full_icon_class = '';
	if($fullwidthIcon == true){
		$full_icon_class = 'full-width-icon';
	}else{
		$full_icon_class = 'normal-width-icon';
	}


	//Set class For full width Nav bar
	$full_width_nav = '';
	if($navWidth == true){
		$full_width_nav = 'full-width';
	}

	// set class For UnderLine
	$underline_class = '';
	if($underline == true){
		$underline_class = 'tab-underline';
	}

	//Set responsive class
	$responsive_class = '';
	if($tabnavResp == 'nav_full'){
		$responsive_class = 'nav-full-width';
	}else if($tabnavResp == 'nav_one'){
		$responsive_class = "nav-one-by-one";
	}else if($tabnavResp == 'tab_accordion'){
		$responsive_class = 'mobile-accordion';
	}


	//Set Vertival TabAlign class
	$alignclass = '';
	if($VerticalAlign == 'top'){
		$alignclass = 'align-top';
	}else if($VerticalAlign == 'center'){
		$alignclass = "align-center";
	}else if($VerticalAlign == 'bottom'){
		$alignclass = "align-bottom";
	}
	$i=0;$j=0;

	// Output for Tab Navigation
	$nav_loop='';
	if(!empty($tablistRepeater)){ 
		foreach ( $tablistRepeater as $index => $item ) :
			$j++;
			// Set active class
			$active='';
			if($j==$activeTab){
				$active=' active';
			}

			$nav_loop .= '<div class="tpgb-tab-li">';
				$nav_loop .= '<div id="'.(!empty($item['uniqueId']) ? esc_attr($item['uniqueId']) : 'tpag-tab-title-'.esc_attr($block_id).esc_attr($j) ).'" class="tpgb-tab-header tpgb-trans-linear '.esc_attr($active).'" data-tab="'.esc_attr($j).'" role="tab" aria-controls="'.(!empty($item['uniqueId']) ? esc_attr($item['uniqueId']) : 'tpag-tab-title-'.esc_attr($block_id).esc_attr($j) ).'">';
					if(!empty($item['innerIcon'])){
						$nav_loop .= '<span class="tab-icon-wrap">';
							if($item['iconFonts'] == 'font_awesome') {
								$nav_loop .= '<i class="tab-icon tpgb-trans-linear '.esc_attr($item['innericonName']).'"> </i>';
							}else if($item['iconFonts'] == 'image'){
								if( !empty($item['iconImage']['id']) ){
									$nav_loop .= wp_get_attachment_image($item['iconImage']['id'],$item['iconimageSize']);
								}
							}
						$nav_loop .= '</span>';
					}
					if(!empty($titleShow)){
						$nav_loop .= '<span>' .wp_kses_post($item['tabTitle']). '</span>';
					}
					
				$nav_loop .= '</div>';
			$nav_loop .= '</div>';
			
		endforeach;
	}
	$tab_nav .= '<div class="tpgb-tabs-nav-wrapper '.esc_attr($navAlign).' '.($tabLayout=='vertical' ? esc_attr($alignclass) : '').' ">';
		$tab_nav .= '<div class="tpgb-tabs-nav tpgb-trans-linear '.esc_attr($full_icon_class).'  '.esc_attr($full_width_nav).' '.esc_attr($underline_class).' " role="tablist">';
			$tab_nav .= $nav_loop;
		$tab_nav .= '</div>';
	$tab_nav .= '</div>';
	
	//Output tab content
	$content_loop = '';
	if(!empty($tablistRepeater)){ 
		if($tabType == 'editor' ){
			$content_loop .= $content;
		}else{
			foreach ( $tablistRepeater as $index => $item ) :
				$i++;
			
				// Set active class
				$active='';
				if($i==$activeTab){
					$active=' active';
				}

				// Set Tab Title For responsive accordian
				$content_loop .= '<div class="tab-mobile-title '.esc_attr($active).' '.esc_attr($navAlign).'" data-tab="'.esc_attr($i).'">';
					if(!empty($item['innerIcon'])){
						$content_loop .= '<span class="tab-icon-wrap">';
							if($item['iconFonts'] == 'font_awesome') {
									$content_loop .= '<i class="tab-icon tpgb-trans-linear '.esc_attr($item['innericonName']).'"> </i>';
							}else if($item['iconFonts'] == 'image'){
								if(!empty($item['iconImage']['id'])){
									$content_loop .= wp_get_attachment_image($item['iconImage']['id'],$item['iconimageSize']);
								}
							} 
						$content_loop .= '</span>';
					}
					$content_loop .= '<span>'.wp_kses_post($item['tabTitle']).'</span>';
				$content_loop .= '</div>';

				$content_loop .= '<div id="tpag-tab-content-'.esc_attr($block_id).esc_attr($i).'" class="tpgb-tab-content '.esc_attr($active).'" data-tab="'.esc_attr($i).'"  role="tabpanel" aria-labelledby="'.(!empty($item['UniqueId']) ? esc_attr($item['UniqueId']) : 'tpag-tab-title-'.esc_attr($block_id).esc_attr($i) ).'">';
					$content_loop .= '<div class ="tpgb-content-editor" >';
						if( !empty($item['contentType']) && $item['contentType'] == 'content'){
							$content_loop .= wp_kses_post($item['tabDescription']);
						}
					$content_loop .= '</div>';
				$content_loop .= '</div>';
				
			endforeach;
		}
	}

	$tab_content .= '<div class="tpgb-tabs-content-wrapper tpgb-trans-linear" >' .$content_loop. '</div>';
	
	$output .= '<div class="tpgb-tabs-tours tpgb-block-'.esc_attr($block_id).'  tab-view-'.esc_attr($tabLayout).' '.esc_attr($blockClass).' '.esc_attr($responsive_class).'">';
		$output .= '<div class="tpgb-tabs-wrapper tpgb-relative-block '.esc_attr($responsive_class).' "    data-tab-default="1" data-tab-hover="no" >';
			if($navPosition == 'top' || $navPosition == 'left'  ){
				$output .= $tab_nav.$tab_content;
			}else{
				$output .= $tab_content.$tab_nav;
			}
		$output .= '</div>';
	$output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_tabs_tours() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_tabs_tours_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_tabs_tours' );