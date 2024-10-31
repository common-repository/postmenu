<?php

/**
 * Fired during plugin activation
 *
 * @link       https://liontude.com/postmenu/
 * @since      1.0.0
 *
 * @package    Postmenu
 * @subpackage Postmenu/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Postmenu
 * @subpackage Postmenu/includes
 * @author     Liontude <info@liontude.com>
 */
class Postmenu_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		//here we can save default options

		if ( class_exists( 'Postmenu_Settings' ) ) {
			$options = Postmenu_Settings::get_options();

			//reset administrator role options and save these options

			if ( is_array( $options ) ) {
				$options['roles'] = array( 'administrator' );
				//update general options
				Postmenu_Settings::update_options( $options );
				//apply changes in roles options
				Postmenu_Settings::update_roles();
			}
		}

	}

}
