<?php
/**
 * @link              http://hello-jeff.com
 * @since             1.0.0
 * @package           WPEPTI
 *
 * @wordpress-plugin
 * Plugin Name:       WPEngine Page Template Identifier
 * Plugin URI:        https://www.wpengine.com
 * Description:       This plugin can be used to help identify  which page templates are being used on what pages.
 * Version:           1.0.0
 * Author:            Jeff Ward
 * Author URI:        http://hello-jeff.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wpe-pti
 * Domain Path:       /languages
 */

/////////////////////////////////////
# Do not call this file directly
/////////////////////////////////////
if( !defined( 'WPINC' ) ) die;

///////////////////////////
# Current plugin version
///////////////////////////
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

///////////////////////////
# Activation events
///////////////////////////
function activate_wpe_pti() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpe-pti-activation.php';
	WPE_PTI_Activation::activate();
}

///////////////////////////
# Deactivation events
///////////////////////////
function deactivate_wpe_pti() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wpe-pti-deactivation.php';
	WPE_PTI_Deactivation::deactivate();
}

register_activation_hook( __FILE__, 'activate_wpe_pti' );
register_deactivation_hook( __FILE__, 'deactivate_wpe_pti' );

///////////////////////////////////
# Include the core plugin class
///////////////////////////////////
require plugin_dir_path( __FILE__ ) . 'includes/class-wpe-pti.php';

//////////////////////////////
# Initialize the plugin
//////////////////////////////
function run_wpe_pti() {
	$plugin = new WPE_PTI();
	$plugin->run();
}
run_wpe_pti();
