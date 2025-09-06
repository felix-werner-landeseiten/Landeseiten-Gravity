<?php
/**
 * Registers the Custom Post Type for Landeseiten Forms.
 *
 * @package LandeseitenForm
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Registers the 'lf_form' custom post type.
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
        'public'             => false, // We don't need a public front-end page for these.
        'publicly_queryable' => false,
        'show_ui'            => true,  // Show in the admin area.
        'show_in_menu'       => true,
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 20, // Position below "Pages".
        'menu_icon'          => 'dashicons-clipboard', // An icon from WordPress Dashicons.
        'supports'           => [ 'title' ], // We only need a title for now.
    ];

    register_post_type( 'lf_form', $args );
}
add_action( 'init', 'lf_register_post_type' );