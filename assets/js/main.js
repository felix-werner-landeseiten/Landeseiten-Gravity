/*
 * == CS Landeseiten Form - Scripts ==
 *
 * Description:   Main JavaScript for the CS Landeseiten Form Gravity Forms wrapper.
 * Handles animations, validation, and state management.
 * Author:          Landeseiten.de
 */

// -----------------------------------------------------------------------------
// VALIDATION CLASSES
// -----------------------------------------------------------------------------

/**
 * Base class for all field validators.
 */
class Validator {
  /**
   * Checks if a field's value is valid. Must be implemented by a subclass.
   * @param {Field} field The field instance to validate.
   * @param {object} messages A key-value pair of error messages.
   * @returns {{valid: boolean, message: string|null}} An object indicating validity and an error message.
   */
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
      return { valid: true, message: null }; // Not required, so an empty value is valid.
    }
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
 * Validates that a field's value contains only numeric digits.
 */
class PhoneValidator extends Validator {
  isValid(field, messages) {
    const value = field.getValue();
    if (typeof value !== "string" || value.trim() === "") {
      return { valid: true, message: null };
    }
    const regex = /^\d+$/;
    const isValid = regex.test(value);
    return {
      valid: isValid,
      message: isValid ? null : messages.phone,
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
      return { valid: true, message: null }; // An empty value is valid if not required
    }
    // A simple regex to check for a valid URL format
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
    this.isDirty = false; // New property
  }

  /**
   * Adds a validator instance to the field.
   * @param {Validator} validator The validator to add.
   */
  addValidator(validator) {
    this.validators.push(validator);
    return this;
  }

  /**
   * Validates the field against all its validators and displays an error if invalid.
   * @param {object} config The LandeseitenForm configuration object.
   * @returns {boolean} True if the field is valid, otherwise false.
   */
  validate(config) {
    let errorMessage = null;
    for (const validator of this.validators) {
      const result = validator.isValid(this, config.errorMessages);
      if (!result.valid) {
        errorMessage = result.message;
        break; // Stop on the first error.
      }
    }

    // Only display the error if the feature is off or the field has been interacted with.
    if (config.hideErrorUntilDirty && !this.isDirty) {
      this.displayError(null);
    } else {
      this.displayError(errorMessage);
    }

    // The field's validity is returned regardless of whether the error is shown.
    return errorMessage === null;
  }

  /**
   * Displays or hides a validation error message for the field.
   * @param {string|null} message The error message to display, or null to hide.
   */
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

  /**
   * Attaches a callback function to the field's change event.
   * @param {Function} callback The function to call on change.
   */
  onChange(callback) {
    throw new Error("Field.onChange() must be implemented by a subclass.");
  }

  /**
   * Gets the current value of the field.
   * @returns {*} The field's value.
   */
  getValue() {
    throw new Error("Field.getValue() must be implemented by a subclass.");
  }

  /**
   * Shows or hides the field.
   * @param {boolean} flag True to show, false to hide.
   */
  show(flag) {
    this.wrapper.classList.toggle("active", flag);
  }

  /**
   * Sets focus on the field's input element.
   */
  focus() {
    // Implemented by subclasses.
  }

  /**
   * Scrolls the field into the center of the viewport.
   */
  scrollTo() {
    this.wrapper.scrollIntoView({ behavior: "smooth", block: "center" });
  }
}
/**
 * Represents a standard text, email, textarea, etc. input field.
 */
class InputField extends Field {
  constructor(container, input) {
    super(container);
    this.input = input;

    // Add real-time input filtering for telephone fields
    if (this.input.type === "tel") {
      this.input.addEventListener("input", function (event) {
        // This instantly removes any character that is NOT a digit.
        const numericValue = this.value.replace(/\D/g, "");
        if (this.value !== numericValue) {
          this.value = numericValue;
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

    // Standard listener for user typing
    this.input.addEventListener("input", handleValidation);

    // Standard listener for Enter key
    this.input.addEventListener("keydown", (event) => {
      if (event.key === "Enter") {
        event.preventDefault();
        onEnterPressed();
      }
    });

    // For date pickers, we listen for clicks on the calendar UI itself.
    if (this.input.classList.contains("datepicker")) {
      const handleDatePickerClick = (event) => {
        // Check if the click happened inside the pop-up calendar
        if (event.target.closest("#ui-datepicker-div")) {
          // Use a tiny delay to ensure the value is set before we validate
          setTimeout(() => {
            handleValidation();
          }, 100);
          // Clean up the listener so it doesn't fire again unnecessarily
          document.body.removeEventListener(
            "click",
            handleDatePickerClick,
            true
          );
        }
      };

      // When the user clicks INTO the date field...
      this.input.addEventListener("focus", () => {
        // ...start listening for a click anywhere on the page.
        document.body.addEventListener("click", handleDatePickerClick, true);
      });
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
 * Represents a file upload field.
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
      // Automatically advance when a file is selected
      if (this.getValue().length > 0) {
        onFileSelected();
      }
    };
    this.input.addEventListener("change", handleChange);
  }

  getValue() {
    // The value of a file input is a FileList object
    return this.input.files;
  }

  focus() {
    // This method is intentionally left empty.
    // Automatically triggering a file input's click event when the field
    // becomes active can be a disruptive user experience. This change
    // requires the user to manually click the button to select a file.
  }
}

/**
 * Base class for choice-based fields like radio buttons and checkboxes.
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

/**
 * Represents a checkbox field, allowing multiple selections.
 */
class CheckboxField extends ChoiceField {
  getValue() {
    return this.choices
      .filter((choice) => choice.checked)
      .map((choice) => choice.value);
  }
}

/**
 * Represents a radio button field, allowing a single selection.
 */
class RadioField extends ChoiceField {
  getValue() {
    const selected = this.choices.find((choice) => choice.checked);
    return selected ? selected.value : null;
  }
}

// -----------------------------------------------------------------------------
// PROVIDER CLASSES
// -----------------------------------------------------------------------------

/**
 * Base class for providing fields from a form source.
 */
class FieldsProvider {
  provide(config) {
    return [];
  }
}

/**
 * Scans a Gravity Forms element and provides an array of Field objects.
 */
class GravityFieldsProvider extends FieldsProvider {
  constructor(form) {
    super();
    this.form = form;
  }

  provide(config) {
    return Array.from(this.form.querySelectorAll(".gfield"))
      .map((wrapper) => this.#resolveSingle(wrapper, config))
      .filter(Boolean); // Filter out any null results
  }

  /**
   * Resolves a single Gravity Forms field wrapper into a specific Field instance.
   * @private
   */
  #resolveSingle(wrapper, config) {
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
    const textarea = wrapper.querySelector("textarea");
    const fileInput = wrapper.querySelector('input[type="file"]');
    const textInput = wrapper.querySelector(
      'input[type="text"], input[type="email"], input[type="number"], input[type="tel"], input[type="url"]'
    );

    let field = null;
    if (isRadio) {
      const choices = Array.from(wrapper.querySelectorAll(".gchoice input"));
      field = new RadioField(wrapper, choices);
    } else if (isCheckbox) {
      const choices = Array.from(wrapper.querySelectorAll(".gchoice input"));
      field = new CheckboxField(wrapper, choices);
    } else if (textarea) {
      field = new InputField(wrapper, textarea);
    } else if (fileInput) {
      field = new FileUploadField(wrapper, fileInput);
    } else if (textInput) {
      field = new InputField(wrapper, textInput);
    }

    if (!field) return null;

    // Add validators based on Gravity Forms classes
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

/**
 * Base class for providing form controls.
 */
class ControlsProvider {
  provide() {
    throw new Error("ControlsProvider.provide() must be implemented.");
  }
}

/**
 * Injects and provides Next/Previous buttons for a Gravity Form.
 */
class GravityFormControlsProvider extends ControlsProvider {
  constructor(form) {
    super();
    this.form = form;
  }

  provide() {
    const container = this.form.querySelector(".gform_footer");
    const submitButton = container?.querySelector("input[type='submit']");
    if (!container || !submitButton) {
      console.error(
        "LandeseitenForm Error: Form footer or submit button not found."
      );
      return { nextButton: null, previousButton: null, submitButton: null };
    }
    const nextButton = document.createElement("button");
    nextButton.type = "button";
    nextButton.className = "gform_button button button-next";
    nextButton.disabled = true;

    const previousButton = document.createElement("button");
    previousButton.type = "button";
    previousButton.className = "gform_button button button-previous";

    container.insertBefore(previousButton, submitButton);
    container.insertBefore(nextButton, submitButton);
    return { nextButton, previousButton, submitButton };
  }
}

// -----------------------------------------------------------------------------
// CORE FORM CLASS
// -----------------------------------------------------------------------------
/**
 * Orchestrates the multi-step form logic, handling state, transitions, and validation.
 */
class LandeseitenForm {
  #onFieldChangeCallback;
  #onNextButtonClickCallback;
  #onPreviousButtonClickCallback;
  #onEnterPressedCallback;

  constructor(form, fieldsProvider, controlsProvider, options = {}) {
    const defaultConfig = {
      mode: "reveal",
      autoFocus: true,
      enterToAdvance: true,
      autoProgressRadio: true,
      hideErrorUntilDirty: true,
      scrollTopMargin: 40,
      buttonText: { next: "Weiter →", previous: "← Zurück" },
      errorMessages: {
        required: "Dieses Feld ist erforderlich.",
        email: "Bitte geben Sie eine gültige E-Mail-Adresse ein.",
        phone: "Bitte geben Sie eine gültige Telefonnummer (nur Ziffern) ein.",
        url: "Bitte geben Sie eine gültige Web-Adresse ein.",
      },
    };
    this.formElement = form;

    // Perform a deep merge for nested objects like buttonText and errorMessages
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

    this.currentFieldIndex = 0;
    this.isAnimating = false;
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
  }
  /**
   * Initializes the form, sets up fields, and attaches event listeners.
   */
  init() {
    if (!this.fields.length || !this.nextButton) {
      console.error(
        "LandeseitenForm could not initialize. Missing fields or controls."
      );
      if (this.submitButton) this.submitButton.style.display = "block";
      return;
    }

    this.formElement.dataset.mode = this.config.mode;
    this.nextButton.textContent = this.config.buttonText.next;
    this.previousButton.textContent = this.config.buttonText.previous;

    // Find the first field with an error, but only if it's visible.
    let initialIndex = this.fields.findIndex(
      (field) =>
        field.wrapper.classList.contains("gfield_error") &&
        this.#isFieldVisible(field)
    );

    // If no visible errored field is found, find the first visible field.
    if (initialIndex === -1) {
      initialIndex = this.fields.findIndex((field) =>
        this.#isFieldVisible(field)
      );
    }

    // If no fields are visible, default to 0.
    this.currentFieldIndex = initialIndex === -1 ? 0 : initialIndex;

    this.fields.forEach((field, index) => {
      const enterHandler = this.config.enterToAdvance
        ? this.#onEnterPressedCallback
        : () => {};

      // Special handling for FileUploadField
      if (field instanceof FileUploadField) {
        const combinedHandler = (event) => {
          this.#onFieldChangeCallback(event);
          // A short delay to allow the validation to run before auto-advancing
          setTimeout(() => this.#onNextButtonClick(), 100);
        };
        field.onChange(this.#onFieldChangeCallback, combinedHandler);
      } else if (field instanceof RadioField && this.config.autoProgressRadio) {
        const combinedHandler = (event) => {
          this.#onFieldChangeCallback(event);
          this.#updateButtonVisibility(); // Crucial for conditional logic
          this.#onRadioSelect();
        };
        field.onChange(combinedHandler);
      } else {
        field.onChange(this.#onFieldChangeCallback, enterHandler);
      }

      // Only show the active field.
      field.show(index === this.currentFieldIndex);
    });

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
  }

  /**
   * Checks if a field is currently visible in the DOM.
   * @param {Field} field The field to check.
   * @returns {boolean} True if the field is visible.
   * @private
   */
  #isFieldVisible(field) {
    if (!field || !field.wrapper) return false;
    // getComputedStyle is the most reliable way to check visibility.
    const style = window.getComputedStyle(field.wrapper);
    return style.display !== "none";
  }

  /**
   * Finds the index of the next visible field.
   * @param {number} startIndex Index to start searching from.
   * @returns {number} The index of the next visible field, or -1.
   * @private
   */
  #findNextVisibleIndex(startIndex) {
    for (let i = startIndex + 1; i < this.fields.length; i++) {
      if (this.#isFieldVisible(this.fields[i])) {
        return i;
      }
    }
    return -1;
  }

  /**
   * Finds the index of the previous visible field.
   * @param {number} startIndex Index to start searching from.
   * @returns {number} The index of the previous visible field, or -1.
   * @private
   */
  #findPrevVisibleIndex(startIndex) {
    for (let i = startIndex - 1; i >= 0; i--) {
      if (this.#isFieldVisible(this.fields[i])) {
        return i;
      }
    }
    return -1;
  }

  /**
   * Handles the selection on a radio button to auto-progress.
   * @private
   */
  #onRadioSelect() {
    setTimeout(() => {
      if (!this.isAnimating && !this.nextButton.disabled) {
        this.#onNextButtonClick();
      }
    }, 150);
  }

  /**
   * Handles the 'Enter' key press to advance the form.
   * @private
   */
  #onEnterPressed() {
    if (!this.nextButton.disabled && !this.isAnimating) {
      this.#onNextButtonClick();
    }
  }

  /**
   * Handles the change event for the current field, validating it and updating button states.
   * @private
   */
  #onFieldChange() {
    const currentField = this.fields[this.currentFieldIndex];
    if (currentField && this.#isFieldVisible(currentField)) {
      // Pass the entire config object to the validate method
      const isCurrentFieldValid = currentField.validate(this.config);
      this.nextButton.disabled = !isCurrentFieldValid;
    } else {
      this.nextButton.disabled = false;
    }
    this.#updateButtonVisibility();
  }

  /**
   * Transitions the form to a new field index with animations and corrected scrolling.
   * @param {number} newIndex The index of the field to transition to.
   * @private
   */
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
        const scrollAmount = oldFieldRect.bottom - this.config.scrollTopMargin;
        window.scrollBy({ top: scrollAmount, behavior: "smooth" });
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
      this.isAnimating = false;
    };

    if (this.config.mode === "paged") {
      oldField.wrapper.classList.add("animating-out");
      setTimeout(completeTransition, 500);
    } else {
      completeTransition();
    }
  }

  /**
   * Handles the click event for the 'Next' button. Finds the next visible
   * field and transitions to it.
   * @private
   */
  #onNextButtonClick() {
    const nextIndex = this.#findNextVisibleIndex(this.currentFieldIndex);
    if (nextIndex === -1) {
      this.#updateButtonVisibility(); // We're at the end.
      return;
    }
    this.fields[this.currentFieldIndex].wrapper.classList.add("step-completed");
    this.#transitionToField(nextIndex);
  }

  /**
   * Handles the click event for the 'Previous' button. Finds the
   * previous visible field and transitions to it.
   * @private
   */
  #onPreviousButtonClick() {
    const prevIndex = this.#findPrevVisibleIndex(this.currentFieldIndex);
    if (prevIndex === -1) return;
    this.fields[prevIndex].wrapper.classList.remove("step-completed");
    this.#transitionToField(prevIndex);
  }

  /**
   * Updates the visibility of the Next, Previous, and Submit buttons
   * based on the current field's position and validity in the sequence of visible fields.
   * @private
   */
  #updateButtonVisibility() {
    const currentFieldIsValid =
      this.fields.length > 0 &&
      this.fields[this.currentFieldIndex] &&
      !this.nextButton.disabled;

    const nextVisibleIndex = this.#findNextVisibleIndex(this.currentFieldIndex);
    const prevVisibleIndex = this.#findPrevVisibleIndex(this.currentFieldIndex);

    const isLastVisibleField = nextVisibleIndex === -1;

    if (isLastVisibleField && currentFieldIsValid) {
      this.submitButton.style.display = "inline-block";
      this.nextButton.style.display = "none";
    } else {
      this.submitButton.style.display = "none";
      this.nextButton.style.display = "inline-block";
    }

    this.previousButton.style.display =
      prevVisibleIndex !== -1 ? "inline-block" : "none";
  }
}
