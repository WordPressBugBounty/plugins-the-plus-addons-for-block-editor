<?php
/* Block : Breadcrumbs
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_breadcrumbs_callback( $attributes, $content) {
    $uid = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $style = (!empty($attributes['style'])) ? $attributes['style'] : '';
    $markupSch = (!empty($attributes['markupSch'])) ? $attributes['markupSch'] : '';
	$ctmHomeurl = (!empty($attributes['ctmHomeurl'])) ? $attributes['ctmHomeurl'] : '';
	
    $showTerms = (!empty($attributes['showTerms'])) ? $attributes['showTerms'] : '';
    $taxonomySlug = (!empty($attributes['taxonomySlug'])) ? $attributes['taxonomySlug'] : '';
    $showpartTerms = (!empty($attributes['showpartTerms'])) ? $attributes['showpartTerms'] : false;
    $showchildTerms = (!empty($attributes['showchildTerms'])) ? $attributes['showchildTerms'] : false;

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
    $icons = $icontype = '';
    if($attributes['homeIcon'] == "icon") {
        if(!empty($attributes["iconFontStyle"]) && $attributes["iconFontStyle"] == 'font_awesome') {
            $icons = (!empty($attributes["iconFawesome"])) ? $attributes["iconFawesome"] : '';
            $icontype = 'icon';
        } else if(!empty($attributes["iconFontStyle"]) && $attributes["iconFontStyle"] == 'icon_image') {
            $iconsImg = (!empty($attributes['iconsImg']['id'])) ? $attributes['iconsImg']['id'] : '';
            if(!empty($iconsImg)){
                $img = wp_get_attachment_image_src($iconsImg);
                $icons = $img[0];
                $icontype = 'image';
            }else if(!empty($attributes['iconsImg']['url'])){
                $icons = $attributes['iconsImg']['url'];
                $icontype = 'image';
            }
        }
    }
    
    $sepIcons = $sepIconType = '';
    if($attributes['sepIcon']=="sep_icon") {
        if(!empty($attributes["sepIconFontStyle"]) && $attributes["sepIconFontStyle"]=='sep_font_awesome') {
            $sepIcons= (!empty($attributes["sepIconFawesome"])) ? $attributes["sepIconFawesome"] : '';
            $sepIconType='sep_icon';
        } else if(!empty($attributes["sepIconFontStyle"]) && $attributes["sepIconFontStyle"]=='sep_icon_image') {
            $sepIconImg = (!empty($attributes['sepIconImg']['id'])) ? $attributes['sepIconImg']['id'] : '';
            if(!empty($sepIconImg)){
                $img = wp_get_attachment_image_src($sepIconImg);
                $sepIcons = $img[0];
                $sepIconType = 'sep_image';
            }else if(!empty($attributes['sepIconImg']['url'])){
                $sepIcons = $attributes['sepIconImg']['url'];
                $sepIconType = 'sep_image';
            }
        }
    }
    
    $cssClass = '';
    if($style == 'style-1') {
        $bredStyleClass = 'bred_style_1';
    } else if($style == 'style-2') {
        $bredStyleClass = 'bred_style_2';
    }
	
    $cssClass = (!empty($attributes["bredAlign"]['md'])) ? ' bred-' . esc_attr($attributes["bredAlign"]['md']) : '';
    $cssClass .= (!empty($attributes["bredAlign"]['sm'])) ? ' bred-tablet-' . esc_attr($attributes["bredAlign"]['sm']) : '';
    $cssClass .= (!empty($attributes["bredAlign"]['xs'])) ? ' bred-mobile-' . esc_attr($attributes["bredAlign"]['xs']) : '';

    $homeTitle = $attributes["homeTitle"];
    
    $bdToggleHome = (!empty($attributes['bdToggleHome'])) ? "on-off-home" : "";
    $bdToggleParent = (!empty($attributes['bdToggleParent'])) ? "on-off-parent" : "";	

    if( (!empty($attributes['bdToggleParent'])) ){
    	$letterLimitParent = (!empty($attributes['letterLimitParent'])) ? $attributes['letterLimitParent'] : '';
	}else{
		$letterLimitParent ='0';
	}
	if((!empty($attributes['bdToggleCurrent']))){
    	$letterLimitCurrent = (!empty($attributes['letterLimitCurrent'])) ? $attributes['letterLimitCurrent'] : '';
	}else{
		$letterLimitCurrent = '0';
	}
    
    $bdToggleCurrent = (!empty($attributes['bdToggleCurrent'])) ? "on-off-current" : "";
    
    $breadcrumbs_last_sec_tri_normal = '';
    $breadcrumbs_bar = '';	
    
    $breadcrumbs_bar .= '<div class="tp-breadcrumbs tpgb-block-'.esc_attr($uid).' '.esc_attr($blockClass).'">';
        $breadcrumbs_bar .= '<div class="pt_plus_breadcrumbs_bar '.  trim( $cssClass ) .'">';
        
            if(!empty($attributes['bredWidth']) && $style == 'style-1') {
                $breadcrumbs_bar .= '<div class="pt_plus_breadcrumbs_bar_inner '.esc_attr($bredStyleClass).'" style="width:100%">';
            } else {
                $breadcrumbs_bar .= '<div class="pt_plus_breadcrumbs_bar_inner '.esc_attr($bredStyleClass).'">';
            }
            
                $activeColorCurrent = ($attributes['activeColorCurrent'] == true) ? "default_active" : "";

                $breadcrumbs_bar .= Tp_Blocks_Helper::theplus_breadcrumbs($icontype, $sepIconType, $icons, $homeTitle, $sepIcons, $activeColorCurrent, $breadcrumbs_last_sec_tri_normal, $bdToggleHome, $bdToggleParent, $bdToggleCurrent, $letterLimitParent, $letterLimitCurrent, $markupSch, $ctmHomeurl , $showTerms , $taxonomySlug , $showpartTerms , $showchildTerms );
            $breadcrumbs_bar .= '</div>';
        $breadcrumbs_bar .= '</div>';
    $breadcrumbs_bar .= '</div>';
    
	$breadcrumbs_bar = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $breadcrumbs_bar);
	
	return $breadcrumbs_bar;
}

function tpgb_tp_breadcrumbs_render() {
    
    $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_breadcrumbs_callback' , true);
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_breadcrumbs_render' );