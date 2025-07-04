document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const searchInput = document.getElementById('searchInput');
    const addStaffBtn = document.getElementById('addStaffBtn');
    const staffModal = document.getElementById('staffModal');
    const staffForm = document.getElementById('staffForm');
    const modalTitle = document.getElementById('modalTitle');

    // Event Listeners
    searchInput.addEventListener('input', filterStaff);
    addStaffBtn.addEventListener('click', () => openModal());
    staffForm.addEventListener('submit', handleSubmit);

    // Filter staff based on search input
    function filterStaff() {
        const searchTerm = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const text = Array.from(row.children)
                .map(cell => cell.textContent.toLowerCase())
                .join(' ');
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }

    // Open modal for add/edit
    function openModal(staffId = null) {
        modalTitle.textContent = staffId ? 'Edit Staff' : 'Add New Staff';
        staffForm.reset();
        document.getElementById('staffId').value = staffId || '';
        
        if (staffId) {
            // Fetch staff data and populate form
            fetch(`api/get_staff.php?id=${staffId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const staff = data.staff;
                        document.getElementById('firstName').value = staff.first_name;
                        document.getElementById('lastName').value = staff.last_name;
                        document.getElementById('age').value = staff.age;
                        document.getElementById('jobTitle').value = staff.job_title;
                        document.getElementById('phoneNumber').value = staff.phone_number;
                        document.getElementById('address').value = staff.address;
                        document.getElementById('email').value = staff.email;
                        document.getElementById('salary').value = staff.salary;
                        // Clear password field for edit mode
                        document.getElementById('password').value = '';
                        document.getElementById('password').required = false;
                    } else {
                        alert('Error fetching staff data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching staff data');
                });
        } else {
            // Set password as required for new staff
            document.getElementById('password').required = true;
        }

        staffModal.style.display = 'block';
    }

    // Close modal
    window.closeModal = function() {
        staffModal.style.display = 'none';
    }

    // Handle form submission
    async function handleSubmit(e) {
        e.preventDefault();

        const formData = new FormData(staffForm);
        const staffId = formData.get('staff_id');
        const endpoint = staffId ? 'api/update_staff.php' : 'api/add_staff.php';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert(staffId ? 'Staff updated successfully!' : 'Staff added successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while saving staff data');
        }
    }

    // Function to delete staff
    window.deleteStaff = function(staffId) {
        if (confirm('Are you sure you want to delete this staff member?')) {
            // First, try to transfer orders to another staff member
            fetch('api/transfer_orders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    from_staff_id: staffId,
                    to_staff_id: window.currentStaffId // Transfer to current logged-in staff
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // After successful transfer, proceed with deletion
                    fetch('api/delete_staff.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ staff_id: staffId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Staff deleted successfully');
                            location.reload();
                        } else {
                            alert('Error deleting staff: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error deleting staff. Please try again.');
                    });
                } else {
                    alert('Error transferring orders: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error transferring orders. Please try again.');
            });
        }
    }

    // Edit staff
    window.editStaff = function(staffId) {
        openModal(staffId);
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === staffModal) {
            closeModal();
        }
    }
}); 