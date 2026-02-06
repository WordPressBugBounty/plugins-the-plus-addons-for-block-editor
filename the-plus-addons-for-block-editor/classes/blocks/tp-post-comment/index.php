<?php
/* Tp Block : Post Comment
 * @since	: 1.3.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_post_comment_render_callback( $attr, $content) {
	$output = '';
	$post_id = get_queried_object_id();
    $post = get_queried_object();
    $block_id = (!empty($attr['block_id'])) ? $attr['block_id'] : uniqid("title");
    $commentTitle = (!empty($attr['commentTitle'])) ? $attr['commentTitle'] : 'Comment';
	$comment_args = tpgb_comment_args($attr);
    $comment = get_comments($post);
    $list_args = array('style' => 'ul', 'short_ping' => true, 'avatar_size' => 100, 'page' => $post_id );
	
	$blockClass = Tp_Blocks_Helper::block_wrapper_classes( $attr );
	
	ob_start();
    echo '<div class="tpgb-post-comment tpgb-trans-linear tpgb-block-'.esc_attr($block_id ).' '.esc_attr($blockClass).'" >';
		echo '<div id="comments" class="comments-area">';
			if(get_comments_number($post_id) > 0) {
				echo '<ul class="comment-list">';
					echo '<li>';
						echo '<div class="comment-section-title">'.wp_kses_post($commentTitle).' ('. esc_html(get_comments_number($post_id)) . ')</div>';
					echo '<li>'; 
					wp_list_comments($list_args, $comment);
				echo "</ul>";
			}
			comment_form($comment_args, $post_id);
		echo "</div>";
	echo '</div>';

	$output .= ob_get_clean();
	$output = Tpgb_Blocks_Global_Options::block_Wrap_Render($attr, $output);
	
    return $output;
}

/**
 * Render for the server-side
 */
function tpgb_post_comment_content() {
    
    if(method_exists('Tpgb_Blocks_Global_Options', 'merge_options_json')){
		$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_post_comment_render_callback');
		register_block_type( $block_data['name'], $block_data );
	}
}
add_action( 'init', 'tpgb_post_comment_content' );

function tpgb_comment_args( $attr = []){
	$commentFormTitle = (!empty($attr) && !empty($attr['commentFormTitle'])) ? $attr['commentFormTitle'] : '';
	$loggedInAsText = (!empty($attr) && !empty($attr['loggedInAsText'])) ? $attr['loggedInAsText'] : '';
	$logOutText = (!empty($attr) && !empty($attr['logOutText'])) ? $attr['logOutText'] : '';
	$cancelReplyText = (!empty($attr) && !empty($attr['cancelReplyText'])) ? $attr['cancelReplyText'] : '';
	$submitBtnText = (!empty($attr) && !empty($attr['submitBtnText'])) ? $attr['submitBtnText'] : '';
	$commentField = (!empty($attr) && !empty($attr['commentField'])) ? $attr['commentField'] : '';
	$user          = wp_get_current_user();
	$user_identity = $user->exists() ? $user->display_name : '';
	$args = array(
	  'id_form'           => 'commentform',
	  'class_form' => 'comment-form',
	  'id_submit'         => 'submit',
	  'title_reply'       => wp_kses_post($commentFormTitle),
	  'title_reply_to'    => wp_kses_post($commentFormTitle),
	  'cancel_reply_link' => wp_kses_post($cancelReplyText),
	  'label_submit'      => wp_kses_post($submitBtnText),

	  'comment_field' =>  '<div class="tpgb-row"><div class="tpgb-col-md-12 tpgb-col"><label><textarea id="comment" name="comment" cols="45" rows="6" placeholder="'.wp_kses_post($commentField).'" aria-required="true"></textarea></label></div></div>',

	  
	  'must_log_in' => '<p class="must-log-in">' .
		sprintf(
            /* translators: You must be %1$slogged in%2$s to post a comment */
            esc_html__( 'You must be %1$slogged in%2$s to post a comment.', 'the-plus-addons-for-block-editor'),
            '<a href="'.wp_login_url( apply_filters( "the_permalink", get_permalink() ) ).'">',
            '</a>'
		) . '</p>',
		
	  'logged_in_as' => '<p class="logged-in-as">' .
		sprintf(
            /* translators: %1$s%2$s. %3$s%4$s%5$s */
			wp_kses_post($loggedInAsText).esc_html__( ' %1$s%2$s. %3$s%4$s%5$s', 'the-plus-addons-for-block-editor'),
		  '<a href="'.admin_url( "profile.php" ).'">'.$user_identity,
		  '</a>',
		  '<a href="'.wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) ).'" title="'.wp_kses_post($logOutText).'">',
		  wp_kses_post($logOutText),
		  '</a>'
		) . '</p>',

	  'comment_notes_before' => '',

	  'comment_notes_after' => '',

	);
	return $args;
}

function tpgb_move_comment_field_to_bottom( $fields ) {
	$comment_field = $fields['comment'];
	unset( $fields['comment'] );
	$fields['comment'] = $comment_field;
	return $fields;
} 
add_filter( 'comment_form_fields', 'tpgb_move_comment_field_to_bottom' );

function tpgb_comment_form_field( $fields ){

	$commenter = wp_get_current_commenter();
	$fields['author'] ='<div class="tpgb-col"><label>' .
		  '<input id="author" name="author" type="text" placeholder="'.esc_attr__('Name','the-plus-addons-for-block-editor').'" value="' . esc_attr( $commenter['comment_author'] ) .
		  '" size="30" /></label></div>';
	
	$fields['email'] ='<div class="tpgb-md-pl15 tpgb-col"><label>' .
		  '<input id="email" name="email" type="text" placeholder="'.esc_attr__('Email Address *','the-plus-addons-for-block-editor').'" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" /></label></div>';
	
	$fields['url'] ='<div class="tpgb-md-pl15 tpgb-col"><label>' .
		  '<input id="url" name="url" type="text" placeholder="'.esc_attr__('Website','the-plus-addons-for-block-editor').'" value="' . esc_attr( $commenter['comment_author_url'] ) .
		  '" size="30" /></label></div>';
	return $fields;
}
add_filter( 'comment_form_default_fields', 'tpgb_comment_form_field',11 );

function tpgb_comment_before_fields(){
	echo '<div class="tpgb-row">';
}
add_action( 'comment_form_before_fields', 'tpgb_comment_before_fields' );
	
function tpgb_comment_after_fields(){
	echo '</div>';
}
add_action( 'comment_form_after_fields', 'tpgb_comment_after_fields' );

//remove comment cookies field form
remove_action( 'set_comment_cookies', 'wp_set_comment_cookies' );