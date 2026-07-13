<?php
/**
 * Ability: Verify that the Nexter blocks on a page actually carry content.
 *
 * Nexter blocks (tpgb/*) are dynamic blocks whose visible output is rendered
 * from their SAVED block HTML — not rebuilt from attributes alone. If a page is
 * built or rewritten by a generic page-builder tool (assemble-page, patch-tree,
 * html-to-builder, write-builder-content, place-widget, …) the blocks get
 * serialized as attribute-only, self-closing comments with no inner HTML, and
 * every Nexter block then renders BLANK on the front end.
 *
 * This ability re-parses a post's stored block tree and reports any Nexter
 * block that is "blank" — has neither inner HTML nor inner blocks — so the MCP
 * client can catch a stripped page immediately (and rebuild the offending
 * blocks with the nexter-blocks/add-tpgb-* abilities) instead of shipping an
 * empty page. Call it after every Nexter build as the final check.
 *
 * @package The_Plus_Addons_For_Block_Editor
 * @since   1.3.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_register_ability(
	'nexter-blocks/verify-page',
	array(
		'label'               => __( 'Verify Nexter Page', 'the-plus-addons-for-block-editor' ),
		'description'         => __( 'Re-parses a post\'s stored block tree and reports whether every Nexter block (tpgb/*) still carries content. Nexter blocks render from their SAVED block HTML, so a block that was written attribute-only (self-closing, no inner HTML and no inner blocks) renders BLANK — this happens when a page is built or rewritten with a generic page-builder tool instead of the nexter-blocks/add-tpgb-* abilities. Run this after any Nexter build: if "ok" is false, the listed blank_blocks were stripped of their content and must be rebuilt with nexter-blocks/add-tpgb-* (never with assemble-page / patch-tree / html-to-builder / write-builder-content / place-widget).', 'the-plus-addons-for-block-editor' ),
		'category'            => 'nexter-blocks',

		'input_schema'        => array(
			'type'                 => 'object',
			'properties'           => array(
				'post_id' => array(
					'type'        => 'integer',
					'description' => 'WordPress page/post ID to verify.',
				),
			),
			'required'             => array( 'post_id' ),
			'additionalProperties' => false,
		),

		'output_schema'       => array(
			'type'       => 'object',
			'properties' => array(
				'ok'                => array(
					'type'        => 'boolean',
					'description' => 'True when every Nexter block on the page carries content (no blank blocks).',
				),
				'post_id'           => array( 'type' => 'integer' ),
				'nexter_block_count' => array(
					'type'        => 'integer',
					'description' => 'Total number of tpgb/* blocks found in the post.',
				),
				'blank_blocks'      => array(
					'type'        => 'array',
					'description' => 'Nexter blocks with no inner HTML and no inner blocks — these render blank and must be rebuilt with nexter-blocks/add-tpgb-*.',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'block_id'   => array( 'type' => 'string' ),
							'block_name' => array( 'type' => 'string' ),
							'depth'      => array( 'type' => 'integer' ),
						),
					),
				),
				'message'           => array(
					'type'        => 'string',
					'description' => 'Human-readable summary of the verification result.',
				),
			),
		),

		'execute_callback'    => 'tpgb_mcp_verify_page_ability',
		'permission_callback' => 'tpgb_mcp_verify_page_permission',
		'meta'                => array(
			'show_in_rest' => true,
			'mcp'          => array(
				'public' => true,
				'type'   => 'tool',
			),
		),
	)
);

/**
 * Permission callback for the verify-page ability.
 *
 * @param array|null $input Ability input arguments.
 * @return bool True when the current user may read the target post.
 */
function tpgb_mcp_verify_page_permission( ?array $input = null ): bool {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return false;
	}
	$post_id = absint( $input['post_id'] ?? 0 );
	if ( $post_id > 0 && ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	return true;
}

/**
 * Recursively collect Nexter blocks that render blank.
 *
 * A tpgb/* block is treated as blank when it has neither inner HTML nor inner
 * blocks — i.e. it was serialized attribute-only (self-closing). Such a block
 * has no saved markup for its render callback to emit, so it disappears on the
 * front end. Containers with children are never flagged (their children ARE
 * their content); a container with no children is flagged because an empty
 * container is almost always an accident of a stripped build.
 *
 * @param array $blocks Parsed block tree.
 * @param int   $depth  Current nesting depth (0 = top level).
 * @param array $blank  Accumulator (passed by reference).
 * @param int   $count  Nexter block counter (passed by reference).
 * @return void
 */
function tpgb_mcp_verify_collect_blank( array $blocks, int $depth, array &$blank, int &$count ): void {
	foreach ( $blocks as $b ) {
		$name = $b['blockName'] ?? '';
		if ( ! is_string( $name ) || 0 !== strpos( $name, 'tpgb/' ) ) {
			// Not a Nexter block — still descend, a Nexter block may be nested inside a core wrapper.
			if ( ! empty( $b['innerBlocks'] ) ) {
				tpgb_mcp_verify_collect_blank( $b['innerBlocks'], $depth + 1, $blank, $count );
			}
			continue;
		}

		++$count;

		$has_inner_html   = isset( $b['innerHTML'] ) && '' !== trim( (string) $b['innerHTML'] );
		$has_inner_blocks = ! empty( $b['innerBlocks'] );

		if ( ! $has_inner_html && ! $has_inner_blocks ) {
			$blank[] = array(
				'block_id'   => isset( $b['attrs']['block_id'] ) ? (string) $b['attrs']['block_id'] : '',
				'block_name' => $name,
				'depth'      => $depth,
			);
		}

		if ( $has_inner_blocks ) {
			tpgb_mcp_verify_collect_blank( $b['innerBlocks'], $depth + 1, $blank, $count );
		}
	}
}

/**
 * Execute callback: verify the Nexter block tree of a post.
 *
 * @param array $input Ability input arguments.
 * @return array|WP_Error Verification result or error on failure.
 */
function tpgb_mcp_verify_page_ability( array $input ) {
	$post_id = absint( $input['post_id'] ?? 0 );
	if ( $post_id <= 0 ) {
		return new WP_Error( 'missing_params', __( 'post_id is required.', 'the-plus-addons-for-block-editor' ) );
	}

	$blocks = tpgb_mcp_get_blocks( $post_id );
	if ( is_wp_error( $blocks ) ) {
		return $blocks;
	}

	$blank = array();
	$count = 0;
	tpgb_mcp_verify_collect_blank( $blocks, 0, $blank, $count );

	$ok = ( array() === $blank );

	if ( 0 === $count ) {
		$message = __( 'No Nexter blocks were found on this post.', 'the-plus-addons-for-block-editor' );
	} elseif ( $ok ) {
		$message = sprintf(
			/* translators: %d: number of Nexter blocks checked. */
			__( 'All %d Nexter block(s) carry content — nothing renders blank.', 'the-plus-addons-for-block-editor' ),
			$count
		);
	} else {
		$message = sprintf(
			/* translators: 1: number of blank blocks, 2: total Nexter blocks. */
			__( '%1$d of %2$d Nexter block(s) render BLANK — they were saved with no inner HTML (typically by a generic page-builder tool). Rebuild each listed block with its nexter-blocks/add-tpgb-* ability; do not use assemble-page / patch-tree / html-to-builder / write-builder-content / place-widget on Nexter pages.', 'the-plus-addons-for-block-editor' ),
			count( $blank ),
			$count
		);
	}

	return array(
		'ok'                 => $ok,
		'post_id'            => $post_id,
		'nexter_block_count' => $count,
		'blank_blocks'       => $blank,
		'message'            => $message,
	);
}
