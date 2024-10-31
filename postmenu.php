<?php

/**
 * @link              https://liontude.com/postmenu/
 * @since             1.1.0
 * @package           Postmenu
 *
 * @wordpress-plugin
 * Plugin Name:       Postmenu
 * Plugin URI:        https://liontude.com/postmenu/
 * Description:       Duplicate Posts, Pages, Menus, Menu Links (Items of Menu). Easily Add your Posts or Pages to the Menus and control who can see the content on the web using “User Roles” in the menu.
 * Version:           1.4.2
 * Author:            Liontude
 * Author URI:        https://liontude.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       postmenu
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Plugin version
define( 'POSTMENU_CURRENT_VERSION', '1.4.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-postmenu-activator.php
 */
function activate_postmenu() {


	require_once plugin_dir_path( __FILE__ ) . 'includes/class-postmenu-activator.php';
	Postmenu_Activator::activate();

}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-postmenu-deactivator.php
 */
function deactivate_postmenu() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-postmenu-deactivator.php';
	Postmenu_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_postmenu' );
register_deactivation_hook( __FILE__, 'deactivate_postmenu' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-postmenu.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_postmenu() {

	$plugin = new Postmenu();
	$plugin->run();

	//Links to settings
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
		$links[] = '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=postmenu' ) ) . '">Settings</a>';
		$links[] = '<a href="https://liontude.com" target="_blank">More plugins by Liontude</a>';

		return $links;
	} );

}

/**
 * Load Addons
 */

include_once 'addons/postmenu-post-type-wpml.php';

run_postmenu();