<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://liontude.com/postmenu/
 * @since      1.0.0
 *
 * @package    Postmenu
 * @subpackage Postmenu/includes
 */

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
 * @subpackage Postmenu/includes
 * @author     Liontude <info@liontude.com>
 */
class Postmenu {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Postmenu_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $postmenu The string used to uniquely identify this plugin.
	 */
	protected $postmenu;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array $options The array options of the plugin.
	 */
	public static $options;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the postmenu and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->postmenu = 'postmenu';
		$this->version  = '1.1.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Postmenu_Loader. Orchestrates the hooks of the plugin.
	 * - Postmenu_i18n. Defines internationalization functionality.
	 * - Postmenu_Admin. Defines all hooks for the admin area.
	 * - Postmenu_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-postmenu-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-postmenu-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-postmenu-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-postmenu-public.php';

		/**
		 * The class responsible for managing all options settings of the Pluging
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-postmenu-settings.php';

		$this->loader = new Postmenu_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Postmenu_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Postmenu_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Postmenu_Admin( $this->get_postmenu(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		//Creating a settings page to configure the plugin
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'postmenu_setting_menu_page' );
		//Resgiter de configuration fields
		$this->loader->add_action( 'admin_init', $plugin_admin, 'postmenu_register_settings' );
		//Upgrade function
		$this->loader->add_action( 'wp_loaded', $plugin_admin, 'postmenu_plugin_upgrade' );
		//Inizialicing links
		$this->loader->add_action( 'admin_init', $plugin_admin, 'postmenu_init_links' );
		$this->loader->add_action( 'wp_before_admin_bar_render', $plugin_admin, 'postmenu_admin_bar_render' );
		//
		$this->loader->add_action( 'wp_ajax_postmenu_ajax_duplicate_post_admin', $plugin_admin, 'postmenu_admin_ajax_duplicate_post' );
		$this->loader->add_action( 'wp_ajax_postmenu_ajax_advanced_duplicate_post_admin', $plugin_admin, 'postmenu_admin_ajax_advanced_duplicate_post' );
		$this->loader->add_action( 'wp_ajax_postmenu_advanced_duplicate_fieldsets', $plugin_admin, 'postmenu_advanced_duplicate_fieldsets' );
		$this->loader->add_action( 'wp_ajax_postmenu_menu_link_fieldsets', $plugin_admin, 'postmenu_menu_link_fieldsets' );
		$this->loader->add_action( 'wp_ajax_postmenu_admin_ajax_duplicate_menu_link', $plugin_admin, 'postmenu_admin_ajax_duplicate_menu_link' );
		$this->loader->add_action( 'wp_ajax_postmenu_admin_ajax_get_menu_items_box', $plugin_admin, 'postmenu_admin_ajax_get_menu_items_box' );
		$this->loader->add_action( 'wp_ajax_postmenu_admin_ajax_get_menu_locations_box', $plugin_admin, 'postmenu_admin_ajax_get_menu_locations_box' );
		$this->loader->add_action( 'wp_ajax_postmenu_admin_ajax_update_menu', $plugin_admin, 'postmenu_admin_ajax_update_menu' );
		$this->loader->add_action( 'wp_ajax_postmenu_admin_ajax_delete_menu', $plugin_admin, 'postmenu_admin_ajax_delete_menu' );
		$this->loader->add_action( 'wp_ajax_postmenu_admin_ajax_delete_menu_item', $plugin_admin, 'postmenu_admin_ajax_delete_menu_item' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Postmenu_Public( $this->get_postmenu(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_ajax_postmenu_ajax_duplicate_post', $plugin_public, 'postmenu_create_duplicate_from_ajax' );
		$this->loader->add_shortcode( 'postmenu-default', $plugin_public, 'shortcode_postmenu_default' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_postmenu() {
		return $this->postmenu;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Postmenu_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
