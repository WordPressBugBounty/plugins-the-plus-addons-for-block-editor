<?php
/**
 * Runs when Nexter Blocks is DELETED (not merely deactivated).
 *
 * Its job is to make sure nothing about the analytics arrangement outlives the plugin. There was no
 * uninstall handling here at all, so the sharing consent, the notice's answer, its snooze and the
 * post-install quiet period all stayed in wp_options — a reinstall picked straight back up where it
 * left off and never asked again. Removing the plugin is a withdrawal, so a reinstall has to begin
 * from an unanswered state.
 *
 * Deliberately narrow: this clears analytics/consent state and the cron it created. Nexter Blocks'
 * own settings — enabled blocks, styling data, white label, templates — are left alone, so deleting
 * and reinstalling does not wipe someone's configuration.
 *
 * @package NexterBlocks
 */

// WordPress defines this when it invokes an uninstall script. Nothing else may run this file.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Is another product that shares this consent still installed?
 *
 * The opt-in option, the notice's answer, its snooze, the quiet-period start and the onboarding flag
 * are shared across the POSIMYTH family under the `nexter_suite` key. Deleting them while a sibling
 * is still present would silently reset that sibling's consent and re-prompt a user who had already
 * decided, so those are only removed once this is the last participating product on the site.
 *
 * Checks the file rather than whether the sibling is ACTIVE, on purpose: an inactive sibling can be
 * switched back on, and it would come back to a consent state that was answered and then wiped.
 *
 * Function name is prefixed for this plugin because a bulk delete runs each plugin's uninstall.php in
 * the same PHP process — Nexter Extension declares its own equivalent, and a shared name would fatal
 * on redeclaration.
 *
 * @return bool
 */
function tpgb_posimyth_suite_sibling_present() {
	$tpgb_siblings = array(
		'nexter-extension/nexter-extension.php',
		'the-plus-addons-for-elementor-page-builder/theplus_elementor_addon.php',
	);

	foreach ( $tpgb_siblings as $tpgb_sibling ) {
		if ( file_exists( WP_PLUGIN_DIR . '/' . $tpgb_sibling ) ) {
			return true;
		}
	}

	return false;
}

$tpgb_sdk_base = __DIR__ . '/includes/posimyth-sdk/class-posimyth-tracker-base.php';
$tpgb_tracker  = __DIR__ . '/includes/posimyth-sdk/class-posimyth-tracker-tpgb.php';

if ( file_exists( $tpgb_sdk_base ) && file_exists( $tpgb_tracker ) ) {
	require_once $tpgb_sdk_base;
	require_once $tpgb_tracker;
}

// method_exists too, not only class_exists: active siblings load before uninstall.php runs, so an
// OLDER sibling may already have defined Posimyth_Tracker_Base without purge_state() — our subclass
// then extends that copy, and calling the missing method would fatal mid-uninstall.
if ( class_exists( 'Posimyth_Tracker_TPGB' ) && method_exists( 'Posimyth_Tracker_TPGB', 'purge_state' ) ) {
	Posimyth_Tracker_TPGB::purge_state( ! tpgb_posimyth_suite_sibling_present(), 'nexter_suite' );
} else {
	// Fall back to clearing by name, so a broken or partial install still cleans up after itself.
	wp_clear_scheduled_hook( 'posimyth_heartbeat_tpgb' );

	delete_option( 'posimyth_tpgb_install_time' );
	delete_option( 'posimyth_tpgb_usage' );
	delete_option( 'posimyth_tpgb_first_use_at' );
	delete_option( 'posimyth_tpgb_activate_reported' );
	delete_transient( 'posimyth_tpgb_deact_reported' );

	if ( ! tpgb_posimyth_suite_sibling_present() ) {
		// Site options first (that is how they are written), then the legacy per-blog shape.
		delete_site_option( 'posimyth_nexter_share_analytics' );
		delete_site_option( 'posi_consent_dismissed_nexter_suite' );
		delete_site_option( 'posi_consent_snoozed_until_nexter_suite' );
		// Start of the notice's post-install quiet period. Left behind, a reinstall inherits an expired
		// window and is asked on the first admin page load instead of getting the quiet days a fresh
		// install is meant to get.
		delete_site_option( 'posi_consent_grace_start_nexter_suite' );
		delete_option( 'posimyth_nexter_share_analytics' );
		delete_option( 'posi_consent_dismissed_nexter_suite' );
		delete_option( 'posi_consent_snoozed_until_nexter_suite' );
		delete_option( 'posi_consent_grace_start_nexter_suite' );
		delete_option( 'nxt_onboarding_done' );
	}
}
