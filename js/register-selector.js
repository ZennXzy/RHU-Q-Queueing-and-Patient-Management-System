// employment-selector
function handleEmploymentChange() {
    const occupationDiv = document.getElementById('occupationDiv');
    const businessDiv = document.getElementById('businessDiv');
    const occupationInput = document.getElementById('occupationInput');
    const businessInput = document.getElementById('businessInput');

    const employmentRadio = document.querySelector('input[name="employment_status"]:checked');

    if (!employmentRadio) return;

    if (employmentRadio.value === 'employed') {
        occupationDiv.style.display = 'block';
        businessDiv.style.display = 'none';
        businessInput.value = ''; // Clear business input
    } else if (employmentRadio.value === 'self-employed') {
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

// gender selector
const gender_checkboxes = document.querySelectorAll(".gender-checkbox");
gender_checkboxes.forEach(checkbox => {
    checkbox.addEventListener("change", function() {
        if (this.checked) {
            gender_checkboxes.forEach(other => {
                if (other !== this) {
                    other.checked = false;
                }
            });
        }
    });
});

// philhealth selector
function handlePhilHChange() {
    const philNumDiv = document.getElementById('philNumDiv');
    const prinMemberDiv = document.getElementById('prinMemDiv');
    const philHNumInput = document.getElementById('philHNumInput');
    const prinMemInput = document.getElementById('prinMemInput');

    const philRadio = document.querySelector('input[name="philStatusInput"]:checked');

    if(!philRadio) return;

    if (philRadio.value === 'member') {
        philNumDiv.style.display = 'block';
        prinMemberDiv.style.display = 'none';
        prinMemberDiv.value = ''; // Clear business input
    } else if (philRadio.value === 'dependent') {
        philNumDiv.style.display = 'block';
        prinMemberDiv.style.display = 'block';
    } else { // unemployed
        philNumDiv.style.display = 'none';
        prinMemberDiv.style.display = 'none';
        philHNumInput.value = ''; 
        prinMemInput.value = ''; 
    }
}

// nhts selector
function handleNhtsChange() {
    const nhtsNumInputDiv = document.getElementById('nhtsNumInputDiv');
    const nhtsNumInput = document.getElementById('nhtsNumInput');

    const nhtsRadio = document.querySelector('input[name="nhtsInput"]:checked');

    if(!nhtsRadio) return;

    if (nhtsRadio.value === 'yes') {
        nhtsNumInputDiv.style.display = 'block';
    
    } else { // no
        nhtsNumInputDiv.style.display = 'none';
        nhtsNumInput.value = '';
    }
}

