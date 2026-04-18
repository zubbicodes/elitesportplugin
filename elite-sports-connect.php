<?php
/**
 * Plugin Name:       Elite Sports Connect
 * Plugin URI:        https://github.com/elite-sports-connect
 * Description:       A premium platform connecting elite coaches and athletes. Features coach registration, student lead capture, a beautiful public directory, and full Elementor widget support.
 * Version:           1.0.3
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Stratonally Dev Team
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       elite-sports-connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Constants ───────────────────────────────────────────────────────────────
define( 'ESC_VERSION',     '1.0.3' );
define( 'ESC_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'ESC_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'ESC_PLUGIN_FILE', __FILE__ );

// ─── Autoload includes ───────────────────────────────────────────────────────
require_once ESC_PLUGIN_DIR . 'includes/class-esc-cpt.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-forms.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-shortcodes.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-emails.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-settings.php';

// ─── Enqueue Assets ───────────────────────────────────────────────────────────────
function esc_enqueue_assets() {
	wp_enqueue_style(
		'esc-style',
		ESC_PLUGIN_URL . 'assets/css/esc-style.css',
		[],
		ESC_VERSION
	);
	wp_enqueue_script(
		'esc-scripts',
		ESC_PLUGIN_URL . 'assets/js/esc-scripts.js',
		[ 'jquery' ],
		ESC_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'esc_enqueue_assets' );

function esc_enqueue_admin_assets( $hook ) {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, [ 'coach', 'student_lead' ], true ) ) {
		return;
	}
	wp_enqueue_style(
		'esc-admin-style',
		ESC_PLUGIN_URL . 'assets/css/esc-style.css',
		[],
		ESC_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'esc_enqueue_admin_assets' );

// ─── Bootstrap ───────────────────────────────────────────────────────────────
function esc_bootstrap(): void {
	static $bootstrapped = false;

	if ( $bootstrapped ) {
		return;
	}

	$bootstrapped = true;

	new ESC_CPT();
	new ESC_Forms();
	new ESC_Shortcodes();
	new ESC_Emails();
	new ESC_Settings();

	// Elementor widgets - only if Elementor is active.
	if ( did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) ) {
		require_once ESC_PLUGIN_DIR . 'includes/class-esc-elementor.php';
		new ESC_Elementor();
	}
}
add_action( 'plugins_loaded', 'esc_bootstrap' );

// ─── Activation / Deactivation ───────────────────────────────────────────────
register_activation_hook( __FILE__, 'esc_activate' );
function esc_activate() {
	ESC_CPT::register_post_types();
	ESC_Forms::register_defaults();
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'esc_deactivate' );
function esc_deactivate() {
	flush_rewrite_rules();
}
