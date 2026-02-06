<?php
/* Block : Data Table
 * @since : 1.1.3
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_datatable_callback( $attributes, $content) {
	$DataTable = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $ContentTable = (!empty($attributes['ContentTable'])) ? $attributes['ContentTable'] : '';
    $TableHeader = (!empty($attributes['TableHeader'])) ? $attributes['TableHeader'] : [];
    $Tablebody = (!empty($attributes['Tablebody'])) ? $attributes['Tablebody'] : [];
	$TbSort = (!empty($attributes['TbSort'])) ? $attributes['TbSort'] : false;
    $IconPosition = (!empty($attributes['IconPosition'])) ? $attributes['IconPosition'] : 'left';
    $ImgPosition = (!empty($attributes['ImgPosition'])) ? $attributes['ImgPosition'] : 'left';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );
	
    $sorting = (!empty($TbSort)) ? 'yes' : 'no';
    $Search = 'no';
    $Filter = 'no';

    $DTHeader = '';
    $DTBody = '';

    $DataTable .= '<div class="tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).'">';
        $DataTable .= '<div class="tpgb-table-wrapper">';
            $DataTable .= '<table class="tpgb-table" id="tpgb-table-id-'.esc_attr($block_id).'" data-id="'.esc_attr($block_id).'" data-sort-table="'.esc_attr($sorting).'" data-show-entry="'.esc_attr($Filter).'" data-searchable="'.esc_attr($Search).'" role="grid">';

                if( $ContentTable =='custom' ){
                    $row_count_tb = count( $TableHeader );
                    $headerArray = array();
                    $headerArrayicon = array();
                    $headerArrayimage = array();
                    if ( $row_count_tb > 1 ) {
                        $counter_row = 1;
                        $inline_count = 0;
                        $cell_col_count = 0;
                        $first_row_th = true;
                        $Mob_thc = 0;

                        foreach ( $TableHeader as $index => $item ) {
                            $ThIcon= '';
                            $ThImg = '';
                            $thColumnSpan = (!empty($item['thColumnSpan'])) ? $item['thColumnSpan'] : 1;
                            $thRowSpan = (!empty($item['thRowSpan'])) ? $item['thRowSpan'] : 1;
							$checkText = (!empty($item['thtext']) ? '' : ' less-icon-space');
                            if( (!empty($item['thDRicon'])) && $item['thDRicon'] == 'icon' && !empty($item['thicon']) ){
                                $ThIcon = '<span class="tpgb-align-icon--'.esc_attr($IconPosition).esc_attr($checkText).'"><i class="'.esc_attr($item['thicon']).' tableicon"></i></span>';
                            }else if(!empty($item['thDRicon']) && $item['thDRicon'] == 'image' && !empty($item['thDRimage'])) {
                                $Thimagesize = (!empty($item['thimagesize'])) ? $item['thimagesize'] : 'thumbnail';
                                $ThImgID = ( isset($item['thDRimage']['id'])) ? $item['thDRimage']['id'] : '';
                                $ThImgurl = wp_get_attachment_image( $ThImgID,$Thimagesize, false, ['class' => 'tpgb-col-img--'.esc_attr($IconPosition).esc_attr($checkText) ] );
                                $ThImg = $ThImgurl;
                            }

                            if( $item['thAction'] === 'cell' ){
                                $DTHeader .= '<th class="tpgb-table-col tp-repeater-item-'.esc_attr($item['_key']).'" colspan="'.esc_attr($thColumnSpan).'" rowspan="'.esc_attr($thRowSpan).'" data-sort="'.esc_attr($cell_col_count).'" scope="col">';
                                        $DTHeader .= '<span class="tpgb-table__text">';
                                            $DTHeader .= ( $IconPosition == 'left' ) ? $ThIcon : '';
                                            $DTHeader .= ( $ImgPosition == 'left') ? $ThImg : '';
												$DTHeader .= (!empty($item['thtext']) ? '<span class="tpgb-table__text-inner">'.wp_kses_post($item['thtext']).'</span>' : '');
                                            $DTHeader .= ( $IconPosition == 'right' ) ? $ThIcon : '';
                                            $DTHeader .= ( $ImgPosition == 'right') ? $ThImg : '';
                                        $DTHeader .= '</span>';
                                        $DTHeader .= '<span class="tpgb-sort-icon">';
                                            if(!empty($TbSort)){
                                                $DTHeader .= '<i class="up-icon fas fa-sort-up"></i>';
                                                $DTHeader .= '<i class="down-icon fas fa-sort-down"></i>';
                                            }
                                        $DTHeader .= '</span>';
                                $DTHeader .= '</th>';
                                
                                    if (isset($item['thtext'])) {
                                        $headerArray[$Mob_thc] = wp_kses_post($item['thtext']);
                                    }
                                    $headerArrayicon[$Mob_thc] = $ThIcon;
                                    $headerArrayimage[$Mob_thc] = $ThImg;

                                $Mob_thc++;
                                $cell_col_count++;
                            }else {
                                if ( $counter_row > 1 && $counter_row < $row_count_tb ) {
                                    $DTHeader .= '</tr><tr class="tpgb-table-row" role="row">';                                    
                                    $first_row_th = false;
                                } elseif ( 1 === $counter_row && "row" === $attributes['TableHeader'][0]['thAction'] ) {                                    
                                    $DTHeader .= '<tr class="tpgb-table-row" role="row">';
                                }
                                $Mob_thc = 0;
                            }   
                            $counter_row++;
                            $inline_count++;
                        }  
                    }          
                    
                    $row_count = count( $Tablebody );
                    if ( $row_count > 1 ) {
                        $counter = 1;	
                        $cell_inline_count = 0;
                        $data_entry_col = 0;
                        $Mob_trc = 0;
                    
                        foreach ( $Tablebody as $index => $item ) {
                            if( $item['trAction'] == 'cell' ){
                                $TrColumnSpan = (!empty($item['TrColumnSpan'])) ? $item['TrColumnSpan'] : 1;
                                $TrRowSpan = (!empty($item['TrRowSpan'])) ? $item['TrRowSpan'] : 1;
                                $Tag = (!empty($item['TrHeading']) && $item['TrHeading'] == 'th') ? $item['TrHeading'] : 'td';
                                $Btntx = (!empty($item['Trbtntext']) ? $item['Trbtntext'] : __('Click Here','the-plus-addons-for-block-editor') );
                                $Btnlink = (!empty($item['TrbtnLink']) && !empty($item['TrbtnLink']['url'])) ? 'href="'.esc_url($item['TrbtnLink']['url']).'"' : '';
                                $target = ( !empty($item['TrbtnLink']['target'])) ? 'target="_blank"' : '';
				                $nofollow = (!empty($item['TrbtnLink']['nofollow'])) ? 'rel="nofollow"' : '';
                                
								$TRIcon = '';
                                $TRImg = '';
                                
								$checkText = (!empty($item['trtext']) ? '' : ' less-icon-space');

                                if( !empty($item['trDricon']) && $item['trDricon'] == 'icon' && !empty($item['TrfaIcon']) ){
                                    $TRIcon = '<span class="tpgb-align-icon--'.esc_attr($IconPosition).$checkText.'"><i class="'.esc_attr($item['TrfaIcon']).' tableicon"></i></span>';
                                }else if(!empty($item['trDricon']) && $item['trDricon'] == 'image' && !empty($item['trDrimage'])){
                                    $TRimagesize = (!empty($item['trimagesize'])) ? $item['trimagesize'] : 'thumbnail';
                                    $TRDrimgid = (!empty($item['trDrimage']['id'])) ? $item['trDrimage']['id'] : '';
                                    $TRImgurl = wp_get_attachment_image( $TRDrimgid,$TRimagesize, false, ['class' => 'tpgb-col-img--'.esc_attr($IconPosition).$checkText ] );
                                    $TRImg = $TRImgurl;
                                }
                                $DTBody .= '<'.Tp_Blocks_Helper::validate_html_tag($Tag).' class="tpgb-table-col tp-repeater-item-'.esc_attr($item['_key']).' '.( !empty($item['TrLink']) && !empty($item['TrLink']['url']) ? ' tpgb-td-flex' : '' ).'"  colspan="'.esc_attr($TrColumnSpan).'" rowspan="'.esc_attr($TrRowSpan).'">';
                                    
                                    if( !empty($item['TrLink']) && !empty($item['TrLink']['url']) ){
										$target1 = ( !empty ($item['TrLink']['target'])) ? 'target="_blank"' : '';
                                        $nofollow1= ( !empty($item['TrLink']['nofollow']) ) ? 'rel="nofollow"' : '';
										$link_attr = Tp_Blocks_Helper::add_link_attributes($item['TrLink']);
                                        $DTBody.='<a href="'.esc_url($item['TrLink']['url']).'" class="tb-col-link tpgb-relative-block " '.$target1.'  '.$nofollow1.' '.$link_attr.'>';
                                    }
                                    if((isset($item['trtext']) && $item['trtext'] != '') || $TRIcon != '' || $TRImg != '' ){
                                        $DTBody .= '<span class="tpgb-table__text">';
                                            $DTBody .= ( $IconPosition == 'left') ? $TRIcon : '';
                                            $DTBody .= ( $ImgPosition == 'left') ? $TRImg : '';
                                                $DTBody .= (!empty($item['trtext']) ? '<span class="tpgb-table__text-inner">'.wp_kses_post($item['trtext']).'</span>' : '');
                                            $DTBody .= ( $IconPosition == 'right') ? $TRIcon : '';
                                            $DTBody .= ( $ImgPosition == 'right') ? $TRImg : '';
                                        $DTBody .= '</span>';   
                                    }
									
									if( !empty($item['TrLink']) && !empty($item['TrLink']['url']) ){
										$DTBody .= '</a>';
									}

                                    if( (!empty($item['Trbtn'])) && $item['Trbtn'] == TRUE ){
										$btn_attr = Tp_Blocks_Helper::add_link_attributes($item['TrbtnLink']);
                                        $DTBody .='<div class="pt_tpgb_button tp-repeater-item-'.esc_attr($item['_key']).' button-style-8">';
                                            $DTBody .='<a '.(!empty($Btnlink) ? $Btnlink : 'href="#"').'  '.$target.' '.$nofollow.' class="button-link-wrap" '.$btn_attr.'>' .wp_kses_post($Btntx).( !empty($item['btnIcon']) ? '<i class="'.esc_attr($item['btnIcon']).'"></i>' : '' ).'</a>';
                                        $DTBody .='</div>';
                                    }
                                 
                                $DTBody .= '</'.Tp_Blocks_Helper::validate_html_tag($Tag).'>';
                                
                                $Mob_trc++;
                            }else{
                                if ( $counter > 1 && $counter < $row_count ) {
                                    $data_entry_col++;
                                    $DTBody .= '</tr><tr data-entry="'.esc_attr($data_entry_col).'" class="tpgb-table-row odd" role="row">';
                                } elseif ( 1 === $counter && "row" === $attributes['Tablebody'][0]['trAction'] ) {
                                    $data_entry_col = 1;
                                    $DTBody .= '<tr data-entry="'.esc_attr($data_entry_col).'" class="tpgb-table-row odd" role="row">';
                                }
                                $Mob_trc = 0;
                            }
                            $counter++;
                            $cell_inline_count++;
                        }                        
                    }

                        $DataTable .= '<thead>';
                            $DataTable .= $DTHeader;
                        $DataTable .= '</thead>';

                        $DataTable .= '<tbody>';
                            $DataTable .= $DTBody;
                        $DataTable .= '</tbody>';
                }

                $DataTable .= '</table>';
        $DataTable .= '</div>';
    $DataTable .= '</div>';
	
	$DataTable = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $DataTable);
	
    return $DataTable;
}

function tpgb_tp_datatable_render() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_datatable_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_tp_datatable_render' );