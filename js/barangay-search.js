document.addEventListener("DOMContentLoaded", () => {
    let barangayList = [];

    // Fetch barangays from API once
    async function fetchBarangays() {
        try {
            const response = await fetch('../api/get_barangay.php');
            const data = await response.json();
            if (data.status === 'success') {
                barangayList = data.barangays;
            }
        } catch (error) {
            console.error('Error fetching barangays:', error);
        }
    }

    fetchBarangays();

    // Function to initialize autocomplete on any given address input field
    function attachBarangayAutocomplete(inputElement) {
        if (!inputElement) return;

        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        inputElement.parentNode.insertBefore(wrapper, inputElement);
        wrapper.appendChild(inputElement);

        const suggestionBox = document.createElement('div');
        suggestionBox.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 180px;
            overflow-y: auto;
            z-index: 1050;
            display: none;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        `;
        wrapper.appendChild(suggestionBox);

        inputElement.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            suggestionBox.innerHTML = '';

            if (!query) {
                suggestionBox.style.display = 'none';
                return;
            }

            const filtered = barangayList.filter(b => b.toLowerCase().includes(query));

            if (filtered.length > 0) {
                suggestionBox.style.display = 'block';
                filtered.forEach(item => {
                    const div = document.createElement('div');
                    div.textContent = item;
                    div.style.cssText = `
                        padding: 8px 12px;
                        cursor: pointer;
                        font-size: 0.9rem;
                        border-bottom: 1px solid #f1f5f9;
                    `;
                    div.addEventListener('mouseenter', () => div.style.backgroundColor = '#f8fafc');
                    div.addEventListener('mouseleave', () => div.style.backgroundColor = '#fff');
                    div.addEventListener('click', () => {
                        inputElement.value = item;
                        suggestionBox.style.display = 'none';
                        inputElement.dispatchEvent(new Event('input'));
                    });
                    suggestionBox.appendChild(div);
                });
            } else {
                suggestionBox.style.display = 'none';
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                suggestionBox.style.display = 'none';
            }
        });
    }

    // Attach autocomplete to both registration and edit patient address fields if present
    const regAddress = document.getElementById('address');
    if (regAddress) attachBarangayAutocomplete(regAddress);

    const editAddress = document.getElementById('editAddress');
    if (editAddress) attachBarangayAutocomplete(editAddress);
});