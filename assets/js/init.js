/*
 * == Landeseiten Form - Initializer ==
 *
 * Description:   Bootstraps the Landeseiten Form logic when the DOM is ready.
 * Single source of truth for default configuration values.
 * Handles AJAX re-initialization after Gravity Forms AJAX submissions.
 * Author:        Landeseiten.de
 * Version:       2.2.0
 */

/**
 * Default configuration — SINGLE SOURCE OF TRUTH.
 * PHP settings from wp_localize_script override these per-form.
 */
const LF_DEFAULT_CONFIG = {
  scrollTopMargin: 150,
  mode: "reveal",
  autoFocus: true,
  enterToAdvance: true,
  autoProgressRadio: true,
  hideErrorUntilDirty: true,
  progressBar: false,
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
    consent: "Bitte stimmen Sie den Bedingungen zu.",
  },
};

/**
 * Track which form wrappers have been initialized to avoid double-init.
 */
const _lfInitializedForms = new WeakSet();

/**
 * Initialize a single Landeseiten form wrapper element.
 *
 * @param {HTMLElement} formElement - The .landeseiten-form-active wrapper.
 */
function lfInitForm(formElement) {
  // Prevent double-initialization
  if (_lfInitializedForms.has(formElement)) {
    return;
  }

  try {
    const formId = formElement.getAttribute("data-form-id") || "";
    const settingsVarName = "lf_form_settings_" + formId;
    const settingsFromPHP =
      typeof window[settingsVarName] !== "undefined"
        ? window[settingsVarName]
        : {};

    // Deep merge: per-form PHP settings override defaults
    const finalConfig = {
      ...LF_DEFAULT_CONFIG,
      ...settingsFromPHP,
      buttonText: {
        ...LF_DEFAULT_CONFIG.buttonText,
        ...(settingsFromPHP.buttonText || {}),
      },
      errorMessages: {
        ...LF_DEFAULT_CONFIG.errorMessages,
        ...(settingsFromPHP.errorMessages || {}),
      },
    };

    const form = new LandeseitenForm(
      formElement,
      new GravityFieldsProvider(formElement),
      new GravityFormControlsProvider(formElement),
      finalConfig
    );
    form.init();
    _lfInitializedForms.add(formElement);
  } catch (error) {
    console.error("Landeseiten Form Initialization Error:", error);
  }
}

/**
 * Initialize all Landeseiten forms on the page.
 */
function lfInitAll() {
  document
    .querySelectorAll(".landeseiten-form-active")
    .forEach((el) => lfInitForm(el));
}

// --- Boot on DOMContentLoaded ---
// Guard for deferred/delayed script loading (e.g. WP Rocket):
// if the DOM is already ready by the time this script runs, call directly.
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", lfInitAll);
} else {
  lfInitAll();
}

// --- Re-initialize after Gravity Forms AJAX submissions ---
// GF triggers gform_post_render after AJAX form submission or page change.
// This ensures the multi-step UI re-attaches after a validation error reload.
if (typeof jQuery !== "undefined") {
  jQuery(document).on("gform_post_render", function (event, formId) {
    const wrapper = document.querySelector(
      '.landeseiten-form-active[data-form-id="' + formId + '"]'
    );
    if (wrapper) {
      // Allow the form to re-init by removing from tracked set
      _lfInitializedForms.delete(wrapper);
      lfInitForm(wrapper);
    }
  });
}
