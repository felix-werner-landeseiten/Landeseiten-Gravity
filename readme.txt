=== Landeseiten Form for Gravity Forms ===
Contributors: gamatech89
Tags: gravity forms, form, multi-step, one page, landing page, conversion
Requires at least: 5.8
Tested up to: 6.5
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.txt

A powerful wrapper for Gravity Forms that transforms any form into an engaging, one-question-at-a-time, animated user experience.

== Description ==

The Landeseiten Form plugin is a powerful wrapper for Gravity Forms designed to increase user engagement and form completion rates. It takes any standard, single-page Gravity Form and converts it into an interactive, multi-step experience without needing to configure multiple pages in the form builder.

With a dedicated settings panel, you can create unlimited configurations and apply them to different Gravity Forms. Customize fonts, colors, button text, and error messages to perfectly match your site's design.

**Features:**
* Converts any Gravity Form into a one-question-at-a-time layout.
* Two animation modes: "Reveal" (next question appears below) and "Paged" (next question replaces the current one).
* Create and manage unlimited form configurations.
* Customize colors, fonts, font sizes, button text, and error messages on a per-form basis.
* Automatic updates directly from your GitHub repository.

== Installation ==

1.  Upload the `landeseiten-form` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to the new "Landeseiten Forms" menu in your WordPress admin.
4.  Click "Add New" to create a configuration.
5.  Select a target Gravity Form, customize your settings, and click "Publish".
6.  Place the Gravity Form's shortcode on any page, and the new layout will be applied automatically.

== Frequently Asked Questions ==

= How do I apply the layout to a form? =

You first need to create a configuration. Go to the "Landeseiten Forms" menu in your admin dashboard, click "Add New", choose the Gravity Form you want to target from the dropdown, customize the settings, and publish it. The layout will then be applied automatically wherever that Gravity Form appears.

= Can I use this for multiple forms on the same site? =

Yes. You can create as many "Landeseiten Form" configurations as you need. Each one can target a different Gravity Form and have its own unique styling and settings.

== Changelog ==

= 1.2.0 =
* NEW: Added "Input Field Styling" section to the settings.
* NEW: Added options to customize background, text, and border colors for input fields in their normal state.
* NEW: Added options to customize background, text, and border/glow colors for input fields in their focus state.
* NEW: Added professional styling for validation error messages.
* FIX: Corrected the input field focus style to be a full `inset` box-shadow, preventing it from being clipped.

= 1.1.1 =
* FIX: Corrected CSS specificity to ensure custom accent color is applied to the final submit button.
* FIX: Added a custom :focus style for text inputs to override default Gravity Forms theme styles.

= 1.1.0 =
* NEW: Added validation for Website/URL fields.
* NEW: Added settings to customize Font Family, Font Sizes, and Text Colors.
* NEW: Added settings to customize Next/Previous button text.
* NEW: Added settings to customize all validation error messages (Required, Email, Phone, URL).

= 1.0.0 =
* Initial release of the plugin.