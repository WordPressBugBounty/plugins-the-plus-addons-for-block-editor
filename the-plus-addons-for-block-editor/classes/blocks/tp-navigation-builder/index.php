<?php
/* Block : Navigation Menu
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_navbuilder_render_callback( $attributes, $content) {
	$output = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$menuName = (!empty($attributes['menuName'])) ? $attributes['menuName'] : '';
	$menuLayout = (!empty($attributes['menuLayout'])) ? $attributes['menuLayout'] : 'horizontal';
	$HvrClick = (!empty($attributes['HvrClick'])) ? $attributes['HvrClick'] : 'hover';
	$menuEffect = (!empty($attributes['menuEffect'])) ? $attributes['menuEffect'] : 'style-1';
	$VtitleBar = (!empty($attributes['VtitleBar'])) ? $attributes['VtitleBar'] : false;
	$titleLink = (!empty($attributes['titleLink'])) ? $attributes['titleLink'] : '';
	$navTitle = (!empty($attributes['navTitle'])) ? $attributes['navTitle'] : 'Navigation Menu';
	$prefixIcon = (!empty($attributes['prefixIcon'])) ? $attributes['prefixIcon'] : '';
	$postfixIcon = (!empty($attributes['postfixIcon'])) ? $attributes['postfixIcon'] : '';
	$vSideevent = (!empty($attributes['vSideevent'])) ? $attributes['vSideevent'] : 'normal';
	$menuAlign = (!empty($attributes['menuAlign'])) ? $attributes['menuAlign'] : 'text-left';
	$respoMenu = (!empty($attributes['respoMenu'])) ? $attributes['respoMenu'] : false;
	$resmenuType = (!empty($attributes['resmenuType'])) ? $attributes['resmenuType'] : 'toggle';
	$momenuType = (!empty($attributes['momenuType'])) ? $attributes['momenuType'] : 'normal-menu';
	$toggleStyle = (!empty($attributes['toggleStyle'])) ? $attributes['toggleStyle'] : 'style-1';
	$toggleAlign = (!empty($attributes['toggleAlign'])) ? $attributes['toggleAlign'] : 'text-left';
	$ctmtoggletype = (!empty($attributes['ctmtoggletype'])) ? $attributes['ctmtoggletype'] : 'custom_icon';
	$openIcon = (!empty($attributes['openIcon'])) ? $attributes['openIcon'] : '';
	$closeIcon = (!empty($attributes['closeIcon'])) ? $attributes['closeIcon'] : '';
	$openImg = (!empty($attributes['openImg'])) ? $attributes['openImg'] : '';
	$closeImg = (!empty($attributes['closeImg'])) ? $attributes['closeImg'] :'';
	$navAlign = (!empty($attributes['navAlign'])) ? $attributes['navAlign'] : 'text-left';
	$iconStyle = (!empty($attributes['iconStyle'])) ? $attributes['iconStyle'] : 'none';
	$navwidth = (!empty($attributes['navwidth'])) ? $attributes['navwidth'] : 'full';
	$Hvreffect = (!empty($attributes['Hvreffect'])) ? $attributes['Hvreffect'] : 'none';
	$menuInver = (!empty($attributes['menuInver'])) ? $attributes['menuInver'] : false;
	$submenuInver = (!empty($attributes['submenuInver'])) ? $attributes['submenuInver'] : false;
	$subMenuindi = (!empty($attributes['subMenuindi'])) ? $attributes['subMenuindi'] : 'none' ;
	$TypeMenu = (!empty($attributes['TypeMenu'])) ? $attributes['TypeMenu'] : '';
	$menulastOpen = (!empty($attributes['menulastOpen'])) ? $attributes['menulastOpen'] : false;
	$accessWeb = (!empty($attributes['accessWeb'])) ? $attributes['accessWeb'] : false;
	$closeMenu = (!empty($attributes['closeMenu'])) ? 'yes' : 'no' ;

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$uid = 'Tpgbmobilemenu2285'.esc_attr($block_id).'';
	//set Main Menu Hover class
	$menu_hover_class = '';
	if($Hvreffect == 'style-1'){
		$menu_hover_class = 'menu-hover-style-1';
	}else if($Hvreffect == 'style-2'){
		$menu_hover_class = 'menu-hover-style-2';
	}

	//set Menu Inverse Class
	$menu_hover_inverse = '';
	if(!empty($menuInver)){
		$menu_hover_inverse = 'hover-inverse-effect';
	}
	if(!empty($submenuInver)){
		$menu_hover_inverse .= ' submenu-hover-inverse-effect';
	}
	
	$menuopenClass = '';
	if(!empty($menulastOpen) ){
		$menuopenClass = 'tpgb-open-sub-menu-left';
	}

	//Get Navigation Title Bar For VerticalSide Menu
	$getnavTitle = '';
	$link_attr = Tp_Blocks_Helper::add_link_attributes($titleLink);
	$getnavTitle .= '<a class="vertical-side-toggle tpgb-trans-easeinout" href="'.(!empty($titleLink['url'])  ? esc_url($titleLink['url']) : '#').'" '.$link_attr.'>';
		$getnavTitle .= '<span>';
			$getnavTitle .= '<i aria-hidden="true" class="pre-icon '.esc_attr($prefixIcon).'"></i>';
			$getnavTitle .= wp_kses_post($navTitle);
		$getnavTitle .= '</span>';
		$getnavTitle .= '<i aria-hidden="true" class="post-icon '.esc_attr($postfixIcon).'"></i>';
	$getnavTitle .= '</a>';

	//get Toggle icon & Image
	$getToogleicon = '';
	$getToogleicon .= '<div class="close-toggle-icon  toggle-icon">';
		if($ctmtoggletype == 'custom_icon'){
			$getToogleicon .= '<i class="'.esc_attr($openIcon).'"></i>';
		}else{
			$opimgSrc ='';
			$altText = (isset($openImg['alt']) && !empty($openImg['alt'])) ? esc_attr($openImg['alt']) : ((!empty($openImg['title'])) ? esc_attr($openImg['title']) : esc_attr__('Close Image','the-plus-addons-for-block-editor'));

			if(!empty($openImg) && !empty($openImg['id'])){
				$opimgSrc = wp_get_attachment_image($openImg['id'] , 'full', false, ['alt'=> $altText]);
			}else if(!empty($openImg['url'])){
				$opimgSrc = '<img src="'.esc_url($openImg['url']).'" alt="'.$altText.'"/>';
			}
			$getToogleicon .= $opimgSrc; 
		} 
	$getToogleicon .= '</div>';
	$getToogleicon .= '<div class="open-toggle-icon toggle-icon">';
		if($ctmtoggletype == 'custom_icon') {
			$getToogleicon .= '<i class="'.esc_attr($closeIcon).'"></i>';
		}else{
			$cloimgSrc ='';
			$altText1 = (isset($closeImg['alt']) && !empty($closeImg['alt'])) ? esc_attr($closeImg['alt']) : ((!empty($closeImg['title'])) ? esc_attr($closeImg['title']) : esc_attr__('Open Image','the-plus-addons-for-block-editor'));

			if(!empty($closeImg) && !empty($closeImg['id'])){
				$cloimgSrc = wp_get_attachment_image($closeImg['id'] , 'full', false, ['alt'=> $altText1]);
			}else if(!empty($closeImg['url'])){
				$cloimgSrc = '<img src="'.esc_url($closeImg['url']).'" alt="'.$altText1.'"/>';
			}
			$getToogleicon .= $cloimgSrc;
		}
	$getToogleicon .= '</div>';
	
	// Set Attr For close Sub Menu on click on Body
	$dataAttr = '';
	if(!empty($closeMenu) && $closeMenu == 'yes' ){
		$dataAttr = 'data-mobile-menu-click="'.esc_attr($closeMenu).'"';
	}

	//Get Navmanu output
    $output .= '<div class="tpgb-navbuilder tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).''.(!empty($accessWeb) ? ' tpgb-web-access' : '').'" data-id="Nav1231" data-menu_id="'.esc_attr($block_id).'" >';
		$output .= '<div class="tpgb-nav-wrap '.esc_attr($menuAlign).'">';
			$output .= '<div class="tpgb-nav-inner menu-'.esc_attr($HvrClick).' menu-'.esc_attr($menuEffect).' indicator-'.esc_attr( $iconStyle ).' sub-menu-indiacator-'.esc_attr($subMenuindi).'" data-menu_transition="'.esc_attr($menuEffect).'" '.$dataAttr.'>';
				$output .= '<div class="tpgb-normal-menu">';
					$output .= '<div class="tpgb-nav-item '.esc_attr($menuLayout).' toggle-'.esc_attr($vSideevent).'">';
						if($menuLayout == 'vertical-side' && !empty($VtitleBar)){
							$output .= $getnavTitle;
						}
						if($TypeMenu != 'standard'){
							$output .= tpgb_mega_menu($attributes);	
						}
					$output .= "</div>";
				$output .= "</div>";
				if(!empty($respoMenu) ){
					if($resmenuType == 'toggle') {
						$output .= '<div class="tpgb-mobile-nav-toggle navbar-header mobile-toggle '.esc_attr($toggleAlign).'">';
							$output .= '<div class="tpgb-toggle-menu hamburger-'.esc_attr($resmenuType).' toggle-'.esc_attr($toggleStyle).'" data-target="#'.esc_attr($uid).'">';
								$output .= '<div class="toggle-line">';
									if($toggleStyle != 'style-5') {
										if($toggleStyle == 'style-1'){
											$output .= '<span></span>';
											$output .= '<span></span>';
										}else{
											$output .= '<span></span>';
											$output .= '<span></span>';
											$output .= '<span></span>';
										}
									}else{
										$output .= $getToogleicon;
									}
								$output .= '</div>';
							$output .= '</div>';
						$output .= "</div>";
					}
					$output .= '<div class="tpgb-mobile-menu tpgb-menu-'.esc_attr($resmenuType).' collapse navbar-collapse navigation-'.esc_attr($navwidth).' '.esc_attr($navAlign).'" id="'.esc_attr($uid).'">';
						if($momenuType=='custom'){
							$output .= tpgb_mega_menu($attributes,1);
						}
					$output .= "</div>";
				}
			$output .= "</div>";
		$output .= "</div>";
    $output .= "</div>";
	
	$css_rule = '';
	if( !empty($menulastOpen) ){
		$menuNo = (!empty($attributes['menuNo'])) ? $attributes['menuNo'] : '';
		if(is_rtl()){
			$css_rule .='[dir="rtl"] .tpgb-block-'.esc_attr($block_id).' .tpgb-nav-item:not(.vertical) .navbar-nav.tpgb-open-sub-menu-left > li:nth-last-child(-n+'.esc_attr($menuNo).') > ul.dropdown-menu ul.dropdown-menu{right: auto;left: 100% !important;}';
			$css_rule .='[dir="rtl"] .tpgb-block-'.esc_attr($block_id).' .tpgb-nav-item:not(.vertical) .navbar-nav.tpgb-open-sub-menu-left > li:nth-last-child(-n+'.esc_attr($menuNo).') > ul.dropdown-menu > li { text-align: left; }';
		}else{
			$css_rule .='.tpgb-block-'.esc_attr($block_id).' .tpgb-nav-item:not(.vertical) .navbar-nav.tpgb-open-sub-menu-left > li:nth-last-child(-n+'.esc_attr($menuNo).') > ul.dropdown-menu ul.dropdown-menu{left: auto !important;right: 100%;}.tpgb-block-'.esc_attr($block_id).' .tpgb-nav-item:not(.menu-vertical) .navbar-nav.tpgb-open-sub-menu-left > li:nth-last-child(-n+'.esc_attr($menuNo).') > ul.dropdown-menu {left: 0;}.tpgb-block-'.esc_attr($block_id).' .tpgb-nav-item:not(.vertical) .navbar-nav.tpgb-open-sub-menu-left > li:nth-last-child(-n+'.esc_attr($menuNo).') > ul.dropdown-menu > li { text-align: right; } .tpgb-block-'.esc_attr($block_id).' .sub-menu-indiacator-style-1 .tpgb-nav-item:not(.vertical) .navbar-nav.tpgb-open-sub-menu-left > li:nth-last-child(-n+'.esc_attr($menuNo).') > ul.dropdown-menu > li .indi-icon .fa{ transform: rotate(180deg);}.tpgb-block-'.esc_attr($block_id).' .sub-menu-indiacator-style-1 .tpgb-nav-item:not(.vertical) .navbar-nav.tpgb-open-sub-menu-left > li:nth-last-child(-n+'.esc_attr($menuNo).') > ul.dropdown-menu > li .indi-icon{left : 0; right : 100%;}.tpgb-block-'.esc_attr($block_id).' .sub-menu-indiacator-style-2 .tpgb-nav-item:not(.vertical) .navbar-nav.tpgb-open-sub-menu-left > li:nth-last-child(-n+'.esc_attr($menuNo).') > ul.dropdown-menu > li .indi-icon:before{left: 10px;right:100%;}.tpgb-block-'.esc_attr($block_id).' .sub-menu-indiacator-style-2 .tpgb-nav-item:not(.vertical) .navbar-nav.tpgb-open-sub-menu-left > li:nth-last-child(-n+'.esc_attr($menuNo).') > ul.dropdown-menu > li .indi-icon:after{left: 4px;right:100%;}';
			
		}
		$output .= '<style>'.$css_rule.'</style>';
	}
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

function tpgb_mega_menu($attributes,$att=''){
	$CustomMenu = '';
	$stylecss = '';
	if(!empty($attributes['ItemMenu'])){
		$CustomMenu .= '<ul class="nav navbar-nav '.($attributes['Hvreffect']=='style-1' ? 'menu-hover-style-1' : ($attributes['Hvreffect']=='style-2' ? 'menu-hover-style-2' : '' )  ).' '.(!empty($attributes['menuInver']) ? 'hover-inverse-effect' : '' ).' '.(!empty($attributes['submenuInver']) ? 'submenu-hover-inverse-effect' : '' ).' '.(!empty($attributes['menulastOpen']) ? ' tpgb-open-sub-menu-left' : '' ).'">';
		$menuArray = $attributes['ItemMenu'];
		$level = 0;
		foreach($attributes['ItemMenu'] as $index => $item){
			
			$depth = $item['depth'];
			$Nextdepth = (!empty($menuArray[intval($index+1)])) ? intval($menuArray[$index+1]['depth']) : '';
			$Prevdepth = (!empty($menuArray[intval($index-1)])) ? intval($menuArray[$index-1]['depth']) : '';
			
			//echo $Prevdepth.'-'.$depth.'-'.$Nextdepth.'</br>';
			$st_child_Li = '';
			if( $depth > 0 ){
				if(($Nextdepth==$depth || $Nextdepth>$depth || $Nextdepth<$depth ) && $Prevdepth!=$depth && $Prevdepth<$depth){
					$level = $level + 1;
					$st_child_Li = '<ul role="menu" class="dropdown-menu">';
				}
				
			}
			
			$st_end_child_Li = $end_child_Li = '';

			if($Nextdepth < $depth) {
				$diff = ((int)$depth - (int)$Nextdepth);
				if($diff >= 1){
					for( $i=0;$i<$diff;$i++ ){
						$end_child_Li .= '</ul></li>';
					}
				}else if($diff===0){
					$end_child_Li .= '</li>';
				}
			}
			
			$name = ''; 
			$itemUrl = '';
			$menuName= '';
			$indiIcon = '';
			$subindiIcon = '';
			//Get Prefix Icon
			$preicon='';
			if(!empty($item['menuiconTy']) && $item['menuiconTy'] == 'icon' ){
				$preicon .= '<span class="tpgb-navicon-wrap"><i class="'.esc_attr($item['preicon']).' nav-menu-icon"></i></span>';
			}else if(!empty($item['menuiconTy']) && $item['menuiconTy'] == 'img'){
				$altText2 = (isset($item['menuImg']['alt']) && !empty($item['menuImg']['alt'])) ? esc_attr($item['menuImg']['alt']) : ((!empty($item['menuImg']['title'])) ? esc_attr($item['menuImg']['title']) : esc_attr__('Navigation Image','the-plus-addons-for-block-editor'));

				if(!empty($item['menuImg']) && !empty($item['menuImg']['id'])){
					$preicon .= '<span class="tpgb-navicon-wrap">'. wp_get_attachment_image($item['menuImg']['id'] , 'full', true, ['class' => 'nav-menu-img', 'alt'=> $altText2]).'</span>';
				}else if(!empty($item['menuImg']['url'])){
					$preicon .= '<span class="tpgb-navicon-wrap"><img src="'.esc_url($item['menuImg']['url']).'" class="nav-menu-img" alt="'.$altText2.'" /></span>';
				}
			}
			
			//Get Descroption
			$navdesc = '';
			if(!empty($item['navDesc'])){
				$navdesc.= '<span class="tpgb-nav-desc">'.wp_kses_post($item['navDesc']).'</span>';
			}
			
			$LinkFilter = (array) $item['LinkFilter'];
			
			$menuName = ( !empty($LinkFilter) && !empty($LinkFilter['filter']) && !empty($LinkFilter['filter']['label']) ) ?  $LinkFilter['filter']['label'] : ''; 
			
			// Get Page Url from id
			$current_active ='';
			if(!empty($LinkFilter['filter']['url'])){
				$itemUrl = $LinkFilter['filter']['url'];
				if( isset($LinkFilter['filter']['id']) && $LinkFilter['filter']['id'] === get_the_ID()){
					$current_active = ' active';
				}
			}else{
				$itemUrl = '#';
			}
			
			$linkAttr = '';
			if(!empty($LinkFilter['filter']) && isset($LinkFilter['filter']['opensInNewTab']) && !empty($LinkFilter['filter']['opensInNewTab'])){
				$linkAttr .= ' target="_blank"';
			}
			
			if($attributes['iconStyle'] == 'style-1' && $depth ==0 && $Nextdepth!='' && $Nextdepth > 0 && $Nextdepth != $depth){
				$indiIcon .= '<span class="indi-icon"><i class="'.($attributes['menuLayout'] == 'vertical-side' ? 'fa fa-angle-right' : 'fa fa-angle-down' ).'"></i></span>';
			}

			if($depth >=1  && $Nextdepth > 1 && $Nextdepth != $depth && $Nextdepth > $depth){
				$subindiIcon .= '<span class="indi-icon">';
					if($attributes['subMenuindi'] == 'style-1'){
						$subindiIcon .= '<i class="fa fa-angle-right"></i>';
					}
				$subindiIcon .= '</span>';
			}

			//Ajax load Class
			$NextMenu =(!empty($menuArray[$index+1])) ? $menuArray[$index+1] : '';

			if(!empty($item['SmenuType']) && $item['SmenuType'] == 'link') {
				$name = '<a href="'.esc_url($itemUrl).'" title="'.esc_attr($menuName).'" data-text="'.esc_attr($menuName).'" '.$linkAttr.'>'.$preicon.'<span class="tpgb-title-wrap">'.esc_html($menuName).$indiIcon.$subindiIcon.$navdesc.'</span></a>';
			}

			$dropdownClass= ($Nextdepth >=2 && ($Nextdepth > $depth) ) ? 'dropdown-submenu menu-item-has-children' : ( ($Nextdepth > $depth) ? 'dropdown menu-item-has-children' : '');
				
			$start_Li = "<li class='menu-item depth-".esc_attr($depth)." ".esc_attr($dropdownClass)." ".(!empty($item['classTxt']) ? esc_attr($item['classTxt']) : '')." tp-repeater-item-".esc_attr($item['_key']). $current_active ."'>";

			$end_Li = '';
			if($Nextdepth===$depth && $depth===0 && $Nextdepth===$Prevdepth ){
				$end_Li = '</li>';
			}
			
			$CustomMenu .= $st_end_child_Li.$st_child_Li.$start_Li.$name.$end_Li.$end_child_Li;
			
		}
		$CustomMenu .= '</ul>';
		if(!empty($stylecss)){
			$CustomMenu .= '<style>'.$stylecss.'</style>';
		}
	}
	return $CustomMenu;
}
/**
 * Render for the server-side
 */
function tpgb_tp_navbuilder() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_navbuilder_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_navbuilder' );