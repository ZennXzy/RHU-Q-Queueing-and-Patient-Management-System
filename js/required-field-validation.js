
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("patientRegistrationForm");

    if (!form) return;

    // Required text and select fields
    const requiredFields = [
        "lname",
        "fname",
        "address",
        "date_of_birth",
        "religion",
        "mlname",
        "mfname",
        "flname",
        "ffname"
    ];

    // Required radio groups
    const requiredRadioGroups = [
        "gender",
        "employment_status",
        "civilStatus",
        "philStatusInput",
        "yakapmem"
    ];

    // Add a red asterisk to a label or placeholder
    function addRequiredAsterisk(element) {
        if (!element) return;

        let label = null;

        if (element.id === "date_of_birth") {
            label = element.closest(".date")?.querySelector("span");
        } else if (element.id === "religion") {
            label = null;
        } else {
            const group = element.closest(".form-group");
            label = group?.querySelector(":scope > span");
        }

        if (label && !label.querySelector(".required-asterisk")) {
            const star = document.createElement("span");
            star.className = "required-asterisk";
            star.textContent = "*";
            label.appendChild(star);
        }

        // For fields that use placeholders instead of visible labels
        if (!label && element.placeholder) {
            if (!element.dataset.originalPlaceholder) {
                element.dataset.originalPlaceholder = element.placeholder;
            }

            if (!element.placeholder.startsWith("* ")) {
                element.placeholder = "* " + element.dataset.originalPlaceholder;
            }
        }
    }

    // Add asterisks to required text fields
    requiredFields.forEach(id => {
        const field = document.getElementById(id);

        if (field) {
            addRequiredAsterisk(field);
        }
    });

    // Add asterisks to family name placeholders
    [
        "mlname",
        "mfname",
        "flname",
        "ffname"
    ].forEach(id => {
        const field = document.getElementById(id);

        if (field && field.placeholder) {
            if (!field.dataset.originalPlaceholder) {
                field.dataset.originalPlaceholder = field.placeholder;
            }

            field.placeholder = "* " + field.dataset.originalPlaceholder;
        }
    });

    // Add asterisks to group headings
    const groupLabels = [
        document.querySelector(".form-group:has(.gender-checkbox) > span"),
        document.querySelector(".form-group:has(input[name='employment_status']) > span"),
        document.querySelector(".form-group:has(input[name='civilStatus']) > span"),
        document.querySelector("#philNumDiv")?.closest(".form-group")?.querySelector("legend"),
        document.querySelector("#prinMemDiv")?.closest(".form-group")?.querySelector("legend")
    ];

    groupLabels.forEach(label => {
        if (label && !label.querySelector(".required-asterisk")) {
            const star = document.createElement("span");
            star.className = "required-asterisk";
            star.textContent = " *";
            label.appendChild(star);
        }
    });

    // Check whether an element is visible
    function isVisible(element) {
        return !!element &&
            element.getClientRects().length > 0 &&
            getComputedStyle(element).display !== "none";
    }

    // Apply or remove an error border
    function setInvalid(element, invalid) {
        if (!element) return;

        element.classList.toggle("required-invalid", invalid);
        element.setAttribute("aria-invalid", invalid ? "true" : "false");

        const wrapper = element.closest(
            ".form-group, .date, .input-container, .input-select, .fieldset-group"
        );

        if (wrapper) {
            wrapper.classList.toggle("required-invalid-group", invalid);
        }
    }

    // Validate text and select fields
    function validateField(id) {
        const field = document.getElementById(id);

        if (!field || !isVisible(field)) return true;

        const invalid = field.value.trim() === "";

        setInvalid(field, invalid);

        return !invalid;
    }

    // Validate radio groups
    function validateRadioGroup(name) {
        const radios = [
            ...form.querySelectorAll(
                `input[type="radio"][name="${name}"], input[type="checkbox"][name="${name}"]`
            )
        ];

        if (radios.length === 0) return true;

        const wrapper = radios[0].closest(
            ".form-group, .fieldset-group, .input-container"
        );

        if (wrapper && !isVisible(wrapper)) return true;

        const invalid = !radios.some(input => input.checked);

        if (wrapper) {
            wrapper.classList.toggle("required-invalid-group", invalid);
        }

        radios.forEach(input => {
            input.setAttribute("aria-invalid", invalid ? "true" : "false");
        });

        return !invalid;
    }

    // Validate conditional fields
    function validateConditionalFields() {
        let valid = true;

        // Spouse is required only for Married or Widow
        const civilStatus = form.querySelector(
            'input[name="civilStatus"]:checked'
        )?.value;

        const spouseRequired =
            civilStatus === "Married" || civilStatus === "Widow";

        ["slname", "sfname"].forEach(id => {
            const field = document.getElementById(id);

            if (!field) return;

            if (spouseRequired && isVisible(field)) {
                const invalid = field.value.trim() === "";

                setInvalid(field, invalid);

                if (invalid) valid = false;
            } else {
                setInvalid(field, false);
            }
        });

        // PhilHealth number is required for Member and Dependent
        const philStatus = form.querySelector(
            'input[name="philStatusInput"]:checked'
        )?.value;

        const philNumber = document.getElementById("philHNumInput");
        const principalMember = document.getElementById("prinMemInput");

        if (philNumber) {
            const required =
                philStatus === "Member" || philStatus === "Dependent";

            const invalid =
                required &&
                isVisible(philNumber) &&
                philNumber.value.trim() === "";

            setInvalid(philNumber, invalid);

            if (invalid) valid = false;
        }

        // Principal member is required for Dependent
        if (principalMember) {
            const required = philStatus === "Dependent";

            const invalid =
                required &&
                isVisible(principalMember) &&
                principalMember.value.trim() === "";

            setInvalid(principalMember, invalid);

            if (invalid) valid = false;
        }

        return valid;
    }

    // Validate all required fields
    function validateAll() {
        let valid = true;

        requiredFields.forEach(id => {
            if (!validateField(id)) valid = false;
        });

        requiredRadioGroups.forEach(name => {
            if (!validateRadioGroup(name)) valid = false;
        });

        if (!validateConditionalFields()) {
            valid = false;
        }

        return valid;
    }

    // Validate while the user types
    requiredFields.forEach(id => {
        const field = document.getElementById(id);

        if (!field) return;

        ["input", "change", "blur"].forEach(eventName => {
            field.addEventListener(eventName, () => {
                if (field.value.trim() !== "") {
                    setInvalid(field, false);
                } else if (form.dataset.submitted === "true") {
                    validateField(id);
                }
            });
        });
    });

    // Clear errors when a radio or checkbox is selected
    requiredRadioGroups.forEach(name => {
        form.querySelectorAll(`[name="${name}"]`).forEach(input => {
            input.addEventListener("change", () => {
                validateRadioGroup(name);
                validateConditionalFields();
            });
        });
    });

    // Validate conditional fields as the user types
    ["slname", "sfname", "philHNumInput", "prinMemInput"].forEach(id => {
        const field = document.getElementById(id);

        if (!field) return;

        field.addEventListener("input", validateConditionalFields);
        field.addEventListener("change", validateConditionalFields);
    });

    // Prevent submission if required fields are empty
    form.addEventListener("submit", event => {
        form.dataset.submitted = "true";

        if (!validateAll()) {
            event.preventDefault();

            const firstInvalid = form.querySelector(
                ".required-invalid, .required-invalid-group"
            );

            if (firstInvalid) {
                firstInvalid.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });

                const focusTarget =
                    firstInvalid.matches("input, select, textarea")
                        ? firstInvalid
                        : firstInvalid.querySelector(
                            "input, select, textarea"
                        );

                if (focusTarget) {
                    focusTarget.focus({ preventScroll: true });
                }
            }
        }
    });

    // Clear errors when Reset is clicked
    form.addEventListener("reset", () => {
        form.dataset.submitted = "false";

        setTimeout(() => {
            form.querySelectorAll(
                ".required-invalid, .required-invalid-group"
            ).forEach(element => {
                element.classList.remove(
                    "required-invalid",
                    "required-invalid-group"
                );

                element.setAttribute("aria-invalid", "false");
            });
        }, 0);
    });
});
