document.addEventListener('DOMContentLoaded', function() {
    // Initialize search and filter functionality
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const typeFilter = document.getElementById('typeFilter');
    const form = document.getElementById('editOrderForm');
    const orderType = document.getElementById('orderType');
    const tableSelect = document.getElementById('tableSelect');
    const tableGroup = document.getElementById('tableGroup');
    const customerTypeButtons = document.querySelectorAll('.customer-type-toggle button');
    const guestInput = document.querySelector('.guest-input');
    const registeredInput = document.querySelector('.registered-input');

    // Add event listeners for search and filters
    if (searchInput && statusFilter && typeFilter) {
        searchInput.addEventListener('input', filterOrders);
        statusFilter.addEventListener('change', filterOrders);
        typeFilter.addEventListener('change', filterOrders);
    }

    // Filter orders based on search input and selected filters
    function filterOrders() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const typeValue = typeFilter.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const orderData = {
                orderId: row.cells[0].textContent.toLowerCase(),
                date: row.cells[1].textContent.toLowerCase(),
                type: row.cells[2].textContent.toLowerCase(),
                table: row.cells[3].textContent.toLowerCase(),
                customer: row.cells[4].textContent.toLowerCase(),
                staff: row.cells[5].textContent.toLowerCase(),
                status: row.cells[8].textContent.trim().toLowerCase()
            };

            const matchesSearch = Object.values(orderData).some(value => 
                value.includes(searchTerm)
            );

            const matchesStatus = !statusValue || orderData.status === statusValue;
            const matchesType = !typeValue || orderData.type === typeValue;

            row.style.display = (matchesSearch && matchesStatus && matchesType) ? '' : 'none';
        });
    }

    // Function to update quantity
    window.updateQuantity = function(button, change) {
        const row = button.closest('tr');
        const quantitySpan = row.querySelector('.quantity');
        let quantity = parseInt(quantitySpan.textContent);
        
        quantity += change;
        if (quantity < 1) quantity = 1;
        
        quantitySpan.textContent = quantity;
        updateSubtotal(row);
    };

    // Function to remove item
    window.removeItem = function(button) {
        if (confirm('Are you sure you want to remove this item?')) {
            const row = button.closest('tr');
            row.remove();
            calculateTotal();
        }
    };

    // Function to update subtotal
    function updateSubtotal(row) {
        const quantity = parseInt(row.querySelector('.quantity').textContent);
        const unitPrice = parseFloat(row.querySelector('td:nth-child(3)').textContent.replace('RM ', ''));
        const subtotal = (quantity * unitPrice).toFixed(2);
        row.querySelector('td:nth-child(4)').textContent = `RM ${subtotal}`;
        calculateTotal();
    }

    // Function to calculate total
    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.order-items-table tbody tr').forEach(row => {
            const subtotal = parseFloat(row.cells[3].textContent);
            total += subtotal;
        });
        document.getElementById('orderTotal').textContent = total.toFixed(2);
    }

    // Handle order type change
    if (orderType) {
        orderType.addEventListener('change', function() {
            const isDineIn = this.options[this.selectedIndex].text === 'Dine In';
            tableSelect.disabled = !isDineIn;
            if (!isDineIn) {
                tableSelect.value = '';
            }
            tableGroup.style.opacity = isDineIn ? '1' : '0.5';
        });
    }

    // Handle customer type toggle
    if (customerTypeButtons.length > 0) {
        customerTypeButtons.forEach(button => {
            button.addEventListener('click', function() {
                customerTypeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                if (this.dataset.type === 'guest') {
                    guestInput.style.display = 'block';
                    registeredInput.style.display = 'none';
                    document.getElementById('customerId').value = '';
                } else {
                    guestInput.style.display = 'none';
                    registeredInput.style.display = 'block';
                    document.getElementById('guestName').value = '';
                }
            });
        });
    }

    // Handle form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Create FormData object
            const formData = new FormData(this);
            
            // Add items data
            const items = Array.from(document.querySelectorAll('.order-items-table tbody tr')).map(row => ({
                item_id: row.dataset.itemId,
                quantity: parseInt(row.querySelector('.quantity').textContent)
            }));
            
            // Log the items data for debugging
            console.log('Items to update:', items);
            
            formData.append('items', JSON.stringify(items));
            
            // Send update request
            fetch('api/update_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Order updated successfully!');
                    window.location.reload();
                } else {
                    alert('Error updating order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the order');
            });
        });
    }

    // Modal functions
    window.closeItemsModal = function() {
        const modal = document.getElementById('itemsModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    window.closeEditModal = function() {
        const modal = document.getElementById('editModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    window.viewItems = function(orderId) {
        const modal = document.getElementById('itemsModal');
        const modalContent = document.getElementById('itemsList');
        modalContent.innerHTML = '<div class="loading">Loading...</div>';
        modal.style.display = 'block';

        fetch(`api/get_order_items.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load items');
                }
                
                let html = '<table><thead><tr><th>Item</th><th>Quantity</th><th>Price (RM)</th><th>Subtotal (RM)</th></tr></thead><tbody>';
                
                data.items.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.quantity}</td>
                            <td>${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table>';
                modalContent.innerHTML = html;
            })
            .catch(error => {
                modalContent.innerHTML = '<div class="error">Error loading items: ' + error.message + '</div>';
                console.error('Error:', error);
            });
    };

    // Close modals when clicking outside
    window.onclick = function(event) {
        const itemsModal = document.getElementById('itemsModal');
        const editModal = document.getElementById('editModal');
        const receiptModal = document.getElementById('receiptModal');
        if (event.target === itemsModal) {
            closeItemsModal();
        }
        if (event.target === editModal) {
            closeEditModal();
        }
        if (event.target === receiptModal) {
            closeReceiptModal();
        }
    };

    // Close buttons for modals
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.onclick = function() {
            const modal = this.closest('.modal');
            if (modal.id === 'itemsModal') {
                closeItemsModal();
            } else if (modal.id === 'editModal') {
                closeEditModal();
            } else if (modal.id === 'receiptModal') {
                closeReceiptModal();
            }
        };
    });

    // Cancel/Close buttons for modals
    const cancelButtons = document.querySelectorAll('.cancel-btn, .close-btn');
    cancelButtons.forEach(button => {
        button.onclick = function() {
            const modal = this.closest('.modal');
            if (modal.id === 'itemsModal') {
                closeItemsModal();
            } else if (modal.id === 'editModal') {
                closeEditModal();
            } else if (modal.id === 'receiptModal') {
                closeReceiptModal();
            }
        };
    });

    // Update status markers when slider changes
    const statusSlider = document.getElementById('statusSlider');
    if (statusSlider) {
        statusSlider.addEventListener('input', function() {
            updateStatusMarkers(this.value);
        });
    }

    function updateStatusMarkers(value) {
        const markers = document.querySelectorAll('.status-marker');
        markers.forEach((marker, index) => {
            marker.classList.toggle('active', index + 1 <= value);
        });
    }

    // Function to edit order
    window.editOrder = function(orderId) {
        // Show the modal
        const modal = document.getElementById('editModal');
        modal.style.display = 'block';
        
        // Set the order ID
        document.getElementById('editOrderId').value = orderId;
        document.getElementById('editOrderNumber').textContent = orderId;
        
        // Fetch order details
        fetch(`api/get_order_details.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const order = data.order;
                    
                    // Set form values
                    document.getElementById('orderType').value = order.order_type_id;
                    document.getElementById('tableSelect').value = order.table_id || '';
                    document.getElementById('orderStatus').value = order.order_status_id;
                    document.getElementById('orderDate').value = new Date(order.order_date).toLocaleString();
                    document.getElementById('notes').value = order.notes || '';

                    // Set customer information
                    if (order.customer_id) {
                        document.querySelector('[data-type="registered"]').click();
                        document.getElementById('customerId').value = order.customer_id;
                    } else {
                        document.querySelector('[data-type="guest"]').click();
                        document.getElementById('guestName').value = order.customer_name || '';
                    }
                    
                    // Handle table select visibility based on order type
                    const tableGroup = document.getElementById('tableGroup');
                    const tableSelect = document.getElementById('tableSelect');
                    if (order.order_type_id === 2) { // Take Away
                        tableGroup.style.display = 'none';
                        tableSelect.disabled = true;
                    } else {
                        tableGroup.style.display = 'block';
                        tableSelect.disabled = false;
                    }
                    
                    // Display order items
                    displayOrderItems(data.items);
                } else {
                    alert('Error loading order details: ' + data.message);
                    closeEditModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while loading order details');
                closeEditModal();
            });
    };

    // Function to display order items
    function displayOrderItems(items) {
        const itemsList = document.getElementById('orderItemsList');
        itemsList.innerHTML = `
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Unit Price (RM)</th>
                        <th>Subtotal (RM)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${items.map(item => `
                        <tr data-item-id="${item.item_id}">
                            <td>${item.name}</td>
                            <td>
                                <div class="quantity-control">
                                    <button type="button" onclick="updateItemQuantity(this, -1)">-</button>
                                    <span class="quantity">${item.quantity}</span>
                                    <button type="button" onclick="updateItemQuantity(this, 1)">+</button>
                                </div>
                            </td>
                            <td>${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>${parseFloat(item.subtotal).toFixed(2)}</td>
                            <td>
                                <button type="button" class="remove-btn" onclick="removeOrderItem(this)">Remove</button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        calculateTotal();
    }

    // Function to update item quantity
    window.updateItemQuantity = function(button, change) {
        const row = button.closest('tr');
        const quantitySpan = row.querySelector('.quantity');
        let quantity = parseInt(quantitySpan.textContent) + change;
        
        if (quantity < 1) quantity = 1;
        quantitySpan.textContent = quantity;
        
        const unitPrice = parseFloat(row.cells[2].textContent);
        const subtotal = (quantity * unitPrice).toFixed(2);
        row.cells[3].textContent = subtotal;
        
        calculateTotal();
    };

    // Function to remove order item
    window.removeOrderItem = function(button) {
        if (confirm('Are you sure you want to remove this item?')) {
            const row = button.closest('tr');
            row.remove();
            calculateTotal();
        }
    };

    // Function to show add item modal
    window.showAddItemModal = function() {
        const modal = document.getElementById('addItemModal');
        modal.style.display = 'block';
        document.getElementById('itemQuantity').value = 1;
        document.getElementById('menuItem').value = '';
    };

    // Function to close add item modal
    window.closeAddItemModal = function() {
        const modal = document.getElementById('addItemModal');
        modal.style.display = 'none';
    };

    // Function to adjust quantity in add item modal
    window.adjustQuantity = function(change) {
        const input = document.getElementById('itemQuantity');
        let value = parseInt(input.value) + change;
        if (value < 1) value = 1;
        input.value = value;
    };

    // Function to add item to order
    window.addItemToOrder = function() {
        const menuItem = document.getElementById('menuItem');
        const quantity = parseInt(document.getElementById('itemQuantity').value);
        
        if (!menuItem.value) {
            alert('Please select an item');
            return;
        }
        
        const selectedOption = menuItem.options[menuItem.selectedIndex];
        const itemId = menuItem.value;
        const itemName = selectedOption.text.split(' (RM ')[0];
        const price = parseFloat(selectedOption.dataset.price);
        const subtotal = (price * quantity).toFixed(2);
        
        const itemsList = document.getElementById('orderItemsList');
        if (!itemsList.querySelector('table')) {
            itemsList.innerHTML = `
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit Price (RM)</th>
                            <th>Subtotal (RM)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            `;
        }
        
        // Check if item already exists
        const existingRow = itemsList.querySelector(`tr[data-item-id="${itemId}"]`);
        if (existingRow) {
            const currentQuantity = parseInt(existingRow.querySelector('.quantity').textContent);
            const newQuantity = currentQuantity + quantity;
            existingRow.querySelector('.quantity').textContent = newQuantity;
            existingRow.cells[3].textContent = (price * newQuantity).toFixed(2);
        } else {
            const tbody = itemsList.querySelector('tbody');
            const newRow = document.createElement('tr');
            newRow.dataset.itemId = itemId;
            newRow.innerHTML = `
                <td>${itemName}</td>
                <td>
                    <div class="quantity-control">
                        <button type="button" onclick="updateItemQuantity(this, -1)">-</button>
                        <span class="quantity">${quantity}</span>
                        <button type="button" onclick="updateItemQuantity(this, 1)">+</button>
                    </div>
                </td>
                <td>${price.toFixed(2)}</td>
                <td>${subtotal}</td>
                <td>
                    <button type="button" class="remove-btn" onclick="removeOrderItem(this)">Remove</button>
                </td>
            `;
            tbody.appendChild(newRow);
        }
        
        calculateTotal();
        closeAddItemModal();
    };

    // Handle order type change in edit form
    document.getElementById('orderType').addEventListener('change', function() {
        const tableGroup = document.getElementById('tableGroup');
        const tableSelect = document.getElementById('tableSelect');
        
        if (this.value === '2') { // Take Away
            tableGroup.style.display = 'none';
            tableSelect.disabled = true;
            tableSelect.value = '';
        } else { // Dine In
            tableGroup.style.display = 'block';
            tableSelect.disabled = false;
        }
    });

    // Function to view receipt
    window.viewReceipt = function(orderId) {
        const modal = document.getElementById('receiptModal');
        const receiptContent = document.getElementById('receiptContent');
        receiptContent.innerHTML = '<div class="loading">Loading...</div>';
        modal.style.display = 'block';

        fetch(`api/get_receipt.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load receipt');
                }

                const { receipt, items } = data;
                const orderDate = new Date(receipt.order_date).toLocaleString();
                
                let html = `
                    <div class="receipt-header">
                        <h3>Bandar Hadhramaut Restaurant</h3>
                        <p>Order Receipt</p>
                    </div>
                    <div class="receipt-details">
                        <p><strong>Order ID:</strong> ${receipt.order_id}</p>
                        <p><strong>Date:</strong> ${orderDate}</p>
                        <p><strong>Type:</strong> ${receipt.type_name}</p>
                        ${receipt.table_number ? `<p><strong>Table:</strong> ${receipt.table_number}</p>` : ''}
                        ${receipt.customer_name ? `<p><strong>Customer:</strong> ${receipt.customer_name}</p>` : ''}
                        ${receipt.staff_name ? `<p><strong>Staff:</strong> ${receipt.staff_name}</p>` : ''}
                    </div>
                    <div class="receipt-items">
                        <table>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                items.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.name}</td>
                            <td>${item.quantity}</td>
                            <td>RM ${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td>RM ${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                                    <td><strong>RM ${parseFloat(receipt.total_amount).toFixed(2)}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="receipt-footer">
                        <p>Thank you for dining with us!</p>
                    </div>
                `;

                receiptContent.innerHTML = html;
            })
            .catch(error => {
                receiptContent.innerHTML = '<div class="error">Error loading receipt: ' + error.message + '</div>';
                console.error('Error:', error);
            });
    };

    // Function to print receipt
    window.printReceipt = function() {
        const receiptContent = document.getElementById('receiptContent').innerHTML;
        const printWindow = window.open('', '_blank');
        
        printWindow.document.write(`
            <html>
                <head>
                    <title>Order Receipt</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 20px;
                            color: #333;
                        }
                        .receipt-header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .receipt-header h3 {
                            margin: 0;
                            color: #8B0000;
                        }
                        .receipt-details {
                            margin-bottom: 20px;
                        }
                        .receipt-details p {
                            margin: 5px 0;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 20px;
                        }
                        th, td {
                            padding: 8px;
                            text-align: left;
                            border-bottom: 1px solid #ddd;
                        }
                        th {
                            background-color: #f8f8f8;
                        }
                        .receipt-footer {
                            text-align: center;
                            margin-top: 20px;
                            padding-top: 20px;
                            border-top: 1px solid #ddd;
                        }
                        @media print {
                            body {
                                margin: 0;
                                padding: 20px;
                            }
                        }
                    </style>
                </head>
                <body>
                    ${receiptContent}
                </body>
            </html>
        `);
        
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    };

    // Function to close receipt modal
    window.closeReceiptModal = function() {
        const modal = document.getElementById('receiptModal');
        modal.style.display = 'none';
        document.getElementById('receiptContent').innerHTML = ''; // Clear the content
    };

    // Close modal when pressing Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeReceiptModal();
        }
    });
}); 