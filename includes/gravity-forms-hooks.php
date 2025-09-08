<?php
/**
 * Handles the integration with Gravity Forms.
 *
 * @package LandeseitenForm
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * A helper class to safely collect and print dynamic CSS.
 */
final class LF_Dynamic_CSS {
    private static $css = '';

    public static function add( $style_string ) {
        self::$css .= $style_string;
    }

    public static function print_css() {
        if ( ! empty( self::$css ) ) {
            echo "<style type='text/css'>" . self::$css . "</style>";
        }
    }
}
add_action( 'wp_footer', [ 'LF_Dynamic_CSS', 'print_css' ] );


/**
 * Checks if a form has a Landeseiten Form configuration and applies it.
 */
function lf_apply_form_settings( $form ) {
    $form_id = $form['id'];

    $args = [
        'post_type'      => 'lf_form',
        'posts_per_page' => 1,
        'meta_key'       => '_lf_gravity_form_id',
        'meta_value'     => $form_id,
        'fields'         => 'ids',
    ];
    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        $config_post_id = $query->posts[0];

        add_filter( 'gform_get_form_filter_' . $form_id, 'lf_add_wrapper_class_to_html', 10, 2 );

        // --- Get all saved settings for JavaScript ---
        $mode          = get_post_meta( $config_post_id, '_lf_mode', true );
        $btn_next_text = get_post_meta( $config_post_id, '_lf_btn_next_text', true );
        $btn_prev_text = get_post_meta( $config_post_id, '_lf_btn_prev_text', true );
        $error_req     = get_post_meta( $config_post_id, '_lf_error_required', true );
        $error_email   = get_post_meta( $config_post_id, '_lf_error_email', true );
        $error_phone   = get_post_meta( $config_post_id, '_lf_error_phone', true );
        $error_url     = get_post_meta( $config_post_id, '_lf_error_url', true ); 

        $settings_for_js = [
            'mode' => $mode,
            'buttonText' => [
                'next'     => $btn_next_text,
                'previous' => $btn_prev_text,
            ],
            'errorMessages' => [
                'required' => $error_req,
                'email'    => $error_email,
                'phone'    => $error_phone,
                'url'      => $error_url, // ** THIS LINE WAS MISSING **
            ],
        ];

        // Remove empty values so the JS defaults can be used.
        $settings_for_js = array_filter( $settings_for_js, function($v) { return !is_null($v) && $v !== ''; } );
        
        wp_localize_script( 'landeseiten-form-init-script', 'lf_form_settings', $settings_for_js );

        // --- Get all saved settings for CSS ---
        $accent_color      = get_post_meta( $config_post_id, '_lf_accent_color', true );
        $text_color        = get_post_meta( $config_post_id, '_lf_text_color', true );
        $active_text_color = get_post_meta( $config_post_id, '_lf_active_text_color', true );
        $label_font_size   = get_post_meta( $config_post_id, '_lf_label_font_size', true );
        $input_font_size   = get_post_meta( $config_post_id, '_lf_input_font_size', true );
        $font_family       = get_post_meta( $config_post_id, '_lf_font_family', true );
        
        // --- Generate Dynamic CSS ---
        $custom_css = ".gform_wrapper.cs-landeseiten-form {";
        if ( $accent_color ) { $custom_css .= "--landeseiten-form-accent-color: {$accent_color};"; }
        if ( $text_color ) { $custom_css .= "--landeseiten-form-text-color: {$text_color};"; }
        if ( $active_text_color ) { $custom_css .= "--landeseiten-form-accent-contrast: {$active_text_color};"; }
        if ( $label_font_size ) { $custom_css .= "--landeseiten-form-label-font-size: {$label_font_size}px;"; }
        if ( $input_font_size ) { $custom_css .= "--landeseiten-form-input-font-size: {$input_font_size}px;"; }
        if ( $font_family ) { $custom_css .= "font-family: {$font_family};"; }
        $custom_css .= "}";
        
        LF_Dynamic_CSS::add( $custom_css );
    }

    return $form;
}
add_filter( 'gform_pre_render', 'lf_apply_form_settings', 10 );

/**
 * Adds our custom classes to the form's wrapper div.
 */
function lf_add_wrapper_class_to_html( $form_string, $form ) {
    $form_string = str_replace(
        'gform_wrapper',
        'gform_wrapper cs-landeseiten-form landeseiten-form-active',
        $form_string
    );
    return $form_string;
}