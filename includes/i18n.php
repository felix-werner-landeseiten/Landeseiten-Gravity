<?php
/**
 * Multilingual support for the plugin's frontend texts (WPML / Polylang).
 *
 * The button labels and validation messages are stored once per config post.
 * Gravity Forms Multilingual translates the Gravity form in place (same form ID
 * in every language), so these texts are registered as WPML strings and looked
 * up in the page language at render time. One config covers all languages.
 *
 * Polylang implements the same WPML hooks, so it is supported through the same
 * code path. Without a multilingual plugin nothing here changes the output.
 *
 * @package LandeseitenForm
 * @since   2.5.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/** WPML string domain (Polylang: string group) for all plugin texts. */
const LF_STRING_DOMAIN = 'Landeseiten Form';

/**
 * The translatable texts: meta key => where it goes in the JS config.
 *
 * `label` is part of the WPML string name. Keep it unchanged: renaming it
 * orphans every translation already made for that text.
 *
 * @return array<string,array{group:string,key:string,label:string}>
 */
function lf_text_fields() {
	return [
		'_lf_btn_next_text'   => [ 'group' => 'buttonText', 'key' => 'next', 'label' => 'Next button' ],
		'_lf_btn_prev_text'   => [ 'group' => 'buttonText', 'key' => 'previous', 'label' => 'Previous button' ],
		'_lf_btn_submit_text' => [ 'group' => 'buttonText', 'key' => 'submit', 'label' => 'Submit button' ],
		'_lf_error_required'  => [ 'group' => 'errorMessages', 'key' => 'required', 'label' => 'Error: required field' ],
		'_lf_error_email'     => [ 'group' => 'errorMessages', 'key' => 'email', 'label' => 'Error: invalid email' ],
		'_lf_error_phone'     => [ 'group' => 'errorMessages', 'key' => 'phone', 'label' => 'Error: invalid phone' ],
		'_lf_error_url'       => [ 'group' => 'errorMessages', 'key' => 'url', 'label' => 'Error: invalid URL' ],
		'_lf_error_consent'   => [ 'group' => 'errorMessages', 'key' => 'consent', 'label' => 'Error: consent required' ],
	];
}

/**
 * Default (German) texts. Must match LF_DEFAULT_CONFIG in assets/js/init.js.
 *
 * @return array<string,string>
 */
function lf_text_defaults() {
	return array_merge(
		[
			'_lf_btn_next_text'   => 'Weiter →',
			'_lf_btn_prev_text'   => '← Zurück',
			'_lf_btn_submit_text' => 'Absenden',
		],
		lf_error_message_defaults()
	);
}

/**
 * Built-in English texts, used on non-German pages while a default text has
 * no translation in WPML / Polylang.
 *
 * @return array<string,string>
 */
function lf_text_defaults_en() {
	return [
		'_lf_btn_next_text'   => 'Next →',
		'_lf_btn_prev_text'   => '← Back',
		'_lf_btn_submit_text' => 'Submit',
		'_lf_error_required'  => 'This field is required.',
		'_lf_error_email'     => 'Please enter a valid email address.',
		'_lf_error_phone'     => 'Please enter a valid phone number.',
		'_lf_error_url'       => 'Please enter a valid web address.',
		'_lf_error_consent'   => 'Please agree to the terms.',
	];
}

/**
 * Whether $value is one of the plugin's German default texts for $meta_key
 * (current or an earlier default), i.e. the site never customised it.
 */
function lf_is_default_text( $meta_key, $value ) {
	$defaults = lf_text_defaults();
	$known    = isset( $defaults[ $meta_key ] ) ? [ $defaults[ $meta_key ] ] : [];
	$legacy   = lf_error_message_legacy_defaults();
	if ( isset( $legacy[ $meta_key ] ) ) {
		$known = array_merge( $known, $legacy[ $meta_key ] );
	}
	return in_array( trim( (string) $value ), $known, true );
}

/**
 * The text a config post shows, in the site's source language.
 *
 * Empty settings fall back to the defaults, and error messages that still
 * match an old default use the current default (see lf_get_custom_error_message).
 */
function lf_get_text( $post_id, $meta_key ) {
	if ( 0 === strpos( $meta_key, '_lf_error_' ) ) {
		$value = lf_get_custom_error_message( $post_id, $meta_key );
	} else {
		$value = trim( (string) get_post_meta( $post_id, $meta_key, true ) );
	}
	if ( '' === $value ) {
		$defaults = lf_text_defaults();
		$value    = isset( $defaults[ $meta_key ] ) ? $defaults[ $meta_key ] : '';
	}
	return $value;
}

/**
 * WPML string name for one text of one Gravity form, e.g. "Form 3: Next button".
 */
function lf_string_name( $form_id, $meta_key ) {
	$fields = lf_text_fields();
	return sprintf( 'Form %d: %s', absint( $form_id ), $fields[ $meta_key ]['label'] );
}

/**
 * Current page language from WPML / Polylang, or '' without a multilingual plugin.
 */
function lf_current_language() {
	$lang = apply_filters( 'wpml_current_language', null );
	return ( is_string( $lang ) && '' !== $lang && 'all' !== $lang ) ? $lang : '';
}

/**
 * Whether the current page is German. Uses the locale, which WPML and Polylang
 * switch per language, so custom language codes (e.g. "de-ch") still match.
 */
function lf_is_german_page() {
	return 0 === stripos( (string) get_locale(), 'de' );
}

/**
 * Returns $value in the current page language.
 *
 * Order: translation from WPML / Polylang → built-in English for untranslated
 * default texts on non-German pages → $value unchanged.
 */
function lf_translate_text( $value, $meta_key, $form_id ) {
	$lang = lf_current_language();
	if ( '' === $lang ) {
		return $value;
	}

	$translated = apply_filters( 'wpml_translate_single_string', $value, LF_STRING_DOMAIN, lf_string_name( $form_id, $meta_key ), $lang );
	if ( is_string( $translated ) && '' !== trim( $translated ) && $translated !== $value ) {
		return $translated;
	}

	if ( ! lf_is_german_page() && lf_is_default_text( $meta_key, $value ) ) {
		$english = lf_text_defaults_en();
		return $english[ $meta_key ];
	}

	return $value;
}

/**
 * Texts for the frontend JS config of one form, in the page language.
 *
 * @param int $post_id Config post ID.
 * @param int $form_id Gravity Forms form ID.
 * @return array{buttonText:array<string,string>,errorMessages:array<string,string>}
 */
function lf_get_frontend_texts( $post_id, $form_id ) {
	$texts = [ 'buttonText' => [], 'errorMessages' => [] ];
	foreach ( lf_text_fields() as $meta_key => $field ) {
		$texts[ $field['group'] ][ $field['key'] ] = lf_translate_text( lf_get_text( $post_id, $meta_key ), $meta_key, $form_id );
	}

	/**
	 * Filters the button labels and error messages sent to the frontend.
	 *
	 * @since 2.5.0
	 * @param array $texts   ['buttonText' => [...], 'errorMessages' => [...]].
	 * @param int   $form_id Gravity Forms form ID.
	 * @param int   $post_id Config post ID.
	 */
	return apply_filters( 'lf_frontend_texts', $texts, $form_id, $post_id );
}

/**
 * Registers every config's texts with WPML String Translation / Polylang.
 *
 * Runs on admin page loads and only does work when the texts changed since the
 * last registration (config saved, defaults changed in an update, or String
 * Translation installed later), so existing configs need no re-save.
 *
 * @since 2.5.0
 */
function lf_register_strings() {
	$has_hook = (bool) has_action( 'wpml_register_single_string' );
	if ( wp_doing_ajax() || ( ! $has_hook && ! function_exists( 'icl_register_string' ) ) ) {
		return;
	}

	$post_ids = get_posts( [
		'post_type'      => 'lf_form',
		'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
		'no_found_rows'    => true,
		'suppress_filters' => true,
	] );

	update_meta_cache( 'post', $post_ids );

	$strings = [];
	foreach ( $post_ids as $post_id ) {
		$form_id = absint( get_post_meta( $post_id, '_lf_gravity_form_id', true ) );
		if ( ! $form_id ) {
			continue;
		}
		foreach ( array_keys( lf_text_fields() ) as $meta_key ) {
			$strings[ lf_string_name( $form_id, $meta_key ) ] = lf_get_text( $post_id, $meta_key );
		}
	}

	$source_lang = apply_filters( 'wpml_default_language', null );
	$signature   = md5( (string) $source_lang . wp_json_encode( $strings ) );
	if ( get_option( 'lf_registered_strings' ) === $signature ) {
		return;
	}

	$source_lang = is_string( $source_lang ) ? $source_lang : '';
	foreach ( $strings as $name => $value ) {
		if ( $has_hook ) {
			do_action( 'wpml_register_single_string', LF_STRING_DOMAIN, $name, $value, false, $source_lang );
		} else {
			icl_register_string( LF_STRING_DOMAIN, $name, $value, false, $source_lang );
		}
	}
	update_option( 'lf_registered_strings', $signature, true );
}
add_action( 'admin_init', 'lf_register_strings', 20 );

/**
 * Admin hint shown above the texts: where to translate them.
 *
 * @return string HTML, or '' on single-language sites.
 */
function lf_translation_notice_html() {
	if ( defined( 'WPML_ST_VERSION' ) ) {
		$url  = admin_url( 'admin.php?page=wpml-string-translation/menu/string-translation.php&context=' . rawurlencode( LF_STRING_DOMAIN ) );
		$text = sprintf(
			/* translators: %s: link to WPML String Translation */
			__( 'Multilingual: translate these texts in %s (domain "Landeseiten Form"). Untranslated default texts show in English on non-German pages.', 'landeseiten-form' ),
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'WPML → String Translation', 'landeseiten-form' ) . '</a>'
		);
	} elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
		$text = esc_html__( 'Multilingual: install and activate WPML String Translation to translate these texts. Until then, untranslated default texts show in English on non-German pages.', 'landeseiten-form' );
	} elseif ( defined( 'POLYLANG_VERSION' ) ) {
		$url  = admin_url( 'admin.php?page=mlang_strings&group=' . rawurlencode( LF_STRING_DOMAIN ) );
		$text = sprintf(
			/* translators: %s: link to Polylang string translations */
			__( 'Multilingual: translate these texts in %s (group "Landeseiten Form"). Untranslated default texts show in English on non-German pages.', 'landeseiten-form' ),
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Languages → Translations', 'landeseiten-form' ) . '</a>'
		);
	} else {
		return '';
	}
	return '<p class="lf-i18n-notice">🌐 ' . $text . '</p>';
}
