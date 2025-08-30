<?php
// routes-dashboard.php - Fleet Routes Management Dashboard
require_once 'config.php';
session_start();

$GEOAPIFY_API_KEY = '053f0cbc8d894135bd0fdb09c21d1620';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

// Get company_id from session
$company_id = $_SESSION['company_id'] ?? null;
if (!$company_id) {
    header('Location: login.php');
    exit();
}

// Handle AJAX requests for route details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        if ($_GET['action'] === 'get_route_details' && isset($_GET['route_id'])) {
            $result = getRouteDetails($_GET['route_id'], $pdo, $company_id);
            echo json_encode($result);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit();
}

// Fetch all routes for the company
try {
    $routesStmt = $pdo->prepare("
        SELECT r.*, v.vehicle_name, v.make, v.model, d.full_name as driver_name,
               COUNT(rd.id) as destination_count,
               ra.fuel_saved_litres, ra.money_saved, ra.distance_saved_km
        FROM routes r
        LEFT JOIN vehicles v ON r.vehicle_id = v.vehicle_id
        LEFT JOIN drivers d ON r.driver_id = d.driver_id
        LEFT JOIN route_destinations rd ON r.route_id = rd.route_id
        LEFT JOIN route_ai_analysis ra ON r.route_id = ra.route_id
        WHERE r.company_id = ?
        GROUP BY r.id
        ORDER BY r.created_at DESC
    ");
    $routesStmt->execute([$company_id]);
    $routes = $routesStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $routes = [];
}

function getRouteDetails($route_id, $pdo, $company_id) {
    try {
        // Get route basic info
        $routeStmt = $pdo->prepare("
            SELECT r.*, v.vehicle_name, v.make, v.model, v.fuel_efficiency, v.fuel_type,
                   d.full_name as driver_name, d.phone as driver_phone
            FROM routes r
            LEFT JOIN vehicles v ON r.vehicle_id = v.vehicle_id
            LEFT JOIN drivers d ON r.driver_id = d.driver_id
            WHERE r.route_id = ? AND r.company_id = ?
        ");
        $routeStmt->execute([$route_id, $company_id]);
        $route = $routeStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$route) {
            throw new Exception('Route not found');
        }
        
        // Get destinations
        $destStmt = $pdo->prepare("
            SELECT * FROM route_destinations 
            WHERE route_id = ? 
            ORDER BY destination_order ASC
        ");
        $destStmt->execute([$route_id]);
        $destinations = $destStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get recurrence info if applicable
        $recurrence = null;
        if ($route['fleet_type'] === 'recurring') {
            $recStmt = $pdo->prepare("SELECT * FROM route_recurrence WHERE route_id = ?");
            $recStmt->execute([$route_id]);
            $recurrence = $recStmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Get AI analysis if available
        $aiAnalysis = null;
        $aiStmt = $pdo->prepare("SELECT * FROM route_ai_analysis WHERE route_id = ?");
        $aiStmt->execute([$route_id]);
        $aiAnalysis = $aiStmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'route' => $route,
            'destinations' => $destinations,
            'recurrence' => $recurrence,
            'ai_analysis' => $aiAnalysis
        ];
        
    } catch (Exception $e) {
        throw $e;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Fleet Routes Dashboard - Route Management</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }

        .header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-primary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .routes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .route-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .route-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .route-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .route-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .route-title {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .route-id {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
        }

        .route-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-draft {
            background: linear-gradient(135deg, #fef5e7, #fed7aa);
            color: #92400e;
        }

        .status-active {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #166534;
        }

        .status-completed {
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            color: #3730a3;
        }

        .route-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-icon {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .vehicle-icon {
            background: linear-gradient(135deg, #ddd6fe, #c4b5fd);
            color: #5b21b6;
        }

        .driver-icon {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
        }

        .distance-icon {
            background: linear-gradient(135deg, #fecaca, #fca5a5);
            color: #991b1b;
        }

        .time-icon {
            background: linear-gradient(135deg, #bfdbfe, #93c5fd);
            color: #1e40af;
        }

        .info-details h4 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 2px;
        }

        .info-details p {
            font-size: 12px;
            color: #6b7280;
        }

        .route-metrics {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .metric {
            text-align: center;
        }

        .metric-value {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
        }

        .metric-label {
            font-size: 11px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ai-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .route-actions {
            display: flex;
            gap: 10px;
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 12px;
            border-radius: 8px;
        }

        .btn-view {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-view:hover {
            transform: translateY(-1px);
        }

        .btn-edit {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #e53e3e, #c53030);
            color: white;
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            width: 95%;
            max-width: 1200px;
            height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
            transform: translateY(30px);
            transition: transform 0.3s ease;
        }

        .modal.active .modal-content {
            transform: translateY(0);
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .modal-sidebar {
            width: 400px;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            overflow-y: auto;
            padding: 25px;
        }

        .modal-map {
            flex: 1;
            position: relative;
        }

        #route-map {
            width: 100%;
            height: 100%;
        }

        .detail-section {
            margin-bottom: 30px;
        }

        .detail-section h3 {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item {
            background: white;
            padding: 15px;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }

        .detail-item h4 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .detail-item p {
            font-size: 13px;
            color: #6b7280;
        }

        .destinations-list {
            list-style: none;
        }

        .destination-item {
            background: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 10px;
            border-left: 4px solid #48bb78;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .destination-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .destination-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 3px;
        }

        .destination-info p {
            font-size: 12px;
            color: #718096;
        }

        .ai-analysis-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .ai-analysis-card h3 {
            color: white;
            margin-bottom: 15px;
        }

        .savings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 15px;
        }

        .savings-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
        }

        .savings-value {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .savings-label {
            font-size: 11px;
            opacity: 0.9;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #4a5568;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .routes-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 100%;
                height: 100vh;
                border-radius: 0;
            }

            .modal-body {
                flex-direction: column;
            }

            .modal-sidebar {
                width: 100%;
                height: 40%;
            }

            .modal-map {
                height: 60%;
            }

            .route-info {
                grid-template-columns: 1fr;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-content">
        <h1><i class="fas fa-route"></i> Fleet Routes Dashboard</h1>
        <div class="header-actions">
            <a href="fleet-route-manager.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Route
            </a>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>
    </div>
</div>

<div class="container">
    <?php if (empty($routes)): ?>
        <div class="empty-state">
            <i class="fas fa-route"></i>
            <h3>No Routes Created Yet</h3>
            <p>Start by creating your first optimized route for your fleet</p>
            <a href="fleet-route-manager.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Your First Route
            </a>
        </div>
    <?php else: ?>
        <div class="routes-grid">
            <?php foreach ($routes as $route): ?>
                <div class="route-card" onclick="viewRouteDetails('<?= $route['route_id'] ?>')">
                    <div class="route-header">
                        <div>
                            <h3 class="route-title"><?= htmlspecialchars($route['route_name']) ?></h3>
                            <p class="route-id">Route ID: <?= htmlspecialchars($route['route_id']) ?></p>
                        </div>
                        <span class="route-status status-<?= $route['status'] ?>">
                            <?= ucfirst($route['status']) ?>
                        </span>
                    </div>

                    <div class="route-info">
                        <div class="info-item">
                            <div class="info-icon vehicle-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="info-details">
                                <h4><?= htmlspecialchars($route['vehicle_name']) ?></h4>
                                <p><?= htmlspecialchars($route['make'] . ' ' . $route['model']) ?></p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon driver-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="info-details">
                                <h4><?= htmlspecialchars($route['driver_name']) ?></h4>
                                <p>Assigned Driver</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon distance-icon">
                                <i class="fas fa-road"></i>
                            </div>
                            <div class="info-details">
                                <h4><?= $route['total_distance'] ?> km</h4>
                                <p>Total Distance</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon time-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-details">
                                <h4><?= $route['estimated_duration'] ?> min</h4>
                                <p>Est. Duration</p>
                            </div>
                        </div>
                    </div>

                    <div class="route-metrics">
                        <div class="metric">
                            <div class="metric-value"><?= $route['destination_count'] ?></div>
                            <div class="metric-label">Destinations</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">$<?= number_format($route['estimated_fuel_cost'], 2) ?></div>
                            <div class="metric-label">Fuel Cost</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value"><?= ucfirst($route['fleet_type']) ?></div>
                            <div class="metric-label">Type</div>
                        </div>
                    </div>

                    <?php if ($route['fuel_saved_litres'] > 0): ?>
                        <div class="ai-badge">
                            <i class="fas fa-brain"></i>
                            AI Optimized - Saved <?= number_format($route['fuel_saved_litres'], 1) ?>L
                        </div>
                    <?php endif; ?>

                    <div class="route-actions" onclick="event.stopPropagation()">
                        <button class="btn btn-view btn-sm" onclick="viewRouteDetails('<?= $route['route_id'] ?>')">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        <button class="btn btn-edit btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-delete btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Route Details Modal -->
<div id="route-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Route Details</h2>
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-sidebar">
                <div id="route-details-content">
                    <!-- Route details will be loaded here -->
                </div>
            </div>
            <div class="modal-map">
                <div id="route-map"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
class RouteDashboard {
    constructor() {
        this.GEOAPIFY_API_KEY = '<?php echo $GEOAPIFY_API_KEY; ?>';
        this.map = null;
        this.markerGroup = null;
        this.routeLine = null;
        this.currentRouteData = null;
    }

    async loadRouteDetails(routeId) {
        try {
            const response = await fetch(`?action=get_route_details&route_id=${routeId}`);
            const data = await response.json();
            
            if (data.success) {
                this.currentRouteData = data;
                this.displayRouteDetails(data);
                this.initRouteMap(data);
            } else {
                throw new Error(data.error || 'Failed to load route details');
            }
        } catch (error) {
            console.error('Error loading route details:', error);
            alert('Failed to load route details. Please try again.');
        }
    }

    displayRouteDetails(data) {
        const { route, destinations, recurrence, ai_analysis } = data;
        
        let html = `
            <div class="detail-section">
                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <h4>Route Name</h4>
                        <p>${route.route_name}</p>
                    </div>
                    <div class="detail-item">
                        <h4>Route ID</h4>
                        <p>${route.route_id}</p>
                    </div>
                    <div class="detail-item">
                        <h4>Fleet Type</h4>
                        <p>${route.fleet_type.replace('_', ' ').toUpperCase()}</p>
                    </div>
                    <div class="detail-item">
                        <h4>Status</h4>
                        <p>${route.status.toUpperCase()}</p>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3><i class="fas fa-truck"></i> Vehicle & Driver</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <h4>Vehicle</h4>
                        <p>${route.vehicle_name} - ${route.make} ${route.model}</p>
                    </div>
                    <div class="detail-item">
                        <h4>Driver</h4>
                        <p>${route.driver_name}</p>
                    </div>
                    <div class="detail-item">
                        <h4>Fuel Efficiency</h4>
                        <p>${route.fuel_efficiency} MPG (${route.fuel_type})</p>
                    </div>
                    <div class="detail-item">
                        <h4>Driver Contact</h4>
                        <p>${route.driver_phone || 'N/A'}</p>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3><i class="fas fa-chart-line"></i> Route Metrics</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <h4>Total Distance</h4>
                        <p>${route.total_distance} km</p>
                    </div>
                    <div class="detail-item">
                        <h4>Estimated Duration</h4>
                        <p>${route.estimated_duration} minutes</p>
                    </div>
                    <div class="detail-item">
                        <h4>Estimated Fuel Cost</h4>
                        <p>$${parseFloat(route.estimated_fuel_cost).toFixed(2)}</p>
                    </div>
                    <div class="detail-item">
                        <h4>Destination Type</h4>
                        <p>${route.destination_type === 'single' ? 'Single Destination' : 'Multiple Stops'}</p>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3><i class="fas fa-clock"></i> Timing</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <h4>Departure Time</h4>
                        <p>${route.departure_time || 'Not specified'}</p>
                    </div>
                    <div class="detail-item">
                        <h4>Expected Arrival</h4>
                        <p>${route.arrival_time || 'Not specified'}</p>
                    </div>
                    <div class="detail-item">
                        <h4>Created</h4>
                        <p>${new Date(route.created_at).toLocaleDateString()}</p>
                    </div>
                    <div class="detail-item">
                        <h4>Last Updated</h4>
                        <p>${new Date(route.updated_at).toLocaleDateString()}</p>
                    </div>
                </div>
            </div>
        `;

        // Add recurrence information if applicable
        if (recurrence) {
            html += `
                <div class="detail-section">
                    <h3><i class="fas fa-repeat"></i> Recurrence Pattern</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <h4>Pattern Type</h4>
                            <p>${recurrence.recurrence_type.toUpperCase()}</p>
                        </div>
                        <div class="detail-item">
                            <h4>Interval</h4>
                            <p>Every ${recurrence.recurrence_interval} ${recurrence.recurrence_type}(s)</p>
                        </div>
                        <div class="detail-item">
                            <h4>Start Date</h4>
                            <p>${recurrence.start_date}</p>
                        </div>
                        <div class="detail-item">
                            <h4>End Date</h4>
                            <p>${recurrence.end_date || 'Ongoing'}</p>
                        </div>
                    </div>
                </div>
            `;
        }

        // Add AI analysis if available
        if (ai_analysis) {
            html += `
                <div class="ai-analysis-card">
                    <h3><i class="fas fa-brain"></i> AI Optimization Results</h3>
                    <div class="savings-grid">
                        <div class="savings-item">
                            <div class="savings-value">${parseFloat(ai_analysis.fuel_saved_litres).toFixed(1)}L</div>
                            <div class="savings-label">Fuel Saved</div>
                        </div>
                        <div class="savings-item">
                            <div class="savings-value">$${parseFloat(ai_analysis.money_saved).toFixed(2)}</div>
                            <div class="savings-label">Money Saved</div>
                        </div>
                        <div class="savings-item">
                            <div class="savings-value">${parseFloat(ai_analysis.distance_saved_km).toFixed(1)}km</div>
                            <div class="savings-label">Distance Saved</div>
                        </div>
                        <div class="savings-item">
                            <div class="savings-value">${new Date(ai_analysis.analysis_timestamp).toLocaleDateString()}</div>
                            <div class="savings-label">Optimized On</div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Add destinations list
        if (destinations && destinations.length > 0) {
            html += `
                <div class="detail-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Destinations (${destinations.length})</h3>
                    <ul class="destinations-list">
            `;
            
            destinations.forEach((dest, index) => {
                const icon = dest.is_manual_pin ? '📌' : '📍';
                html += `
                    <li class="destination-item">
                        <div class="destination-number">${dest.destination_order}</div>
                        <div class="destination-info">
                            <h4>${icon} ${dest.location_name || 'Unnamed Location'}</h4>
                            <p>${dest.address}</p>
                            <p style="font-size: 11px; margin-top: 2px;">
                                Coordinates: ${parseFloat(dest.latitude).toFixed(6)}, ${parseFloat(dest.longitude).toFixed(6)}
                            </p>
                        </div>
                    </li>
                `;
            });
            
            html += `
                    </ul>
                </div>
            `;
        }

        document.getElementById('route-details-content').innerHTML = html;
    }

    initRouteMap(data) {
        // Destroy existing map if it exists
        if (this.map) {
            this.map.remove();
        }

        const { destinations } = data;
        
        if (!destinations || destinations.length === 0) {
            document.getElementById('route-map').innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #718096;">No destinations to display</div>';
            return;
        }

        // Initialize map
        this.map = L.map('route-map');
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(this.map);

        this.markerGroup = L.layerGroup().addTo(this.map);

        // Add markers for each destination
        destinations.forEach((dest, index) => {
            const isStart = index === 0;
            const isEnd = index === destinations.length - 1;
            const isManual = dest.is_manual_pin;
            
            let bgColor = '#667eea';
            if (isStart) bgColor = '#48bb78';
            if (isEnd && destinations.length > 1) bgColor = '#e53e3e';
            if (isManual) bgColor = '#9333ea';
            
            const icon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="
                    background: ${bgColor}; 
                    color: white; 
                    width: 35px; 
                    height: 35px; 
                    border-radius: 50%; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    font-weight: bold; 
                    font-size: 14px; 
                    border: 3px solid white; 
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                    font-family: 'Segoe UI', sans-serif;
                ">${dest.destination_order}</div>`,
                iconSize: [35, 35],
                iconAnchor: [17.5, 17.5],
                popupAnchor: [0, -17.5]
            });

            const marker = L.marker([dest.latitude, dest.longitude], { icon: icon })
                .bindPopup(`
                    <div style="font-weight: bold; margin-bottom: 8px; font-size: 15px;">
                        ${dest.is_manual_pin ? '📌' : '📍'} ${dest.location_name || 'Unnamed Location'}
                    </div>
                    <div style="font-size: 13px; color: #666; margin-bottom: 8px;">
                        <div><strong>Order:</strong> Stop ${dest.destination_order} of ${destinations.length}</div>
                        <div><strong>Type:</strong> ${dest.is_manual_pin ? 'Manual Pin' : 'Search Result'}</div>
                        <div><strong>Address:</strong> ${dest.address}</div>
                    </div>
                `);

            this.markerGroup.addLayer(marker);
        });

        // Draw route if multiple destinations
        if (destinations.length >= 2) {
            this.drawRoute(destinations);
        }

        // Fit map to show all markers
        const group = new L.featureGroup(this.markerGroup.getLayers());
        this.map.fitBounds(group.getBounds().pad(0.1));
    }

    async drawRoute(destinations) {
        try {
            const waypoints = destinations.map(dest => `${dest.latitude},${dest.longitude}`).join('|');
            const response = await fetch(
                `https://api.geoapify.com/v1/routing?waypoints=${waypoints}&mode=drive&details=instruction_details&apiKey=${this.GEOAPIFY_API_KEY}`
            );
            const data = await response.json();

            if (data.features && data.features.length > 0) {
                const routeFeature = data.features[0];
                let coordinates = [];
                
                if (routeFeature.geometry.type === 'MultiLineString') {
                    routeFeature.geometry.coordinates.forEach(lineString => {
                        lineString.forEach(coord => {
                            coordinates.push([coord[1], coord[0]]);
                        });
                    });
                } else if (routeFeature.geometry.type === 'LineString') {
                    coordinates = routeFeature.geometry.coordinates.map(coord => [coord[1], coord[0]]);
                }
                
                if (coordinates.length > 0) {
                    this.routeLine = L.polyline(coordinates, {
                        color: '#667eea',
                        weight: 6,
                        opacity: 0.8,
                        dashArray: '15,10'
                    }).addTo(this.map);
                }
            }
        } catch (error) {
            console.error('Error drawing route:', error);
        }
    }
}

// Initialize dashboard
const dashboard = new RouteDashboard();

// Global functions
function viewRouteDetails(routeId) {
    document.getElementById('route-modal').classList.add('active');
    dashboard.loadRouteDetails(routeId);
}

function closeModal() {
    document.getElementById('route-modal').classList.remove('active');
    if (dashboard.map) {
        setTimeout(() => {
            dashboard.map.remove();
            dashboard.map = null;
        }, 300);
    }
}

// Close modal when clicking outside
document.getElementById('route-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Escape key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

</body>
</html>
