/*
 * == CS Landeseiten Form - Scripts ==
 *
 * Description:   Main JavaScript for the CS Landeseiten Form Gravity Forms wrapper.
 * Handles animations, validation, state management, and the progress bar.
 * Author:        Landeseiten.de
 * Version:       2.5.0
 */

// -----------------------------------------------------------------------------
// VALIDATION CLASSES
// -----------------------------------------------------------------------------

/**
 * Base class for all field validators.
 */
class Validator {
  isValid(field, messages) {
    throw new Error("Validator.isValid() must be implemented by a subclass.");
  }
}

/**
 * Validates that a field has a value.
 */
class RequiredValidator extends Validator {
  isValid(field, messages) {
    const value = field.getValue();
    let hasValue = false;

    if (Array.isArray(value)) {
      hasValue = value.length > 0;
    } else if (typeof value === "string") {
      hasValue = value.trim() !== "";
    } else if (value instanceof FileList) {
      hasValue = value.length > 0;
    } else {
      hasValue = value != null;
    }

    // Consent boxes get their own configurable message.
    const message =
      field instanceof ConsentField && messages.consent
        ? messages.consent
        : messages.required;

    return {
      valid: hasValue,
      message: hasValue ? null : message,
    };
  }
}

/**
 * Validates that a field's value is a properly formatted email address.
 */
class EmailValidator extends Validator {
  isValid(field, messages) {
    const value = field.getValue();
    if (typeof value !== "string" || value.trim() === "") {
      return { valid: true, message: null };
    }

    // Standard Email Regex
    const regex =
      /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}(\.[0-9]{1,3}){3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    const isValid = regex.test(value.toLowerCase());

    return {
      valid: isValid,
      message: isValid ? null : messages.email,
    };
  }
}

/**
 * Validates phone numbers using a permissive approach.
 * Allows spaces, +, -, () but ensures at least 7 digits are present.
 */
class PhoneValidator extends Validator {
  isValid(field, messages) {
    const value = field.getValue();
    if (typeof value !== "string" || value.trim() === "") {
      return { valid: true, message: null };
    }

    // 1. Strip all formatting to count actual numbers
    const digitsOnly = value.replace(/[^0-9]/g, "");

    // 2. Check length (International numbers are usually 7-15 digits)
    const isLengthValid = digitsOnly.length >= 7 && digitsOnly.length <= 16;

    // 3. Ensure no illegal characters (letters, etc) remain in the raw value
    // Allowed: 0-9, space, +, -, (, )
    const hasValidChars = /^[0-9+\-\s()]*$/.test(value);

    return {
      valid: isLengthValid && hasValidChars,
      message: isLengthValid && hasValidChars ? null : messages.phone,
    };
  }
}

/**
 * Validates that a field's value is a properly formatted URL.
 */
class UrlValidator extends Validator {
  isValid(field, messages) {
    const value = field.getValue();
    if (typeof value !== "string" || value.trim() === "") {
      return { valid: true, message: null };
    }

    const regex =
      /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
    const isValid = regex.test(value.toLowerCase());

    return {
      valid: isValid,
      message: isValid ? null : messages.url,
    };
  }
}

// -----------------------------------------------------------------------------
// FIELD CLASSES
// -----------------------------------------------------------------------------

/**
 * Base class for all form field types.
 */
class Field {
  constructor(wrapper) {
    this.wrapper = wrapper;
    this.validators = [];
    this.isDirty = false;
    // Set when GF rendered this field with a server-side validation error
    // (AJAX re-render after a failed submit). Its message stays until the user
    // touches the field — client validators may not cover every server rule.
    this.hasServerError = wrapper.classList.contains("gfield_error");
  }

  addValidator(validator) {
    this.validators.push(validator);
    return this;
  }

  /**
   * Runs the validators without touching the DOM.
   * @returns {string|null} The first error message, or null if valid.
   */
  getError(config) {
    for (const validator of this.validators) {
      const result = validator.isValid(this, config.errorMessages);
      if (!result.valid) return result.message;
    }
    return null;
  }

  validate(config) {
    const errorMessage = this.getError(config);

    if (this.hasServerError && !this.isDirty) {
      // Leave GF's server-side message in place until the user interacts.
    } else if (config.hideErrorUntilDirty && !this.isDirty) {
      // Only show error if the user has interacted with the field (isDirty)
      // or if the config forces errors to show immediately.
      this.displayError(null);
    } else {
      this.displayError(errorMessage);
    }
    return errorMessage === null;
  }

  displayError(message) {
    const errorClassName = "gfield_description validation_message";
    let errorEl = this.wrapper.querySelector(
      `.${errorClassName.replace(/ /g, ".")}`
    );

    this.wrapper.classList.remove("gfield_error");
    if (errorEl) errorEl.style.display = "none";

    if (!message) return;

    this.wrapper.classList.add("gfield_error");
    if (!errorEl) {
      errorEl = document.createElement("div");
      errorEl.className = errorClassName;
      const inputContainer = this.wrapper.querySelector(".ginput_container");
      if (inputContainer) {
        inputContainer.insertAdjacentElement("afterend", errorEl);
      } else {
        this.wrapper.appendChild(errorEl);
      }
    }
    errorEl.textContent = message;
    errorEl.style.display = "block";
  }

  onChange(callback) {
    throw new Error("Field.onChange() must be implemented by a subclass.");
  }

  getValue() {
    throw new Error("Field.getValue() must be implemented by a subclass.");
  }

  show(flag) {
    this.wrapper.classList.toggle("active", flag);
  }

  focus() {
    // Implemented by subclasses
  }

  scrollTo() {
    this.wrapper.scrollIntoView({ behavior: "smooth", block: "center" });
  }
}

/**
 * Represents standard text inputs (text, email, tel, number, url).
 */
class InputField extends Field {
  constructor(container, input) {
    super(container);
    this.input = input;

    // Smart Phone Input Handler: Restricts input to allowed characters
    if (this.input.type === "tel") {
      this.input.addEventListener("input", (event) => {
        const validChars = this.input.value.replace(/[^0-9+\-\s()]/g, "");
        if (this.input.value !== validChars) {
          this.input.value = validChars;
        }
      });
    }
  }

  onChange(callback, onEnterPressed) {
    const handleValidation = () => {
      if (!this.isDirty) {
        this.isDirty = true;
      }
      callback();
    };

    this.input.addEventListener("input", handleValidation);
    this.input.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        onEnterPressed();
      }
    });

    // --- Flatpickr Date Integration ---
    if (
      this.input.classList.contains("datepicker") ||
      this.input.classList.contains("hasDatepicker")
    ) {
      const fieldId = this.input.id;
      const parts = fieldId.split("_");
      const formId = parts[1];
      const configVarName = "lf_datepicker_config_" + formId;
      const config =
        window[configVarName] && window[configVarName][fieldId]
          ? window[configVarName][fieldId]
          : {};
      const mode = config.mode || "single";

      if (typeof flatpickr !== "undefined") {
        // Cleanup legacy Gravity Forms classes
        this.input.classList.remove("datepicker", "gform-datepicker");
        // GF 3.1+ attaches an IMask input mask via data-mask, which wipes values
        // written by Flatpickr. The PHP filter strips it at render; this covers
        // any markup that slipped through (e.g. third-party re-renders).
        this.input.removeAttribute("data-mask");
        // GF 3.x renders its own calendar toggle button; Flatpickr opens on focus.
        const gfToggle = this.input.parentElement
          ? this.input.parentElement.querySelector(".gform-datepicker-toggle")
          : null;
        if (gfToggle) gfToggle.remove();
        if (typeof jQuery !== "undefined") {
          try {
            jQuery(this.input).datepicker("destroy");
            jQuery(this.input).removeClass("hasDatepicker");
          } catch (e) {}
        }

        // Determine locale from per-form settings
        const settingsVarName = "lf_form_settings_" + formId;
        const formSettings = window[settingsVarName] || {};
        const localeConfig = {};
        if (formSettings.calendarLang && formSettings.calendarLang !== "en") {
          const localeObj =
            typeof flatpickr.l10ns !== "undefined"
              ? flatpickr.l10ns[formSettings.calendarLang]
              : null;
          if (localeObj) {
            localeConfig.locale = localeObj;
          }
        }

        flatpickr(this.input, {
          ...localeConfig,
          mode: mode,
          minDate: config.minDate || null,
          maxDate: config.maxDate || null,
          dateFormat: "d.m.Y",
          disableMobile: "true",
          onChange: function (selectedDates, dateStr, instance) {
            if (mode === "range") {
              if (selectedDates.length === 2) {
                handleValidation();
              }
            } else {
              if (selectedDates.length === 1) {
                handleValidation();
              }
            }
          },
        });
      }
    }
  }

  getValue() {
    return this.input.value;
  }

  focus() {
    this.input.focus({ preventScroll: true });
  }
}

/**
 * Represents file upload inputs.
 */
class FileUploadField extends Field {
  constructor(container, input) {
    super(container);
    this.input = input;
  }

  onChange(callback, onFileSelected) {
    const handleChange = (event) => {
      if (!this.isDirty) {
        this.isDirty = true;
      }
      callback(event);
      // Auto-advance if file is selected
      if (this.getValue().length > 0) {
        onFileSelected();
      }
    };
    this.input.addEventListener("change", handleChange);
  }

  getValue() {
    return this.input.files;
  }

  focus() {}
}

/**
 * Represents dropdown select menus.
 */
class SelectField extends Field {
  constructor(container, select) {
    super(container);
    this.select = select;
  }

  onChange(callback) {
    const handleChange = (event) => {
      if (!this.isDirty) {
        this.isDirty = true;
      }
      callback(event);
    };
    this.select.addEventListener("change", handleChange);
  }

  getValue() {
    return this.select.value;
  }

  focus() {
    this.select.focus({ preventScroll: true });
  }
}

/**
 * Represents the Consent / Privacy Policy checkbox.
 */
class ConsentField extends Field {
  constructor(container, input) {
    super(container);
    this.input = input;
  }

  onChange(callback) {
    const handleChange = (event) => {
      if (!this.isDirty) {
        this.isDirty = true;
      }
      callback(event);
    };
    this.input.addEventListener("change", handleChange);
  }

  getValue() {
    // Return value if checked, otherwise null to trigger validation error
    return this.input.checked ? this.input.value : null;
  }

  focus() {
    this.input.focus({ preventScroll: true });
  }
}

/**
 * Base class for multi-choice fields (Radio / Checkbox).
 */
class ChoiceField extends Field {
  constructor(container, choices) {
    super(container);
    this.choices = choices;
  }

  onChange(callback) {
    const handleInput = (event) => {
      if (!this.isDirty) {
        this.isDirty = true;
      }
      callback(event);
    };
    this.choices.forEach((choice) => {
      choice.addEventListener("input", handleInput);
    });
  }

  focus() {
    if (this.choices.length > 0) {
      this.choices[0].focus({ preventScroll: true });
    }
  }
}

class CheckboxField extends ChoiceField {
  getValue() {
    return this.choices
      .filter((choice) => choice.checked)
      .map((choice) => choice.value);
  }
}

class RadioField extends ChoiceField {
  getValue() {
    const selected = this.choices.find((choice) => choice.checked);
    return selected ? selected.value : null;
  }
}

// -----------------------------------------------------------------------------
// PROVIDER CLASSES
// -----------------------------------------------------------------------------

class FieldsProvider {
  provide(config) {
    return [];
  }
}

class GravityFieldsProvider extends FieldsProvider {
  constructor(form) {
    super();
    this.form = form;
  }

  provide(config) {
    return Array.from(this.form.querySelectorAll(".gfield"))
      .map((wrapper) => this.#resolveSingle(wrapper, config))
      .filter(Boolean);
  }

  #resolveSingle(wrapper, config) {
    // Skip hidden or utility fields. Use inline style check (not getComputedStyle)
    // so that deferred/cached CSS does not incorrectly exclude fields at init time.
    // Section and page-break fields are always excluded regardless.
    if (
      wrapper.style.display === "none" ||
      wrapper.classList.contains("gform_validation_container") ||
      wrapper.classList.contains("gfield_visibility_hidden") ||
      wrapper.classList.contains("gfield--type-honeypot") ||
      wrapper.classList.contains("lf-skip") ||
      wrapper.classList.contains("gsection") ||
      wrapper.classList.contains("gfield--type-section") ||
      wrapper.classList.contains("gfield--type-page")
    ) {
      return null;
    }

    const isRadio = wrapper.querySelector(".gfield_radio") !== null;
    const isCheckbox = wrapper.querySelector(".gfield_checkbox") !== null;
    const isConsent = wrapper.classList.contains("gfield--type-consent");
    const textarea = wrapper.querySelector("textarea");
    const fileInput = wrapper.querySelector('input[type="file"]');
    const select = wrapper.querySelector("select");
    const textInput = wrapper.querySelector(
      'input[type="text"], input[type="email"], input[type="number"], input[type="tel"], input[type="url"]'
    );

    let field = null;

    if (isRadio) {
      field = new RadioField(
        wrapper,
        Array.from(wrapper.querySelectorAll(".gchoice input"))
      );
    } else if (isCheckbox) {
      field = new CheckboxField(
        wrapper,
        Array.from(wrapper.querySelectorAll(".gchoice input"))
      );
    } else if (isConsent) {
      const input = wrapper.querySelector('input[type="checkbox"]');
      if (input) {
        field = new ConsentField(wrapper, input);
      }
    } else if (fileInput) {
      field = new FileUploadField(wrapper, fileInput);
    } else if (select) {
      field = new SelectField(wrapper, select);
    } else if (textarea) {
      field = new InputField(wrapper, textarea);
    } else if (textInput) {
      field = new InputField(wrapper, textInput);
    }

    if (!field) return null;

    // Add Validators based on field type and Gravity Forms classes
    if (wrapper.classList.contains("gfield_contains_required")) {
      field.addValidator(new RequiredValidator());
    }
    if (textInput && textInput.type === "email") {
      field.addValidator(new EmailValidator());
    }
    if (textInput && textInput.type === "tel") {
      field.addValidator(new PhoneValidator());
    }
    if (textInput && textInput.type === "url") {
      field.addValidator(new UrlValidator());
    }

    return field;
  }
}

class ControlsProvider {
  provide() {
    throw new Error("Not implemented");
  }
}

class GravityFormControlsProvider extends ControlsProvider {
  constructor(form) {
    super();
    this.form = form;
  }

  provide(config) {
    const container = this.form.querySelector(".gform-footer") || this.form.querySelector(".gform_footer");
    // GF 3.0 renders <button type="submit">; GF 2.x rendered <input type="submit">.
    // Match both so the form initializes on either version.
    const submitButton = container?.querySelector(
      "input[type='submit'], button[type='submit']"
    );

    if (!container || !submitButton) {
      return { nextButton: null, previousButton: null, submitButton: null };
    }

    submitButton.classList.add("button-submit");

    // Reuse existing custom buttons if already created (prevents duplicates on re-init)
    let nextButton = container.querySelector(".button-next");
    let previousButton = container.querySelector(".button-previous");

    if (!nextButton) {
      nextButton = document.createElement("button");
      nextButton.className = "button button-next";
      nextButton.type = "button";
      nextButton.disabled = true;
      container.insertBefore(nextButton, submitButton);
    }

    if (!previousButton) {
      previousButton = document.createElement("button");
      previousButton.className = "button button-previous";
      previousButton.type = "button";
      container.insertBefore(previousButton, nextButton);
    }

    return { nextButton, previousButton, submitButton };
  }
}

// -----------------------------------------------------------------------------
// CORE FORM CLASS
// -----------------------------------------------------------------------------

class LandeseitenForm {
  #onFieldChangeCallback;
  #onNextButtonClickCallback;
  #onPreviousButtonClickCallback;
  #onSubmitClickCallback;

  constructor(form, fieldsProvider, controlsProvider, options = {}) {
    this.formElement = form;

    // Use options as-is; init.js is the single source of truth for defaults.
    this.config = options;

    this.fields = fieldsProvider.provide(this.config);
    // Each step is an array of fields: one field, or all fields GF placed in
    // the same row (shown side by side and completed together).
    this.steps = this.#groupIntoSteps(this.fields);
    const { nextButton, previousButton, submitButton } =
      controlsProvider.provide();

    this.nextButton = nextButton;
    this.previousButton = previousButton;
    this.submitButton = submitButton;

    this.#onFieldChangeCallback = this.#onFieldChange.bind(this);
    this.#onNextButtonClickCallback = this.#onNextButtonClick.bind(this);
    this.#onPreviousButtonClickCallback =
      this.#onPreviousButtonClick.bind(this);
    this.#onSubmitClickCallback = this.#onSubmitClick.bind(this);

    this.progressBarEl = null;
    this.progressFillEl = null;
  }

  init() {
    if (!this.fields.length || !this.nextButton) {
      if (this.submitButton) this.submitButton.style.display = "block";
      return;
    }

    this.formElement.dataset.mode = this.config.mode;
    this.nextButton.textContent = this.config.buttonText.next;
    this.previousButton.textContent = this.config.buttonText.previous;

    // Apply custom submit button text if provided
    if (this.config.buttonText.submit) {
      // <input> carries its label in .value, <button> in .textContent
      if (this.submitButton.tagName === "BUTTON") {
        this.submitButton.textContent = this.config.buttonText.submit;
      } else {
        this.submitButton.value = this.config.buttonText.submit;
      }
    }

    // Initialize Progress Bar if enabled
    if (this.config.progressBar) {
      this.progressBarEl = document.createElement("div");
      this.progressBarEl.className = "lf-progress-container";
      this.progressFillEl = document.createElement("div");
      this.progressFillEl.className = "lf-progress-bar";
      this.progressBarEl.appendChild(this.progressFillEl);

      // Insert at the very top of the form wrapper
      this.formElement.insertBefore(
        this.progressBarEl,
        this.formElement.firstChild
      );
    }

    // Calculate initial active step
    let initialIndex = this.steps.findIndex((step) =>
      this.#visibleFields(step).some((field) =>
        field.wrapper.classList.contains("gfield_error")
      )
    );
    if (initialIndex === -1) {
      initialIndex = this.steps.findIndex((step) => this.#isStepVisible(step));
    }
    this.currentStepIndex = initialIndex === -1 ? 0 : initialIndex;

    this.fields.forEach((field) => this.#bindField(field));

    this.steps.forEach((step, index) => {
      // After a server-side validation error the form re-renders with the
      // error step active. In reveal mode, keep the steps before it visible
      // as completed so the user still sees what they filled in.
      const isRevealedStep =
        this.config.mode === "reveal" &&
        index < this.currentStepIndex &&
        this.#isStepVisible(step);
      if (isRevealedStep) this.#setStepCompleted(step, true);
      this.#showStep(step, index === this.currentStepIndex || isRevealedStep);
    });

    this.formElement.classList.toggle(
      "lf-has-rows",
      this.steps.some((step) => step.length > 1)
    );
    this.#updateRowLayout();

    // Hook into Gravity Forms conditional logic
    // This ensures buttons update if GF suddenly shows/hides the last field
    if (typeof jQuery !== "undefined") {
      jQuery(document).on("gform_post_conditional_logic", (event, formId, fields, isInit) => {
        // Only trigger if this instance belongs to the triggered form
        const instanceFormId = this.formElement.getAttribute("data-form-id") || this.formElement.id.replace(/\D/g, "");
        if (formId == instanceFormId) {
          this.#updateRowLayout();
          // Re-validate the current step so nextButton.disabled is fresh
          // before computing button/submit visibility from the new field layout.
          this.#onFieldChange();
          this.#updateProgressBar();
        }
      });
    }

    // Set initial focus. A field with a server-side error is always focused
    // and scrolled into view so the user lands on what needs fixing.
    const initialStep = this.steps[this.currentStepIndex];
    if (initialStep && this.#isStepVisible(initialStep)) {
      const serverErrorField = this.#visibleFields(initialStep).find(
        (field) => field.hasServerError
      );
      if (serverErrorField) {
        this.#revealServerErrorField(serverErrorField);
      } else if (this.config.autoFocus) {
        this.#focusStep(initialStep);
      }
    }

    this.nextButton.addEventListener("click", this.#onNextButtonClickCallback);
    this.previousButton.addEventListener(
      "click",
      this.#onPreviousButtonClickCallback
    );

    // Capture phase on the wrapper runs before GF's own handlers on the button
    // (including its inline onclick), so an invalid step never reaches GF.
    this.formElement.addEventListener("click", this.#onSubmitClickCallback, true);

    this.submitButton.style.display = "none";
    this.#onFieldChange();
    this.#updateButtonVisibility();
    this.#updateProgressBar();
  }

  /**
   * Scrolls to and focuses the first step GF rejected on the server.
   * After gform_post_render GF focuses its error summary
   * (#gform_{id}_validation_container), and its AJAX handler scrolls back to
   * the form top 50ms later (scrollTop to #gform_wrapper_{id}). Both would
   * override us, so reveal 150ms after the summary focus, with a fallback
   * for GF versions/settings that don't focus the summary.
   */
  #revealServerErrorField(field) {
    let revealed = false;
    const reveal = () => {
      revealed = true;
      field.scrollTo();
      field.focus();
    };

    const summary = this.formElement.querySelector(
      ".gform_validation_errors, [id$='_validation_container']"
    );
    if (summary) {
      const onSummaryFocus = () => setTimeout(reveal, 150);
      summary.addEventListener("focus", onSummaryFocus, { once: true });
      setTimeout(
        () => summary.removeEventListener("focus", onSummaryFocus),
        2000
      );
    }

    setTimeout(() => {
      if (!revealed) reveal();
    }, 400);
  }

  #isFieldVisible(field) {
    if (!field || !field.wrapper) return false;
    // Inline style check (GF conditional logic sets display:none directly)
    if (field.wrapper.style.display === "none") return false;
    // Class check (GF always-hidden fields use this class)
    if (field.wrapper.classList.contains("gfield_visibility_hidden")) return false;
    // Computed style fallback (catches fields hidden by CSS rules, e.g. honeypot).
    // Safe in reveal mode: inactive fields use position:absolute/visibility:hidden,
    // NOT display:none, so getComputedStyle().display is always "block" for them.
    if (window.getComputedStyle(field.wrapper).display === "none") return false;
    return true;
  }

  #visibleFields(step) {
    return step.filter((field) => this.#isFieldVisible(field));
  }

  #isStepVisible(step) {
    return step.some((field) => this.#isFieldVisible(field));
  }

  /**
   * Groups consecutive fields with the same GF row (data-lf-row, set in PHP)
   * into one step. Fields without a row are a step of their own.
   */
  #groupIntoSteps(fields) {
    const steps = [];
    fields.forEach((field) => {
      const row = field.wrapper.dataset.lfRow;
      const lastStep = steps[steps.length - 1];
      if (row && lastStep && lastStep[0].wrapper.dataset.lfRow === row) {
        lastStep.push(field);
      } else {
        steps.push([field]);
      }
    });
    return steps;
  }

  /**
   * Sizes the fields of each row step on the 12-column grid. GF's spans are
   * scaled to fill the row, so a row whose other fields are hidden by
   * conditional logic still spans the full width.
   */
  #updateRowLayout() {
    this.steps.forEach((step) => {
      const visibleFields = this.#visibleFields(step);
      const isRow = visibleFields.length > 1;
      const spans = visibleFields.map(
        (field) => parseInt(field.wrapper.dataset.lfSpan, 10) || 6
      );
      const totalSpan = spans.reduce((sum, span) => sum + span, 0);
      let usedSpan = 0;

      step.forEach((field) => {
        const position = visibleFields.indexOf(field);
        const inRow = isRow && position !== -1;
        field.wrapper.classList.toggle("lf-in-row", inRow);
        field.wrapper.classList.toggle("lf-row-start", inRow && position === 0);
        if (!inRow) {
          field.wrapper.style.removeProperty("--lf-row-span");
          return;
        }
        const isLast = position === visibleFields.length - 1;
        const span = isLast
          ? Math.max(1, 12 - usedSpan)
          : Math.max(1, Math.round((spans[position] * 12) / totalSpan));
        usedSpan += span;
        field.wrapper.style.setProperty("--lf-row-span", span);
      });
    });
  }

  #showStep(step, flag) {
    step.forEach((field) => field.show(flag));
  }

  #setStepCompleted(step, flag) {
    step.forEach((field) => field.wrapper.classList.toggle("step-completed", flag));
  }

  /**
   * Focuses the first field showing an error, otherwise the first field.
   */
  #focusStep(step) {
    const visibleFields = this.#visibleFields(step);
    const target =
      visibleFields.find((field) =>
        field.wrapper.classList.contains("gfield_error")
      ) || visibleFields[0];
    if (target) target.focus();
  }

  #findNextVisibleIndex(startIndex) {
    for (let i = startIndex + 1; i < this.steps.length; i++) {
      if (this.#isStepVisible(this.steps[i])) return i;
    }
    return -1;
  }

  #findPrevVisibleIndex(startIndex) {
    for (let i = startIndex - 1; i >= 0; i--) {
      if (this.#isStepVisible(this.steps[i])) return i;
    }
    return -1;
  }

  #bindField(field) {
    const onCompleted = () => this.#onFieldCompleted(field);

    if (field instanceof FileUploadField) {
      field.onChange(this.#onFieldChangeCallback, (e) => {
        this.#onFieldChangeCallback(e);
        setTimeout(onCompleted, 100);
      });
    } else if (field instanceof SelectField) {
      field.onChange((e) => {
        this.#onFieldChangeCallback(e);
        setTimeout(onCompleted, 200);
      });
    } else if (field instanceof RadioField && this.config.autoProgressRadio) {
      field.onChange((e) => {
        this.#onFieldChangeCallback(e);
        setTimeout(onCompleted, 150);
      });
    } else {
      field.onChange(
        this.#onFieldChangeCallback,
        this.config.enterToAdvance ? onCompleted : () => {}
      );
    }
  }

  /**
   * Runs when the user is done with a field: a choice picked, a file chosen or
   * Enter pressed. Advances once the whole step is valid. In a row with fields
   * still missing, focus moves to the next one instead.
   */
  #onFieldCompleted(field) {
    const currentStep = this.steps[this.currentStepIndex];
    // Edits to earlier steps (reveal mode keeps them editable) don't advance.
    if (this.isAnimating || !currentStep || !currentStep.includes(field)) return;

    if (!this.nextButton.disabled) {
      this.#onNextButtonClick();
      return;
    }

    if (field.getError(this.config) !== null) return;
    const pendingField = this.#visibleFields(currentStep).find(
      (other) => other.getError(this.config) !== null
    );
    if (pendingField) pendingField.focus();
  }

  #onFieldChange() {
    const currentStep = this.steps[this.currentStepIndex] || [];
    const currentFields = this.#visibleFields(currentStep);
    if (currentFields.length) {
      // Validate every field so each one shows its own error.
      const results = currentFields.map((field) => field.validate(this.config));
      this.nextButton.disabled = results.includes(false);
    } else {
      this.nextButton.disabled = false;
    }

    // Clear errors on earlier steps the submit guard flagged once they're fixed
    // (reveal mode keeps them editable while another step is current).
    this.fields.forEach((field) => {
      if (
        !currentStep.includes(field) &&
        field.isDirty &&
        field.wrapper.classList.contains("gfield_error")
      ) {
        field.validate(this.config);
      }
    });

    this.#updateButtonVisibility();
  }

  /**
   * Blocks submission while any reached step is invalid, the way "Weiter"
   * already does for each step. Without this, an unticked consent box goes to
   * the server, and GF's re-rendered error form is what the user lands on.
   */
  #onSubmitClick(event) {
    if (!this.submitButton.contains(event.target)) return;

    const invalidFieldsOf = (step) =>
      this.#visibleFields(step).filter(
        (field) => field.getError(this.config) !== null
      );
    const invalidIndex = this.steps.findIndex(
      (step, index) =>
        index <= this.currentStepIndex && invalidFieldsOf(step).length > 0
    );
    if (invalidIndex === -1) return;

    event.preventDefault();
    event.stopImmediatePropagation();

    const invalidFields = invalidFieldsOf(this.steps[invalidIndex]);
    invalidFields.forEach((field) => {
      field.isDirty = true;
    });

    if (invalidIndex !== this.currentStepIndex && this.config.mode === "paged") {
      // Earlier steps are hidden in paged mode; go back to the invalid one.
      // #transitionToStep re-validates it, which now shows the errors.
      this.#transitionToStep(invalidIndex);
      return;
    }

    invalidFields.forEach((field) => field.validate(this.config));
    this.#onFieldChange();
    invalidFields[0].scrollTo();
    invalidFields[0].focus();
  }

  #transitionToStep(newIndex) {
    if (this.isAnimating) return;
    this.isAnimating = true;

    const oldIndex = this.currentStepIndex;
    const isForward = newIndex > oldIndex;
    this.formElement.classList.toggle("is-reversing", !isForward);

    const oldStep = this.steps[oldIndex];
    const newStep = this.steps[newIndex];

    const scrollToShowStep = () => {
      requestAnimationFrame(() => {
        // The step's first field is its top edge (rows stack on phones).
        const newWrapper = (this.#visibleFields(newStep)[0] || newStep[0]).wrapper;
        const rect = newWrapper.getBoundingClientRect();
        const absoluteTop = rect.top + window.scrollY;
        const targetScroll = absoluteTop - this.config.scrollTopMargin;
        // In reveal mode going forward, never scroll UP — the new field must
        // always be below the current position. If targetScroll < scrollY it
        // means a stale/duplicate instance is pointing at the wrong field.
        if (this.config.mode === "reveal" && isForward && targetScroll <= window.scrollY) {
          return;
        }
        newWrapper.style.scrollMarginTop = this.config.scrollTopMargin + "px";
        newWrapper.scrollIntoView({ behavior: "smooth", block: "start" });
      });
    };

    const completeTransition = () => {
      oldStep.forEach((field) => field.wrapper.classList.remove("animating-out"));
      if (this.config.mode === "paged" || !isForward) {
        this.#showStep(oldStep, false);
      }
      this.#showStep(newStep, true);
      scrollToShowStep();

      if (this.config.autoFocus) {
        setTimeout(() => {
          this.#focusStep(newStep);
        }, 300);
      }

      this.currentStepIndex = newIndex;
      this.#onFieldChange();
      this.#updateProgressBar();
      this.isAnimating = false;
    };

    if (this.config.mode === "paged") {
      oldStep.forEach((field) => field.wrapper.classList.add("animating-out"));
      setTimeout(completeTransition, 500);
    } else {
      completeTransition();
    }
  }

  #onNextButtonClick() {
    const nextIndex = this.#findNextVisibleIndex(this.currentStepIndex);
    if (nextIndex === -1) {
      this.#updateButtonVisibility();
      return;
    }
    this.#setStepCompleted(this.steps[this.currentStepIndex], true);
    this.#transitionToStep(nextIndex);
  }

  #onPreviousButtonClick() {
    const prevIndex = this.#findPrevVisibleIndex(this.currentStepIndex);
    if (prevIndex === -1) return;
    this.#setStepCompleted(this.steps[prevIndex], false);
    this.#transitionToStep(prevIndex);
  }

  #updateButtonVisibility() {
    const nextVisibleIndex = this.#findNextVisibleIndex(this.currentStepIndex);
    const prevVisibleIndex = this.#findPrevVisibleIndex(this.currentStepIndex);

    // Show submit on the last visible step regardless of validity —
    // #onSubmitClick validates on click. Showing Weiter on the last
    // step confuses users who have already filled it in.
    if (nextVisibleIndex === -1) {
      this.submitButton.style.display = "inline-block";
      this.nextButton.style.display = "none";
    } else {
      this.submitButton.style.display = "none";
      this.nextButton.style.display = "inline-block";
    }
    this.previousButton.style.display =
      prevVisibleIndex !== -1 ? "inline-block" : "none";
  }

  #updateProgressBar() {
    if (!this.progressBarEl || !this.progressFillEl) return;

    // Filter visible steps to calculate accurate percentage
    const visibleSteps = this.steps.filter((step) => this.#isStepVisible(step));
    const currentVisibleIndex = visibleSteps.indexOf(
      this.steps[this.currentStepIndex]
    );
    const total = visibleSteps.length;

    if (total <= 1) {
      this.progressFillEl.style.width = "0%";
      return;
    }

    const percent = Math.min(
      100,
      Math.max(0, ((currentVisibleIndex + 1) / total) * 100)
    );
    this.progressFillEl.style.width = percent + "%";
  }
}
