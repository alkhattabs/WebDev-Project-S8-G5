// Global state
let currentOrder = {
    type: 'takeaway',
    tableId: null,
    items: {},
    total: 0
};

// Global functions
function updateQuantity(itemId, change) {
    const item = currentOrder.items[itemId];
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromOrder(itemId);
        } else {
            updateOrderDisplay();
        }
    }
}

function removeFromOrder(itemId) {
    delete currentOrder.items[itemId];
    updateOrderDisplay();
}

function updateOrderDisplay() {
    const orderItemsContainer = document.querySelector('.order-items');
    const totalElement = document.querySelector('.total span:last-child');
    const placeOrderBtn = document.querySelector('.place-order-btn');
    
    orderItemsContainer.innerHTML = '';
    let total = 0;

    for (const [itemId, item] of Object.entries(currentOrder.items)) {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;

        const itemElement = document.createElement('div');
        itemElement.className = 'order-item';
        itemElement.innerHTML = `
            <div class="item-info">
                <div class="item-name">${item.name}</div>
                <div class="item-price">RM ${item.price.toFixed(2)}</div>
            </div>
            <div class="item-quantity">
                <button type="button" class="quantity-btn" onclick="updateQuantity('${itemId}', -1)">-</button>
                <span>${item.quantity}</span>
                <button type="button" class="quantity-btn" onclick="updateQuantity('${itemId}', 1)">+</button>
            </div>
        `;
        orderItemsContainer.appendChild(itemElement);
    }

    currentOrder.total = total;
    totalElement.textContent = `RM ${total.toFixed(2)}`;
    if (placeOrderBtn) {
        placeOrderBtn.disabled = total === 0;
    }
}

// Global place order function
function placeOrder() {
    console.log('Place order function called');
    
    // Validate staff ID
    const staffId = document.getElementById('staffId')?.value;
    if (!staffId) {
        alert('Please select a staff member');
        console.log('Staff ID missing');
        return;
    }

    const orderType = document.querySelector('.order-type button.active')?.dataset.type;
    if (!orderType) {
        alert('Please select an order type');
        console.log('Order type missing');
        return;
    }

    const tableId = orderType === 'dinein' ? document.getElementById('tableSelect')?.value : null;
    if (orderType === 'dinein' && !tableId) {
        alert('Please select a table for dine-in orders');
        console.log('Table ID missing for dine-in order');
        return;
    }
    
    console.log('Order type:', orderType);
    console.log('Table ID:', tableId);
    console.log('Staff ID:', staffId);
    
    // Get customer information
    const customerTypeBtn = document.querySelector('.customer-type button.active');
    if (!customerTypeBtn) {
        alert('Please select customer type (Guest or Registered)');
        console.log('Customer type not selected');
        return;
    }

    const isGuest = customerTypeBtn.dataset.type === 'guest';
    let customerInfo = null;
    
    if (isGuest) {
        const guestName = document.getElementById('guestName')?.value.trim();
        if (!guestName) {
            alert('Please enter guest name');
            console.log('Guest name missing');
            return;
        }
        customerInfo = { isGuest: true, name: guestName };
    } else {
        const customerId = document.getElementById('customerId')?.value;
        if (!customerId) {
            alert('Please select a customer');
            console.log('Customer ID missing');
            return;
        }
        customerInfo = { isGuest: false, customerId: customerId };
    }
    
    console.log('Customer info:', customerInfo);

    // Validate order items
    if (Object.keys(currentOrder.items).length === 0) {
        alert('Please add items to the order');
        console.log('No items in order');
        return;
    }

    // Prepare order data
    const orderData = {
        orderType: orderType,
        tableId: tableId,
        staffId: staffId,
        customerInfo: customerInfo,
        items: currentOrder.items,
        total: currentOrder.total
    };
    
    console.log('Sending order data:', orderData);

    // Send order to server
    fetch('api/place_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => {
        console.log('Server response:', response);
        return response.json();
    })
    .then(data => {
        console.log('Parsed response data:', data);
        if (data.success) {
            alert('Order placed successfully!');
            // Clear order
            currentOrder = {
                type: currentOrder.type,
                tableId: null,
                items: {},
                total: 0
            };
            updateOrderDisplay();
            // Reset customer inputs
            document.getElementById('guestName').value = '';
            document.getElementById('customerId').value = '';
            // Reset table selection for dine-in
            if (currentOrder.type === 'dinein') {
                document.getElementById('tableSelect').value = '';
            }
        } else {
            alert('Error placing order: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error placing order. Please try again.');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const menuItems = document.querySelectorAll('.menu-item');
    const orderTypeButtons = document.querySelectorAll('.order-type button');
    const tableSelect = document.querySelector('.table-select');
    const placeOrderBtn = document.querySelector('.place-order-btn');
    const applySearchBtn = document.getElementById('applySearch');
    const resetSearchBtn = document.getElementById('resetSearch');

    // Initialize state from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const tableId = urlParams.get('table');
    const tableNumber = urlParams.get('table_number');
    currentOrder.type = window.initialOrderType || 'takeaway';
    currentOrder.tableId = tableId || null;

    // Initialize table selection if table ID is provided
    if (tableId && currentOrder.type === 'dinein') {
        const tableSelect = document.getElementById('tableSelect');
        tableSelect.value = tableId;
        
        if (!tableSelect.value && tableNumber) {
            const option = new Option(`Table ${tableNumber}`, tableId);
            tableSelect.add(option);
            tableSelect.value = tableId;
        }
        
        tableSelect.style.display = 'block';
        document.querySelector('.table-select').style.display = 'block';
        
        document.querySelector('.order-type button[data-type="dinein"]').classList.add('active');
        document.querySelector('.order-type button[data-type="takeaway"]').classList.remove('active');
    }

    // Event Listeners
    applySearchBtn.addEventListener('click', filterMenuItems);
    resetSearchBtn.addEventListener('click', resetSearch);
    categoryFilter.addEventListener('change', filterMenuItems);

    // Enter key in search input triggers search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            filterMenuItems();
        }
    });

    // Order type toggle
    orderTypeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            orderTypeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentOrder.type = type;
            tableSelect.style.display = type === 'dinein' ? 'block' : 'none';
            if (type === 'takeaway') {
                currentOrder.tableId = null;
                document.getElementById('tableSelect').value = '';
            }
            const newUrl = new URL(window.location.href);
            if (type === 'takeaway') {
                newUrl.searchParams.set('type', 'takeaway');
                newUrl.searchParams.delete('table');
                newUrl.searchParams.delete('table_number');
            } else {
                newUrl.searchParams.delete('type');
            }
            window.history.pushState({}, '', newUrl);
        });
    });

    // Table selection
    document.getElementById('tableSelect').addEventListener('change', function() {
        currentOrder.tableId = this.value;
    });

    // Menu item selection
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            const itemId = this.dataset.id;
            const itemName = this.querySelector('h3').textContent;
            const itemPrice = parseFloat(this.querySelector('.price').textContent.replace('RM ', ''));
            addToOrder(itemId, itemName, itemPrice);
        });
    });

    // Customer type switching
    const customerTypeButtons = document.querySelectorAll('.customer-type button');
    const guestInput = document.querySelector('.guest-input');
    const registeredInput = document.querySelector('.registered-input');

    customerTypeButtons.forEach(button => {
        button.addEventListener('click', () => {
            customerTypeButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            if (button.dataset.type === 'guest') {
                guestInput.style.display = 'block';
                registeredInput.style.display = 'none';
            } else {
                guestInput.style.display = 'none';
                registeredInput.style.display = 'block';
            }
        });
    });

    // Add event listener for place order button
    if (placeOrderBtn) {
        console.log('Adding click event listener to place order button');
        placeOrderBtn.addEventListener('click', placeOrder);
    } else {
        console.error('Place order button not found');
    }

    function addToOrder(itemId, itemName, itemPrice) {
        if (currentOrder.items[itemId]) {
            currentOrder.items[itemId].quantity++;
        } else {
            currentOrder.items[itemId] = {
                name: itemName,
                price: itemPrice,
                quantity: 1
            };
        }
        updateOrderDisplay();
    }

    function filterMenuItems() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value.toLowerCase();

        menuItems.forEach(item => {
            const itemName = item.querySelector('h3').textContent.toLowerCase();
            const itemCategory = item.dataset.category.toLowerCase();
            const matchesSearch = itemName.includes(searchTerm);
            const matchesCategory = !selectedCategory || itemCategory === selectedCategory;

            item.style.display = matchesSearch && matchesCategory ? 'block' : 'none';
        });
    }

    function resetSearch() {
        searchInput.value = '';
        categoryFilter.value = '';
        menuItems.forEach(item => {
            item.style.display = 'block';
        });
    }
}); 