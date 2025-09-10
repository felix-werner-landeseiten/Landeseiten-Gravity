=== Landeseiten Form for Gravity Forms ===
Contributors: gamatech89, felix-werner-landeseiten
Tags: gravity forms, form, multi-step, one page, landing page, conversion, file upload
Requires at least: 5.8
Tested up to: 6.5
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.txt

A powerful wrapper for Gravity Forms that transforms any form into an engaging, one-question-at-a-time, animated user experience with deep customization.

== Description ==

The Landeseiten Form plugin is a powerful wrapper for Gravity Forms designed to increase user engagement and form completion rates. It takes any standard, single-page Gravity Form and converts it into an interactive, multi-step experience without needing to configure multiple pages in the form builder.

With a dedicated settings panel, you can create unlimited configurations and apply them to different Gravity Forms. Customize fonts, colors, button text, and error messages to perfectly match your site's design.

**Features:**
* Converts any Gravity Form into a one-question-at-a-time layout.
* Now supports File Upload fields.
* Allows specific fields (like hidden or read-only fields) to be excluded from the multi-step experience.
* Two animation modes: "Reveal" and "Paged".
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

= How do I exclude a field from the multi-step experience? =

The plugin automatically ignores fields that are set to "Hidden" in Gravity Forms. For any other field you want to make visible but not interactive (like a pre-populated, read-only field), go to the field's settings, click the "Appearance" tab, and add `lf-skip` to the "Custom CSS Class" box.

= Can I use this for multiple forms on the same site? =

Yes. You can create as many "Landeseiten Form" configurations as you need. Each one can target a different Gravity Form and have its own unique styling and settings.

== Changelog ==

= 1.3.1 =
* FIX: Resolved an issue where selecting a date from a Gravity Forms Date Picker field would not enable the 'Next' button or remove the validation error.

= 1.3.0 =
* NEW: Added full support for File Upload fields.
* NEW: Added the ability to exclude fields from the multi-step flow by adding the `lf-skip` CSS class.
* IMPROVEMENT: The plugin now automatically ignores Gravity Forms' standard "Hidden" fields.
* IMPROVEMENT: File upload fields now auto-advance upon file selection but do not automatically open the file dialog on focus.

= 1.2.0 =
* NEW: Added "Input Field Styling" section to the settings.
* NEW: Added options to customize background, text, and border colors for input fields in their normal and focus states.
* NEW: Added professional styling for validation error messages.
* FIX: Corrected the input field focus style to be a full `inset` box-shadow.

= 1.1.1 =
* FIX: Corrected CSS specificity to ensure custom accent color is applied to the final submit button and input focus styles.

= 1.1.0 =
* NEW: Added validation for Website/URL fields and corresponding settings.
* NEW: Added settings to customize Font Family, Font Sizes, Text Colors, and Button/Error text.

= 1.0.0 =
* Initial release of the plugin.