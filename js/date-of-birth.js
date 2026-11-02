
document.addEventListener("DOMContentLoaded", () => {
    const container = document.getElementById("dobContainer");
    const dob = document.getElementById("date_of_birth");
    const placeholder = document.getElementById("dobPlaceholder");

    if (!container || !dob || !placeholder) return;

    // --Activate Date of Birth--
    function activateDOB() {
        container.classList.add("active");
    }

    // --Open Date Picker--
    function openDatePicker() {
        activateDOB();

        if (typeof dob.showPicker === "function") {
            try {
                dob.showPicker();
            } catch (error) {
                dob.focus();
            }
        } else {
            dob.focus();
        }
    }

    // --Container Click--
    container.addEventListener("click", (event) => {
        if (event.target === dob) {
            activateDOB();
            return;
        }

        openDatePicker();
    });

    // --Date Selection--
    dob.addEventListener("change", () => {
        container.classList.toggle("has-value", Boolean(dob.value));
    });

    // --Reset Date--
    if (dob.form) {
        dob.form.addEventListener("reset", () => {
            setTimeout(() => {
                container.classList.remove("active", "has-value");
            }, 0);
        });
    }
});
