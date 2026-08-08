function handleEmploymentChange() {
    const occupationDiv = document.getElementById('occupationDiv');
    const businessDiv = document.getElementById('businessDiv');
    const occupationInput = document.getElementById('occupationInput');
    const businessInput = document.getElementById('businessInput');

    const selectedRadio = document.querySelector('input[name="employment_status"]:checked');

    if (!selectedRadio) return;

    if (selectedRadio.value === 'employed') {
        occupationDiv.style.display = 'block';
        businessDiv.style.display = 'none';
        businessInput.value = ''; // Clear business input
    } else if (selectedRadio.value === 'self-employed') {
        occupationDiv.style.display = 'none';
        businessDiv.style.display = 'block';
        occupationInput.value = ''; // Clear occupation input
    } else { // unemployed
        occupationDiv.style.display = 'none';
        businessDiv.style.display = 'none';
        occupationInput.value = '';
        businessInput.value = '';
    }
}

const gender_checkboxes = document.querySelectorAll(".gender-checkbox");
gender_checkboxes.forEach(checkbox => {
    checkbox.addEventListener("change", function() {
        if (this.checked) {
            checkboxes.forEach(other => {
                if (other !== this) {
                    other.checked = false;
                }
            });
        }
    });
});

