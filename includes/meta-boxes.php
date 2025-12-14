<?php
/**
 * Handles the Meta Boxes and custom fields for the Landeseiten Form CPT.
 *
 * This file is responsible for rendering the administration interface
 * where users configure the form settings, styles, and text.
 *
 * @package LandeseitenForm
 * @since   1.0.0
 * @version 2.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Registers the meta box for the 'lf_form' CPT.
 *
 * @since 1.0.0
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
 * Renders the HTML for the settings meta box.
 *
 * Uses a tabbed interface (General, Design, Content) to organize
 * the configuration options.
 *
 * @param WP_Post $post The current post object.
 * @since 1.0.0
 */
function lf_render_settings_meta_box( $post ) {
	wp_nonce_field( 'lf_save_meta_box_data', 'lf_meta_box_nonce' );

	// --- 1. Retrieve General Settings ---
	$saved_form_id   = get_post_meta( $post->ID, '_lf_gravity_form_id', true );
	$saved_mode      = get_post_meta( $post->ID, '_lf_mode', true );
	$saved_max_width = get_post_meta( $post->ID, '_lf_container_max_width', true );

	// --- 2. Retrieve Design & Color Settings ---
	$saved_accent_color   = get_post_meta( $post->ID, '_lf_accent_color', true );
	$saved_text_color     = get_post_meta( $post->ID, '_lf_text_color', true );
	$saved_contrast_color = get_post_meta( $post->ID, '_lf_active_text_color', true );
	$saved_bg_color       = get_post_meta( $post->ID, '_lf_form_bg_color', true );
	$saved_shadow         = get_post_meta( $post->ID, '_lf_enable_shadow', true );

	// Progress Bar
	$saved_progress_bar   = get_post_meta( $post->ID, '_lf_enable_progress_bar', true );
	$saved_progress_color = get_post_meta( $post->ID, '_lf_progress_bar_color', true );

	// --- 3. Retrieve Input Styling ---
	$saved_input_bg_color       = get_post_meta( $post->ID, '_lf_input_bg_color', true );
	$saved_input_text_color     = get_post_meta( $post->ID, '_lf_input_text_color', true );
	$saved_input_border_color   = get_post_meta( $post->ID, '_lf_input_border_color', true );
	$saved_input_focus_bg       = get_post_meta( $post->ID, '_lf_input_focus_bg_color', true );
	$saved_input_focus_text     = get_post_meta( $post->ID, '_lf_input_focus_text_color', true );
	$saved_input_focus_border   = get_post_meta( $post->ID, '_lf_input_focus_border_color', true );
	$saved_input_radius         = get_post_meta( $post->ID, '_lf_input_border_radius', true );
	$saved_input_height         = get_post_meta( $post->ID, '_lf_input_height', true );

	// --- 4. Retrieve Buttons & Typography ---
	$saved_btn_radius     = get_post_meta( $post->ID, '_lf_btn_border_radius', true );
	$saved_btn_full_width = get_post_meta( $post->ID, '_lf_btn_full_width', true );
	$saved_btn_next       = get_post_meta( $post->ID, '_lf_btn_next_text', true );
	$saved_btn_prev       = get_post_meta( $post->ID, '_lf_btn_prev_text', true );
	$saved_btn_submit     = get_post_meta( $post->ID, '_lf_btn_submit_text', true );
	$saved_font_family     = get_post_meta( $post->ID, '_lf_font_family', true );
	$saved_label_font_size = get_post_meta( $post->ID, '_lf_label_font_size', true );
	$saved_input_font_size = get_post_meta( $post->ID, '_lf_input_font_size', true );

	// --- 5. Retrieve Error Messages ---
	$saved_error_req     = get_post_meta( $post->ID, '_lf_error_required', true );
	$saved_error_email   = get_post_meta( $post->ID, '_lf_error_email', true );
	$saved_error_phone   = get_post_meta( $post->ID, '_lf_error_phone', true );
	$saved_error_url     = get_post_meta( $post->ID, '_lf_error_url', true );
	$saved_error_consent = get_post_meta( $post->ID, '_lf_error_consent', true );

	?>
	<style>
		/* --- Material UI Inspired Admin Styles --- */
		.lf-admin-wrapper { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; background: #f0f2f5; padding: 20px; margin: -6px -12px -12px -12px; }
		
		/* Tabs Navigation */
		.lf-tabs-nav { display: flex; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border-radius: 4px; padding: 0 10px; margin-bottom: 20px; }
		.lf-tab-btn { background: none; border: none; padding: 15px 20px; font-size: 14px; font-weight: 500; color: #5c6c7f; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s ease; }
		.lf-tab-btn:hover { color: #0073aa; background: #f8f9fa; }
		.lf-tab-btn.active { color: #0073aa; border-bottom-color: #0073aa; font-weight: 600; }
		
		/* Tab Content */
		.lf-tab-content { display: none; animation: fadeIn 0.3s ease; }
		.lf-tab-content.active { display: block; }
		@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

		/* Card Containers */
		.lf-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
		.lf-card-title { font-size: 16px; font-weight: 600; color: #1d2327; margin: 0 0 20px 0; padding-bottom: 10px; border-bottom: 1px solid #eee; display: flex; align-items: center; gap: 8px; }
		
		/* Grid Layout */
		.lf-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
		.lf-grid-full { grid-column: 1 / -1; }
		
		/* Input Styling */
		.lf-field { margin-bottom: 5px; }
		.lf-field label { display: block; font-size: 13px; font-weight: 500; color: #646970; margin-bottom: 6px; }
		.lf-field input[type="text"], .lf-field input[type="number"], .lf-field select { width: 100%; border: 1px solid #dcdcde; border-radius: 4px; padding: 0 12px; height: 40px; box-shadow: none; transition: border-color 0.2s; font-size: 14px; }
		.lf-field input:focus, .lf-field select:focus { border-color: #0073aa; box-shadow: 0 0 0 1px #0073aa; outline: none; }
		.description { font-size: 12px; color: #8c8f94; margin-top: 5px; display: block; }

		/* Color Picker Fix */
		.lf-field .wp-picker-container { display: block; }
		.lf-field .wp-picker-container input[type="text"] { width: 80px !important; display: inline-block; }

		/* Toggle Switches */
		.lf-switch-wrapper { display: flex; align-items: center; gap: 10px; margin-top: 5px; }
		.lf-switch-wrapper input[type="checkbox"] { margin: 0; cursor: pointer; }
		.lf-switch-wrapper label { margin: 0; font-weight: 600; color: #1d2327; cursor: pointer; display: inline-block; }
	</style>

	<div class="lf-admin-wrapper">
		<div class="lf-tabs-nav">
			<button type="button" class="lf-tab-btn active" data-tab="tab-general"><?php esc_html_e( 'General & Target', 'landeseiten-form' ); ?></button>
			<button type="button" class="lf-tab-btn" data-tab="tab-design"><?php esc_html_e( 'Design & Styling', 'landeseiten-form' ); ?></button>
			<button type="button" class="lf-tab-btn" data-tab="tab-text"><?php esc_html_e( 'Content & Text', 'landeseiten-form' ); ?></button>
		</div>

		<div id="tab-general" class="lf-tab-content active">
			<div class="lf-card">
				<h3 class="lf-card-title">🔌 <?php esc_html_e( 'Connection', 'landeseiten-form' ); ?></h3>
				<div class="lf-grid">
					<div class="lf-field lf-grid-full">
						<label><?php esc_html_e( 'Select Gravity Form', 'landeseiten-form' ); ?></label>
						<?php
						if ( class_exists( 'GFAPI' ) ) {
							$forms = GFAPI::get_forms();
							if ( ! empty( $forms ) ) {
								echo '<select name="lf_gravity_form_id">';
								echo '<option value="">' . esc_html__( '-- Select a Form --', 'landeseiten-form' ) . '</option>';
								foreach ( $forms as $form ) {
									echo '<option value="' . esc_attr( $form['id'] ) . '" ' . selected( $saved_form_id, $form['id'], false ) . '>' . esc_html( $form['title'] ) . ' (ID: ' . esc_attr( $form['id'] ) . ')</option>';
								}
								echo '</select>';
							} else {
								echo '<p>' . esc_html__( 'No Gravity Forms found. Please create one first.', 'landeseiten-form' ) . '</p>';
							}
						} else {
							echo '<p style="color:red;">' . esc_html__( 'Gravity Forms is not active. Please activate it.', 'landeseiten-form' ) . '</p>';
						}
						?>
					</div>
				</div>
			</div>

			<div class="lf-card">
				<h3 class="lf-card-title">⚙️ <?php esc_html_e( 'Behavior & Layout', 'landeseiten-form' ); ?></h3>
				<div class="lf-grid">
					<div class="lf-field">
						<label><?php esc_html_e( 'Transition Mode', 'landeseiten-form' ); ?></label>
						<select name="lf_mode">
							<option value="reveal" <?php selected( $saved_mode ?: 'reveal', 'reveal' ); ?>><?php esc_html_e( 'Reveal (Scroll Effect)', 'landeseiten-form' ); ?></option>
							<option value="paged" <?php selected( $saved_mode, 'paged' ); ?>><?php esc_html_e( 'Paged (Slide Effect)', 'landeseiten-form' ); ?></option>
						</select>
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Max Width (px)', 'landeseiten-form' ); ?></label>
						<input type="number" name="lf_container_max_width" value="<?php echo esc_attr( $saved_max_width ?: '800' ); ?>" placeholder="800" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Form Shadow', 'landeseiten-form' ); ?></label>
						<div class="lf-switch-wrapper">
							<input type="checkbox" id="lf_enable_shadow" name="lf_enable_shadow" value="1" <?php checked( $saved_shadow, '1' ); ?> />
							<label for="lf_enable_shadow"><?php esc_html_e( 'Add shadow to container?', 'landeseiten-form' ); ?></label>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="tab-design" class="lf-tab-content">
			<div class="lf-card">
				<h3 class="lf-card-title">🎨 <?php esc_html_e( 'Global Palette', 'landeseiten-form' ); ?></h3>
				<div class="lf-grid">
					<div class="lf-field">
						<label><?php esc_html_e( 'Primary Accent', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_accent_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_accent_color ?: '#0073aa' ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Contrast Text', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_active_text_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_contrast_color ?: '#ffffff' ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Default Text', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_text_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_text_color ?: '#333333' ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Form Background', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_form_bg_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_bg_color ?: '#ffffff' ); ?>" />
					</div>
				</div>
			</div>

			<div class="lf-card">
				<h3 class="lf-card-title">📊 <?php esc_html_e( 'Progress Bar', 'landeseiten-form' ); ?></h3>
				<div class="lf-grid">
					<div class="lf-field">
						<label><?php esc_html_e( 'Status', 'landeseiten-form' ); ?></label>
						<div class="lf-switch-wrapper">
							<input type="checkbox" id="lf_enable_progress_bar" name="lf_enable_progress_bar" value="1" <?php checked( $saved_progress_bar, '1' ); ?> />
							<label for="lf_enable_progress_bar"><?php esc_html_e( 'Show Progress Bar?', 'landeseiten-form' ); ?></label>
						</div>
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Bar Color', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_progress_bar_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_progress_color ?: '#00c853' ); ?>" />
					</div>
				</div>
			</div>

			<div class="lf-card">
				<h3 class="lf-card-title">⌨️ <?php esc_html_e( 'Input Fields', 'landeseiten-form' ); ?></h3>
				<div class="lf-grid">
					<div class="lf-field">
						<label><?php esc_html_e( 'Background', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_input_bg_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_bg_color ?: '#ffffff' ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Border Color', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_input_border_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_border_color ?: '#cccccc' ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Text Color', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_input_text_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_text_color ?: '#333333' ); ?>" />
					</div>
					
					<div class="lf-field">
						<label><?php esc_html_e( 'Focus Background', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_input_focus_bg_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_focus_bg ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Focus Border', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_input_focus_border_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_focus_border ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Focus Text', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_input_focus_text_color" class="lf-color-picker" value="<?php echo esc_attr( $saved_input_focus_text ); ?>" />
					</div>

					<div class="lf-field">
						<label><?php esc_html_e( 'Height (px)', 'landeseiten-form' ); ?></label>
						<input type="number" name="lf_input_height" value="<?php echo esc_attr( $saved_input_height ?: '50' ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Radius (px)', 'landeseiten-form' ); ?></label>
						<input type="number" name="lf_input_border_radius" value="<?php echo esc_attr( $saved_input_radius ?: '6' ); ?>" />
					</div>
				</div>
			</div>

			<div class="lf-card">
				<h3 class="lf-card-title">🔘 <?php esc_html_e( 'Buttons & Typography', 'landeseiten-form' ); ?></h3>
				<div class="lf-grid">
					<div class="lf-field">
						<label><?php esc_html_e( 'Button Radius (px)', 'landeseiten-form' ); ?></label>
						<input type="number" name="lf_btn_border_radius" value="<?php echo esc_attr( $saved_btn_radius ?: '6' ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Button Width', 'landeseiten-form' ); ?></label>
						<select name="lf_btn_full_width">
							<option value="" <?php selected( $saved_btn_full_width, '' ); ?>><?php esc_html_e( 'Auto (Text Width)', 'landeseiten-form' ); ?></option>
							<option value="1" <?php selected( $saved_btn_full_width, '1' ); ?>><?php esc_html_e( 'Full Width (Block)', 'landeseiten-form' ); ?></option>
						</select>
					</div>
					<div class="lf-field lf-grid-full">
						<label><?php esc_html_e( 'Font Family', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_font_family" value="<?php echo esc_attr( $saved_font_family ); ?>" placeholder="e.g. Roboto, Open Sans, sans-serif" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Label Size (px)', 'landeseiten-form' ); ?></label>
						<input type="number" name="lf_label_font_size" value="<?php echo esc_attr( $saved_label_font_size ?: '24' ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Input Size (px)', 'landeseiten-form' ); ?></label>
						<input type="number" name="lf_input_font_size" value="<?php echo esc_attr( $saved_input_font_size ?: '18' ); ?>" />
					</div>
				</div>
			</div>
		</div>

		<div id="tab-text" class="lf-tab-content">
			<div class="lf-card">
				<h3 class="lf-card-title">🏷️ <?php esc_html_e( 'UI Labels', 'landeseiten-form' ); ?></h3>
				<div class="lf-grid">
					<div class="lf-field">
						<label><?php esc_html_e( 'Next Button', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_btn_next_text" value="<?php echo esc_attr( $saved_btn_next ?: 'Weiter →' ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Previous Button', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_btn_prev_text" value="<?php echo esc_attr( $saved_btn_prev ?: '← Zurück' ); ?>" />
					</div>
					<div class="lf-field">
						<label><?php esc_html_e( 'Submit Button', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_btn_submit_text" value="<?php echo esc_attr( $saved_btn_submit ); ?>" placeholder="e.g. Absenden" />
						<span class="description"><?php esc_html_e( 'Leaves default if empty.', 'landeseiten-form' ); ?></span>
					</div>
				</div>
			</div>

			<div class="lf-card">
				<h3 class="lf-card-title">⚠️ <?php esc_html_e( 'Error Messages', 'landeseiten-form' ); ?></h3>
				<div class="lf-grid">
					<div class="lf-field lf-grid-full">
						<label><?php esc_html_e( 'Required Field', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_error_required" value="<?php echo esc_attr( $saved_error_req ?: 'Dieses Feld ist erforderlich.' ); ?>" />
					</div>
					<div class="lf-field lf-grid-full">
						<label><?php esc_html_e( 'Invalid Email', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_error_email" value="<?php echo esc_attr( $saved_error_email ?: 'Bitte geben Sie eine gültige E-Mail-Adresse ein.' ); ?>" />
					</div>
					<div class="lf-field lf-grid-full">
						<label><?php esc_html_e( 'Invalid Phone', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_error_phone" value="<?php echo esc_attr( $saved_error_phone ?: 'Bitte geben Sie eine gültige Telefonnummer (nur Ziffern) ein.' ); ?>" />
					</div>
					<div class="lf-field lf-grid-full">
						<label><?php esc_html_e( 'Invalid URL', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_error_url" value="<?php echo esc_attr( $saved_error_url ?: 'Bitte geben Sie eine gültige Web-Adresse ein.' ); ?>" />
					</div>
					<div class="lf-field lf-grid-full">
						<label><?php esc_html_e( 'Consent Required', 'landeseiten-form' ); ?></label>
						<input type="text" name="lf_error_consent" value="<?php echo esc_attr( $saved_error_consent ?: 'Bitte stimmen Sie den Bedingungen zu.' ); ?>" />
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
	jQuery(document).ready(function($) {
		$('.lf-tab-btn').on('click', function() {
			var tab_id = $(this).attr('data-tab');
			$('.lf-tab-btn').removeClass('active');
			$('.lf-tab-content').removeClass('active');
			$(this).addClass('active');
			$("#"+tab_id).addClass('active');
		});
	});
	</script>
	<?php
}

/**
 * Enqueues admin scripts for the color picker.
 *
 * @param string $hook The current admin page hook.
 * @since 1.0.0
 */
function lf_admin_enqueue_scripts( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}
	if ( 'lf_form' !== get_post_type() ) {
		return;
	}
	wp_enqueue_style( 'wp-color-picker' );
	
	// Ensure the Admin JS is versioned correctly for cache busting
	wp_enqueue_script( 'lf-admin-script', LF_PLUGIN_URL . 'assets/js/admin.js', [ 'wp-color-picker' ], LF_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'lf_admin_enqueue_scripts' );

/**
 * Saves the custom meta data when the post is saved.
 *
 * @param int $post_id The ID of the post being saved.
 * @since 1.0.0
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

	$fields = [
		// General
		'_lf_gravity_form_id'          => 'absint',
		'_lf_mode'                     => 'sanitize_text_field',
		'_lf_container_max_width'      => 'absint',
		'_lf_enable_shadow'            => 'sanitize_text_field',
		
		// Global Colors
		'_lf_accent_color'             => 'sanitize_hex_color',
		'_lf_text_color'               => 'sanitize_hex_color',
		'_lf_active_text_color'        => 'sanitize_hex_color',
		'_lf_form_bg_color'            => 'sanitize_hex_color',
		
		// Progress Bar
		'_lf_enable_progress_bar'      => 'sanitize_text_field',
		'_lf_progress_bar_color'       => 'sanitize_hex_color',

		// Typography & Text
		'_lf_label_font_size'          => 'absint',
		'_lf_input_font_size'          => 'absint',
		'_lf_font_family'              => 'sanitize_text_field',
		'_lf_btn_next_text'            => 'sanitize_text_field',
		'_lf_btn_prev_text'            => 'sanitize_text_field',
		'_lf_btn_submit_text'          => 'sanitize_text_field',
		
		// Error Messages
		'_lf_error_required'           => 'sanitize_text_field',
		'_lf_error_email'              => 'sanitize_text_field',
		'_lf_error_phone'              => 'sanitize_text_field',
		'_lf_error_url'                => 'sanitize_text_field',
		'_lf_error_consent'            => 'sanitize_text_field',
		
		// Styling
		'_lf_input_bg_color'           => 'sanitize_hex_color',
		'_lf_input_text_color'         => 'sanitize_hex_color',
		'_lf_input_border_color'       => 'sanitize_hex_color',
		'_lf_input_focus_bg_color'     => 'sanitize_hex_color',
		'_lf_input_focus_text_color'   => 'sanitize_hex_color',
		'_lf_input_focus_border_color' => 'sanitize_hex_color',
		'_lf_input_border_radius'      => 'absint',
		'_lf_input_height'             => 'absint',
		'_lf_btn_border_radius'        => 'absint',
		'_lf_btn_full_width'           => 'sanitize_text_field',
	];

	foreach ( $fields as $key => $callback ) {
		$post_key = ltrim( $key, '_' );
		if ( isset( $_POST[ $post_key ] ) ) {
			$val = $_POST[ $post_key ];
			if ( 'sanitize_hex_color' === $callback && '' === $val ) {
				update_post_meta( $post_id, $key, '' );
			} else {
				update_post_meta( $post_id, $key, call_user_func( $callback, $val ) );
			}
		} else {
			// Handle checkboxes (if not posted, it means unchecked)
			if ( in_array( $key, [ '_lf_enable_shadow', '_lf_enable_progress_bar', '_lf_btn_full_width' ] ) ) {
				update_post_meta( $post_id, $key, '' );
			}
		}
	}
}
add_action( 'save_post', 'lf_save_post_meta' );