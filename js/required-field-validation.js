
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("patientRegistrationForm");
    if (!form) return;

    // --Required Fields--
    const requiredFields = [
        "lname", "fname", "address", "date_of_birth", "religion",
        "mlname", "mfname", "flname", "ffname"
    ];

    const requiredRadioGroups = [
        "gender", "employment_status", "civilStatus", "philStatusInput", "yakapmem"
    ];

    const conditionalFields = ["slname", "sfname", "philHNumInput", "prinMemInput"];
    const touched = new Set();
    let submitted = false;

    // --Required Asterisks--
    function addStar(label) {
        if (!label || label.querySelector(".required-asterisk")) return;
        const star = document.createElement("span");
        star.className = "required-asterisk";
        star.textContent = " *";
        label.appendChild(star);
    }

    requiredFields.forEach(id => {
        const field = document.getElementById(id);
        if (!field) return;

        if (id === "date_of_birth") {
            addStar(document.getElementById("dobPlaceholder"));
        } else if (id !== "religion" && field.placeholder) {
            if (!field.placeholder.startsWith("* ")) field.placeholder = "* " + field.placeholder;
        }
    });

    ["gender", "employment_status", "civilStatus"].forEach(name => {
        const input = form.querySelector(`[name="${name}"]`);
        addStar(input?.closest(".form-group")?.querySelector(":scope > span"));
    });

    ["philStatusInput", "yakapmem"].forEach(name => {
        const input = form.querySelector(`[name="${name}"]`);
        addStar(input?.closest(".checkbox-row")?.querySelector("legend"));
    });

    addStar(document.querySelector("#philNumDiv legend"));
    addStar(document.querySelector("#prinMemDiv legend"));

    // --Visible Controls--
    function isVisible(element) {
        if (!element) return false;
        return element.getClientRects().length > 0 && getComputedStyle(element).visibility !== "hidden";
    }

    function targetForField(field) {
        if (field.id === "date_of_birth") return document.getElementById("dobContainer");
        if (field.id === "religion") return field.closest(".input-select");
        return field;
    }

    function setFieldInvalid(field, invalid) {
        if (!field) return;
        const target = targetForField(field);
        if (target) target.classList.toggle("required-invalid", invalid && target === field);
        if (target && target !== field) target.classList.toggle("required-invalid-group", invalid);
        if (target !== field) field.classList.remove("required-invalid");
        field.setAttribute("aria-invalid", String(invalid));
    }

    function groupTarget(name) {
        const input = form.querySelector(`[name="${name}"]`);
        if (!input) return null;
        if (name === "gender" || name === "employment_status" || name === "civilStatus") {
            return input.closest(".input-container");
        }
        return input.closest(".checkbox-row");
    }

    function validateField(id, show = true) {
        const field = document.getElementById(id);
        if (!field) return true;
        const visible = id === "date_of_birth"
            ? isVisible(document.getElementById("dobContainer"))
            : isVisible(field);
        if (!visible) {
            setFieldInvalid(field, false);
            return true;
        }
        const invalid = !String(field.value || "").trim();
        setFieldInvalid(field, show && invalid);
        return !invalid;
    }

    function validateRadioGroup(name, show = true) {
        const inputs = [...form.querySelectorAll(`[name="${name}"]`)];
        if (!inputs.length) return true;
        const target = groupTarget(name);
        if (!isVisible(target)) return true;
        const invalid = !inputs.some(input => input.checked);
        target.classList.toggle("required-invalid-group", show && invalid);
        inputs.forEach(input => input.setAttribute("aria-invalid", String(show && invalid)));
        return !invalid;
    }

    // --Conditional Requirements--
    function isConditionalRequired(id) {
        const civil = form.querySelector('input[name="civilStatus"]:checked')?.value;
        const phil = form.querySelector('input[name="philStatusInput"]:checked')?.value;
        if (id === "slname" || id === "sfname") return civil === "Married" || civil === "Widow";
        if (id === "philHNumInput") return phil === "Member" || phil === "Dependent";
        if (id === "prinMemInput") return phil === "Dependent";
        return false;
    }

    function validateConditional(id, show = true) {
        const field = document.getElementById(id);
        if (!field) return true;
        if (!isConditionalRequired(id) || !isVisible(field)) {
            setFieldInvalid(field, false);
            return true;
        }
        const invalid = !field.value.trim();
        setFieldInvalid(field, show && invalid);
        return !invalid;
    }

    // --Validate On Leaving An Empty Field--
    [...requiredFields, ...conditionalFields].forEach(id => {
        const field = document.getElementById(id);
        if (!field) return;
        const check = () => {
            if (requiredFields.includes(id)) validateField(id, submitted || touched.has(id));
            else validateConditional(id, submitted || touched.has(id));
        };
        field.addEventListener("blur", () => {
            touched.add(id);
            check();
        });
        field.addEventListener("input", check);
        field.addEventListener("change", check);
    });

    // --Date Container Blur--
    const dobContainer = document.getElementById("dobContainer");
    dobContainer?.addEventListener("focusout", event => {
        if (!dobContainer.contains(event.relatedTarget)) {
            touched.add("date_of_birth");
            validateField("date_of_birth");
        }
    });

    // --Radio Groups--
    requiredRadioGroups.forEach(name => {
        form.querySelectorAll(`[name="${name}"]`).forEach(input => {
            input.addEventListener("change", () => {
                touched.add(name);
                validateRadioGroup(name);
                conditionalFields.forEach(id => validateConditional(id, submitted || touched.has(id)));
            });
        });
    });

    // --Radio Group Blur--
    requiredRadioGroups.forEach(name => {
        const target = groupTarget(name);
        target?.addEventListener("focusout", event => {
            if (!target.contains(event.relatedTarget)) {
                touched.add(name);
                validateRadioGroup(name);
            }
        });
    });

    // --Validate On Submit--
    form.addEventListener("submit", event => {
        submitted = true;
        let valid = true;
        requiredFields.forEach(id => { if (!validateField(id)) valid = false; });
        requiredRadioGroups.forEach(name => { if (!validateRadioGroup(name)) valid = false; });
        conditionalFields.forEach(id => { if (!validateConditional(id)) valid = false; });

        if (!valid) {
            event.preventDefault();
            const first = form.querySelector(".required-invalid, .required-invalid-group");
            if (first) {
                first.scrollIntoView({ behavior: "smooth", block: "center" });
            }
        }
    });

    // --Reset Validation--
    form.addEventListener("reset", () => {
        submitted = false;
        touched.clear();
        form.querySelectorAll(".required-invalid, .required-invalid-group").forEach(element => {
            element.classList.remove("required-invalid", "required-invalid-group");
        });
        form.querySelectorAll('[aria-invalid="true"]').forEach(element => {
            element.setAttribute("aria-invalid", "false");
        });
    });
});
