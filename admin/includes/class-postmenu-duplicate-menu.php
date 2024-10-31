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
class Postmenu_Duplicate_Menu {

	public function __construct() {
		// switch the admin walker
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'postmenu_edit_nav_menu_walker' ) );

		// add new fields via hook
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'postmenu_nav_custom_fields' ), 10, 4 );

		// save the menu item meta
		add_action( 'wp_update_nav_menu_item', array( $this, 'postmenu_update_nav_menu_item' ), 10, 2 );

		// add meta to menu item
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'postmenu_setup_nav_menu_item' ) );

		// exclude items via filter instead of via custom Walker
		if ( ! is_admin() ) {
			$priority = 20; // Because WP_Customize_Nav_Menu_Item_Setting::filter_wp_get_nav_menu_items() runs at 10.
			add_filter( 'wp_get_nav_menu_items', array( $this, 'postmenu_get_nav_menu_items' ), $priority );
		}
	}

	public function postmenu_save_menu_dropdown() {
		if ( isset( $_GET['menu'] ) && ( ! isset( $_GET['action'] ) || "delete" != $_GET['action'] ) ) {
			$menu_id = intval( $_GET['menu'] );
		} else {
			$nav_menus = wp_get_nav_menus();
			$menu_id   = ! empty( $nav_menus ) ? $nav_menus[0]->term_id : 0;
		}
		$item_class = ( empty( $menu_id ) || $menu_id == 0 ) ? 'lp-disabled' : '';

		$save_menu_button_links = array(
			'duplicate'      => '<a  
                id="postmenu_duplicate_menu"
                class="' . $item_class . '"
                title="' . esc_attr__( "Duplicate this item", 'postmenu' ) . '"
                href="#">'
			                    . esc_html__( 'Duplicate', 'postmenu' ) . '</a>',
			'duplicate_edit' => '<a  
                class="' . $item_class . '"
                title="' . esc_attr__( "Duplicate & Edit", 'postmenu' ) . '"
                href="' . $this->postmenu_get_duplicate_menu_link( $menu_id, 'edit' ) . '">'
			                    . esc_html__( 'Duplicate & Edit', 'postmenu' ) . '</a>',
			'menu_id'        => $menu_id,
			'errorMessaje'   => esc_html__( "There was a problem duplicating your menu. No action was taken.", "postmenu" ),
			'repeitedItem'   => esc_html__( "You can't duplicate the menu, the name for the new menu (duplicated) is already in use. Try changing the name of the old menu or duplicating the menu using the general settings of Postmenu in Settings / Postmenu / Menu (tab) picking a new name.", "postmenu" ),
		);

		$warning = "";
		if ( isset( $_GET['duplicateerror'] ) ) {
			$warning = 'LP_Scope.notifyError("' .
			           esc_html__( "You can't duplicate the menu, the name for the new menu (duplicated) is already in use. Try changing the name of the old menu or duplicating the menu using the general settings of Postmenu in Settings / Postmenu / Menu (tab) picking a new name.", "postmenu" ) . '");';
		}

		echo '<script>
                window.LP_Scope = window.LP_Scope || {};
                LP_Scope.save_menu_button_links = ' . json_encode( $save_menu_button_links ) . ';
                document.addEventListener("DOMContentLoaded", function() {
                    LP_Scope.addSaveMenuButton();' . $warning . '
                });
                </script>';
	}

	/**
	 * Retrieve duplicate post link for post.
	 *
	 *
	 * @param int $id Optional. Menu ID.
	 * @param string $context Optional, default to display. How to write the '&', defaults to '&amp;'.
	 * @param boolean $advanced Optional, default to true
	 *
	 * @return string
	 */
	public function postmenu_get_duplicate_menu_link( $id = 0, $redirect_to = 'list', $context = 'display' ) {

		if ( ! current_user_can( 'copy_posts' ) ) {
			return;
		}
		if ( ! $menu = wp_get_nav_menu_object( $id ) ) {
			return;
		}
		$action_name = "postmenu_save_as_new_menu";

		if ( 'display' == $context ) {
			$action = '?action=' . $action_name . '&amp;menu_id=' . $menu->term_id . '&amp;redirect=' . $redirect_to;
		} else {
			$action = '?action=' . $action_name . '&menu_id=' . $menu->term_id . '&redirect=' . $redirect_to;
		}

		return apply_filters( 'postmenu_get_duplicate_menu_link', admin_url( "admin.php" . $action ), $menu->term_id, $context );
	}

	/*
	 * This function calls the creation of a new copy of the selected menu (by default preserving the original publish status)
	* then redirects to the post list
	*/
	function postmenu_save_as_new_menu() {
		if ( ! ( isset( $_GET['menu_id'] ) || isset( $_POST['menu_id'] ) || ( isset( $_REQUEST['action'] ) && 'postmenu_save_as_new_menu' == $_REQUEST['action'] ) ) ) {
			wp_die( esc_html__( 'No menu to duplicate has been supplied!', 'postmenu' ) );
		}

		// Get the original post
		$menu_id = intval( isset( $_GET['menu_id'] ) ? $_GET['menu_id'] : $_POST['menu_id'] );

		$redirect_to = ( isset( $_GET['redirect'] ) ? $_GET['redirect'] : $_POST['redirect'] );
		// Copy the menu and insert it
		$new_id = $this->postmenu_duplicate_menu_link( $menu_id );
		if ( ! $new_id || $new_id == "error" ) {
			$sendback = remove_query_arg( array(
				'trashed',
				'untrashed',
				'deleted',
				'cloned',
				'ids'
			), admin_url( 'nav-menus.php?menu=' . $menu_id . '&duplicateerror=1' ) );
			wp_redirect( add_query_arg( array( 'ids' => $menu_id ), $sendback ) );
			exit;
		}elseif ( $redirect_to == 'list' ) {
			$sendback = remove_query_arg( array(
				'trashed',
				'untrashed',
				'deleted',
				'cloned',
				'ids'
			), admin_url( 'nav-menus.php?menu=' . $menu_id ) );
			// Redirect to the post list screen
			wp_redirect( add_query_arg( array( 'cloned' => 1, 'ids' => $menu_id ), $sendback ) );
		}elseif ( $redirect_to == 'edit' ) {
			// Redirect to the edit screen for the new draft post
			wp_redirect( add_query_arg( array(
				'cloned' => 1,
				'ids'    => $menu_id
			), admin_url( 'nav-menus.php?menu=' . $new_id ) ) );
		}
		exit;
	}

	/**
	 * Add the link to action list for post_row_actions
	 *
	 * This is visible on Post List
	 *
	 */
	function postmenu_make_duplicate_link_row( $actions, $post ) {

		if ( current_user_can( 'copy_posts' ) && Postmenu_Settings::is_post_type_enabled( $post->post_type ) ) {
			if ( Postmenu_Settings::get_option( 'enable_menu_link' ) == 1 ) {
				global $wp_version;
				$develop_src = false !== strpos( $wp_version, '-src' );
				$dev_suffix  = $develop_src ? '' : '.min';
				wp_enqueue_script( 'jquery-ui-sortable', plugin_dir_url( __FILE__ ) . "/wp-includes/js/jquery/ui/sortable$dev_suffix.js", array( 'jquery-ui-mouse' ), POSTMENU_CURRENT_VERSION, 1 );
				$actions['lion_pm_menu_link_row_link'] = '<a id="lpml-' . $post->ID . '" href="#" title="'
				                                         . esc_attr__( 'Menu Link', 'postmenu' )
				                                         . '">' . esc_html__( 'Menu Link', 'postmenu' ) . '</a>';
			}
		}

		return $actions;
	}

	/**
	 * Add the links in the edit add new button.
	 */
	public function postmenu_edit_form_after_editor( $post ) {
		$permalink_option = get_option( 'permalink_structure' );
		$containerHtml    = ( $permalink_option === false || $permalink_option == "" ) ? "plain" : "other";
		if ( Postmenu_Settings::is_post_type_enabled( $post->post_type ) ) {
			echo '<script>

                    var permalinks_structure = "' . $containerHtml . '";
					LP_Scope.addMenuLinkButton("' . esc_html__( 'Menu Link', 'postmenu' ) . '");
				</script>';
		}
	}

	/**
	 * Duplicates a menu if $id is supplied instead creates a menu if name is supplied.
	 *
	 * @param int $id Optional. Menu ID to duplicate.
	 * @param string $name Optional. Name of the new menu.
	 *
	 * @return int new Menu ID.
	 */
	public function postmenu_duplicate_menu_link( $id = null, $name = null ) {

		// sanity check
		if ( empty( $id ) ) {
			if ( empty( $name ) ) {
				return false;
			} else if ( wp_get_nav_menu_object( $name ) ) {
				return 'error';
			} else {
				$new_id = wp_create_nav_menu( $name );

				return $new_id;
			}
		}

		$id     = intval( $id );
		$source = wp_get_nav_menu_object( $id );

		$name = $this->postmenu_generate_unique_names( $name, $source );

		$source_items = wp_get_nav_menu_items( $id );
		$new_id       = wp_create_nav_menu( $name );
		// echo $new_id;wp_die();
		if ( ! $new_id ) {
			return false;
		}

		// key is the original db ID, val is the new
		$rel = array();

		$i = 1;
		foreach ( $source_items as $menu_item ) {
			$args = array(
				'menu-item-db-id'       => $menu_item->db_id,
				'menu-item-object-id'   => $menu_item->object_id,
				'menu-item-object'      => $menu_item->object,
				'menu-item-position'    => $i,
				'menu-item-type'        => $menu_item->type,
				'menu-item-title'       => $menu_item->title,
				'menu-item-url'         => $menu_item->url,
				'menu-item-description' => $menu_item->description,
				'menu-item-attr-title'  => $menu_item->attr_title,
				'menu-item-target'      => $menu_item->target,
				'menu-item-classes'     => implode( ' ', $menu_item->classes ),
				'menu-item-xfn'         => $menu_item->xfn,
				'menu-item-status'      => $menu_item->post_status
			);

			$parent_id = wp_update_nav_menu_item( $new_id, 0, $args );

			$rel[ $menu_item->db_id ] = $parent_id;

			// did it have a parent? if so, we need to update with the NEW ID
			if ( $menu_item->menu_item_parent ) {
				$args['menu-item-parent-id'] = $rel[ $menu_item->menu_item_parent ];
				wp_update_nav_menu_item( $new_id, $parent_id, $args );
			}

			$i ++;
		}

		return $new_id;
	}

	public function postmenu_update_nav_menu_item( $menu_id, $menu_item_id ) {
		// verify this came from our screen and with proper authorization.
		if ( ! isset( $_POST['postmenu-role-nonce'] ) || ! wp_verify_nonce( $_POST['postmenu-role-nonce'], 'postmenu-role-nonce' ) ) {
			return;
		}

		if ( isset( $_POST['postmenu-user-type'] ) && array_key_exists( $menu_item_id, $_POST['postmenu-user-type'] ) ) {
			$allowed_roles = $_POST['postmenu-user-type'][ $menu_item_id ];
			if ( $allowed_roles == 'in' && isset( $_POST['postmenu-menu-roles'] )
			     && isset( $_POST['postmenu-menu-roles'][ $menu_item_id ] )
			     && is_array( $_POST['postmenu-menu-roles'][ $menu_item_id ] )
			     && ! empty( $_POST['postmenu-menu-roles'][ $menu_item_id ] )
			) {
				$allowed_roles = array();
				foreach ( $_POST['postmenu-menu-roles'][ $menu_item_id ] as $role ) {
					$allowed_roles[] = $role;
				}
			}
			if ( empty( $allowed_roles ) ) {
				delete_post_meta( $menu_item_id, '_lp_nav_menu_roles' );
			} else {
				update_post_meta( $menu_item_id, '_lp_nav_menu_roles', $allowed_roles );
			}
		}
	}

	public function postmenu_setup_nav_menu_item( $menu_item ) {
		$roles = get_post_meta( $menu_item->ID, '_lp_nav_menu_roles', true );

		if ( ! empty( $roles ) ) {
			$menu_item->allowed_roles = $roles;
		}

		return $menu_item;
	}

	public function postmenu_get_nav_menu_items( $items ) {
		$hidden_items = array();

		// Iterate over the items to search and destroy
		foreach ( $items as $key => $item ) {

			$visible = true;

			// hide any item that is the child of a hidden item
			if ( in_array( $item->menu_item_parent, $hidden_items ) ) {
				$visible = false;
			}

			// check any item that has NMR roles set
			if ( $visible && isset( $item->allowed_roles ) ) {
				// check all logged in, all logged out, or role
				switch ( $item->allowed_roles ) {
					case 'in' :
						$visible = is_user_logged_in() ? true : false;
						break;
					case 'out' :
						$visible = ! is_user_logged_in() ? true : false;
						break;
					case 'none' :
						$visible = false;
						break;
					default:
						$visible = false;
						if ( is_array( $item->allowed_roles ) && ! empty( $item->allowed_roles ) ) {
							foreach ( $item->allowed_roles as $role ) {
								if ( current_user_can( $role ) ) {
									$visible = true;
								}
							}
						}
						break;
				}
			}
			// unset non-visible item
			if ( ! $visible ) {
				$hidden_items[] = $item->ID; // store ID of item
				unset( $items[ $key ] );
			}
		}

		return $items;
	}

	public function postmenu_admin_ajax_update_menu() {
		require_once( ABSPATH . 'wp-admin/includes/nav-menu.php' );
		$locations            = get_registered_nav_menus();
		$menu_locations       = get_nav_menu_locations();
		$nav_menu_selected_id = intval( isset( $_POST['menu'] ) ? $_POST['menu'] : - 1 );

		// Remove menu locations that have been unchecked.
		foreach ( $locations as $location => $description ) {
			if ( ( empty( $_POST['menu-locations'] ) || empty( $_POST['menu-locations'][ $location ] ) ) && isset( $menu_locations[ $location ] ) && $menu_locations[ $location ] == $nav_menu_selected_id ) {
				unset( $menu_locations[ $location ] );
			}
		}

		// Merge new and existing menu locations if any new ones are set.
		if ( isset( $_POST['menu-locations'] ) ) {
			$new_menu_locations = array_map( 'absint', $_POST['menu-locations'] );
			$menu_locations     = array_merge( $menu_locations, $new_menu_locations );
		}

		// Set menu locations.
		set_theme_mod( 'nav_menu_locations', $menu_locations );

		$_menu_object = wp_get_nav_menu_object( $nav_menu_selected_id );

		$menu_title = trim( esc_html( $_POST['menu-name'] ) );
		if ( ! $menu_title ) {
			$messages[] = '<div id="message" class="error notice is-dismissible"><p>' . __( 'Please enter a valid menu name.' ) . '</p></div>';
			$menu_title = $_menu_object->name;
		}

		if ( ! is_wp_error( $_menu_object ) ) {
			$_nav_menu_selected_id = wp_update_nav_menu_object( $nav_menu_selected_id, array( 'menu-name' => $menu_title ) );
			if ( is_wp_error( $_nav_menu_selected_id ) ) {
				$_menu_object = $_nav_menu_selected_id;
				$messages[]   = '<div id="message" class="error notice is-dismissible"><p>' . $_nav_menu_selected_id->get_error_message() . '</p></div>';
			} else {
				$_menu_object            = wp_get_nav_menu_object( $_nav_menu_selected_id );
				$nav_menu_selected_title = $_menu_object->name;
			}
		}

		// Update menu items.
		if ( ! is_wp_error( $_menu_object ) ) {
			$messages = array_merge( $messages, wp_nav_menu_update_menu_items( $_nav_menu_selected_id, $nav_menu_selected_title ) );
		}

		if ( empty( $messages ) ) {
			return '';
		} else {
			return $messages;
		}
	}

	public function postmenu_admin_ajax_delete_menu() {

		$nav_menu_selected_id = intval( isset( $_POST['menu'] ) ? $_POST['menu'] : - 1 );
		if ( is_nav_menu( $nav_menu_selected_id ) ) {
			$deletion = wp_delete_nav_menu( $nav_menu_selected_id );
		}

		if ( ! isset( $deletion ) ) {
			if ( is_wp_error( $deletion ) ) {
				$messages[] = '<div id="message" class="error notice is-dismissible"><p>' . $deletion->get_error_message() . '</p></div>';
			} else {
				$messages[] = '<div id="message" class="updated notice is-dismissible"><p>' . __( 'The menu has been successfully deleted.' ) . '</p></div>';
			}
		}
	}

	public function postmenu_admin_ajax_delete_menu_item() {
		$menu_item_id = intval( $_POST['menu-item'] );

		if ( is_nav_menu_item( $menu_item_id ) && wp_delete_post( $menu_item_id, true ) ) {
			wp_die( "1" );
		}

	}

	public static function postmenu_get_menu_items_box( $menu ) {

		$menu_items = wp_get_nav_menu_items( $menu );

		if ( ! empty( $menu_items ) ) {
			/** Lion_Posmenu_Custom_Walker_Nav_Menu class */
			require_once( plugin_dir_path( __FILE__ ) . 'class-posmenu-walker-nav-menu-edit.php' );

			$args = array(
				'after'       => '',
				'before'      => '',
				'link_after'  => '',
				'link_before' => '',
				'walker'      => new Lion_Posmenu_Walker_Nav_Menu_Edit,
			);

			return walk_nav_menu_tree( $menu_items, 0, (object) $args );
		}

		return '';
	}

	public static function postmenu_get_menu_locations_box( $menu ) {
		ob_start();
		// Retrieve menu locations.
		$locations = array();
		if ( current_theme_supports( 'menus' ) ) {
			$locations      = get_registered_nav_menus();
			$menu_locations = get_nav_menu_locations();
		}
		$page_count                  = wp_count_posts( 'page' );
		$one_theme_location_no_menus = ( 1 == count( get_registered_nav_menus() ) && empty( $nav_menus ) && ! empty( $page_count->publish ) ) ? true : false;
		?>
        <div <?php if ( $one_theme_location_no_menus ) { ?>style="display: none;"<?php } ?>>
            <h3><?php _e( 'Menu Settings' ); ?></h3>
			<?php
			if ( ! isset( $auto_add ) ) {
				$auto_add = get_option( 'nav_menu_options' );
				if ( ! isset( $auto_add['auto_add'] ) ) {
					$auto_add = false;
				} elseif ( false !== array_search( $menu, $auto_add['auto_add'] ) ) {
					$auto_add = true;
				} else {
					$auto_add = false;
				}
			} ?>

            <fieldset class="menu-settings-group auto-add-pages">
                <legend class="menu-settings-group-name howto"><?php _e( 'Auto add pages' ); ?></legend>
                <div class="menu-settings-input checkbox-input">
                    <input type="checkbox"<?php checked( $auto_add ); ?> name="auto-add-pages" id="auto-add-pages"
                           value="1"/> <label
                            for="auto-add-pages"><?php printf( __( 'Automatically add new top-level pages to this menu' ), esc_url( admin_url( 'edit.php?post_type=page' ) ) ); ?></label>
                </div>
            </fieldset>

			<?php if ( current_theme_supports( 'menus' ) ) : ?>

                <fieldset class="menu-settings-group menu-theme-locations">
                    <legend class="menu-settings-group-name howto"><?php _e( 'Display location' ); ?></legend>
					<?php foreach ( $locations as $location => $description ) : ?>
                        <div class="menu-settings-input checkbox-input">
                            <input type="checkbox"<?php checked( isset( $menu_locations[ $location ] ) && $menu_locations[ $location ] == $menu ); ?>
                                   name="menu-locations[<?php echo esc_attr( $location ); ?>]"
                                   id="locations-<?php echo esc_attr( $location ); ?>"
                                   value="<?php echo esc_attr( $menu ); ?>"/>
                            <label for="locations-<?php echo esc_attr( $location ); ?>"><?php echo $description; ?></label>
							<?php if ( ! empty( $menu_locations[ $location ] ) && $menu_locations[ $location ] != $menu ) : ?>
                                <span class="theme-location-set"><?php
									/* translators: %s: menu name */
									printf( _x( '(Currently set to: %s)', 'menu location' ),
										wp_get_nav_menu_object( $menu_locations[ $location ] )->name
									);
									?></span>
							<?php endif; ?>
                        </div>
					<?php endforeach; ?>
                </fieldset>

			<?php endif; ?>

        </div>
        <div class="major-publishing-actions wp-clearfix">
            <span class="delete-action">
                <a id="delete-menu-action" class="submitdelete deletion menu-delete"
                   href="#"><?php _e( 'Delete Menu' ); ?></a>
            </span><!-- END .delete-action -->
        </div><!-- END .major-publishing-actions -->

		<?php
		return ob_get_clean();
	}

	/**
	 * Override the Admin Menu Walker
	 * @since 1.0
	 */
	public function postmenu_edit_nav_menu_walker() {
		if ( ! class_exists( 'Lion_Posmenu_Walker_Nav_Menu_Edit' ) ) {
			require_once( plugin_dir_path( __FILE__ ) . 'class-posmenu-walker-nav-menu-edit.php' );
		}

		return 'Lion_Posmenu_Walker_Nav_Menu_Edit';
	}

	public function postmenu_nav_custom_fields( $item_id, $item, $depth, $args ) {
		$arrow_id           = "postmenu_arrow-" . $item_id;
		$field_container_id = "postmenu_field_container-" . $item_id;
		$roles_container_id = "postmenu_roles_container-" . $item_id;
		global $wp_roles;
		$all_roles = $wp_roles->role_names;

		/* Get the roles saved for the post. */
		$allowed_roles = get_post_meta( $item->ID, '_lp_nav_menu_roles', true );

		// by default nothing is checked (will match "everyone" radio)
		$user_type = 'all';
		$hidden    = "hidden";

		// specific roles are saved as an array, so "in" or an array equals "in" is checked
		if ( is_array( $allowed_roles ) || $allowed_roles == 'in' ) {
			$user_type = 'in';
			$hidden    = "";
		} else if ( $allowed_roles == 'out' ) {
			$user_type = 'out';
		} else if ( $allowed_roles == 'none' ) {
			$user_type = 'none';
		}
		?>
        <div class="postmenu-advanced-menu-item-container">
            <div class="postmenu-advanced-menu-item-bar">
                <div class="postmenu-dropdown-action">
                    <a id="<?php echo $arrow_id ?>" class="postmenu-arrow"></a>
                </div>
                <div class="postmenu-advanced-menu-item-title">
                    <h4><?php esc_html_e( "Advanced", 'postmenu' ); ?></h4>
                </div>
            </div>
            <div id="<?php echo $field_container_id ?>" class="postmenu-advanced-menu-item-fields hidden">
                <ul class="subsubsub">
                    <li><a class="postmenu-duplicate-menu-item"
                           href="#"><?php esc_html_e( "Duplicate item", 'postmenu' ); ?></a></li>
                </ul>
                <input type="hidden" name="postmenu-role-nonce"
                       value="<?php echo wp_create_nonce( 'postmenu-role-nonce' ); ?>"/>
                <label class="postmenu-label-form"><?php esc_html_e( "Display Mode", 'postmenu' ); ?></label>
                <div class="lp-col-3">
                    <label class="postmenu-label-inline">
                        <input class="postmenu-user-type" name="postmenu-user-type[<?php echo $item->ID; ?>]"
                               type="radio" <?php checked( $user_type, "in" ) ?> value="in"/>
                        <span><?php esc_html_e( "Logged in users", 'postmenu' ); ?></span>
                    </label>
                </div>
                <div class="lp-col-4">
                    <label class="postmenu-label-inline">
                        <input class="postmenu-user-type" name="postmenu-user-type[<?php echo $item->ID; ?>]"
                               type="radio" <?php checked( $user_type, "out" ) ?> value="out"/>
                        <span><?php esc_html_e( "Logged out users", 'postmenu' ); ?></span>
                    </label>
                </div>
                <div class="lp-col-2">
                    <label class="postmenu-label-inline">
                        <input class="postmenu-user-type" name="postmenu-user-type[<?php echo $item->ID; ?>]"
                               type="radio" <?php checked( $user_type, "all" ) ?> value=""/>
                        <span><?php esc_html_e( "Everyone", 'postmenu' ); ?></span>
                    </label>
                </div>
                <div class="lp-col-3">
                    <label class="postmenu-label-inline">
                        <input class="postmenu-user-type" name="postmenu-user-type[<?php echo $item->ID; ?>]"
                               type="radio" <?php checked( $user_type, "none" ) ?> value="none"/>
                        <span><?php esc_html_e( "Deactivate", 'postmenu' ); ?></span>
                    </label>
                </div>
                <div id="<?php echo $roles_container_id ?>" class="<?php echo 'postmenu-item-roles ' . $hidden; ?>">
                    <label class="postmenu-label-form"><?php esc_html_e( "Restrict menu item to a minimum role", 'postmenu' ); ?></label>
					<?php $i = 0;
					foreach ( $all_roles as $role => $display_name ) : ?>
                        <div class="lp-col-3 lp-col-sm5">
                            <label class="postmenu-label-inline">
                                <input type="checkbox"
                                       name="postmenu-menu-roles[<?php echo $item->ID; ?>][<?php echo $i; ?>]"
                                       value="<?php echo $role ?>"
									<?php checked( ( is_array( $allowed_roles ) && in_array( $role, $allowed_roles ) ), true ) ?> />
                                <span><?php echo translate_user_role( $display_name ); ?></span>
                            </label>
                        </div>
						<?php $i ++;
					endforeach; ?>
                </div>
            </div>
        </div>

	<?php }

	/**
	 * Function to generate unique names
	 *
	 * @since 1.2
	 *
	 * @param $name
	 * @param $menu
	 *
	 * @return string
	 */
	public function postmenu_generate_unique_names( $name, $menu ) {

		$name = ( $name != null && ! empty( $name ) ) ? sanitize_text_field( $name ) : $menu->name;
		$slug = sanitize_title( $name );
		if ( wp_get_nav_menu_object( $slug ) ) {
			$name = $name . "-copy";
			$name = $this->postmenu_generate_unique_names( $name, $menu );
		}
		return $name;
	}
}