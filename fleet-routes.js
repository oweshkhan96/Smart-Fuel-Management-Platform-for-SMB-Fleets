class FleetRouteManager {
    constructor() {
        // API Keys from demo
        this.GEOAPIFY_API_KEY = '053f0cbc8d894135bd0fdb09c21d1620';
        this.OPENROUTER_API_KEY = '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';
        
        // State management
        this.currentStep = 1;
        this.routeData = this.initializeRouteData();
        this.vehicles = [];
        this.drivers = [];
        this.destinations = [];
        this.map = null;
        this.markerGroup = null;
        this.routeLine = null;
        this.manualPinEnabled = false;
        
        this.init();
    }

    initializeRouteData() {
        return {
            vehicle_id: "",
            driver_id: "",
            configuration: {
                fleet_type: "one_time",
                recurrence_pattern: {
                    type: "daily",
                    interval: 1,
                    start_date: null,
                    end_date: null
                },
                timing: {
                    departure_time: "",
                    arrival_time: ""
                }
            },
            destinations: [],
            ai_analysis: {
                ordered_point_ids: [],
                fuel_saved: 0,
                money_saved: 0,
                analysis_timestamp: null
            },
            errors: []
        };
    }

    init() {
        this.bindEvents();
        this.loadVehicles();
        this.loadDrivers();
        console.log('🚗 Fleet Route Manager initialized with database support');
    }

    bindEvents() {
        // New Route Button
        document.getElementById('newRouteBtn').addEventListener('click', () => {
            this.startWizard();
        });

        // Step Navigation
        document.getElementById('continueStep1').addEventListener('click', () => this.goToStep(2));
        document.getElementById('backStep2').addEventListener('click', () => this.goToStep(1));
        document.getElementById('continueStep2').addEventListener('click', () => this.goToStep(3));
        document.getElementById('backStep3').addEventListener('click', () => this.goToStep(2));
        document.getElementById('continueStep3').addEventListener('click', () => this.goToStep(4));
        document.getElementById('backStep4').addEventListener('click', () => this.goToStep(3));

        // Cancel
        document.getElementById('cancelStep1').addEventListener('click', () => this.closeWizard());

        // Form Changes
        document.querySelectorAll('input[name="fleet_type"]').forEach(radio => {
            radio.addEventListener('change', () => this.handleFleetTypeChange());
        });

        // Location Search
        document.getElementById('locationSearch').addEventListener('input', (e) => {
            this.handleLocationSearch(e.target.value);
        });

        // Manual Pin Toggle
        document.getElementById('enableManualPin').addEventListener('click', () => {
            this.toggleManualPin();
        });

        // AI Analysis
        document.getElementById('aiAnalyzeBtn').addEventListener('click', () => {
            this.performAIAnalysis();
        });

        // Save Route - Main Database Save Function
        document.getElementById('saveRoute').addEventListener('click', () => {
            this.saveRouteToDatabase();
        });
    }

    // === VEHICLE & DRIVER LOADING ===
    async loadVehicles() {
        try {
            const response = await fetch('vehicles_api.php');
            const result = await response.json();
            
            if (result.success) {
                this.vehicles = result.data?.filter(v => v.status === 'Active') || [];
                this.renderVehicleGrid();
            } else {
                this.showError('VEHICLE_LOAD_FAIL', 'Failed to load vehicles');
            }
        } catch (error) {
            this.showError('VEHICLE_LOAD_FAIL', 'Failed to load vehicles: ' + error.message);
        }
    }

    async loadDrivers() {
        try {
            const response = await fetch('drivers_api.php');
            const result = await response.json();
            
            if (result.success) {
                this.drivers = result.data?.filter(d => d.status === 'Active') || [];
                this.renderDriverGrid();
            } else {
                this.showError('DRIVER_LOAD_FAIL', 'Failed to load drivers');
            }
        } catch (error) {
            this.showError('DRIVER_LOAD_FAIL', 'Failed to load drivers: ' + error.message);
        }
    }

    // === VEHICLE & DRIVER SELECTION ===
    selectVehicle(vehicleId, cardElement) {
        document.querySelectorAll('.vehicle-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        cardElement.classList.add('selected');
        this.routeData.vehicle_id = vehicleId;
        
        document.getElementById('continueStep1').disabled = false;
    }

    selectDriver(driverId, cardElement) {
        document.querySelectorAll('.driver-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        cardElement.classList.add('selected');
        this.routeData.driver_id = driverId;
        
        document.getElementById('continueStep2').disabled = false;
    }

    // === CONFIGURATION HANDLING ===
    validateConfigData() {
        const form = document.getElementById('configForm');
        const formData = new FormData(form);
        
        const fleetType = formData.get('fleet_type');
        const departureTime = formData.get('departure_time');
        const arrivalTime = formData.get('arrival_time');
        
        if (!departureTime || !arrivalTime) {
            this.showError('VALIDATION_FAIL', 'Please set departure and arrival times');
            return false;
        }
        
        // Update route data with configuration
        this.routeData.configuration = {
            fleet_type: fleetType,
            timing: {
                departure_time: departureTime,
                arrival_time: arrivalTime
            },
            recurrence_pattern: {
                type: formData.get('recurrence_type') || 'daily',
                interval: parseInt(formData.get('recurrence_interval')) || 1,
                start_date: formData.get('start_date') || null,
                end_date: formData.get('end_date') || null
            }
        };
        
        return true;
    }

    // === MAP & DESTINATIONS ===
    initializeMap() {
        if (this.map) return;
        
        this.map = L.map('routeMap').setView([26.2389, 73.0243], 10);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);
        
        this.markerGroup = L.layerGroup().addTo(this.map);
        
        // Add click event for manual pin placement
        this.map.on('click', (e) => {
            if (this.manualPinEnabled) {
                const { lat, lng } = e.latlng;
                const customName = prompt('Enter name for this location:', `Location ${this.destinations.length + 1}`);
                if (customName && customName.trim()) {
                    this.addDestination(customName.trim(), lat, lng, true);
                }
            }
        });
    }

    async handleLocationSearch(query) {
        if (query.length < 3) {
            document.getElementById('searchSuggestions').style.display = 'none';
            return;
        }

        try {
            const response = await fetch(
                `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(query)}&limit=5&apiKey=${this.GEOAPIFY_API_KEY}`
            );
            const data = await response.json();
            this.displaySearchSuggestions(data.features || []);
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    addDestination(name, lat, lng, isManual) {
        const destination = {
            id: Date.now(),
            name: name,
            lat: lat,
            lng: lng,
            order: this.destinations.length + 1,
            isManual: isManual
        };

        this.destinations.push(destination);
        this.updateDestinationsList();
        this.updateMapMarkers();
        this.updateAIAnalysisSection();
        
        // Update route data destinations
        this.routeData.destinations = this.destinations.map(d => ({
            lat: d.lat,
            lng: d.lng,
            address: d.name,
            order: d.order
        }));
    }

    // === AI ANALYSIS (using demo code) ===
    async performAIAnalysis() {
        if (this.destinations.length < 3) {
            this.showError('AI_ANALYZE_FAIL', 'Need at least 3 destinations for AI optimization');
            return;
        }

        this.showLoadingOverlay('🧠 AI Route Optimization in Progress');

        try {
            const originalDistance = await this.calculateTotalDistance();
            const optimizedOrder = await this.getAIRouteOptimization();
            
            if (optimizedOrder && Array.isArray(optimizedOrder)) {
                await this.applyAIOptimization(optimizedOrder, originalDistance);
            } else {
                throw new Error('AI returned invalid optimization order');
            }

        } catch (error) {
            console.error('AI optimization error:', error);
            this.routeData.errors.push({
                code: 'AI_ANALYZE_FAIL',
                message: error.message || 'Could not analyze the selected route points. Please adjust your selection and try again.'
            });
            this.showError('AI_ANALYZE_FAIL', this.routeData.errors[this.routeData.errors.length - 1].message);
        } finally {
            this.hideLoadingOverlay();
        }
    }

    async getAIRouteOptimization() {
        const destinationsInfo = this.destinations.map((dest, index) => ({
            id: index + 1,
            name: dest.name,
            lat: dest.lat.toFixed(6),
            lng: dest.lng.toFixed(6)
        }));

        const prompt = `Optimize route order for shortest distance and fuel efficiency:

${destinationsInfo.map(dest => `${dest.id}: ${dest.name} (${dest.lat}, ${dest.lng})`).join('\n')}

Return JSON array with optimal order: [1,3,2,4]`;

        const requestBody = {
            model: 'google/gemini-2.0-flash-exp',
            messages: [
                {
                    role: 'system',
                    content: 'You are a route optimizer. Return ONLY a JSON array of destination numbers in optimal order for fuel efficiency.'
                },
                {
                    role: 'user',
                    content: prompt
                }
            ],
            temperature: 0.1,
            max_tokens: 100
        };

        const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.OPENROUTER_API_KEY}`,
                'Content-Type': 'application/json',
                'HTTP-Referer': window.location.origin,
                'X-Title': 'Fleet Route Optimizer'
            },
            body: JSON.stringify(requestBody)
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`AI API Error ${response.status}: ${errorText}`);
        }

        const data = await response.json();

        if (!data.choices || !data.choices[0] || !data.choices[0].message) {
            throw new Error('Invalid AI API response structure');
        }

        const aiResponse = data.choices[0].message.content.trim();
        return this.extractOptimalOrder(aiResponse);
    }

    async applyAIOptimization(optimizedOrder, originalDistance) {
        // Validate optimization order
        if (!Array.isArray(optimizedOrder) || optimizedOrder.length !== this.destinations.length) {
            throw new Error(`Invalid order length: expected ${this.destinations.length}, got ${optimizedOrder.length}`);
        }

        const expectedIds = Array.from({length: this.destinations.length}, (_, i) => i + 1);
        const hasAllIds = expectedIds.every(id => optimizedOrder.includes(id));
        
        if (!hasAllIds) {
            throw new Error('Missing or invalid destination IDs in optimization');
        }

        // Apply the optimized order
        const reorderedDestinations = optimizedOrder.map(id => this.destinations[id - 1]);
        
        // Update order numbers
        reorderedDestinations.forEach((dest, index) => {
            dest.order = index + 1;
        });
        
        this.destinations = reorderedDestinations;
        
        // Calculate savings
        const newDistance = await this.calculateTotalDistance();
        const distanceSaved = Math.max(0, originalDistance - newDistance);
        const fuelSaved = distanceSaved * 0.08; // Estimate: 0.08L per km
        const moneySaved = fuelSaved * 1.5; // Estimate: $1.5 per liter
        
        // Update AI analysis data
        this.routeData.ai_analysis = {
            ordered_point_ids: optimizedOrder.map(id => this.destinations[id - 1].id.toString()),
            fuel_saved: parseFloat(fuelSaved.toFixed(2)),
            money_saved: parseFloat(moneySaved.toFixed(2)),
            analysis_timestamp: new Date().toISOString()
        };
        
        // Update destinations in route data
        this.routeData.destinations = this.destinations.map(d => ({
            lat: d.lat,
            lng: d.lng,
            address: d.name,
            order: d.order
        }));
        
        // Update UI
        this.updateDestinationsList();
        this.updateMapMarkers();
        this.displayAIResults(fuelSaved, moneySaved);
    }

    // === DATABASE SAVE FUNCTION ===
    async saveRouteToDatabase() {
        // Final validation
        if (!this.validateFinalRouteData()) {
            return;
        }

        this.showLoadingOverlay('💾 Saving Route to Database...');

        try {
            console.log('🔄 Saving route data:', JSON.stringify(this.routeData, null, 2));

            const response = await fetch('fleet_routes_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.routeData)
            });

            const responseText = await response.text();
            console.log('📥 Save response:', responseText);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${responseText}`);
            }

            const result = JSON.parse(responseText);
            
            if (result.success) {
                this.showSuccessMessage('✅ Route saved successfully to database!');
                
                // Show saved route details
                this.displaySavedRouteInfo(result.data);
                
                // Reset and close wizard
                setTimeout(() => {
                    this.closeWizard();
                }, 2000);
                
            } else {
                throw new Error(result.message || 'Failed to save route');
            }

        } catch (error) {
            console.error('❌ Error saving route:', error);
            
            // Add error to route data
            this.routeData.errors.push({
                code: 'SAVE_FAIL',
                message: error.message || 'Unknown error occurred while saving'
            });
            
            this.showError('SAVE_FAIL', 'Failed to save route: ' + error.message);
            
        } finally {
            this.hideLoadingOverlay();
        }
    }

    validateFinalRouteData() {
        const errors = [];

        // Check vehicle selection
        if (!this.routeData.vehicle_id) {
            errors.push('Vehicle selection is required');
        }

        // Check driver selection
        if (!this.routeData.driver_id) {
            errors.push('Driver selection is required');
        }

        // Check destinations
        if (!this.routeData.destinations || this.routeData.destinations.length < 2) {
            errors.push('At least 2 destinations are required');
        }

        // Check timing configuration
        if (!this.routeData.configuration.timing.departure_time) {
            errors.push('Departure time is required');
        }

        if (!this.routeData.configuration.timing.arrival_time) {
            errors.push('Arrival time is required');
        }

        // Show validation errors
        if (errors.length > 0) {
            const errorMessage = 'Please fix the following issues:\n\n' + errors.map(e => `• ${e}`).join('\n');
            this.showError('VALIDATION_FAIL', errorMessage);
            return false;
        }

        return true;
    }

    displaySavedRouteInfo(savedData) {
        const infoDiv = document.createElement('div');
        infoDiv.className = 'saved-route-info';
        infoDiv.innerHTML = `
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <strong>Route Saved Successfully!</strong>
            </div>
            <div class="route-details">
                <p><strong>Route ID:</strong> ${savedData.route_id}</p>
                <p><strong>Vehicle:</strong> ${savedData.vehicle_name}</p>
                <p><strong>Driver:</strong> ${savedData.driver_name}</p>
                <p><strong>Destinations:</strong> ${this.routeData.destinations.length} stops</p>
                <p><strong>Fleet Type:</strong> ${this.routeData.configuration.fleet_type}</p>
                ${this.routeData.ai_analysis.fuel_saved > 0 ? 
                    `<p><strong>AI Savings:</strong> ${this.routeData.ai_analysis.fuel_saved}L fuel, $${this.routeData.ai_analysis.money_saved}</p>` : 
                    ''
                }
                <p><strong>Created:</strong> ${new Date().toLocaleString()}</p>
            </div>
        `;
        
        // Insert before step actions
        const stepContent = document.querySelector('#step4 .step-content');
        stepContent.insertBefore(infoDiv, stepContent.querySelector('.step-actions'));
    }

    // === UTILITY FUNCTIONS ===
    showSuccessMessage(message) {
        const toast = document.createElement('div');
        toast.className = 'toast success show';
        toast.innerHTML = `
            <div class="toast-content">
                <div class="toast-icon">✓</div>
                <div class="toast-message">${message}</div>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    showError(code, message) {
        document.getElementById('errorCode').textContent = code;
        document.getElementById('errorMessage').textContent = message;
        document.getElementById('errorModal').classList.add('show');
        
        console.error(`[${code}] ${message}`);
    }

    showLoadingOverlay(message = 'Processing...') {
        const overlay = document.getElementById('loadingOverlay');
        overlay.querySelector('.loading-message').textContent = message;
        overlay.style.display = 'flex';
    }

    hideLoadingOverlay() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in kilometers
        const dLat = this.toRad(lat2 - lat1);
        const dLon = this.toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(this.toRad(lat1)) * Math.cos(this.toRad(lat2)) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    toRad(deg) {
        return deg * (Math.PI / 180);
    }

    async calculateTotalDistance() {
        if (this.destinations.length < 2) return 0;
        
        let totalDistance = 0;
        for (let i = 0; i < this.destinations.length - 1; i++) {
            const dist = this.calculateDistance(
                this.destinations[i].lat, this.destinations[i].lng,
                this.destinations[i + 1].lat, this.destinations[i + 1].lng
            );
            totalDistance += dist;
        }
        return totalDistance;
    }

    // === STEP MANAGEMENT ===
    startWizard() {
        document.getElementById('routesList').style.display = 'none';
        document.getElementById('routeWizard').style.display = 'block';
        this.goToStep(1);
    }

    closeWizard() {
        document.getElementById('routeWizard').style.display = 'none';
        document.getElementById('routesList').style.display = 'block';
        this.resetWizard();
    }

    resetWizard() {
        this.currentStep = 1;
        this.routeData = this.initializeRouteData();
        this.destinations = [];
        
        // Clear selections
        document.querySelectorAll('.vehicle-card, .driver-card').forEach(card => {
            card.classList.remove('selected');
        });
        
        // Reset forms
        if (document.getElementById('configForm')) {
            document.getElementById('configForm').reset();
        }
        
        if (document.getElementById('locationSearch')) {
            document.getElementById('locationSearch').value = '';
        }
        
        // Clear map
        if (this.map) {
            this.map.remove();
            this.map = null;
        }

        // Clear any success messages
        document.querySelectorAll('.saved-route-info').forEach(el => el.remove());
    }

    goToStep(stepNumber) {
        if (stepNumber === 3 && !this.validateStepData()) return;
        if (stepNumber === 4 && !this.validateConfigData()) {
            return;
        }

        // Initialize map when reaching step 4
        if (stepNumber === 4) {
            setTimeout(() => {
                this.initializeMap();
            }, 100);
        }

        // Hide current step
        document.querySelectorAll('.wizard-step').forEach(step => {
            step.classList.remove('active');
        });

        // Show target step
        document.getElementById(`step${stepNumber}`).classList.add('active');

        // Update progress
        document.querySelectorAll('.progress-step').forEach((step, index) => {
            step.classList.remove('active', 'completed');
            if (index + 1 < stepNumber) {
                step.classList.add('completed');
            } else if (index + 1 === stepNumber) {
                step.classList.add('active');
            }
        });

        this.currentStep = stepNumber;
    }

    validateStepData() {
        if (!this.routeData.vehicle_id || !this.routeData.driver_id) {
            this.showError('VALIDATION_FAIL', 'Please select both vehicle and driver');
            return false;
        }
        return true;
    }

    // ... [Additional helper methods for rendering grids, handling UI, etc.]
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.fleetRouteManager = new FleetRouteManager();
});
