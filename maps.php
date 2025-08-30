<?php
// maps.php - Smart Fleet Fuel Management Mapping System with Enhanced AI Route Optimization
// Configuration
$GEOAPIFY_API_KEY = '053f0cbc8d894135bd0fdb09c21d1620';
$OPENROUTER_API_KEY = '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';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Smart Fleet Fuel Management - AI Route Optimizer</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            height: 100vh;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .header .stats {
            display: flex;
            gap: 20px;
            font-size: 14px;
        }

        .container {
            display: flex;
            height: calc(100vh - 70px);
        }

        #sidebar {
            width: 350px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
            z-index: 1000;
        }

        #map {
            flex: 1;
            height: 100%;
            cursor: crosshair;
        }

        .sidebar-section {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .sidebar-section h3 {
            margin-bottom: 15px;
            color: #2c3e50;
            font-size: 16px;
            font-weight: 600;
        }

        .search-container {
            position: relative;
            margin-bottom: 15px;
        }

        .search-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }

        .suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 0 0 8px 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1001;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .suggestion-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.2s;
        }

        .suggestion-item:hover {
            background-color: #f8f9fa;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .btn-danger:hover {
            background: #c53030;
        }

        .btn-success {
            background: #38a169;
            color: white;
        }

        .btn-success:hover {
            background: #2f855a;
        }

        .btn-ai {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-ai:hover {
            background: linear-gradient(135deg, #ee5a24, #ff6b6b);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6);
        }

        .btn-secondary {
            background: #718096;
            color: white;
        }

        .btn-secondary:hover {
            background: #4a5568;
        }

        .btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .spinner {
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .stop-list {
            list-style: none;
        }

        .stop-item {
            background: #f8f9fa;
            margin-bottom: 8px;
            padding: 12px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s;
        }

        .stop-item:hover {
            transform: translateX(2px);
        }

        .stop-info {
            flex: 1;
        }

        .stop-name {
            font-weight: 500;
            color: #2d3748;
            margin-bottom: 2px;
        }

        .stop-details {
            font-size: 12px;
            color: #718096;
        }

        .stop-actions {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 6px 10px;
            font-size: 12px;
        }

        .route-info {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .route-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 600;
        }

        .stat-label {
            font-size: 12px;
            opacity: 0.9;
        }

        .fuel-stations {
            margin-top: 15px;
        }

        .fuel-station {
            background: #f0f8ff;
            border: 1px solid #bee3f8;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .fuel-station:hover {
            background-color: #e6f3ff;
        }

        .fuel-station.route-station {
            background: #fff5f5;
            border: 1px solid #fed7d7;
        }

        .fuel-station.route-station:hover {
            background-color: #fef5e7;
        }

        .fuel-price {
            font-weight: 600;
            color: #2b6cb0;
        }

        .instructions {
            max-height: 300px;
            overflow-y: auto;
        }

        .instruction-step {
            padding: 8px 0;
            border-bottom: 1px solid #f7fafc;
            font-size: 13px;
        }

        .step-distance {
            color: #718096;
            font-weight: 500;
        }

        .controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #718096;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .alert-warning {
            background: #fffaf0;
            border: 1px solid #f6d55c;
            color: #744210;
        }

        .alert-info {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            color: #2c5282;
        }

        .alert-success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            color: #276749;
        }

        .alert-error {
            background: #fed7d7;
            border: 1px solid #fc8181;
            color: #742a2a;
        }

        .efficiency-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .efficiency-good {
            background: #c6f6d5;
            color: #22543d;
        }

        .efficiency-average {
            background: #fefcbf;
            color: #744210;
        }

        .efficiency-poor {
            background: #fed7d7;
            color: #742a2a;
        }

        .ai-suggestion {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .ai-suggestion h4 {
            margin-bottom: 10px;
            font-size: 14px;
        }

        .ai-suggestion p {
            font-size: 13px;
            line-height: 1.4;
            opacity: 0.9;
        }

        .manual-pin-info {
            background: #f7fafc;
            border: 1px solid #cbd5e0;
            color: #2d3748;
            padding: 10px;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 13px;
        }

        /* Loading Overlay Styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            color: white;
            font-size: 20px;
            text-align: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #333;
            border-top: 4px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        .route-fuel-tag {
            display: inline-block;
            background: #ffeaa7;
            color: #2d3436;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 12px;
            margin-left: 5px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            #sidebar {
                width: 100%;
                height: 40vh;
            }
            
            #map {
                height: 60vh;
            }

            .header {
                flex-direction: column;
                gap: 10px;
            }

            .header .stats {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1><i class="fas fa-robot"></i> AI-Powered Fleet Route Optimizer</h1>
    <div class="stats">
        <div><i class="fas fa-gas-pump"></i> <span id="total-fuel-cost">$0</span></div>
        <div><i class="fas fa-road"></i> <span id="total-distance">0 km</span></div>
        <div><i class="fas fa-clock"></i> <span id="total-time">0 min</span></div>
    </div>
</div>

<div class="container">
    <div id="sidebar">
        <!-- Route Planning Section -->
        <div class="sidebar-section">
            <h3><i class="fas fa-map-marker-alt"></i> Route Planning</h3>
            <div class="search-container">
                <input type="text" id="search-input" class="search-input" 
                       placeholder="Search for stops, addresses, or fuel stations..." autocomplete="off">
                <div id="suggestions" class="suggestions" style="display:none;"></div>
            </div>
            
            <div class="controls">
                <button id="enable-manual-pin" class="btn btn-secondary btn-sm">
                    <i class="fas fa-map-pin"></i> Click to Pin
                </button>
                <button id="ai-optimize-route" class="btn btn-ai btn-sm">
                    <i class="fas fa-brain"></i> AI Optimize
                </button>
                <button id="optimize-route" class="btn btn-success btn-sm">
                    <i class="fas fa-magic"></i> Standard Optimize
                </button>
                <button id="clear-all" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> Clear All
                </button>
            </div>
            
            <div class="manual-pin-info">
                💡 <strong>Tip:</strong> Click anywhere on the map to add a custom stop at that location!
            </div>
        </div>

        <!-- AI Suggestions Section -->
        <div class="sidebar-section" id="ai-suggestions-section" style="display:none;">
            <div id="ai-suggestions" class="ai-suggestion">
                <h4><i class="fas fa-lightbulb"></i> AI Route Insights</h4>
                <p id="ai-suggestion-text">AI analysis will appear here...</p>
            </div>
        </div>

        <!-- Stops List Section -->
        <div class="sidebar-section">
            <h3><i class="fas fa-list"></i> Route Stops (<span id="stop-count">0</span>)</h3>
            <ul id="stops-list" class="stop-list"></ul>
        </div>

        <!-- Route Information Section -->
        <div class="sidebar-section">
            <div id="route-info" style="display:none;">
                <div class="route-info">
                    <h4><i class="fas fa-info-circle"></i> Route Summary</h4>
                    <div class="route-stats">
                        <div class="stat-item">
                            <div class="stat-value" id="route-distance">0</div>
                            <div class="stat-label">Total Distance</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="route-duration">0</div>
                            <div class="stat-label">Est. Time</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="fuel-cost">$0</div>
                            <div class="stat-label">Est. Fuel Cost</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="fuel-efficiency">0</div>
                            <div class="stat-label">MPG</div>
                        </div>
                    </div>
                </div>

                <div id="efficiency-alert"></div>
            </div>
        </div>

        <!-- Fuel Stations Section -->
        <div class="sidebar-section">
            <h3><i class="fas fa-gas-pump"></i> Fuel Pumps Along Route</h3>
            <div id="fuel-stations" class="fuel-stations">
                <p style="color: #718096; text-align: center;">Plan a route to see fuel pumps along the way</p>
            </div>
        </div>

        <!-- Turn-by-Turn Instructions -->
        <div class="sidebar-section">
            <h3><i class="fas fa-directions"></i> Turn-by-Turn Directions</h3>
            <div id="instructions" class="instructions">
                <p style="color: #718096; text-align: center;">Add at least 2 stops to see directions</p>
            </div>
        </div>
    </div>

    <div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
class SmartFleetMapper {
    constructor() {
        this.GEOAPIFY_API_KEY = '<?php echo $GEOAPIFY_API_KEY; ?>';
        this.OPENROUTER_API_KEY = '<?php echo $OPENROUTER_API_KEY; ?>';
        this.map = null;
        this.markerGroup = null;
        this.routeLine = null;
        this.stops = [];
        this.fuelStations = [];
        this.routeFuelStations = [];
        this.currentRoute = null;
        this.manualPinEnabled = true;
        
        this.vehicleConfig = {
            avgMPG: 25,
            fuelCapacity: 20,
            fuelPrice: 3.50,
            vehicleType: 'delivery_van',
        };
        
        this.init();
    }

    init() {
        this.initMap();
        this.bindEvents();
    }

    initMap() {
        // Start with Jodhpur, India as default view
        this.map = L.map('map').setView([26.2389, 73.0243], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(this.map);
        
        this.markerGroup = L.layerGroup().addTo(this.map);
        
        // Add click event for manual pin placement
        this.map.on('click', (e) => {
            if (this.manualPinEnabled) {
                const { lat, lng } = e.latlng;
                const customName = prompt(
                    'Enter name for the new stop:', 
                    `Custom Stop ${this.stops.length + 1}`
                );
                if (customName && customName.trim()) {
                    this.addStop(customName.trim(), lat, lng, { manual: true });
                }
            }
        });
    }

    bindEvents() {
        const searchInput = document.getElementById('search-input');
        const suggestions = document.getElementById('suggestions');
        let searchTimeout = null;

        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const query = searchInput.value.trim();
            
            if (query.length < 2) {
                suggestions.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                this.searchPlaces(query);
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.style.display = 'none';
            }
        });

        document.getElementById('enable-manual-pin').addEventListener('click', () => {
            this.toggleManualPinMode();
        });

        document.getElementById('ai-optimize-route').addEventListener('click', () => {
            this.aiOptimizeRoute();
        });

        document.getElementById('optimize-route').addEventListener('click', () => {
            this.optimizeRoute();
        });

        document.getElementById('clear-all').addEventListener('click', () => {
            this.clearAllStops();
        });
    }

    showLoadingOverlay(message) {
        // Remove any existing overlay
        this.hideLoadingOverlay();
        
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-spinner"></div>
            <div style="margin-bottom: 10px; font-weight: 600;">${message}</div>
            <div style="font-size: 14px; opacity: 0.8;">Please wait while we process your request...</div>
        `;
        document.body.appendChild(overlay);
    }

    hideLoadingOverlay() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            document.body.removeChild(overlay);
        }
    }

    toggleManualPinMode() {
        this.manualPinEnabled = !this.manualPinEnabled;
        const btn = document.getElementById('enable-manual-pin');
        
        if (this.manualPinEnabled) {
            btn.innerHTML = '<i class="fas fa-map-pin"></i> Click to Pin';
            btn.className = 'btn btn-secondary btn-sm';
            this.map.getContainer().style.cursor = 'crosshair';
            this.showAlert('Manual pin mode enabled. Click anywhere on the map to add stops.', 'info');
        } else {
            btn.innerHTML = '<i class="fas fa-hand-pointer"></i> Pan Mode';
            btn.className = 'btn btn-primary btn-sm';
            this.map.getContainer().style.cursor = '';
            this.showAlert('Manual pin mode disabled. Map is now in pan mode.', 'info');
        }
    }

    async searchPlaces(query) {
        try {
            const response = await fetch(
                `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(query)}&limit=5&apiKey=${this.GEOAPIFY_API_KEY}`
            );
            const data = await response.json();
            this.displaySuggestions(data.features || []);
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    displaySuggestions(features) {
        const suggestions = document.getElementById('suggestions');
        suggestions.innerHTML = '';

        if (features.length === 0) {
            suggestions.style.display = 'none';
            return;
        }

        features.forEach(feature => {
            const div = document.createElement('div');
            div.className = 'suggestion-item';
            div.innerHTML = `
                <div style="font-weight: 500;">${feature.properties.name || 'Unknown'}</div>
                <div style="font-size: 12px; color: #718096;">${feature.properties.formatted}</div>
            `;
            div.onclick = () => {
                this.addStop(
                    feature.properties.formatted,
                    feature.properties.lat,
                    feature.properties.lon,
                    feature.properties
                );
                document.getElementById('search-input').value = '';
                suggestions.style.display = 'none';
            };
            suggestions.appendChild(div);
        });

        suggestions.style.display = 'block';
    }

    addStop(name, lat, lon, properties = {}) {
        const stop = {
            id: Date.now(),
            name: name,
            lat: lat,
            lon: lon,
            properties: properties,
            order: this.stops.length + 1
        };

        this.stops.push(stop);
        this.updateStopsList();
        this.updateMarkers();
        this.calculateRoute();
        this.updateStats();
        
        if (properties.manual) {
            this.showAlert(`Added custom stop: ${name}`, 'success');
        }
    }

    removeStop(stopId) {
        this.stops = this.stops.filter(stop => stop.id !== stopId);
        this.updateStopsList();
        this.updateMarkers();
        this.calculateRoute();
        this.updateStats();
    }

    clearAllStops() {
        this.stops = [];
        this.fuelStations = [];
        this.routeFuelStations = [];
        this.updateStopsList();
        this.updateMarkers();
        this.clearRoute();
        this.updateStats();
        document.getElementById('route-info').style.display = 'none';
        document.getElementById('ai-suggestions-section').style.display = 'none';
        document.getElementById('fuel-stations').innerHTML = '<p style="color: #718096; text-align: center;">Plan a route to see fuel pumps along the way</p>';
    }

    async aiOptimizeRoute() {
        if (this.stops.length < 3) {
            this.showAlert('Need at least 3 stops for AI optimization', 'warning');
            return;
        }

        // Show loading overlay
        this.showLoadingOverlay('🧠 AI Route Optimization in Progress');

        try {
            const originalDistance = this.currentRoute ? 
                (this.currentRoute.properties.distance / 1000).toFixed(2) : 0;

            const optimizedOrder = await this.getAIRouteOptimization();
            
            if (optimizedOrder && Array.isArray(optimizedOrder)) {
                await this.applyAIOptimization(optimizedOrder, originalDistance);
            } else {
                throw new Error('AI returned invalid optimization order');
            }

        } catch (error) {
            console.error('AI optimization error:', error);
            this.showAlert(`AI optimization failed: ${error.message}`, 'error');
        } finally {
            // Hide loading overlay
            this.hideLoadingOverlay();
        }
    }

    async getAIRouteOptimization() {
        const stopsInfo = this.stops.map((stop, index) => ({
            id: index + 1,
            name: stop.name,
            lat: stop.lat.toFixed(6),
            lon: stop.lon.toFixed(6)
        }));

        const prompt = `Optimize route order for shortest distance:

${stopsInfo.map(stop => `${stop.id}: ${stop.name} (${stop.lat}, ${stop.lon})`).join('\n')}

Return JSON array with optimal order: [1,3,2,4]`;

        const requestBody = {
            model: 'google/gemini-2.5-pro',
            messages: [
                {
                    role: 'system',
                    content: 'You are a route optimizer. Return ONLY a JSON array of stop numbers in optimal order.'
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
            throw new Error(`API Error ${response.status}: ${errorText}`);
        }

        const data = await response.json();

        if (!data.choices || !data.choices[0] || !data.choices[0].message) {
            throw new Error('Invalid API response structure');
        }

        const aiResponse = data.choices[0].message.content.trim();
        console.log('Raw AI Response:', aiResponse);
        
        return this.extractOptimalOrder(aiResponse);
    }

    extractOptimalOrder(aiResponse) {
        const arrayMatch = aiResponse.match(/\[[\d,\s]+\]/);
        if (arrayMatch) {
            try {
                const parsed = JSON.parse(arrayMatch[0]);
                if (Array.isArray(parsed) && parsed.length === this.stops.length) {
                    console.log('Strategy 1 success:', parsed);
                    return parsed;
                }
            } catch (e) {
                console.log('Strategy 1 failed:', e.message);
            }
        }

        const numbers = aiResponse.match(/\d+/g);
        if (numbers && numbers.length === this.stops.length) {
            const order = numbers.map(n => parseInt(n)).filter(n => n >= 1 && n <= this.stops.length);
            if (order.length === this.stops.length && new Set(order).size === this.stops.length) {
                console.log('Strategy 2 success:', order);
                return order;
            }
        }

        console.log('All extraction strategies failed, using fallback optimization');
        return this.createFallbackOptimization();
    }

    createFallbackOptimization() {
        if (this.stops.length <= 2) return [1, 2];
        
        const unvisited = [...Array(this.stops.length).keys()].map(i => i + 1);
        const optimized = [];
        
        let current = 1;
        optimized.push(current);
        unvisited.splice(0, 1);
        
        while (unvisited.length > 0) {
            let nearestIndex = 0;
            let shortestDistance = Infinity;
            
            const currentStop = this.stops[current - 1];
            
            unvisited.forEach((stopId, index) => {
                const candidateStop = this.stops[stopId - 1];
                const distance = this.calculateDistance(
                    currentStop.lat, currentStop.lon,
                    candidateStop.lat, candidateStop.lon
                );
                
                if (distance < shortestDistance) {
                    shortestDistance = distance;
                    nearestIndex = index;
                }
            });
            
            current = unvisited[nearestIndex];
            optimized.push(current);
            unvisited.splice(nearestIndex, 1);
        }
        
        console.log('Fallback optimization result:', optimized);
        return optimized;
    }

    async applyAIOptimization(optimizedOrder, originalDistance) {
        if (!Array.isArray(optimizedOrder) || optimizedOrder.length !== this.stops.length) {
            throw new Error(`Invalid order length: expected ${this.stops.length}, got ${optimizedOrder.length}`);
        }

        const expectedIds = Array.from({length: this.stops.length}, (_, i) => i + 1);
        const hasAllIds = expectedIds.every(id => optimizedOrder.includes(id));
        
        if (!hasAllIds) {
            throw new Error('Missing or invalid stop IDs in optimization');
        }

        const reorderedStops = optimizedOrder.map(id => this.stops[id - 1]);
        
        this.stops = reorderedStops;
        this.updateStopsList();
        this.updateMarkers();
        
        await this.calculateRoute();
        
        const newDistance = this.currentRoute ? 
            (this.currentRoute.properties.distance / 1000).toFixed(2) : 0;
        
        const distanceSaved = originalDistance ? 
            (parseFloat(originalDistance) - parseFloat(newDistance)).toFixed(2) : 0;
            
        let suggestionHtml = `<strong>🚀 Route Optimized by AI!</strong><br><br>`;
        suggestionHtml += `<strong>📏 Original:</strong> ${originalDistance} km<br>`;
        suggestionHtml += `<strong>📏 Optimized:</strong> ${newDistance} km<br>`;
        
        if (distanceSaved > 0) {
            suggestionHtml += `<strong>💾 Distance Saved:</strong> ${distanceSaved} km<br>`;
            const fuelSavings = (distanceSaved * 0.621371 / this.vehicleConfig.avgMPG * this.vehicleConfig.fuelPrice);
            suggestionHtml += `<strong>⛽ Fuel Savings:</strong> $${fuelSavings.toFixed(2)}`;
        } else if (distanceSaved < 0) {
            suggestionHtml += `<strong>📊 Distance Change:</strong> +${Math.abs(distanceSaved)} km<br>`;
            suggestionHtml += `<strong>ℹ️ Note:</strong> Original route was already well optimized`;
        } else {
            suggestionHtml += `<strong>✅ Route confirmed optimal!</strong>`;
        }
        
        this.displayAISuggestions({ customMessage: suggestionHtml });
        this.showAlert('Route successfully optimized by AI!', 'success');
    }

    displayAISuggestions(aiSuggestion) {
        const suggestionsSection = document.getElementById('ai-suggestions-section');
        const suggestionsText = document.getElementById('ai-suggestion-text');
        
        suggestionsText.innerHTML = aiSuggestion.customMessage || 'AI optimization completed!';
        suggestionsSection.style.display = 'block';
    }

    showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
        
        const sidebar = document.getElementById('sidebar');
        sidebar.insertBefore(alertDiv, sidebar.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    updateStopsList() {
        const stopsList = document.getElementById('stops-list');
        const stopCount = document.getElementById('stop-count');
        
        stopsList.innerHTML = '';
        stopCount.textContent = this.stops.length;

        this.stops.forEach((stop, index) => {
            const li = document.createElement('li');
            li.className = 'stop-item';
            
            const stopIcon = stop.properties.manual ? '📌' : 
                            stop.properties.fuelStation ? '⛽' : '📍';
                            
            li.innerHTML = `
                <div class="stop-info">
                    <div class="stop-name">${stopIcon} ${index + 1}. ${stop.name}</div>
                    <div class="stop-details">${stop.lat.toFixed(4)}, ${stop.lon.toFixed(4)}</div>
                </div>
                <div class="stop-actions">
                    <button class="btn btn-primary btn-sm" onclick="mapper.moveStopUp(${stop.id})">
                        <i class="fas fa-arrow-up"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="mapper.moveStopDown(${stop.id})">
                        <i class="fas fa-arrow-down"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="mapper.removeStop(${stop.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            stopsList.appendChild(li);
        });
    }

    moveStopUp(stopId) {
        const index = this.stops.findIndex(stop => stop.id === stopId);
        if (index > 0) {
            [this.stops[index], this.stops[index - 1]] = [this.stops[index - 1], this.stops[index]];
            this.updateStopsList();
            this.updateMarkers();
            this.calculateRoute();
        }
    }

    moveStopDown(stopId) {
        const index = this.stops.findIndex(stop => stop.id === stopId);
        if (index < this.stops.length - 1) {
            [this.stops[index], this.stops[index + 1]] = [this.stops[index + 1], this.stops[index]];
            this.updateStopsList();
            this.updateMarkers();
            this.calculateRoute();
        }
    }

    // Clean up station names for better display
    cleanStationName(name) {
        if (!name) return 'Fuel Pump';
        // Remove common unnecessary prefixes/suffixes
        return name.replace(/^(Fuel Station|Gas Station|Petrol Station)\s*/i, '')
                   .replace(/\s*(Fuel Station|Gas Station|Petrol Station)$/i, '')
                   .trim() || 'Fuel Pump';
    }

    // Determine available fuel types based on properties
    determineFuelTypes(properties) {
        const fuelTypes = [];
        if (properties.fuel_diesel === 'yes') fuelTypes.push('Diesel');
        if (properties.fuel_gasoline === 'yes' || properties.fuel_petrol === 'yes') fuelTypes.push('Petrol');
        if (properties.fuel_lpg === 'yes') fuelTypes.push('LPG');
        if (properties.fuel_e85 === 'yes') fuelTypes.push('E85');
        if (properties.fuel_electric === 'yes') fuelTypes.push('Electric');
        return fuelTypes.length > 0 ? fuelTypes.join(', ') : 'Fuel Available';
    }

    updateMarkers() {
        this.markerGroup.clearLayers();

        this.stops.forEach((stop, index) => {
            const isStart = index === 0;
            const isEnd = index === this.stops.length - 1;
            const isManual = stop.properties.manual;
            const isFuelStation = stop.properties.fuelStation;
            
            let bgColor = '#667eea'; // Default blue
            if (isStart) bgColor = '#38a169'; // Green for start
            if (isEnd) bgColor = '#e53e3e'; // Red for end
            if (isManual) bgColor = '#805ad5'; // Purple for manual pins
            if (isFuelStation) bgColor = '#e74c3c'; // Red for fuel stations
            
            const icon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background: ${bgColor}; 
                              color: white; width: 30px; height: 30px; border-radius: 50%; 
                              display: flex; align-items: center; justify-content: center; 
                              font-weight: bold; font-size: 12px; border: 2px solid white; 
                              box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${index + 1}</div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15],
                popupAnchor: [0, -15]
            });

            const stopType = isManual ? 'Manual Pin' : 
                            isFuelStation ? 'Fuel Station' : 'Search Result';

            const marker = L.marker([stop.lat, stop.lon], { icon: icon })
                .bindPopup(`
                    <div style="font-weight: bold; margin-bottom: 5px;">${stop.name}</div>
                    <div style="font-size: 12px; color: #666;">
                        <div><strong>Type:</strong> ${stopType}</div>
                        <div><strong>Position:</strong> Stop ${index + 1} of ${this.stops.length}</div>
                        <div><strong>Coordinates:</strong> ${stop.lat.toFixed(4)}, ${stop.lon.toFixed(4)}</div>
                    </div>
                `);

            this.markerGroup.addLayer(marker);
        });

        // Add fuel station markers with improved icons
        this.routeFuelStations.forEach(station => {
            const fuelIcon = L.divIcon({
                className: 'route-fuel-marker',
                html: `<div style="
                    background: #e74c3c;
                    color: white;
                    width: 24px;
                    height: 24px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border: 2px solid white;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                "><i class="fas fa-gas-pump" style="font-size: 10px;"></i></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            const displayName = station.brand && station.brand !== station.name ? 
                               `${station.brand}` : this.cleanStationName(station.name);

            const marker = L.marker([station.lat, station.lon], { icon: fuelIcon })
                .bindPopup(`
                    <div style="font-weight: bold; margin-bottom: 5px;">${displayName}</div>
                    <div style="font-size: 12px;">
                        <div><strong>Price:</strong> $${station.price.toFixed(2)}/gal</div>
                        <div><strong>Distance from route:</strong> ${station.distanceFromRoute.toFixed(1)} km</div>
                        ${station.fuelType ? `<div><strong>Fuel Type:</strong> ${station.fuelType}</div>` : ''}
                        ${station.address ? `<div><strong>Address:</strong> ${station.address}</div>` : ''}
                        <div style="margin-top: 8px;">
                            <button onclick="mapper.addStop('⛽ ${displayName.replace(/'/g, "\\'")}', ${station.lat}, ${station.lon}, { fuelStation: true })" 
                                    style="padding: 4px 8px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                Add as Stop
                            </button>
                        </div>
                    </div>
                `);

            this.markerGroup.addLayer(marker);
        });

        if (this.stops.length > 0) {
            const group = new L.featureGroup(this.markerGroup.getLayers());
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }

    async calculateRoute() {
        if (this.stops.length < 2) {
            this.clearRoute();
            return;
        }

        try {
            const waypoints = this.stops.map(stop => `${stop.lat},${stop.lon}`).join('|');
            const response = await fetch(
                `https://api.geoapify.com/v1/routing?waypoints=${waypoints}&mode=drive&details=instruction_details&apiKey=${this.GEOAPIFY_API_KEY}`
            );
            const data = await response.json();

            if (data.features && data.features.length > 0) {
                this.currentRoute = data.features[0];
                this.drawRoute(this.currentRoute);
                this.updateRouteInfo(this.currentRoute);
                this.showTurnByTurnDirections(this.currentRoute);
                
                // Find fuel stations along the route
                await this.findFuelStationsAlongRoute(this.currentRoute);
            }
        } catch (error) {
            console.error('Route calculation error:', error);
        }
    }

    async findFuelStationsAlongRoute(routeFeature) {
        if (!routeFeature || !routeFeature.geometry) return;
        
        this.routeFuelStations = [];
        
        // Get route coordinates
        let coordinates = [];
        if (routeFeature.geometry.type === 'MultiLineString') {
            routeFeature.geometry.coordinates.forEach(lineString => {
                coordinates = coordinates.concat(lineString);
            });
        } else if (routeFeature.geometry.type === 'LineString') {
            coordinates = routeFeature.geometry.coordinates;
        }

        if (coordinates.length === 0) return;

        // Sample more points along the route for better coverage
        const samplePoints = [];
        const totalPoints = coordinates.length;
        const maxSamples = 20; // Increased from 15 for better fuel pump detection
        const step = Math.max(1, Math.floor(totalPoints / maxSamples));
        
        // Sample points evenly distributed along the route
        for (let i = 0; i < totalPoints; i += step) {
            samplePoints.push(coordinates[i]);
        }
        
        // Also add the last point if not already included
        if (samplePoints[samplePoints.length - 1] !== coordinates[totalPoints - 1]) {
            samplePoints.push(coordinates[totalPoints - 1]);
        }

        const fuelStationsSet = new Map(); // Use Map to avoid duplicates
        const radius = 1000; // Reduced to 1km for more accurate "roadside" results
        
        // Show loading indicator
        const fuelStationsDiv = document.getElementById('fuel-stations');
        fuelStationsDiv.innerHTML = '<p style="color: #718096; text-align: center;"><i class="fas fa-spinner fa-spin"></i> Finding fuel pumps along your route...</p>';
        
        console.log(`Sampling ${samplePoints.length} points along the route for fuel stations...`);
        
        // Fetch fuel stations near each sample point
        for (let i = 0; i < samplePoints.length; i++) {
            const coord = samplePoints[i];
            const [lon, lat] = coord;
            
            try {
                // Use specific fuel category for better accuracy
                const response = await fetch(
                    `https://api.geoapify.com/v2/places?categories=fuel&filter=circle:${lon},${lat},${radius}&limit=10&apiKey=${this.GEOAPIFY_API_KEY}`
                );
                
                if (!response.ok) {
                    console.error(`API error at point ${i}: ${response.status}`);
                    continue;
                }
                
                const data = await response.json();
                console.log(`Point ${i}: Found ${data.features?.length || 0} fuel stations`);
                
                (data.features || []).forEach(feature => {
                    const stationId = feature.properties.place_id || 
                                   feature.properties.osm_id || 
                                   `${feature.properties.lat}_${feature.properties.lon}`;
                    
                    if (!fuelStationsSet.has(stationId)) {
                        const distFromRoute = this.calculateDistanceToRoute(
                            feature.properties.lat,
                            feature.properties.lon,
                            coordinates
                        );
                        
                        // Only add stations that are very close to the route (within 1.5km)
                        if (distFromRoute <= 1.5) {
                            const station = {
                                name: this.cleanStationName(feature.properties.name || 'Fuel Station'),
                                lat: feature.properties.lat,
                                lon: feature.properties.lon,
                                price: this.generateRandomPrice(),
                                distanceFromRoute: distFromRoute,
                                // Enhanced properties for better display
                                brand: feature.properties.brand || feature.properties.operator || '',
                                address: feature.properties.address_line2 || feature.properties.street || '',
                                amenity: feature.properties.amenity || '',
                                // Check for additional fuel-related tags
                                fuelType: this.determineFuelTypes(feature.properties)
                            };
                            
                            fuelStationsSet.set(stationId, station);
                            this.routeFuelStations.push(station);
                        }
                    }
                });
                
                // Smaller delay to prevent rate limiting while being more responsive
                await new Promise(resolve => setTimeout(resolve, 80));
                
            } catch (error) {
                console.error(`Error fetching fuel stations at point ${i}:`, error);
            }
        }
        
        // Remove duplicates and sort by distance from route
        this.routeFuelStations = Array.from(fuelStationsSet.values());
        this.routeFuelStations.sort((a, b) => a.distanceFromRoute - b.distanceFromRoute);
        
        console.log(`Total unique fuel stations found: ${this.routeFuelStations.length}`);
        
        // Display fuel stations and update markers
        this.displayRouteFuelStations();
        this.updateMarkers();
    }

    calculateDistanceToRoute(lat, lon, routeCoordinates) {
        let minDistance = Infinity;
        
        routeCoordinates.forEach(coord => {
            const distance = this.calculateDistance(lat, lon, coord[1], coord[0]);
            if (distance < minDistance) {
                minDistance = distance;
            }
        });
        
        return minDistance;
    }

    displayRouteFuelStations() {
        const fuelStationsDiv = document.getElementById('fuel-stations');
        
        fuelStationsDiv.innerHTML = '';
        
        if (this.routeFuelStations.length === 0) {
            fuelStationsDiv.innerHTML = '<p style="color: #718096; text-align: center;">No fuel pumps found along the route</p>';
            return;
        }
        
        const title = document.createElement('div');
        title.innerHTML = `<strong><i class="fas fa-gas-pump"></i> Found ${this.routeFuelStations.length} fuel pumps along route</strong>`;
        title.style.marginBottom = '10px';
        title.style.color = '#2d3748';
        title.style.fontSize = '14px';
        fuelStationsDiv.appendChild(title);
        
        // Show fuel stations with enhanced information
        this.routeFuelStations.slice(0, 12).forEach((station, index) => {
            const div = document.createElement('div');
            div.className = 'fuel-station route-station';
            
            const displayName = station.brand && station.brand !== station.name ? 
                               `${station.brand}` : station.name;
            
            div.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <div style="font-weight: 500; display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-gas-pump" style="color: #e74c3c; font-size: 12px;"></i>
                            ${displayName}
                            <span class="route-fuel-tag">ROADSIDE</span>
                        </div>
                        <div style="font-size: 11px; color: #666; margin-top: 2px;">
                            ${station.distanceFromRoute.toFixed(1)} km from route
                            ${station.fuelType ? ' • ' + station.fuelType : ''}
                        </div>
                        ${station.address ? `<div style="font-size: 10px; color: #888;">${station.address}</div>` : ''}
                    </div>
                    <div class="fuel-price" style="text-align: right;">
                        <div style="font-weight: 600; color: #2b6cb0;">$${station.price.toFixed(2)}</div>
                        <div style="font-size: 10px; color: #666;">per gal</div>
                    </div>
                </div>
            `;
            
            div.onclick = () => {
                const confirmAdd = confirm(`Add "${displayName}" fuel pump as a stop?`);
                if (confirmAdd) {
                    this.addStop(`⛽ ${displayName}`, station.lat, station.lon, { fuelStation: true });
                    this.showAlert(`Added fuel pump: ${displayName}`, 'success');
                }
            };
            
            fuelStationsDiv.appendChild(div);
        });
        
        // Add "show more" option if there are many stations
        if (this.routeFuelStations.length > 12) {
            const showMoreDiv = document.createElement('div');
            showMoreDiv.innerHTML = `<p style="text-align: center; color: #667eea; cursor: pointer; padding: 10px; font-size: 12px;"><i class="fas fa-chevron-down"></i> Show ${this.routeFuelStations.length - 12} more fuel pumps</p>`;
            showMoreDiv.onclick = () => {
                this.displayAllRouteFuelStations();
            };
            fuelStationsDiv.appendChild(showMoreDiv);
        }
    }

    displayAllRouteFuelStations() {
        const fuelStationsDiv = document.getElementById('fuel-stations');
        
        fuelStationsDiv.innerHTML = '';
        
        const title = document.createElement('div');
        title.innerHTML = `<strong><i class="fas fa-gas-pump"></i> All ${this.routeFuelStations.length} fuel pumps along route</strong>`;
        title.style.marginBottom = '10px';
        title.style.color = '#2d3748';
        title.style.fontSize = '14px';
        fuelStationsDiv.appendChild(title);
        
        this.routeFuelStations.forEach((station, index) => {
            const div = document.createElement('div');
            div.className = 'fuel-station route-station';
            
            const displayName = station.brand && station.brand !== station.name ? 
                               `${station.brand}` : station.name;
            
            div.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <div style="font-weight: 500; display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-gas-pump" style="color: #e74c3c; font-size: 12px;"></i>
                            ${displayName}
                            <span class="route-fuel-tag">ROADSIDE</span>
                        </div>
                        <div style="font-size: 11px; color: #666; margin-top: 2px;">
                            ${station.distanceFromRoute.toFixed(1)} km from route
                            ${station.fuelType ? ' • ' + station.fuelType : ''}
                        </div>
                        ${station.address ? `<div style="font-size: 10px; color: #888;">${station.address}</div>` : ''}
                    </div>
                    <div class="fuel-price" style="text-align: right;">
                        <div style="font-weight: 600; color: #2b6cb0;">$${station.price.toFixed(2)}</div>
                        <div style="font-size: 10px; color: #666;">per gal</div>
                    </div>
                </div>
            `;
            
            div.onclick = () => {
                const confirmAdd = confirm(`Add "${displayName}" fuel pump as a stop?`);
                if (confirmAdd) {
                    this.addStop(`⛽ ${displayName}`, station.lat, station.lon, { fuelStation: true });
                    this.showAlert(`Added fuel pump: ${displayName}`, 'success');
                }
            };
            
            fuelStationsDiv.appendChild(div);
        });
    }

    async optimizeRoute() {
        if (this.stops.length < 3) {
            alert('Need at least 3 stops to optimize route');
            return;
        }

        try {
            const waypoints = this.stops.map(stop => `${stop.lat},${stop.lon}`).join('|');
            const response = await fetch(
                `https://api.geoapify.com/v1/routing?waypoints=${waypoints}&mode=drive&details=instruction_details&waypoints_order=optimized&apiKey=${this.GEOAPIFY_API_KEY}`
            );
            const data = await response.json();

            if (data.features && data.features.length > 0) {
                if (data.features[0].properties.waypoints) {
                    const optimizedOrder = data.features[0].properties.waypoints.map(wp => wp.original_index);
                    const optimizedStops = optimizedOrder.map(index => this.stops[index]);
                    this.stops = optimizedStops;
                    
                    this.updateStopsList();
                    this.updateMarkers();
                    this.calculateRoute();
                    this.showAlert('Route optimized successfully!', 'success');
                } else {
                    this.currentRoute = data.features[0];
                    this.drawRoute(this.currentRoute);
                    this.updateRouteInfo(this.currentRoute);
                    this.showTurnByTurnDirections(this.currentRoute);
                }
            }
        } catch (error) {
            console.error('Route optimization error:', error);
        }
    }

    drawRoute(routeFeature) {
        this.clearRoute();

        let coordinates = [];
        
        if (routeFeature.geometry.type === 'MultiLineString') {
            routeFeature.geometry.coordinates.forEach(lineString => {
                lineString.forEach(coord => {
                    coordinates.push([coord[1], coord[0]]);
                });
            });
        } else if (routeFeature.geometry.type === 'LineString') {
            coordinates = routeFeature.geometry.coordinates.map(coord => [coord[1], coord[0]]);
        } else {
            console.error('Unsupported geometry type:', routeFeature.geometry.type);
            return;
        }
        
        this.routeLine = L.polyline(coordinates, {
            color: '#667eea',
            weight: 5,
            opacity: 0.8,
            dashArray: '10,5'
        }).addTo(this.map);

        this.map.fitBounds(this.routeLine.getBounds().pad(0.1));
    }

    clearRoute() {
        if (this.routeLine) {
            this.map.removeLayer(this.routeLine);
            this.routeLine = null;
        }
        this.routeFuelStations = [];
        document.getElementById('instructions').innerHTML = '<p style="color: #718096; text-align: center;">Add at least 2 stops to see directions</p>';
    }

    updateRouteInfo(routeFeature) {
        const properties = routeFeature.properties;
        const distance = (properties.distance / 1000).toFixed(1);
        const duration = Math.round(properties.time / 60);
        
        const distanceMiles = distance * 0.621371;
        const fuelNeeded = distanceMiles / this.vehicleConfig.avgMPG;
        const fuelCost = fuelNeeded * this.vehicleConfig.fuelPrice;
        const efficiency = this.calculateEfficiency(distanceMiles, duration);

        document.getElementById('route-distance').textContent = `${distance} km`;
        document.getElementById('route-duration').textContent = `${duration} min`;
        document.getElementById('fuel-cost').textContent = `$${fuelCost.toFixed(2)}`;
        document.getElementById('fuel-efficiency').innerHTML = `${this.vehicleConfig.avgMPG} <span style="font-size:12px;">MPG</span>`;
        
        document.getElementById('route-info').style.display = 'block';
        this.showEfficiencyAlert(efficiency, fuelCost, distance);
    }

    calculateEfficiency(distanceMiles, durationMinutes) {
        const avgSpeed = distanceMiles / (durationMinutes / 60);
        
        if (avgSpeed < 25 || avgSpeed > 65) {
            return 'poor';
        } else if (avgSpeed < 35 || avgSpeed > 55) {
            return 'average';
        } else {
            return 'good';
        }
    }

    showEfficiencyAlert(efficiency, fuelCost, distance) {
        const alertDiv = document.getElementById('efficiency-alert');
        
        if (efficiency === 'poor' || fuelCost > 50) {
            alertDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Efficiency Alert:</strong> This route may consume more fuel than optimal.
                </div>
            `;
        } else {
            alertDiv.innerHTML = `
                <div class="efficiency-indicator efficiency-${efficiency}">
                    <i class="fas fa-leaf"></i> ${efficiency.charAt(0).toUpperCase() + efficiency.slice(1)} Efficiency
                </div>
            `;
        }
    }

    showTurnByTurnDirections(routeFeature) {
        const instructionsDiv = document.getElementById('instructions');
        const legs = routeFeature.properties.legs;
        
        let instructionsHtml = '';
        
        legs.forEach((leg, legIndex) => {
            leg.steps.forEach((step, stepIndex) => {
                const distance = (step.distance / 1000).toFixed(1);
                instructionsHtml += `
                    <div class="instruction-step">
                        <div>${step.instruction.text}</div>
                        <div class="step-distance">${distance} km</div>
                    </div>
                `;
            });
        });
        
        instructionsDiv.innerHTML = instructionsHtml;
    }

    generateRandomPrice() {
        return 3.20 + Math.random() * 0.60;
    }

    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
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

    updateStats() {
        const totalDistance = this.currentRoute ? 
            (this.currentRoute.properties.distance / 1000).toFixed(1) : 0;
        const totalTime = this.currentRoute ? 
            Math.round(this.currentRoute.properties.time / 60) : 0;
        const totalCost = this.currentRoute ? 
            ((this.currentRoute.properties.distance / 1000) * 0.621371 / this.vehicleConfig.avgMPG * this.vehicleConfig.fuelPrice).toFixed(2) : 0;

        document.getElementById('total-distance').textContent = `${totalDistance} km`;
        document.getElementById('total-time').textContent = `${totalTime} min`;
        document.getElementById('total-fuel-cost').textContent = `$${totalCost}`;
    }
}

// Initialize the mapper
const mapper = new SmartFleetMapper();
</script>

</body>
</html>
