/*
 * == Landeseiten Form - Initializer ==
 *
 * Description:   Bootstraps the Landeseiten Form logic when the DOM is ready.
 * Handles merging of PHP settings with defaults and initializing form instances.
 * Author:        Landeseiten.de
 * Version:       2.0.0
 */

document.addEventListener("DOMContentLoaded", () => {
  // Locate all forms marked as active by our PHP hooks
  const landeseitenForms = document.querySelectorAll(
    ".landeseiten-form-active"
  );

  if (landeseitenForms.length === 0) {
    return;
  }

  // Default configuration values
  // These act as fallbacks if no specific settings are provided from the backend.
  const defaultConfig = {
    scrollTopMargin: 150,
    mode: "reveal",
    autoFocus: true,
    enterToAdvance: true,
    autoProgressRadio: true,
    hideErrorUntilDirty: true,
    progressBar: false, // Default to false unless enabled in backend
    buttonText: {
      next: "Weiter →",
      previous: "← Zurück",
      submit: "Absenden",
    },
    errorMessages: {
      required: "Dieses Feld ist erforderlich.",
      email: "Bitte geben Sie eine gültige E-Mail-Adresse ein.",
      phone: "Bitte geben Sie eine gültige Telefonnummer (nur Ziffern) ein.",
      url: "Bitte geben Sie eine gültige Web-Adresse ein.",
      consent: "Bitte stimmen Sie den Bedingungen zu.", // Default consent error
    },
  };

  // Retrieve settings passed from PHP via wp_localize_script
  const settingsFromPHP =
    typeof lf_form_settings !== "undefined" ? lf_form_settings : {};

  // --- Create the final configuration ---
  // We perform a deep merge for nested objects (buttonText, errorMessages)
  // to ensure user settings override defaults without deleting other keys.
  const finalConfig = {
    ...defaultConfig,
    ...settingsFromPHP, // Merges top-level keys (mode, progressBar, etc.)
    buttonText: {
      ...defaultConfig.buttonText,
      ...(settingsFromPHP.buttonText || {}),
    },
    errorMessages: {
      ...defaultConfig.errorMessages,
      ...(settingsFromPHP.errorMessages || {}),
    },
  };

  // Initialize logic for each matching form found on the page
  landeseitenForms.forEach((formElement) => {
    try {
      const form = new LandeseitenForm(
        formElement,
        new GravityFieldsProvider(formElement),
        new GravityFormControlsProvider(formElement),
        finalConfig
      );
      form.init();
    } catch (error) {
      console.error("Landeseiten Form Initialization Error:", error);
    }
  });
});
