document.addEventListener('DOMContentLoaded', function() {
    // =============================================
    // Inventory Table Pagination Functionality
    // =============================================
    try {
        const table = document.getElementById('inventoryTable');
        if (!table) {
            console.debug('Inventory table not found - skipping table pagination');
            return;
        }

        const tbody = table.querySelector('tbody');
        if (!tbody) {
            console.error('Table body not found in inventory table');
            return;
        }

        // Pagination configuration
        const rowsPerPage = 5;
        const rows = tbody.querySelectorAll('tr');
        const totalEntries = rows.length;
        const totalPages = Math.ceil(totalEntries / rowsPerPage);
        let currentPage = 1;
        
        // Get pagination controls
        const startEntry = document.getElementById('startEntry');
        const endEntry = document.getElementById('endEntry');
        const totalEntriesSpan = document.getElementById('totalEntries');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        
        // Initialize pagination info
        if (totalEntriesSpan) totalEntriesSpan.textContent = totalEntries;
        
        function updatePagination() {
            const start = (currentPage - 1) * rowsPerPage + 1;
            const end = Math.min(currentPage * rowsPerPage, totalEntries);
            
            if (startEntry) startEntry.textContent = start;
            if (endEntry) endEntry.textContent = end;
            
            // Show/hide rows
            rows.forEach((row, index) => {
                row.style.display = (index >= start - 1 && index < end) ? '' : 'none';
            });
            
            // Update button states
            if (prevBtn) prevBtn.disabled = currentPage === 1;
            if (nextBtn) nextBtn.disabled = currentPage === totalPages;
        }
        
        // Event listeners for pagination buttons
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    updatePagination();
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    updatePagination();
                }
            });
        }
        
        // Initialize pagination
        if (rows.length > 0) {
            updatePagination();
        }
        
        // =============================================
        // Row Action Handlers (Edit/Delete)
        // =============================================
        const editButtons = document.querySelectorAll('.btn-edit');
        const deleteButtons = document.querySelectorAll('.btn-delete');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const itemName = row.cells[1].textContent;
                alert(`Edit functionality for ${itemName} would go here`);
            });
        });
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const itemName = row.cells[1].textContent;
                if (confirm(`Are you sure you want to delete ${itemName}?`)) {
                    row.remove();
                    // Update pagination after deletion
                    updatePagination();
                }
            });
        });

    } catch (error) {
        console.error('Error in inventory table functionality:', error);
    }

    // =============================================
    // Back Button Functionality
    // =============================================
    try {
        const backBtn = document.querySelector('.back-btn');
        if (backBtn) {
            backBtn.addEventListener('click', () => {
                window.history.back();
            });
        }
    } catch (error) {
        console.error('Error in back button functionality:', error);
    }

    // =============================================
    // Menu Items Functionality
    // =============================================
    try {
        const menuItems = document.querySelectorAll('.menu-items li');
        if (menuItems.length > 0) {
            // Get current page filename (no query, lowercased)
            let currentPage = window.location.pathname.split('/').pop().toLowerCase();
            if (currentPage.includes('?')) {
                currentPage = currentPage.split('?')[0];
            }
            menuItems.forEach(item => {
                const link = item.querySelector('a[href]');
                if (link) {
                    // Get link target filename (no query, lowercased)
                    let linkPage = link.getAttribute('href').split('/').pop().toLowerCase();
                    if (linkPage.includes('?')) {
                        linkPage = linkPage.split('?')[0];
                    }
                    if (linkPage === currentPage) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                } else {
                    item.classList.remove('active');
                }
            });
        }
    } catch (error) {
        console.error('Error in menu items functionality:', error);
    }
});

// =============================================
// API Fetch Handling (for get_raw_materials.php)
// =============================================
async function fetchRawMaterials() {
    try {
        const response = await fetch('../api/get_raw_materials.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        console.log('Raw materials data:', data);
        return data;
    } catch (error) {
        console.error('Error fetching raw materials:', error);
        return null;
    }
}

// Call the fetch function if needed
// fetchRawMaterials().then(data => { /* handle data */ });