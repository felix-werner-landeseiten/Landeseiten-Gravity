/*
 * == CS Landeseiten Form - Scripts ==
 *
 * Description:   Main JavaScript for the CS Landeseiten Form Gravity Forms wrapper.
 * Handles animations, validation, state management, and the progress bar.
 * Author:        Landeseiten.de
 * Version:       2.0.0
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

    return {
      valid: hasValue,
      message: hasValue ? null : messages.required,
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
  }

  addValidator(validator) {
    this.validators.push(validator);
    return this;
  }

  validate(config) {
    let errorMessage = null;
    for (const validator of this.validators) {
      const result = validator.isValid(this, config.errorMessages);
      if (!result.valid) {
        errorMessage = result.message;
        break;
      }
    }

    // Only show error if the user has interacted with the field (isDirty)
    // or if the config forces errors to show immediately.
    if (config.hideErrorUntilDirty && !this.isDirty) {
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
        if (typeof jQuery !== "undefined") {
          try {
            jQuery(this.input).datepicker("destroy");
            jQuery(this.input).removeClass("hasDatepicker");
          } catch (e) {}
        }

        flatpickr(this.input, {
          mode: mode,
          minDate: config.minDate || null,
          maxDate: config.maxDate || null,
          dateFormat: "m/d/Y",
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
    // Skip hidden or utility fields
    if (
      wrapper.style.display === "none" ||
      wrapper.classList.contains("gform_validation_container") ||
      wrapper.classList.contains("gfield_visibility_hidden") ||
      wrapper.classList.contains("lf-skip")
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
    const container = this.form.querySelector(".gform_footer");
    const submitButton = container?.querySelector("input[type='submit']");

    if (!container || !submitButton) {
      return { nextButton: null, previousButton: null, submitButton: null };
    }

    const nextButton = document.createElement("button");
    nextButton.className = "gform_button button button-next";
    nextButton.type = "button";
    nextButton.disabled = true;

    const previousButton = document.createElement("button");
    previousButton.className = "gform_button button button-previous";
    previousButton.type = "button";

    container.insertBefore(previousButton, submitButton);
    container.insertBefore(nextButton, submitButton);

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
  #onEnterPressedCallback;

  constructor(form, fieldsProvider, controlsProvider, options = {}) {
    const defaultConfig = {
      mode: "reveal",
      autoFocus: true,
      progressBar: false,
      enterToAdvance: true,
      autoProgressRadio: true,
      hideErrorUntilDirty: true,
      scrollTopMargin: 40,
      buttonText: {
        next: "Weiter →",
        previous: "← Zurück",
        submit: "Absenden",
      },
      errorMessages: {
        required: "Required",
        email: "Invalid Email",
        phone: "Invalid Phone",
        url: "Invalid URL",
      },
    };

    this.formElement = form;

    // Merge configuration
    this.config = {
      ...defaultConfig,
      ...options,
      buttonText: {
        ...defaultConfig.buttonText,
        ...(options.buttonText || {}),
      },
      errorMessages: {
        ...defaultConfig.errorMessages,
        ...(options.errorMessages || {}),
      },
    };

    this.fields = fieldsProvider.provide(this.config);
    const { nextButton, previousButton, submitButton } =
      controlsProvider.provide();

    this.nextButton = nextButton;
    this.previousButton = previousButton;
    this.submitButton = submitButton;

    this.#onFieldChangeCallback = this.#onFieldChange.bind(this);
    this.#onNextButtonClickCallback = this.#onNextButtonClick.bind(this);
    this.#onPreviousButtonClickCallback =
      this.#onPreviousButtonClick.bind(this);
    this.#onEnterPressedCallback = this.#onEnterPressed.bind(this);

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
      this.submitButton.value = this.config.buttonText.submit;
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

    // Calculate initial active field index
    let initialIndex = this.fields.findIndex(
      (field) =>
        field.wrapper.classList.contains("gfield_error") &&
        this.#isFieldVisible(field)
    );
    if (initialIndex === -1) {
      initialIndex = this.fields.findIndex((field) =>
        this.#isFieldVisible(field)
      );
    }
    this.currentFieldIndex = initialIndex === -1 ? 0 : initialIndex;

    // Bind events to fields
    this.fields.forEach((field, index) => {
      const enterHandler = this.config.enterToAdvance
        ? this.#onEnterPressedCallback
        : () => {};

      if (field instanceof FileUploadField) {
        field.onChange(this.#onFieldChangeCallback, (e) => {
          this.#onFieldChangeCallback(e);
          setTimeout(() => this.#onNextButtonClick(), 100);
        });
      } else if (field instanceof SelectField) {
        field.onChange((e) => {
          this.#onFieldChangeCallback(e);
          setTimeout(() => this.#onNextButtonClick(), 200);
        });
      } else if (field instanceof RadioField && this.config.autoProgressRadio) {
        field.onChange((e) => {
          this.#onFieldChangeCallback(e);
          this.#updateButtonVisibility();
          this.#onRadioSelect();
        });
      } else {
        field.onChange(this.#onFieldChangeCallback, enterHandler);
      }
      field.show(index === this.currentFieldIndex);
    });

    // Set initial focus
    if (
      this.config.autoFocus &&
      this.fields[this.currentFieldIndex] &&
      this.#isFieldVisible(this.fields[this.currentFieldIndex])
    ) {
      this.fields[this.currentFieldIndex].focus();
    }

    this.nextButton.addEventListener("click", this.#onNextButtonClickCallback);
    this.previousButton.addEventListener(
      "click",
      this.#onPreviousButtonClickCallback
    );

    this.submitButton.style.display = "none";
    this.#onFieldChange();
    this.#updateButtonVisibility();
    this.#updateProgressBar();
  }

  #isFieldVisible(field) {
    if (!field || !field.wrapper) return false;
    return window.getComputedStyle(field.wrapper).display !== "none";
  }

  #findNextVisibleIndex(startIndex) {
    for (let i = startIndex + 1; i < this.fields.length; i++) {
      if (this.#isFieldVisible(this.fields[i])) return i;
    }
    return -1;
  }

  #findPrevVisibleIndex(startIndex) {
    for (let i = startIndex - 1; i >= 0; i--) {
      if (this.#isFieldVisible(this.fields[i])) return i;
    }
    return -1;
  }

  #onRadioSelect() {
    setTimeout(() => {
      if (!this.isAnimating && !this.nextButton.disabled) {
        this.#onNextButtonClick();
      }
    }, 150);
  }

  #onEnterPressed() {
    if (!this.nextButton.disabled && !this.isAnimating) {
      this.#onNextButtonClick();
    }
  }

  #onFieldChange() {
    const currentField = this.fields[this.currentFieldIndex];
    if (currentField && this.#isFieldVisible(currentField)) {
      const isCurrentFieldValid = currentField.validate(this.config);
      this.nextButton.disabled = !isCurrentFieldValid;
    } else {
      this.nextButton.disabled = false;
    }
    this.#updateButtonVisibility();
  }

  #transitionToField(newIndex) {
    if (this.isAnimating) return;
    this.isAnimating = true;

    const oldIndex = this.currentFieldIndex;
    const isForward = newIndex > oldIndex;
    this.formElement.classList.toggle("is-reversing", !isForward);

    const oldField = this.fields[oldIndex];
    const newField = this.fields[newIndex];

    const scrollToShowField = () => {
      if (this.config.mode === "reveal" && isForward) {
        const oldFieldRect = oldField.wrapper.getBoundingClientRect();
        window.scrollBy({
          top: oldFieldRect.bottom - this.config.scrollTopMargin,
          behavior: "smooth",
        });
      } else {
        const fieldTop =
          newField.wrapper.getBoundingClientRect().top + window.scrollY;
        window.scrollTo({
          top: fieldTop - this.config.scrollTopMargin,
          behavior: "smooth",
        });
      }
    };

    const completeTransition = () => {
      oldField.wrapper.classList.remove("animating-out");
      if (this.config.mode === "paged" || !isForward) {
        oldField.show(false);
      }
      newField.show(true);
      scrollToShowField();

      if (this.config.autoFocus) {
        setTimeout(() => {
          newField.focus();
        }, 300);
      }

      this.currentFieldIndex = newIndex;
      this.#onFieldChange();
      this.#updateProgressBar();
      this.isAnimating = false;
    };

    if (this.config.mode === "paged") {
      oldField.wrapper.classList.add("animating-out");
      setTimeout(completeTransition, 500);
    } else {
      completeTransition();
    }
  }

  #onNextButtonClick() {
    const nextIndex = this.#findNextVisibleIndex(this.currentFieldIndex);
    if (nextIndex === -1) {
      this.#updateButtonVisibility();
      return;
    }
    this.fields[this.currentFieldIndex].wrapper.classList.add("step-completed");
    this.#transitionToField(nextIndex);
  }

  #onPreviousButtonClick() {
    const prevIndex = this.#findPrevVisibleIndex(this.currentFieldIndex);
    if (prevIndex === -1) return;
    this.fields[prevIndex].wrapper.classList.remove("step-completed");
    this.#transitionToField(prevIndex);
  }

  #updateButtonVisibility() {
    const currentFieldIsValid =
      this.fields.length > 0 &&
      this.fields[this.currentFieldIndex] &&
      !this.nextButton.disabled;
    const nextVisibleIndex = this.#findNextVisibleIndex(this.currentFieldIndex);
    const prevVisibleIndex = this.#findPrevVisibleIndex(this.currentFieldIndex);

    if (nextVisibleIndex === -1 && currentFieldIsValid) {
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

    // Filter visible fields to calculate accurate percentage
    const visibleFields = this.fields.filter((f) => this.#isFieldVisible(f));
    const currentVisibleIndex = visibleFields.indexOf(
      this.fields[this.currentFieldIndex]
    );
    const total = visibleFields.length;

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
