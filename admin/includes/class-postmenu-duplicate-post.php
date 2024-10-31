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
class Postmenu_Duplicate_Post {

	private $post;

	public function __construct() {

	}

	/**
	 * Add the link to action list for post_row_actions
	 */
	function postmenu_make_duplicate_link_row( $actions, $post ) {

		if ( current_user_can( 'copy_posts' ) && Postmenu_Settings::is_post_type_enabled( $post->post_type ) ) {
			if ( ! current_user_can( apply_filters( 'woocommerce_duplicate_product_capability', 'manage_woocommerce' ) )
			     || $post->post_type != 'product'
			) {
				Postmenu_Admin::postmenu_array_insert( $actions, 'trash',
					[
						"lion_pm_duplicate_row_link" => '<a href="' . $this->postmenu_get_duplicate_post_link( $post->ID, 'list', 'display' ) . '" title="'
						                                . esc_attr__( "Duplicate this item", 'postmenu' )
						                                . '">' . esc_html__( 'Duplicate', 'postmenu' ) . '</a>'
					] );
			}
			if ( Postmenu_Settings::get_option( 'enable_advanced' ) == 1 ) {
				Postmenu_Admin::postmenu_array_insert( $actions, 'trash',
					[
						"lion_pm_advanced_duplicate_row_link" => '<a id="lpm-' . $post->ID . '" href="#" title="' . esc_attr__( 'Advanced Duplicate', 'postmenu' )
						                                         . '">' . esc_html__( 'Advanced Duplicate', 'postmenu' ) . '</a>'
					] );
			}
		}

		return $actions;
	}

	/**
	 * Retrieve duplicate post link for post.
	 *
	 *
	 * @param int $id Optional. Post ID.
	 * @param string $context Optional, default to display. How to write the '&', defaults to '&amp;'.
	 * @param boolean $advanced Optional, default to true
	 *
	 * @return string
	 */
	public function postmenu_get_duplicate_post_link( $id = 0, $redirect_to = 'list', $context = 'display' ) {

		if ( ! current_user_can( 'copy_posts' ) ) {
			return;
		}

		if ( ! $post = get_post( $id ) ) {
			return;
		}

		if ( ! Postmenu_Settings::is_post_type_enabled( $post->post_type ) ) {
			return;
		}

		$action_name = "postmenu_save_as_new_post";

		if ( 'display' == $context ) {
			$action = '?action=' . $action_name . '&amp;post=' . $post->ID . '&amp;redirect=' . $redirect_to;
		} else {
			$action = '?action=' . $action_name . '&post=' . $post->ID . '&redirect=' . $redirect_to;
		}

		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object ) {
			return;
		}

		return apply_filters( 'postmenu_get_duplicate_post_link', admin_url( "admin.php" . $action ), $post->ID, $context );
	}

	/*
	 * This function calls the creation of a new copy of the selected post (by default preserving the original publish status)
	* then redirects to the post list
	*/
	function postmenu_save_as_new_post( $status = '' ) {
		if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && 'postmenu_save_as_new_post' == $_REQUEST['action'] ) ) ) {
			wp_die( esc_html__( 'No post to duplicate has been supplied!', 'postmenu' ) );
		}

		// Get the original post
		$id   = ( isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post'] );
		$post = get_post( $id );

		$redirect_to = ( isset( $_GET['redirect'] ) ? $_GET['redirect'] : $_POST['redirect'] );
		// Copy the post and insert it
		if ( isset( $post ) && $post != null ) {
			$new_id = $this->postmenu_create_duplicate( $post, $status );
			if ( $status == '' ) {
				if ( $redirect_to == 'list' ) {
					$sendback = remove_query_arg( array(
						'trashed',
						'untrashed',
						'deleted',
						'cloned',
						'ids'
					), admin_url( 'edit.php?post_type=' . $post->post_type ) );
					// Redirect to the post list screen
					wp_redirect( add_query_arg( array( 'cloned' => 1, 'ids' => $post->ID ), $sendback ) );
				}
				if ( $redirect_to == 'editpost' ) {
					// Redirect to the edit screen for the new draft post
					wp_redirect( add_query_arg( array(
						'cloned' => 1,
						'ids'    => $post->ID
					), admin_url( 'post.php?action=edit&post=' . $new_id ) ) );
				}

			} else {
				// Redirect to the edit screen for the new draft post
				wp_redirect( add_query_arg( array(
					'cloned' => 1,
					'ids'    => $post->ID
				), admin_url( 'post.php?action=edit&post=' . $new_id ) ) );
			}
			exit;

		} else {
			wp_die( esc_html__( 'Copy creation failed, could not find original:', 'postmenu' ) . ' ' . htmlspecialchars( $id ) );
		}
	}

	/**
	 * Add the link to bulk action list for bulk_actions-edit
	 */
	public function postmenu_register_bulk_action( $bulk_actions ) {
		$bulk_actions['lion_pm_duplicate_bulk_action'] = esc_html__( 'Duplicate', 'postmenu' );

		return $bulk_actions;
	}

	/**
	 * Add the action handler to bulk action link
	 */
	public function postmenu_action_handler( $redirect_to, $doaction, $post_ids ) {
		if ( $doaction !== 'lion_pm_duplicate_bulk_action' ) {
			return $redirect_to;
		}
		$counter = 0;
		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );
			if ( ! empty( $post ) ) {
				if ( $this->postmenu_create_duplicate( $post ) ) {
					$counter ++;
				}
			}
		}
		$redirect_to = add_query_arg( 'cloned', $counter, $redirect_to );

		return $redirect_to;
	}

	/**
	 * Add the links in the edit add new button.
	 */
	public function postmenu_edit_form_after_editor( $post ) {
		if ( Postmenu_Settings::is_post_type_enabled( $post->post_type ) ) {
			$edit_menu_button_links = array(
				'postmenu_duplicate_dropdown_menu'      => '<a href="#" id="postmenu_duplicate_dropdown_menu" title="'
				                                           . esc_attr__( "Duplicate this item", 'postmenu' )
				                                           . '">' . esc_html__( 'Duplicate', 'postmenu' ) . '</a>',
				'postmenu_duplicate_edit_dropdown_menu' => '<a href="' . $this->postmenu_get_duplicate_post_link( $post->ID, 'editpost', 'edit' ) . '" title="'
				                                           . esc_attr__( "Duplicate & Edit", 'postmenu' )
				                                           . '">' . esc_html__( 'Duplicate & Edit', 'postmenu' ) . '</a>',
			);

			if ( Postmenu_Settings::get_option( 'enable_advanced' ) == 1 ) {
				$edit_menu_button_links['postmenu_advanced_duplicate_dropdown_menu'] = '<a href="#" id="postmenu_advanced_duplicate_dropdown_menu" title="'
				                                                                       . esc_attr__( 'Advanced Duplicate', 'postmenu' )
				                                                                       . '">' . esc_html__( 'Advanced Duplicate', 'postmenu' ) . '</a>';
			}

			echo '<script>
					LP_Scope.addEditMenuButton(' . json_encode( $edit_menu_button_links ) . ')
				</script>';
		}
	}

	/**
	 * Add the link to admin bar list for wp_before_admin_bar_render
	 */
	public function postmenu_admin_bar_render( $wp_admin_bar, $post ) {
		$current_object = get_queried_object();
		if ( empty( $current_object ) ) {
			return;
		}
		$param = 'view';
		if ( isset( $_GET['preview'] ) || isset( $_POST['preview'] ) ) {
			$param = 'preview';
		}
		if ( ! empty( $current_object->post_type )
		     && ( $post_type_object = get_post_type_object( $current_object->post_type ) )
		     && current_user_can( 'copy_posts' )
		     && ( $post_type_object->show_ui || 'attachment' == $current_object->post_type )
		     && ( Postmenu_Settings::is_post_type_enabled( $current_object->post_type ) )
		     && ( ( Postmenu_Settings::get_option( 'show_in_adminbar' ) == 1 && $param == 'view' )
		          || Postmenu_Settings::get_option( 'show_in_viewpreview' ) == 1 && $param == 'preview' )
		) {
			$wp_admin_bar->add_menu( array(
				'id'    => 'lion_pm_duplicate_admin_bar',
				'title' => '<img class="copy-post-img" 
                            title="' . esc_attr__( "Duplicate this item", 'postmenu' ) . '"
                            src="' . plugins_url( 'postmenu/admin/images/copy-post.svg' ) . '"></img>',
				'href'  => '"#"'
			) );
			echo '<script>
					document.addEventListener("DOMContentLoaded",
						function() {
							LP_Scope.handleDuplicatePostFunction(' . $current_object->ID . ', "' . esc_html__( "item duplicated", "postmenu" ) . '");
						});
				</script>';
		}
	}

	/**
	 * Create a duplicate from a post
	 */
	public function postmenu_create_duplicate( $post, $status = '', $conditions = null, $parent_id = '' ) {

		if ( ! Postmenu_Settings::is_post_type_enabled( $post->post_type ) && $post->post_type != 'attachment' ) {
			wp_die( esc_html__( 'Copy features for this post type are not enabled in options page', 'postmenu' ) );
		}

		// var_dump(get_posts(array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' => 'any', 'post_parent' => $post->ID )));
		// die;

		$new_post_status = ( empty( $status ) ) ? $post->post_status : $status;

		if ( $post->post_type != 'attachment' ) {

			if ( 'publish' == $new_post_status || 'future' == $new_post_status ) {
				// check if the user has the right capability
				if ( is_post_type_hierarchical( $post->post_type ) ) {
					if ( ! current_user_can( 'publish_pages' ) ) {
						$new_post_status = 'pending';
					}
				} else {
					if ( ! current_user_can( 'publish_posts' ) ) {
						$new_post_status = 'pending';
					}
				}
			}
		}

		$new_post_author    = wp_get_current_user();
		$new_post_author_id = $new_post_author->ID;
		if ( current_user_can( 'edit_others_pages' ) && ! empty( $post->post_author ) ) {
			$new_post_author_id = $post->post_author;
		}

		$new_post = array(
			'menu_order'            => $post->menu_order,
			'comment_status'        => $post->comment_status,
			'ping_status'           => $post->ping_status,
			'post_author'           => $new_post_author_id,
			'post_content'          => $post->post_content,
			'post_content_filtered' => $post->post_content_filtered,
			'post_excerpt'          => $post->post_excerpt,
			'post_mime_type'        => $post->post_mime_type,
			'post_parent'           => $new_post_parent = empty( $parent_id ) ? $post->post_parent : $parent_id,
			'post_password'         => $post->post_password,
			'post_status'           => $new_post_status,
			'post_title'            => ( $conditions && ! empty( $conditions ) ) ? $post->post_title : $post->post_title . '-copy',
			'post_type'             => $post->post_type,
		);

		$new_post['post_date']     = $new_post_date = $post->post_date;
		$new_post['post_date_gmt'] = get_gmt_from_date( $new_post_date );
		$new_post_id               = wp_insert_post( wp_slash( $new_post ), true );

		// If the copy is published or scheduled, we have to set a proper slug.
		if ( $new_post_status == 'publish' || $new_post_status == 'future' ) {
			if ( $conditions && ! empty( $conditions ) ) {
				$post_name = $post->post_name;
			} else {
				$post_name = $post->post_name . '-copy';
			}

			$post_name = wp_unique_post_slug( $post_name, $new_post_id, $new_post_status, $post->post_type, $new_post_parent );

			$new_post              = array();
			$new_post['ID']        = $new_post_id;
			$new_post['post_name'] = $post_name;

			// Update the post into the database
			wp_update_post( wp_slash( $new_post ) );
		}

		delete_post_meta( $new_post_id, '_dp_original' );
		add_post_meta( $new_post_id, '_dp_original', $post->ID );

		if ( $conditions && ! empty( $conditions ) ) {
			foreach ( $conditions as $feature ) {
				$this->postmenu_copy_advanced_post_features( $new_post_id, $post, $feature['name'], $feature['value'] );
			}
		} else if ( empty( $parent_id ) ) {
			$this->postmenu_copy_post_features( $new_post_id, $post );
		}

		do_action( 'postmenu_duplicate_post_type_end', $new_post_id, $post, $status, $this );

		return $new_post_id;
	}

	/**
	 * Copy parent features
	 */
	public function postmenu_copy_post_features( $post_id, $original_post ) {
		$post_taxonomies = get_object_taxonomies( $original_post->post_type );

		if ( array_search( "category", $post_taxonomies ) !== false ) {
			$categories_list = get_categories();
			$post_categories = array();
			foreach ( $categories_list as $category ) {
				if ( has_category( $category->term_id, $original_post ) ) {
					$post_categories[] = $category->cat_ID;
				}
			}
			if ( ! empty( $post_categories ) ) {
				wp_set_post_categories( $post_id, $post_categories );
			}
		}

		if ( array_search( "post_tag", $post_taxonomies ) !== false ) {
			$post_tags = wp_get_post_tags( $original_post->ID );
			$tags      = "";
			foreach ( $post_tags as $tag ) {
				$tags = $tags . $tag->name . ",";
			}
			wp_set_post_tags( $post_id, $tags );
		}

		if ( is_sticky( $original_post->ID ) ) {
			stick_post( $post_id );
		}

		$metadata = has_meta( $original_post->ID );
		foreach ( $metadata as $key => $value ) {
			if ( ! is_protected_meta( $metadata[ $key ]['meta_key'], 'post' ) && current_user_can( 'edit_post_meta', $original_post->ID, $metadata[ $key ]['meta_key'] ) ) {
				$success = add_post_meta( $post_id, $metadata[ $key ]['meta_key'], $metadata[ $key ]['meta_value'] );
			} else {
				$meta_values = get_post_custom_values( $metadata[ $key ]['meta_key'], $original_post->ID );
				foreach ( $meta_values as $meta_value ) {
					$meta_value = maybe_unserialize( $meta_value );
					add_post_meta( $post_id, $metadata[ $key ]['meta_key'], $this->postmenu_wp_slash( $meta_value ) );
				}
			}
		}

		if ( $format = get_post_format( $original_post->ID ) ) {
			set_post_format( $post_id, $format );
		}

		$this->postmenu_copy_attachments( $post_id, $original_post );

		$this->postmenu_copy_children( $post_id, $original_post );

		$this->postmenu_copy_comments( $post_id, $original_post );

	}

	/**
	 * Copy selected features
	 */
	public function postmenu_copy_advanced_post_features( $post_id, $origin_post, $feature_name, $feature_value ) {

		/**
		 * @TODO: The advance duplicate only send post_categories and post_tags as taxonomy.
		 *
		 * in postmenu-admin.js you see:
		 * var post_categories = $('[name="postmenu_post_categories"]'),
		 * post_parent = $('[name="postmenu_post_parent"]'),
		 * post_tags = $('#postmenu_post_tags'),
		 * is_sticky = $('[name="postmenu_sticky"]');
		 *
		 * To send any taxonomy you can replace the condition to:
		 *  if ( $feature_name != 'post_tags' && taxonomy_exists($feature_name) && ! empty( $feature_value ) && is_array( $feature_value ) ) {
		 *
		 *
		 */

		if ( $feature_name == 'post_categories' && taxonomy_exists('category') && ! empty( $feature_value ) && is_array( $feature_value ) ) {
			wp_set_post_terms( $post_id, $feature_value, 'category' );
		}elseif ( $feature_name == 'post_tags' && ! empty( $feature_value ) ) {
			$valid_tags = $feature_value . explode( ',' );
			foreach ( $valid_tags as $tag ) {
				if ( ! tag_exists( $tag ) ) {
					add_rewrite_tag( '%' . $tag . '%', '([^&]+)' );
				}
			}
			wp_set_post_tags( $post_id, $feature_value );
		}elseif ( $feature_name == 'is_sticky' && $feature_value == '1' ) {
			stick_post( $post_id );
		}elseif ( $feature_name == 'post_customfields' && $feature_value == '1' ) {
			$metadata = has_meta( $origin_post->ID );
			foreach ( $metadata as $key => $value ) {
				if ( ! is_protected_meta( $metadata[ $key ]['meta_key'], 'post' ) && current_user_can( 'edit_post_meta', $origin_post->ID, $metadata[ $key ]['meta_key'] ) ) {
					$success = add_post_meta( $post_id, $metadata[ $key ]['meta_key'], $metadata[ $key ]['meta_value'] );
				}
			}
		}elseif ( $feature_name == 'post_thumbnail' && $feature_value == '1' ) {
			$post_thumbnail_id = get_post_thumbnail_id( $origin_post->ID );
			set_post_thumbnail( $post_id, $post_thumbnail_id );
		}elseif ( $feature_name == 'post_template' && $feature_value == '1' ) {
			$meta_values = get_post_custom_values( '_wp_page_template', $origin_post->ID );
			foreach ( $meta_values as $meta_value ) {
				$meta_value = maybe_unserialize( $meta_value );
				add_post_meta( $post_id, '_wp_page_template', $this->postmenu_wp_slash( $meta_value ) );
			}
		}elseif ( $feature_name == 'post_format' && $feature_value == '1' ) {
			$format = get_post_format( $origin_post->ID );
			set_post_format( $post_id, $format );
		}elseif ( $feature_name == 'post_attachments' && $feature_value == '1' ) {
			$this->postmenu_copy_attachments( $post_id, $origin_post );
		}elseif ( $feature_name == 'post_children' && $feature_value == '1' ) {
			$this->postmenu_copy_children( $post_id, $origin_post );
		}elseif ( $feature_name == 'post_comments' && $feature_value == '1' ) {
			$this->postmenu_copy_comments( $post_id, $origin_post );
		}

	}

	/**
	 * Copy the attachments
	 */
	public function postmenu_copy_attachments( $post_id, $origin_post ) {
		$attachments = get_posts( array(
			'post_type'   => 'attachment',
			'numberposts' => -1,
			'post_status' => 'any',
			'post_parent' => $origin_post->ID
		) );
		foreach ( $attachments as $attachment ) {
			$url = wp_get_attachment_url( $attachment->ID );
			// Let's copy the actual file
			$tmp = download_url( $url );
			if ( is_wp_error( $tmp ) ) {
				@unlink( $tmp );
				continue;
			}

			$desc = wp_slash( $attachment->post_content );

			$file_array             = array();
			$file_array['name']     = basename( $url );
			$file_array['tmp_name'] = $tmp;
			// "Upload" to the media collection
			$new_attachment_id = media_handle_sideload( $file_array, $post_id, $desc );
			if ( is_wp_error( $new_attachment_id ) ) {
				@unlink( $file_array['tmp_name'] );
				continue;
			}

			$cloned_child = array(
				'ID'           => $new_attachment_id,
				'post_title'   => $attachment->post_title,
				'post_exceprt' => $attachment->post_title,
				'post_author'  => $origin_post->post_parent
			);
			wp_update_post( wp_slash( $cloned_child ) );

			$alt_title = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
			if ( $alt_title ) {
				update_post_meta( $new_attachment_id, '_wp_attachment_image_alt', wp_slash( $alt_title ) );
			}
		}
	}

	/**
	 * Copy children posts
	 */
	public function postmenu_copy_children( $post_id, $origin_post ) {
		$attachments = get_posts( array(
			'post_type'   => $origin_post->post_type,
			'numberposts' => - 1,
			'post_status' => 'any',
			'post_parent' => $origin_post->ID
		) );
		foreach ( $attachments as $attachment ) {
			$this->postmenu_create_duplicate( $attachment, '', null, $post_id );
		}
	}

	/**
	 * Copy comments
	 */
	public function postmenu_copy_comments( $post_id, $origin_post ) {
		$comments = get_comments( array(
			'post_id' => $origin_post->ID,
			'order'   => 'ASC',
			'orderby' => 'comment_date_gmt'
		) );

		$old_id_to_new = array();
		foreach ( $comments as $comment ) {
			//do not copy pingbacks or trackbacks
			if ( ! empty( $comment->comment_type ) ) {
				continue;
			}
			$parent                                = ( $comment->comment_parent && $old_id_to_new[ $comment->comment_parent ] ) ? $old_id_to_new[ $comment->comment_parent ] : 0;
			$commentdata                           = array(
				'comment_post_ID'      => $post_id,
				'comment_author'       => $comment->comment_author,
				'comment_author_email' => $comment->comment_author_email,
				'comment_author_url'   => $comment->comment_author_url,
				'comment_content'      => $comment->comment_content,
				'comment_type'         => '',
				'comment_parent'       => $parent,
				'user_id'              => $comment->user_id,
				'comment_author_IP'    => $comment->comment_author_IP,
				'comment_agent'        => $comment->comment_agent,
				'comment_karma'        => $comment->comment_karma,
				'comment_approved'     => $comment->comment_approved,
			);
			$commentdata['comment_date']           = $comment->comment_date;
			$commentdata['comment_date_gmt']       = get_gmt_from_date( $comment->comment_date );
			$new_comment_id                        = wp_insert_comment( $commentdata );
			$old_id_to_new[ $comment->comment_ID ] = $new_comment_id;
		}
	}

	/*
	* Workaround for inconsistent wp_slash.
	* Works only with WP 4.4+ (map_deep)
	*/
	function postmenu_addslashes_deep( $value ) {
		if ( function_exists( 'map_deep' ) ) {
			return map_deep( $value, array( $this, 'postmenu_addslashes_to_strings_only' ) );
		} else {
			return wp_slash( $value );
		}
	}

	function postmenu_addslashes_to_strings_only( $value ) {
		return is_string( $value ) ? addslashes( $value ) : $value;
	}

	function postmenu_wp_slash( $value ) {
		return $this->postmenu_addslashes_deep( $value );
	}

}