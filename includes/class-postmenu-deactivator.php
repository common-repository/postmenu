<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://liontude.com/postmenu/
 * @since      1.0.0
 *
 * @package    Postmenu
 * @subpackage Postmenu/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Postmenu
 * @subpackage Postmenu/includes
 * @author     Liontude <info@liontude.com>
 */
class Postmenu_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		if ( class_exists( 'Postmenu_Settings' ) ) {
			$option_key = Postmenu_Settings::get_db_option();
			delete_option( $option_key );
		}
	}

}
