document.addEventListener('DOMContentLoaded', () => {
    const addBtn = document.getElementById('addBtn');
    const addModal = document.getElementById('addAppointmentModal');
    const closeAddModal = document.getElementById('closeAddModal');
    const cancelAddBtn = document.getElementById('cancelAddBtn');
    const addForm = document.getElementById('addAppointmentForm');

    function fetchPatientAppointments() {
        fetch('../php/patient_appointment_list.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('patientAppointmentsList').innerHTML = data.html;
                    document.getElementById('patientAppCount').innerText = data.total;
                }
            })
            .catch(err => console.error('Error fetching appointments:', err));
    }

    // Modal Control
    function openModal() {
        if (addModal) addModal.style.display = 'flex';
    }

    function closeModal() {
        if (addModal) {
            addModal.style.display = 'none';
            if (addForm) addForm.reset();
        }
    }

    if (addBtn) addBtn.addEventListener('click', openModal);
    if (closeAddModal) closeAddModal.addEventListener('click', closeModal);
    if (cancelAddBtn) cancelAddBtn.addEventListener('click', closeModal);

    window.addEventListener('click', (e) => {
        if (e.target === addModal) closeModal();
    });

    // Handle AJAX Form Submit
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message || 'Appointment requested successfully!');
                    closeModal();
                    fetchPatientAppointments();
                } else {
                    alert(data.message || 'Failed to request appointment.');
                }
            })
            .catch(err => {
                console.error('Error submitting form:', err);
                alert('An error occurred while scheduling your appointment.');
            });
        });
    }

    // Initial Load
    fetchPatientAppointments();
});