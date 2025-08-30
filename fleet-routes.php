<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Route Management - Fleetly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="dashboard-styles.css">
    <link rel="stylesheet" href="fleet-routes-styles.css">
</head>
<body class="dashboard-page">
    <!-- Navbar and Sidebar (same as vehicles page) -->
    <nav class="navbar">
        <!-- Same navbar structure -->
    </nav>

    <aside class="sidebar" id="sidebar">
        <!-- Same sidebar structure with Fleet Routes active -->
    </aside>

    <main class="main-content" id="mainContent">
        <div class="content-header">
            <div class="page-title">
                <h1>Fleet Route Management</h1>
                <p>Configure intelligent routes for your fleet operations</p>
            </div>
            
            <div class="header-actions">
                <button class="btn-primary" id="newRouteBtn">
                    <i class="fas fa-plus"></i>
                    Create New Route
                </button>
            </div>
        </div>

        <!-- Multi-Step Route Configuration -->
        <div class="route-wizard" id="routeWizard" style="display: none;">
            <!-- Step Progress Indicator -->
            <div class="wizard-progress">
                <div class="progress-step active" data-step="1">
                    <div class="step-icon"><i class="fas fa-car"></i></div>
                    <div class="step-label">Vehicle Selection</div>
                </div>
                <div class="progress-step" data-step="2">
                    <div class="step-icon"><i class="fas fa-user"></i></div>
                    <div class="step-label">Driver Assignment</div>
                </div>
                <div class="progress-step" data-step="3">
                    <div class="step-icon"><i class="fas fa-cog"></i></div>
                    <div class="step-label">Configuration</div>
                </div>
                <div class="progress-step" data-step="4">
                    <div class="step-icon"><i class="fas fa-map"></i></div>
                    <div class="step-label">Destinations</div>
                </div>
            </div>

            <!-- Step 1: Vehicle Selection -->
            <div class="wizard-step active" id="step1">
                <div class="step-content">
                    <h3>Select Vehicle</h3>
                    <p>Choose a vehicle for this route configuration</p>
                    
                    <div class="vehicle-grid" id="vehicleGrid">
                        <!-- Populated by JavaScript -->
                    </div>

                    <div class="step-actions">
                        <button class="btn-secondary" id="cancelStep1">Cancel</button>
                        <button class="btn-primary" id="continueStep1" disabled>Continue</button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Driver Assignment -->
            <div class="wizard-step" id="step2">
                <div class="step-content">
                    <h3>Assign Driver</h3>
                    <p>Select an available driver for this route</p>
                    
                    <div class="driver-grid" id="driverGrid">
                        <!-- Populated by JavaScript -->
                    </div>

                    <div class="step-actions">
                        <button class="btn-secondary" id="backStep2">Back</button>
                        <button class="btn-primary" id="continueStep2" disabled>Continue</button>
                    </div>
                </div>
            </div>

            <!-- Step 3: Fleet Configuration -->
            <div class="wizard-step" id="step3">
                <div class="step-content">
                    <h3>Route Configuration</h3>
                    <p>Configure the route type and scheduling options</p>
                    
                    <form id="configForm" class="config-form">
                        <div class="form-section">
                            <h4>Fleet Usage Type</h4>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="fleet_type" value="one_time" checked>
                                    <span class="radio-custom"></span>
                                    <div class="radio-content">
                                        <strong>Single Use / One-Time</strong>
                                        <small>Execute this route once</small>
                                    </div>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="fleet_type" value="recurring">
                                    <span class="radio-custom"></span>
                                    <div class="radio-content">
                                        <strong>Recurring Route</strong>
                                        <small>Repeat on a schedule</small>
                                    </div>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="fleet_type" value="time_period">
                                    <span class="radio-custom"></span>
                                    <div class="radio-content">
                                        <strong>Specific Time Period</strong>
                                        <small>Active for a date range</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Recurring Options (shown when recurring is selected) -->
                        <div class="form-section recurring-options" style="display: none;">
                            <h4>Recurrence Pattern</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Repeat Every</label>
                                    <select name="recurrence_type">
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Interval</label>
                                    <input type="number" name="recurrence_interval" min="1" value="1" placeholder="Every X days/weeks/months">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date">
                                </div>
                                <div class="form-group">
                                    <label>End Date (Optional)</label>
                                    <input type="date" name="end_date">
                                </div>
                            </div>
                        </div>

                        <!-- Timing Configuration -->
                        <div class="form-section">
                            <h4>Timing</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Departure Time</label>
                                    <input type="time" name="departure_time" required>
                                </div>
                                <div class="form-group">
                                    <label>Expected Arrival Time</label>
                                    <input type="time" name="arrival_time" required>
                                </div>
                            </div>
                        </div>

                        <!-- Destination Type -->
                        <div class="form-section">
                            <h4>Destination Type</h4>
                            <div class="radio-group">
                                <label class="radio-option">
                                    <input type="radio" name="destination_type" value="single" checked>
                                    <span class="radio-custom"></span>
                                    <div class="radio-content">
                                        <strong>Single Destination</strong>
                                        <small>Point A to Point B route</small>
                                    </div>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="destination_type" value="multiple">
                                    <span class="radio-custom"></span>
                                    <div class="radio-content">
                                        <strong>Multiple Points</strong>
                                        <small>Multi-stop route with optimization</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </form>

                    <div class="step-actions">
                        <button class="btn-secondary" id="backStep3">Back</button>
                        <button class="btn-primary" id="continueStep3">Continue</button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Destinations -->
            <div class="wizard-step" id="step4">
                <div class="step-content destinations-step">
                    <h3>Configure Destinations</h3>
                    <p id="destinationSubtitle">Set up your route destinations</p>
                    
                    <div class="destinations-layout">
                        <!-- Left Panel: Controls -->
                        <div class="destinations-controls">
                            <div class="search-section">
                                <h4>Add Destination</h4>
                                <div class="search-container">
                                    <input type="text" id="locationSearch" placeholder="Search for locations..." class="search-input">
                                    <div class="search-suggestions" id="searchSuggestions"></div>
                                </div>
                                <div class="manual-pin-controls">
                                    <button class="btn-secondary btn-sm" id="enableManualPin">
                                        <i class="fas fa-map-pin"></i> Click Map to Pin
                                    </button>
                                    <span class="manual-pin-info">Or click anywhere on the map</span>
                                </div>
                            </div>

                            <!-- Destinations List -->
                            <div class="destinations-list-section">
                                <h4>Route Destinations <span class="destination-count">(0)</span></h4>
                                <div class="destinations-list" id="destinationsList">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>

                            <!-- AI Analysis Section -->
                            <div class="ai-analysis-section" id="aiAnalysisSection" style="display: none;">
                                <h4>AI Route Optimization</h4>
                                <div class="ai-info">
                                    <p><strong>Purpose:</strong> Analyze your selected destinations to find the most fuel-efficient route order.</p>
                                    <p><strong>Requirements:</strong> Minimum 3 destinations needed for optimization.</p>
                                </div>
                                <button class="btn-ai" id="aiAnalyzeBtn" disabled>
                                    <i class="fas fa-brain"></i> AI Route Analyze
                                </button>
                                
                                <!-- AI Results -->
                                <div class="ai-results" id="aiResults" style="display: none;">
                                    <div class="ai-results-header">
                                        <i class="fas fa-check-circle"></i>
                                        <strong>Optimization Complete!</strong>
                                    </div>
                                    <div class="savings-display">
                                        <div class="savings-item">
                                            <span class="savings-label">Fuel Saved:</span>
                                            <span class="savings-value" id="fuelSaved">0 L</span>
                                        </div>
                                        <div class="savings-item">
                                            <span class="savings-label">Money Saved:</span>
                                            <span class="savings-value" id="moneySaved">$0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="step-actions">
                                <button class="btn-secondary" id="backStep4">Back</button>
                                <button class="btn-success" id="saveRoute" disabled>
                                    <i class="fas fa-save"></i> Save Route
                                </button>
                            </div>
                        </div>

                        <!-- Right Panel: Map -->
                        <div class="destinations-map">
                            <div id="routeMap" class="route-map"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Route List (when not in wizard) -->
        <div class="routes-list" id="routesList">
            <div class="routes-grid" id="routesGrid">
                <!-- Populated with existing routes -->
            </div>
        </div>
    </main>

    <!-- Error Modal -->
    <div class="modal-overlay" id="errorModal">
        <div class="modal error-modal">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Error</h3>
                <button class="modal-close" id="closeErrorModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="error-content">
                    <div class="error-message" id="errorMessage"></div>
                    <div class="error-code" id="errorCode"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-primary" id="closeError">OK</button>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="loading-spinner"></div>
        <div class="loading-message">Processing AI analysis...</div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="fleet-routes.js"></script>
</body>
</html>
