<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://liontude.com/postmenu/
 * @since      1.0.0
 *
 * @package    Postmenu
 * @subpackage Postmenu/admin/partials
 */

/**
 * Add the option page
 *
 * @since    1.0.0
 */
function postmenu_options( $default = false ) {

	global $wp_roles;
	$roles            = $wp_roles->get_names();
	$postmenu_options = Postmenu_Settings::get_options();
	$nav_menus        = wp_get_nav_menus();
	$error_message    = "";

	if ( current_user_can( 'promote_users' ) && ( ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true ) || $default == true ) ) {

//        $dp_roles = $postmenu_options['roles'];
//        $dp_roles =  ( $dp_roles == "" ) ? $dp_roles = array() : $dp_roles;

		Postmenu_Settings::update_roles();
	}
	?>
    <div class="wrap">

        <ul class="subsubsub" style="float: right;">
            <li>
                <a href="https://liontude.com"><?php esc_html_e( "Rate", 'postmenu' ); ?></a> |
            </li>
            <li>
                <a href="https://liontude.com/support"><?php esc_html_e( "Support", 'postmenu' ); ?></a> |
            </li>
            <li>
                <a href="https://liontude.com/#contact"><?php esc_html_e( "Contact", 'postmenu' ); ?></a> |
            </li>
            <li>
                <a href="https://liontude.com/postmenu"><?php esc_html_e( "Documentation", 'postmenu' ); ?></a>
            </li>
        </ul>
        <h1>
			<?php esc_html_e( "Postmenu", 'postmenu' ); ?>
        </h1>

        <div id="message" class="updated" style="display:none;"><p></p></div>

        <form method="post" action="options.php" style="clear: both">
			<?php settings_fields( 'postmenu_group' ); ?>

            <h2 class="postmenu-nav-tab-wrapper">
                <a class="nav-tab nav-tab-active"
                   href=""><?php esc_html_e( 'Posts', 'postmenu' ); ?>
                </a> <a class="nav-tab"
                        href=""><?php esc_html_e( 'Menus', 'postmenu' ); ?>
                </a>
            </h2>

            <section>

                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e( "Display in", 'postmenu' ); ?>
                        </th>
                        <td>
                            <label class="postmenu-label-form" for="postmenu_options_show_in_postlist">
                                <input type="checkbox" name="postmenu_options[show_in_postlist]"
									<?php checked( Postmenu_Settings::get_option( 'show_in_postlist' ), 1 ); ?>
                                       id="postmenu_options_show_in_postlist" value="1"/>
								<?php esc_html_e( "Post list", 'postmenu' ); ?>
                            </label>

                            <label class="postmenu-label-form" for="postmenu_options_show_in_editscreen">
                                <input type="checkbox" name="postmenu_options[show_in_editscreen]"
									<?php checked( Postmenu_Settings::get_option( 'show_in_editscreen' ), 1 ); ?>
                                       id="postmenu_options_show_in_editscreen" value="1"/>
								<?php esc_html_e( "Edit screen", 'postmenu' ); ?>
                            </label>

                            <label class="postmenu-label-form" for="postmenu_options_show_in_adminbar">
                                <input type="checkbox" name="postmenu_options[show_in_adminbar]"
									<?php checked( Postmenu_Settings::get_option( 'show_in_adminbar' ), 1 ); ?>
                                       id="postmenu_options_show_in_adminbar" value="1"/>
								<?php esc_html_e( "Admin bar", 'postmenu' ); ?>
                            </label>

							<?php global $wp_version;
							if ( version_compare( $wp_version, '4.7' ) >= 0 ) { ?>

                                <label class="postmenu-label-form" for="postmenu_options_show_in_bulkactions">
                                    <input type="checkbox" name="postmenu_options[show_in_bulkactions]"
										<?php checked( Postmenu_Settings::get_option( 'show_in_bulkactions' ), 1 ); ?>
                                           id="postmenu_options_show_in_bulkactions" value="1"/>
									<?php esc_html_e( "Bulk Actions", 'default' ); ?>
                                </label>

							<?php } ?>

                            <label class="postmenu-label-form" for="postmenu_options_show_in_viewpreview">
                                <input type="checkbox" name="postmenu_options[show_in_viewpreview]"
									<?php checked( Postmenu_Settings::get_option( 'show_in_viewpreview' ), 1 ); ?>
                                       id="postmenu_options_show_in_viewpreview" value="1"/>
								<?php esc_html_e( "View Preview", 'postmenu' ); ?>
                            </label>

                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( "Enable", 'postmenu' ); ?>
                        </th>
                        <td>
                            <label class="postmenu-label-form" for="postmenu_options_enable_advanced">
                                <input type="checkbox" name="postmenu_options[enable_advanced]"
									<?php checked( Postmenu_Settings::get_option( 'enable_advanced' ), 1 ); ?>
                                       id="postmenu_options_enable_advanced" value="1"/>
								<?php esc_html_e( "Advanced Duplicate", 'postmenu' ); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( "Enable for these post types", 'postmenu' ); ?>
                        </th>
                        <td>
							<?php $post_types = get_post_types( array( 'show_ui' => true ), 'objects' );
							foreach ( $post_types as $post_type_object ) :
								if ( $post_type_object->name == 'attachment' ) {
									continue;
								}
								$field_name = 'postmenu_options_types_enabled_' . $post_type_object->name ?>

                                <label class="postmenu-label-form" for="<?php echo $field_name ?>">
                                    <input type="checkbox" name="postmenu_options[types_enabled][]"
                                           id="<?php echo $field_name ?>" value="<?php echo $post_type_object->name ?>"
										<?php checked( Postmenu_Settings::is_post_type_enabled( $post_type_object->name ), true ); ?> />
									<?php echo $post_type_object->labels->name ?>
                                </label>

							<?php endforeach; ?>
                            <span class="description">
                        <?php esc_html_e( "Select the post types you want the plugin to be enabled", 'postmenu' ); ?>
                                <br/>
								<?php esc_html_e( "Whether the links are displayed for custom post types registered by themes or plugins depends on their use of standard WordPress UI elements", 'postmenu' ); ?>
                    </span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( "Enable for these taxonomy", 'postmenu' ); ?>
                        </th>
                        <td>
							<?php
							$taxonomies = get_taxonomies( [], 'objects' );

							foreach ( $taxonomies as $taxonomy ) :
                                if( in_array( $taxonomy->name , array('nav_menu', 'link_category', 'post_format'))){ continue; }

								$field_name = 'postmenu_options_taxonomy_enabled_' . $taxonomy->name ?>

                                <label class="postmenu-label-form" for="<?php echo $field_name ?>">
                                    <input type="checkbox" name="postmenu_options[taxonomy_enabled][]"
                                           id="<?php echo $field_name; ?>" value="<?php echo $taxonomy->name; ?>"
										<?php checked( Postmenu_Settings::is_taxonomy_enabled( $taxonomy->name ), true ); ?> />
									<?php echo $taxonomy->label . ' ( <i>' . ucwords( str_replace( '_', ' ', implode(', ', $taxonomy->object_type) )) . ' </i>)'; ?>
                                </label>

							<?php endforeach; ?>
                            <span class="description">
                        <?php esc_html_e( "Select the taxonomy you want the plugin to be enabled", 'postmenu' ); ?><br/>
                    </span>
                        </td>
                    </tr>
					<?php if ( current_user_can( 'promote_users' ) ) { ?>
                        <tr>
                            <th scope="row"><?php esc_html_e( "Roles allowed to copy", 'postmenu' ); ?>
                            </th>
                            <td>
								<?php foreach ( $roles as $name => $display_name ): $role = get_role( $name );
									if ( ! $role->has_cap( 'edit_posts' ) ) {
										continue;
									} ?>

                                    <label class="postmenu-label-form">
                                        <input type="checkbox" name="postmenu_options[roles][]"
                                               value="<?php echo $name ?>"
											<?php checked( $role->has_cap( 'copy_posts' ), true ); ?> />
										<?php echo translate_user_role( $display_name ); ?>
                                    </label>

								<?php endforeach; ?>
                                <span class="description">
                        <?php esc_html_e( "Warning: users will be able to copy all posts, even those of other users", 'postmenu' ); ?>
                                    <br/>
									<?php esc_html_e( "Passwords and contents of password-protected posts may become visible to undesired users and visitors", 'postmenu' ); ?>
                    </span>
                            </td>
                        </tr>
					<?php } ?>
                    </tbody>

                </table>
            </section>
            <section style="display: none">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( "Select Menu to duplicate", 'postmenu' ); ?>
                        </th>
                        <td>
							<?php if ( empty( $nav_menus ) ) : ?>
                                <p><?php esc_html_e( "You haven't created any Menus yet.", 'postmenu' ); ?></p>
							<?php else: ?>
                                <select class="regular-text" name="postmenu_selected_menu">
									<?php foreach ( (array) $nav_menus as $_nav_menu ) : ?>
                                        <option value="<?php echo esc_attr( $_nav_menu->term_id ) ?>">
											<?php echo esc_html( $_nav_menu->name ); ?>
                                        </option>
									<?php endforeach; ?>
                                </select>
							<?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( "New Menu Name", 'postmenu' ); ?>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="postmenu_new_menu_name"/>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <input type="button" class="button action" id="postmenu_duplicate_menu"
                                   value="<?php esc_html_e( 'Duplicate Menu', 'postmenu' ) ?>"
								<?php if ( empty( $nav_menus ) )
									echo 'disabled="disabled"' ?> />
                        </th>
                        <td></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( "Enable", 'postmenu' ); ?>
                        </th>
                        <td><label><input type="checkbox" name="postmenu_options[enable_menu_link]"
                                          value="1" <?php checked( Postmenu_Settings::get_option( 'enable_menu_link' ), 1 ); ?> />
								<?php esc_html_e( "Menu Link", 'postmenu' ); ?> </label>
                        </td>
                    </tr>
                </table>
            </section>
            <p class="submit">
                <input type="submit" class="button-primary"
                       value="<?php esc_html_e( 'Save Changes', 'postmenu' ) ?>"/>
            </p>

        </form>
    </div>
    <script>
        jQuery('#postmenu_duplicate_menu').on('click', function () {
            var id = jQuery('[name="postmenu_selected_menu"]').val(),
                name = jQuery('[name="postmenu_new_menu_name"]').val();
            if (!id || !name) {
                jQuery('#message').show();
                jQuery('#message p').text('<?php esc_html_e( "There is an empty field.", "postmenu" ); ?>');
                setTimeout(function () {
                    jQuery('#message').hide();
                }, 2000);
            } else {
                LP_Scope.duplicateMenu(id, name, function (data) {
                    if (data == 'error') {
                        jQuery('#message').show();
                        jQuery('#message p').text('<?php esc_html_e( "This menu name already exist, chose another.", "postmenu" ); ?>');
                        setTimeout(function () {
                            jQuery('#message').hide();
                        }, 2000);

                    } else if (!data) {
                        jQuery('#message').show();
                        jQuery('#message p').text('<?php esc_html_e( "There was a problem duplicating your menu. No action was taken.", "postmenu" ); ?>');
                        setTimeout(function () {
                            jQuery('#message').hide();
                        }, 2000);
                    } else {
                        jQuery('[name="postmenu_selected_menu"]').append('<option value="' + data + '">' + name + '</option>');
                        jQuery('#message').show();
                        jQuery('#message p').text(postmenu_success_message);
                        setTimeout(function () {
                            jQuery('#message').hide();
                        }, 2000);
                    }
                })
            }
        });

    </script>
	<?php
}


