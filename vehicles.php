<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles Management - Fleetly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard-styles.css">
    <link rel="stylesheet" href="vehicles-styles.css">
</head>
<body class="dashboard-page">
    <!-- Top Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="logo">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <rect width="32" height="32" rx="8" fill="#1C4E80"/>
                    <text x="6" y="22" fill="white" font-family="Inter" font-weight="700" font-size="12">F</text>
                </svg>
                <span class="logo-text">Fleetly</span>
            </div>
        </div>

        <div class="navbar-center">
            <div class="search-container">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="M21 21l-4.35-4.35"></path>
                </svg>
                <input type="text" placeholder="Search vehicles..." class="search-input" id="searchInput">
            </div>
        </div>

        <div class="navbar-right">
            <div class="navbar-actions">
                <button class="action-btn notification-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="badge">3</span>
                </button>
                
                <div class="user-menu">
                    <div class="user-avatar">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <span class="user-name">John Doe</span>
                        <span class="user-role">Fleet Manager</span>
                    </div>
                    <svg class="dropdown-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="6,9 12,15 18,9"></polyline>
                    </svg>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span class="nav-text">Dashboard</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span class="nav-text">Fleet Overview</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polygon points="10,8 16,12 10,16 10,8"></polygon>
                        </svg>
                        <span class="nav-text">Live Tracking</span>
                    </div>

                    <div class="nav-item" onclick="window.location.href='drivers.php'">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="nav-text">Drivers</span>
                    </div>
                    <div class="nav-item active" onclick="window.location.href='vehicles.php'">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="nav-text">Vehicles</span>
                    </div>
                    <div class="nav-item" onclick="window.location.href='route-config.php'">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="nav-text">Fleet Management</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span class="nav-text">Fuel Management</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"></polyline>
                        </svg>
                        <span class="nav-text">Analytics</span>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-header">Management</div>
                    
                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                        <span class="nav-text">Maintenance</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        <span class="nav-text">Reports</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span class="nav-text">Settings</span>
                    </div>
                </div>
            </nav>
        </div>

        <div class="sidebar-footer">
            <div class="plan-card">
                <div class="plan-icon">⚡</div>
                <div class="plan-content">
                    <h4>Upgrade to Pro</h4>
                    <p>Get advanced analytics and unlimited tracking</p>
                </div>
            </div>
        </div>
    </aside>
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="content-header">
            <div class="page-title">
                <h1>Vehicles Management</h1>
                <p>Manage your fleet vehicles and their information</p>
            </div>
            
            <div class="header-actions">
                <div class="filter-dropdown">
                    <select class="filter-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Out of Service">Out of Service</option>
                    </select>
                </div>
                
                <button class="btn-primary" id="addVehicleBtn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    Add New Vehicle
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="vehicles-stats">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="totalVehicles">0</div>
                    <div class="stat-label">Total Vehicles</div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="16,12 12,16 8,12"></polyline>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="activeVehicles">0</div>
                    <div class="stat-label">Active Vehicles</div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="maintenanceVehicles">0</div>
                    <div class="stat-label">In Maintenance</div>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="avgMileage">0 km/l</div>
                    <div class="stat-label">Avg. Fuel Efficiency</div>
                </div>
            </div>
        </div>

        <!-- Vehicles Table -->
        <div class="table-container">
            <div class="table-header">
                <h3>All Vehicles</h3>
                <div class="table-actions">
                    <button class="btn-secondary" id="exportBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Export
                    </button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="vehicles-table" id="vehiclesTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Vehicle</th>
                            <th>Vehicle ID</th>
                            <th>License Plate</th>
                            <th>Type</th>
                            <th>Odometer</th>
                            <th>Fuel Efficiency</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="vehiclesTableBody">
                        <!-- Table rows will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <div class="table-info">
                    Showing <span id="showingFrom">0</span> to <span id="showingTo">0</span> of <span id="totalRecords">0</span> vehicles
                </div>
                <div class="table-pagination">
                    <button class="pagination-btn" id="prevBtn" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                    </button>
                    <span class="pagination-info">Page <span id="currentPage">1</span> of <span id="totalPages">1</span></span>
                    <button class="pagination-btn" id="nextBtn" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit Vehicle Modal -->
    <div class="modal-overlay" id="vehicleModal">
        <div class="modal large-modal">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Vehicle</h3>
                <button class="modal-close" id="closeModal">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <form id="vehicleForm" class="vehicle-form" novalidate>
                    <input type="hidden" id="vehicleId">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="vehicleName">Vehicle Name *</label>
                            <input type="text" id="vehicleName" name="vehicleName" required placeholder="Enter vehicle name">
                            <span class="error-message"></span>
                        </div>

                        <div class="form-group">
                            <label for="vehicleIdField">Vehicle ID</label>
                            <input type="text" id="vehicleIdField" name="vehicleIdField" readonly placeholder="Auto-generated">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="vehicleType">Vehicle Type *</label>
                            <select id="vehicleType" name="vehicleType" required>
                                <option value="">Select Type</option>
                                <option value="Car">Car</option>
                                <option value="Truck">Truck</option>
                                <option value="Van">Van</option>
                                <option value="Bus">Bus</option>
                                <option value="Motorcycle">Motorcycle</option>
                                <option value="Other">Other</option>
                            </select>
                            <span class="error-message"></span>
                        </div>

                        <div class="form-group">
                            <label for="make">Make *</label>
                            <input type="text" id="make" name="make" required placeholder="e.g., Toyota, Ford">
                            <span class="error-message"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="model">Model *</label>
                            <input type="text" id="model" name="model" required placeholder="e.g., Camry, F-150">
                            <span class="error-message"></span>
                        </div>

                        <div class="form-group">
                            <label for="year">Year *</label>
                            <input type="number" id="year" name="year" required min="1980" max="2030" placeholder="2023">
                            <span class="error-message"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="licensePlate">License Plate *</label>
                            <input type="text" id="licensePlate" name="licensePlate" required placeholder="ABC-1234">
                            <span class="error-message"></span>
                        </div>

                        <div class="form-group">
                            <label for="fuelType">Fuel Type *</label>
                            <select id="fuelType" name="fuelType" required>
                                <option value="">Select Fuel Type</option>
                                <option value="Petrol">Petrol</option>
                                <option value="Diesel">Diesel</option>
                                <option value="Electric">Electric</option>
                                <option value="Hybrid">Hybrid</option>
                                <option value="CNG">CNG</option>
                                <option value="LPG">LPG</option>
                            </select>
                            <span class="error-message"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="odometerReading">Odometer Reading (km) *</label>
                            <input type="number" id="odometerReading" name="odometerReading" step="0.01" placeholder="50000.00" required>
                            <span class="error-message"></span>
                        </div>

                        <div class="form-group">
                            <label for="fuelEfficiency">Fuel Efficiency (km/l)</label>
                            <input type="number" id="fuelEfficiency" name="fuelEfficiency" step="0.1" placeholder="15.5">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="currentMileage">Current Mileage (km/month)</label>
                            <input type="number" id="currentMileage" name="currentMileage" step="0.01" placeholder="2500.00">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Out of Service">Out of Service</option>
                            </select>
                            <span class="error-message"></span>
                        </div>

                        <div class="form-group">
                            <label for="color">Color</label>
                            <input type="text" id="color" name="color" placeholder="e.g., White, Black">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Any additional information about the vehicle..."></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelBtn">Cancel</button>
                <button type="submit" form="vehicleForm" class="btn-primary" id="saveBtn">
                    <span class="btn-text">Save Vehicle</span>
                    <div class="btn-loader" style="display: none;">
                        <div class="spinner"></div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal delete-modal">
            <div class="modal-header">
                <h3>Delete Vehicle</h3>
                <button class="modal-close" id="closeDeleteModal">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <div class="delete-content">
                    <div class="delete-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    <h4>Are you sure?</h4>
                    <p>This action cannot be undone. This will permanently delete <strong id="deleteVehicleName"></strong> and all associated data.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelDeleteBtn">Cancel</button>
                <button type="button" class="btn-danger" id="confirmDeleteBtn">
                    <span class="btn-text">Delete Vehicle</span>
                    <div class="btn-loader" style="display: none;">
                        <div class="spinner"></div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <div class="toast-content">
            <div class="toast-icon"></div>
            <div class="toast-message"></div>
        </div>
        <button class="toast-close">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <script src="vehicles.js"></script>
</body>
</html>
