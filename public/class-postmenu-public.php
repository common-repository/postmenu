<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://liontude.com/postmenu/
 * @since      1.0.0
 *
 * @package    Postmenu
 * @subpackage Postmenu/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the postmenu, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Postmenu
 * @subpackage Postmenu/public
 * @author     Liontude <info@liontude.com>
 */
class Postmenu_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $postmenu The ID of this plugin.
	 */
	private $postmenu;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wp_postmenu The name of the plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $postmenu, $version ) {

		$this->postmenu = $postmenu;
		$this->version  = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Postmenu_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Postmenu_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->postmenu, plugin_dir_url( __FILE__ ) . 'css/postmenu-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WP_Postmenu_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WP_Postmenu_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->postmenu, plugin_dir_url( __FILE__ ) . 'js/postmenu-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->postmenu, 'postmenu_ajax_url', admin_url( 'admin-ajax.php' ) );

	}

	public function postmenu_create_duplicate_from_ajax() {
		if ( isset( $_GET['id'] ) || isset( $_POST['id'] ) ) {
			$id           = ( isset( $_GET['id'] ) ? $_GET['id'] : $_POST['id'] );
			$admin_plugin = new Postmenu_Admin( $this->postmenu, $this->version );
			$admin_plugin->postmenu_admin_ajax_duplicate_post( $id );
		}
	}

}
