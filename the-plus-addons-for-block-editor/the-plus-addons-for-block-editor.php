<?php
/**
 * Plugin Name: Nexter Blocks
 * Plugin URI: https://nexterwp.com/nexter-blocks/
 * Description: Highly customizable WordPress Gutenberg blocks to build professional websites with top-notch performance and sleek design. Includes 40+ FREE WordPress Blocks.
 * Version: 5.0.4
 * Author: POSIMYTH
 * Author URI: https://posimyth.com
 * Tested up to: 7.0
 * Text Domain: the-plus-addons-for-block-editor
 * Domain Path: /languages
 * License: GPLv3
 * License URI: https://opensource.org/licenses/GPL-3.0
 *
 * @package ThePluginAddonsForBlockEditor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

defined( 'TPGB_VERSION' ) || define( 'TPGB_VERSION', '5.0.4' );
define( 'TPGB_FILE__', __FILE__ );

define( 'TPGB_PATH', plugin_dir_path( __FILE__ ) );
define( 'TPGB_BASENAME', plugin_basename( __FILE__ ) );
define( 'TPGB_BDNAME', basename( __DIR__ ) );
define( 'TPGB_URL', plugins_url( '/', __FILE__ ) );
define( 'TPGB_ASSETS_URL', TPGB_URL );
define( 'TPGB_INCLUDES_URL', TPGB_PATH . 'includes/' );
define( 'TPGB_CATEGORY', 'tpgb' );
define( 'TPGB_ADMIN_NOTICE_FALG', 3 );

if ( ! version_compare( PHP_VERSION, '5.6.40', '>=' ) ) {
	add_action( 'admin_notices', 'tpgb_check_php_version' );
} elseif ( ! version_compare( get_bloginfo( 'version' ), '4.7.1', '>=' ) ) {
	add_action( 'admin_notices', 'tpgb_check_wp_version' );
} else {
	if ( defined( 'TPGBP_VERSION' ) && ! version_compare( TPGBP_VERSION, '4.0.0', '>=' ) ) {
		add_action( 'admin_notices', 'tpgb_free_check_tpag_version' );
	}
	require_once 'plus-block-loader.php';
}

/**
 * Nexter Blocks check minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @since 1.0.0
 *
 * @return void The result.
 */
function tpgb_check_php_version() {
	/* translators: Nexter Blocks requires PHP version %s+. The plugin is currently not running. Please update to the latest PHP version. */
	$check_message   = sprintf( esc_html__( 'Nexter Blocks requires PHP version %s+. The plugin is currently not running. Please update to the latest PHP version.', 'the-plus-addons-for-block-editor' ), '5.6.40' ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
	$display_message = sprintf( '<div class="error">%s</div>', wpautop( $check_message ) );
	echo wp_kses_post( $display_message );
}

/**
 * Nexter Blocks check minimum WordPress version.
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @since 1.0.0
 *
 * @return void The result.
 */
function tpgb_check_wp_version() {
	/* translators: Nexter Blocks requires at least WordPress version %s+. Because you’re using an older version, the plugin is currently not running. Please update WordPress to the latest version. */
	$check_message   = sprintf( esc_html__( 'Nexter Blocks requires at least WordPress version %s+. Because you’re using an older version, the plugin is currently not running. Please update WordPress to the latest version.', 'the-plus-addons-for-block-editor' ), '4.7.1' );
	$display_message = sprintf( '<div class="error">%s</div>', wpautop( $check_message ) );
	echo wp_kses_post( $display_message );
}

/**
 * Nexter Blocks Pro check minimum version 4.0.0.
 *
 * Warning when the site doesn't have the minimum required Nexter Blocks version.
 *
 * @since 4.0.2
 *
 * @return void The result.
 */
function tpgb_free_check_tpag_version() {
	/** translators: Nexter Blocks Pro requires Nexter Blocks Free version %s+. Since you’re using an older version, the plugin is currently not active. */ // phpcs:ignore Generic.Commenting.DocComment.ShortNotCapital,Generic.Commenting.DocComment.LongNotCapital,Generic.Commenting.DocComment.MissingShort
	$check_message = sprintf( '<b>Note:</b>' . esc_html__( ' Please update the Pro version to at least V4.0.0. If you don’t see the upgrade notice, upload the zip manually to the latest version from the ', 'the-plus-addons-for-block-editor' ) . '<a href="%s">store download.</a>', esc_url( 'https://store.posimyth.com/download/' ) );

	$display_message = sprintf( '<div class="error">%s</div>', wpautop( $check_message ) );

	echo wp_kses_post( $display_message );
}

/*
 * Nexter Blocks Plugin Update Message
 * @since 1.1.3
 */
add_action( 'in_plugin_update_message-the-plus-addons-for-block-editor/the-plus-addons-for-block-editor.php', 'tpgb_plugin_update_message', 10, 2 );
/**
 * Tpgb plugin update message.
 *
 * @param array $data The data.
 * @param mixed $response The response.
 */
function tpgb_plugin_update_message( $data, $response ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	if ( isset( $data['upgrade_notice'] ) && ! empty( $data['upgrade_notice'] ) ) {
		$message = sprintf( '<div class="update-message">%s</div>', wpautop( $data['upgrade_notice'] ) );
		echo wp_kses_post( $message );
	}
}

add_filter(
	'wpml_config_files',
	function ( $config_files ) {
		$config_files[] = TPGB_PATH . '/wpml-config.xml';
		return $config_files; // phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable
	}
);

/*
 * POSIMYTH Analytics SDK — registered, not required (E6). Direct requires behind class_exists
 * guards meant whichever POSIMYTH plugin loaded FIRST supplied the shared classes to everyone, so
 * an outdated sibling could silently downgrade the suite. Each plugin now registers its bundled
 * copy and the loader requires the NEWEST one at plugins_loaded priority 0; the Nexter Blocks
 * subclass loads inside the consumer callback below, after the winner exists.
 */
require_once TPGB_PATH . 'includes/posimyth-sdk/posimyth-sdk-loader.php';
posimyth_sdk_register( TPGB_PATH . 'includes/posimyth-sdk' );

/**
 * White label (Pro): a rebranded install must never surface POSIMYTH-branded UI AND must never phone
 * api.posimyth.com. Reproduces the exact test the removed legacy popup used
 * (Tpgb_Deactive::tpgb_check_white_label) so behaviour does not change for existing white-labelled
 * sites: ANY white-label value that is set and is not the literal 'hidden' marker counts as
 * rebranded — not just brand_name.
 *
 * Must be testable BEFORE the tracker boots. This check used to sit below
 * Posimyth_Tracker_TPGB::init(), so it hid the UI but left the activate / deactivate / heartbeat
 * pings running on a rebranded site whose consent was already ON.
 *
 * @return bool
 */
function tpgb_posimyth_is_white_labelled() {
	// No TPGBP_VERSION gate (A8): the stored white-label values are the evidence, and they outlive
	// Pro. Requiring the Pro constant meant deactivating Pro on a rebranded install brought the
	// POSIMYTH UI back and resumed the pings. A site that never had Pro has nothing stored.
	$tpgb_wl = get_option( 'tpgb_white_label' );
	if ( empty( $tpgb_wl ) || ! is_array( $tpgb_wl ) ) {
		return false;
	}
	foreach ( $tpgb_wl as $tpgb_wl_val ) {
		if ( ! empty( $tpgb_wl_val ) && 'hidden' !== $tpgb_wl_val ) {
			return true;
		}
	}
	return false;
}

add_action(
	'plugins_loaded',
	function () {
		// Nothing below may run on a rebranded install — tracker included.
		if ( tpgb_posimyth_is_white_labelled() ) {
			return;
		}

		// Shared base normally loaded by the SDK loader at priority 0; the subclass is ours alone. But
		// the loader is "first copy wins", so a badly skewed sibling could leave the base unloaded —
		// and our subclass `extends Posimyth_Tracker_Base`, so requiring it without the base present
		// would fatal at parse time. Bail cleanly instead.
		if ( ! class_exists( 'Posimyth_Tracker_Base', false ) ) {
			return;
		}
		require_once TPGB_PATH . 'includes/posimyth-sdk/class-posimyth-tracker-tpgb.php';
		if ( ! class_exists( 'Posimyth_Tracker_TPGB' ) ) {
			return;
		}

		// Registers activate / deactivate / weekly-heartbeat hooks + cron (all consent-gated).
		Posimyth_Tracker_TPGB::init();

		if ( ! is_admin() ) {
			return;
		}

		// Consent notice. suite_key is shared with Nexter Extension so only ONE notice ever renders
		// across the suite and a single Dismiss covers all of them — the user is never asked twice.
		// The opt-in option is shared too, so Allow here also enables Nexter Extension.
		// The constructor registers its own hooks; nothing else needs the instance.
		// The shared SDK is loaded by a "first copy wins" loader, so a version-skewed sibling (e.g. an
		// older Nexter Extension) can define the tracker base yet never load THIS class — which would
		// make the unguarded `new` below a fatal. Fall back to our own bundled copy, then guard the
		// instantiation so a stale sibling degrades the notice gracefully instead of white-screening.
		if ( ! class_exists( 'Posimyth_Consent_Notice' ) && is_readable( TPGB_PATH . 'includes/posimyth-sdk/class-posimyth-consent-notice.php' ) ) {
			require_once TPGB_PATH . 'includes/posimyth-sdk/class-posimyth-consent-notice.php';
		}
		if ( class_exists( 'Posimyth_Consent_Notice' ) ) {
			new Posimyth_Consent_Notice(
				array(
					'plugin_name'      => 'Nexter Blocks',
					'plugin_slug'      => 'the-plus-addons-for-block-editor',
					'opt_in_option'    => 'posimyth_nexter_share_analytics',
					'ajax_action'      => 'posimyth_consent_tpgb',
					'installed_option' => 'posimyth_tpgb_first_use_at',
					'tracker_cb'       => array( 'Posimyth_Tracker_TPGB', 'send_first_ping' ),
					// Campaign is per placement so the docs page can tell which surface sent the reader.
					// Same scheme as Nexter's other admin CTAs.
					'docs_url'         => 'https://nexterwp.com/docs/data-sharing/?utm_source=wpbackend&utm_medium=admin&utm_campaign=datasharingnotice',
					'suite_key'        => 'nexter_suite',
					// Legacy hook only, for host stylesheets carrying a `.nxt-notice-wrap` rule. The SDK
					// stylesheet keys off the stable `posi-*` classes, not this.
					'css_prefix'       => 'nxt',
					// Passed explicitly even though it equals the SDK's neutral default. One copy of the
					// notice class serves every active POSIMYTH plugin, so a product that passes nothing is
					// painted by whatever that copy defaults to. The value travels inline as --posi-accent
					// on this instance's own markup, so it cannot reach a sibling's notice.
					'accent'           => '#1717CC',
				)
			);
		}

		/*
		 * Action trigger for the Dashboard toggle + Onboarding checkbox, mirroring Nexter Extension's.
		 *
		 * Nexter Blocks ships the same dashboard bundle, and that bundle POSTs to this action — but the
		 * action was never registered here, so in Nexter Blocks every request returned 400 and the
		 * consent was silently discarded. The only thing that read the onboarding checkbox was the legacy
		 * tpgb/v2 telemetry call in includes/plus-settings-options.php, which has been removed.
		 */

		/*
		 * This action is SHARED with Nexter Extension — both plugins register a callback on it. If a
		 * callback ends the request with wp_send_json_success() (which calls wp_die()), any sibling
		 * callback registered after it never runs, and that product's activation ping to the hub is
		 * silently dropped whenever consent is toggled with both plugins active.
		 *
		 * So the work is split by priority and NEVER terminates in the side-effect pass:
		 *   - priority 5  : validate, persist the shared option, fire THIS product's ping. No wp_die,
		 *                   so every sibling on the hook still gets to fire its own ping.
		 *   - PHP_INT_MAX : send the single JSON response, guarded so it happens exactly once.
		 * If a sibling (e.g. Nexter Extension) still terminates at the default priority 10, it responds
		 * before the PHP_INT_MAX pass is reached — which is fine: our ping already fired at priority 5.
		 * If Nexter Blocks is the only product, the priority-5 pass fires our ping and the PHP_INT_MAX
		 * pass sends the response.
		 */
		add_action(
			'wp_ajax_nxt_set_share_analytics',
			function () {
				if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'nexter_admin_nonce', 'security', false ) ) {
					wp_send_json_error( array( 'message' => 'unauthorized' ), 403 );
				}

				$tpgb_raw     = isset( $_POST['enabled'] ) ? sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) : '0';
				$tpgb_enabled = ! in_array( $tpgb_raw, array( '', '0', 'false', 'off', 'no' ), true );

				// Site option + autoloaded, matching the notice and has_consent(). Idempotent, so it is
				// harmless if a sibling callback also writes the same shared option in the same request.
				update_site_option( 'posimyth_nexter_share_analytics', $tpgb_enabled ? 1 : 0 );

				// Answering here counts as answering the notice, either way — saying no used to leave the
				// option at 0, which is exactly the state the notice looks for, so it asked again on the
				// next page load.
				if ( class_exists( 'Posimyth_Consent_Notice' ) ) {
					update_site_option( 'posi_consent_dismissed_nexter_suite', 1 );
				}

				if ( $tpgb_enabled && class_exists( 'Posimyth_Tracker_TPGB' ) ) {
					Posimyth_Tracker_TPGB::send_first_ping();
				}
			},
			5
		);

		// Single response for the shared action, sent after every sibling has had its side-effect pass.
		// Guarded so it fires once even though the callback is on a hook that can run more than once.
		add_action(
			'wp_ajax_nxt_set_share_analytics',
			function () {
				static $responded = false;
				if ( $responded ) {
					return;
				}
				$responded = true;

				$tpgb_raw     = isset( $_POST['enabled'] ) ? sanitize_text_field( wp_unslash( $_POST['enabled'] ) ) : '0'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$tpgb_enabled = ! in_array( $tpgb_raw, array( '', '0', 'false', 'off', 'no' ), true );

				wp_send_json_success( array( 'enabled' => $tpgb_enabled ? 1 : 0 ) );
			},
			PHP_INT_MAX
		);

		// No "first successful use" gate any more: the consent notice now shows from activation and
		// keeps asking (snoozed by Dismiss, never silenced) until the user decides — here, in Onboarding,
		// or in Dashboard → Settings. See Posimyth_Consent_Notice::should_show().

		// "Why are you leaving?" survey. Submitting with a reason is itself the consent; "Submit
		// Anonymously" sends the reason only; Skip sends nothing. Its own ajax action + slug so the
		// hub records this churn against Nexter Blocks, not Nexter Extension.
		// Same version-skew guard as the consent notice above: never let a stale sibling's loader turn
		// this instantiation into a fatal.
		if ( ! class_exists( 'Posimyth_Deactivation_Survey' ) && is_readable( TPGB_PATH . 'includes/posimyth-sdk/class-posimyth-deactivation-survey.php' ) ) {
			require_once TPGB_PATH . 'includes/posimyth-sdk/class-posimyth-deactivation-survey.php';
		}
		if ( class_exists( 'Posimyth_Deactivation_Survey' ) ) {
			new Posimyth_Deactivation_Survey(
				array(
					'plugin_name'   => 'Nexter Blocks',
					'plugin_slug'   => 'the-plus-addons-for-block-editor',
					// Matched against the Deactivate link's href, which is locale-proof.
					'plugin_file'   => TPGB_BASENAME,
					'ajax_action'   => 'posimyth_tpgb_deact',
					'opt_in_option' => 'posimyth_nexter_share_analytics',
					'tracker_cb'    => array( 'Posimyth_Tracker_TPGB', 'do_request' ),
					// The reason set, labels and icons Nexter Blocks showed before this SDK existed, taken from
					// classes/extras/tpag-deactive.php (still on disk, no longer included anywhere). Labels keep
					// their original full stops. Slugs are new: the old form had no slugs at all, and the hub
					// needs one to group by.
					//
					// No 'accent' key: those icons are already #1717CC and that is both Nexter's brand and this
					// SDK's default, so setting it would only restate the default.
					//
					// A closure, not a literal array: this config is built at plugins_loaded, and calling __()
					// there trips WP 6.7's "translation loading was triggered too early" notice.
					'reasons'       => function () {
						return array(
							'just-debugging'       => array(
								'label' => esc_html__( 'Just Debugging.', 'the-plus-addons-for-block-editor' ),
								'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"><g stroke="#1717CC" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.667" clip-path="url(#a)"><path d="M10 18.333a8.333 8.333 0 1 0 0-16.666 8.333 8.333 0 0 0 0 16.666ZM8.333 12.5v-5M11.667 12.5v-5"/></g><defs><clipPath id="a"><path fill="#fff" d="M0 0h20v20H0z"/></clipPath></defs></svg>',
							),
							'plugin-issues'        => array(
								'label' => esc_html__( 'Plugin Issue.', 'the-plus-addons-for-block-editor' ),
								'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"><path fill="#1717CC" d="M10.179 2.771a3.601 3.601 0 0 1 3.42 3.596l.113.007a.9.9 0 0 1 .273.08l2.73-1.745.08-.046a.9.9 0 0 1 .89 1.562L14.97 7.961c.244.623.391 1.283.428 1.956l.002.05h2.7l.092.004a.9.9 0 0 1 0 1.791l-.092.005h-2.7v.9l-.006.268a5.405 5.405 0 0 1-.172 1.103l2.44 1.457.076.05a.9.9 0 0 1-.918 1.537l-.082-.042-2.264-1.353a5.402 5.402 0 0 1-8.95.001L3.261 17.04l-.461-.773-.462-.772 2.44-1.457a5.403 5.403 0 0 1-.178-1.372v-.899H1.9a.901.901 0 0 1 0-1.8h2.7v-.05l.038-.42a6.301 6.301 0 0 1 .391-1.536L2.314 6.225l-.075-.054a.9.9 0 0 1 1.045-1.463l2.73 1.747a.9.9 0 0 1 .274-.081l.111-.007A3.602 3.602 0 0 1 10 2.767l.179.004ZM3.26 17.04a.9.9 0 0 1-.923-1.545l.923 1.545Zm3.652-8.873a4.499 4.499 0 0 0-.514 1.837v2.662a3.602 3.602 0 0 0 2.7 3.486v-4.385a.9.9 0 0 1 1.8 0v4.385a3.602 3.602 0 0 0 2.697-3.307l.004-.179V9.995a4.496 4.496 0 0 0-.514-1.829H6.913ZM10 4.566a1.802 1.802 0 0 0-1.8 1.8h3.6l-.009-.178a1.8 1.8 0 0 0-1.613-1.613L10 4.566Z"/></svg>',
							),
							'slow-performance'     => array(
								'label' => esc_html__( 'Slow Performance.', 'the-plus-addons-for-block-editor' ),
								'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"><path fill="#1717CC" d="M2.8 10.931c0 1.99.806 3.79 2.109 5.091l-1.272 1.272A8.972 8.972 0 0 1 1 10.931a9 9 0 0 1 9-9 9 9 0 0 1 6.364 15.364l-1.273-1.273A7.2 7.2 0 1 0 2.8 10.932Zm4.236-4.236 4.05 4.05-1.272 1.272-4.05-4.05 1.272-1.272Z"/></svg>',
							),
							'switched-alternative' => array(
								'label' => esc_html__( 'Switched to Alternative.', 'the-plus-addons-for-block-editor' ),
								'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"><path fill="#1717CC" d="M5.532 9.195a.809.809 0 0 1 0 1.61l-.083.003H3.252a5.58 5.58 0 0 0 6.222 2.772l.352-.097a5.562 5.562 0 0 0 3.681-3.716.81.81 0 0 1 1.55.465 7.181 7.181 0 0 1-1.265 2.415l4.97 4.972.056.061a.81.81 0 0 1-1.137 1.14l-.062-.056-4.972-4.973a7.183 7.183 0 0 1-2.794 1.361v.001a7.199 7.199 0 0 1-7.236-2.406v.893a.808.808 0 1 1-1.617 0V10l.004-.083a.809.809 0 0 1 .805-.726h3.64l.083.004ZM6.506 1.2a7.199 7.199 0 0 1 7.235 2.406V2.72a.81.81 0 0 1 1.619 0v3.64a.81.81 0 0 1-.81.809h-3.64a.81.81 0 0 1 0-1.617h2.201a5.583 5.583 0 0 0-6.226-2.78h-.002a5.565 5.565 0 0 0-3.919 3.474l-.115.346a.81.81 0 0 1-1.551-.463l.071-.225a7.18 7.18 0 0 1 5.137-4.705v.001Z"/></svg>',
							),
							'no-longer-needed'     => array(
								'label' => esc_html__( 'No Longer Needed.', 'the-plus-addons-for-block-editor' ),
								'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"><path fill="#1717CC" d="M16.566 1.914a2.7 2.7 0 0 1 1.643 4.595c-.287.287-.633.5-1.009.633v8.259a2.701 2.701 0 0 1-2.7 2.7h-9a2.704 2.704 0 0 1-2.688-2.433L2.8 15.4V7.143a2.7 2.7 0 0 1-1.01-.634 2.701 2.701 0 0 1-.777-1.641L.999 4.6a2.702 2.702 0 0 1 2.7-2.7h12.6l.267.014ZM4.6 15.4l.004.089a.903.903 0 0 0 .896.811h9a.903.903 0 0 0 .9-.9V7.3H4.6v8.1Zm7.292-6.296a.9.9 0 0 1 0 1.791l-.092.005H8.2a.9.9 0 0 1 0-1.8h3.6l.092.004ZM3.699 3.701a.9.9 0 0 0-.9.9l.005.088a.902.902 0 0 0 .895.811h12.6l.09-.004A.901.901 0 0 0 17.2 4.6a.9.9 0 0 0-.811-.895l-.09-.004H3.7Z"/></svg>',
							),
							'compatibility-issues' => array(
								'label' => esc_html__( 'Compatibility Issue.', 'the-plus-addons-for-block-editor' ),
								'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"><path fill="#1717CC" fill-rule="evenodd" d="M19 10a9 9 0 0 1-9 9 9 9 0 0 1-9-9 9 9 0 0 1 9-9 9 9 0 0 1 9 9Zm-9 7.2a7.2 7.2 0 1 0 0-14.4 7.2 7.2 0 0 0 0 14.4Z" clip-rule="evenodd"/><path fill="#1717CC" fill-rule="evenodd" d="M16.036 4.414a.9.9 0 0 1 0 1.272l-10.35 10.35a.9.9 0 0 1-1.272-1.272l10.35-10.35a.9.9 0 0 1 1.272 0Z" clip-rule="evenodd"/></svg>',
							),
							'missing-feature'      => array(
								'label' => esc_html__( 'Missing Feature.', 'the-plus-addons-for-block-editor' ),
								'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"><path fill="#1717CC" d="M17.363 10a1.154 1.154 0 0 0-.263-.734l-.075-.084-1.377-1.376a1.636 1.636 0 0 1 .774-2.749l.157-.048a1.23 1.23 0 0 0 .408-.26l.11-.12a1.23 1.23 0 0 0-.093-1.633 1.228 1.228 0 0 0-2.012.426l-.049.155a1.638 1.638 0 0 1-2.585.919l-.164-.143-1.376-1.377a1.157 1.157 0 0 0-1.551-.077l-.085.077-1.378 1.376h.001l.184.05A2.864 2.864 0 1 1 4.404 7.99l-.051-.184-1.378 1.377a1.158 1.158 0 0 0-.338.818l.006.114a1.157 1.157 0 0 0 .331.703h.001l1.377 1.377.144.163a1.636 1.636 0 0 1-.92 2.585h.001a1.228 1.228 0 0 0-.024 2.381 1.228 1.228 0 0 0 1.504-.9 1.637 1.637 0 0 1 2.748-.775l1.377 1.376.085.077a1.16 1.16 0 0 0 .733.262l.113-.005a1.16 1.16 0 0 0 .705-.334l1.377-1.376a2.864 2.864 0 1 1 3.401-3.637l.05.183v.001h.002l1.377-1.377.075-.084a1.156 1.156 0 0 0 .263-.734ZM19 10a2.793 2.793 0 0 1-.634 1.771l-.185.204-1.377 1.375.001.001a1.638 1.638 0 0 1-2.75-.775v-.001a1.227 1.227 0 1 0-1.479 1.482l.207.064a1.637 1.637 0 0 1 .712 2.52l-.143.165-1.377 1.375a2.794 2.794 0 0 1-1.7.805l-.275.013a2.793 2.793 0 0 1-1.772-.633l-.203-.184-1.377-1.377v-.001a2.864 2.864 0 1 1-3.636-3.402l.184-.05-1.377-1.376v-.001a2.793 2.793 0 0 1-.805-1.701L1 10a2.793 2.793 0 0 1 .82-1.975l1.376-1.377a1.638 1.638 0 0 1 2.337.023c.202.21.344.47.412.753l.047.155a1.228 1.228 0 0 0 2.326-.776 1.227 1.227 0 0 0-.739-.81l-.155-.05a1.636 1.636 0 0 1-.776-2.748l1.377-1.376.203-.184a2.793 2.793 0 0 1 3.747.184l1.377 1.377.051-.185a2.861 2.861 0 0 1 4.759-1.171 2.864 2.864 0 0 1 .092 3.952l-.134.137a2.863 2.863 0 0 1-1.132.67l-.184.05 1.377 1.376.185.203A2.796 2.796 0 0 1 19 10Z"/></svg>',
							),
							'other'                => array(
								'label' => esc_html__( 'Other Reasons.', 'the-plus-addons-for-block-editor' ),
								'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none"><path fill="#1717CC" d="M10 1a9 9 0 0 1 9 9 9 9 0 0 1-9 9 9 9 0 0 1-9-9 9 9 0 0 1 9-9Zm0 1.8a7.2 7.2 0 1 0 0 14.4 7.2 7.2 0 0 0 0-14.4Zm0 10.8a.9.9 0 1 1 0 1.8.9.9 0 0 1 0-1.8Zm0-8.55a3.263 3.263 0 0 1 1.213 6.291.72.72 0 0 0-.274.18c-.04.046-.046.103-.045.163l.006.116a.9.9 0 0 1-1.794.105L9.1 11.8v-.225c0-1.038.837-1.66 1.444-1.904a1.463 1.463 0 1 0-2.007-1.358.9.9 0 1 1-1.8 0A3.262 3.262 0 0 1 10 5.05Z"/></svg>',
							),
						);
					},
				)
			);
		}
	}
);

/**
 * Analytics activation ping.
 *
 * Must run from the activation hook, NOT the SDK's `activated_plugin` hook: during a plugin's own
 * activation request WordPress fires `plugins_loaded` before it includes this file, so init() never
 * runs and nothing is listening when `activated_plugin` fires. Consent-gated inside
 * on_self_activate(), so a fresh install still sends nothing.
 */
register_activation_hook(
	__FILE__,
	function () {
		// Rebranded installs must not phone home from here either — this runs during our own
		// activation request, before the plugins_loaded gate has had a chance to short-circuit.
		if ( tpgb_posimyth_is_white_labelled() ) {
			return;
		}
		// The plugins_loaded callback never ran in this request, so load the subclass here; the
		// loader already loaded the shared base immediately (did_action branch) on include.
		require_once TPGB_PATH . 'includes/posimyth-sdk/class-posimyth-tracker-tpgb.php';
		if ( class_exists( 'Posimyth_Tracker_TPGB' ) ) {
			Posimyth_Tracker_TPGB::on_self_activate();
		}
	}
);

/**
 * Clear the analytics heartbeat cron on deactivate.
 *
 * Without this, the recurring `posimyth_heartbeat_tpgb` event scheduled by the tracker survives a
 * plain deactivate (delete/uninstall already cleans it via uninstall.php → purge_state()), leaving a
 * dangling weekly cron entry with no callback. Mirrors Nexter Extension's own deactivation cleanup.
 * The subclass is loaded here for the same reason as the activation hook above: plugins_loaded has
 * already fired in the deactivation request, so init() is not what wires this.
 */
register_deactivation_hook(
	__FILE__,
	function () {
		require_once TPGB_PATH . 'includes/posimyth-sdk/class-posimyth-tracker-tpgb.php';
		if ( class_exists( 'Posimyth_Tracker_TPGB' ) && method_exists( 'Posimyth_Tracker_TPGB', 'unschedule' ) ) {
			Posimyth_Tracker_TPGB::unschedule();
		} else {
			wp_clear_scheduled_hook( 'posimyth_heartbeat_tpgb' );
		}
	}
);
