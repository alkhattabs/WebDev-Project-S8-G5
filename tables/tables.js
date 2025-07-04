document.addEventListener('DOMContentLoaded', function() {
    const tables = document.querySelectorAll('.table-item');
    const selectButton = document.getElementById('selectTableBtn');
    let selectedTable = null;

    // Edit table functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-table-btn')) {
            e.stopPropagation(); // Prevent table selection when clicking edit
            
            const tableItem = e.target.closest('.table-item');
            const tableId = tableItem.dataset.tableId;
            const tableNumber = tableItem.querySelector('.table-number').textContent;
            const capacity = parseInt(tableItem.querySelector('.table-capacity').textContent);

            // Set values in edit modal
            document.getElementById('editTableId').value = tableId;
            document.getElementById('editTableNumber').value = tableNumber;
            document.getElementById('editCapacity').value = capacity;

            // Show edit modal
            document.getElementById('editTableModal').style.display = 'block';
        }
    });

    // Edit Table Modal Elements
    const editTableModal = document.getElementById('editTableModal');
    const editCloseBtn = editTableModal.querySelector('.close');
    const editCancelBtn = editTableModal.querySelector('.cancel-btn');
    const editTableForm = document.getElementById('editTableForm');

    // Close edit modal functions
    function closeEditModal() {
        editTableModal.style.display = 'none';
        editTableForm.reset();
    }

    editCloseBtn.addEventListener('click', closeEditModal);
    editCancelBtn.addEventListener('click', closeEditModal);

    // Close edit modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === editTableModal) {
            closeEditModal();
        }
    });

    // Handle edit form submission
    editTableForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(editTableForm);
        
        try {
            const response = await fetch('api/update_table.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Update capacity display
                const tableId = formData.get('table_id');
                const tableItem = document.querySelector(`.table-item[data-table-id="${tableId}"]`);
                const capacitySpan = tableItem.querySelector('.table-capacity');
                capacitySpan.textContent = `${data.table.capacity} seats`;

                // Show success message
                alert('Table updated successfully!');
                closeEditModal();
            } else {
                alert(data.message || 'Failed to update table');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating the table');
        }
    });

    // Delete table functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-table-btn')) {
            e.stopPropagation(); // Prevent table selection when clicking delete
            
            const tableItem = e.target.closest('.table-item');
            const tableId = tableItem.dataset.tableId;
            const tableNumber = tableItem.querySelector('.table-number').textContent;

            if (confirm(`Are you sure you want to delete Table ${tableNumber}?`)) {
                const formData = new FormData();
                formData.append('table_id', tableId);

                fetch('api/delete_table.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        tableItem.remove();
                        alert('Table deleted successfully');
                    } else {
                        alert(data.message || 'Failed to delete table');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the table');
                });
            }
        }
    });

    tables.forEach(table => {
        table.addEventListener('click', function() {
            // Don't allow selecting occupied tables
            if (this.classList.contains('occupied')) {
                return;
            }

            // Remove selection from previously selected table
            if (selectedTable) {
                selectedTable.classList.remove('selected');
            }

            // Select the new table
            this.classList.add('selected');
            selectedTable = this;
            
            // Enable the select button
            selectButton.disabled = false;
        });
    });

    selectButton.addEventListener('click', function() {
        if (selectedTable) {
            const tableNumber = selectedTable.querySelector('.table-number').textContent;
            const tableId = selectedTable.dataset.tableId;
            // Redirect to order page with table info and set as dine-in
            window.location.href = `../orders/?type=dinein&table=${tableId}&table_number=${tableNumber}`;
        }
    });

    // Add Table Modal Elements
    const addTableBtn = document.getElementById('addTableBtn');
    const addTableModal = document.getElementById('addTableModal');
    const closeBtn = addTableModal.querySelector('.close');
    const cancelBtn = addTableModal.querySelector('.cancel-btn');
    const addTableForm = document.getElementById('addTableForm');

    // Open modal
    addTableBtn.addEventListener('click', function() {
        addTableModal.style.display = 'block';
        document.getElementById('tableNumber').focus();
    });

    // Close modal functions
    function closeModal() {
        addTableModal.style.display = 'none';
        addTableForm.reset();
    }

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === addTableModal) {
            closeModal();
        }
    });

    // Handle form submission
    addTableForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(addTableForm);
        
        try {
            const response = await fetch('api/add_table.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Add new table to the grid
                const tablesGrid = document.querySelector('.tables-grid');
                const newTableDiv = document.createElement('div');
                newTableDiv.className = 'table-item available';
                newTableDiv.setAttribute('data-table-id', data.table.table_id);
                
                newTableDiv.innerHTML = `
                    <div class="table-icon">
                        <span class="table-number">${data.table.table_number}</span>
                        <span class="table-capacity">${data.table.capacity} seats</span>
                        <div class="table-actions">
                            <button class="edit-table-btn" title="Edit Table">✎</button>
                            <button class="delete-table-btn" title="Delete Table">×</button>
                        </div>
                    </div>
                `;

                tablesGrid.appendChild(newTableDiv);

                // Add click event listener to the new table
                newTableDiv.addEventListener('click', function() {
                    const allTables = document.querySelectorAll('.table-item');
                    allTables.forEach(table => table.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('selectTableBtn').disabled = false;
                });

                // Show success message
                alert('Table added successfully!');
                closeModal();

                // Reload the page to ensure proper ordering
                window.location.reload();
            } else {
                alert(data.message || 'Failed to add table');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while adding the table');
        }
    });
}); 