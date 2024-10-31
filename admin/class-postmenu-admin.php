<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the postmenu, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Postmenu
 * @subpackage Postmenu/admin
 * @author     Liontude <info@liontude.com>
 */
class Postmenu_Admin {

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
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Postmenu_Duplicate_Post $duplicate_post The duplicate post function handler.
	 */
	private $duplicate_post;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Postmenu_Duplicate_Menu $duplicate_menu The duplicate menu function handler.
	 */
	private $duplicate_menu;

	/**
	 * The version of this plugin.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      Postmenu_Duplicate_Taxonomy $duplicate_taxonomy The duplicate category function handler.
	 */
	private $duplicate_taxonomy;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $wp_postmenu The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $postmenu, $version ) {

		$this->postmenu = $postmenu;
		$this->version  = $version;
		$this->load_dependencies();

	}

	/**
	 * Load the required dependencies for this class.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Postmenu_Duplicate_Post. Handles the duplicate post functions.
	 * - Postmenu_Duplicate_Menu. Handles the duplicate menu functions.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for duplicating post.
		 */
		require_once( dirname( __FILE__ ) . '/includes/class-postmenu-duplicate-post.php' );
		$this->duplicate_post = new Postmenu_Duplicate_Post();

		/**
		 * The class responsible for duplicating menu.
		 */
		require_once( dirname( __FILE__ ) . '/includes/class-postmenu-duplicate-menu.php' );
		$this->duplicate_menu = new Postmenu_Duplicate_Menu();

		/**
		 * The class responsible for duplicating menu.
		 */
		require_once( dirname( __FILE__ ) . '/includes/class-postmenu-duplicate-taxonomy.php' );
		$this->duplicate_taxonomy = new Postmenu_Duplicate_Taxonomy();
	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->postmenu, plugin_dir_url( __FILE__ ) . 'css/postmenu-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->postmenu, plugin_dir_url( __FILE__ ) . 'js/postmenu-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->postmenu, 'postmenu_success_message', esc_html__( "item duplicated", "postmenu" ) );

	}

	/**
	 * Whitelist options.
	 *
	 * @since    1.0.0
	 */
	public function postmenu_register_settings() {
		register_setting( 'postmenu_group', 'postmenu_options' );
	}

	/**
	 * Function to create the options settings page.
	 *
	 * @since    1.0.0
	 */
	public function postmenu_setting_menu_page() {

		require_once( dirname( __FILE__ ) . '/partials/postmenu-options.php' );

		add_options_page( __( "Postmenu Options", 'postmenu' ), __( "Postmenu", 'postmenu' ), 'manage_options', 'postmenu', 'postmenu_options' );

	}

	/**
	 * Plugin upgrade
	 *
	 * @since    1.0.0
	 */
	public function postmenu_plugin_upgrade() {
		$installed_version = get_site_option( 'postmenu_version' );

		if ( $installed_version == POSTMENU_CURRENT_VERSION ) {
			return;
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

		Postmenu_Settings::update_options( $options );

		delete_option( 'postmenu_version' );
		update_site_option( 'postmenu_version', POSTMENU_CURRENT_VERSION );

	}

	public function postmenu_init_links() {

		//Add the duplicate options in the edit menu of the post and pages view
		if ( Postmenu_Settings::get_option( 'show_in_postlist' ) == 1 ) {
			add_filter( 'post_row_actions', array( $this, 'postmenu_make_duplicate_link_row' ), 10, 2 );
			add_filter( 'page_row_actions', array( $this, 'postmenu_make_duplicate_link_row' ), 10, 2 );
			add_filter( 'tag_row_actions', array(
				$this->duplicate_taxonomy,
				'postmenu_make_duplicate_link_row'
			), 10, 2 );
		}
		//Add the duplicate option in the bulk actions
		if ( Postmenu_Settings::get_option( 'show_in_bulkactions' ) == 1 ) {

			// Bulk to Post Types
			$postmenu_options_types_enabled = Postmenu_Settings::get_option( 'types_enabled' );
			if ( ! is_array( $postmenu_options_types_enabled ) ) {
				$postmenu_options_types_enabled = array( $postmenu_options_types_enabled );
			}
			foreach ( $postmenu_options_types_enabled as $postmenu_type_enabled ) {
				add_filter( "bulk_actions-edit-{$postmenu_type_enabled}", array(
					$this->duplicate_post,
					'postmenu_register_bulk_action'
				) );
				add_filter( "handle_bulk_actions-edit-{$postmenu_type_enabled}", array(
					$this->duplicate_post,
					'postmenu_action_handler'
				), 10, 3 );
			}
			// Bulk to Taxonomy
			$postmenu_options_taxonomy_enabled = Postmenu_Settings::get_option( 'taxonomy_enabled' );
			if ( ! is_array( $postmenu_options_taxonomy_enabled ) ) {
				$postmenu_options_taxonomy_enabled = array( $postmenu_options_taxonomy_enabled );
			}
			foreach ( $postmenu_options_taxonomy_enabled as $postmenu_taxonomy_enabled ) {
				add_filter( "bulk_actions-edit-{$postmenu_taxonomy_enabled}", array(
					$this->duplicate_taxonomy,
					'postmenu_register_bulk_action'
				) );
				add_filter( "handle_bulk_actions-edit-{$postmenu_taxonomy_enabled}", array(
					$this->duplicate_taxonomy,
					'postmenu_action_handler'
				), 10, 3 );
			}
		}
		// Add a button in the post/page edit screen to create a clone
		if ( Postmenu_Settings::get_option( 'show_in_editscreen' ) == 1 ) {
			add_action( 'edit_form_after_editor', array( $this, 'postmenu_edit_form_after_editor' ) );
		}
		//Connect actions to functions
		add_action( 'admin_action_postmenu_save_as_new_post', array(
			$this->duplicate_post,
			'postmenu_save_as_new_post'
		) );
		add_action( 'admin_action_postmenu_save_as_new_menu', array(
			$this->duplicate_menu,
			'postmenu_save_as_new_menu'
		) );
		add_action( 'admin_action_postmenu_save_as_new_taxonomy', array(
			$this->duplicate_taxonomy,
			'postmenu_save_as_new_taxonomy'
		) );
		//
		if ( Postmenu_Settings::get_option( 'enable_menu_link' ) == 1 ) {
			global $pagenow;
			if ( $pagenow == 'nav-menus.php' ) {
				$this->duplicate_menu->postmenu_save_menu_dropdown();
			}
		}
	}

	/**
	 * Add the link to action list for post_row_actions
	 */
	function postmenu_make_duplicate_link_row( $actions, $post ) {

		$actions = $this->duplicate_post->postmenu_make_duplicate_link_row( $actions, $post );
		$actions = $this->duplicate_menu->postmenu_make_duplicate_link_row( $actions, $post );

		return $actions;
	}

	/**
	 * Add the links in the edit post view.
	 */
	public function postmenu_edit_form_after_editor() {
		global $post;
		$this->duplicate_post->postmenu_edit_form_after_editor( $post );
		$this->duplicate_menu->postmenu_edit_form_after_editor( $post );
	}

	/**
	 * Add the link to admin bar list for wp_before_admin_bar_render
	 */
	public function postmenu_admin_bar_render() {
		if ( ! is_admin_bar_showing() ) {
			return;
		}
		global $wp_admin_bar, $post;
		if ( ! empty( $post ) ) {
			echo '<script>LP_Scope.current_postId = ' . $post->ID . ';</script>';
		}
		$this->duplicate_post->postmenu_admin_bar_render( $wp_admin_bar, $post );
	}

	public function postmenu_advanced_duplicate_fieldsets() {

		require_once( dirname( __FILE__ ) . '/partials/postmenu-advanced-duplicate-form.php' );

		$post_id    = intval( isset( $_GET['id'] ) ? $_GET['id'] : $_POST['id'] );
		$post       = get_post( $post_id );
		$row_number = intval( isset( $_GET['rownumber'] ) ? $_GET['rownumber'] : $_POST['rownumber'] );

		postmenu_advanced_duplicate_form_function( $post, $row_number );
		wp_die();
	}

	public function postmenu_menu_link_fieldsets() {

		require_once( dirname( __FILE__ ) . '/partials/postmenu-menu-link-form.php' );

		$post_id    = intval( isset( $_GET['id'] ) ? $_GET['id'] : $_POST['id'] );
		$post       = get_post( $post_id );
		$row_number = intval( isset( $_GET['rownumber'] ) ? $_GET['rownumber'] : $_POST['rownumber'] );

		postmenu_menu_link_form_function( $post, $row_number );
		wp_die();
	}

	public function postmenu_admin_ajax_duplicate_post( $post_id = "" ) {

		if ( $post_id == "" ) {
			$post_id = intval( isset( $_GET['id'] ) ? $_GET['id'] : $_POST['id'] );
		}
		$post_id = intval( $post_id );
		if ( $post = get_post( $post_id ) ) {
			$this->duplicate_post->postmenu_create_duplicate( $post );
		}

		wp_die();
	}

	public function postmenu_admin_ajax_advanced_duplicate_post() {
		$params = (array) ( isset( $_GET['params'] ) ? $_GET['params'] : $_POST['params'] );
//		$post_copy = array_map( 'esc_html',( array_key_exists('post_copy', $params) ? $params['post_copy'] : null));
//		$post =  (Object) $post_copy;
		$post = ( array_key_exists( 'post_copy', $params ) ? (Object) $params['post_copy'] : null );
		$this->duplicate_post->postmenu_create_duplicate( $post, '', $params['conditions'] );
		wp_die();
	}

	public function postmenu_admin_ajax_duplicate_menu_link() {

		$menu_id   = intval( isset( $_GET['id'] ) ? $_GET['id'] : $_POST['id'] );
		$menu_name = sanitize_text_field( isset( $_GET['name'] ) ? $_GET['name'] : $_POST['name'] );
		$new_id    = $this->duplicate_menu->postmenu_duplicate_menu_link( $menu_id, $menu_name );
		wp_die( $new_id );
	}

	public function postmenu_admin_ajax_get_menu_items_box() {
		$menu_id = intval( isset( $_GET['menu'] ) ? $_GET['menu'] : $_POST['menu'] );
		echo Postmenu_Duplicate_Menu::postmenu_get_menu_items_box( $menu_id );
		wp_die();
	}

	public function postmenu_admin_ajax_get_menu_locations_box() {
		$menu_id = intval( isset( $_GET['menu'] ) ? $_GET['menu'] : $_POST['menu'] );
		echo Postmenu_Duplicate_Menu::postmenu_get_menu_locations_box( $menu_id );
		wp_die();
	}

	public function postmenu_admin_ajax_update_menu() {
		$this->duplicate_menu->postmenu_admin_ajax_update_menu();
	}

	public function postmenu_admin_ajax_delete_menu() {
		$this->duplicate_menu->postmenu_admin_ajax_delete_menu();
	}

	public function postmenu_admin_ajax_delete_menu_item() {
		$this->duplicate_menu->postmenu_admin_ajax_delete_menu_item();
	}

	public static function postmenu_array_insert( &$array, $position, $insert ) {
		if ( is_int( $position ) ) {
			array_splice( $array, $position, 0, $insert );
		} else {
			$pos   = array_search( $position, array_keys( $array ) );
			$array = array_merge(
				array_slice( $array, 0, $pos ),
				$insert,
				array_slice( $array, $pos )
			);
		}
	}

}
