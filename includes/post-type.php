<?php
/**
 * Registers the Custom Post Type for Landeseiten Forms.
 *
 * This file handles the registration of the 'lf_form' post type,
 * which is used to store configuration settings for individual
 * Gravity Forms integrations.
 *
 * @package LandeseitenForm
 * @since 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Registers the 'lf_form' custom post type.
 *
 * This CPT is not public; it serves as a configuration container
 * accessible only via the WordPress Admin interface.
 *
 * @since 1.0.0
 * @return void
 */
function lf_register_post_type() {

	$labels = [
		'name'               => _x( 'Landeseiten Forms', 'post type general name', 'landeseiten-form' ),
		'singular_name'      => _x( 'Landeseiten Form', 'post type singular name', 'landeseiten-form' ),
		'menu_name'          => _x( 'Landeseiten Forms', 'admin menu', 'landeseiten-form' ),
		'name_admin_bar'     => _x( 'Landeseiten Form', 'add new on admin bar', 'landeseiten-form' ),
		'add_new'            => _x( 'Add New', 'landeseiten-form', 'landeseiten-form' ),
		'add_new_item'       => __( 'Add New Landeseiten Form', 'landeseiten-form' ),
		'new_item'           => __( 'New Landeseiten Form', 'landeseiten-form' ),
		'edit_item'          => __( 'Edit Landeseiten Form', 'landeseiten-form' ),
		'view_item'          => __( 'View Landeseiten Form', 'landeseiten-form' ),
		'all_items'          => __( 'All Landeseiten Forms', 'landeseiten-form' ),
		'search_items'       => __( 'Search Landeseiten Forms', 'landeseiten-form' ),
		'not_found'          => __( 'No forms found.', 'landeseiten-form' ),
		'not_found_in_trash' => __( 'No forms found in Trash.', 'landeseiten-form' ),
	];

	$args = [
		'labels'             => $labels,
		'description'        => __( 'Configuration settings for Landeseiten multi-step forms.', 'landeseiten-form' ),
		'public'             => false, // Not accessible via frontend URL.
		'publicly_queryable' => false,
		'show_ui'            => true,  // Visible in Admin Dashboard.
		'show_in_menu'       => true,
		'query_var'          => false,
		'rewrite'            => false,
		'capability_type'    => 'lf_form',
		'map_meta_cap'       => true,
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 20, // Position below "Pages".
		'menu_icon'          => 'dashicons-clipboard',
		'supports'           => [ 'title' ],
		'show_in_rest'       => false,
	];

	register_post_type( 'lf_form', $args );
}
add_action( 'init', 'lf_register_post_type' );

/**
 * Grants administrators the custom capabilities for managing Landeseiten Forms.
 *
 * Runs on plugin activation or admin_init to ensure caps are always set.
 *
 * @since 2.0.2
 */
function lf_add_admin_capabilities() {
	$role = get_role( 'administrator' );
	if ( ! $role ) {
		return;
	}

	$caps = [
		'edit_lf_form',
		'read_lf_form',
		'delete_lf_form',
		'edit_lf_forms',
		'edit_others_lf_forms',
		'publish_lf_forms',
		'read_private_lf_forms',
		'delete_lf_forms',
		'delete_private_lf_forms',
		'delete_published_lf_forms',
		'delete_others_lf_forms',
		'edit_private_lf_forms',
		'edit_published_lf_forms',
	];

	foreach ( $caps as $cap ) {
		if ( ! $role->has_cap( $cap ) ) {
			$role->add_cap( $cap );
		}
	}
}
add_action( 'admin_init', 'lf_add_admin_capabilities' );