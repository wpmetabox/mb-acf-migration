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

namespace MetaBox\ACF;

defined( 'ABSPATH' ) || die;

require __DIR__ . '/vendor/autoload.php';

define( 'MBACF_DIR', __DIR__ );

if ( is_admin() ) {
    new AdminPage;
    new Ajax;
}