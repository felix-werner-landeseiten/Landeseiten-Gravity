<?php
/**
 * Handles the Meta Boxes and custom fields for the Landeseiten Form CPT.
 *
 * @package LandeseitenForm
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Registers the meta box for the 'lf_form' CPT.
 */
function lf_add_meta_boxes() {
    add_meta_box(
        'lf_form_settings_meta_box',
        __( 'Form Configuration', 'landeseiten-form' ),
        'lf_render_settings_meta_box',
        'lf_form',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'lf_add_meta_boxes' );

/**
 * Renders the HTML for the meta box.
 *
 * @param WP_Post $post The post object.
 */
function lf_render_settings_meta_box( $post ) {
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
    $saved_error_url = get_post_meta( $post->ID, '_lf_error_url', true );

    // ** NEW ** Get saved values for Input Styling
    $saved_input_bg_color = get_post_meta( $post->ID, '_lf_input_bg_color', true );
    $saved_input_text_color = get_post_meta( $post->ID, '_lf_input_text_color', true );
    $saved_input_border_color = get_post_meta( $post->ID, '_lf_input_border_color', true );
    $saved_input_focus_bg_color = get_post_meta( $post->ID, '_lf_input_focus_bg_color', true );
    $saved_input_focus_text_color = get_post_meta( $post->ID, '_lf_input_focus_text_color', true );
    $saved_input_focus_border_color = get_post_meta( $post->ID, '_lf_input_focus_border_color', true );

    ?>
    <style>.lf-meta-table td { padding: 10px 15px 15px 0; vertical-align: top; } .lf-meta-table p.description { margin-top: 4px; }</style>

    <h3><?php esc_html_e( 'Target Gravity Form', 'landeseiten-form' ); ?></h3>
    <?php
    if ( class_exists( 'GFAPI' ) ) {
        $forms = GFAPI::get_forms();
        if ( ! empty( $forms ) ) {
            ?>
            <select name="lf_gravity_form_id" id="lf_gravity_form_id" style="width: 100%; max-width: 400px;">
                <option value=""><?php esc_html_e( '-- Select a Form --', 'landeseiten-form' ); ?></option>
                <?php foreach ( $forms as $form ) : ?>
                    <option value="<?php echo esc_attr( $form['id'] ); ?>" <?php selected( $saved_form_id, $form['id'] ); ?>>
                        <?php echo esc_html( $form['title'] ); ?> (ID: <?php echo esc_attr( $form['id'] ); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e( 'Choose which Gravity Form this configuration will apply to.', 'landeseiten-form' ); ?></p>
            <?php
        } else {
            echo '<p>' . esc_html__( 'No Gravity Forms found. Please create one first.', 'landeseiten-form' ) . '</p>';
        }
    } else {
        echo '<p style="color: red;">' . esc_html__( 'Gravity Forms is not active. Please activate it to use this plugin.', 'landeseiten-form' ) . '</p>';
    }
    ?>
    <hr>
    
    <h3><?php esc_html_e( 'General Color Settings', 'landeseiten-form' ); ?></h3>
    <table class="lf-meta-table">
        <tr valign="top">
            <td>
                <label for="lf_accent_color"><?php esc_html_e( 'Accent Color', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_accent_color" name="lf_accent_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_accent_color ?: '#0073aa' ); ?>" />
                <p class="description"><?php esc_html_e( 'For radio buttons, checkboxes, etc.', 'landeseiten-form' ); ?></p>
            </td>
            <td>
                <label for="lf_text_color"><?php esc_html_e( 'Default Text Color', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_text_color" name="lf_text_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_text_color ?: '#333333' ); ?>" />
            </td>
            <td>
                <label for="lf_active_text_color"><?php esc_html_e( 'Active/Contrast Text Color', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_active_text_color" name="lf_active_text_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_active_text_color ?: '#ffffff' ); ?>" />
            </td>
        </tr>
    </table>
    <hr>

    <h3><?php esc_html_e( 'Input Field Styling (Text, Email, etc.)', 'landeseiten-form' ); ?></h3>
    <table class="lf-meta-table">
        <tr valign="top">
            <td><strong><?php esc_html_e( 'Normal State', 'landeseiten-form' ); ?></strong></td>
            <td>
                <label for="lf_input_bg_color"><?php esc_html_e( 'Background Color', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_input_bg_color" name="lf_input_bg_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_bg_color ?: '#ffffff' ); ?>" />
            </td>
            <td>
                <label for="lf_input_text_color"><?php esc_html_e( 'Text Color', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_input_text_color" name="lf_input_text_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_text_color ?: '#333333' ); ?>" />
            </td>
            <td>
                <label for="lf_input_border_color"><?php esc_html_e( 'Border Color', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_input_border_color" name="lf_input_border_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_border_color ?: '#cccccc' ); ?>" />
            </td>
        </tr>
        <tr valign="top">
            <td><strong><?php esc_html_e( 'Focus State (when clicked)', 'landeseiten-form' ); ?></strong></td>
            <td>
                <label for="lf_input_focus_bg_color"><?php esc_html_e( 'Background Color', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_input_focus_bg_color" name="lf_input_focus_bg_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_focus_bg_color ); ?>" />
            </td>
            <td>
                <label for="lf_input_focus_text_color"><?php esc_html_e( 'Text Color', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_input_focus_text_color" name="lf_input_focus_text_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_focus_text_color ); ?>" />
            </td>
            <td>
                <label for="lf_input_focus_border_color"><?php esc_html_e( 'Border/Glow Color', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_input_focus_border_color" name="lf_input_focus_border_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_focus_border_color ); ?>" />
                 <p class="description"><?php esc_html_e( 'Tip: Use your Accent Color.', 'landeseiten-form' ); ?></p>
            </td>
        </tr>
    </table>
    <hr>
    
    <h3><?php esc_html_e( 'Typography', 'landeseiten-form' ); ?></h3>
    <table class="lf-meta-table">
        <tr valign="top">
            <td colspan="2">
                <label for="lf_font_family"><?php esc_html_e( 'Font Family', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_font_family" name="lf_font_family" value="<?php echo esc_attr( $saved_font_family ); ?>" style="width: 100%; max-width: 400px;" placeholder="<?php esc_attr_e( 'e.g., Roboto, Helvetica, Arial, sans-serif', 'landeseiten-form' ); ?>" />
            </td>
        </tr>
        <tr valign="top">
            <td>
                <label for="lf_label_font_size"><?php esc_html_e( 'Label Font Size (px)', 'landeseiten-form' ); ?></label><br>
                <input type="number" id="lf_label_font_size" name="lf_label_font_size" value="<?php echo esc_attr( $saved_label_font_size ?: '24' ); ?>" />
            </td>
            <td>
                <label for="lf_input_font_size"><?php esc_html_e( 'Input Font Size (px)', 'landeseiten-form' ); ?></label><br>
                <input type="number" id="lf_input_font_size" name="lf_input_font_size" value="<?php echo esc_attr( $saved_input_font_size ?: '18' ); ?>" />
            </td>
        </tr>
    </table>
    <hr>

    <h3><?php esc_html_e( 'Text & Messages', 'landeseiten-form' ); ?></h3>
    <table class="lf-meta-table">
        <tr valign="top">
            <td>
                <label for="lf_btn_next_text"><?php esc_html_e( 'Next Button Text', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_btn_next_text" name="lf_btn_next_text" value="<?php echo esc_attr( $saved_btn_next ?: 'Weiter →' ); ?>" />
            </td>
            <td>
                <label for="lf_btn_prev_text"><?php esc_html_e( 'Previous Button Text', 'landeseiten-form' ); ?></label><br>
                <input type="text" id="lf_btn_prev_text" name="lf_btn_prev_text" value="<?php echo esc_attr( $saved_btn_prev ?: '← Zurück' ); ?>" />
            </td>
        </tr>
        <tr><td colspan="2"><label for="lf_error_required"><?php esc_html_e( 'Required Field Error', 'landeseiten-form' ); ?></label><br><input type="text" id="lf_error_required" name="lf_error_required" value="<?php echo esc_attr( $saved_error_req ?: 'Dieses Feld ist erforderlich.' ); ?>" style="width: 100%; max-width: 400px;" /></td></tr>
        <tr><td colspan="2"><label for="lf_error_email"><?php esc_html_e( 'Invalid Email Error', 'landeseiten-form' ); ?></label><br><input type="text" id="lf_error_email" name="lf_error_email" value="<?php echo esc_attr( $saved_error_email ?: 'Bitte geben Sie eine gültige E-Mail-Adresse ein.' ); ?>" style="width: 100%; max-width: 400px;" /></td></tr>
        <tr><td colspan="2"><label for="lf_error_phone"><?php esc_html_e( 'Invalid Phone Error', 'landeseiten-form' ); ?></label><br><input type="text" id="lf_error_phone" name="lf_error_phone" value="<?php echo esc_attr( $saved_error_phone ?: 'Bitte geben Sie eine gültige Telefonnummer (nur Ziffern) ein.' ); ?>" style="width: 100%; max-width: 400px;" /></td></tr>
        <tr><td colspan="2"><label for="lf_error_url"><?php esc_html_e( 'Invalid URL/Website Error', 'landeseiten-form' ); ?></label><br><input type="text" id="lf_error_url" name="lf_error_url" value="<?php echo esc_attr( $saved_error_url ?: 'Bitte geben Sie eine gültige Web-Adresse ein.' ); ?>" style="width: 100%; max-width: 400px;" /></td></tr>
    </table>
    <hr>

    <h3><?php esc_html_e( 'Functionality', 'landeseiten-form' ); ?></h3>
    <p>
        <label for="lf_mode"><?php esc_html_e( 'Transition Mode', 'landeseiten-form' ); ?></label><br>
        <select name="lf_mode" id="lf_mode">
            <option value="reveal" <?php selected( $saved_mode ?: 'reveal', 'reveal' ); ?>><?php esc_html_e( 'Reveal', 'landeseiten-form' ); ?></option>
            <option value="paged" <?php selected( $saved_mode, 'paged' ); ?>><?php esc_html_e( 'Paged', 'landeseiten-form' ); ?></option>
        </select>
    </p>

    <?php
}

/**
 * Enqueues admin scripts for the color picker.
 */
function lf_admin_enqueue_scripts( $hook ) {
    if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) { return; }
    if ( 'lf_form' !== get_post_type() ) { return; }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'lf-admin-script', LF_PLUGIN_URL . 'assets/js/admin.js', [ 'wp-color-picker' ], '1.2.0', true );
}
add_action( 'admin_enqueue_scripts', 'lf_admin_enqueue_scripts' );

/**
 * Saves the custom meta data when the post is saved.
 */
function lf_save_post_meta( $post_id ) {
    if ( ! isset( $_POST['lf_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['lf_meta_box_nonce'], 'lf_save_meta_box_data' ) ) { return; }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
    if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

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
        '_lf_error_url'         => 'sanitize_text_field',
        '_lf_input_bg_color'          => 'sanitize_hex_color',
        '_lf_input_text_color'        => 'sanitize_hex_color',
        '_lf_input_border_color'      => 'sanitize_hex_color',
        '_lf_input_focus_bg_color'    => 'sanitize_hex_color',
        '_lf_input_focus_text_color'  => 'sanitize_hex_color',
        '_lf_input_focus_border_color'=> 'sanitize_hex_color',
    ];

    foreach ($fields_to_save as $meta_key => $sanitize_callback) {
        $post_key = ltrim($meta_key, '_');
        if (isset($_POST[$post_key])) {
            $value = $_POST[$post_key];
            // Allow empty values for color pickers to be saved.
            if ( 'sanitize_hex_color' === $sanitize_callback && '' === $value ) {
                update_post_meta($post_id, $meta_key, '');
            } else {
                $sanitized_value = call_user_func($sanitize_callback, $value);
                update_post_meta($post_id, $meta_key, $sanitized_value);
            }
        }
    }
}
add_action( 'save_post', 'lf_save_post_meta' );