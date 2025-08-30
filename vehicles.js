class VehiclesManager {
    constructor() {
        this.vehicles = [];
        this.filteredVehicles = [];
        // Remove: this.availableDrivers = [];
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.selectedVehicleId = null;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupSidebar();
        this.loadVehicles();
        // Remove: this.loadAvailableDrivers();
        console.log('🚗 Vehicles Manager initialized');
    }

    setupEventListeners() {
        // Add Vehicle Button
        document.getElementById('addVehicleBtn').addEventListener('click', () => {
            this.openVehicleModal();
        });

        // Search Input
        document.getElementById('searchInput').addEventListener('input', (e) => {
            this.searchVehicles(e.target.value);
        });

        // Status Filter
        document.getElementById('statusFilter').addEventListener('change', (e) => {
            this.filterByStatus(e.target.value);
        });

        // Modal Events
        document.getElementById('closeModal').addEventListener('click', () => {
            this.closeModal('vehicleModal');
        });

        document.getElementById('cancelBtn').addEventListener('click', () => {
            this.closeModal('vehicleModal');
        });

        document.getElementById('closeDeleteModal').addEventListener('click', () => {
            this.closeModal('deleteModal');
        });

        document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
            this.closeModal('deleteModal');
        });

        // Form Submission
        document.getElementById('vehicleForm').addEventListener('submit', (e) => {
            this.handleFormSubmit(e);
        });

        // Delete Confirmation
        document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
            this.deleteVehicle();
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
            this.exportVehicles();
        });

        // Toast Close
        document.querySelector('.toast-close').addEventListener('click', () => {
            this.hideToast();
        });

        // Form validation on input
        const inputs = document.querySelectorAll('#vehicleForm input, #vehicleForm select, #vehicleForm textarea');
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

    async loadVehicles() {
        try {
            console.log('🔄 Loading vehicles from database...');
            await this.fetchVehicles();
        } catch (error) {
            console.error('Error loading vehicles:', error);
            this.showToast('Failed to load vehicles', 'error');
        }
    }

    async fetchVehicles() {
        try {
            const response = await fetch('vehicles_api.php', {
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
                this.vehicles = result.data || [];
                this.filteredVehicles = [...this.vehicles];
                console.log(`✅ Loaded ${this.vehicles.length} vehicles from database`);
            } else {
                throw new Error(result.message || 'Failed to fetch vehicles');
            }
            
        } catch (error) {
            console.error('❌ Error fetching vehicles:', error);
            this.vehicles = [];
            this.filteredVehicles = [];
            this.showToast('Failed to load vehicles: ' + error.message, 'error');
        }
        
        this.renderVehicles();
        this.updateStats();
    }

    // Remove loadAvailableDrivers method completely
    // Remove populateDriverOptions method completely

    renderVehicles() {
        const tbody = document.getElementById('vehiclesTableBody');
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const vehiclesToShow = this.filteredVehicles.slice(startIndex, endIndex);

        tbody.innerHTML = '';

        if (vehiclesToShow.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="opacity: 0.5;">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <div>
                                <h4 style="margin-bottom: 0.5rem;">No vehicles found</h4>
                                <p>Click "Add New Vehicle" to get started</p>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        } else {
            vehiclesToShow.forEach(vehicle => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <input type="checkbox" class="vehicle-checkbox" data-vehicle-id="${vehicle.id}">
                    </td>
                    <td>
                        <div class="vehicle-info">
                            <div class="vehicle-avatar">
                                ${vehicle.vehicle_image ? 
                                    `<img src="${vehicle.vehicle_image}" alt="${vehicle.vehicle_name}">` : 
                                    vehicle.vehicle_name.charAt(0).toUpperCase()
                                }
                            </div>
                            <div class="vehicle-details">
                                <div class="vehicle-name">${vehicle.vehicle_name}</div>
                                <div class="vehicle-license">${vehicle.license_plate}</div>
                                <div class="vehicle-make-model">${vehicle.make} ${vehicle.model} (${vehicle.year})</div>
                            </div>
                        </div>
                    </td>
                    <td><strong>${vehicle.vehicle_id}</strong></td>
                    <td>${vehicle.license_plate}</td>
                    <td>
                        <span class="type-badge ${vehicle.vehicle_type.toLowerCase()}">
                            ${vehicle.vehicle_type}
                        </span>
                    </td>
                    <td>
                        <div class="mileage-display">
                            <span class="mileage-number">${parseFloat(vehicle.odometer_reading || 0).toLocaleString()}</span>
                            <span class="mileage-unit">km</span>
                        </div>
                    </td>
                    <td>
                        <div class="mileage-display">
                            <span class="mileage-number">${parseFloat(vehicle.fuel_efficiency || 0).toFixed(1)}</span>
                            <span class="mileage-unit">km/l</span>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge ${vehicle.status.toLowerCase().replace(' ', '-')}">
                            ${vehicle.status}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn edit" onclick="vehiclesManager.editVehicle(${vehicle.id})" title="Edit Vehicle">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.12 2.12 0 0 1 3 3l-9.5 9.5L8 16l1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <button class="action-btn delete" onclick="vehiclesManager.confirmDeleteVehicle(${vehicle.id}, '${vehicle.vehicle_name}')" title="Delete Vehicle">
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
        const total = this.vehicles.length;
        const active = this.vehicles.filter(v => v.status === 'Active').length;
        const maintenance = this.vehicles.filter(v => v.status === 'Maintenance').length;
        
        const totalEfficiency = this.vehicles.reduce((sum, v) => sum + parseFloat(v.fuel_efficiency || 0), 0);
        const avgEfficiency = total > 0 ? (totalEfficiency / total).toFixed(1) : 0;
        
        document.getElementById('totalVehicles').textContent = total;
        document.getElementById('activeVehicles').textContent = active;
        document.getElementById('maintenanceVehicles').textContent = maintenance;
        document.getElementById('avgMileage').textContent = `${avgEfficiency} km/l`;
    }

    updatePagination() {
        const totalPages = Math.ceil(this.filteredVehicles.length / this.itemsPerPage);
        const startRecord = this.filteredVehicles.length === 0 ? 0 : (this.currentPage - 1) * this.itemsPerPage + 1;
        const endRecord = Math.min(this.currentPage * this.itemsPerPage, this.filteredVehicles.length);
        
        document.getElementById('showingFrom').textContent = startRecord;
        document.getElementById('showingTo').textContent = endRecord;
        document.getElementById('totalRecords').textContent = this.filteredVehicles.length;
        document.getElementById('currentPage').textContent = totalPages === 0 ? 0 : this.currentPage;
        document.getElementById('totalPages').textContent = Math.max(1, totalPages);
        
        document.getElementById('prevBtn').disabled = this.currentPage <= 1;
        document.getElementById('nextBtn').disabled = this.currentPage >= totalPages;
    }

    changePage(page) {
        const totalPages = Math.ceil(this.filteredVehicles.length / this.itemsPerPage);
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.renderVehicles();
        }
    }

    searchVehicles(query) {
        if (!query.trim()) {
            this.filteredVehicles = [...this.vehicles];
        } else {
            this.filteredVehicles = this.vehicles.filter(vehicle => 
                vehicle.vehicle_name.toLowerCase().includes(query.toLowerCase()) ||
                vehicle.vehicle_id.toLowerCase().includes(query.toLowerCase()) ||
                vehicle.license_plate.toLowerCase().includes(query.toLowerCase()) ||
                vehicle.make.toLowerCase().includes(query.toLowerCase()) ||
                vehicle.model.toLowerCase().includes(query.toLowerCase())
                // Remove driver name search
            );
        }
        this.currentPage = 1;
        this.renderVehicles();
    }

    filterByStatus(status) {
        if (!status) {
            this.filteredVehicles = [...this.vehicles];
        } else {
            this.filteredVehicles = this.vehicles.filter(vehicle => vehicle.status === status);
        }
        this.currentPage = 1;
        this.renderVehicles();
    }

    openVehicleModal(vehicle = null) {
        const modal = document.getElementById('vehicleModal');
        const form = document.getElementById('vehicleForm');
        const title = document.getElementById('modalTitle');
        
        form.reset();
        this.clearFormErrors();
        
        if (vehicle) {
            // Edit mode - remove all driver assignment references
            title.textContent = 'Edit Vehicle';
            document.getElementById('vehicleId').value = vehicle.id;
            document.getElementById('vehicleName').value = vehicle.vehicle_name;
            document.getElementById('vehicleType').value = vehicle.vehicle_type;
            document.getElementById('make').value = vehicle.make;
            document.getElementById('model').value = vehicle.model;
            document.getElementById('year').value = vehicle.year;
            document.getElementById('licensePlate').value = vehicle.license_plate;
            document.getElementById('fuelType').value = vehicle.fuel_type;
            document.getElementById('odometerReading').value = vehicle.odometer_reading;
            document.getElementById('fuelEfficiency').value = vehicle.fuel_efficiency;
            document.getElementById('currentMileage').value = vehicle.current_mileage;
            // Remove: document.getElementById('assignedDriverId').value = vehicle.assigned_driver_id || '';
            document.getElementById('status').value = vehicle.status;
            document.getElementById('color').value = vehicle.color || '';
            document.getElementById('notes').value = vehicle.notes || '';
            document.getElementById('vehicleIdField').value = vehicle.vehicle_id;
        } else {
            // Add mode
            title.textContent = 'Add New Vehicle';
            document.getElementById('vehicleId').value = '';
            document.getElementById('vehicleIdField').value = 'Auto-generated';
        }
        
        modal.classList.add('show');
    }

    editVehicle(vehicleId) {
        const vehicle = this.vehicles.find(v => v.id == vehicleId);
        if (vehicle) {
            this.openVehicleModal(vehicle);
        }
    }

    confirmDeleteVehicle(vehicleId, vehicleName) {
        this.selectedVehicleId = vehicleId;
        document.getElementById('deleteVehicleName').textContent = vehicleName;
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
            const vehicleId = document.getElementById('vehicleId').value;
            
            let method = vehicleId ? 'PUT' : 'POST';
            
            if (vehicleId) {
                formData.append('id', vehicleId);
            }
            
            console.log('💾 Saving vehicle...', method);
            
            const response = await fetch('vehicles_api.php', {
                method: method,
                body: formData
            });
            
            const responseText = await response.text();
            console.log('📥 Save response:', responseText);
            
            const result = JSON.parse(responseText);
            
            if (result.success) {
                this.closeModal('vehicleModal');
                await this.loadVehicles();
                this.showToast(result.message, 'success');
            } else {
                throw new Error(result.message || 'Failed to save vehicle');
            }
            
        } catch (error) {
            console.error('❌ Error saving vehicle:', error);
            this.showToast('Error saving vehicle: ' + error.message, 'error');
        } finally {
            // Reset button state
            saveBtn.disabled = false;
            saveBtn.classList.remove('loading');
            btnText.style.opacity = '1';
            btnLoader.style.display = 'none';
        }
    }

    async deleteVehicle() {
        if (!this.selectedVehicleId) return;

        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const btnText = deleteBtn.querySelector('.btn-text');
        const btnLoader = deleteBtn.querySelector('.btn-loader');

        // Show loading state
        deleteBtn.disabled = true;
        deleteBtn.classList.add('loading');
        btnText.style.opacity = '0';
        btnLoader.style.display = 'flex';

        try {
            console.log('🗑️ Deleting vehicle...');
            
            const formData = new FormData();
            formData.append('id', this.selectedVehicleId);
            
            const response = await fetch('vehicles_api.php', {
                method: 'DELETE',
                body: formData
            });
            
            const responseText = await response.text();
            console.log('📥 Delete response:', responseText);
            
            const result = JSON.parse(responseText);
            
            if (result.success) {
                this.closeModal('deleteModal');
                await this.loadVehicles();
                this.showToast(result.message, 'success');
            } else {
                throw new Error(result.message || 'Failed to delete vehicle');
            }
            
        } catch (error) {
            console.error('❌ Error deleting vehicle:', error);
            this.showToast('Error deleting vehicle: ' + error.message, 'error');
        } finally {
            // Reset button state
            deleteBtn.disabled = false;
            deleteBtn.classList.remove('loading');
            btnText.style.opacity = '1';
            btnLoader.style.display = 'none';
        }
    }

    validateForm() {
        const form = document.getElementById('vehicleForm');
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
        // Year validation
        else if (name === 'year' && value) {
            const year = parseInt(value);
            const currentYear = new Date().getFullYear();
            if (year < 1980 || year > currentYear + 1) {
                isValid = false;
                errorMessage = `Year must be between 1980 and ${currentYear + 1}`;
            }
        }
        // Numeric validations
        else if ((name === 'odometerReading' || name === 'fuelEfficiency' || name === 'currentMileage') && value) {
            const numValue = parseFloat(value);
            if (isNaN(numValue) || numValue < 0) {
                isValid = false;
                errorMessage = 'Please enter a valid positive number';
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
        const form = document.getElementById('vehicleForm');
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
            this.selectedVehicleId = null;
        }
    }

    toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.vehicle-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
    }

    exportVehicles() {
        if (this.filteredVehicles.length === 0) {
            this.showToast('No vehicles to export', 'error');
            return;
        }

        // Create CSV content - remove Driver column
        const headers = ['Vehicle ID', 'Vehicle Name', 'Type', 'Make', 'Model', 'Year', 'License Plate', 'Odometer', 'Fuel Efficiency', 'Status'];
        const csvContent = [
            headers.join(','),
            ...this.filteredVehicles.map(vehicle => [
                vehicle.vehicle_id,
                `"${vehicle.vehicle_name}"`,
                vehicle.vehicle_type,
                vehicle.make,
                vehicle.model,
                vehicle.year,
                vehicle.license_plate,
                vehicle.odometer_reading,
                vehicle.fuel_efficiency,
                vehicle.status
                // Remove driver name from export
            ].join(','))
        ].join('\n');

        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `vehicles_export_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        this.showToast('Vehicles exported successfully', 'success');
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

// Initialize when DOM is loaded - Remove duplicate initialization
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing Vehicles Manager...');
    
    // Test if elements exist
    const addBtn = document.getElementById('addVehicleBtn');
    const modal = document.getElementById('vehicleModal');
    
    if (!addBtn) {
        console.error('Add Vehicle button not found!');
        return;
    }
    
    if (!modal) {
        console.error('Vehicle modal not found!');
        return;
    }
    
    console.log('Both button and modal found, initializing manager...');
    
    // Initialize the vehicles manager
    window.vehiclesManager = new VehiclesManager();
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
