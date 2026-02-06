<?php
/**
 * Block : TP Social Feed
 * @since 1.3.0.1
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_social_feed() {

    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_social_feed_render_callback', true, false, true);
		register_block_type( $block_data['name'], $block_data );
    }
}
add_action( 'init', 'tpgb_tp_social_feed' );

function tpgb_tp_social_feed_render_callback( $attributes, $content) {
	$SocialFeed = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$feed_id = (!empty($attributes['feed_id'])) ? $attributes['feed_id'] : uniqid("feed");
	$layout = (!empty($attributes['layout'])) ? $attributes['layout'] : 'grid';
	$style = (!empty($attributes['style'])) ? $attributes['style'] : 'style-1';
	$Rsocialfeed = (!empty($attributes['AllReapeter'])) ? $attributes['AllReapeter'] : [];
	$columns = (!empty($attributes['columns'])) ? $attributes['columns'] : 'tpgb-col-12';
	$RefreshTime = !empty($attributes['TimeFrq']) ? $attributes['TimeFrq'] : '3600';
	$TimeFrq = array( 'TimeFrq' => $attributes['TimeFrq'] );
	$TotalPost = (!empty($attributes['TotalPost'])) ? $attributes['TotalPost'] : 1000;
	
	$FeedId = (!empty($attributes['FeedId'])) ? preg_split("/\,/", $attributes['FeedId']) : [];
	$ShowTitle = !empty($attributes['ShowTitle']) ? $attributes['ShowTitle'] : false;
	$showFooterIn = (!empty($attributes['showFooterIn'])) ? true : false;
	
	$Postdisplay = (!empty($attributes['Postdisplay']) ? (int)$attributes['Postdisplay'] : 6);
	$postLodop = (!empty($attributes['postLodop']) ? $attributes['postLodop'] : '');
	$postview = (!empty($attributes['postview']) ? $attributes['postview'] : 1);
	$loadbtnText = (!empty($attributes['loadbtnText']) ? $attributes['loadbtnText'] : '');
	$loadingtxt = (!empty($attributes['loadingtxt']) ? $attributes['loadingtxt'] : '');
	$allposttext = (!empty($attributes['allposttext']) ? $attributes['allposttext'] : '');

	$txtLimt = (!empty($attributes['TextLimit']) ? $attributes['TextLimit'] : false );
	$TextCount = (!empty($attributes['TextCount']) ? $attributes['TextCount'] : 100 );
	$TextType = (!empty($attributes['TextType']) ? $attributes['TextType'] : 'char' );
	$TextMore = (!empty($attributes['TextMore']) ? $attributes['TextMore'] : 'Show More' );
	$TextDots = (!empty($attributes['TextDots']) ? '...' : '' );

	$FancyStyle = (!empty($attributes['FancyStyle']) ? $attributes['FancyStyle'] : 'default' );
	$DescripBTM = (!empty($attributes['DescripBTM']) ? $attributes['DescripBTM'] : false );
	$MediaFilter = (!empty($attributes['MediaFilter']) ? $attributes['MediaFilter'] : 'default' );
	
	$ShowFeedId = !empty($attributes['ShowFeedId']) ? $attributes['ShowFeedId'] : false;
	$PopupOption = !empty($attributes['OnPopup']) ? $attributes['OnPopup'] : 'Donothing';
	$Performance = !empty($attributes['perf_manage']) ? $attributes['perf_manage'] : false;

	$NormalScroll='';
	$ScrollOn = !empty($attributes['ScrollOn']) ? $attributes['ScrollOn'] : false;
	$FcyScrolllOn = !empty($attributes['FcySclOn']) ? $attributes['FcySclOn'] : false;
	$OffsetPost = !empty($FeedId) ? $Postdisplay - count($FeedId) : '';
	
	if( !empty($ScrollOn) || !empty($FcyScrolllOn) ){
		$ScrollData = array(
			'className'     => 'tpgb-normal-scroll',
			'ScrollOn'      => $ScrollOn,
			'Height'        => !empty($attributes['ScrollHgt']) ? (int)$attributes['ScrollHgt'] : 150,
			'TextLimit'     => $txtLimt,

			'Fancyclass'    => 'tpgb-fancy-scroll',
			'FancyScroll'   => $FcyScrolllOn,
			'FancyHeight'   => !empty($attributes['FcySclHgt']) ? (int)$attributes['FcySclHgt'] : 150
		);
		$NormalScroll = json_encode($ScrollData, true);
	}

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$list_layout='';
	if( $layout=='grid' || $layout=='masonry' ){
		$list_layout = 'tpgb-isotope';
	}else{
		$list_layout = 'tpgb-isotope';
	}

	$desktop_class='';
	if( $columns ){
		$desktop_class .= 'tpgb-col-'.esc_attr($columns['xs']);
		$desktop_class .= ' tpgb-col-lg-'.esc_attr($columns['md']);
		$desktop_class .= ' tpgb-col-md-'.esc_attr($columns['sm']);
		$desktop_class .= ' tpgb-col-sm-'.esc_attr($columns['xs']);
	}
	
	$fancybox_settings = "";
	if($PopupOption=='OnFancyBox'){
		$fancybox_settings = tpgb_social_feed_fancybox($attributes);
		$fancybox_settings = json_encode($fancybox_settings);
	}
	

	$SocialFeed .= '<div id="'.esc_attr($block_id).'" class="tpgb-block-'.esc_attr($block_id).' '.esc_attr($blockClass).' tpgb-social-feed tpgb-relative-block '.esc_attr($list_layout).'" data-style="'.esc_attr($style).'" data-layout="'.esc_attr($layout).'" data-id="'.esc_attr($block_id).'" data-fid="'.esc_attr($feed_id).'" data-fancy-option=\''.$fancybox_settings.'\' data-scroll-normal=\''.esc_attr($NormalScroll).'\' >';
		
		$FancyBoxJS = '';
		if($PopupOption == 'OnFancyBox'){
			$FancyBoxJS = 'data-fancybox="'.esc_attr($block_id).'"';
		}
		
		$FinalData = [];
        
        if( $Performance == false ){ // || ($Performance == true && $Perfo_transient === false)
			$AllData = [];
			foreach ($Rsocialfeed as $index => $social) {
				$RFeed = (!empty($social['selectFeed'])) ? $social['selectFeed'] : 'Facebook';
				$social = array_merge($TimeFrq,$social);

				if($RFeed == 'Facebook'){
					$AllData[] = tpgb_FacebookFeed($social, $attributes);
                }
			}
            
			if(!empty($AllData)){
				foreach($AllData as $key => $val){
					foreach($val as $key => $vall){ 
						$FinalData[] =  $vall; 
					}
				}
			}
			$Feed_Index = array_column($FinalData, 'Feed_Index');
			array_multisort($Feed_Index, SORT_ASC, $FinalData);
			set_transient("SF-Performance-$feed_id", $FinalData, $RefreshTime);
            set_transient("SF-Performance-$block_id", $FinalData, $RefreshTime);
            set_transient("SF-free-backup-$block_id", $FinalData, 0);
		} else {
			$FinalData = get_transient("SF-Performance-$feed_id");

            if ($FinalData === false || empty($FinalData)) {
                $FinalData = get_transient("SF-Performance-$block_id");
            }
            
            if ($FinalData === false || empty($FinalData)) {
                $FinalData = get_transient("SF-free-backup-$block_id");
            }
		}

		if(!empty($FinalData)){
			foreach ($FinalData as $index => $data) {
				$PostId = !empty($data['PostId']) ? $data['PostId'] : [];
				if(in_array($PostId, $FeedId)){
					unset($FinalData[$index]);
				}
			}
          
			if(!empty($FinalData)){

				$SocialFeed .= '<div class="post-loop-inner social-feed-'.esc_attr($style).'" >';
				foreach ($FinalData as $F_index => $AllVmData) {
					$uniqEach = uniqid();
					$PopupSylNum = $block_id . "-" . $F_index . "-" . $uniqEach;
					$RKey = (!empty($AllVmData['RKey'])) ? $AllVmData['RKey'] : '';
					$PostId = (!empty($AllVmData['PostId'])) ? $AllVmData['PostId'] : '';
					$UName = (!empty($AllVmData['UName'])) ? $AllVmData['UName'] : '';
					$selectFeed = (!empty($AllVmData['selectFeed'])) ? $AllVmData['selectFeed'] : '';
					$Massage = (!empty($AllVmData['Massage'])) ? $AllVmData['Massage'] : '';
					$Description = (!empty($AllVmData['Description'])) ? $AllVmData['Description'] : '';
					$Type = (!empty($AllVmData['Type'])) ? $AllVmData['Type'] : '';
					$PostLink = (!empty($AllVmData['PostLink'])) ? $AllVmData['PostLink'] : '';
					$CreatedTime = (!empty($AllVmData['CreatedTime'])) ? $AllVmData['CreatedTime'] : '';
					$PostImage = (!empty($AllVmData['PostImage'])) ? $AllVmData['PostImage'] : '';
					$UserName = (!empty($AllVmData['UserName'])) ? $AllVmData['UserName'] : '';
					$UserImage = (!empty($AllVmData['UserImage'])) ? $AllVmData['UserImage'] : '';
					$UserLink = (!empty($AllVmData['UserLink'])) ? $AllVmData['UserLink'] : '';
					$socialIcon = (!empty($AllVmData['socialIcon'])) ? $AllVmData['socialIcon'] : '';
					$ErrorClass = (!empty($AllVmData['ErrorClass'])) ? $AllVmData['ErrorClass'] : '';

					$EmbedURL = (!empty($AllVmData['Embed'])) ? $AllVmData['Embed'] : '';
					$EmbedType = (!empty($AllVmData['EmbedType'])) ? $AllVmData['EmbedType'] : '';
					
					if($selectFeed == 'Facebook'){
						$Fblikes = (!empty($AllVmData['FbLikes'])) ? $AllVmData['FbLikes'] : 0;
						$comment = (!empty($AllVmData['comment'])) ? $AllVmData['comment'] : 0;
						$share = (!empty($AllVmData['share'])) ? $AllVmData['share'] : 0;
						$likeImg = TPGB_ASSETS_URL.'assets/images/social-feed/like.png';
						$ReactionImg = TPGB_ASSETS_URL.'assets/images/social-feed/love.png';
						
						$FbAlbum = (!empty($AllVmData['FbAlbum'])) ? $AllVmData['FbAlbum'] : false;
						// if(!empty($FbAlbum)){
						// 	$FancyBoxJS = 'data-fancybox="album-Facebook'.esc_attr($F_index).'-'.esc_attr($block_id).'"';
						// }
					}
					$ImageURL=$videoURL="";
					if($Type == 'video' || $Type == 'photo'){
						$sepPostId = explode("_",$PostId);
						$newPId = (!empty($sepPostId[1])) ? $sepPostId[1] : '';
						$fbPostRD = 'https://www.facebook.com/'.esc_attr($UName).'/posts/'.esc_attr($newPId);
						$videoURL = ($selectFeed == 'Facebook' && $PopupOption == 'GoWebsite') ? (!empty($PostLink[0]['link'])) ? $PostLink[0]['link'] : $fbPostRD : $PostLink;
						$ImageURL = $PostImage;
					}
					if(!empty($FbAlbum)){
						$PostLink = (!empty($PostLink[0]['link'])) ? $PostLink[0]['link'] : 0;
					}
					
					if( (!in_array($PostId,$FeedId) && $F_index < $TotalPost) && ( ($MediaFilter == 'default') || ($MediaFilter == 'ompost' && !empty($PostLink) && !empty($PostImage)) || ($MediaFilter == 'hmcontent' &&  empty($PostLink) && empty($PostImage) )) ){
						$SocialFeed .= '<div class="grid-item splide__slide '.esc_attr('feed-'.$selectFeed.' '.$desktop_class .' '.$RKey.' ').'" data-index="'.esc_attr($selectFeed).esc_attr($F_index).'" >';
							ob_start(); 
								include TPGB_INCLUDES_URL. "social-feed/social-feed-".sanitize_file_name($style).".php";
								$SocialFeed .= ob_get_contents();
							ob_end_clean();
						$SocialFeed .= '</div>';
					}

				}
				$SocialFeed .= '</div>';
			}else{
				$SocialFeed .= '<div class="error-handal">'.esc_html__('All Social Feed','the-plus-addons-for-block-editor').'</div>';
			}
		}else{
			$SocialFeed .= '<div class="error-handal">'.esc_html__('All Social Feed','the-plus-addons-for-block-editor').'</div>';
		}

	$SocialFeed .= '</div>';

    return $SocialFeed;
}

function tpgb_FacebookFeed($social,$attr){
	$BaseURL = 'https://graph.facebook.com/v20.0';
	$FbKey = (!empty($social['_key'])) ? $social['_key'] : '';
	$FbAcT = (!empty($social['RAToken'])) ? $social['RAToken'] : '';
	$FbPType = (!empty($social['ProfileType'])) ? $social['ProfileType'] : 'post';
	$FbPageid = (!empty($social['Pageid'])) ? $social['Pageid'] : '';
	$FbAlbum = (!empty($social['fbAlbum'])) ? $social['fbAlbum'] : false;
	$FbLimit = (!empty($social['MaxR'])) ? $social['MaxR'] : 6;
	$FbALimit = (!empty($social['AlbumMaxR'])) ? $social['AlbumMaxR'] : 6;	
	$Fbcontent = (!empty($social['content'])) ? $social['content'] : [];
	$FbTime = (!empty($social['TimeFrq'])) ? $social['TimeFrq'] : '3600';
	$FbselectFeed = !empty($social['selectFeed']) ? $social['selectFeed'] : '';
	$FbIcon = 'fab fa-facebook social-logo-fb';
	$SSL_VER = $attr['CURLOPT_SSL_VERIFYPEER'];
	$content = [];
	if(!empty($Fbcontent) && (is_array($Fbcontent) || is_object($Fbcontent)) ){
		foreach ($Fbcontent as $Data) {
			$Filter = (!empty($Data['value'])) ? $Data['value'] : 'photo';
			array_push($content,$Filter);
		}
	}else{
        array_push($content, 'photo', 'video', 'status'); 
	}
	
	$url = '';
	$FbAllData = '';
	$FbArr = [];
    if(!empty($FbAcT) && $FbPType == 'post'){
        $url = "{$BaseURL}/me?fields=id,name,first_name,last_name,link,email,birthday,picture,posts.limit($FbLimit){type,message,story,caption,description,shares,picture,full_picture,source,created_time,reactions.summary(true),comments.summary(true).filter(toplevel)},albums.limit($FbALimit){id,type,link,picture,created_time,name,count,photos.limit($FbLimit){id,link,created_time,likes,images,name,comments.summary(true).filter(toplevel)}}&access_token={$FbAcT}";
    }else if(!empty($FbAcT) && !empty($FbPageid) && $FbPType == 'page'){
        $url = "{$BaseURL}/{$FbPageid}?fields=id,name,username,link,fan_count,new_like_count,phone,emails,about,birthday,category,picture,posts.limit($FbLimit){id,full_picture,created_time,message,attachments{media,media_type,title,url},picture,story,status_type,shares,reactions.summary(true),likes.summary(true),comments.summary(true).filter(toplevel)},albums.limit($FbALimit){id,type,link,picture,created_time,name,count,photos.limit($FbLimit){id,link,created_time,images,name}}&access_token={$FbAcT}";
    }
	
	if(!empty($url)){
		$GetFbRL = get_transient("Fb-Url-$FbKey");
		$GetFbTime = get_transient("Fb-Time-$FbKey");
		
		if( $GetFbRL != $url || $GetFbTime != $FbTime ){
			$FbAllData = tpgb_api_call($url,$SSL_VER);
				set_transient("Fb-Url-$FbKey", $url, $FbTime);
				set_transient("Data-Fb-$FbKey", $FbAllData, $FbTime);
				set_transient("Fb-Time-$FbKey", $FbTime, $FbTime);
		 }else{
		 	$FbAllData = get_transient("Data-Fb-$FbKey");
		 }
		
		$status = (!empty($FbAllData['HTTP_CODE']) ? $FbAllData['HTTP_CODE'] : '');
		if($status == 200){
			$FbPost = '';
			if(!empty($FbAlbum)){
				$FbPost = (!empty($FbAllData['albums']['data'])) ? $FbAllData['albums']['data'] : [];
			}else{
				$FbPost = !empty($FbAllData['posts']['data']) ? $FbAllData['posts']['data'] : (!empty($FbAllData['albums']['data']) ? $FbAllData['albums']['data'] : []);
			}
			
			foreach ($FbPost as $index => $FbData){
				
				$link = (!empty($FbAllData['link']) ? $FbAllData['link'] : '');
				$name = (!empty($FbAllData['name']) ? $FbAllData['name'] : '');
				$u_name = (!empty($FbAllData['username']) ? $FbAllData['username'] : '');
				$id = (!empty($FbData['id']) ? $FbData['id'] : '');
				$type = (!empty($FbData['type']) ? $FbData['type'] : '');
				$FbMessage = (!empty($FbData['message']) ? $FbData['message'] : '');
				$FbPicture = $FbSource = (!empty($FbData['full_picture']) ? $FbData['full_picture'] : '');
				$Created_time = (!empty($FbData['created_time'])) ? tpgb_feed_Post_time($FbData['created_time']) : '';
				$FbReactions = (!empty($FbData['reactions']['summary']['total_count']) ? tpgb_number_short($FbData['reactions']['summary']['total_count']) : 0);
				$FbComments = (!empty($FbData['comments']['summary']['total_count']) ? tpgb_number_short($FbData['comments']['summary']['total_count']) : 0);
				$Fbshares = (!empty($FbData['shares']['count']) ? tpgb_number_short($FbData['shares']['count']) : '');
				
				

				if($type == "video"){
					$FbSource = (!empty($FbData['source']) ? $FbData['source'] : '');
				}
				$FbCaption = (!empty($FbData['caption']) ? $FbData['caption'] : '');
				$FbDescription = (!empty($FbData['description'])) ? $FbData['description'] : '';
				
				if($FbPType == 'page'){
					$type = (!empty($FbData['attachments']['data'][0]['media_type']) ? $FbData['attachments']['data'][0]['media_type'] : '');
					if($type == 'album'){
						$type = "photo";
					}
					if($type == 'video'){
						$FbSource = (!empty($FbData['attachments']['data'][0]['media']['source']) ? $FbData['attachments']['data'][0]['media']['source'] : '');
					}
				}
				
				if(!empty($FbAlbum)){
					$type = 'video'; 
					$link = (!empty($FbData['link']) ? $FbData['link'] : '');
					$FbMessage = (!empty($FbData['name']) ? $FbData['name'] : '');
					$Fbcount = (!empty($FbData['count']) ? $FbData['count'] : '');
					$FbPicture = (!empty($FbData['picture']['data']['url']) ? $FbData['picture']['data']['url'] : '');
					$FbSource = (!empty($FbData['photos']['data']) ? $FbData['photos']['data'] : []);
				}
               
				if( (in_array('photo',$content) ) || (in_array('video',$content) ) || ( in_array('status',$content) ) ){	
                   
					$FbArr[] = array(
						"Feed_Index"	=> $index,
						"PostId"		=> $id,
						"Massage" 		=> $FbCaption . $FbDescription,
						"Description"	=> $FbMessage,
						"Type" 			=> "video",
						"PostLink" 		=> $FbSource,
						"CreatedTime" 	=> $Created_time,
						"PostImage" 	=> $FbPicture,
						"UserName" 		=> $name,
						"UName"			=> $u_name,
						"UserImage" 	=> (!empty($FbAllData['picture']['data']['url']) ? $FbAllData['picture']['data']['url'] : ''),
						"UserLink" 		=> $link,
						"share" 		=> $Fbshares,
						"comment" 		=> $FbComments,
						"FbLikes" 		=> $FbReactions,
						"Embed" 		=> "Alb",
						"EmbedType"     => $type,
						"FbAlbum" 		=> $FbAlbum,
						"socialIcon" 	=> $FbIcon,
						"selectFeed"    => $FbselectFeed,
						"RKey" 			=> "tp-repeater-item-$FbKey",
					);
				}
			}		
		}else{
			$FbArr[] = tpgb_SF_Error_handler($FbAllData, $FbKey, $FbselectFeed, $FbIcon);
		}
	}else{
		$Msg = "";
		if(empty($FbAcT)){
			$Msg .= 'Empty Access Token </br>';
		}
		if($FbPType == 'page' && empty($FbPageid)){
			$Msg .= 'Empty Page ID';
		}
		$ErrorData['error']['message'] = $Msg;
		$FbArr[] = tpgb_SF_Error_handler($ErrorData, $FbKey, $FbselectFeed, $FbIcon);
	}
	
	return $FbArr;
}

function tpgb_api_call( $API, $SSL = true ){
	$Final=[];

	$args = array(
        'method'  => 'GET',
        'timeout' => 30,
        'sslverify' => $SSL,
    );

	$URL = wp_remote_get($API, $args);
	
	$status_code = wp_remote_retrieve_response_code($URL);
	$body = wp_remote_retrieve_body($URL);
	$status_code = array( "HTTP_CODE" => $status_code );

	$Response = json_decode($body, true);
	if( is_array($status_code) && is_array($Response) ){
		$Final = array_merge($status_code, $Response);
	}
	return $Final;
}

function tpgb_feed_Post_time($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
 
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
 
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
 
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function tpgb_number_short( $n, $precision = 1 ) {
    if ($n < 900) {
        $n_format = number_format($n, $precision);
        $suffix = '';
    } else if ($n < 900000) {
        $n_format = number_format($n / 1000, $precision);
        $suffix = 'K';
    } else if ($n < 900000000) {
        $n_format = number_format($n / 1000000, $precision);
        $suffix = 'M';
    } else if ($n < 900000000000) {
        $n_format = number_format($n / 1000000000, $precision);
        $suffix = 'B';
    } else {
        $n_format = number_format($n / 1000000000000, $precision);
        $suffix = 'T';
	}
	
    if ( $precision > 0 ) {
        $dotzero = '.' . str_repeat( '0', $precision );
        $n_format = str_replace( $dotzero, '', $n_format );
    }
    return $n_format . $suffix;
}

function tpgb_social_feed_fancybox($attr){
	$button = array();
	$button[] = 'close';

	$fancybox = array();
	$fancybox['loop'] = $attr['LoopFancy'];
	$fancybox['arrows'] = $attr['ArrowsFancy'];
	$fancybox['clickContent'] = $attr['ClickContent'];
	$fancybox['transitionEffect'] = $attr['TransitionFancy'];
	$fancybox['button'] = $button;

	return $fancybox;
}

function tpgb_SF_Error_handler($ErrorData, $Rkey='', $selectFeed='', $Icon=''){
	$Error = !empty($ErrorData['error']) ? $ErrorData['error'] : [];
	return array(
		"Feed_Index" 	=> 0,
		"ErrorClass"    => "error-class",
		"socialIcon" 	=> $Icon,
		"CreatedTime" 	=> "<b>{$selectFeed}</b>",
		"Description" 	=> !empty($Error['message']) ? $Error['message'] : 'Something Wrong',
		"UserName" 		=> !empty($Error['HTTP_CODE']) ? 'Error Code : '.$Error['HTTP_CODE'] : 400,
		"UserImage" 	=> TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg',
		"selectType"    => $selectFeed,
		"RKey" 			=> "tp-repeater-item-$Rkey",
	);
}