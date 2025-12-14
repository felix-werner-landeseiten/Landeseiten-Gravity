<?php
/**
 * Handles the integration with Gravity Forms, applying settings, styles, and validation.
 *
 * This file is the bridge between the custom settings (from the CPT) and the
 * actual Gravity Forms rendering process. It handles:
 * 1. Injecting custom field settings into the GF Form Editor.
 * 2. Applying those settings to the frontend form render.
 * 3. Generating dynamic CSS based on user colors/fonts.
 * 4. Custom validation logic for advanced fields (Date Range).
 *
 * @package LandeseitenForm
 * @since   1.0.0
 * @version 2.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * A helper class to safely collect and print dynamic CSS in the footer.
 *
 * This prevents multiple style tags from being injected if multiple forms exist,
 * and keeps the <head> clean.
 *
 * @since 1.0.0
 */
final class LF_Dynamic_CSS {
	/**
	 * Stores the accumulated CSS string.
	 *
	 * @var string
	 */
	private static $css = '';

	/**
	 * Adds CSS to the queue.
	 *
	 * @param string $style_string The CSS rules to add.
	 */
	public static function add( $style_string ) {
		self::$css .= $style_string;
	}

	/**
	 * Prints the minified CSS in the footer.
	 */
	public static function print_css() {
		if ( ! empty( self::$css ) ) {
			// Simple minification: remove newlines and double spaces.
			$minified_css = str_replace( [ "\r", "\n", "  " ], '', self::$css );
			echo "<style type='text/css' id='landeseiten-form-dynamic-styles'>" . $minified_css . "</style>";
		}
	}
}
add_action( 'wp_footer', [ 'LF_Dynamic_CSS', 'print_css' ], 99 );

/* ---------------------------------------------------------------------------
 * 1. GRAVITY FORMS BUILDER SETTINGS (Date Picker Options)
 * --------------------------------------------------------------------------- */

/**
 * Adds custom settings to the "General" tab of the Date field in the Form Editor.
 *
 * @param int $position The current setting position.
 * @param int $form_id  The ID of the form.
 */
add_action( 'gform_field_standard_settings', 'lf_add_datepicker_settings', 10, 2 );
function lf_add_datepicker_settings( $position, $form_id ) {
	if ( $position == 25 ) { // Display after standard field label settings
		?>
		<li class="lf_datepicker_settings field_setting">
			<div style="border-top: 1px solid #ccc; padding-top: 10px; margin-top: 10px;">
				<label class="section_label">
					<?php esc_html_e( 'Landeseiten Date Options', 'landeseiten-form' ); ?>
				</label>
				
				<div style="margin-bottom: 10px;">
					<input type="checkbox" id="lf_enable_range" onclick="SetFieldProperty('lfEnableRange', this.checked);" />
					<label for="lf_enable_range" class="inline">
						<strong><?php esc_html_e( 'Enable Date Range', 'landeseiten-form' ); ?></strong>
					</label>
				</div>

				<div style="margin-bottom: 5px;">
					<input type="checkbox" id="lf_disable_past" onclick="SetFieldProperty('lfDisablePast', this.checked);" />
					<label for="lf_disable_past" class="inline">
						<?php esc_html_e( 'Disable Past Dates', 'landeseiten-form' ); ?>
					</label>
				</div>

				<div style="margin-bottom: 10px;">
					<input type="checkbox" id="lf_disable_future" onclick="SetFieldProperty('lfDisableFuture', this.checked);" />
					<label for="lf_disable_future" class="inline">
						<?php esc_html_e( 'Disable Future Dates', 'landeseiten-form' ); ?>
					</label>
				</div>

				<div>
					<label for="lf_custom_min_date" class="inline" style="display:block; margin-bottom:5px; font-size: 13px;">
						<?php esc_html_e( 'Custom Min Date (YYYY-MM-DD)', 'landeseiten-form' ); ?>
					</label>
					<input type="text" id="lf_custom_min_date" onchange="SetFieldProperty('lfCustomMinDate', this.value);" class="fieldwidth-3" />
				</div>

				<div>
					<label for="lf_custom_max_date" class="inline" style="display:block; margin-bottom:5px; font-size: 13px;">
						<?php esc_html_e( 'Custom Max Date (YYYY-MM-DD)', 'landeseiten-form' ); ?>
					</label>
					<input type="text" id="lf_custom_max_date" onchange="SetFieldProperty('lfCustomMaxDate', this.value);" class="fieldwidth-3" />
				</div>
			</div>
		</li>
		<?php
	}
}

/**
 * Injects JavaScript into the Gravity Forms editor to save/load custom settings.
 */
add_action( 'gform_editor_js', 'lf_editor_script' );
function lf_editor_script() {
	?>
	<script type='text/javascript'>
		// Add our custom class to the Date field settings
		fieldSettings.date += ', .lf_datepicker_settings';

		// Bind loading logic to populate fields when a field is selected
		jQuery(document).bind('gform_load_field_settings', function(event, field, form){
			jQuery('#lf_enable_range').prop('checked', field.lfEnableRange == true);
			jQuery('#lf_disable_past').prop('checked', field.lfDisablePast == true);
			jQuery('#lf_disable_future').prop('checked', field.lfDisableFuture == true);
			jQuery('#lf_custom_min_date').val(field.lfCustomMinDate);
			jQuery('#lf_custom_max_date').val(field.lfCustomMaxDate);
		});
	</script>
	<?php
}

/* ---------------------------------------------------------------------------
 * 2. FRONTEND INTEGRATION & CSS GENERATION
 * --------------------------------------------------------------------------- */

/**
 * Intercepts form rendering to apply custom classes, settings, and styles.
 *
 * @param array $form The form object.
 * @return array The modified form object.
 */
add_filter( 'gform_pre_render', 'lf_apply_form_settings', 10 );
function lf_apply_form_settings( $form ) {
	$form_id = $form['id'];

	// Look for a Landeseiten Config Post associated with this Form ID
	$query = new WP_Query([
		'post_type'      => 'lf_form',
		'posts_per_page' => 1,
		'meta_key'       => '_lf_gravity_form_id',
		'meta_value'     => $form_id,
		'fields'         => 'ids'
	]);

	// If a config exists, apply it
	if ( $query->have_posts() ) {
		$config_post_id = $query->posts[0];

		// Disable default Gravity Forms theme CSS to prevent conflicts
		add_filter( 'gform_disable_form_theme_css', '__return_true' );
		
		// Add custom wrapper class
		add_filter( 'gform_get_form_filter_' . $form_id, 'lf_add_wrapper_class_to_html', 10, 2 );

		// --- 1. Prepare Date Picker Configs ---
		$date_fields_config = [];
		foreach ( $form['fields'] as $field ) {
			if ( $field->type === 'date' ) {
				$config = [];
				$config['mode'] = ! empty( $field->lfEnableRange ) ? 'range' : 'single';
				
				// Handle Min/Max Logic
				if ( ! empty( $field->lfDisablePast ) ) $config['minDate'] = 'today';
				if ( ! empty( $field->lfDisableFuture ) ) $config['maxDate'] = 'today';
				if ( ! empty( $field->lfCustomMinDate ) ) $config['minDate'] = $field->lfCustomMinDate;
				if ( ! empty( $field->lfCustomMaxDate ) ) $config['maxDate'] = $field->lfCustomMaxDate;
				
				$input_id = 'input_' . $form['id'] . '_' . $field->id;
				$date_fields_config[ $input_id ] = $config;
			}
		}
		
		// Pass date config to JS
		if ( ! empty( $date_fields_config ) ) {
			wp_localize_script( 'landeseiten-form-init-script', 'lf_datepicker_config_' . $form['id'], $date_fields_config );
		}

		// --- 2. Prepare General Form Settings for JS ---
		$settings_for_js = [];
		if ( $mode = get_post_meta( $config_post_id, '_lf_mode', true ) ) {
			$settings_for_js['mode'] = $mode;
		}
		
		// Progress Bar
		if ( get_post_meta( $config_post_id, '_lf_enable_progress_bar', true ) ) {
			$settings_for_js['progressBar'] = true;
		}

		// Button Texts
		$btn_next   = get_post_meta( $config_post_id, '_lf_btn_next_text', true );
		$btn_prev   = get_post_meta( $config_post_id, '_lf_btn_prev_text', true );
		$btn_submit = get_post_meta( $config_post_id, '_lf_btn_submit_text', true );
		
		if ( $btn_next || $btn_prev || $btn_submit ) {
			$settings_for_js['buttonText'] = [];
			if ( $btn_next ) $settings_for_js['buttonText']['next'] = $btn_next;
			if ( $btn_prev ) $settings_for_js['buttonText']['previous'] = $btn_prev;
			if ( $btn_submit ) $settings_for_js['buttonText']['submit'] = $btn_submit;
		}

		// Error Messages
		$error_req   = get_post_meta( $config_post_id, '_lf_error_required', true );
		$error_email = get_post_meta( $config_post_id, '_lf_error_email', true );
		$error_phone = get_post_meta( $config_post_id, '_lf_error_phone', true );
		$error_url   = get_post_meta( $config_post_id, '_lf_error_url', true );
		
		$settings_for_js['errorMessages'] = [];
		if ( $error_req ) $settings_for_js['errorMessages']['required'] = $error_req;
		if ( $error_email ) $settings_for_js['errorMessages']['email'] = $error_email;
		if ( $error_phone ) $settings_for_js['errorMessages']['phone'] = $error_phone;
		if ( $error_url ) $settings_for_js['errorMessages']['url'] = $error_url;
		
		wp_localize_script( 'landeseiten-form-init-script', 'lf_form_settings', $settings_for_js );

		// --- 3. Generate Dynamic CSS Variables ---
		$get_clean_meta = function( $key ) use ( $config_post_id ) {
			return rtrim( trim( get_post_meta( $config_post_id, $key, true ) ), ' ;' );
		};

		$css = "";
		// Target this specific form instance
		$selector = ".gform_wrapper.cs-landeseiten-form[data-form-index='" . absint( $form['form_index'] ) . "']";

		$css .= "{$selector} {";
		
		// Colors
		if ( $v = $get_clean_meta( '_lf_accent_color' ) ) $css .= "--landeseiten-form-accent-color: {$v};";
		if ( $v = $get_clean_meta( '_lf_text_color' ) ) $css .= "--landeseiten-form-text-color: {$v};";
		if ( $v = $get_clean_meta( '_lf_active_text_color' ) ) $css .= "--landeseiten-form-accent-contrast: {$v};";
		if ( $v = $get_clean_meta( '_lf_form_bg_color' ) ) $css .= "--landeseiten-form-bg-color: {$v};";
		
		// Typography
		if ( $v = $get_clean_meta( '_lf_label_font_size' ) ) $css .= "--landeseiten-form-label-font-size: {$v}px;";
		if ( $v = $get_clean_meta( '_lf_input_font_size' ) ) $css .= "--landeseiten-form-input-font-size: {$v}px;";
		if ( $v = $get_clean_meta( '_lf_font_family' ) ) $css .= "font-family: {$v};";
		
		// Input Styles
		if ( $v = $get_clean_meta( '_lf_input_bg_color' ) ) $css .= "--landeseiten-form-bg-color: {$v};";
		if ( $v = $get_clean_meta( '_lf_input_text_color' ) ) $css .= "--landeseiten-form-text-color: {$v};";
		if ( $v = $get_clean_meta( '_lf_input_border_color' ) ) $css .= "--landeseiten-form-border-color: {$v};";
		if ( $v = $get_clean_meta( '_lf_input_height' ) ) $css .= "--landeseiten-form-input-height: {$v}px;";
		
		// Layout & Geometry
		if ( $v = $get_clean_meta( '_lf_container_max_width' ) ) $css .= "--landeseiten-form-container-width: {$v}px;";
		if ( $get_clean_meta( '_lf_enable_shadow' ) ) $css .= "box-shadow: 0 10px 40px rgba(0,0,0,0.08); padding: 40px; border-radius: 12px;";

		// Progress Bar
		if ( $v = $get_clean_meta( '_lf_progress_bar_color' ) ) $css .= "--landeseiten-form-progress-color: {$v};";

		// Border Radius Logic (Smart switch between box and pill shape)
		if ( $v = $get_clean_meta( '_lf_input_border_radius' ) ) {
			$css .= "--landeseiten-form-input-border-radius: {$v}px;";
			if ( $v > 20 ) {
				$css .= "--landeseiten-form-choice-border-radius: 50px;"; // Pill shape for large radius
			} else {
				$css .= "--landeseiten-form-choice-border-radius: {$v}px;"; // Match input for small radius
			}
		}
		if ( $v = $get_clean_meta( '_lf_btn_border_radius' ) ) $css .= "--landeseiten-form-button-border-radius: {$v}px;";
		
		$css .= "}"; // End Root Variables

		// Focus States
		$f_bg  = $get_clean_meta( '_lf_input_focus_bg_color' );
		$f_txt = $get_clean_meta( '_lf_input_focus_text_color' );
		$f_brd = $get_clean_meta( '_lf_input_focus_border_color' );
		$accent = $get_clean_meta( '_lf_accent_color' ); 
		
		if ( $f_bg || $f_txt || $f_brd || $accent ) {
			 $css .= "{$selector} .ginput_container input:focus, {$selector} .ginput_container select:focus, {$selector} .ginput_container textarea:focus {";
			 if ( $f_bg ) $css .= "background-color: {$f_bg} !important;";
			 if ( $f_txt ) $css .= "color: {$f_txt} !important;";
			 
			 // Default to accent color if no specific border color is chosen
			 $borderColor = $f_brd ? $f_brd : $accent;
			 if ( $borderColor ) {
				 $css .= "border-color: {$borderColor} !important; box-shadow: inset 0 0 0 1px {$borderColor} !important; outline: none !important;";
			 }
			 $css .= "}";
		}
		
		// Button Styling
		$p_bg = $get_clean_meta( '_lf_primary_btn_bg' ); 
		$p_txt = $get_clean_meta( '_lf_primary_btn_text_color' ); 
		$p_brd = $get_clean_meta( '_lf_primary_btn_border' );
		$full_width = $get_clean_meta( '_lf_btn_full_width' );

		if ( $p_bg || $p_txt || $p_brd || $full_width ) {
			$css .= "{$selector} .button-next, {$selector} input[type='submit'].gform_button {";
			if ( $p_bg ) $css .= "background: {$p_bg} !important;";
			if ( $p_txt ) $css .= "color: {$p_txt} !important;";
			if ( $p_brd ) $css .= "border: {$p_brd} !important;";
			if ( $full_width ) $css .= "width: 100% !important; display: block !important;";
			$css .= "}";
		}
		
		$s_bg = $get_clean_meta( '_lf_secondary_btn_bg' ); 
		$s_txt = $get_clean_meta( '_lf_secondary_btn_text_color' ); 
		$s_brd = $get_clean_meta( '_lf_secondary_btn_border' );
		
		if ( $s_bg || $s_txt || $s_brd ) {
			$css .= "{$selector} .button-previous {";
			if ( $s_bg ) $css .= "background: {$s_bg} !important;";
			if ( $s_txt ) $css .= "color: {$s_txt} !important;";
			if ( $s_brd ) $css .= "border: {$s_brd} !important;";
			$css .= "}";
		}
		
		LF_Dynamic_CSS::add( $css );
	}
	return $form;
}

/**
 * Adds custom classes to the form wrapper for easier CSS targeting.
 *
 * @param string $form_string The form HTML string.
 * @param array  $form        The form object.
 * @return string Modified form HTML.
 */
function lf_add_wrapper_class_to_html( $form_string, $form ) {
	return str_replace( 'gform_wrapper', 'gform_wrapper cs-landeseiten-form landeseiten-form-active', $form_string );
}

/* ---------------------------------------------------------------------------
 * 3. CUSTOM VALIDATION & DATA HANDLING
 * --------------------------------------------------------------------------- */

/**
 * Allows "Date Range" format (Date to Date) to pass validation.
 *
 * Gravity Forms strictly validates dates by default. This filter tells GF
 * that a string containing " to " is a valid date input for our custom picker.
 */
add_filter( 'gform_field_validation', 'lf_allow_date_ranges', 10, 4 );
function lf_allow_date_ranges( $result, $value, $form, $field ) {
	if ( $field->type === 'date' ) {
		if ( strpos( $value, ' to ' ) !== false ) {
			$result['is_valid'] = true;
			$result['message']  = '';
		}
	}
	return $result;
}

/**
 * Saves the raw Date Range string to the database.
 *
 * Bypasses Gravity Forms standard date sanitization which might corrupt
 * the "YYYY-MM-DD to YYYY-MM-DD" format.
 */
add_filter( 'gform_save_field_value', 'lf_save_date_range_value', 10, 4 );
function lf_save_date_range_value( $value, $entry, $field, $form ) {
	if ( $field->type === 'date' ) {
		$input_name = 'input_' . $field->id;
		// Check $_POST directly for the raw value
		if ( isset( $_POST[ $input_name ] ) && strpos( $_POST[ $input_name ], ' to ' ) !== false ) {
			return sanitize_text_field( $_POST[ $input_name ] );
		}
	}
	return $value;
}

/**
 * Ensures the Date Range displays correctly in the Admin Entry View.
 *
 * Prevents Gravity Forms from trying to re-format the range string
 * into a standard date format (which causes "//" errors).
 */
add_filter( 'gform_entry_field_value', 'lf_display_date_range_value', 10, 4 );
function lf_display_date_range_value( $value, $field, $entry, $form ) {
	if ( $field->type === 'date' ) {
		$stored_value = rgar( $entry, $field->id );
		if ( strpos( $stored_value, ' to ' ) !== false ) {
			return $stored_value;
		}
	}
	return $value;
}