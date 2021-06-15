<?php
/**
 * Plugin Name: MB ACF Migration
 * Plugin URI:  https://metabox.io/plugins/mb-acf-migration/
 * Description: Migrate ACF custom fields to Meta Box.
 * Version:     0.1.0
 * Author:      MetaBox.io
 * Author URI:  https://metabox.io
 * License:     GPL2+
 * Text Domain: meta-box
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || die;

if ( ! function_exists( 'mb_acf_load' ) ) {
	if ( file_exists( __DIR__ . '/vendor' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	}

	add_action( 'init', 'mb_acf_load', 0 );

	function mb_acf_load() {
		if ( ! defined( 'RWMB_VER' ) ) {
			return;
		}

		define( 'MBACF_DIR', __DIR__ );

		if ( is_admin() ) {
			new MetaBox\ACF\AdminPage;
			new MetaBox\ACF\Ajax;
		}
	}
}