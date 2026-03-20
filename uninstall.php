<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Cleans up all data created by the plugin:
 * - Removes all 'lf_form' custom post type entries and their post meta.
 *
 * @package LandeseitenForm
 * @since   2.0.2
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

/**
 * Remove all Landeseiten Form configuration posts and associated meta data.
 */
$lf_posts = get_posts( [
	'post_type'      => 'lf_form',
	'post_status'    => 'any',
	'numberposts'    => -1,
	'fields'         => 'ids',
] );

if ( ! empty( $lf_posts ) ) {
	foreach ( $lf_posts as $post_id ) {
		// Delete all meta associated with this post
		delete_post_meta( $post_id, '_lf_gravity_form_id' );
		delete_post_meta( $post_id, '_lf_mode' );
		delete_post_meta( $post_id, '_lf_container_max_width' );
		delete_post_meta( $post_id, '_lf_enable_shadow' );
		delete_post_meta( $post_id, '_lf_accent_color' );
		delete_post_meta( $post_id, '_lf_text_color' );
		delete_post_meta( $post_id, '_lf_active_text_color' );
		delete_post_meta( $post_id, '_lf_form_bg_color' );
		delete_post_meta( $post_id, '_lf_enable_progress_bar' );
		delete_post_meta( $post_id, '_lf_progress_bar_color' );
		delete_post_meta( $post_id, '_lf_font_family' );
		delete_post_meta( $post_id, '_lf_label_font_size' );
		delete_post_meta( $post_id, '_lf_input_font_size' );
		delete_post_meta( $post_id, '_lf_btn_next_text' );
		delete_post_meta( $post_id, '_lf_btn_prev_text' );
		delete_post_meta( $post_id, '_lf_btn_submit_text' );
		delete_post_meta( $post_id, '_lf_error_required' );
		delete_post_meta( $post_id, '_lf_error_email' );
		delete_post_meta( $post_id, '_lf_error_phone' );
		delete_post_meta( $post_id, '_lf_error_url' );
		delete_post_meta( $post_id, '_lf_error_consent' );
		delete_post_meta( $post_id, '_lf_input_bg_color' );
		delete_post_meta( $post_id, '_lf_input_text_color' );
		delete_post_meta( $post_id, '_lf_input_border_color' );
		delete_post_meta( $post_id, '_lf_input_focus_bg_color' );
		delete_post_meta( $post_id, '_lf_input_focus_text_color' );
		delete_post_meta( $post_id, '_lf_input_focus_border_color' );
		delete_post_meta( $post_id, '_lf_input_border_radius' );
		delete_post_meta( $post_id, '_lf_input_height' );
		delete_post_meta( $post_id, '_lf_btn_border_radius' );
		delete_post_meta( $post_id, '_lf_btn_full_width' );
		delete_post_meta( $post_id, '_lf_primary_btn_bg' );
		delete_post_meta( $post_id, '_lf_primary_btn_text_color' );
		delete_post_meta( $post_id, '_lf_primary_btn_border' );
		delete_post_meta( $post_id, '_lf_secondary_btn_bg' );
		delete_post_meta( $post_id, '_lf_secondary_btn_text_color' );
		delete_post_meta( $post_id, '_lf_secondary_btn_border' );

		// Delete the post itself (skip trash)
		wp_delete_post( $post_id, true );
	}
}
