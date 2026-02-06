<?php
/* Tp Block : Post Author
 * @since	: 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_post_author_render_callback( $attr, $content) {
	$output = '';
	
    $post = get_queried_object();
    $block_id = (!empty($attr['block_id'])) ? $attr['block_id'] : uniqid("title");
	$Align = (!empty($attr['Align'])) ? $attr['Align'] : '';
	$authorStyle = (!empty($attr['authorStyle'])) ? $attr['authorStyle'] : 'style-1';
    $ShowName = (!empty($attr['ShowName'])) ? $attr['ShowName'] : false;
    $ShowBio = (!empty($attr['ShowBio'])) ? $attr['ShowBio'] : false;
    $ShowAvatar = (!empty($attr['ShowAvatar'])) ? $attr['ShowAvatar'] : false;
    $ShowSocial = (!empty($attr['ShowSocial'])) ? $attr['ShowSocial'] : false;
    $ShowRole = (!empty($attr['ShowRole'])) ? $attr['ShowRole'] : false;
	$roleLabel = (!empty($attr['roleLabel'])) ? $attr['roleLabel'] : 'Role : ';
    $titleLabel = (!empty($attr['titleLabel'])) ? $attr['titleLabel'] : 'Author : ';
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attr );
	
	$outputavatar=$outputname=$outputbio=$outputrole=$authorsocial='';
	if(!empty($post)){
		$author_page_url = get_author_posts_url($post->post_author);
		$author_bio =  get_the_author_meta('user_description',$post->post_author);
		if( !empty( $ShowName ) ){
			$author_name = get_the_author_meta('display_name', $post->post_author);
			$outputname .='<a href="'.esc_url($author_page_url).'" class="author-name tpgb-trans-linear" rel="'.esc_attr__('author','the-plus-addons-for-block-editor').'" >'.wp_kses_post($titleLabel).esc_html($author_name).'</a>';
		}
		if(!empty($ShowAvatar)){
			$author_name = get_the_author_meta('display_name', $post->post_author);
			$outputavatar .= '<a href="'.esc_url($author_page_url).'" rel="'.esc_attr__('author','the-plus-addons-for-block-editor').'" aria-label="'.esc_attr($author_name).'" class="author-avatar tpgb-trans-linear">'.get_avatar( get_the_author_meta('email',$post->post_author), 130 ).'</a>';
		}
		if(!empty($ShowBio)){
			$outputbio .= '<div class="author-bio tpgb-trans-linear" >'.esc_html($author_bio).'</div>';
		}

		$user_meta=get_the_author_meta('roles',$post->post_author);
		if(!empty($ShowRole) && !empty($user_meta)){
			$author_role = $user_meta[0];
			$outputrole .= '<span class="author-role">'.wp_kses_post( $roleLabel ).esc_html($author_role).'</span>';
		}

		if(!empty($ShowSocial)){
			$author_website =  get_the_author_meta('user_url',$post->post_author);
			$author_facebook = get_the_author_meta('author_facebook', $post->post_author);
			$author_email =  get_the_author_meta('email',$post->post_author);
			$author_twitter = get_the_author_meta('author_twitter', $post->post_author);
			$author_instagram = get_the_author_meta('author_instagram', $post->post_author);
			$authorsocial .= '<div class="author-social">';
				if(!empty($author_website)){
					$authorsocial .= '<div class="tpgb-author-social-list" ><a href="'.esc_url($author_website).'" aria-label="'.esc_attr__("website",'the-plus-addons-for-block-editor').'" target="_blank"><i class="fas fa-globe-asia"></i></a></div>';
				}
				if(!empty($author_email)){
					$authorsocial .= '<div class="tpgb-author-social-list" ><a href="mailto:'.sanitize_email($author_email).'" aria-label="'.esc_attr__("Email",'the-plus-addons-for-block-editor').'" target="_blank"><i class="fas fa-envelope"></i></a></div>';
				}
				if(!empty($author_facebook)){
					$authorsocial .= '<div class="tpgb-author-social-list" ><a href="'.esc_url($author_facebook).'" aria-label="'.esc_attr__("facebook",'the-plus-addons-for-block-editor').'" target="_blank"><i class="fab fa-facebook-f"></i></a></div>';
				}
				if(!empty($author_twitter)){
					$authorsocial .= '<div class="tpgb-author-social-list" ><a href="'.esc_url($author_twitter).'" aria-label="'.esc_attr__("twitter",'the-plus-addons-for-block-editor').'" target="_blank"><i class="fab fa-twitter" ></i></a></div>';
				}
				if(!empty($author_instagram)){
					$authorsocial .= '<div class="tpgb-author-social-list" ><a href="'.esc_url($author_instagram).'" aria-label="'.esc_attr__("instagram",'the-plus-addons-for-block-editor').'" target="_blank"><i class="fab fa-instagram"></i></a></div>';
				}
			$authorsocial .='</div>';
		}
	}

    $output .= '<div class="tpgb-post-author tpgb-block-'.esc_attr($block_id ).' '.esc_attr($blockClass).'" >';
		$output .= '<div class="tpgb-post-inner  author-'.esc_attr($authorStyle).' '.($authorStyle == 'style-2' ? ' text-'.esc_attr($Align) : '' ).' ">';
			if($ShowAvatar){
				$output .=$outputavatar;
			}
			$output .='<div class="author-info">';
				if(!empty($ShowName)){
					$output .=$outputname;
				}
				if(!empty($ShowRole)){
					$output .= $outputrole;
				}
				if(!empty($ShowBio)){
					$output .=$outputbio;
				}
				if(!empty($ShowSocial)){
					$output .=$authorsocial;
				}
			$output .= '</div>';
		$output .= '</div>';
    $output .= '</div>';
	
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attr, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_post_author_content() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_post_author_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_post_author_content' );