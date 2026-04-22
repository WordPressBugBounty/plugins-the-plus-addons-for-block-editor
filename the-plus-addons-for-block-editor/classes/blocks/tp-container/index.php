<?php
/* Block : Container(Section)
 * @since : 1.3.0
 *
 * Registration only. render.php is lazy-loaded by WordPress — included only
 * when tpgb/tp-container is present on the page being rendered.
 *
 * merge_options_json() is still called to inject global attribute schemas
 * (global-option, global-position, global-display-rules, equal-height, etc.)
 * into the registration. The render_callback param is intentionally empty —
 * the "render": "file:./render.php" declaration in block.json owns that.
 */
defined( 'ABSPATH' ) || exit;
 
/**
 * Render for the server-side
 */
function tpgb_tp_container_row() {
 
	$block_data = Tpgb_Blocks_Global_Options::merge_options_json(__DIR__, '' , true, false, false, true);
	register_block_type_from_metadata( __DIR__, array( 'attributes' => $block_data['attributes'] ) );
}
add_action( 'init', 'tpgb_tp_container_row' );