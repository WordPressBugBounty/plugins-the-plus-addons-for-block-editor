/**
 * FB-4: visitor-facing form messages were hardcoded English, so a translated
 * site showed English exactly when the visitor made a mistake. Strings are
 * resolved from window.tpgbFormL10n (printed translated by PHP) and fall back
 * to the original English if that object is absent.
 */
function tpgbFormText( key, fallback ) {
	var bag = window.tpgbFormL10n || {};
	if ( bag && Object.prototype.hasOwnProperty.call( bag, key ) && bag[ key ] ) {
		return bag[ key ];
	}
	if ( window.wp && window.wp.i18n && 'function' === typeof window.wp.i18n.__ ) {
		return window.wp.i18n.__( fallback, 'the-plus-addons-for-block-editor' );
	}
	return fallback;
}
document.addEventListener("DOMContentLoaded", function () {
    const formBlocks = document.querySelectorAll(".tp-form-block");
    formBlocks.forEach((formBlock) => {
        let formDataAttributes = {};
        let isSubmitting = false;

        const formId = formBlock.getAttribute("data-block-id");
        if (formId) {
            const validateEmail = (emailInput, errorShow) => {
                if (
                    !emailInput ||
                    !emailInput.hasAttribute("data-required") ||
                    emailInput.getAttribute("data-required") === "false"
                ) {
                    return true;
                }
                const emailValue = emailInput.value.trim();
                const customErrorMessage =
                    errorShow.getAttribute("data-error-message");
                if (!emailValue) {
                    errorShow.textContent =
                        customErrorMessage || tpgbFormText( 'required', "This field is required." );
                    return false;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailValue)) {
                    errorShow.textContent =
                        customErrorMessage ||
                        tpgbFormText( 'email', "Please enter a valid email address." );
                    return false;
                }

                errorShow.textContent = "";
                return true;
            };

            const validateMessage = (messageTextarea, errorShow) => {
                if (
                    !messageTextarea ||
                    !messageTextarea.hasAttribute("data-required") ||
                    messageTextarea.getAttribute("data-required") === "false"
                ) {
                    return true;
                }
                const messageValue = messageTextarea.value.trim();
                const customErrorMessage =
                    errorShow.getAttribute("data-error-message");
                if (!messageValue) {
                    errorShow.textContent =
                        customErrorMessage || tpgbFormText( 'required', "This field is required." );
                    return false;
                }
                errorShow.textContent = "";
                return true;
            };

            const validateCheckbox = (input, errorShow) => {
                if (
                    !input ||
                    !input.hasAttribute("data-required") ||
                    input.getAttribute("data-required") === "false"
                ) {
                    if (errorShow) {
                        errorShow.textContent = "";
                    }
                    return true;
                }

                const container = input.closest(
                    ".tp-form-checkbox-button, .tp-form-radio-button"
                );
                if (!container) return true;

                let isChecked = false;

                if (input.type === "radio") {
                    // For radio buttons
                    const radios = container.querySelectorAll(
                        `input[type="radio"][name="${input.name}"]`
                    );
                    isChecked = Array.from(radios).some(
                        (radio) => radio.checked
                    );
                } else {
                    // For checkboxes
                    const checkboxes = container.querySelectorAll(
                        `input[name="${input.name}"]`
                    );
                    isChecked = Array.from(checkboxes).some((cb) => cb.checked);
                }

                let customErrorMessage = tpgbFormText( 'required', "This field is required." );
                if (errorShow && typeof errorShow.getAttribute === "function") {
                    const msg = errorShow.getAttribute("data-error-message");
                    if (msg) {
                        customErrorMessage = msg;
                    }
                }

                if (!isChecked) {
                    if (errorShow) {
                        errorShow.textContent = customErrorMessage;
                    }
                    return false;
                } else {
                    if (errorShow) {
                        errorShow.textContent = "";
                    }
                    return true;
                }
            };

            const nameInputs = formBlock.querySelectorAll(".nxt-name-richtext");
            const emailInputs = formBlock.querySelectorAll(
                ".nxt-email-richtext"
            );
            const messageTextareas = formBlock.querySelectorAll(
                ".nxt-message-richtext"
            );
            const checkboxGroups = formBlock.querySelectorAll(
                ".tp-form-checkbox-button"
            );

            const radioGroups = formBlock.querySelectorAll(
                ".tp-form-radio-button "
            );
            const selectFields =
                formBlock.querySelectorAll(".nxt-option-field");
            const numberInputs = formBlock.querySelectorAll(
                ".nxt-number-richtext"
            );

            // Select corresponding error spans
            const errorShows = formBlock.querySelectorAll(".nxt-error-show");
            const errorShowsEmail =
                formBlock.querySelectorAll(".nxt-error-email");
            const errorShowsMessage =
                formBlock.querySelectorAll(".nxt-message-error");
            const errorShowsCheckbox = formBlock.querySelectorAll(
                ".nxt-error-checkbox"
            );
            const errorShowsRadio = formBlock.querySelectorAll(
                ".nxt-error-radiobox"
            );
            const errorShowsSelect =
                formBlock.querySelectorAll(".nxt-error-select");
            const errorShowsNumber =
                formBlock.querySelectorAll(".nxt-error-number");

            // Attach event listeners for real-time validation
            nameInputs.forEach((input, index) => {
                input.addEventListener("input", () =>
                    validateMessage(input, errorShows[index])
                );
            });

            emailInputs.forEach((input, index) => {
                input.addEventListener("input", () =>
                    validateEmail(input, errorShowsEmail[index])
                );
            });

            messageTextareas.forEach((textarea, index) => {
                textarea.addEventListener("input", () =>
                    validateMessage(textarea, errorShowsMessage[index])
                );
            });

            checkboxGroups.forEach((group) => {
                const checkboxes = group.querySelectorAll(
                    'input[type="checkbox"]'
                );
                const errorElement = group.querySelector(".nxt-error-checkbox");
                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener("change", () =>
                        validateCheckbox(checkbox, errorElement)
                    );
                });
            });

            radioGroups.forEach((group) => {
                const radio = group.querySelectorAll('input[type="radio"]');
                const errorElement = group.querySelector(".nxt-error-radiobox");
                radio.forEach((radio) => {
                    radio.addEventListener("change", () =>
                        validateCheckbox(radio, errorElement)
                    );
                });
            });

            selectFields.forEach((input, index) => {
                input.addEventListener("change", () =>
                    validateMessage(input, errorShowsSelect[index])
                );
            });

            numberInputs.forEach((input, index) => {
                input.addEventListener("input", () =>
                    validateMessage(input, errorShowsNumber[index])
                );
            });

            // Form Validation Function
            const isFormValid = () => {
                let isValid = true;

                nameInputs.forEach((nameInput, index) => {
                    if (nameInput.hasAttribute("data-required")) {
                        const isNameValid = validateMessage(
                            nameInput,
                            errorShows[index]
                        );
                        isValid = isValid && isNameValid;
                    }
                });

                emailInputs.forEach((emailInput, index) => {
                    if (emailInput.hasAttribute("data-required")) {
                        const isEmailValid = validateEmail(
                            emailInput,
                            errorShowsEmail[index]
                        );
                        isValid = isValid && isEmailValid;
                    }
                });

                messageTextareas.forEach((messageTextarea, index) => {
                    if (messageTextarea.hasAttribute("data-required")) {
                        const isMessageValid = validateMessage(
                            messageTextarea,
                            errorShowsMessage[index]
                        );
                        isValid = isValid && isMessageValid;
                    }
                });

                checkboxGroups.forEach((group) => {
                    const firstCheckbox = group.querySelector(
                        'input[type="checkbox"]'
                    );
                    const errorElement = group.querySelector(
                        ".nxt-error-checkbox"
                    );

                    if (
                        firstCheckbox &&
                        firstCheckbox.hasAttribute("data-required")
                    ) {
                        const isCheckboxValid = validateCheckbox(
                            firstCheckbox,
                            errorElement
                        );
                        isValid = isValid && isCheckboxValid;
                    }
                });

                radioGroups.forEach((group) => {
                    const firstRadio = group.querySelector(
                        'input[type="radio"]'
                    );
                    const errorElement = group.querySelector(
                        ".nxt-error-radiobox"
                    );

                    if (
                        firstRadio &&
                        firstRadio.hasAttribute("data-required")
                    ) {
                        const isRadioValid = validateCheckbox(
                            firstRadio,
                            errorElement
                        );
                        isValid = isValid && isRadioValid;
                    }
                });

                selectFields.forEach((selectField, index) => {
                    if (selectField.hasAttribute("data-required")) {
                        const isSelectValid = validateMessage(
                            selectField,
                            errorShowsSelect[index]
                        );
                        isValid = isValid && isSelectValid;
                    }
                });

                numberInputs.forEach((numberInput, index) => {
                    if (numberInput.hasAttribute("data-required")) {
                        const isNumberValid = validateMessage(
                            numberInput,
                            errorShowsNumber[index]
                        );
                        isValid = isValid && isNumberValid;
                    }
                });

                return isValid;
            };

            const formElement = formBlock.querySelector(".nxt-form");
            if (formElement) {
                formDataAttributes =
                    formElement.getAttribute("data-formdata") || "";
                formElement.addEventListener("submit", handleSubmit);
            }
            function handleSubmit(event) {
                event.preventDefault();
                if (!isFormValid()) {
                    return;
                }
                if (isSubmitting) {
                    return;
                }

                isSubmitting = true;
                submitForm(formDataAttributes);
                function submitForm(formDataAttributes) {
                    const formData = new FormData();

                    const getFieldValue = (name) => {
                        if (name === "checkbox") {
                            const checkboxContainer = formBlock.querySelector(
                                ".tp-form-checkbox-button"
                            );
                            if (checkboxContainer) {
                                const titleLabel =
                                    checkboxContainer.querySelector(
                                        ".nxt-check-title-label"
                                    );
                                const label = titleLabel
                                    ? titleLabel.innerText.trim()
                                    : name;

                                const checkedBoxes = Array.from(
                                    checkboxContainer.querySelectorAll(
                                        'input[type="checkbox"]:checked'
                                    )
                                ).map((checkbox) => checkbox.value);

                                if (checkedBoxes.length > 0) {
                                    return [
                                        {
                                            label,
                                            value: checkedBoxes.join(", "),
                                        },
                                    ];
                                }
                            }
                            return [];
                        }

                        return Array.from(
                            formBlock.querySelectorAll(
                                `input[name='${name}'], textarea[name='${name}'], select[name='${name}']`
                            )
                        )
                            .filter((field) => {
                                if (field.type === "radio") {
                                    return field.checked;
                                }
                                return field.value;
                            })
                            .map((field) => {
                                const parentContainer =
                                    field.type === "email"
                                        ? field.closest(".nxt-email-input")
                                        : field.classList.contains(
                                              "nxt-name-richtext"
                                          )
                                        ? field.closest(".nxt-name-input")
                                        : field.type === "number"
                                        ? field.closest(".nxt-number-input")
                                        : field.parentElement;
                                const labelEl = parentContainer
                                    ? parentContainer.querySelector("label")
                                    : null;
                                return {
                                    value: field.value,
                                    label: labelEl
                                        ? labelEl.innerText.trim()
                                        : name,
                                };
                            });
                    };

                    [
                        "text-field",
                        "email",
                        "message",
                        "number",
                        "select",
                        "checkbox",
                    ].forEach((fieldName) =>
                        getFieldValue(fieldName).forEach((field) => {
                            formData.append(field.label, field.value);
                        })
                    );

                    // Handle radio groups separately
                    Array.from(
                        formBlock.querySelectorAll('input[type="radio"]')
                    ).forEach((radio) => {
                        if (
                            radio.checked &&
                            radio.name.startsWith("radio-group")
                        ) {
                            const radioContainer = radio.closest(
                                ".tp-form-radio-button"
                            );
                            const titleLabel = radioContainer
                                ? radioContainer.querySelector(
                                      ".nxt-radio-title-label"
                                  )
                                : null;
                            const label = titleLabel
                                ? titleLabel.innerText.trim()
                                : radio.name;
                            formData.append(label, radio.value);
                        }
                    });

                    let cap = formBlock.querySelector(
                        '[name="cf-turnstile-response"],[name="g-recaptcha-response"] '
                    );

                    formData.append("actionOption", formDataAttributes || []);
                    formData.append("action", "nxt_form_action");
                    formData.append("nonce", tpgb_config.tpgb_nonce);
                    if (cap) {
                        formData.append("cf-turnstile-response", cap.value);
                    }
                    const actionOption =
                        formElement.hasAttribute("data-actionoption") &&
                        formElement.getAttribute("data-actionoption").trim() !==
                            ""
                            ? JSON.parse(
                                  formElement.getAttribute("data-actionoption")
                              ).map((item) => item.value)
                            : "";
                    let redirect = "";
                    let hasEmail = "";
                    let linkBlnk;
                    let lnkNoFlw;
                    const showMessage = (message, isError) => {
                        const messageElement = formBlock.querySelector(
                            ".nxt-success-message"
                        );
                        if (messageElement) {
                            messageElement.textContent = message;
                            messageElement.classList.remove(
                                "nxt-success-message",
                                "error-message"
                            );
                            messageElement.classList.add(
                                isError
                                    ? "error-message"
                                    : "nxt-success-message"
                            );
                            messageElement.style.display = "block";
                        }
                    };

                    if (actionOption && Array.isArray(actionOption)) {
                        const hasRedirectOption = actionOption.some(
                            (option) => option.toLowerCase() === "redirect"
                        );

                        if (
                            hasRedirectOption &&
                            formElement.hasAttribute("data-redirect")
                        ) {
                            redirect =
                                formElement.getAttribute("data-redirect");
                        }
                        if (
                            hasRedirectOption &&
                            formElement.hasAttribute("data-link-blank")
                        ) {
                            linkBlnk =
                                formElement.getAttribute("data-link-blank");
                        }
                        if (
                            hasRedirectOption &&
                            formElement.hasAttribute("data-link-nofollow")
                        ) {
                            lnkNoFlw =
                                formElement.getAttribute("data-link-nofollow");
                        }
                        const isEmail = actionOption.some(
                            (option) => option.toLowerCase() === "email"
                        );
                        if (isEmail) {
                            hasEmail = true;
                        }
                    }
                    const isValidURL = (string) => {
                        try {
                            new URL(string);
                            return true;
                        } catch (_) {
                            return false;
                        }
                    };

                    if (!actionOption || actionOption.length === 0) {
                        showMessage(tpgbFormText( 'noAction', "No form action is selected." ), true);
                        isSubmitting = false;
                        return;
                    }

                    if (isFormValid()) {
                        if (hasEmail) {
                            const submitButton =
                                formBlock.querySelector(".nxt-submit");
                            const loaderSpan =
                                submitButton.querySelector(".nxt-loader");
                            const buttonSvg =
                                submitButton.querySelector(".nxt-button-svg");
                            let buttonText =
                                submitButton.querySelector(".nxt-btn-text");
                            buttonText ? (buttonText.style.opacity = "0") : "";
                            buttonSvg ? (buttonSvg.style.opacity = "0") : "";
                            loaderSpan.style.opacity = "1";

                            tpgbAjax({
                                body: formData,
                                nonceField: "nonce",
                                nonceKind: "addons",
                                headers: {
                                    "X-WP-Nonce": tpgb_config.tpgb_nonce,
                                },
                                onSuccess: (responseData) => {
                                    isSubmitting = false;
                                    loaderSpan.style.opacity = "0";
                                    buttonSvg
                                        ? (buttonSvg.style.opacity = "1")
                                        : "";
                                    buttonText
                                        ? (buttonText.style.opacity = "1")
                                        : "";

                                    if (responseData.success) {
                                        const successMessage =
                                            formBlock
                                                .querySelector(
                                                    ".nxt-success-message"
                                                )
                                                .getAttribute(
                                                    "data-success-message"
                                                ) ||
                                            tpgbFormText( 'success', "Your submission was successful." );
                                        showMessage(successMessage, false);
                                        // Reset all form fields
                                        const formFields =
                                            formBlock.querySelectorAll(
                                                "input, textarea, select"
                                            );
                                        formFields.forEach((field) => {
                                            if (
                                                field.type === "checkbox" ||
                                                field.type === "radio"
                                            ) {
                                                field.checked = false;
                                            } else {
                                                field.value = "";
                                            }
                                        });
                                    } else {
                                        const errorMessage =
                                            responseData.data ||
                                            tpgbFormText( 'unknown', "Unknown error occurred." );
                                        showMessage(errorMessage, true);
                                        loaderSpan.style.opacity = "0";
                                        buttonSvg
                                            ? (buttonSvg.style.opacity = "1")
                                            : "";
                                        buttonText
                                            ? (buttonTextstyle.opacity = "1")
                                            : "";
                                    }
                                },
                                onError: (error) => {
                                    loaderSpan.style.opacity = "0";
                                    buttonSvg
                                        ? (buttonSvgstyle.opacity = "1")
                                        : "";
                                    buttonText
                                        ? (buttonTextstyle.opacity = "1")
                                        : "";
                                    showMessage(
                                        "An error occurred while sending the form data.",
                                        true
                                    );
                                    isSubmitting = false;
                                },
                            });
                        }
                        setTimeout(() => {
                            if (redirect && isValidURL(redirect)) {
                                if (linkBlnk === "1") {
                                    let newWindow = window.open("", "_blank");

                                    if (lnkNoFlw === "1") {
                                        let linkElement =
                                            newWindow.document.createElement(
                                                "a"
                                            );
                                        linkElement.setAttribute(
                                            "href",
                                            redirect
                                        );
                                        linkElement.setAttribute(
                                            "rel",
                                            "nofollow"
                                        );
                                        newWindow.document.body.appendChild(
                                            linkElement
                                        );

                                        linkElement.click();
                                    } else {
                                        newWindow.location.href = redirect;
                                    }
                                } else {
                                    window.location.href = redirect;
                                }
                            }
                        }, 2000);
                    }
                }
            }
        }
    });
});


/* ------------------------------------------------------------------------ *
 * Conditional Logic
 *
 * A field carrying data-nxt-cond is shown or hidden based on the answer to a
 * radio or checkbox field in the same form.
 *
 * Hiding a field also clears its inputs and marks them not-required, which is
 * what keeps the existing code correct without touching it: every validator
 * returns early when data-required is "false", and the submit collector skips
 * fields that are empty or unchecked. So a hidden field can neither block the
 * submit nor end up in the email.
 * ------------------------------------------------------------------------ */
document.addEventListener("DOMContentLoaded", function () {
    var original = new WeakMap();

    document.querySelectorAll(".tp-form-block").forEach(function (form) {
        var rules = [];

        form.querySelectorAll("[data-nxt-cond]").forEach(function (el) {
            var rule;
            try {
                rule = JSON.parse(el.getAttribute("data-nxt-cond"));
            } catch (e) {
                return;
            }
            if (!rule || !rule.src || !rule.op) {
                return;
            }
            /* A block id often starts with a digit, which is not a valid CSS
               class selector — match on the class attribute instead. */
            if (!/^[A-Za-z0-9_-]+$/.test(rule.src)) {
                return;
            }
            var source = form.querySelector(
                '[class~="tpgb-block-' + rule.src + '"]'
            );
            if (source) {
                rules.push({ el: el, rule: rule, source: source });
            }
        });

        if (!rules.length) {
            return;
        }

        /* Values currently chosen in the driving field. */
        function answers(source) {
            return Array.prototype.slice
                .call(
                    source.querySelectorAll(
                        'input[type="radio"], input[type="checkbox"]'
                    )
                )
                .filter(function (input) {
                    return input.checked;
                })
                .map(function (input) {
                    return (input.value || "").toLowerCase();
                });
        }

        function matches(rule, values) {
            var want = (rule.val || "").toLowerCase();
            switch (rule.op) {
                case "is":
                    return values.length === 1 && values[0] === want;
                case "isnot":
                    return !(values.length === 1 && values[0] === want);
                case "contains":
                    return values.indexOf(want) !== -1;
                case "notcontains":
                    return values.indexOf(want) === -1;
                case "empty":
                    return !values.length;
                case "notempty":
                    return !!values.length;
            }
            return false;
        }

        function setVisible(el, show) {
            var hidden = el.getAttribute("data-nxt-cond-hidden") === "1";
            if (show === !hidden) {
                return;
            }
            var inputs = el.querySelectorAll("input, textarea, select");

            if (show) {
                el.style.display = "";
                el.removeAttribute("data-nxt-cond-hidden");
                Array.prototype.forEach.call(inputs, function (input) {
                    var req = original.get(input);
                    if (req === undefined) {
                        return;
                    }
                    if (req === null) {
                        input.removeAttribute("data-required");
                    } else {
                        input.setAttribute("data-required", req);
                        input.required = req === "true";
                    }
                    original.delete(input);
                });
                return;
            }

            el.style.display = "none";
            el.setAttribute("data-nxt-cond-hidden", "1");
            Array.prototype.forEach.call(inputs, function (input) {
                if (!original.has(input)) {
                    original.set(input, input.getAttribute("data-required"));
                }
                input.setAttribute("data-required", "false");
                input.required = false;
                if (input.type === "checkbox" || input.type === "radio") {
                    input.checked = false;
                } else if (input.tagName === "SELECT") {
                    input.selectedIndex = 0;
                } else {
                    input.value = "";
                }
            });
        }

        /* Re-run a few times so a rule driven by a field that was itself just
           hidden settles instead of lagging one change behind. */
        function apply() {
            for (var pass = 0; pass < 3; pass++) {
                rules.forEach(function (item) {
                    var hit = matches(item.rule, answers(item.source));
                    setVisible(item.el, item.rule.act === "hide" ? !hit : hit);
                });
            }
        }

        form.addEventListener("change", apply);
        apply();
    });
});

/**
 * Form field accessibility layer.
 *
 * EF-1 - no form input carried an id, which made error association structurally
 *   impossible (aria-describedby had nothing to point at).
 * FB-1 - required state was communicated only by a custom data-required attribute
 *   and a CSS ::after asterisk; no `required`, no aria-required.
 * FB-2 - validation errors were written into a span with no link to the field and
 *   no aria-invalid, so assistive tech read the input as valid.
 * FB-8 - visible <label> elements were emitted without `for`, so four of five
 *   fields had no programmatically associated label.
 * FB-3 - nothing moved focus to the first invalid field on a failed submit.
 *
 * Applied to the rendered DOM on purpose: forms saved by any earlier version are
 * upgraded in place. save.js is untouched, so no block deprecation is needed and
 * no existing form is invalidated.
 */
( function () {
	var seq = 0;
	function uid( p ) {
		seq += 1;
		return p + '-' + seq + '-' + Math.random().toString( 36 ).slice( 2, 7 );
	}

	function enhanceField( control ) {
		if ( ! control || '1' === control.getAttribute( 'data-tpgb-a11y' ) ) {
			return;
		}
		var type = ( control.getAttribute( 'type' ) || '' ).toLowerCase();
		if ( 'hidden' === type || 'submit' === type || 'button' === type ) {
			return;
		}

		// the field wrapper: nearest ancestor that also holds the label / error span
		var box = control.closest( '[class*="-input"]' ) || control.parentElement;
		var hop = 0;
		while ( box && hop < 4 && ! box.querySelector( '[data-error-message]' ) && ! box.querySelector( 'label' ) ) {
			box = box.parentElement;
			hop += 1;
		}
		if ( ! box ) {
			return;
		}

		if ( ! control.id ) {
			control.id = uid( 'tpgb-field' );
		}

		// FB-8: associate the visible label that was emitted without `for`
		var labels = box.querySelectorAll( 'label:not([for])' );
		if ( 1 === labels.length && ( ! control.labels || ! control.labels.length ) ) {
			labels[ 0 ].setAttribute( 'for', control.id );
		}

		// FB-1: mirror the custom data-required onto the real attributes
		var req = control.getAttribute( 'data-required' );
		if ( req && 'false' !== req ) {
			if ( ! control.hasAttribute( 'required' ) ) {
				control.setAttribute( 'required', 'required' );
			}
			control.setAttribute( 'aria-required', 'true' );
		}

		// EF-1 + FB-2: give the error node an id and point the input at it
		var err = box.querySelector( '[data-error-message]' );
		if ( err ) {
			if ( ! err.id ) {
				err.id = uid( 'tpgb-err' );
			}
			var described = ( control.getAttribute( 'aria-describedby' ) || '' ).split( /\s+/ ).filter( Boolean );
			if ( described.indexOf( err.id ) === -1 ) {
				described.push( err.id );
				control.setAttribute( 'aria-describedby', described.join( ' ' ) );
			}
			// announce the message when it appears, and mirror it onto aria-invalid
			if ( ! err.getAttribute( 'role' ) ) {
				err.setAttribute( 'role', 'alert' );
				err.setAttribute( 'aria-live', 'polite' );
			}
			var sync = function () {
				var bad = '' !== ( err.textContent || '' ).trim();
				control.setAttribute( 'aria-invalid', bad ? 'true' : 'false' );
			};
			sync();
			if ( window.MutationObserver ) {
				new window.MutationObserver( sync ).observe( err, { childList: true, characterData: true, subtree: true } );
			}
		}

		control.setAttribute( 'data-tpgb-a11y', '1' );
	}

	function enhanceForm( form ) {
		Array.prototype.forEach.call( form.querySelectorAll( 'input, textarea, select' ), enhanceField );

		if ( '1' === form.getAttribute( 'data-tpgb-a11y-form' ) ) {
			return;
		}
		// FB-3: on a failed submit, take the user to the first field that failed
		form.addEventListener( 'submit', function () {
			window.setTimeout( function () {
				var errs = form.querySelectorAll( '[data-error-message]' );
				for ( var i = 0; i < errs.length; i++ ) {
					if ( '' !== ( errs[ i ].textContent || '' ).trim() ) {
						var id = errs[ i ].id;
						var target = id ? form.querySelector( '[aria-describedby~="' + id + '"]' ) : null;
						if ( target && 'function' === typeof target.focus ) {
							target.focus();
							if ( target.scrollIntoView ) {
								target.scrollIntoView( { block: 'center', behavior: 'smooth' } );
							}
						}
						return;
					}
				}
			}, 60 );
		} );
		form.setAttribute( 'data-tpgb-a11y-form', '1' );
	}

	function tpgbFormA11y( scope ) {
		var root = scope || document;
		if ( ! root || ! root.querySelectorAll ) {
			return;
		}
		Array.prototype.forEach.call( root.querySelectorAll( '.tp-form-block form, form.nxt-form, .tp-form-block' ), function ( node ) {
			enhanceForm( 'FORM' === node.tagName ? node : node );
		} );
	}

	window.tpgbFormA11y = tpgbFormA11y;
	document.addEventListener( 'DOMContentLoaded', function () {
		tpgbFormA11y( document );
	} );
}() );
