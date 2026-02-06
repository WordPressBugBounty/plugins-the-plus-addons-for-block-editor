<?php
/**
 * Block : Social Reviews
 * @since 2.0.2
 */
defined( 'ABSPATH' ) || exit;

function tpgb_social_reviews_callback($attributes, $content) {
	$reviews = '';
    $block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
    $review_id = (!empty($attributes['review_id'])) ? $attributes['review_id'] : uniqid("review");

    $layout = (!empty($attributes['layout'])) ? $attributes['layout'] : 'grid';
    $RType = (!empty($attributes['RType'])) ? $attributes['RType'] : 'review';
    $style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
    $columns = (!empty($attributes['columns'])) ? $attributes['columns'] : 'tpgb-col-12';
    $Rowclass = ($layout != 'carousel') ? 'tpgb-row' : '';
    
    $Repeater = (!empty($attributes['Rreviews'])) ? $attributes['Rreviews'] : [];
    $RefreshTime = (!empty($attributes['TimeFrq'])) ? $attributes['TimeFrq'] : '3600';
    $TimeFrq = array( 'TimeFrq' => $RefreshTime );
    $OverlayImage = (!empty($attributes['OverlayImage'])) ? "overlayimage" : "";

    $FeedId = (!empty($attributes['FeedId'])) ? preg_split("/\,/", $attributes['FeedId']) : [];
	$ShowFeedId = (!empty($attributes['ShowFeedId'])) ? $attributes['ShowFeedId'] : false;
	
    $txtLimt = (!empty($attributes['TextLimit']) ? $attributes['TextLimit'] : false );
	$TextCount = (!empty($attributes['TextCount']) ? $attributes['TextCount'] : 100 );
	$TextType = (!empty($attributes['TextType']) ? $attributes['TextType'] : 'char' );
	$TextMore = (!empty($attributes['TextMore']) ? $attributes['TextMore'] : 'Show More' );
	$TextLess = (!empty($attributes['TextLess']) ? $attributes['TextLess'] : 'Show Less' );
	$TextDots = (!empty($attributes['TextDots']) ? '...' : '' );
	$UserFooter = (!empty($attributes['s2Layout']) ? $attributes['s2Layout'] : 'layout-1' );

	$Performance = !empty($attributes['perf_manage']) ? $attributes['perf_manage'] : false;
    $disSocialIcon = !empty($attributes['disSocialIcon']) ? $attributes['disSocialIcon'] : false;
	$disProfileIcon = !empty($attributes['disProfileIcon']) ? $attributes['disProfileIcon'] : false;

    $blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

    $list_layout='';
	if( $layout=='grid' || $layout=='masonry' ){
		$list_layout = 'tpgb-isotope';
	}else{
		$list_layout = 'tpgb-isotope';
	}
	$desktop_class=$tablet_class=$mobile_class= '';
	if( $layout !='carousel' && $columns ){
		$desktop_class .= ' tpgb-col-'.esc_attr($columns['xs']);
		$desktop_class .= ' tpgb-col-lg-'.esc_attr($columns['md']);
		$tablet_class .= ' tpgb-col-md-'.esc_attr($columns['sm']);
		$mobile_class .= ' tpgb-col-sm-'.esc_attr($columns['xs']);
	}
	
	$nFeedId = [];
	if(!empty($ShowFeedId)){
		$nFeedId = $FeedId;
	}
	
	$NormalScroll="";
	$cntScBr = !empty($attributes['cntScBr']) ? true : false;
	$sbheight = !empty($attributes['scrlHeight']) ? $attributes['scrlHeight'] : 100;
	if( !empty($cntScBr)){
		$ScrollData = array(
			'className'     => 'tpgb-normal-scroll',
			'ScrollOn'      => $cntScBr,
			'Height'        => (int)$sbheight,
			'TextLimit'     => $txtLimt,
		);
		$NormalScroll = json_encode($ScrollData, true);
	}
	$txtlimitData='';
	if(!empty($txtLimt)){
		$txtlimitDataa = array(
				'showmoretxt'     => $TextMore,
				'showlesstxt'     => $TextLess,
			);
	   $txtlimitData = json_encode($txtlimitDataa, true);
	}

    $reviews .= '<div class="tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' tpgb-social-reviews tpgb-relative-block '.esc_attr($list_layout).'" id="'.esc_attr($block_id).'" data-style="'.esc_attr($style).'" data-layout="'.esc_attr($layout).'" data-id="'.esc_attr($block_id).'" data-rid="'.esc_attr($review_id).'" data-scroll-normal="'.esc_attr($NormalScroll).'" data-textlimit="'.esc_attr($txtlimitData).'">';

        if( $layout == 'carousel' &&  ( ( isset($showArrows['md']) && !empty($showArrows['md']) ) || ( isset($showArrows['sm']) && !empty($showArrows['sm']) ) || ( isset($showArrows['xs']) && !empty($showArrows['xs']) ) )){
            if(isset($showArrows) && !empty($showArrows)){
                $reviews .= Tp_Blocks_Helper::tpgb_carousel_arrow($arrowsStyle,$arrowsPosition);
            }
        }

        if($RType == "review"){
			$FinalData = [];
			$Perfo_transient = get_transient("SR-Performance-".$review_id);
			if( ($Performance == false) || ($Performance == true && $Perfo_transient === false) ){
				$AllData = [];
				foreach ($Repeater as $index => $R) {
					$RRT = (!empty($R['ReviewsType'])) ? $R['ReviewsType'] : 'facebook';
					$R = array_merge($TimeFrq,$R);

					if($RRT == 'facebook'){
						$AllData[] = tpgb_Facebook_Reviews($R,$attributes);
					}
				}
				if(!empty($AllData)){
					foreach($AllData as $key => $val){
						foreach($val as $key => $vall){ 
							$FinalData[] =  $vall; 
						}
					}
				}
				$Reviews_Index = array_column($FinalData, 'Reviews_Index');
				array_multisort($Reviews_Index, SORT_ASC, $FinalData);	 
				set_transient("SR-Performance-$review_id", $FinalData, $RefreshTime);
			}else{
				$FinalData = get_transient("SR-Performance-".$review_id);
			}
			
			if(!empty($FinalData)){
				
				foreach ($FinalData as $index => $data) {
					$PostId = !empty($data['PostId']) ? $data['PostId'] : [];
					if(in_array($PostId, $nFeedId)){
						unset($FinalData[$index]);
					}
				}
				
                $reviews .= '<div class="'.esc_attr($Rowclass).' post-loop-inner social-reviews-'.esc_attr($style).' '.esc_attr($OverlayImage).'" >';
                    foreach ($FinalData as $F_index => $Review) {
                        $RKey = (!empty($Review['RKey'])) ? $Review['RKey'] : '';
                        $RIndex = (!empty($Review['Reviews_Index'])) ? $Review['Reviews_Index'] : '';
                        $PostId = (!empty($Review['PostId'])) ? $Review['PostId'] : '';
                        $Type = (!empty($Review['Type'])) ? $Review['Type'] : '';
                        $Time = (!empty($Review['CreatedTime'])) ? $Review['CreatedTime'] : '';
                        $UName = (!empty($Review['UserName'])) ? $Review['UserName'] : '';
                        $UImage = (!empty($Review['UserImage'])) ? $Review['UserImage'] : '';
                        $ULink = (!empty($Review['UserLink'])) ? $Review['UserLink'] : '';
                        $PageLink = (!empty($Review['PageLink'])) ? $Review['PageLink'] : '';
                        $Massage = (!empty($Review['Massage'])) ? $Review['Massage'] : '';
                        $Icon = (!empty($Review['Icon'])) ? $Review['Icon'] : 'fas fa-star';
                        $Logo = (!empty($Review['Logo'])) ? $Review['Logo'] : '';
                        $rating = (!empty($Review['rating'])) ? $Review['rating'] : '';
                        $CategoryText = (!empty($Review['FilterCategory'])) ? $Review['FilterCategory'] : '';
                        $ReviewClass = (!empty($Review['selectType'])) ? ' '.esc_attr($Review['selectType']) : '';
                        $ErrClass = (!empty($Review['ErrorClass']) ? $Review['ErrorClass'] : '');
                        $PlatformName = (!empty($Review['selectType'])) ? ucwords(str_replace('custom', '', $Review['selectType'])) : '';
                    
                        if(!in_array($PostId, $nFeedId)){
                            ob_start();
                            include TPGB_PATH. "includes/social-reviews/".sanitize_file_name('social-review-'.$style.'.php');
                                $reviews .= ob_get_contents();
                            ob_end_clean();
                        }
                    }
                
                $reviews .='</div>';
            }else{
                $reviews .= '<div class="error-handal">'.esc_html__('All Social Feed','the-plus-addons-for-block-editor').'</div>';
            }
			
			
        }

    $reviews .='</div>';

    return $reviews;
}

function tpgb_Facebook_Reviews($RData,$attr){
    $Key = (!empty($RData['_key']) ? $RData['_key'] : '');
    $Token = (!empty($RData['Token']) ? $RData['Token'] : '');
    $PageId = (!empty($RData['FbPageId']) ? $RData['FbPageId'] : '');
    $FbRType = (!empty($RData['FbRType']) ? $RData['FbRType'] : '');
    $MaxR = (!empty($RData['MaxR']) ? $RData['MaxR'] : 6);
    $Ricon = (!empty($RData['icons']) ? $RData['icons'] : 'fas fa-star');
    $TimeFrq = (!empty($RData['TimeFrq']) ? $RData['TimeFrq'] : '');
	$RCategory = !empty($RData['RCategory']) ? $RData['RCategory'] : '';
	$ReviewsType = !empty($RData['ReviewsType']) ? $RData['ReviewsType'] : '';
	$Fb_Icon = TPGB_ASSETS_URL.'assets/images/social-review/facebook.svg';
	$FBNagative = !empty($attr['FBNagative']) ? $attr['FBNagative'] : 1;

    $API = '';
    if(!empty($PageId) && !empty($Token)){
        $API = "https://graph.facebook.com/v20.0/{$PageId}?access_token={$Token}&fields=ratings.fields(reviewer{id,name,picture.width(120).height(120)},created_time,rating,recommendation_type,review_text,open_graph_story{id}).limit($MaxR),overall_star_rating,rating_count";
    }
	
    $Fbdata=$FbArr=[];

    if(!empty($API)){
        $GetAPI = get_transient("Fb-R-Url-$Key");
        $GetTime = get_transient("Fb-R-Time-$Key");
        if( $GetAPI != $API || $GetTime != $TimeFrq ){
            $Fbdata = tpgb_Review_Api($API);
            $Fbdata = json_encode($Fbdata);
            set_transient("Fb-R-Url-$Key", $API, $TimeFrq);
            set_transient("Fb-R-Data-$Key", $Fbdata, $TimeFrq);
            set_transient("Fb-R-Time-$Key", $TimeFrq, $TimeFrq);
        }else{
            $Fbdata = get_transient("Fb-R-Data-$Key");
        }
        
        if(!is_array($Fbdata)){
            $Fbdata = json_decode($Fbdata,true);
        }
    
        $Fb_status = (!empty($Fbdata['HTTP_CODE']) ? $Fbdata['HTTP_CODE'] : 400);
        if($Fb_status == 200){
            $Rating = (!empty($Fbdata['ratings']) && !empty($Fbdata['ratings']['data']) ? $Fbdata['ratings']['data'] : []);
            foreach ($Rating as $index => $Data){
                $FB = (!empty($Data['reviewer']) ? $Data['reviewer'] : '');
                $RT = (!empty($Data['recommendation_type']) ? $Data['recommendation_type'] : '');
                $Userlink = (!empty($Data['open_graph_story']) && !empty($Data['open_graph_story']['id']) ?$Data['open_graph_story']['id'] : '');
                $FType = (($FbRType == 'default') ? $RT : $FbRType);
                $rating = 5;
                if($RT == "negative"){
                    $rating = $FBNagative;
                }
                
                if($FType == $RT){
                    $FbArr[] = array(
                        "Reviews_Index"	=> $index,
                        "PostId"		=> (!empty($FB['id']) ? $FB['id'] : ''),
                        "Type" 			=> $RT,
                        "CreatedTime" 	=> (!empty($Data['created_time']) ? tpgb_Review_Time($Data['created_time']) : ''),
                        "UserName" 		=> (!empty($FB['name']) ? $FB['name'] : ''),
                        "UserImage" 	=> (!empty($FB['picture']) && !empty($FB['picture']['data']['url']) ? $FB['picture']['data']['url'] : TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg'),
                        "UserLink"  	=> "https://www.facebook.com/$Userlink",
                        "PageLink"  	=> "https://www.facebook.com/{$PageId}/reviews",
                        "Massage" 		=> (!empty($Data['review_text']) ? $Data['review_text'] : ''),
                        "Icon" 	        => $Ricon,
                        "rating"        => $rating,
                        "Logo"          => $Fb_Icon,
                        "selectType"    => $ReviewsType,
                        "FilterCategory"=> $RCategory,
                        "RKey" 			=> "tp-repeater-item-$Key",
                    );
                }
    
            }
        }else{
            $FbArr[] = tpgb_Review_Error_array( $Fbdata, $Key, $Fb_Icon, $ReviewsType, $RCategory );
        }
    }else{
        $Msg = "";
        $ErrorData = [];
		if(empty($Token)){
			$Msg .= 'Empty Access Token </br>';
		}
		if(empty($PageId)){
			$Msg .= 'Empty Page ID';
		}
		$ErrorData['error']['message'] = $Msg;
        $FbArr[] = tpgb_Review_Error_array( $ErrorData, $Key, $Fb_Icon, $ReviewsType, $RCategory );
    }
    return $FbArr;
}

function tpgb_Review_Time($datetime, $full = false) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks separately (no dynamic property)
    $weeks = floor($diff->d / 7);
    $days  = $diff->d - ($weeks * 7);

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );

    $values = array(
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $days,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    );

    foreach ($string as $k => &$v) {
        if ($values[$k]) {
            $v = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }

    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function tpgb_Review_Api($API){
	$Final=[];

	$URL = wp_remote_get($API);
	$StatusCode = wp_remote_retrieve_response_code($URL);
	$GetDataOne = wp_remote_retrieve_body($URL);
	$Statuscode = array( "HTTP_CODE" => $StatusCode );

	$Response = json_decode($GetDataOne, true);
	if( is_array($Statuscode) && is_array($Response) ){
		$Final = array_merge($Statuscode, $Response);
	}
	return $Final;
}

function tpgb_Review_Error_array( $Data, $RKey, $Icon, $ReviewsType, $RCategory ){
	$Message='';
	if( !empty($Data) && !empty($Data['error_message']) ){
		$Message = $Data['error_message'];
	}else if( !empty($Data) && !empty($Data['error']) && !empty($Data['error']['Message_Errorcurl']) ){
		$Message = $Data['error']['Message_Errorcurl'];
	}else if( !empty($Data) && !empty($Data['error']) ){ 	/* new */
		$Message = $Data['error']['message'];
	}else if( !empty($Data) && !empty($Data['status']) ){	/* new */
		$Message = $Data['status'];
	}else{
		$Message = 'Something Wrong';
	}

	return  array(
		"Reviews_Index" => 1,
		"ErrorClass"    => "danger-error",
		"CreatedTime" 	=> !empty($Data['status']) ? $Data['status'] : '',
		"Massage" 		=> $Message,
		"UserName" 		=> !empty($Data['HTTP_CODE']) ? 'Error No : '.$Data['HTTP_CODE'] : '',
		"UserImage" 	=> $Icon,
		"Logo"          => $Icon,
		"selectType"    => $ReviewsType,
		"FilterCategory"=> $RCategory,
		"RKey" 			=> "tp-repeater-item-{$RKey}",
	);
}

function tpgb_social_reviews() {

    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_social_reviews_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_social_reviews' );