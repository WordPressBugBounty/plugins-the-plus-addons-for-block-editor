<?php
/* Block : Tp Image
 * @since : 2.0.0
 */
defined( 'ABSPATH' ) || exit;

function tpgb_tp_image_callback($attr, $content) {
    $pattern = '/\btpgb-wrap-/';
    
    if (preg_match($pattern, $content)) {
        // Check Display Condition
        if( class_exists('Tpgb_Blocks_Global_Options') ){
            $global_blocks = Tpgb_Blocks_Global_Options::get_instance();
            $content = $global_blocks::block_row_conditional_render($attr,$content);
        }
        return $content;
    }
	return Tpgb_Blocks_Global_Options::block_Wrap_Render($attr, $content);
}

function tpgb_tp_image_render() {
    $block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, 'tpgb_tp_image_callback');
	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_image_render' );