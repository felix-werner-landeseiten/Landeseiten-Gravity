<?php
/**
 * Handles the integration with Gravity Forms, applying settings and dynamic styles.
 *
 * @package LandeseitenForm
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * A helper class to safely collect and print dynamic CSS in the footer.
 * This ensures styles are loaded last and can override defaults.
 */
final class LF_Dynamic_CSS {
    private static $css = '';

    public static function add( $style_string ) {
        self::$css .= $style_string;
    }

    public static function print_css() {
        if ( ! empty( self::$css ) ) {
            // Minify the CSS slightly for output.
            $minified_css = str_replace( ["\r", "\n", "  "], '', self::$css );
            echo "<style type='text/css' id='landeseiten-form-dynamic-styles'>" . $minified_css . "</style>";
        }
    }
}
add_action( 'wp_footer', [ 'LF_Dynamic_CSS', 'print_css' ] );


/**
 * Checks if a form has a Landeseiten Form configuration and applies it.
 * Hooks into `gform_pre_render` to access form data before it is displayed.
 *
 * @param array $form The Gravity Form object.
 * @return array The original form object.
 */
function lf_apply_form_settings( $form ) {
    $form_id = $form['id'];

    // Find the configuration post for the current Gravity Form ID.
    $query = new WP_Query([
        'post_type'      => 'lf_form',
        'posts_per_page' => 1,
        'meta_key'       => '_lf_gravity_form_id',
        'meta_value'     => $form_id,
        'fields'         => 'ids',
    ]);

    if ( $query->have_posts() ) {
        $config_post_id = $query->posts[0];

        // Hook into the final form HTML to add our custom classes.
        add_filter( 'gform_get_form_filter_' . $form_id, 'lf_add_wrapper_class_to_html', 10, 2 );

        // --- Prepare settings to pass to JavaScript ---
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
                'url'      => $error_url,
            ],
        ];

        // Remove empty values so the JS defaults can be used.
        $settings_for_js = array_filter( $settings_for_js, function($v) { return !is_null($v) && $v !== ''; } );
        
        wp_localize_script( 'landeseiten-form-init-script', 'lf_form_settings', $settings_for_js );

        // --- Prepare settings for Dynamic CSS ---
        $accent_color        = get_post_meta( $config_post_id, '_lf_accent_color', true );
        $font_family         = get_post_meta( $config_post_id, '_lf_font_family', true );
        
        // Get new Input Field Styles
        $input_bg_color      = get_post_meta( $config_post_id, '_lf_input_bg_color', true );
        $input_text_color    = get_post_meta( $config_post_id, '_lf_input_text_color', true );
        $input_border_color  = get_post_meta( $config_post_id, '_lf_input_border_color', true );
        $input_focus_bg_color = get_post_meta( $config_post_id, '_lf_input_focus_bg_color', true );
        $input_focus_text_color = get_post_meta( $config_post_id, '_lf_input_focus_text_color', true );
        $input_focus_border_color = get_post_meta( $config_post_id, '_lf_input_focus_border_color', true );
        
        // --- Generate and collect all dynamic CSS ---
        $custom_css = ".gform_wrapper.cs-landeseiten-form {";
        if ( $accent_color ) { $custom_css .= "--landeseiten-form-accent-color: {$accent_color};"; }
        if ( $font_family ) { $custom_css .= "font-family: {$font_family};"; }
        // Set variables for normal input state
        if ( $input_bg_color ) { $custom_css .= "--landeseiten-form-bg-color: {$input_bg_color};"; }
        if ( $input_text_color ) { $custom_css .= "--landeseiten-form-text-color: {$input_text_color};"; }
        if ( $input_border_color ) { $custom_css .= "--landeseiten-form-border-color: {$input_border_color};"; }
        $custom_css .= "}";

        // Generate specific override rules for the focus state
         if ( $input_focus_border_color || $input_focus_bg_color || $input_focus_text_color ) {
            $custom_css .= ".gform_wrapper.cs-landeseiten-form .ginput_container input:focus, .gform_wrapper.cs-landeseiten-form .ginput_container textarea:focus {";
            if ( $input_focus_bg_color ) { $custom_css .= "background-color: {$input_focus_bg_color} !important;"; }
            if ( $input_focus_text_color ) { $custom_css .= "color: {$input_focus_text_color} !important;"; }
            if ( $input_focus_border_color ) { $custom_css .= "border-color: {$input_focus_border_color} !important; box-shadow: inset 0 0 0 1px {$input_focus_border_color} !important; outline: none !important;"; }
            $custom_css .= "}";
        }
        
        LF_Dynamic_CSS::add( $custom_css );
    }

    return $form;
}
add_filter( 'gform_pre_render', 'lf_apply_form_settings', 10 );

/**
 * Adds our custom classes to the form's wrapper div.
 *
 * @param string $form_string The HTML string of the form.
 * @param array  $form        The Gravity Form object.
 * @return string The modified HTML string.
 */
function lf_add_wrapper_class_to_html( $form_string, $form ) {
    $form_string = str_replace(
        'gform_wrapper',
        'gform_wrapper cs-landeseiten-form landeseiten-form-active',
        $form_string
    );
    return $form_string;
}