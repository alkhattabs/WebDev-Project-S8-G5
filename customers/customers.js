document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('customerModal');
    const addCustomerBtn = document.getElementById('addCustomerBtn');
    const closeBtn = document.querySelector('.close');
    const customerForm = document.getElementById('customerForm');
    const searchInput = document.getElementById('searchInput');
    const cancelBtn = document.querySelector('.cancel-btn');

    // Form fields
    const customerIdInput = document.getElementById('customerId');
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    const ageInput = document.getElementById('age');
    const phoneNumberInput = document.getElementById('phoneNumber');
    const emailInput = document.getElementById('email');

    // Open modal for adding new customer
    addCustomerBtn.addEventListener('click', () => {
        openModal('add');
    });

    // Close modal when clicking the close button or cancel button
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Handle form submission
    customerForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Basic validation
        if (!firstNameInput.value.trim()) {
            alert('Please enter first name');
            return;
        }
        if (!lastNameInput.value.trim()) {
            alert('Please enter last name');
            return;
        }
        if (!phoneNumberInput.value.trim()) {
            alert('Please enter phone number');
            return;
        }
        if (emailInput.value && !isValidEmail(emailInput.value)) {
            alert('Please enter a valid email address');
            return;
        }

        const formData = new FormData();
        formData.append('firstName', firstNameInput.value.trim());
        formData.append('lastName', lastNameInput.value.trim());
        formData.append('age', ageInput.value || '');
        formData.append('phoneNumber', phoneNumberInput.value.trim());
        formData.append('email', emailInput.value.trim() || '');

        if (customerIdInput.value) {
            formData.append('customerId', customerIdInput.value);
        }

        try {
            const isEdit = customerIdInput.value !== '';
            const url = isEdit ? 'api/update_customer.php' : 'api/add_customer.php';

            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const result = await response.json();
            if (result.success) {
                closeModal();
                location.reload(); // Refresh the page to show updated data
            } else {
                throw new Error(result.message || 'Failed to save customer');
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message);
        }
    });

    // Handle search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const firstName = row.children[1].textContent.toLowerCase();
            const lastName = row.children[2].textContent.toLowerCase();
            const phone = row.children[4].textContent.toLowerCase();
            const email = row.children[5].textContent.toLowerCase();
            
            const matches = firstName.includes(searchTerm) || 
                          lastName.includes(searchTerm) || 
                          phone.includes(searchTerm) || 
                          email.includes(searchTerm);
            
            row.style.display = matches ? '' : 'none';
        });
    });
});

// Helper functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Modal control functions
function openModal(mode = 'add', customer = null) {
    const modal = document.getElementById('customerModal');
    const modalTitle = document.getElementById('modalTitle');
    const customerForm = document.getElementById('customerForm');
    const customerIdInput = document.getElementById('customerId');
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    const ageInput = document.getElementById('age');
    const phoneNumberInput = document.getElementById('phoneNumber');
    const emailInput = document.getElementById('email');

    modalTitle.textContent = mode === 'add' ? 'Add New Customer' : 'Edit Customer';
    if (customer) {
        customerIdInput.value = customer.id || '';
        firstNameInput.value = customer.first_name || '';
        lastNameInput.value = customer.last_name || '';
        ageInput.value = customer.age || '';
        phoneNumberInput.value = customer.phone_number || '';
        emailInput.value = customer.email || '';
    } else {
        customerForm.reset();
        customerIdInput.value = '';
    }
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('customerModal');
    modal.style.display = 'none';
    document.getElementById('customerForm').reset();
}

// Function to edit customer
function editCustomer(customerId) {
    fetch(`api/get_customer.php?id=${customerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openModal('edit', {
                    id: data.customer.customer_id,
                    first_name: data.customer.first_name,
                    last_name: data.customer.last_name,
                    age: data.customer.age,
                    phone_number: data.customer.phone_number,
                    email: data.customer.email
                });
            } else {
                alert('Error fetching customer details: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error fetching customer details: ' + error.message);
        });
}

// Function to delete customer
function deleteCustomer(customerId) {
    if (confirm('Are you sure you want to delete this customer?')) {
        fetch(`api/delete_customer.php?id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting customer: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error deleting customer: ' + error.message);
            });
    }
} 