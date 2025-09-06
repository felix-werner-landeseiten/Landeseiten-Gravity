<?php
/**
 * Handles the Meta Boxes for the Landeseiten Form CPT.
 *
 * @package LandeseitenForm
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Adds the meta box container.
 */
function lf_add_meta_boxes() {
    add_meta_box(
        'lf_form_settings_meta_box',          // Unique ID
        __( 'Form Settings', 'landeseiten-form' ), // Box title
        'lf_render_settings_meta_box',        // Callback function
        'lf_form',                            // Screen to show on (our CPT)
        'normal',                             // Context (where it appears)
        'high'                                // Priority
    );
}
add_action( 'add_meta_boxes', 'lf_add_meta_boxes' );

/**
 * Renders the content of the meta box.
 *
 * @param WP_Post $post The post object.
 */
function lf_render_settings_meta_box( $post ) {
    // Add a nonce field for security.
    wp_nonce_field( 'lf_save_meta_box_data', 'lf_meta_box_nonce' );

    // Get existing saved values.
    $saved_form_id   = get_post_meta( $post->ID, '_lf_gravity_form_id', true );
    $saved_mode      = get_post_meta( $post->ID, '_lf_mode', true );
    $saved_accent_color = get_post_meta( $post->ID, '_lf_accent_color', true );
    $saved_text_color = get_post_meta( $post->ID, '_lf_text_color', true );
    $saved_active_text_color = get_post_meta( $post->ID, '_lf_active_text_color', true );
    $saved_label_font_size = get_post_meta( $post->ID, '_lf_label_font_size', true );
    $saved_input_font_size = get_post_meta( $post->ID, '_lf_input_font_size', true );
    $saved_font_family = get_post_meta( $post->ID, '_lf_font_family', true );
    $saved_btn_next = get_post_meta( $post->ID, '_lf_btn_next_text', true );
    $saved_btn_prev = get_post_meta( $post->ID, '_lf_btn_prev_text', true );
    $saved_error_req = get_post_meta( $post->ID, '_lf_error_required', true );
    $saved_error_email = get_post_meta( $post->ID, '_lf_error_email', true );
    $saved_error_phone = get_post_meta( $post->ID, '_lf_error_phone', true );

    // --- Gravity Form Selector ---
    echo '<h4>' . esc_html__( 'Target Gravity Form', 'landeseiten-form' ) . '</h4>';
    if ( class_exists( 'GFAPI' ) ) {
        $forms = GFAPI::get_forms();
        if ( ! empty( $forms ) ) {
            echo '<select name="lf_gravity_form_id" id="lf_gravity_form_id" style="width: 100%; max-width: 400px;">';
            echo '<option value="">' . esc_html__( '-- Select a Form --', 'landeseiten-form' ) . '</option>';
            foreach ( $forms as $form ) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr( $form['id'] ),
                    selected( $saved_form_id, $form['id'], false ),
                    esc_html( $form['title'] )
                );
            }
            echo '</select>';
            echo '<p class="description">' . esc_html__( 'Choose which Gravity Form this configuration will apply to.', 'landeseiten-form' ) . '</p>';
        } else {
            echo '<p>' . esc_html__( 'No Gravity Forms found. Please create one first.', 'landeseiten-form' ) . '</p>';
        }
    } else {
        echo '<p style="color: red;">' . esc_html__( 'Gravity Forms is not active. Please activate it to use this plugin.', 'landeseiten-form' ) . '</p>';
    }
    
    echo '<hr style="margin: 20px 0;">';

    // --- Styling ---
    echo '<h4>' . esc_html__( 'Color Settings', 'landeseiten-form' ) . '</h4>';
    echo '<table><tr valign="top">';
    echo '<td style="padding-right: 20px;">';
    echo '<label for="lf_accent_color">' . esc_html__( 'Accent Color', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="text" id="lf_accent_color" name="lf_accent_color" class="lf-color-picker" value="' . esc_attr( $saved_accent_color ?: '#0073aa' ) . '" />';
    echo '</td>';
    echo '<td style="padding-right: 20px;">';
    echo '<label for="lf_text_color">' . esc_html__( 'Text Color', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="text" id="lf_text_color" name="lf_text_color" class="lf-color-picker" value="' . esc_attr( $saved_text_color ?: '#333333' ) . '" />';
    echo '</td>';
    echo '<td>';
    echo '<label for="lf_active_text_color">' . esc_html__( 'Active Text Color', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="text" id="lf_active_text_color" name="lf_active_text_color" class="lf-color-picker" value="' . esc_attr( $saved_active_text_color ?: '#ffffff' ) . '" />';
    echo '<p class="description" style="margin-top: 0;">' . esc_html__( 'Text color for items with an accent background.', 'landeseiten-form' ) . '</p>';
    echo '</td>';
    echo '</tr></table>';
    
    echo '<hr style="margin: 20px 0;">';

    // --- Typography ---
    echo '<h4>' . esc_html__( 'Typography', 'landeseiten-form' ) . '</h4>';
    echo '<p>';
    echo '<label for="lf_font_family">' . esc_html__( 'Font Family', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="text" id="lf_font_family" name="lf_font_family" value="' . esc_attr( $saved_font_family ) . '" style="width: 100%; max-width: 400px;" placeholder="e.g., Helvetica, Arial, sans-serif" />';
    echo '</p>';
    echo '<table><tr valign="top">';
    echo '<td style="padding-right: 20px;">';
    echo '<label for="lf_label_font_size">' . esc_html__( 'Label Font Size (px)', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="number" id="lf_label_font_size" name="lf_label_font_size" value="' . esc_attr( $saved_label_font_size ?: '24' ) . '" />';
    echo '</td>';
    echo '<td>';
    echo '<label for="lf_input_font_size">' . esc_html__( 'Input Font Size (px)', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="number" id="lf_input_font_size" name="lf_input_font_size" value="' . esc_attr( $saved_input_font_size ?: '18' ) . '" />';
    echo '</td>';
    echo '</tr></table>';

    echo '<hr style="margin: 20px 0;">';

    // --- Text & Messages ---
    echo '<h4>' . esc_html__( 'Text & Messages', 'landeseiten-form' ) . '</h4>';
    echo '<table><tr valign="top">';
    echo '<td style="padding-right: 20px;">';
    echo '<label for="lf_btn_next_text">' . esc_html__( 'Next Button Text', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="text" id="lf_btn_next_text" name="lf_btn_next_text" value="' . esc_attr( $saved_btn_next ?: 'Weiter →' ) . '" />';
    echo '</td>';
    echo '<td>';
    echo '<label for="lf_btn_prev_text">' . esc_html__( 'Previous Button Text', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="text" id="lf_btn_prev_text" name="lf_btn_prev_text" value="' . esc_attr( $saved_btn_prev ?: '← Zurück' ) . '" />';
    echo '</td>';
    echo '</tr></table>';
    echo '<p><label for="lf_error_required">' . esc_html__( 'Required Field Error', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="text" id="lf_error_required" name="lf_error_required" value="' . esc_attr( $saved_error_req ?: 'Dieses Feld ist erforderlich.' ) . '" style="width: 100%; max-width: 400px;" />';
    echo '</p>';
    echo '<p><label for="lf_error_email">' . esc_html__( 'Invalid Email Error', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="text" id="lf_error_email" name="lf_error_email" value="' . esc_attr( $saved_error_email ?: 'Bitte geben Sie eine gültige E-Mail-Adresse ein.' ) . '" style="width: 100%; max-width: 400px;" />';
    echo '</p>';
     echo '<p><label for="lf_error_phone">' . esc_html__( 'Invalid Phone Error', 'landeseiten-form' ) . '</label><br>';
    echo '<input type="text" id="lf_error_phone" name="lf_error_phone" value="' . esc_attr( $saved_error_phone ?: 'Bitte geben Sie eine gültige Telefonnummer (nur Ziffern) ein.' ) . '" style="width: 100%; max-width: 400px;" />';
    echo '</p>';

    echo '<hr style="margin: 20px 0;">';

    // --- Functionality ---
    echo '<h4>' . esc_html__( 'Functionality', 'landeseiten-form' ) . '</h4>';
    $mode = $saved_mode ?: 'reveal';
    echo '<p>';
    echo '<label for="lf_mode">' . esc_html__( 'Transition Mode', 'landeseiten-form' ) . '</label><br>';
    echo '<select name="lf_mode" id="lf_mode">';
    echo '<option value="reveal" ' . selected( $mode, 'reveal', false ) . '>' . esc_html__( 'Reveal', 'landeseiten-form' ) . '</option>';
    echo '<option value="paged" ' . selected( $mode, 'paged', false ) . '>' . esc_html__( 'Paged', 'landeseiten-form' ) . '</option>';
    echo '</select>';
    echo '</p>';
}

/**
 * Loads admin scripts for the color picker.
 */
function lf_admin_enqueue_scripts( $hook ) {
    if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
        return;
    }
    if ( 'lf_form' !== get_post_type() ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'lf-admin-script', LF_PLUGIN_URL . 'assets/js/admin.js', [ 'wp-color-picker' ], '1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'lf_admin_enqueue_scripts' );

/**
 * Saves the custom meta box data.
 */
function lf_save_post_meta( $post_id ) {
    if ( ! isset( $_POST['lf_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['lf_meta_box_nonce'], 'lf_save_meta_box_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $fields_to_save = [
        '_lf_gravity_form_id'   => 'absint',
        '_lf_mode'              => 'sanitize_text_field',
        '_lf_accent_color'      => 'sanitize_hex_color',
        '_lf_text_color'        => 'sanitize_hex_color',
        '_lf_active_text_color' => 'sanitize_hex_color',
        '_lf_label_font_size'   => 'absint',
        '_lf_input_font_size'   => 'absint',
        '_lf_font_family'       => 'sanitize_text_field',
        '_lf_btn_next_text'     => 'sanitize_text_field',
        '_lf_btn_prev_text'     => 'sanitize_text_field',
        '_lf_error_required'    => 'sanitize_text_field',
        '_lf_error_email'       => 'sanitize_text_field',
        '_lf_error_phone'       => 'sanitize_text_field',
    ];

    foreach ($fields_to_save as $meta_key => $sanitize_callback) {
        $post_key = ltrim($meta_key, '_');
        if (isset($_POST[$post_key])) {
            $value = call_user_func($sanitize_callback, $_POST[$post_key]);
            update_post_meta($post_id, $meta_key, $value);
        }
    }
}
add_action( 'save_post', 'lf_save_post_meta' );