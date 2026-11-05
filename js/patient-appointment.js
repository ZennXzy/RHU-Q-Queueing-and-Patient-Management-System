
document.addEventListener('DOMContentLoaded', () => {
    const addBtn = document.getElementById('addBtn');
    const addModal = document.getElementById('addAppointmentModal');
    const closeAddModal = document.getElementById('closeAddModal');
    const cancelAddBtn = document.getElementById('cancelAddBtn');
    const addForm = document.getElementById('addAppointmentForm');
    const appointmentList = document.getElementById('patientAppointmentsList');
    const appointmentCount = document.getElementById('patientAppCount');

    let isQueueing = false;
    let isRemoving = false;

    async function fetchPatientAppointments() {
        if (isQueueing || isRemoving) return;

        try {
            const res = await fetch('../php/patient_appointment_list.php', {
                cache: 'no-store'
            });

            if (!res.ok) throw new Error('Failed to fetch appointments.');

            const data = await res.json();

            if (data.status === 'success') {
                appointmentList.innerHTML = data.html;
                appointmentCount.innerText = data.total;
            }
        } catch (err) {
            console.error('Error fetching appointments:', err);
        }
    }

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

    window.addEventListener('click', e => {
        if (e.target === addModal) closeModal();
    });

    if (addForm) {
        addForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            try {
                const res = await fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this)
                });

                const data = await res.json();

                if (data.status === 'success') {
                    alert(data.message || 'Appointment requested successfully!');
                    closeModal();
                    await fetchPatientAppointments();
                } else {
                    alert(data.message || 'Failed to request appointment.');
                }
            } catch (err) {
                console.error('Error submitting form:', err);
                alert('An error occurred while scheduling your appointment.');
            }
        });
    }

    if (appointmentList) {
        appointmentList.addEventListener('click', async e => {
            const removeButton = e.target.closest('.btn-patient-remove');
            const queueButton = e.target.closest('.btn-patient-queue');

            if (isQueueing || isRemoving) return;

            if (removeButton && !removeButton.disabled) {
                const appointmentId = removeButton.dataset.appointmentId;
                if (!appointmentId) return;

                if (!confirm('Are you sure you want to permanently remove this appointment? This action cannot be undone.')) {
                    return;
                }

                isRemoving = true;
                removeButton.disabled = true;
                removeButton.textContent = 'Removing...';

                try {
                    const formData = new FormData();
                    formData.append('appointment_id', appointmentId);

                    const res = await fetch('../php/patient_remove_appointment.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await res.json();

                    if (data.status !== 'success') {
                        throw new Error(data.message || 'Failed to remove appointment.');
                    }

                    alert(data.message || 'Appointment removed successfully.');
                } catch (err) {
                    console.error('Appointment removal error:', err);
                    alert(err.message || 'Failed to remove appointment.');
                } finally {
                    isRemoving = false;
                    await fetchPatientAppointments();
                }

                return;
            }

            if (!queueButton || queueButton.disabled) return;

            const appointmentId = queueButton.dataset.appointmentId;
            if (!appointmentId) return;

            if (!confirm('Do you want to register yourself in the queue for this appointment?')) {
                return;
            }

            isQueueing = true;
            queueButton.disabled = true;
            queueButton.textContent = 'Registering...';

            try {
                const formData = new FormData();
                formData.append('appointment_id', appointmentId);

                const res = await fetch('../php/patient_queue_appointment.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (data.status !== 'success') {
                    throw new Error(data.message || 'Unable to register in queue.');
                }

                alert(`Successfully added to queue!\nYour Queue Number: ${data.queue_code}`);
            } catch (err) {
                console.error('Queue registration error:', err);
                alert(err.message || 'Queue registration failed.');
            } finally {
                isQueueing = false;
                await fetchPatientAppointments();
            }
        });
    }

    fetchPatientAppointments();

    setInterval(() => {
        if (!document.hidden && !isQueueing && !isRemoving) {
            fetchPatientAppointments();
        }
    }, 3000);
});
