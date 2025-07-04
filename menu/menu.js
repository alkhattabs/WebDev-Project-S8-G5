// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded');
    
    // Check if elements exist
    const addPopup = document.getElementById('addMenuPopup');
    const editPopup = document.getElementById('editMenuPopup');
    const addForm = document.getElementById('addMenuForm');
    const editForm = document.getElementById('editMenuForm');
    
    console.log('Add popup exists:', !!addPopup);
    console.log('Edit popup exists:', !!editPopup);
    console.log('Add form exists:', !!addForm);
    console.log('Edit form exists:', !!editForm);
    
    // Add click event listeners to all edit buttons
    const editButtons = document.querySelectorAll('.edit-btn');
    console.log('Number of edit buttons found:', editButtons.length);
    
    editButtons.forEach((button, index) => {
        console.log(`Edit button ${index} onclick:`, button.onclick);
    });
});

// Popup Functions
function openAddPopup() {
    console.log('Opening add popup');
    document.getElementById('addMenuPopup').style.display = 'block';
    document.getElementById('addMenuForm').reset();
}

function closeAddPopup() {
    console.log('Closing add popup');
    document.getElementById('addMenuPopup').style.display = 'none';
}

function openEditPopup(item) {
    console.log('Opening edit popup with item:', item);
    const popup = document.getElementById('editMenuPopup');
    console.log('Edit popup element:', popup);
    popup.style.display = 'block';
    
    // Fill the form with item data
    document.getElementById('edit_item_id').value = item.item_id;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_category_id').value = item.category_id;
    document.getElementById('edit_price').value = item.price;
    document.getElementById('edit_is_available').checked = item.is_available == 1;

    // Show current image
    const imagePreview = document.getElementById('current_image_preview');
    if (item.image_path) {
        imagePreview.innerHTML = `<img src="../${item.image_path}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;">`;
    } else {
        imagePreview.innerHTML = `<img src="../images/default-food.jpg" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;">`;
    }
    console.log('Edit popup opened and form filled');
}

function closeEditPopup() {
    console.log('Closing edit popup');
    document.getElementById('editMenuPopup').style.display = 'none';
}

// Close popups when clicking outside
window.onclick = function(event) {
    const addPopup = document.getElementById('addMenuPopup');
    const editPopup = document.getElementById('editMenuPopup');
    if (event.target === addPopup) {
        closeAddPopup();
    }
    if (event.target === editPopup) {
        closeEditPopup();
    }
}

// Form Submission Handlers
document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.getElementById('addMenuForm');
    const editForm = document.getElementById('editMenuForm');
    
    if (addForm) {
        addForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('./api/add_menu_item.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message || 'Menu item added successfully');
                    location.reload();
                } else {
                    alert(data.message || 'Error adding menu item');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error adding menu item. Please try again.');
            }
        });
    }
    
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const itemId = document.getElementById('edit_item_id').value;
            
            try {
                const response = await fetch(`./api/edit_menu_item.php?id=${itemId}`, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message || 'Menu item updated successfully');
                    location.reload();
                } else {
                    alert(data.message || 'Error updating menu item');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating menu item. Please try again.');
            }
        });
    }
});

// Function to edit menu item
async function editMenuItem(itemId) {
    try {
        console.log('Editing menu item:', itemId);
        console.log('Making request to:', `./api/get_menu_item.php?id=${itemId}`);
        const response = await fetch(`./api/get_menu_item.php?id=${itemId}`);
        console.log('Response:', response);
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            openEditPopup(data.item);
        } else {
            throw new Error(data.message || 'Failed to fetch menu item details');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error fetching menu item details. Please try again.');
    }
}

// Function to delete menu item
async function deleteMenuItem(itemId) {
    if (confirm('Are you sure you want to delete this menu item? This action cannot be undone and will also remove this item from all order history.')) {
        try {
            const formData = new FormData();
            formData.append('item_id', itemId);
            
            const response = await fetch('./delete_menu_item.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                alert(data.message || 'Menu item deleted successfully');
                location.reload();
            } else {
                alert(data.message || 'Error deleting menu item');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error deleting menu item. Please try again.');
        }
    }
} 