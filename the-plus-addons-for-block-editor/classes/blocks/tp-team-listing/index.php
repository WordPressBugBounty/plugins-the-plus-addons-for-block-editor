<?php
/* Block : Team Member Listing
 * @since : 1.3.0
 */
defined( 'ABSPATH' ) || exit;
if (!function_exists('tpgb_tp_team_member_listing_render_callback')) {
function tpgb_tp_team_member_listing_render_callback( $attributes, $content) {
	$TeamMember = '';
	$block_id = (!empty($attributes['block_id'])) ? $attributes['block_id'] : uniqid("title");
	$style = (!empty($attributes['Style'])) ? $attributes['Style'] : 'style-1';
	$layout = (!empty($attributes['layout'])) ? $attributes['layout'] : 'grid';
	$DisbleLink = (!empty($attributes['DisLink'])) ? $attributes['DisLink'] : false;
	$TeamMemberR = (!empty($attributes['TeamMemberR'])) ? $attributes['TeamMemberR'] : [];
	$columns = (!empty($attributes['columns'])) ? $attributes['columns'] : 'md';
	$TitleTag = (!empty($attributes['TitleTag'])) ? $attributes['TitleTag'] : 'h3';
	$Designation = (!empty($attributes['DesignDis'])) ? $attributes['DesignDis'] : false;
	$DisableIcon = (!empty($attributes['SocialIcon'])) ? $attributes['SocialIcon'] : false;
	$DisableISize = (!empty($attributes['DImgS'])) ? $attributes['DImgS'] : false;
	$ImageSize = (!empty($attributes['ImgSize'])) ? $attributes['ImgSize'] : 'full';

	$showArrows = (!empty($attributes['showArrows'])) ? $attributes['showArrows'] : [ 'md' => false ];
	
	$Default_Img = TPGB_ASSETS_URL.'assets/images/tpgb-placeholder.jpg';

	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attributes );

	$desktop_class = '';
	if( $layout !='carousel' && $layout !='metro' && $columns ){
		$desktop_class .= 'tpgb-col-'.esc_attr($columns['xs']);
		$desktop_class .= ' tpgb-col-lg-'.esc_attr($columns['md']);
		$desktop_class .= ' tpgb-col-md-'.esc_attr($columns['sm']);
		$desktop_class .= ' tpgb-col-sm-'.esc_attr($columns['xs']);
	}


	
	
	$TeamMember .= '<div id="'.esc_attr($block_id).'" class="tpgb-block-'.esc_attr($block_id).' tpgb-relative-block  tpgb-team-member-list team-'.esc_attr($style).' '.esc_attr($blockClass).' " data-style="'.esc_attr($style).'" data-layout="'.esc_attr($layout).'" data-id="'.esc_attr($block_id).'">';		
		$TeamMember .= '<div class="post-loop-inner tpgb-row">';
			
			if( !empty($TeamMemberR) ){
				foreach ( $TeamMemberR as $index => $TeamItem ) {
					$TeamName = (isset($TeamItem['TName']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($TeamItem['TName']) : $TeamItem['TName'];
					$TeamDesignation = ( !empty($TeamItem['TDesig']) ) ? $TeamItem['TDesig'] : '';
					$ImgId = ( !empty($TeamItem['TImage']) ) ? $TeamItem['TImage'] : [];
					$TeamCUrl = (isset($TeamItem['CusUrl']['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['CusUrl']) : (!empty($TeamItem['CusUrl']['url']) ? $TeamItem['CusUrl']['url'] : '');
					$TeamWsUrl = (isset($TeamItem['WsUrl']['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['WsUrl']) : (!empty($TeamItem['WsUrl']['url']) ? $TeamItem['WsUrl']['url'] : '');
					$TeamFbUrl = (isset($TeamItem['FbUrl']['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['FbUrl']) : (!empty($TeamItem['FbUrl']['url']) ? $TeamItem['FbUrl']['url'] : '');
					$TeamMailUrl = (isset($TeamItem['MailUrl']['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['MailUrl']) : (!empty($TeamItem['MailUrl']['url']) ? $TeamItem['MailUrl']['url'] : '');
					$TeamIGUrl = (isset($TeamItem['IGUrl']['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['IGUrl']) : (!empty($TeamItem['IGUrl']['url']) ? $TeamItem['IGUrl']['url'] : '');
					$TeamTwUrl = (isset($TeamItem['TwUrl']['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['TwUrl']) : (!empty($TeamItem['TwUrl']['url']) ? $TeamItem['TwUrl']['url'] : '');
					$TeamldUrl = (isset($TeamItem['ldUrl']['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($TeamItem['ldUrl']) : (!empty($TeamItem['ldUrl']['url']) ? $TeamItem['ldUrl']['url'] : '');
					$Telephone = (isset($TeamItem['TelNum']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_val($TeamItem['TelNum']) : $TeamItem['TelNum'];

					// Set Default Image Url
					if(empty($ImgId)){
						$ImgId['url'] = $Default_Img;
					}

					$TeamMember .= '<div class="grid-item '.esc_attr($desktop_class).'">';
						$TeamMember .= '<div class="team-list-content tpgb-trans-linear">';
						
								$ImageHTML = $TeamImage = $AttImg = '';
								if(!empty($TeamCUrl) || !empty($ImgId)){
								
									if(!empty($ImgId)){
										$linkImage = '';
										$altText = (isset($ImgId['alt']) && !empty($ImgId['alt'])) ? esc_attr($ImgId['alt']) : ((!empty($ImgId['title'])) ? esc_attr($ImgId['title']) : esc_attr__('Profile Image','the-plus-addons-for-block-editor'));
										if( $layout !='carousel' && !empty($DisableISize) ){
											if(!empty($ImgId['id'])){
												$AttImg .= wp_get_attachment_image($ImgId['id'] , $ImageSize, false, ['alt'=> $altText]);
											}else if(!empty($ImgId['url'])){
												$imgUrl = (isset($ImgId['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($ImgId) : (!empty($ImgId['url']) ? $ImgId['url'] : '');
												$AttImg .= '<img src="'.esc_url($imgUrl).'" alt="'.$altText.'"/>';
											}else{
												$AttImg .= '<img src="'.esc_url($Default_Img).'" alt="'.$altText.'"/>';
											}
											$TeamImage .= $AttImg;
										}else{
											if(!empty($ImgId['id'])){
												
												$AttImg .= wp_get_attachment_image($ImgId['id'] , 'full' , false, ['alt'=> $altText]);
											}else if(!empty($ImgId['url'])){
												$imgUrl = (isset($ImgId['dynamic']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::tpgb_dynamic_repeat_url($ImgId) : (!empty($ImgId['url']) ? $ImgId['url'] : '');
												$AttImg .= '<img src="'.esc_url($imgUrl).'" alt="'.$altText.'"/>';
											}else{
												$AttImg .= '<img src="'.esc_url($Default_Img).'" alt="'.$altText.'"/>';
											}
											$TeamImage .= $AttImg;
										}

										$linkImage .= '<div class="tpgb-team-profile">';
											$linkImage .= '<span class="thumb-wrap">'.$TeamImage.'</span>';
										$linkImage .= '</div>';

										if(!empty($DisbleLink)){
											$ImageHTML .= $linkImage;
										}else{
											$link_attr = (isset($TeamItem['CusUrl']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['CusUrl']) : '';
											$ImageHTML .= '<a href="'.esc_url($TeamCUrl).'" '.$link_attr.' aria-label="'.esc_attr($TeamName).'">'.$linkImage.'</a>';
										}
									}
								}

								$IconHTML = '';
								if( !empty($DisableIcon) ){
									$Nofollow=$Target="";

									$IconHTML .= '<div class="tpgb-team-social-content">';
										$IconHTML .= '<div class="tpgb-team-social-list">';
											if( !empty($TeamWsUrl) ){
												$wb_attr = (isset($TeamItem['WsUrl']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['WsUrl']) : "";
												$Target = ( !empty($TeamItem['WsUrl']) && !empty($TeamItem['WsUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['WsUrl']) && !empty($TeamItem['WsUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="tpgb-team-profile-link">';
													$IconHTML .= '<a href="'.esc_url($TeamWsUrl).'" '.$Target.' '.$Nofollow.' '.$wb_attr.'  aria-label="'.esc_attr__('Site URL','the-plus-addons-for-block-editor').'"><i class="fas fa-globe" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($TeamFbUrl) ){
												$fb_attr = (isset($TeamItem['FbUrl'])&& class_exists('Tpgbp_Pro_Blocks_Helper'))? Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['FbUrl']):"";
												$Target = ( !empty($TeamItem['FbUrl']) && !empty($TeamItem['FbUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['FbUrl']) && !empty($TeamItem['FbUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="fb-link">';
													$IconHTML .= '<a href="'.esc_url($TeamFbUrl).'" '.$Target.' '.$Nofollow.' '.$fb_attr.' aria-label="'.esc_attr__('Facebook','the-plus-addons-for-block-editor').'"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($TeamTwUrl) ){
												$tw_attr = (isset($TeamItem['TwUrl']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['TwUrl']) : "";
												$Target = ( !empty($TeamItem['TwUrl']) && !empty($TeamItem['TwUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['TwUrl']) && !empty($TeamItem['TwUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="twitter-link">';
													$IconHTML .= '<a href="'.esc_url($TeamTwUrl).'" '.$Target.' '.$Nofollow.' '.$tw_attr.' aria-label="'.esc_attr__('Twitter','the-plus-addons-for-block-editor').'"><i class="fab fa-twitter" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($TeamIGUrl) ){
												$ig_attr = (isset($TeamItem['IGUrl']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['IGUrl']) : '';
												$Target = ( !empty($TeamItem['IGUrl']) && !empty($TeamItem['IGUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['IGUrl']) && !empty($TeamItem['IGUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="instagram-link">';
													$IconHTML .= '<a href="'.esc_url($TeamIGUrl).'" '.$Target.' '.$Nofollow.' '.$ig_attr.' aria-label="'.esc_attr__('Instagram','the-plus-addons-for-block-editor').'"><i class="fab fa-instagram" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($TeamMailUrl) ){
												$ml_attr = (isset($TeamItem['MailUrl']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['MailUrl']) : "";
												$Target = ( !empty($TeamItem['MailUrl']) && !empty($TeamItem['MailUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['MailUrl']) && !empty($TeamItem['MailUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="mail-link">';
													$IconHTML .= '<a href="'.esc_url($TeamMailUrl).'" '.$Target.' '.$Nofollow.' '.$ml_attr.' aria-label="'.esc_attr__('Mail','the-plus-addons-for-block-editor').'"><i class="fas fa-envelope-square"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($TeamItem['ldUrl']['url']) ){
												$ld_attr = (isset($TeamItem['ldUrl']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['ldUrl']) : '';
												$Target = ( !empty($TeamItem['ldUrl']['target']) ) ? 'target="_blank"' : "";
												$Nofollow = ( !empty($TeamItem['ldUrl']['nofollow']) ) ? 'rel="nofollow"' : "";
												$IconHTML .= '<div class="linkedin-link">';
													$IconHTML .= '<a href="'.esc_url($TeamItem['ldUrl']['url']).'" '.$Target.' '.$Nofollow.' '.$ld_attr.' aria-label="'.esc_attr__('LinkedIn','the-plus-addons-for-block-editor').'"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
											if( !empty($Telephone) ){
												$IconHTML .= '<div class="Telephone-link">';
													$IconHTML .= '<a href="'.esc_url('tel:'.$Telephone).'" aria-label="'.esc_attr__('Phone No','the-plus-addons-for-block-editor').'"><i class="fas fa-phone" aria-hidden="true"></i></a>';
												$IconHTML .= '</div>';
											}
										$IconHTML .= '</div>';
									$IconHTML .= '</div>';	
								}

							$TitleHTML = '';
							if(!empty($TeamName)){
								$TitleHTML .= '<'.Tp_Blocks_Helper::validate_html_tag($TitleTag).' class="tpgb-post-title">';
									if( !empty($DisbleLink) ){
										$TitleHTML .= wp_kses_post($TeamName);
									}else{
										$link_attr = (isset($TeamItem['CusUrl']) && class_exists('Tpgbp_Pro_Blocks_Helper')) ? Tpgbp_Pro_Blocks_Helper::add_link_attributes($TeamItem['CusUrl']) : '';
										$TitleHTML .= '<a href="'.esc_attr($TeamCUrl).'" '.$link_attr.'>'.wp_kses_post($TeamName).'</a>';
									}
								$TitleHTML .= '</'.Tp_Blocks_Helper::validate_html_tag($TitleTag).'>';
							}

							$DesigHTML = '';
							if( !empty($TeamDesignation) && !empty($Designation) ){
								$DesigHTML .= '<div class="tpgb-member-designation">'.wp_kses_post($TeamDesignation).'</div>';
							}					

							$FinalHTML = '';
							if( $style == 'style-1' ){
								$FinalHTML .= '<div class="post-content-image">';
									$FinalHTML .= $ImageHTML;
									$FinalHTML .= $IconHTML;
								$FinalHTML .= '</div>';
								$FinalHTML .= '<div class="post-content-bottom">';
									$FinalHTML .= $TitleHTML;
									$FinalHTML .= $DesigHTML;
								$FinalHTML .= '</div>';
							}

							$TeamMember .= $FinalHTML;
						$TeamMember .= '</div>';
					$TeamMember .= '</div>';
				}
			}
		$TeamMember .= "</div>";
	$TeamMember .= "</div>";
	
	$TeamMember = Tpgb_Blocks_Global_Options::block_Wrap_Render($attributes, $TeamMember);
    return $TeamMember;
}

}
if (!function_exists('tpgb_tp_team_member_listing')) {
function tpgb_tp_team_member_listing() {
	if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_team_member_listing_render_callback', true, true);
		register_block_type( $block_data['name'], $block_data );
	}
}
}
add_action( 'init', 'tpgb_tp_team_member_listing' );