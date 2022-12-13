<?php
/**
 * Plugin Name: UPS Shipping Label PDF Generator For Woocommerce
 * Plugin URI:  http://www.saifulislam.dev/wc-ups-shipping-label-generator
 * Description: This plugin will create shipping label in pdf format for ups courier right after any order created from through.
 * Version:     1.0
 * Requires at least: 5.0
 * Requires PHP:      5.4
 * Author:      Saiful Islam
 * Author URI:   http://www.saifulislam.dev
 * Text Domain: shipping-label-gen
 * Domain Path: /languages/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SHIPPING_LABEL_GENERATOR_VERSION', '1.0.0' );
define( 'PLUGIN_NAME', 'SHIPPING_LABEL_GENERATOR' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ups-shipping-label-generator.php';

/**
 * Initialise the main function
 *
 * @return void
 */
function ups_shipping_label(){
	UPS_Shipping_Label_Generator::init();
}
// let's start the plugin
ups_shipping_label();
