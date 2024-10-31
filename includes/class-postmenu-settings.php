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
class Postmenu_Settings {

	/**
	 * The name for plugin options in the DB
	 *
	 * @var string
	 */
	static $db_option = 'postmenu_options';


	/**
	 * Return the name of the key used to save options for this plugin
	 *
	 * @return value of the key option used to save an options list
	 * @access public
	 */
	static public function get_db_option() {
		return self::$db_option;
	}

	/**
	 * Return a specific settings of Plugin
	 *
	 * @return value of the options of the given key
	 * @access public
	 */
	static public function get_option( $key ) {
		$options = self::get_options();

		return array_key_exists( $key, $options ) ? $options[ $key ] : 0;
	}

	/**
	 * Test if post type is enable to be copied
	 *
	 * @return boolean general options of plugin
	 * @access public
	 */
	static public function is_post_type_enabled( $post_type ) {
		$options = self::get_option( 'types_enabled' );
		if ( ! is_array( $options ) ) {
			$options = array( $options );
		}

		return in_array( $post_type, $options );
	}

	/**
	 * Test if taxonomy is enable to be copied
	 *
	 * @return boolean general options of plugin
	 * @access public
	 */
	static public function is_taxonomy_enabled( $taxonomy ) {
		$options = self::get_option( 'taxonomy_enabled' );
		if ( ! is_array( $options ) ) {
			$options = array( $options );
		}

		return in_array( $taxonomy, $options );
	}

	/**
	 * Return the General Settings of Plugin, and set them to default values if they are empty
	 *
	 * @return array general options of plugin
	 * @access public
	 */
	static function get_options() {
		// If isn't empty, return class variable
		if ( Postmenu::$options ) {
			return Postmenu::$options;
		}

		// default values
		$options = array( // Global Settings
			'show_in_postlist'    => 1,
			'show_in_editscreen'  => 1,
			'show_in_adminbar'    => 1,
			'show_in_bulkactions' => 1,
			'show_in_viewpreview' => 1,
			'enable_advanced'     => 1,
			'types_enabled'       => array( 'post', 'page' ),
			'taxonomy_enabled'    => array( 'category', 'tag' ),
			'roles'               => array( 'editor', 'administrator' ),
			'enable_menu_link'    => 1
		);

		// get saved options
		$saved = get_option( self::$db_option );

		// assign them
		if ( ! empty( $saved ) ) {
			$options = $saved;
		} else {
			update_option( self::$db_option, $options );
		}

		// Save class variable
		Postmenu::$options = $options;

		//return the options
		return $options;
	}

	static function update_roles() {

		global $wp_roles;
		$roles            = $wp_roles->get_names();
		$postmenu_options = Postmenu_Settings::get_options();

		$dp_roles = $postmenu_options['roles'];
		$dp_roles = ( $dp_roles == "" ) ? $dp_roles = array() : $dp_roles;

		foreach ( $roles as $name => $display_name ) {
			$role = get_role( $name );

			// role should have at least edit_posts capability
			if ( ! $role->has_cap( 'edit_posts' ) ) {
				continue;
			}

			/* If the role doesn't have the capability and it was selected, add it. */
			if ( ! $role->has_cap( 'copy_posts' ) && in_array( $name, $dp_roles ) ) {
				$role->add_cap( 'copy_posts' );
			} /* If the role has the capability and it wasn't selected, remove it. */
			elseif ( $role->has_cap( 'copy_posts' ) && ! in_array( $name, $dp_roles ) ) {
				$role->remove_cap( 'copy_posts' );
			}
		}
	}


	/**
	 * Updates the General Settings of Plugin
	 *
	 * @param array $options
	 *
	 * @return array
	 * @access public
	 */
	static public function update_options( $options ) {
		// Save Class variable
		Postmenu::$options = $options;

		return update_option( self::$db_option, $options );
	}

}