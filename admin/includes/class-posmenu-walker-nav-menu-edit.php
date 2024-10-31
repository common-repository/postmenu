<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Postmenu
 * @subpackage Postmenu/admin/includes
 * @author     Liontude <info@liontude.com>
 */

if ( ! class_exists( 'Walker_Nav_Menu_Edit' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php' );
}

class Lion_Posmenu_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$item_output = '';
		$output      .= parent::start_el( $item_output, $item, $depth, $args, $id );
		global $wp_version;
		if ( version_compare( $wp_version, '4.7', '>=' ) ) {
			$output .= preg_replace(
				'/(?=<fieldset[^>]+class="[^"]*field-move)/',
				$this->get_custom_fields( $item, $depth, $args ),
				$item_output
			);
		} else {
			$output .= preg_replace(
				'/(?=<p[^>]+class="[^"]*field-move)/',
				$this->get_custom_fields( $item, $depth, $args ),
				$item_output
			);
		}

	}

	protected function get_custom_fields( $item, $depth = 0, $args = array() ) {
		ob_start();
		$item_id = intval( $item->ID );
		/**
		 * Get menu item custom fields from plugins/themes
		 *
		 * @since 0.1.0
		 *
		 * @param int $item_id post ID of menu
		 * @param object $item Menu item data object.
		 * @param int $depth Depth of menu item. Used for padding.
		 * @param array $args Menu item args.
		 *
		 * @return string Custom fields
		 */

		do_action( 'wp_nav_menu_item_custom_fields', $item_id, $item, $depth, $args );

		return ob_get_clean();
	}

}