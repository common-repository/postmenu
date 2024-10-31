<?php

/**
 * @version 1.4.0
 *
 */

add_action( 'admin_init', 'postmenu_post_type_wpml_init' );

function postmenu_post_type_wpml_init() {
	if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
		add_action( 'postmenu_duplicate_post_type_end', 'postmenu_post_type_wpml_duplicate_translations', 10, 4 );
	}
}

function postmenu_post_type_wpml_duplicate_translations( $post_id, $post, $status = '', $obj ) {
	global $sitepress;

	remove_action( 'postmenu_duplicate_post_type_end', 'postmenu_post_type_wpml_duplicate_translations', 10 );
	$current_language = $sitepress->get_current_language();
	$trid             = $sitepress->get_element_trid( $post->ID );
	if ( ! empty( $trid ) ) {
		$translations = $sitepress->get_element_translations( $trid );
		$new_trid     = $sitepress->get_element_trid( $post_id );
		foreach ( $translations as $code => $details ) {
			if ( $code != $current_language ) {
				$translation   = get_post( $details->element_id );
				$new_post_id_t = $obj->postmenu_create_duplicate( $translation, $status, $conditions = null, $parent_id = '' );
				$sitepress->set_element_language_details( $new_post_id_t, 'post_' . $translation->post_type, $new_trid, $code, $current_language );
			}
		}
	}
}
?>