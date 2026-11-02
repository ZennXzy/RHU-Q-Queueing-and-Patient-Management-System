
(() => {
    "use strict";

    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("accountRegistrationForm");
        if (!form) return;

        // --Registration Steps--
        const step1 = document.getElementById("patientInfoStep");
        const step2 = document.getElementById("credentialsStep");
        const nextButton = document.getElementById("nextStepBtn");
        const backButton = document.getElementById("backStepBtn");

        // --Required Patient Fields--
        const patientFields = [
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

        // --Required Selection Groups--
        const patientGroups = [
            "gender",
            "employment_status",
            "civilStatus",
            "philStatusInput",
            "yakapmem"
        ];

        // --Conditional Required Fields--
        const conditionalFields = [
            "slname",
            "sfname",
            "philHNumInput",
            "prinMemInput"
        ];

        // --Required Account Credentials--
        const credentialFields = [
            "username",
            "password",
            "confirm_password"
        ];

        const touched = new Set();

        let patientSubmitted = false;
        let credentialsSubmitted = false;

        // --Helpers--
        const field = id => form.querySelector(`#${id}`);

        const selected = name =>
            form.querySelector(
                `input[name="${name}"]:checked`
            )?.value;

        function isVisible(element) {
            if (!element) return false;

            return (
                element.getClientRects().length > 0 &&
                getComputedStyle(element).visibility !== "hidden"
            );
        }

        // --Validation Targets--
        function targetFor(input) {
            if (!input) return null;

            if (input.id === "date_of_birth") {
                return document.getElementById("dobContainer") ||
                    input.closest(".date");
            }

            if (input.id === "religion") {
                return input.closest(".input-select");
            }

            return input;
        }

        // --Apply / Remove Validation--
        function mark(input, invalid) {
            if (!input) return;

            const target = targetFor(input);

            if (target === input) {
                input.classList.toggle(
                    "required-invalid",
                    invalid
                );
            } else if (target) {
                target.classList.toggle(
                    "required-invalid",
                    invalid
                );

                input.classList.remove("required-invalid");
            }

            input.setAttribute(
                "aria-invalid",
                String(invalid)
            );
        }

        // --Check Individual Fields--
        function checkField(id, show = true) {
            const input = field(id);

            if (!input) return true;

            const target = targetFor(input);

            if (!isVisible(target)) {
                mark(input, false);
                return true;
            }

            let valid = String(input.value || "").trim().length > 0;

            // --Password Minimum Length--
            if (id === "password") {
                valid = input.value.length >= 6;
            }

            // --Confirm Password--
            if (id === "confirm_password") {
                valid =
                    valid &&
                    input.value === field("password")?.value;
            }

            mark(input, show && !valid);

            return valid;
        }

        // --Selection Group Container--
        function groupTarget(name) {
            const input = form.querySelector(
                `input[name="${name}"]`
            );

            if (!input) return null;

            if (
                name === "gender" ||
                name === "employment_status" ||
                name === "civilStatus"
            ) {
                return input.closest(".input-container");
            }

            return input.closest(".checkbox-row");
        }

        // --Check Radio / Checkbox Groups--
        function checkGroup(name, show = true) {
            const options = [
                ...form.querySelectorAll(
                    `input[name="${name}"]`
                )
            ];

            if (!options.length) return true;

            const target = groupTarget(name);

            if (!isVisible(target)) return true;

            const valid = options.some(
                option => option.checked
            );

            target.classList.toggle(
                "required-invalid",
                show && !valid
            );

            options.forEach(option => {
                option.setAttribute(
                    "aria-invalid",
                    String(show && !valid)
                );
            });

            return valid;
        }

        // --Conditional Requirements--
        function isConditionalRequired(id) {
            const civilStatus = selected("civilStatus");
            const philStatus = selected("philStatusInput");

            if (id === "slname" || id === "sfname") {
                return (
                    civilStatus === "Married" ||
                    civilStatus === "Widow"
                );
            }

            if (id === "philHNumInput") {
                return (
                    philStatus === "Member" ||
                    philStatus === "Dependent"
                );
            }

            if (id === "prinMemInput") {
                return philStatus === "Dependent";
            }

            return false;
        }

        function checkConditional(id, show = true) {
            const input = field(id);

            if (!input) return true;

            if (
                !isConditionalRequired(id) ||
                !isVisible(input)
            ) {
                mark(input, false);
                return true;
            }

            return checkField(id, show);
        }

        // --Validate Patient Information--
        function validatePatient(showAll = false) {
            let valid = true;

            patientFields.forEach(id => {
                if (!checkField(
                    id,
                    showAll || touched.has(id)
                )) {
                    valid = false;
                }
            });

            patientGroups.forEach(name => {
                if (!checkGroup(
                    name,
                    showAll || touched.has(name)
                )) {
                    valid = false;
                }
            });

            conditionalFields.forEach(id => {
                if (!checkConditional(
                    id,
                    showAll || touched.has(id)
                )) {
                    valid = false;
                }
            });

            return valid;
        }

        // --Validate Account Credentials--
        function validateCredentials(showAll = false) {
            let valid = true;

            credentialFields.forEach(id => {
                if (!checkField(
                    id,
                    showAll || touched.has(id)
                )) {
                    valid = false;
                }
            });

            return valid;
        }

        // --Required Asterisk--
        function addStar(element) {
            if (!element) return;

            if (element.querySelector(".required-asterisk")) {
                return;
            }

            const star = document.createElement("span");

            star.className = "required-asterisk";
            star.textContent = " *";
            star.setAttribute("aria-hidden", "true");

            element.appendChild(star);
        }

        // --Add Required Indicators--
        function addMarkers() {
            // --Text Field Placeholders--
            [...patientFields, ...conditionalFields].forEach(id => {
                const input = field(id);

                if (!input) return;

                if (id === "date_of_birth") {
                    addStar(
                        document.getElementById("dobPlaceholder")
                    );
                    return;
                }

                if (input.tagName === "SELECT") {
                    return;
                }

                if (input.placeholder &&
                    !input.placeholder.startsWith("* ")) {
                    input.placeholder = "* " + input.placeholder;
                }
            });

            // --Radio Group Headings--
            [
                "gender",
                "employment_status",
                "civilStatus"
            ].forEach(name => {
                const input = form.querySelector(
                    `input[name="${name}"]`
                );

                const heading = input
                    ?.closest(".form-group")
                    ?.querySelector(":scope > span");

                addStar(heading);
            });

            // --PhilHealth / Yakap Headings--
            [
                "philStatusInput",
                "yakapmem"
            ].forEach(name => {
                const target = groupTarget(name);

                addStar(
                    target?.querySelector("legend")
                );
            });

            addStar(
                document.querySelector("#philNumDiv legend")
            );

            addStar(
                document.querySelector("#prinMemDiv legend")
            );

            // --Credential Labels--
            credentialFields.forEach(id => {
                const input = field(id);

                const label = input
                    ?.closest(".form-group")
                    ?.querySelector("label");

                if (!label) return;

                // Remove existing plain asterisk.
                if (!label.querySelector(".required-asterisk")) {
                    label.textContent =
                        label.textContent.replace(/\s*\*$/, "");

                    addStar(label);
                }
            });
        }

        addMarkers();

        // --Validate When Leaving An Empty Input--
        [
            ...patientFields,
            ...conditionalFields,
            ...credentialFields
        ].forEach(id => {
            const input = field(id);

            if (!input) return;

            const validate = () => {
                const show =
                    touched.has(id) ||
                    (credentialFields.includes(id)
                        ? credentialsSubmitted
                        : patientSubmitted);

                if (conditionalFields.includes(id)) {
                    checkConditional(id, show);
                } else {
                    checkField(id, show);
                }

                // --Password Confirmation Dependency--
                if (
                    id === "password" &&
                    (touched.has("confirm_password") ||
                        credentialsSubmitted)
                ) {
                    checkField("confirm_password", true);
                }
            };

            input.addEventListener("blur", () => {
                touched.add(id);
                validate();
            });

            input.addEventListener("input", validate);
            input.addEventListener("change", validate);
        });

        // --Date of Birth Container Blur--
        const dobContainer =
            document.getElementById("dobContainer");

        dobContainer?.addEventListener(
            "focusout",
            event => {
                if (
                    !dobContainer.contains(event.relatedTarget)
                ) {
                    touched.add("date_of_birth");

                    checkField("date_of_birth", true);
                }
            }
        );

        // --Validate Radio Groups--
        patientGroups.forEach(name => {
            const options = form.querySelectorAll(
                `input[name="${name}"]`
            );

            options.forEach(option => {
                option.addEventListener("change", () => {
                    touched.add(name);

                    checkGroup(name, true);

                    // Recheck conditional inputs.
                    conditionalFields.forEach(id => {
                        checkConditional(
                            id,
                            patientSubmitted || touched.has(id)
                        );
                    });
                });
            });

            // --Validate When Leaving Group--
            const target = groupTarget(name);

            target?.addEventListener(
                "focusout",
                event => {
                    if (
                        !target.contains(event.relatedTarget)
                    ) {
                        touched.add(name);

                        checkGroup(name, true);
                    }
                }
            );
        });

        // --Find First Invalid Field--
        function focusFirstInvalid(section) {
            const invalid = section?.querySelector(
                ".required-invalid"
            );

            if (!invalid) return;

            const control = invalid.matches(
                "input, select"
            )
                ? invalid
                : invalid.querySelector("input, select");

            invalid.scrollIntoView({
                behavior: "smooth",
                block: "center"
            });

            if (control && isVisible(control)) {
                control.focus({
                    preventScroll: true
                });
            }
        }

        // --Next Step Validation--
        nextButton?.addEventListener(
            "click",
            event => {
                patientSubmitted = true;

                if (!validatePatient(true)) {
                    event.preventDefault();
                    event.stopImmediatePropagation();

                    focusFirstInvalid(step1);
                }
            },
            true
        );

        // --Back Button--
        backButton?.addEventListener("click", () => {
            // Preserve validation state when returning.
            queueMicrotask(() => {
                if (isVisible(step1)) {
                    validatePatient(patientSubmitted);
                }
            });
        });

        // --Final Registration Validation--
        form.addEventListener(
            "submit",
            event => {
                patientSubmitted = true;
                credentialsSubmitted = true;

                const patientOK = validatePatient(true);
                const credentialsOK =
                    validateCredentials(true);

                if (patientOK && credentialsOK) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();

                if (!patientOK) {
                    if (step1) {
                        step1.style.display = "";
                    }

                    if (step2) {
                        step2.style.display = "none";
                    }

                    requestAnimationFrame(() => {
                        focusFirstInvalid(step1);
                    });
                } else {
                    focusFirstInvalid(step2);
                }
            },
            true
        );

        // --Reset Validation--
        form.addEventListener("reset", () => {
            patientSubmitted = false;
            credentialsSubmitted = false;

            touched.clear();

            requestAnimationFrame(() => {
                form.querySelectorAll(
                    ".required-invalid"
                ).forEach(element => {
                    element.classList.remove(
                        "required-invalid"
                    );
                });

                form.querySelectorAll(
                    '[aria-invalid="true"]'
                ).forEach(element => {
                    element.setAttribute(
                        "aria-invalid",
                        "false"
                    );
                });
            });
        });
    });
})();
