=== Landeseiten Form for Gravity Forms ===
Contributors: gamatech89, felix-werner-landeseiten
Tags: gravity forms, form, multi-step, one page, landing page, conversion, progress bar, datepicker, gdpr
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.txt

A premium wrapper for Gravity Forms that transforms standard forms into engaging, multi-step user experiences with modern styling, animations, and advanced features.

== Description ==

The Landeseiten Form plugin is the ultimate conversion booster for Gravity Forms. It automatically converts any standard Gravity Form into a high-end, interactive, "one-question-at-a-time" experience—perfect for funnels, lead generation, and applications.

**Version 2.0.0 is a major overhaul**, introducing a completely redesigned backend, modern date pickers, visual progress bars, and deep styling controls.

With the new Tabbed Settings Panel, you can create unlimited configurations to match your brand perfectly. Control everything from border radius and shadows to custom error messages and button widths.

**Key Features:**

* **Multi-Step Experience:** Converts long forms into engaging slides.
* **Visual Progress Bar:** Keep users motivated with a customizable progress bar.
* **Modern Date Picker:** Integrated **Flatpickr** library for mobile-friendly Date Range selections (Booking style).
* **Smart Validation:**
    * **Phone:** Permissive input (allows spaces, +49, dashes) with strict length checks.
    * **Consent:** Dedicated support for GDPR/Privacy Policy checkboxes.
* **Deep Customization:**
    * **Design:** Control Border Radius, Box Shadows, Input Heights, and Container Max-Width.
    * **Colors:** Full control over Accents, Backgrounds, Text, and Focus states.
    * **Typography:** Customize Font Family and Sizes.
* **Advanced Layouts:** Choose between "Reveal" (Vertical Scroll) and "Paged" (Horizontal Slide) animations.
* **Developer Friendly:** Clean code structure, strictly versioned assets, and automatic updates via GitHub.

== Installation ==

1.  Upload the `landeseiten-form` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to the "Landeseiten Forms" menu in your WordPress admin.
4.  Click "Add New" to create a configuration.
5.  Select your target Gravity Form, configure your Design/Text settings in the new Tabbed UI, and click "Publish".
6.  Place the standard Gravity Form shortcode on any page. The plugin will automatically detect and style it.

== Frequently Asked Questions ==

= How do I enable the Date Range Picker? =
In your Gravity Form editor, select your Date field. Under the "General" tab, look for "Landeseiten Date Options". Check "Enable Date Range". This replaces the standard date picker with a modern Flatpickr instance.

= How do I exclude a field from the multi-step experience? =
The plugin automatically ignores "Hidden" fields. To skip a visible field (e.g., read-only data), go to the field's Appearance tab and add the Custom CSS Class: `lf-skip`.

= Can I customize the "Submit" button text? =
Yes! In version 2.0.0, go to the "Content & Text" tab in your Landeseiten Form configuration and enter your custom text (e.g., "Get Quote Now") in the Submit Button field.

== Changelog ==

= 2.0.0 =
* **MAJOR RELEASE**
* **NEW:** Visual Progress Bar with customizable color.
* **NEW:** Completely redesigned Admin UI with a clean, Tabbed layout (Material Design style).
* **NEW:** Integrated Flatpickr for modern, mobile-friendly Date & Date Range selection.
* **NEW:** Dedicated "Consent Field" support for GDPR/Privacy checkboxes.
* **NEW:** Design Controls: Add Box Shadows, adjust Border Radius, set Container Max Width, and toggle Full Width Buttons.
* **NEW:** Option to override the final "Submit" button text.
* **IMPROVEMENT:** Smart Phone Validation (now allows international formats like +49 and spaces).
* **IMPROVEMENT:** Refactored codebase for better performance and stricter security.
* **FIX:** Resolved CSS specificity issues with input backgrounds and focus states.

= 1.3.1 =
* FIX: Resolved an issue where selecting a date from a Gravity Forms Date Picker field would not enable the 'Next' button.

= 1.3.0 =
* NEW: Added full support for File Upload fields.
* NEW: Added `lf-skip` class to exclude fields from flow.
* IMPROVEMENT: Automatically ignores Hidden fields.

= 1.2.0 =
* NEW: Added "Input Field Styling" section (Focus states, Borders).
* NEW: Professional styling for validation error messages.

= 1.1.0 =
* NEW: Added validation for Website/URL fields.
* NEW: Added Typography settings (Font Family, Size).

= 1.0.0 =
* Initial release.