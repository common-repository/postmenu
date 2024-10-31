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
 * Add the advanced duplicate form page
 *
 * @since    1.0.0
 */
function postmenu_advanced_duplicate_form_function( $post, $rownumber ) {

	$date          = explode( " ", $post->post_date )[0];
	$time          = explode( " ", $post->post_date )[1];
	$year          = explode( "-", $date )[0];
	$month         = explode( "-", $date )[1];
	$day           = explode( "-", $date )[2];
	$hour          = explode( ":", $time )[0];
	$minute        = explode( ":", $time )[1];
	$seconds       = explode( ":", $time )[2];
	$years_options = "";
	$mm            = array(
		esc_attr__( 'Jan', 'postmenu' ),
		esc_attr__( 'Feb', 'postmenu' ),
		esc_attr__( 'Mar', 'postmenu' ),
		esc_attr__( 'Apr', 'postmenu' ),
		esc_attr__( 'May', 'postmenu' ),
		esc_attr__( 'Jun', 'postmenu' ),
		esc_attr__( 'Jul', 'postmenu' ),
		esc_attr__( 'Aug', 'postmenu' ),
		esc_attr__( 'Sep', 'postmenu' ),
		esc_attr__( 'Oct', 'postmenu' ),
		esc_attr__( 'Nov', 'postmenu' ),
		esc_attr__( 'Dec', 'postmenu' ),
	);
	$mm_number     = array( '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12' );
	for ( $i = 0; $i < 12; $i ++ ) {
		$years_options = $years_options . '<option value="' . $mm_number[ $i ] . '" data-text="' . $mm[ $i ] .
		                 '" ' . selected( $month, $mm_number[ $i ], false ) . '>' . $mm_number[ $i ] . '-' . $mm[ $i ] . '</option>';
	}
	$author_name = get_the_author_meta( 'display_name', $post->post_author );

	$post_statuses = get_post_statuses();

	//Get all object Taxonomy to a post_type
	$post_taxonomies = get_object_taxonomies( $post->post_type , 'all');

	$terms_fields = array();
    global $wp_version;

	if ( count($post_taxonomies) > 0 ) {
		foreach ( $post_taxonomies as $taxonomy ) {

		    $terms = null;
			/**
             * Prior to 4.5.0, the first parameter of get_terms() was a taxonomy or list of taxonomies
             *
			 * Since 4.5.0, taxonomies should be passed via the ‘taxonomy’ argument in the $args arra
			 */
		    if ( version_compare( $wp_version, '4.5' ) < 0 ) {
			    $terms = get_terms( $taxonomy->name, array( 'hide_empty' => false ) );
			}else{
			    $terms = get_terms( array( 'taxonomy' => $taxonomy->name, 'hide_empty' => false ) );
            }

			foreach ( $terms as $term ) {
				if ( ! array_key_exists( $taxonomy->name, $terms_fields ) ) {
					$terms_fields[ $taxonomy->name ] = '';
				}

				if ( $taxonomy->name == "post_tag") {
					$post_tags = wp_get_post_tags( $post->ID );
					foreach ( $post_tags as $tag ) {
						$terms_fields[ $taxonomy->name ] .= $tag->name . ",";
					}

				}else{
					$terms_fields[ $taxonomy->name ] .= '<li id="postmenu_' . $taxonomy->name. '-' . $term->term_id . '">
                <label class="selectit"><input value="' . $term->term_id
					                                   . '" type="checkbox" name="postmenu_post_' . $taxonomy->rest_base . '" id="in-' . $taxonomy->name . '-' . $term->term_id . '" '
					                                   . checked( has_term( $term->term_id, $taxonomy->name,$post->ID ), true, false ) . '>'
					                                   . $term->name . '</label></li>';
                }
			}

		}

	}
	$statuses_options = "";
	$statuses         = array_keys( $post_statuses );
	foreach ( $statuses as $status ) {
		if ( $status != 'private' ) {
			$statuses_options = $statuses_options . '<option value="' . $status . '" ' . selected( $status, $post->post_status, false ) . '>' . $post_statuses[ $status ] . '</option>';
		}
	}
	if ( is_post_type_hierarchical( $post->post_type ) ) {
		$args          = array(
			'sort_order'   => 'asc',
			'sort_column'  => 'post_title',
			'hierarchical' => 1,
			'exclude'      => $post->ID,
			'include'      => '',
			'meta_key'     => '',
			'meta_value'   => '',
			'authors'      => '',
			'child_of'     => 0,
			'parent'       => - 1,
			'exclude_tree' => '',
			'number'       => '',
			'offset'       => 0,
			'post_type'    => $post->post_type,
			'post_status'  => 'publish'
		);
		$pages         = get_pages( $args );
		$pages_options = '<option value="0">' . esc_attr__( 'Main Page (no parent)', 'default' ) . '</option>';
		foreach ( $pages as $page ) {
			$pages_options = $pages_options . '<option value="' . $page->ID . '" ' . selected( $page->ID, $post->post_parent, false ) . '>' . $page->post_title . '</option>';
		}
	}

	$default_properties_to_copy = array(
		array( 'tax' => 'post_excerpt', 'tag' => esc_html__( "Excerpt", 'default' ) ),
		array( 'tax' => 'post_content', 'tag' => esc_html__( "Content", 'default' ) ),
		array( 'tax' => 'post_author', 'tag' => esc_html__( "Author", 'default' ) ),
		array( 'tax' => 'post_attachments', 'tag' => esc_html__( "Attachments", 'postmenu' ) ),
		array( 'tax' => 'post_children', 'tag' => esc_html__( "Childen", 'postmenu' ) ),
		array( 'tax' => 'post_comments', 'tag' => esc_html__( "Comments", 'default' ) ),
	);
	if ( get_post_format( $post->ID ) ) {
		$default_properties_to_copy[] = array( 'tax' => 'post_format', 'tag' => esc_html__( "Format", 'default' ) );
	}

	$metadata = has_meta( $post->ID );
	foreach ( $metadata as $key => $value ) {
		if ( is_protected_meta( $metadata[ $key ]['meta_key'], 'post' ) || ! current_user_can( 'edit_post_meta', $post->ID, $metadata[ $key ]['meta_key'] ) ) {
			unset( $metadata[ $key ] );
		}
		if ( $key == '_thumbnail_id' ) {
			$default_properties_to_copy[] = array(
				'tax' => 'post_thumbnail',
				'tag' => esc_html__( "Featured Image", 'default' )
			);
		}
		if ( $key == '_wp_page_template' ) {
			$default_properties_to_copy[] = array(
				'tax' => 'post_template',
				'tag' => esc_html__( "Template", 'default' )
			);
		}
	}

	if ( ! empty( $metadata ) ) {
		$default_properties_to_copy[] = array(
			'tax' => 'post_customfields',
			'tag' => esc_html__( "Custom Fields", 'postmenu' )
		);
	}

	$classtype         = ( array_search( "category", array_column($post_taxonomies, 'name') ) !== false ) ? 'post' : 'page';
	$containerclass    = 'inline-edit-row inline-edit-row-' .
	                     $classtype . ' inline-edit-' . $classtype . ' quick-edit-row quick-edit-row-' .
	                     $classtype . ' inline-edit-' . $classtype . ' inline-editor';
	$containerselector = '.inline-edit-row.inline-edit-row-' .
	                     $classtype . '.inline-edit-' . $classtype . '.quick-edit-row.quick-edit-row-' .
	                     $classtype . '.inline-edit-' . $classtype . '.inline-editor';

	//Template
	if ( $rownumber > 0 ) {
		echo '<tr class="hidden"></tr><tr class="' . $containerclass . '"><td colspan="' . $rownumber . '" class="colspanchange">';
	} else {
		echo '<div class="' . $containerclass . '"><div class="postmenu-advanced-duplicate-container">';
	}
	?>
    <form id="postmenu_advanced_duplicate_form" name="postmenu_advanced_duplicate_form"
          class="postmenu-advanced-duplicate-form" method="POST">
        <fieldset class="inline-edit-col-left">
            <legend class="inline-edit-legend"><?php esc_html_e( 'Advanced Duplicate', 'postmenu' ) ?></legend>
            <div class="inline-edit-col">
                <input type="hidden" id="postmenu_post_id" name="postmenu_post_id" value="<?php echo $post->ID ?>">
                <label>
                    <span class="title"><?php esc_html_e( 'Title', 'default' ) ?></span>
                    <span class="input-text-wrap">
                        <input type="text" name="postmenu_post_title" class="ptitle"
                               value="<?php echo $post->post_title ?>">
                    </span>
                </label>
                <label>
                    <span class="title"><?php esc_html_e( 'Slug', 'default' ) ?></span>
                    <span class="input-text-wrap"><input type="text" name="postmenu_post_name" class="ptitle"
                                                         value="<?php echo $post->post_name ?>"></span>
                </label>
                <fieldset class="inline-edit-date">
                    <legend><span class="title"><?php esc_html_e( 'Date', 'default' ) ?></span></legend>
                    <div class="timestamp-wrap">
                        <label><span class="screen-reader-text"><?php esc_html_e( 'Month', 'default' ) ?></span>
                            <select name="postmenu_mm"><?php echo $years_options ?></select></label>
                        <label><span class="screen-reader-text"><?php esc_html_e( 'Day', 'default' ) ?></span>
                            <input type="text" name="postmenu_dd" size="2" maxlength="2" autocomplete="off"
                                   value="<?php echo $day ?>"></label>,
                        <label><span class="screen-reader-text"><?php esc_html_e( 'Year', 'default' ) ?></span>
                            <input type="text" name="postmenu_aa" size="4" maxlength="4" autocomplete="off"
                                   value="<?php echo $year ?>"></label> @
                        <label><span class="screen-reader-text"><?php esc_html_e( 'Hour', 'default' ) ?></span>
                            <input type="text" name="postmenu_hh" size="2" maxlength="2" autocomplete="off"
                                   value="<?php echo $hour ?>"></label>:
                        <label><span class="screen-reader-text"><?php esc_html_e( 'Minute', 'default' ) ?></span>
                            <input type="text" name="postmenu_mn" size="2" maxlength="2" autocomplete="off"
                                   value="<?php echo $minute ?>"></label>
                    </div>
                    <input type="hidden" id="ss" name="postmenu_ss" value="<?php echo $seconds ?>">
                </fieldset>
                <br class="clear">
                <label class="inline-edit-author" style="display: none;">
                    <span class="title"><?php esc_html_e( 'Author', 'default' ) ?></span><select
                            name="postmenu_post_author" class="authors">
                        <option
						<?php echo 'value="' . $post->post_author . '">' . $author_name; ?></option></select></select>
                </label>
                <div class="inline-edit-group wp-clearfix">
                    <label class="alignleft">
                        <span class="title"><?php esc_html_e( 'Password', 'default' ) ?></span>
                        <span class="input-text-wrap">
                            <input type="text" name="postmenu_post_password" class="inline-edit-password-input"
                                   value="<?php echo $post->post_password ?>"></span>
                    </label>
                    <em class="alignleft inline-edit-or">
						<?php esc_html_e( '-OR-', 'postmenu' ) ?> </em>
                    <label class="alignleft inline-edit-private">
                        <input type="checkbox"
                               name="postmenu_keep_private" <?php checked( $post->post_status, "private" ); ?>
                               value="private">
                        <span class="checkbox-title"><?php esc_html_e( 'Private', 'default' ) ?></span>
                    </label>
                </div>
            </div>
        </fieldset>
<!--		--><?php //if ( array_search( "category", array_column($post_taxonomies, 'name') ) !== false ) { ?>
<!--            <fieldset class="inline-edit-col-center inline-edit-categories">-->
<!--                <div class="inline-edit-col">-->
<!--                    <span class="title inline-edit-categories-label">--><?php //esc_html_e( 'Categories', 'default' ) ?><!--</span>-->
<!--                    <input type="hidden" name="post_category[]" value="0">-->
<!--                    <ul class="cat-checklist category-checklist">-->
<!--						--><?php //echo $categories_fields ?>
<!--                    </ul>-->
<!--                </div>-->
<!--            </fieldset>-->
<!--		--><?php //} ?>
        <?php foreach ($post_taxonomies as $taxonomy): ?>
            <?php if($taxonomy->name === "post_tag" || $taxonomy->name === "post_format"){ continue; } ?>
            <fieldset class="inline-edit-col-center inline-edit-<?php echo $taxonomy->rest_base; ?>">
                <div class="inline-edit-col">
                    <span class="title inline-edit-<?php echo $taxonomy->rest_base; ?>-label"><?php echo $taxonomy->label; ?></span>
<!--                    <input type="hidden" name="--><?php //echo $taxonomy->name; ?><!--[]" value="0">-->
                    <input type="hidden" name="post_category[]" value="0">
                    <ul class="cat-checklist <?php echo $taxonomy->name; ?>-checklist">
					    <?php echo $terms_fields[$taxonomy->name]; ?>
                    </ul>
                </div>
            </fieldset>
        <?php endforeach; ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
				<?php if ( is_post_type_hierarchical( $post->post_type ) ) { ?>
                    <label>
                        <span class="title"><?php esc_html_e( 'Parent', 'default' ) ?></span>
                        <select name="postmenu_post_parent" id="post_parent">
							<?php echo $pages_options ?>
                        </select>
                    </label>
                    <label>
                        <span class="title"><?php esc_html_e( 'Order', 'default' ) ?></span>
                        <span class="input-text-wrap">
                        <input type="text" name="postmenu_menu_order" class="inline-edit-menu-order-input"
                               value="<?php echo $post->menu_order ?>">
                    </span>
                    </label>
				<?php }
				if ( array_search( "post_tag", array_column($post_taxonomies, 'name') ) !== false ) { ?>
                    <label class="inline-edit-tags">
                        <span class="title"><?php echo $post_taxonomies['post_tag']->label; ?></span>
                        <textarea data-wp-taxonomy="post_tag" cols="22" rows="1" id="postmenu_post_tags"
                                  name="tax_input[post_tag]" class="tax_input_post_tag ui-autocomplete-input"
                                  autocomplete="off"
                                  role="combobox" aria-autocomplete="list" aria-expanded="false"
                                  aria-owns="ui-id-1"><?php echo $terms_fields['post_tag']; ?></textarea>
                    </label>

				<?php } ?>
                <div class="inline-edit-group wp-clearfix">
                    <label class="alignleft">
                        <input type="checkbox" name="postmenu_comment_status"
                               value="open" <?php checked( $post->comment_status, "open" ); ?> >
                        <span class="checkbox-title"><?php esc_html_e( 'Allow Comments', 'default' ) ?></span>
                    </label>
					<?php if ( $post->ping_status == "open" ) { ?>
                        <label class="alignleft">
                            <input type="checkbox" name="postmenu_ping_status"
                                   value="open" <?php checked( $post->ping_status, "open" ); ?> >
                            <span class="checkbox-title"><?php esc_html_e( 'Allow Pings', 'default' ) ?></span>
                        </label>
					<?php } ?>
                </div>
                <div class="inline-edit-group wp-clearfix">
                    <label class="inline-edit-status alignleft">
                        <span class="title"><?php esc_html_e( 'Status', 'default' ) ?></span>
                        <select name="postmenu_post_status"><?php echo $statuses_options ?></select>
                    </label>
					<?php if ( $post->post_type == "post" ) { ?>
                        <label class="alignleft">
                            <input type="checkbox" name="postmenu_sticky"
                                   value="sticky" <?php checked( is_sticky( $post->ID ), true ); ?> >
                            <span class="checkbox-title"><?php esc_html_e( 'Make this post sticky', 'default' ) ?></span>
                        </label>
					<?php } ?>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <div class="inline-edit-col">
                <div class="inline-edit-group wp-clearfix">
                    <label class="inline-edit-status alignleft">
                        <span class="title"><?php esc_html_e( 'To copy', 'postmenu' ) ?></span>
                    </label>
					<?php foreach ( $default_properties_to_copy as $prop ) : ?>
                        <label class="alignleft" style="margin-right: .5em;">
                            <input type="checkbox" class="postmenu_default_properties_to_copy"
                                   value="1" <?php echo 'name="' . $prop['tax'] . '"' ?>>
                            <span class="checkbox-title"><?php echo $prop['tag'] ?></span>
                        </label>
					<?php endforeach; ?>
                </div>
            </div>
        </fieldset>
        <p class="submit inline-edit-save">
            <button type="button"
                    class="button cancel alignleft postmenu"><?php esc_html_e( 'Cancel', 'default' ) ?></button>
            <input type="hidden" id="_inline_edit" name="_inline_edit" value="9ca1c48eea">
            <button type="submit"
                    class="button button-primary save alignright postmenu"><?php esc_html_e( 'Duplicate', 'postmenu' ) ?></button>
            <span class="spinner postmenu"></span><input type="hidden" name="post_view" value="list">
            <input type="hidden" name="screen" value="edit-page">
            <span class="error" style="display:none"></span><br class="clear">
        </p>
    </form>
    <script>
		<?php
		/**
         * To avoid .wpTagsSuggest js function on versions < 4.7.
         *
		 * The .wpTagsSuggest js function was implemented by Wordpress in the version 4.7,
         *
		 */
		global $wp_version;
		if ( version_compare( $wp_version, '4.7' ) >= 0 ): ?>
           jQuery('#postmenu_post_tags').wpTagsSuggest();
		<?php endif; ?>

        var copy_post = <?php echo json_encode( $post ); ?>;
        jQuery('.button.cancel.postmenu').on('click', function () {
            jQuery(<?php echo "'" . $containerselector . "'" ?>).remove();
            if (LP_Scope.selector) {
                jQuery(LP_Scope.selector).show();
                jQuery('tr.hidden').remove();
            }
        });
        jQuery('#postmenu_advanced_duplicate_form').on('submit', function () {
            var spiner = jQuery('.spinner.postmenu');
            spiner.css('visibility', 'visible');
            jQuery('.button.button-primary.save.alignright.postmenu').attr('disabled', 'disabled');
            LP_Scope.saveAdvancedDuplicateForm(copy_post,
                function () {
                    jQuery(<?php echo "'" . $containerselector . "'" ?>).remove();
                    if (LP_Scope.selector) {
                        jQuery(LP_Scope.selector).show();
                    }
					<?php
					if($rownumber > 0){
					echo 'window.location.reload();';
				} else {
					?>
                    jQuery("hr.wp-header-end").after('<div id="message" class="updated notice is-dismissible">' +
                        '<p>' + postmenu_success_message + '</p>' +
                        '<button type="button" class="notice-dismiss">' +
                        '<span class="screen-reader-text">Dismiss this notice.</span>' +
                        '</button>' +
                        '</div>');

                    setTimeout(function () {
                        jQuery("#message").remove();
                    }, 5000);
                    jQuery(document).on("click", ".notice-dismiss", function () {
                        jQuery("#message").remove();
                    });
					<?php } ?>
                }
            );
            return false;
        })
    </script>

	<?php
	if ( $rownumber > 0 ) {
		echo '</tr></td>';
	} else {
		echo '</div></div>';
	}
}
