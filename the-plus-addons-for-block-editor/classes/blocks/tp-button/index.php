<?php
/* Block : TP Button
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_button_render_callback( $attributes, $content ) {
	$output = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$styleType = (!empty($attributes['styleType'])) ? $attributes['styleType'] : 'style-1';
	$btnHvrType = (!empty($attributes['btnHvrType'])) ? $attributes['btnHvrType'] : 'hover-left';
	$iconHvrType = (!empty($attributes['iconHvrType'])) ? $attributes['iconHvrType'] : 'hover-top';
	$iconPosition = (!empty($attributes['iconPosition'])) ? $attributes['iconPosition'] : 'iconAfter';
	$icnVrtcal = (!empty($attributes['icnVrtcal'])) ? $attributes['icnVrtcal'] : 'icon-top';
	$iconType = (!empty($attributes['iconType'])) ? $attributes['iconType'] : 'fontAwesome';
	$fontAwesomeIcon = (!empty($attributes['fontAwesomeIcon'])) ? $attributes['fontAwesomeIcon'] : '';
	$imageName = (!empty($attributes['imageName']['url'])) ? $attributes['imageName'] : '';
	$imageSize = (!empty($attributes['imageSize'])) ? $attributes['imageSize'] : 'full';
	$btnText = (!empty($attributes['btnText'])) ? $attributes['btnText'] : '';
	$btnTagText = (!empty($attributes['btnTagText'])) ? $attributes['btnTagText'] : '';
	$hoverText = (!empty($attributes['hoverText'])) ? $attributes['hoverText'] : '';
	$btnLink = (!empty($attributes['btnLink']['url'])) ? $attributes['btnLink']['url'] : '';
	$target = (!empty($attributes['btnLink']['target'])) ? ' target="_blank" ' : '';
	$nofollow = (!empty($attributes['btnLink']['nofollow'])) ? ' rel="nofollow" ' : '';
	$link_attr = Tp_Blocks_Helper::add_link_attributes($attributes['btnLink']);
	$ariaLabel = (!empty($attributes['ariaLabel'])) ? $attributes['ariaLabel'] : '';
	$shakeAnimate = (!empty($attributes['shakeAnimate'])) ? $attributes['shakeAnimate'] : false;
	$btnHvrCnt = (!empty($attributes['btnHvrCnt'])) ? $attributes['btnHvrCnt'] : false;
	$selectHvrCnt = (!empty($attributes['selectHvrCnt'])) ? $attributes['selectHvrCnt'] : '';
	$fancyBox = (!empty($attributes['fancyBox'])) ? $attributes['fancyBox'] : '';
    $svgIcon = (!empty($attributes['svgIcon'])) ? $attributes['svgIcon'] : '';

    $headSvgColor = (!empty($attributes['headSvgColor'])) ? $attributes['headSvgColor'] : '';
    $headSvgfill = (!empty($attributes['headSvgfill'])) ? $attributes['headSvgfill'] : '';
    $headSvgHoverColor = (!empty($attributes['SvgStrHover'])) ? $attributes['SvgStrHover'] : '';
    $headSvgHoverFill = (!empty($attributes['SvgFillHover'])) ? $attributes['SvgFillHover'] : '';
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
	if(class_exists('Tpgbp_Pro_Blocks_Helper')){
		$btnLink = (isset($attributes['btnLink']['dynamic'])) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($attributes['btnLink']) : (!empty($attributes['btnLink']['url']) ? $attributes['btnLink']['url'] : '');
	}

	$IShakeAnimate='';
	if(!empty($shakeAnimate)){
		$IShakeAnimate='shake_animate';
	}
	$iconHover='';
	if($styleType=='style-11' || $styleType=='style-13'){
		$iconHover .=$btnHvrType;
	}
	if($styleType=='style-17'){
		$iconHover .=$iconHvrType;
	}
	$s23VrtclCntr ='';
	if($styleType=='style-23'){
		$s23VrtclCntr .=$icnVrtcal;
	}
	$translin = '';
	if($styleType!='style-10' && $styleType!='style-13'){
		$translin = 'tpgb-trans-linear';
	}
	$getBfrIcon ='';
	$getBfrIcon .='<span class="btn-icon '.esc_attr($translin).' '.($styleType!='style-17' ? ' button-before' : ' tpgb-rel-flex').'">';
	$getBfrIcon .='<i class="'.esc_attr($fontAwesomeIcon).'"></i>';
	$getBfrIcon .='</span>';
	
	$getAftrIcon ='';
	$getAftrIcon .='<span class="btn-icon '.esc_attr($translin).' '.($styleType!='style-17' ? ' button-after' : ' tpgb-rel-flex').'">';
	$getAftrIcon .='<i class="'.esc_attr($fontAwesomeIcon).'"></i>';
	$getAftrIcon .='</span>';

	$imgSrc = $imgBfAf ='';
	$altText = (isset($imageName['alt']) && !empty($imageName['alt'])) ? esc_attr($imageName['alt']) : ((!empty($imageName['title'])) ? esc_attr($imageName['title']) : esc_attr__('Button','the-plus-addons-for-block-editor'));

	if(!empty($imageName) && !empty($imageName['id'])){
		if($styleType!='style-17'){
			if($iconPosition == 'iconBefore'){
				$imgBfAf = 'button-before';
			}else if($iconPosition == 'iconAfter'){
				$imgBfAf = 'button-after';
			}
		}else{
			$imgBfAf = 'tpgb-rel-flex';
		}
		$imgSrc = wp_get_attachment_image($imageName['id'] , $imageSize, false, ['class' => 'btn-icon '.esc_attr($translin).' '.$imgBfAf, 'alt' => $altText]);
	}else if(!empty($imageName['url'])){
		$imgSrc = '<img src="'.esc_url($imageName['url']).'" class="btn-icon '.esc_attr($translin).' '.esc_attr($imgBfAf).'" alt="'.$altText.'"/>';
	}

    
    $svgSrc  = '';
    $svgBfAf = '';

    $svgaltText = ( isset( $svgIcon['alt'] ) && ! empty( $svgIcon['alt'] ) )
        ? esc_attr( $svgIcon['alt'] )
        : ( ! empty( $svgIcon['title'] )
            ? esc_attr( $svgIcon['title'] )
            : esc_attr__( 'Button', 'the-plus-addons-for-block-editor' )
        );

    /* Icon position class */
    if ( ! empty( $svgIcon ) ) {
        if ( $styleType !== 'style-17' ) {
            if ( $iconPosition === 'iconBefore' ) {
                $svgBfAf = 'button-before';
            } elseif ( $iconPosition === 'iconAfter' ) {
                $svgBfAf = 'button-after';
            }
        } else {
            $svgBfAf = 'tpgb-rel-flex';
        }
    }

    /* ALWAYS render SVG using <object> */
    if ( ! empty( $svgIcon['id'] ) ) {

        $svg_url = wp_get_attachment_url( $svgIcon['id'] );

        if ( $svg_url ) {
            $svgSrc  = '<div class="tpgb-draw-svg btn-icon ' . esc_attr( $translin ) . ' ' . esc_attr( $svgBfAf ) . '"
            data-id="service-svg-' . esc_attr( $block_id ) . '"
            data-type="delayed"
            data-duration="1"
            data-stroke="' . esc_attr( $headSvgColor ) . '"
            data-fillColor="' . esc_attr( $headSvgfill ) . '"
            data-strokeColorHover="' . esc_attr( $headSvgHoverColor ) . '"                              
            data-fillColorHover="' . esc_attr( $headSvgHoverFill ) . '"
            data-fillEnable="yes">';
        
            $svgSrc .= '<object
                id="service-svg-' . esc_attr( $block_id ) . '"
                type="image/svg+xml"
                data="' . esc_url( $svg_url ) . '"
                aria-label="' . esc_attr( $svgaltText ) . '">
            </object>';
        
        $svgSrc .= '</div>';
        
        }

    } elseif ( ! empty( $svgIcon['url'] ) ) {

        $svgSrc = '<div class="tpgb-draw-svg btn-icon ' . esc_attr( $translin ) . ' ' . esc_attr( $svgBfAf ) . '"
            data-id="service-svg-' . esc_attr( $block_id ) . '"
            data-type="delayed"
            data-duration="1"
            data-stroke="' . esc_attr( $headSvgColor ) . '"
            data-fillColor="' . esc_attr( $headSvgfill ) . '"   
            data-strokeColorHover="' . esc_attr( $headSvgHoverColor ) . '"                              
            data-fillColorHover="' . esc_attr( $headSvgHoverFill ) . '"
            data-fillEnable="yes">';
        
        $svgSrc .= '<object
            id="service-svg-' . esc_attr( $block_id ) . '"
            type="image/svg+xml"
            data="' . esc_url( $svgIcon['url'] ) . '"
            aria-label="' . esc_attr( $svgaltText ) . '">
        </object>'; 
        
        $svgSrc .= '</div>';
        
    }
	
	$getButtonSource='';
	
	if($styleType!='style-3' && $styleType!='style-6' && $styleType!='style-7' && $styleType!='style-9' && $styleType!='style-23' && $iconPosition=='iconBefore'){
		if($iconType=='fontAwesome'){
			$getButtonSource .= $getBfrIcon;
		}else if($iconType=='image'){
			$getButtonSource .= $imgSrc;
		}else if($iconType=='svg'){
			$getButtonSource .= $svgSrc;
		}
	}
	if($styleType=='style-6'){
		$getButtonSource .='<span class="btn-left-arrow"><i class="fas fa-chevron-right"></i></span>';
	}
	if($styleType=='style-17'){
		$getButtonSource .='<span class="tpgb-rel-flex">'.wp_kses_post($btnText).'</span>';
	}
	if($styleType!='style-17' && $styleType!='style-23'){
		$getButtonSource.= wp_kses_post($btnText);
	}
	if($styleType=='style-23'){
		if($icnVrtcal=='icon-top'){
			$getButtonSource .='<span class="button-tag-hint">';
				if($iconPosition=='iconBefore'){
					if($iconType=='fontAwesome'){
						$getButtonSource .= $getBfrIcon;
					}else if($iconType=='image'){
						$getButtonSource .= $imgSrc;
					}else if($iconType=='svg'){
						$getButtonSource .= $svgSrc;
					}   
				}
					$getButtonSource .= wp_kses_post($btnTagText);
				if($iconPosition=='iconAfter'){
					if($iconType=='fontAwesome'){
						$getButtonSource .= $getAftrIcon;
					}else if($iconType=='image'){
						$getButtonSource .= $imgSrc;
					}else if($iconType=='svg'){
						$getButtonSource .= $svgSrc;
					}
				}
			$getButtonSource .= '</span>';
			$getButtonSource .='<span>'.wp_kses_post($btnText).'</span>';
		}
		if($icnVrtcal=='icon-middle'){
			if($iconPosition=='iconBefore'){
				if($iconType=='fontAwesome'){
					$getButtonSource .= $getBfrIcon;
				}else if($iconType=='image'){
					$getButtonSource .= $imgSrc;
				}else if($iconType=='svg'){
					$getButtonSource .= $svgSrc;
				}
			}
			$getButtonSource .='<span>';	
				$getButtonSource .='<span class="button-tag-hint">'.wp_kses_post($btnTagText).'</span>';
				$getButtonSource.= wp_kses_post($btnText);
			$getButtonSource .='</span>';
			if($iconPosition=='iconAfter'){
				if($iconType=='fontAwesome'){
					$getButtonSource .= $getAftrIcon;
				}else if($iconType=='image'){
					$getButtonSource .= $imgSrc;
				}else if($iconType=='svg'){
					$getButtonSource .= $svgSrc;
				}
			}
		}
		if($icnVrtcal=='icon-bottom'){
			$getButtonSource .='<span class="button-tag-hint">';
				$getButtonSource .= wp_kses_post($btnTagText);
			$getButtonSource .= '</span>';
			$getButtonSource .='<span>';
			if($iconPosition=='iconBefore'){
				if($iconType=='fontAwesome'){
					$getButtonSource .= $getBfrIcon;
				}else if($iconType=='image'){
					$getButtonSource .= $imgSrc;
				}else if($iconType=='svg'){
					$getButtonSource .= $svgSrc;
				}
			}
			$getButtonSource .= wp_kses_post($btnText);
			if($iconPosition=='iconAfter'){
				if($iconType=='fontAwesome'){
					$getButtonSource .= $getAftrIcon;
				}else if($iconType=='image'){
					$getButtonSource .= $imgSrc;
				}else if($iconType=='svg'){
					$getButtonSource .= $svgSrc;
				}
			}
			$getButtonSource .='</span>';
		}
		}
	if($styleType=='style-3'){
		$getButtonSource .='<svg class="arrow" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid" width="48" height="9" viewBox="0 0 48 9">';
			$getButtonSource .='<path d="M48.000,4.243 L43.757,8.485 L43.757,5.000 L0.000,5.000 L0.000,4.000 L43.757,4.000 L43.757,0.000 L48.000,4.243 Z" class="cls-1"></path>';
		$getButtonSource .='</svg>';
		$getButtonSource .='<svg class="arrow-1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid" width="48" height="9" viewBox="0 0 48 9">';
			$getButtonSource .='<path d="M48.000,4.243 L43.757,8.485 L43.757,5.000 L0.000,5.000 L0.000,4.000 L43.757,4.000 L43.757,0.000 L48.000,4.243 Z" class="cls-1"></path>';
		$getButtonSource .='</svg>';
	}
	if($styleType!='style-3' && $styleType!='style-6' && $styleType!='style-7' && $styleType!='style-9' && $styleType!='style-23' && $iconPosition=='iconAfter'){
		if($iconType=='fontAwesome'){
			$getButtonSource .= $getAftrIcon;
		}else if($iconType=='image'){
			$getButtonSource .= $imgSrc;
		}else if($iconType=='svg'){
			$getButtonSource .= $svgSrc;
		}
	}
	if($styleType=='style-7'){
		$getButtonSource .='<span class="btn-arrow '.esc_attr($translin).'"><span class="btn-right-arrow"><i class="fas fa-chevron-right"></i></span></span>';
	}
	if($styleType=='style-9'){
		$getButtonSource .='<span class="btn-arrow '.esc_attr($translin).'">';
			$getButtonSource .='<i class="btn-show fa fa-chevron-right" aria-hidden="true"></i>';
			$getButtonSource .='<i class="btn-hide fa fa-chevron-right" aria-hidden="true"></i>';
		$getButtonSource .='</span>';
	}
	if($styleType=='style-12'){
		$getButtonSource .='<div class="button_line"></div>';
	}
	
	$contentHvrClass='';
	if(!empty($btnHvrCnt) && !empty($selectHvrCnt) ){
		$contentHvrClass = ' tpgb_cnt_hvr_effect cnt_hvr_'.esc_attr($selectHvrCnt);
	}

	$extrAttr = ''; 
	$fancyData = [];
	 
	global $post;
	$post_id = isset($post->ID) ? $post->ID : 0;
	if(!empty($fancyBox)){
		$extrAttr .= 'data-src="#tpgb-query-'.esc_attr($block_id).'-'.esc_attr($post_id).'" data-touch="false" href="javascript:;" ';
		
		$autoDimen = (!empty($attributes['autoDimen'])) ? $attributes['autoDimen'] : false ;


		$fancyData['autoDimensions'] = (int) $autoDimen ;
		$fancyData = htmlspecialchars(json_encode($fancyData), ENT_QUOTES, 'UTF-8');

		$extrAttr .= ' data-fancy-opt= \'' .$fancyData. '\' ';

	}else{
		$extrAttr = 'href="'.esc_url($btnLink).'" '.$target.' '.$nofollow;
	}

	$ariaLabelT = (!empty($ariaLabel)) ? esc_attr($ariaLabel) : ((!empty($btnText)) ? esc_attr($btnText) : esc_attr__("Button", 'the-plus-addons-for-block-editor'));
    $output .= '<div class="tpgb-plus-button tpgb-relative-block tpgb-block-'.esc_attr($block_id).' button-'.esc_attr($styleType).' '.esc_attr($iconHover).' '.esc_attr($blockClass).' ">';
		$output .='<div class="animted-content-inner'.esc_attr($contentHvrClass).'">';
			$output .='<a '.$extrAttr.' class="button-link-wrap '.esc_attr($translin).' '.esc_attr($IShakeAnimate).' '.esc_attr($s23VrtclCntr).' '.(!empty($fancyBox) ? ' tpgb-fancy-popup' : '').' " role="button" aria-label="'.$ariaLabelT.'" data-hover="'.wp_kses_post($hoverText).'" '.$link_attr.'>';
				if($styleType != 'style-17' && $styleType != 'style-23'){
					$output .='<span>'.$getButtonSource.'</span>';
				}
				if($styleType == 'style-17' || $styleType == 'style-23'){
					$output .=$getButtonSource;
				}
			$output .='</a>';
		$output .='</div>';

		// Load Fancy Box Content 
		if(!empty($fancyBox)){
			$output .= '<div class="tpgb-btn-fpopup" id="tpgb-query-'.esc_attr($block_id).'-'.esc_attr($post_id).'" >';
				ob_start();
				if(!empty($attributes['templates']) && $attributes['templates'] != 'none') {
					echo Tpgb_Library()->plus_do_block($attributes['templates']);
				}
				$output .= ob_get_contents();
				ob_end_clean();
			$output .= '</div>';
		}
    $output .= '</div>';

	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_tp_button() {
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_button_render_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_button' );