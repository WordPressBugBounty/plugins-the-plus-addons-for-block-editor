<?php
/**
 * Nexter Cookie Consent — dynamic block.
 *
 * Every piece of content that can change without a post re-save (category labels,
 * the Necessary lock, button labels chosen by the editor) is assembled here on every
 * request from Nexter Extension's live settings — never cached into post_content.
 * Nexter Blocks owns none of the consent logic itself (02-DEVELOPER-BRIEF.md PART
 * 1.1): this file only emits markup carrying the data-nexter-* attributes that
 * Extension's window.nexterConsent contract (assets/js/cookie-consent/nexter-consent.js)
 * already knows how to drive — see its "Generic banner surface contract" section.
 *
 * @package ThePluginAddonsForBlockEditor
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the Cookie Consent module is available to render against. Mirrors the
 * class_exists() guard pattern Nexter Extension itself uses for optional modules
 * (CLAUDE.md "Compatibility") — Nexter Blocks must never fatal when Nexter Extension
 * is inactive or its Cookie Consent switch is off.
 *
 * @return bool
 */
function tpgb_tp_cookie_consent_available() {
	// class_exists() alone is not enough: an older Extension may ship the class without
	// CATEGORY_IDS, and on PHP 8 a missing class constant is a fatal Error, not a notice.
	if ( ! class_exists( 'Nxt_Cookie_Consent' )
		|| ! defined( 'Nxt_Cookie_Consent::CATEGORY_IDS' )
		|| ! method_exists( 'Nxt_Cookie_Consent', 'get_settings' ) ) {
		return false;
	}

	$settings = Nxt_Cookie_Consent::get_settings();

	return ! empty( $settings['switch'] );
}

/**
 * Fallback blurbs for the four frozen category ids. The engine stores only a label,
 * so a description typed there would be lost on the next config version bump — these
 * are factual descriptions of what the category gates, never a compliance claim.
 *
 * @param string $id Frozen category id.
 * @return string
 */
function tpgb_tp_cookie_consent_category_desc( $id ) {
	switch ( $id ) {
		case 'necessary':
			return __( 'Required for the site to work. These cannot be switched off.', 'the-plus-addons-for-block-editor' );
		case 'functional':
			return __( 'Remember your preferences, such as language or region.', 'the-plus-addons-for-block-editor' );
		case 'analytics':
			return __( 'Help us understand how the site is used, in aggregate.', 'the-plus-addons-for-block-editor' );
		case 'marketing':
			return __( 'Used to show you advertising that is relevant to you.', 'the-plus-addons-for-block-editor' );
	}

	return '';
}

/**
 * Renders one button. Kept in one place so the three call sites cannot drift on
 * class names — the Compliance Check's reject-prominence item compares computed
 * styles of .nxt-cc-btn-reject against .nxt-cc-btn-accept, so those two classes are
 * a contract with the engine, not decoration.
 *
 * @param string $action data-nexter-action value.
 * @param string $slug   Button slug used in the class name.
 * @param string $label  Visible label.
 * @return string
 */
function tpgb_tp_cookie_consent_button( $action, $slug, $label ) {
	return '<button type="button" class="nxt-cc-btn nxt-cc-btn-' . esc_attr( $slug ) . '" data-nexter-action="' . esc_attr( $action ) . '">' . esc_html( $label ) . '</button>';
}

/**
 * Render callback for tpgb/tp-cookie-consent.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function tpgb_tp_cookie_consent_render( $attributes ) {
	if ( ! tpgb_tp_cookie_consent_available() ) {
		// Editor gets an explanatory placeholder; a real visitor gets nothing rather
		// than a broken half-rendered banner (PART 8 dependency table).
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return '<div class="tpgb-cookie-notice">' .
				esc_html__( 'Cookie Consent is not active in Nexter Extension yet. Enable it under Nexter Extension → Privacy → Cookie Consent.', 'the-plus-addons-for-block-editor' ) .
				'</div>';
		}
		return '';
	}

	// One banner per frontend request: a footer template part plus a page block would stack two.
	// Renders whose output is discarded (meta description in wp_head, excerpts) must neither be
	// guarded nor claim the slot, or the banner that is actually printed is suppressed. The editor
	// preview always renders every instance.
	static $rendered = false;
	$discarded = doing_action( 'wp_head' ) || doing_filter( 'get_the_excerpt' ) || doing_filter( 'wp_trim_excerpt' );
	if ( ! $discarded && ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		if ( $rendered ) {
			return '';
		}
		$rendered = true;
	}

	$settings   = Nxt_Cookie_Consent::get_settings();
	$categories = isset( $settings['categories'] ) && is_array( $settings['categories'] ) ? $settings['categories'] : array();

	// Sanitised because block_id lands in id= and aria-labelledby=. One space in it splits a
	// single ID reference into two that do not exist, leaving the dialog with no accessible name.
	$block_id    = ! empty( $attributes['block_id'] ) ? sanitize_html_class( $attributes['block_id'] ) : '';
	$block_id    = '' !== $block_id ? $block_id : uniqid( 'nxtcc' );
	$block_class = class_exists( 'Tp_Blocks_Helper' ) ? Tp_Blocks_Helper::block_wrapper_classes( $attributes ) : '';

	$preset     = isset( $attributes['preset'] ) ? sanitize_html_class( $attributes['preset'] ) : 'minimal';
	$layout     = isset( $attributes['layout'] ) ? sanitize_html_class( $attributes['layout'] ) : 'bar-bottom';
	$overlay    = ! empty( $attributes['overlay'] );
	$show_cats  = ! isset( $attributes['showCategories'] ) || ! empty( $attributes['showCategories'] );
	$show_rjct  = ! isset( $attributes['showReject'] ) || ! empty( $attributes['showReject'] );
	$show_close = ! empty( $attributes['showCloseIcon'] );
	$show_title = ! empty( $attributes['showTitle'] );
	$stack      = ! empty( $attributes['btnStackMobile'] );
	$show_desc  = ! isset( $attributes['showCategoryDesc'] ) || ! empty( $attributes['showCategoryDesc'] );
	$prefs_all  = ! isset( $attributes['prefsAcceptAll'] ) || ! empty( $attributes['prefsAcceptAll'] );

	// block.json ships English defaults and WordPress fills them in on every render, so a
	// __() fallback would never run. A value still equal to its default is treated as untouched
	// and translated; anything the editor changed is theirs and is left alone.
	$title_text = isset( $attributes['titleText'] ) ? sanitize_text_field( $attributes['titleText'] ) : '';
	if ( 'We value your privacy' === $title_text ) {
		$title_text = __( 'We value your privacy', 'the-plus-addons-for-block-editor' );
	}
	$consent_text = isset( $attributes['consentText'] ) ? wp_kses_post( $attributes['consentText'] ) : '';
	if ( 'We use cookies to improve your experience. Choose which categories you are comfortable with.' === $consent_text ) {
		$consent_text = __( 'We use cookies to improve your experience. Choose which categories you are comfortable with.', 'the-plus-addons-for-block-editor' );
	}

	$show_policy  = ! empty( $attributes['showPolicyLink'] );
	$policy_label = isset( $attributes['policyLinkLabel'] ) ? sanitize_text_field( $attributes['policyLinkLabel'] ) : '';
	if ( 'Privacy Policy' === $policy_label ) {
		$policy_label = __( 'Privacy Policy', 'the-plus-addons-for-block-editor' );
	}
	// Pmgc_Url stores { url, target, nofollow }, not a bare string. The string form is
	// still read so a block saved before this attribute changed shape keeps its link.
	$policy_link  = isset( $attributes['policyLinkUrl'] ) ? $attributes['policyLinkUrl'] : '';
	$policy_raw   = is_array( $policy_link ) ? ( isset( $policy_link['url'] ) ? $policy_link['url'] : '' ) : (string) $policy_link;
	$policy_url   = '' !== $policy_raw ? esc_url_raw( $policy_raw ) : '';
	$policy_blank = is_array( $policy_link ) && ! empty( $policy_link['target'] );
	$policy_nofo  = is_array( $policy_link ) && ! empty( $policy_link['nofollow'] );
	if ( $show_policy && '' === $policy_url ) {
		// An empty URL would render a link to the current page, which reads as broken.
		// Fall back to the site's own privacy page; hide the link when there isn't one.
		$policy_url = (string) get_privacy_policy_url();
	}

	$accept_label    = isset( $attributes['acceptLabel'] ) ? sanitize_text_field( $attributes['acceptLabel'] ) : __( 'Accept All', 'the-plus-addons-for-block-editor' );
	if ( 'Accept All' === $accept_label ) {
		$accept_label = __( 'Accept All', 'the-plus-addons-for-block-editor' );
	}
	$reject_label    = isset( $attributes['rejectLabel'] ) ? sanitize_text_field( $attributes['rejectLabel'] ) : __( 'Reject All', 'the-plus-addons-for-block-editor' );
	if ( 'Reject All' === $reject_label ) {
		$reject_label = __( 'Reject All', 'the-plus-addons-for-block-editor' );
	}
	$customize_label = isset( $attributes['customizeLabel'] ) ? sanitize_text_field( $attributes['customizeLabel'] ) : __( 'Customize', 'the-plus-addons-for-block-editor' );
	if ( 'Customize' === $customize_label ) {
		$customize_label = __( 'Customize', 'the-plus-addons-for-block-editor' );
	}

	$prefs_title  = isset( $attributes['prefsTitle'] ) ? sanitize_text_field( $attributes['prefsTitle'] ) : __( 'Cookie preferences', 'the-plus-addons-for-block-editor' );
	if ( 'Cookie preferences' === $prefs_title ) {
		$prefs_title = __( 'Cookie preferences', 'the-plus-addons-for-block-editor' );
	}
	$prefs_desc   = isset( $attributes['prefsDescription'] ) ? wp_kses_post( $attributes['prefsDescription'] ) : '';
	$prefs_save   = isset( $attributes['prefsSaveLabel'] ) ? sanitize_text_field( $attributes['prefsSaveLabel'] ) : __( 'Save preferences', 'the-plus-addons-for-block-editor' );
	if ( 'Save preferences' === $prefs_save ) {
		$prefs_save = __( 'Save preferences', 'the-plus-addons-for-block-editor' );
	}
	$button_order = isset( $attributes['btnOrder'] ) ? $attributes['btnOrder'] : 'reject-customize-accept';

	// The button order is a display concern only — every order still emits all three
	// buttons, so no order can hide Reject behind a setting.
	$buttons = array();
	if ( $show_rjct ) {
		$buttons['reject'] = tpgb_tp_cookie_consent_button( 'reject', 'reject', $reject_label );
	}
	if ( $show_cats ) {
		$buttons['customize'] = tpgb_tp_cookie_consent_button( 'preferences', 'customize', $customize_label );
	}
	$buttons['accept'] = tpgb_tp_cookie_consent_button( 'accept', 'accept', $accept_label );

	$order = explode( '-', $button_order );
	$html  = '';
	foreach ( $order as $slug ) {
		if ( isset( $buttons[ $slug ] ) ) {
			$html .= $buttons[ $slug ];
			unset( $buttons[ $slug ] );
		}
	}
	$actions_html = $html . implode( '', $buttons );

	$wrap_classes = array(
		'tpgb-cookie-consent',
		'tpgb-relative-block',
		'tpgb-block-' . $block_id,
	);
	if ( ! empty( $block_class ) ) {
		$wrap_classes[] = $block_class;
	}

	$cc_classes = array(
		'nxt-cc',
		'nxt-cc-layout-' . $layout,
		'nxt-cc-preset-' . $preset,
	);
	if ( $stack ) {
		$cc_classes[] = 'nxt-cc-stack-mobile';
	}
	// Drives the reserved track for the close icon. A CSS-only guess is not possible: the
	// icon is optional, and reserving the space unconditionally indents every banner without one.
	if ( $show_close ) {
		$cc_classes[] = 'nxt-cc-has-close';
	}

	// aria-labelledby must point at an element that exists. showTitle alone is not enough:
	// a title that sanitises to empty (a tag-only value, say) is suppressed by the render
	// guard below, and the reference would then name nothing at all — which silences the
	// dialog's accessible name rather than falling back to the text.
	$has_title = $show_title && '' !== $title_text;
	$title_id  = 'nxt-cc-title-' . $block_id;
	$text_id  = 'nxt-cc-text-' . $block_id;
	$prefs_id = 'nxt-cc-prefs-title-' . $block_id;

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $wrap_classes ) ); ?>">
		<div class="<?php echo esc_attr( implode( ' ', $cc_classes ) ); ?>" data-nexter-banner hidden>
			<?php if ( $overlay ) : ?>
				<div class="nxt-cc-overlay"></div>
			<?php endif; ?>
			<div class="nxt-cc-banner" role="dialog" aria-modal="false" aria-labelledby="<?php echo esc_attr( $has_title ? $title_id : $text_id ); ?>">
				<div class="nxt-cc-content">
					<?php if ( $has_title ) : ?>
						<p class="nxt-cc-title" id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $title_text ); ?></p>
					<?php endif; ?>
					<div class="nxt-cc-text" id="<?php echo esc_attr( $text_id ); ?>"><?php echo wp_kses_post( $consent_text ); ?></div>
					<?php if ( $show_policy && '' !== $policy_url && '' !== $policy_label ) : ?>
						<?php
						// noopener is unconditional on a new-tab link: without it the opened page
						// gets window.opener back and can navigate this one away mid-consent.
						$policy_rel = array();
						if ( $policy_blank ) {
							$policy_rel[] = 'noopener';
							$policy_rel[] = 'noreferrer';
						}
						if ( $policy_nofo ) {
							$policy_rel[] = 'nofollow';
						}
						?>
						<a class="nxt-cc-policy-link" href="<?php echo esc_url( $policy_url ); ?>"<?php echo $policy_blank ? ' target="_blank"' : ''; ?><?php echo ! empty( $policy_rel ) ? ' rel="' . esc_attr( implode( ' ', $policy_rel ) ) . '"' : ''; ?>><?php echo esc_html( $policy_label ); ?></a>
					<?php endif; ?>
				</div>
				<div class="nxt-cc-actions">
					<?php echo $actions_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from esc_html()/esc_attr() in tpgb_tp_cookie_consent_button(). ?>
				</div>
				<?php if ( $show_close ) : ?>
					<button type="button" class="nxt-cc-close" data-nexter-action="reject" aria-label="<?php esc_attr_e( 'Reject all and close', 'the-plus-addons-for-block-editor' ); ?>"><svg viewBox="0 0 16 16" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M2 2 14 14M14 2 2 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $show_cats ) : ?>
			<div class="nxt-cc-modal-wrap nxt-cc-preset-<?php echo esc_attr( $preset ); ?>" data-nexter-preferences-modal hidden role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $prefs_id ); ?>">
				<div class="nxt-cc-modal-backdrop" data-nexter-action="close-preferences"></div>
				<div class="nxt-cc-modal">
					<div class="nxt-cc-modal-head">
						<h2 class="nxt-cc-modal-title" id="<?php echo esc_attr( $prefs_id ); ?>"><?php echo esc_html( $prefs_title ); ?></h2>
						<button type="button" class="nxt-cc-modal-close" data-nexter-action="close-preferences" aria-label="<?php esc_attr_e( 'Close', 'the-plus-addons-for-block-editor' ); ?>"><svg viewBox="0 0 16 16" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M2 2 14 14M14 2 2 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
					</div>
					<?php if ( '' !== $prefs_desc ) : ?>
						<div class="nxt-cc-modal-desc"><?php echo wp_kses_post( $prefs_desc ); ?></div>
					<?php endif; ?>
					<ul class="nxt-cc-list">
						<?php foreach ( Nxt_Cookie_Consent::CATEGORY_IDS as $id ) : ?>
							<?php
							$cat    = isset( $categories[ $id ] ) ? $categories[ $id ] : array();
							$label  = isset( $cat['label'] ) ? $cat['label'] : $id;
							$locked = ! empty( $cat['locked'] ) || 'necessary' === $id;
							$desc   = isset( $cat['description'] ) ? $cat['description'] : tpgb_tp_cookie_consent_category_desc( $id );
							?>
							<li class="nxt-cc-item<?php echo $locked ? ' nxt-cc-item-locked' : ''; ?>">
								<div class="nxt-cc-item-main">
									<span class="nxt-cc-item-label"><?php echo esc_html( $label ); ?></span>
									<label class="nxt-cc-switch">
										<input
											type="checkbox"
											data-nexter-category-toggle="<?php echo esc_attr( $id ); ?>"
											<?php checked( $locked ); ?>
											<?php disabled( $locked ); ?>
										/>
										<span class="nxt-cc-switch-track" aria-hidden="true"><span class="nxt-cc-switch-knob"></span></span>
										<span class="screen-reader-text"><?php echo esc_html( $label ); ?></span>
									</label>
								</div>
								<?php if ( $show_desc && '' !== $desc ) : ?>
									<p class="nxt-cc-item-desc"><?php echo esc_html( $desc ); ?></p>
								<?php endif; ?>
								<?php if ( $locked ) : ?>
									<span class="nxt-cc-item-lock"><?php esc_html_e( 'Always active', 'the-plus-addons-for-block-editor' ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
					<div class="nxt-cc-modal-actions">
						<?php if ( $show_rjct ) : ?>
							<?php echo tpgb_tp_cookie_consent_button( 'reject', 'reject', $reject_label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
						<?php endif; ?>
						<?php if ( $prefs_all ) : ?>
							<?php echo tpgb_tp_cookie_consent_button( 'accept', 'accept', $accept_label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
						<?php endif; ?>
						<?php echo tpgb_tp_cookie_consent_button( 'save-preferences', 'save', $prefs_save ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside. ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php
	$output = ob_get_clean();

	if ( class_exists( 'Tpgb_Blocks_Global_Options' ) ) {
		$output = Tpgb_Blocks_Global_Options::block_Wrap_Render( $attributes, $output );
	}

	return $output;
}

/**
 * Registers the block through merge_options_json() so it inherits the same Advanced-tab
 * attribute set (background, position, display rules) every other block in the library
 * gets — registering straight from block.json would leave those attributes undeclared
 * and the Advanced tab's controls would silently write values the CSS generator drops.
 *
 * @return void
 */
function tpgb_tp_cookie_consent_register() {
	if ( ! class_exists( 'Tpgb_Blocks_Global_Options' ) ) {
		return;
	}

	$block_data = Tpgb_Blocks_Global_Options::merge_options_json( __DIR__, 'tpgb_tp_cookie_consent_render', true, false, false );
	if ( empty( $block_data ) || empty( $block_data['name'] ) ) {
		return;
	}

	register_block_type( $block_data['name'], $block_data );
}
add_action( 'init', 'tpgb_tp_cookie_consent_register' );
