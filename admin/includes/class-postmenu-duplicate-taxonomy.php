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
 * @since      1.2.1
 * @package    Postmenu
 * @subpackage Postmenu/admin/includes
 * @author     Liontude <info@liontude.com>
 */
class Postmenu_Duplicate_Taxonomy {

	private $taxonomy;

	public function __construct() {

	}

	/**
	 * Add the link to action list for post_row_actions
	 */
	function postmenu_make_duplicate_link_row( $actions, $taxonomy ) {
		if ( Postmenu_Settings::is_taxonomy_enabled( $taxonomy->taxonomy ) ) {
			Postmenu_Admin::postmenu_array_insert( $actions, 'delete',
				[
					"lion_pm_duplicate_row_link" => '<a href="' . $this->postmenu_get_duplicate_taxonomy_link( $taxonomy->term_id, 'list', 'display' ) . '" title="'
					                                . esc_attr__( "Duplicate this item", 'postmenu' ) . $taxonomy->term_id
					                                . '">' . esc_html__( 'Duplicate', 'postmenu' ) . '</a>'
				] );
//			}
//	        if(Postmenu_Settings::get_option('enable_advanced') == 1){
//				Postmenu_Admin::postmenu_array_insert($actions, 'trash',
//					["lion_pm_advanced_duplicate_row_link" => '<a id="lpm-'.$post->ID.'" href="#" title="'. esc_attr__('Advanced Duplicate', 'postmenu')
//						. '">' .  esc_html__('Advanced Duplicate', 'postmenu') . '</a>']);
		}

		//}
		return $actions;
	}

	/**
	 * Retrieve duplicate post link for post.
	 *
	 * @sinse 1.2.1
	 *
	 * @param int $id Optional. Post ID.
	 * @param string $context Optional, default to display. How to write the '&', defaults to '&amp;'.
	 * @param boolean $advanced Optional, default to true
	 *
	 * @return string
	 */
	public function postmenu_get_duplicate_taxonomy_link( $id = 0, $redirect_to = 'list', $context = 'display' ) {

//		if ( !current_user_can('copy_posts') ){
//			return;
//		}
//
//		if ( !$category = get_term( $id ) ){
////			return;
//		}
		$term = get_term( $id );
		if ( ! Postmenu_Settings::is_taxonomy_enabled( $term->taxonomy ) ) {
			return;
		}

		$action_name = "postmenu_save_as_new_taxonomy";

		if ( 'display' == $context ) {
			$action = '?action=' . $action_name . '&amp;taxonomy=' . $term->taxonomy . '&amp;taxonomy_id=' . $term->term_id . '&amp;redirect=' . $redirect_to;
		} else {
			$action = '?action=' . $action_name . '&post=' . $term->term_id . '&redirect=' . $redirect_to;
		}


		return apply_filters( 'postmenu_get_duplicate_taxonomy_link', admin_url( "admin.php" . $action ), $term->term_id, $context );
	}

	/**
	 * This function calls the creation of a new copy of the selected taxonomy (by default preserving the original)
	 * then redirects to the post list
	 *
	 * @since 1.2.1
	 *
	 * @param string $status
	 */
	function postmenu_save_as_new_taxonomy( $status = '' ) {
		if ( ! ( isset( $_REQUEST['taxonomy_id'] ) || isset( $_REQUEST['taxonomy'] ) || ( isset( $_REQUEST['action'] ) && 'postmenu_save_as_new_taxonomy' == $_REQUEST['action'] ) ) ) {
			wp_die( esc_html__( 'No post to duplicate has been supplied!', 'postmenu' ) );
		}

		// Get the original Taxonomy
		$taxonomy_id   = intval( $_REQUEST['taxonomy_id'] );
		$taxonomy_name = ( $_REQUEST['taxonomy'] ); //FIXME: JCRC: Validate to string.
		$taxonomy      = get_term_by( 'id', $taxonomy_id, $taxonomy_name );

		$redirect_to = ( isset( $_GET['redirect'] ) ? $_GET['redirect'] : $_POST['redirect'] );
		// Copy the post and insert it
		if ( isset( $taxonomy ) && $taxonomy != null ) {
			$new_id = $this->postmenu_create_duplicate( $taxonomy, $status );
			if ( $status == '' ) {
				if ( $redirect_to == 'list' ) {
					$sendback = remove_query_arg( array(
						'trashed',
						'untrashed',
						'deleted',
						'cloned',
						'ids'
					), admin_url( 'edit-tags.php?taxonomy=' . $taxonomy->taxonomy ) );
					// Redirect to the post list screen
					wp_redirect( add_query_arg( array( 'cloned' => 1, 'ids' => $taxonomy->term_id ), $sendback ) );
				}
				if ( $redirect_to == 'edittaxonomy' ) {
					// Redirect to the edit screen for the new draft post
					wp_redirect( add_query_arg( array(
						'cloned' => 1,
						'ids'    => $taxonomy->term_id
					), admin_url( 'post.php?action=edit&post=' . $new_id ) ) );
				}

			} else {
				// Redirect to the edit screen for the new draft post
				wp_redirect( add_query_arg( array(
					'cloned' => 1,
					'ids'    => $taxonomy->term_id
				), admin_url( 'post.php?action=edit&post=' . $new_id ) ) );
			}
			exit;

		} else {
			wp_die( esc_html__( 'Copy creation failed, could not find original:', 'postmenu' ) . ' ' . htmlspecialchars( $taxonomy_id ) );
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
	public function postmenu_action_handler( $redirect_to, $doaction, $term_ids ) {
		if ( $doaction !== 'lion_pm_duplicate_bulk_action' ) {
			return $redirect_to;
		}
		$counter       = 0;
		$taxonomy_name = isset( $_GET['taxonomy'] ) ? $_GET['taxonomy'] : '';
		foreach ( $term_ids as $term_id ) {
			$term = get_term_by( 'id', $term_id, $taxonomy_name );
			if ( ! empty( $term ) ) {
				if ( $this->postmenu_create_duplicate( $term ) ) {
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
//		if(Postmenu_Settings::is_post_type_enabled($post->post_type)){
//			$edit_menu_button_links = array(
//				'postmenu_duplicate_dropdown_menu' => '<a href="#" id="postmenu_duplicate_dropdown_menu" title="'
//				. esc_attr__("Duplicate this item", 'postmenu')
//				. '">' .  esc_html__('Duplicate', 'postmenu') . '</a>',
//				'postmenu_duplicate_edit_dropdown_menu' => '<a href="'.$this->postmenu_get_duplicate_post_link( $post->ID, 'editpost', 'edit' ).'" title="'
//				. esc_attr__("Duplicate & Edit", 'postmenu')
//				. '">' .  esc_html__('Duplicate & Edit', 'postmenu') . '</a>',
//			);
//
//			if(Postmenu_Settings::get_option('enable_advanced') == 1){
//				$edit_menu_button_links['postmenu_advanced_duplicate_dropdown_menu'] = '<a href="#" id="postmenu_advanced_duplicate_dropdown_menu" title="'
//				. esc_attr__('Advanced Duplicate', 'postmenu')
//				. '">' .  esc_html__('Advanced Duplicate', 'postmenu') . '</a>';
//			}
//
//			echo '<script>
//					LP_Scope.addEditMenuButton('.json_encode($edit_menu_button_links).')
//				</script>';
//		}
	}

	/**
	 * Add the link to admin bar list for wp_before_admin_bar_render
	 */
	public function postmenu_admin_bar_render( $wp_admin_bar, $post ) {
//		$current_object = get_queried_object();
//		if ( empty($current_object) )
//			return;
//		$param = 'view';
//		if( isset( $_GET['preview']) || isset( $_POST['preview'])) {
//			$param = 'preview';
//		}
//		if ( ! empty( $current_object->post_type )
//			&& ( $post_type_object = get_post_type_object( $current_object->post_type ) )
//			&& current_user_can('copy_posts')
//			&& ( $post_type_object->show_ui || 'attachment' == $current_object->post_type )
//			&& (Postmenu_Settings::is_post_type_enabled($current_object->post_type) )
//			&& ((Postmenu_Settings::get_option('show_in_adminbar') == 1 && $param == 'view')
//			|| Postmenu_Settings::get_option('show_in_viewpreview') == 1 && $param == 'preview'))
//		{
//			$wp_admin_bar->add_menu( array(
//				'id' => 'lion_pm_duplicate_admin_bar',
//				'title' => '<img class="copy-post-img"
//                            title="'. esc_attr__("Duplicate this item", 'postmenu') .'"
//                            src="' . plugins_url( 'postmenu/admin/images/copy-post.svg') . '"></img>',
//				'href' => '"#"'
//			) );
//			echo '<script>
//					document.addEventListener("DOMContentLoaded",
//						function() {
//							LP_Scope.handleDuplicatePostFunction('.$current_object->ID.', "'.esc_html__("item duplicated", "postmenu").'");
//						});
//				</script>';
//		}
	}

	/**
	 * Create a duplicate from a post
	 */
	public function postmenu_create_duplicate( $taxonomy, $status = '', $conditions = null, $parent_id = '' ) {

		if ( ! Postmenu_Settings::is_taxonomy_enabled( $taxonomy->taxonomy ) ) {
			wp_die( esc_html__( 'Copy features for this taxonomy are not enabled in options term', 'postmenu' ) );
		}

		$new_taxonomy_name = $this->postmenu_generate_unique_names( $taxonomy->name, $taxonomy );

		$new_taxonomy_object = wp_insert_term(
			$new_taxonomy_name, // the term
			$taxonomy->taxonomy, // the taxonomy
			array(
				'description' => $taxonomy->description,
//                'slug' => 'apple',
				'parent'      => $taxonomy->parent
			)
		);

		// Get all term meta from original and create to a duplicate
		$taxonomy_meta = get_term_meta( $taxonomy->term_id );
		foreach ( $taxonomy_meta as $taxonomy_meta_key => $taxonomy_meta_values ) {
			foreach ( $taxonomy_meta_values as $key => $taxonomy_meta_value ) {
				add_term_meta( $new_taxonomy_object['term_id'], $taxonomy_meta_key, $taxonomy_meta_value );
			}
		}

		//Get all associate post
		$posts_inside = new WP_Query( array(
			'posts_per_page' => - 1,
			'tax_query'      => array(
				array(
					'taxonomy'         => $taxonomy->taxonomy,
					'field'            => 'id',
					'terms'            => $taxonomy->term_id,
					'include_children' => false
				)
			)
		) );

		foreach ( $posts_inside->get_posts() as $post ) {

			$post_terms = wp_get_post_terms( $post->ID, $taxonomy->taxonomy, array( 'fields' => 'ids' ) );
			array_push( $post_terms, $new_taxonomy_object['term_id'] );

			$term_taxonomy_ids = wp_set_object_terms( $post->ID, $post_terms, $taxonomy->taxonomy );

			if ( is_wp_error( $term_taxonomy_ids ) ) {
				// There was an error somewhere and the terms couldn't be set.
			} else {
				// Success! The post's categories were set.
			}
		}
	}

	/**
	 * Function to generate unique names
	 *
	 * @since 1.2
	 *
	 * @param $name
	 * @param $taxonomy
	 *
	 * @return string
	 */
	public function postmenu_generate_unique_names( $name, $taxonomy ) {

		$slug = sanitize_title( $name );
		if ( term_exists( $slug, $taxonomy->taxonomy ) ) {
			$name = $name . "-copy";
			$name = $this->postmenu_generate_unique_names( $name, $taxonomy );
		}

		return $name;
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