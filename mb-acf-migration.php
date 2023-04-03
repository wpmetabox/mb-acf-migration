<?php
/**
 * Plugin Name: MB ACF Migration
 * Plugin URI:  https://metabox.io/plugins/mb-acf-migration/
 * Description: Migrate ACF custom fields to Meta Box.
 * Version:     1.0.2
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
		if ( ! defined( 'RWMB_VER' ) || ! class_exists( 'ACF' ) || ! is_admin() ) {
			return;
		}

		define( 'MBACF_DIR', __DIR__ );

		new MetaBox\ACF\AdminPage;
		new MetaBox\ACF\Ajax;
	}
}