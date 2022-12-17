<?php
/**
 * Plugin Name: Shipping Label PDF Generator With UPS For Woocommerce
 * Plugin URI:  http://www.saifulislam.dev/wc-ups-shipping-label-generator
 * Description: This plugin will create shipping label in pdf format for ups courier right after any order created from through.
 * Version:     1.0
 * Requires at least: 5.0
 * Requires PHP:      5.4
 * Author:      Saiful Islam
 * Author URI:   http://www.saifulislam.dev
 * Text Domain: shipping-label-gen
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Requires main plugin file
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-shipping-label-generator-ups.php';

/**
 * Initialise the main function
 *
 * @return void
 */
function shipping_label_ups(){
	Shipping_Label_Generator_UPS::init();
}
// let's start the plugin
shipping_label_ups();
