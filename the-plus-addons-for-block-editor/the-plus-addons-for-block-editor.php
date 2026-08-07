<?php
/**
 * Plugin Name: Nexter Blocks
 * Plugin URI: https://nexterwp.com/nexter-blocks/
 * Description: Highly customizable WordPress Gutenberg blocks to build professional websites with top-notch performance and sleek design. Includes 40+ FREE WordPress Blocks.
 * Version: 5.0.3
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

defined( 'TPGB_VERSION' ) || define( 'TPGB_VERSION', '5.0.3' );
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
