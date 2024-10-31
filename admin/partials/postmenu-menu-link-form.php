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
 * Add the menu link form page
 *
 * @since    1.0.0
 */
function postmenu_menu_link_form_function( $post, $rownumber ) {
	global $_wp_nav_menu_max_depth;
	$max_depth            = (int) $_wp_nav_menu_max_depth;
	$nav_menus            = wp_get_nav_menus();
	$nav_menus            = ! is_array( $nav_menus ) ? array() : $nav_menus;
	$selected_menu_object = empty( $nav_menus )
		? array( 'name' => '', 'id' => '' )
		: array( 'name' => $nav_menus[0]->name, 'id' => $nav_menus[0]->term_id );
	$post_url             = get_permalink( $post );
	$post_taxonomies      = get_object_taxonomies( $post->post_type );
	$classtype            = ( array_search( "category", $post_taxonomies ) !== false ) ? 'post' : 'page';
	$containerclass       = 'inline-edit-row inline-edit-row-' .
	                        $classtype . ' inline-edit-' . $classtype . ' quick-edit-row quick-edit-row-' .
	                        $classtype . ' inline-edit-' . $classtype . ' inline-editor';
	$containerselector    = '.inline-edit-row.inline-edit-row-' .
	                        $classtype . '.inline-edit-' . $classtype . '.quick-edit-row.quick-edit-row-' .
	                        $classtype . '.inline-edit-' . $classtype . '.inline-editor';
	$params               = array(
		'containerselector'    => $containerselector,
		'rownumber'            => $rownumber,
		'selected_menu_object' => $selected_menu_object,
		'menu_max_depth'       => $max_depth,
		'empty_menu_label'     => esc_attr__( '— Select —', 'postmenu' ),
		'success_message'      => esc_attr__( 'item added.', 'postmenu' ),
		'success_delete'       => __( 'The item has been successfully deleted.' )
	);
	//Template
	if ( $rownumber > 0 ) {
		echo '<tr class="hidden"></tr><tr class="' . $containerclass . '"><td colspan="' . $rownumber . '" class="colspanchange">';
	} else {
		echo '<div class="' . $containerclass . '"><div class="postmenu-advanced-duplicate-container">';
	}
	?>
    <form id="postmenu_advanced_publish_form" name="postmenu_advanced_duplicate_form"
          class="postmenu-advanced-duplicate-form" method="POST">
        <fieldset>
            <legend class="inline-edit-legend"><?php esc_html_e( 'Menu Link', 'postmenu' ) ?></legend>
        </fieldset>
        <fieldset>
            <div class="inline-edit-col lp-col-2">
                <label class="title-label">
                    <b><?php esc_html_e( $post->post_type, 'default' ) ?> :</b>
					<?php esc_html_e( $post->post_title, 'default' ) ?>
                </label>
                <label class="title-label sub">
                    <input id="add-to-menu-action"
                           type="button" <?php if ( empty( $nav_menus ) )
						echo 'disabled="disabled"' ?> class="button"
                           value="<?php esc_html_e( 'Add to Menu', 'default' ) ?>"/>
                </label>
            </div>
            <div class="inline-edit-col lp-col-8">
                <label class="title-label sub">
					<?php esc_html_e( 'Select a menu to use', 'postmenu' ) ?>
                </label>
                <label class="title-label sub">
                    <select name="selected-menu" <?php if ( empty( $nav_menus ) )
						echo 'disabled="disabled" style="min-width: 120px;"' ?> >
						<?php foreach ( (array) $nav_menus as $_nav_menu ) : ?>
                            <option value="<?php echo esc_attr( $_nav_menu->term_id ) ?>">
								<?php echo esc_html( $_nav_menu->name ); ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </label>
                <label class="title-label sub action">
                    <input id="select-menu-action" type="button" class="button"
                           value="<?php esc_html_e( 'Select', 'default' ) ?>" <?php if ( empty( $nav_menus ) )
						echo 'disabled="disabled"' ?> />
					<?php esc_html_e( 'or', 'default' ) ?>
                    <a id="show-create-new-menu" href="#"><?php esc_html_e( 'create a new menu', 'default' ) ?></a>
                </label>
            </div>
            <div id="nav-menu-meta">
                <input type="hidden" name="menu" id="menu"
                       value="<?php echo esc_attr( $selected_menu_object['id'] ); ?>"/>
				<?php wp_nonce_field( 'add-menu_item', 'menu-settings-column-nonce' ); ?>
                <input type="hidden" name="action" value="postmenu_admin_ajax_update_menu"/>
                <input type="hidden" class="menu-item-data-object-id" value="<?php echo esc_attr( $post->ID ); ?>"/>
                <input type="hidden" class="menu-item-data-object" value="<?php echo esc_attr( $post->post_type ); ?>"/>
                <input type="hidden" class="menu-item-data-parent-id"
                       value="<?php echo esc_attr( $post->post_parent ); ?>"/>
                <input type="hidden" class="menu-item-data-type" value="post_type"/>
                <input type="hidden" class="edit-menu-item-title" value="<?php echo esc_attr( $post->post_title ); ?>"/>
                <input type="hidden" class="edit-menu-item-url" value="<?php echo esc_attr( $post_url ); ?>"/>
            </div>
        </fieldset>
        <fieldset id="create-new-menu" style="display: none;">
            <div class="inline-edit-col">
                <label class="title-label">
					<?php esc_html_e( "Menu Name", 'default' ); ?>
                </label>
                <label class="title-label">
                    <input type="text" class="regular-text" name="postmenu_new_menu_name"/>
                </label>
                <label class="title-label sub">
                    <input id="create-new-menu-action" type="button" disabled="disabled" class="button button-primary"
                           value="<?php esc_html_e( 'Create Menu', 'default' ) ?>"/>
                </label>
            </div>
        </fieldset>
        <!-- /#menu-settings-column -->
        <fieldset id="menu-management-liquid" class="nav-menus-php <?php if ( empty( $nav_menus ) )
			echo 'hidden' ?>">
            <div class="inline-edit-col">
                <form id="update-nav-menu" method="post" enctype="multipart/form-data">
                    <div class="menu-edit">
                        <div id="post-body" style="border: 0; background: none">
                            <div id="post-body-content" class="wp-clearfix" style="margin: 0;">
                                <div class="lp-col-5">
                                    <h3><?php _e( 'Menu Structure' ); ?></h3>
									<?php $starter_copy = __( 'Click the arrow on the right of the item to reveal additional configuration options.' ); ?>
                                    <div class="drag-instructions post-body-plain"
									     <?php if ( isset( $menu_items ) && 0 == count( $menu_items ) ) { ?>style="display: none;"<?php } ?>>
                                        <p><?php echo $starter_copy; ?></p>
                                    </div>
                                    <ul class="menu" id="menu-to-edit">
										<?php if ( ! empty( $selected_menu_object['id'] ) ) {
											echo Postmenu_Duplicate_Menu::postmenu_get_menu_items_box( $selected_menu_object['id'] );
										} ?>
                                    </ul>
                                </div>
                                <div class="lp-col-5" id="menu-location-box-container">
									<?php echo Postmenu_Duplicate_Menu::postmenu_get_menu_locations_box( $selected_menu_object['id'] ); ?>
                                </div>
                            </div><!-- /#post-body-content -->
                        </div><!-- /#post-body -->
                    </div><!-- /.menu-edit -->
                </form><!-- /#update-nav-menu -->
            </div>
        </fieldset>
        <!-- /#menu-management-liquid -->
        <p class="submit inline-edit-save">
            <button type="button"
                    class="button cancel alignleft postmenu"><?php esc_html_e( 'Cancel', 'default' ) ?></button>
            <input type="hidden" id="_inline_edit" name="_inline_edit" value="9ca1c48eea">
            <button id="update-menu-action" type="submit" <?php if ( empty( $nav_menus ) )
				echo 'disabled="disabled"' ?>
                    class="button button-primary save alignright postmenu"><?php esc_html_e( 'Save Menu', 'default' ) ?></button>
            <span class="spinner postmenu"></span><input type="hidden" name="post_view" value="list">
            <input type="hidden" name="screen" value="edit-page">
            <span class="error" style="display:none"></span><br class="clear">
        </p>
    </form>
    <script>
        LP_Scope.initEditMenuForm(<?php echo json_encode( $params )?>);
    </script>
	<?php
	if ( $rownumber > 0 ) {
		echo '</tr></td>';
	} else {
		echo '</div></div>';
	}
}
