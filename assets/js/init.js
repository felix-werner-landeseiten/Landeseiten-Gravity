/*
 * == Landeseiten Form - Initializer ==
 */
document.addEventListener('DOMContentLoaded', () => {
    const landeseitenForms = document.querySelectorAll('.landeseiten-form-active');

    if (landeseitenForms.length === 0) {
        return; 
    }

    // Default configuration.
    const defaultConfig = {
        scrollTopMargin: 150,
        mode: "reveal",
        autoFocus: true,
        enterToAdvance: true,
        autoProgressRadio: true,
        hideErrorUntilDirty: true,
        buttonText: {
            next: "Weiter →",
            previous: "← Zurück"
        },
        errorMessages: {
            required: "Dieses Feld ist erforderlich.",
            email: "Bitte geben Sie eine gültige E-Mail-Adresse ein.",
            phone: "Bitte geben Sie eine gültige Telefonnummer (nur Ziffern) ein.",
        },
    };

    // The settings object passed from PHP.
    const settingsFromPHP = typeof lf_form_settings !== 'undefined' ? lf_form_settings : {};

    // --- Create the final configuration, correctly merging nested objects ---
    const finalConfig = {
        ...defaultConfig,
        ...settingsFromPHP, // Add top-level settings from PHP (e.g., mode)
        buttonText: {
            ...defaultConfig.buttonText, // Start with defaults
            ...(settingsFromPHP.buttonText || {}) // Add settings from PHP
        },
        errorMessages: {
            ...defaultConfig.errorMessages, // Start with defaults
            ...(settingsFromPHP.errorMessages || {}) // Add settings from PHP
        }
    };

    // Initialize each form found on the page.
    landeseitenForms.forEach((formElement) => {
        const form = new LandeseitenForm(
            formElement,
            new GravityFieldsProvider(formElement),
            new GravityFormControlsProvider(formElement),
            finalConfig
        );
        form.init();
    });
});