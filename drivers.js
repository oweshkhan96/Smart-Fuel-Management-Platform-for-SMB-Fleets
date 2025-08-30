class DriversManager {
    constructor() {
        this.drivers = [];
        this.filteredDrivers = [];
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.selectedDriverId = null;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupSidebar();
        this.loadDrivers();
        console.log('🚗 Drivers Manager initialized');
    }

    setupEventListeners() {
        // Add Driver Button
        document.getElementById('addDriverBtn').addEventListener('click', () => {
            this.openDriverModal();
        });

        // Search Input
        document.getElementById('searchInput').addEventListener('input', (e) => {
            this.searchDrivers(e.target.value);
        });

        // Status Filter
        document.getElementById('statusFilter').addEventListener('change', (e) => {
            this.filterByStatus(e.target.value);
        });

        // Modal Events
        document.getElementById('closeModal').addEventListener('click', () => {
            this.closeModal('driverModal');
        });

        document.getElementById('cancelBtn').addEventListener('click', () => {
            this.closeModal('driverModal');
        });

        document.getElementById('closeDeleteModal').addEventListener('click', () => {
            this.closeModal('deleteModal');
        });

        document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
            this.closeModal('deleteModal');
        });

        // Form Submission
        document.getElementById('driverForm').addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });

        // Delete Confirmation
        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            this.deleteDriver();
        });

        // Pagination
        document.getElementById('prevBtn').addEventListener('click', () => {
            this.changePage(this.currentPage - 1);
        });

        document.getElementById('nextBtn').addEventListener('click', () => {
            this.changePage(this.currentPage + 1);
        });

        // Select All Checkbox
        document.getElementById('selectAll').addEventListener('change', (e) => {
            this.toggleSelectAll(e.target.checked);
        });

        // Export Button
        document.getElementById('exportBtn').addEventListener('click', () => {
            this.exportDrivers();
        });

        // Toast Close
        document.querySelector('.toast-close').addEventListener('click', () => {
            this.hideToast();
        });

        // Form validation on input
        const inputs = document.querySelectorAll('#driverForm input, #driverForm select');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('error')) {
                    this.validateField(input);
                }
            });
        });
    }

    setupSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                mainContent.style.marginLeft = 'var(--sidebar-collapsed-width)';
            } else {
                mainContent.style.marginLeft = 'var(--sidebar-width)';
            }
        });

        // Mobile sidebar toggle
        if (window.innerWidth <= 768) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }
    }

    async loadDrivers() {
        try {
            console.log('🔄 Loading drivers from database...');
            await this.fetchDrivers();
        } catch (error) {
            console.error('Error loading drivers:', error);
            this.showToast('Failed to load drivers', 'error');
        }
    }

    async fetchDrivers() {
        try {
            const response = await fetch('drivers_api.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            const responseText = await response.text();
            console.log('📥 Raw response:', responseText);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = JSON.parse(responseText);
            console.log('📥 Parsed response:', result);
            
            if (result.success) {
                this.drivers = result.data || [];
                this.filteredDrivers = [...this.drivers];
                console.log(`✅ Loaded ${this.drivers.length} drivers from database`);
            } else {
                throw new Error(result.message || 'Failed to fetch drivers');
            }
            
        } catch (error) {
            console.error('❌ Error fetching drivers:', error);
            this.drivers = [];
            this.filteredDrivers = [];
            this.showToast('Failed to load drivers: ' + error.message, 'error');
        }
        
        this.renderDrivers();
        this.updateStats();
    }

    renderDrivers() {
        const tbody = document.getElementById('driversTableBody');
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const driversToShow = this.filteredDrivers.slice(startIndex, endIndex);

        tbody.innerHTML = '';

        if (driversToShow.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="opacity: 0.5;">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <div>
                                <h4 style="margin-bottom: 0.5rem;">No drivers found</h4>
                                <p>Click "Add New Driver" to get started</p>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            driversToShow.forEach(driver => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <input type="checkbox" class="driver-checkbox" data-driver-id="${driver.id}">
                    </td>
                    <td>
                        <div class="driver-info">
                            <div class="driver-avatar">
                                ${driver.avatar_url ? 
                                    `<img src="${driver.avatar_url}" alt="${driver.full_name}">` : 
                                    driver.full_name.charAt(0).toUpperCase()
                                }
                            </div>
                            <div class="driver-details">
                                <div class="driver-name">${driver.full_name}</div>
                                <div class="driver-email">${driver.email}</div>
                            </div>
                        </div>
                    </td>
                    <td><strong>${driver.driver_id}</strong></td>
                    <td>${driver.license_number}</td>
                    <td>${driver.phone}</td>
                    <td>${driver.email}</td>
                    <td>
                        <span class="status-badge ${driver.status.toLowerCase().replace(' ', '-')}">
                            ${driver.status}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn edit" onclick="driversManager.editDriver(${driver.id})" title="Edit Driver">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3l-9.5 9.5L8 16l1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <button class="action-btn delete" onclick="driversManager.confirmDeleteDriver(${driver.id}, '${driver.full_name}')" title="Delete Driver">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <polyline points="3,6 5,6 21,6"></polyline>
                                    <path d="M19,6v14a2,2 0 0,1-2,2H7a2,2 0 0,1-2-2V6M10,11v6M14,11v6M8,6V4a2,2 0 0,1,2-2h4a2,2 0 0,1,2,2v2"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        this.updatePagination();
    }

    updateStats() {
        const total = this.drivers.length;
        const active = this.drivers.filter(d => d.status === 'Active').length;
        const inactive = this.drivers.filter(d => d.status === 'Inactive').length;
        
        document.getElementById('totalDrivers').textContent = total;
        document.getElementById('activeDrivers').textContent = active;
        document.getElementById('inactiveDrivers').textContent = inactive;
    }

    updatePagination() {
        const totalPages = Math.ceil(this.filteredDrivers.length / this.itemsPerPage);
        const startRecord = this.filteredDrivers.length === 0 ? 0 : (this.currentPage - 1) * this.itemsPerPage + 1;
        const endRecord = Math.min(this.currentPage * this.itemsPerPage, this.filteredDrivers.length);
        
        document.getElementById('showingFrom').textContent = startRecord;
        document.getElementById('showingTo').textContent = endRecord;
        document.getElementById('totalRecords').textContent = this.filteredDrivers.length;
        document.getElementById('currentPage').textContent = totalPages === 0 ? 0 : this.currentPage;
        document.getElementById('totalPages').textContent = Math.max(1, totalPages);
        
        document.getElementById('prevBtn').disabled = this.currentPage <= 1;
        document.getElementById('nextBtn').disabled = this.currentPage >= totalPages;
    }

    changePage(page) {
        const totalPages = Math.ceil(this.filteredDrivers.length / this.itemsPerPage);
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.renderDrivers();
        }
    }

    searchDrivers(query) {
        if (!query.trim()) {
            this.filteredDrivers = [...this.drivers];
        } else {
            this.filteredDrivers = this.drivers.filter(driver => 
                driver.full_name.toLowerCase().includes(query.toLowerCase()) ||
                driver.email.toLowerCase().includes(query.toLowerCase()) ||
                driver.driver_id.toLowerCase().includes(query.toLowerCase()) ||
                driver.phone.includes(query) ||
                driver.license_number.toLowerCase().includes(query.toLowerCase())
            );
        }
        this.currentPage = 1;
        this.renderDrivers();
    }

    filterByStatus(status) {
        if (!status) {
            this.filteredDrivers = [...this.drivers];
        } else {
            this.filteredDrivers = this.drivers.filter(driver => driver.status === status);
        }
        this.currentPage = 1;
        this.renderDrivers();
    }

    openDriverModal(driver = null) {
        const modal = document.getElementById('driverModal');
        const form = document.getElementById('driverForm');
        const title = document.getElementById('modalTitle');
        
        form.reset();
        this.clearFormErrors();
        
        if (driver) {
            // Edit mode
            title.textContent = 'Edit Driver';
            document.getElementById('driverId').value = driver.id;
            document.getElementById('fullName').value = driver.full_name;
            document.getElementById('email').value = driver.email;
            document.getElementById('phone').value = driver.phone;
            document.getElementById('licenseNumber').value = driver.license_number;
            document.getElementById('licenseExpiry').value = driver.license_expiry;
            document.getElementById('dateOfBirth').value = driver.date_of_birth;
            document.getElementById('status').value = driver.status;
            document.getElementById('address').value = driver.address || '';
            document.getElementById('driverIdField').value = driver.driver_id;
        } else {
            // Add mode
            title.textContent = 'Add New Driver';
            document.getElementById('driverId').value = '';
            document.getElementById('driverIdField').value = 'Auto-generated';
        }
        
        modal.classList.add('show');
    }

    editDriver(driverId) {
        const driver = this.drivers.find(d => d.id == driverId);
        if (driver) {
            this.openDriverModal(driver);
        }
    }

    confirmDeleteDriver(driverId, driverName) {
        this.selectedDriverId = driverId;
        document.getElementById('deleteDriverName').textContent = driverName;
        document.getElementById('deleteModal').classList.add('show');
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        if (!this.validateForm()) {
            return;
        }

        const saveBtn = document.getElementById('saveBtn');
        const btnText = saveBtn.querySelector('.btn-text');
        const btnLoader = saveBtn.querySelector('.btn-loader');

        // Show loading state
        saveBtn.disabled = true;
        saveBtn.classList.add('loading');
        btnText.style.opacity = '0';
        btnLoader.style.display = 'flex';

        try {
            const formData = new FormData(e.target);
            const driverId = document.getElementById('driverId').value;
            
            let method = driverId ? 'PUT' : 'POST';
            
            if (driverId) {
                formData.append('id', driverId);
            }
            
            console.log('💾 Saving driver...', method);
            
            const response = await fetch('drivers_api.php', {
                method: method,
                body: formData
            });
            
            const responseText = await response.text();
            console.log('📥 Save response:', responseText);
            
            const result = JSON.parse(responseText);
            
            if (result.success) {
                this.closeModal('driverModal');
                await this.loadDrivers();
                this.showToast(result.message, 'success');
            } else {
                throw new Error(result.message || 'Failed to save driver');
            }
            
        } catch (error) {
            console.error('❌ Error saving driver:', error);
            this.showToast('Error saving driver: ' + error.message, 'error');
        } finally {
            // Reset button state
            saveBtn.disabled = false;
            saveBtn.classList.remove('loading');
            btnText.style.opacity = '1';
            btnLoader.style.display = 'none';
        }
    }

    async deleteDriver() {
        if (!this.selectedDriverId) return;

        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const btnText = deleteBtn.querySelector('.btn-text');
        const btnLoader = deleteBtn.querySelector('.btn-loader');

        // Show loading state
        deleteBtn.disabled = true;
        deleteBtn.classList.add('loading');
        btnText.style.opacity = '0';
        btnLoader.style.display = 'flex';

        try {
            console.log('🗑️ Deleting driver...');
            
            const formData = new FormData();
            formData.append('id', this.selectedDriverId);
            
            const response = await fetch('drivers_api.php', {
                method: 'DELETE',
                body: formData
            });
            
            const responseText = await response.text();
            console.log('📥 Delete response:', responseText);
            
            const result = JSON.parse(responseText);
            
            if (result.success) {
                this.closeModal('deleteModal');
                await this.loadDrivers();
                this.showToast(result.message, 'success');
            } else {
                throw new Error(result.message || 'Failed to delete driver');
            }
            
        } catch (error) {
            console.error('❌ Error deleting driver:', error);
            this.showToast('Error deleting driver: ' + error.message, 'error');
        } finally {
            // Reset button state
            deleteBtn.disabled = false;
            deleteBtn.classList.remove('loading');
            btnText.style.opacity = '1';
            btnLoader.style.display = 'none';
        }
    }

    validateForm() {
        const form = document.getElementById('driverForm');
        const requiredFields = form.querySelectorAll('input[required], select[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        const name = field.name;
        let isValid = true;
        let errorMessage = '';

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        }
        // Email validation
        else if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }
        // Phone validation
        else if (name === 'phone' && value) {
            const phoneRegex = /^[\+]?[0-9\-\(\)\s]+$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid phone number';
            }
        }
        // Date validation
        else if (field.type === 'date' && value) {
            const date = new Date(value);
            const today = new Date();
            
            if (name === 'dateOfBirth') {
                const age = today.getFullYear() - date.getFullYear();
                if (age < 18 || age > 80) {
                    isValid = false;
                    errorMessage = 'Driver must be between 18 and 80 years old';
                }
            } else if (name === 'licenseExpiry') {
                if (date <= today) {
                    isValid = false;
                    errorMessage = 'License expiry must be in the future';
                }
            }
        }

        this.updateFieldValidation(field, isValid, errorMessage);
        return isValid;
    }

    updateFieldValidation(field, isValid, errorMessage) {
        const errorElement = field.parentNode.querySelector('.error-message');
        
        if (isValid) {
            field.classList.remove('error');
            if (errorElement) {
                errorElement.classList.remove('show');
                setTimeout(() => {
                    errorElement.textContent = '';
                }, 300);
            }
        } else {
            field.classList.add('error');
            if (errorElement) {
                errorElement.textContent = errorMessage;
                errorElement.classList.add('show');
            }
        }
    }

    clearFormErrors() {
        const form = document.getElementById('driverForm');
        const fields = form.querySelectorAll('input, select, textarea');
        const errorMessages = form.querySelectorAll('.error-message');
        
        fields.forEach(field => {
            field.classList.remove('error');
        });
        
        errorMessages.forEach(error => {
            error.classList.remove('show');
            error.textContent = '';
        });
    }

    closeModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
        if (modalId === 'deleteModal') {
            this.selectedDriverId = null;
        }
    }

    toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.driver-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
    }

    exportDrivers() {
        if (this.filteredDrivers.length === 0) {
            this.showToast('No drivers to export', 'error');
            return;
        }

        // Create CSV content
        const headers = ['Driver ID', 'Full Name', 'Email', 'Phone', 'License Number', 'Status', 'Created Date'];
        const csvContent = [
            headers.join(','),
            ...this.filteredDrivers.map(driver => [
                driver.driver_id,
                `"${driver.full_name}"`,
                driver.email,
                driver.phone,
                driver.license_number,
                driver.status,
                driver.created_at.split(' ')[0] // Just date part
            ].join(','))
        ].join('\n');

        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `drivers_export_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        this.showToast('Drivers exported successfully', 'success');
    }

    showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const messageEl = toast.querySelector('.toast-message');
        
        messageEl.textContent = message;
        toast.className = `toast ${type}`;
        toast.classList.add('show');
        
        // Auto hide after 3 seconds
        setTimeout(() => {
            this.hideToast();
        }, 3000);
    }

    hideToast() {
        document.getElementById('toast').classList.remove('show');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.driversManager = new DriversManager();
});

// Handle window resize
window.addEventListener('resize', () => {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth <= 768) {
        mainContent.style.marginLeft = '0';
        sidebar.classList.remove('collapsed');
    } else {
        mainContent.style.marginLeft = sidebar.classList.contains('collapsed') 
            ? 'var(--sidebar-collapsed-width)' 
            : 'var(--sidebar-width)';
    }
});
