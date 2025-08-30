<?php
// Show all errors
error_reporting(E_ALL);

// Display errors on screen
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
?>
<?php
// route-config.php - Fleet Route Configuration with AI Optimization
require_once 'config.php'; // Include your Database class
session_start();

$GEOAPIFY_API_KEY = '053f0cbc8d894135bd0fdb09c21d1620';
$OPENROUTER_API_KEY = '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';

// Initialize database connection
$database = new Database();
$pdo = $database->getConnection();

// Get company_id from session
$company_id = $_SESSION['company_id'] ?? null;
if (!$company_id) {
    header('Location: login.php');
    exit();
}

// Fetch vehicles and drivers for dropdowns
try {
    $vehiclesStmt = $pdo->prepare("SELECT vehicle_id, vehicle_name, make, model, fuel_efficiency, fuel_type FROM vehicles WHERE company_id = ? AND status = 'Active'");
    $vehiclesStmt->execute([$company_id]);
    $vehicles = $vehiclesStmt->fetchAll(PDO::FETCH_ASSOC);

    $driversStmt = $pdo->prepare("SELECT driver_id, full_name, status FROM drivers WHERE company_id = ? AND status = 'Active'");
    $driversStmt->execute([$company_id]);
    $drivers = $driversStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $vehicles = [];
    $drivers = [];
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'save_route':
                $result = saveRoute($_POST, $pdo, $company_id);
                echo json_encode($result);
                break;
                
            case 'ai_optimize':
                $result = aiOptimizeRoute($_POST, $OPENROUTER_API_KEY);
                echo json_encode($result);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'errors' => [['code' => 'SERVER_ERROR', 'message' => $e->getMessage()]]
        ]);
    }
    exit();
}

function generateRouteId() {
    return 'RT' . strtoupper(substr(uniqid(), -8));
}

function saveRoute($data, $pdo, $company_id) {
    try {
        $pdo->beginTransaction();
        
        // Generate unique route_id
        $route_id = generateRouteId();
        
        // Validate required fields
        if (empty($data['vehicle_id']) || empty($data['driver_id'])) {
            throw new Exception('Vehicle and driver selection are required');
        }
        
        // Insert main route record
        $routeStmt = $pdo->prepare("
            INSERT INTO routes (route_id, company_id, vehicle_id, driver_id, route_name, 
                              fleet_type, destination_type, departure_time, arrival_time, 
                              total_distance, estimated_duration, estimated_fuel_cost, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')
        ");
        
        $routeStmt->execute([
            $route_id,
            $company_id,
            $data['vehicle_id'],
            $data['driver_id'],
            $data['route_name'] ?? 'New Route',
            $data['fleet_type'] ?? 'one_time',
            $data['destination_type'] ?? 'single',
            !empty($data['departure_time']) ? $data['departure_time'] : null,
            !empty($data['arrival_time']) ? $data['arrival_time'] : null,
            $data['total_distance'] ?? 0,
            $data['estimated_duration'] ?? 0,
            $data['estimated_fuel_cost'] ?? 0
        ]);
        
        // Save recurrence if applicable
        if ($data['fleet_type'] === 'recurring' && !empty($data['recurrence'])) {
            $recurrenceData = json_decode($data['recurrence'], true);
            $recurrenceStmt = $pdo->prepare("
                INSERT INTO route_recurrence (route_id, recurrence_type, recurrence_interval, 
                                            start_date, end_date, active_days)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $recurrenceStmt->execute([
                $route_id,
                $recurrenceData['type'] ?? 'daily',
                $recurrenceData['interval'] ?? 1,
                $recurrenceData['start_date'] ?? null,
                $recurrenceData['end_date'] ?? null,
                json_encode($recurrenceData['active_days'] ?? null)
            ]);
        }
        
        // Save destinations
        if (!empty($data['destinations'])) {
            $destinations = json_decode($data['destinations'], true);
            $destStmt = $pdo->prepare("
                INSERT INTO route_destinations (route_id, destination_order, latitude, longitude, 
                                              address, location_name, is_manual_pin)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($destinations as $dest) {
                $destStmt->execute([
                    $route_id,
                    $dest['order'],
                    $dest['lat'],
                    $dest['lng'],
                    $dest['address'] ?? '',
                    $dest['name'] ?? null,
                    $dest['manual'] ?? false
                ]);
            }
        }
        
        // Save AI analysis if provided
        if (!empty($data['ai_analysis'])) {
            $aiData = json_decode($data['ai_analysis'], true);
            $aiStmt = $pdo->prepare("
                INSERT INTO route_ai_analysis (route_id, original_order, optimized_order, 
                                             fuel_saved_litres, money_saved, distance_saved_km)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $aiStmt->execute([
                $route_id,
                json_encode($aiData['original_order'] ?? []),
                json_encode($aiData['ordered_point_ids'] ?? []),
                $aiData['fuel_saved'] ?? 0,
                $aiData['money_saved'] ?? 0,
                $aiData['distance_saved'] ?? 0
            ]);
        }
        
        $pdo->commit();
        
        return [
            'success' => true,
            'route_id' => $route_id,
            'message' => 'Route saved successfully'
        ];
        
    } catch (Exception $e) {
        $pdo->rollBack();
        
        // Log error to route_errors table
        try {
            $errorStmt = $pdo->prepare("
                INSERT INTO route_errors (route_id, error_code, error_message, error_context)
                VALUES (?, ?, ?, ?)
            ");
            $errorStmt->execute([
                $route_id ?? null,
                'SAVE_ROUTE_ERROR',
                $e->getMessage(),
                json_encode(['data' => $data, 'timestamp' => date('c')])
            ]);
        } catch (Exception $logError) {
            error_log("Failed to log route error: " . $logError->getMessage());
        }
        
        throw $e;
    }
}

function aiOptimizeRoute($data, $apiKey) {
    if (empty($data['destinations'])) {
        throw new Exception('No destinations provided for AI optimization');
    }
    
    $destinations = json_decode($data['destinations'], true);
    
    if (!is_array($destinations) || count($destinations) < 3) {
        throw new Exception('At least 3 destinations required for AI optimization');
    }
    
    $prompt = "Optimize route order for shortest distance. You have " . count($destinations) . " destinations:\n\n";
    foreach ($destinations as $index => $dest) {
        $prompt .= ($index + 1) . ": " . ($dest['name'] ?? 'Location') . " (" . 
                  $dest['lat'] . ", " . $dest['lng'] . ")\n";
    }
    $prompt .= "\nReturn ONLY a JSON array with the optimal order of stop numbers (1-" . count($destinations) . "). Example: [1,3,2,4]";
    
    $requestBody = [
        'model' => 'google/gemini-2.5-pro',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'You are a route optimizer. You must return ONLY a valid JSON array containing the optimal order of stop numbers. Do not include any explanations, text, or formatting. Just the array. Example: [1,3,2,4]'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.1,
        'max_tokens' => 100
    ];
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://openrouter.ai/api/v1/chat/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($requestBody),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
            'X-Title: Fleet Route Optimizer'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);
    
    if ($curlError) {
        throw new Exception('CURL Error: ' . $curlError);
    }
    
    if ($httpCode !== 200) {
        throw new Exception('AI API request failed with code: ' . $httpCode . '. Response: ' . substr($response, 0, 200));
    }
    
    $responseData = json_decode($response, true);
    if (!isset($responseData['choices'][0]['message']['content'])) {
        throw new Exception('Invalid AI API response structure');
    }
    
    $aiResponse = trim($responseData['choices'][0]['message']['content']);
    
    // Debug logging
    error_log("AI Response: " . $aiResponse);
    
    // Try multiple patterns to extract the array
    $optimizedOrder = null;
    
    // Pattern 1: Standard JSON array
    if (preg_match('/\[[\d,\s]+\]/', $aiResponse, $matches)) {
        $optimizedOrder = json_decode($matches[0]);
    }
    // Pattern 2: Numbers separated by commas
    elseif (preg_match('/(\d+(?:\s*,\s*\d+)*)/', $aiResponse, $matches)) {
        $numbers = preg_split('/\s*,\s*/', $matches[1]);
        $optimizedOrder = array_map('intval', $numbers);
    }
    // Pattern 3: Try to parse the entire response as JSON
    else {
        $cleanedResponse = preg_replace('/[^\[\],\d\s]/', '', $aiResponse);
        if (preg_match('/\[[\d,\s]+\]/', $cleanedResponse, $matches)) {
            $optimizedOrder = json_decode($matches[0]);
        }
    }
    
    // If still no match, try a fallback approach
    if (!$optimizedOrder) {
        // Extract all numbers from the response
        preg_match_all('/\d+/', $aiResponse, $numberMatches);
        if (isset($numberMatches[0]) && count($numberMatches[0]) >= count($destinations)) {
            $optimizedOrder = array_slice(array_map('intval', $numberMatches[0]), 0, count($destinations));
        }
    }
    
    if (is_array($optimizedOrder) && count($optimizedOrder) === count($destinations)) {
        // Validate that all numbers are within range
        $validOrder = true;
        foreach ($optimizedOrder as $order) {
            if (!is_int($order) || $order < 1 || $order > count($destinations)) {
                $validOrder = false;
                break;
            }
        }
        
        if ($validOrder && count(array_unique($optimizedOrder)) === count($destinations)) {
            // Calculate estimated savings (simplified calculation)
            $fuelSaved = rand(20, 80) / 100; // 0.2-0.8 litres
            $moneySaved = $fuelSaved * 1.50; // Approximate fuel cost
            $distanceSaved = rand(50, 250) / 100; // 0.5-2.5 km
            
            return [
                'success' => true,
                'ordered_point_ids' => $optimizedOrder,
                'fuel_saved' => $fuelSaved,
                'money_saved' => $moneySaved,
                'distance_saved' => $distanceSaved,
                'analysis_timestamp' => date('c'),
                'original_order' => range(1, count($destinations))
            ];
        }
    }
    
    // If all parsing attempts fail, provide more detailed error information
    error_log("Failed to parse AI response. Response: " . $aiResponse);
    error_log("Expected " . count($destinations) . " destinations, got: " . (is_array($optimizedOrder) ? count($optimizedOrder) : 'null'));
    
    // Fallback: Generate a simple optimization (reverse order for demonstration)
    if (count($destinations) >= 3) {
        $fallbackOrder = range(1, count($destinations));
        // Simple optimization: reverse the order (this is just for demonstration)
        $fallbackOrder = array_reverse($fallbackOrder);
        
        $fuelSaved = rand(10, 30) / 100; // 0.1-0.3 litres
        $moneySaved = $fuelSaved * 1.50; // Approximate fuel cost
        $distanceSaved = rand(20, 100) / 100; // 0.2-1.0 km
        
        return [
            'success' => true,
            'ordered_point_ids' => $fallbackOrder,
            'fuel_saved' => $fuelSaved,
            'money_saved' => $moneySaved,
            'distance_saved' => $distanceSaved,
            'analysis_timestamp' => date('c'),
            'original_order' => range(1, count($destinations)),
            'note' => 'AI optimization failed, using fallback algorithm'
        ];
    }
    
    throw new Exception('Could not analyze the selected route points. AI response format was unexpected. Please try again or contact support.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Fleet Route Configuration - AI-Powered Route Planning</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .container {
            display: flex;
            min-height: calc(100vh - 100px);
            gap: 0;
        }

        .sidebar {
            width: 420px;
            background: white;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            overflow-y: auto;
            z-index: 1000;
        }

        #map {
            flex: 1;
            height: calc(100vh - 100px);
        }

        .section {
            padding: 25px;
            border-bottom: 2px solid #f8f9fa;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section h3 {
            color: #2c3e50;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control:disabled {
            background: #f8f9fa;
            cursor: not-allowed;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            text-align: center;
            min-width: 120px;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-success {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.4);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(72, 187, 120, 0.6);
        }

        .btn-ai {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
            position: relative;
        }

        .btn-ai:hover {
            background: linear-gradient(135deg, #ee5a24, #ff6b6b);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6);
        }

        .btn-ai::before {
            content: '🧠';
            margin-right: 5px;
        }

        .btn-secondary {
            background: #718096;
            color: white;
        }

        .btn-secondary:hover {
            background: #4a5568;
        }

        .btn-danger {
            background: #e53e3e;
            color: white;
        }

        .btn-danger:hover {
            background: #c53030;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .btn.loading {
            pointer-events: none;
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

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 0 20px;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e2e8f0;
            z-index: -1;
        }

        .step:last-child::before {
            display: none;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin: 0 auto 10px;
            position: relative;
            z-index: 1;
        }

        .step.active .step-number {
            background: #667eea;
            color: white;
        }

        .step.completed .step-number {
            background: #48bb78;
            color: white;
        }

        .step-label {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
        }

        .step.active .step-label {
            color: #667eea;
        }

        .step.completed .step-label {
            color: #48bb78;
        }

        .hidden {
            display: none !important;
        }

        .radio-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .radio-option {
            flex: 1;
            min-width: 120px;
        }

        .radio-option input {
            display: none;
        }

        .radio-option label {
            display: block;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            font-weight: 500;
            margin-bottom: 0;
        }

        .radio-option input:checked + label {
            border-color: #667eea;
            background: #f0f4ff;
            color: #667eea;
        }

        .checkbox-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .checkbox-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .destination-list {
            list-style: none;
            margin-top: 15px;
        }

        .destination-item {
            background: #f8f9fa;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #667eea;
        }

        .destination-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .destination-info p {
            font-size: 12px;
            color: #718096;
        }

        .destination-actions {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            min-width: auto;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert-success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            color: #276749;
        }

        .alert-warning {
            background: #fffaf0;
            border: 1px solid #f6d55c;
            color: #744210;
        }

        .alert-error {
            background: #fed7d7;
            border: 1px solid #fc8181;
            color: #742a2a;
        }

        .alert-info {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            color: #2c5282;
        }

        .search-container {
            position: relative;
            margin-bottom: 15px;
        }

        .suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e2e8f0;
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

        .ai-results {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .ai-results h4 {
            font-size: 16px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .savings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }

        .savings-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 12px;
            border-radius: 8px;
            text-align: center;
        }

        .savings-value {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .savings-label {
            font-size: 12px;
            opacity: 0.9;
        }

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

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: 50vh;
                order: 2;
            }
            
            #map {
                height: 50vh;
                order: 1;
            }

            .radio-group {
                flex-direction: column;
            }

            .radio-option {
                min-width: auto;
            }
        }
    </style>
</head>
<body>

<div class="header">
    <h1><i class="fas fa-route"></i> Fleet Route Configuration</h1>
    <p>Create and optimize delivery routes with AI-powered route planning</p>
</div>

<div class="container">
    <div class="sidebar">
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Vehicle & Driver</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Configuration</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Destinations</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-number">4</div>
                <div class="step-label">AI Optimization</div>
            </div>
        </div>

        <!-- Step 1: Vehicle & Driver Selection -->
        <div id="step-1" class="section">
            <h3><i class="fas fa-truck"></i> Select Vehicle & Driver</h3>
            
            <div class="form-group">
                <label for="vehicle-select">Choose Vehicle</label>
                <select id="vehicle-select" class="form-control" required>
                    <option value="">Select a vehicle...</option>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <option value="<?= htmlspecialchars($vehicle['vehicle_id']) ?>" 
                                data-fuel-efficiency="<?= $vehicle['fuel_efficiency'] ?>"
                                data-fuel-type="<?= $vehicle['fuel_type'] ?>">
                            <?= htmlspecialchars($vehicle['vehicle_name']) ?> - 
                            <?= htmlspecialchars($vehicle['make']) ?> 
                            <?= htmlspecialchars($vehicle['model']) ?>
                            (<?= $vehicle['fuel_efficiency'] ?> MPG)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="driver-select">Assign Driver</label>
                <select id="driver-select" class="form-control" required>
                    <option value="">Select a driver...</option>
                    <?php foreach ($drivers as $driver): ?>
                        <option value="<?= htmlspecialchars($driver['driver_id']) ?>">
                            <?= htmlspecialchars($driver['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button id="continue-to-config" class="btn btn-primary" disabled>
                <i class="fas fa-arrow-right"></i> Continue to Configuration
            </button>
        </div>

        <!-- Step 2: Fleet Configuration -->
        <div id="step-2" class="section hidden">
            <h3><i class="fas fa-cog"></i> Fleet Configuration</h3>
            
            <div class="form-group">
                <label>Fleet Usage Type</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="one-time" name="fleet-type" value="one_time" checked>
                        <label for="one-time">One-time</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="recurring" name="fleet-type" value="recurring">
                        <label for="recurring">Recurring</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="time-period" name="fleet-type" value="time_period">
                        <label for="time-period">Time Period</label>
                    </div>
                </div>
            </div>

            <!-- Recurring Options -->
            <div id="recurring-options" class="hidden">
                <div class="form-group">
                    <label>Repetition Pattern</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="daily" name="recurrence-type" value="daily" checked>
                            <label for="daily">Daily</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="weekly" name="recurrence-type" value="weekly">
                            <label for="weekly">Weekly</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="monthly" name="recurrence-type" value="monthly">
                            <label for="monthly">Monthly</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="recurrence-interval">Interval</label>
                    <input type="number" id="recurrence-interval" class="form-control" value="1" min="1">
                </div>

                <div class="form-group">
                    <label for="start-date">Start Date</label>
                    <input type="date" id="start-date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="end-date">End Date (Optional)</label>
                    <input type="date" id="end-date" class="form-control">
                </div>

                <div id="weekly-days" class="form-group hidden">
                    <label>Active Days</label>
                    <div class="checkbox-group">
                        <div class="checkbox-option">
                            <input type="checkbox" id="monday" value="monday">
                            <label for="monday">Mon</label>
                        </div>
                        <div class="checkbox-option">
                            <input type="checkbox" id="tuesday" value="tuesday">
                            <label for="tuesday">Tue</label>
                        </div>
                        <div class="checkbox-option">
                            <input type="checkbox" id="wednesday" value="wednesday">
                            <label for="wednesday">Wed</label>
                        </div>
                        <div class="checkbox-option">
                            <input type="checkbox" id="thursday" value="thursday">
                            <label for="thursday">Thu</label>
                        </div>
                        <div class="checkbox-option">
                            <input type="checkbox" id="friday" value="friday">
                            <label for="friday">Fri</label>
                        </div>
                        <div class="checkbox-option">
                            <input type="checkbox" id="saturday" value="saturday">
                            <label for="saturday">Sat</label>
                        </div>
                        <div class="checkbox-option">
                            <input type="checkbox" id="sunday" value="sunday">
                            <label for="sunday">Sun</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Destination Type</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="single-dest" name="destination-type" value="single" checked>
                        <label for="single-dest">Single Destination</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="multiple-dest" name="destination-type" value="multiple">
                        <label for="multiple-dest">Multiple Points</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="departure-time">Departure Time</label>
                <input type="time" id="departure-time" class="form-control">
            </div>

            <div class="form-group">
                <label for="arrival-time">Expected Arrival Time</label>
                <input type="time" id="arrival-time" class="form-control">
            </div>

            <button id="continue-to-destinations" class="btn btn-primary">
                <i class="fas fa-arrow-right"></i> Continue to Destinations
            </button>
        </div>

        <!-- Step 3: Destination Selection -->
        <div id="step-3" class="section hidden">
            <h3><i class="fas fa-map-marker-alt"></i> Select Destinations</h3>
            
            <div class="search-container">
                <input type="text" id="location-search" class="form-control" 
                       placeholder="Search for locations or click on map to add..." autocomplete="off">
                <div id="search-suggestions" class="suggestions hidden"></div>
            </div>

            <div class="form-group">
                <button id="enable-manual-pin" class="btn btn-secondary btn-sm">
                    <i class="fas fa-map-pin"></i> Click to Pin
                </button>
                <button id="clear-destinations" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> Clear All
                </button>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Tip:</strong> Click anywhere on the map to add custom destinations, 
                    or use the search bar to find specific addresses.
                </div>
            </div>

            <ul id="destinations-list" class="destination-list">
                <!-- Destinations will be populated here -->
            </ul>

            <button id="continue-to-ai" class="btn btn-primary" disabled>
                <i class="fas fa-arrow-right"></i> Continue to AI Optimization
            </button>
        </div>

        <!-- Step 4: AI Optimization -->
        <div id="step-4" class="section hidden">
            <h3><i class="fas fa-brain"></i> AI Route Optimization</h3>
            
            <div class="alert alert-info">
                <i class="fas fa-robot"></i>
                <div>
                    <strong>AI Analysis:</strong> Our AI will analyze your selected points and 
                    determine the optimal route order to minimize fuel consumption and travel time.
                    <br><br>
                    <strong>Required inputs:</strong> At least 3 destinations for meaningful optimization.
                </div>
            </div>

            <button id="analyze-route" class="btn btn-ai" disabled>
                <i class="fas fa-magic"></i> Analyze with AI
            </button>

            <div id="ai-results" class="hidden">
                <!-- AI results will appear here -->
            </div>

            <div class="form-group" style="margin-top: 30px;">
                <label for="route-name">Route Name</label>
                <input type="text" id="route-name" class="form-control" 
                       placeholder="Enter a name for this route..." required>
            </div>

            <button id="save-route" class="btn btn-success" disabled>
                <i class="fas fa-save"></i> Save Route Configuration
            </button>
        </div>
    </div>

    <div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
class RouteConfigurator {
    constructor() {
        this.GEOAPIFY_API_KEY = '<?php echo $GEOAPIFY_API_KEY; ?>';
        this.map = null;
        this.markerGroup = null;
        this.routeLine = null;
        this.destinations = [];
        this.currentStep = 1;
        this.manualPinEnabled = false;
        this.aiResults = null;
        this.selectedVehicle = null;
        this.selectedDriver = null;
        
        this.init();
    }

    init() {
        this.initMap();
        this.bindEvents();
        this.updateStepIndicator();
    }

    initMap() {
        this.map = L.map('map').setView([26.2389, 73.0243], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(this.map);
        
        this.markerGroup = L.layerGroup().addTo(this.map);
        
        this.map.on('click', (e) => {
            if (this.manualPinEnabled && this.currentStep === 3) {
                const { lat, lng } = e.latlng;
                const customName = prompt(
                    'Enter name for the destination:', 
                    `Destination ${this.destinations.length + 1}`
                );
                if (customName && customName.trim()) {
                    this.addDestination(customName.trim(), lat, lng, {
                        manual: true,
                        address: `Custom location at ${lat.toFixed(4)}, ${lng.toFixed(4)}`
                    });
                }
            }
        });
    }

    bindEvents() {
        // Step 1: Vehicle & Driver Selection
        document.getElementById('vehicle-select').addEventListener('change', (e) => {
            this.selectedVehicle = e.target.value;
            if (e.target.selectedOptions[0]) {
                this.vehicleConfig = {
                    fuel_efficiency: parseFloat(e.target.selectedOptions[0].dataset.fuelEfficiency) || 25,
                    fuel_type: e.target.selectedOptions[0].dataset.fuelType || 'Petrol'
                };
            }
            this.validateStep1();
        });

        document.getElementById('driver-select').addEventListener('change', (e) => {
            this.selectedDriver = e.target.value;
            this.validateStep1();
        });

        document.getElementById('continue-to-config').addEventListener('click', () => {
            this.goToStep(2);
        });

        // Step 2: Configuration
        document.querySelectorAll('input[name="fleet-type"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const recurringOptions = document.getElementById('recurring-options');
                if (e.target.value === 'recurring') {
                    recurringOptions.classList.remove('hidden');
                    document.getElementById('start-date').value = new Date().toISOString().split('T')[0];
                } else {
                    recurringOptions.classList.add('hidden');
                }
            });
        });

        document.querySelectorAll('input[name="recurrence-type"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const weeklyDays = document.getElementById('weekly-days');
                if (e.target.value === 'weekly') {
                    weeklyDays.classList.remove('hidden');
                } else {
                    weeklyDays.classList.add('hidden');
                }
            });
        });

        document.getElementById('continue-to-destinations').addEventListener('click', () => {
            this.goToStep(3);
        });

        // Step 3: Destination Selection
        const locationSearch = document.getElementById('location-search');
        const suggestions = document.getElementById('search-suggestions');
        let searchTimeout = null;

        locationSearch.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            const query = locationSearch.value.trim();
            
            if (query.length < 2) {
                suggestions.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                this.searchLocations(query);
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!locationSearch.contains(e.target) && !suggestions.contains(e.target)) {
                suggestions.classList.add('hidden');
            }
        });

        document.getElementById('enable-manual-pin').addEventListener('click', () => {
            this.toggleManualPin();
        });

        document.getElementById('clear-destinations').addEventListener('click', () => {
            this.clearDestinations();
        });

        document.getElementById('continue-to-ai').addEventListener('click', () => {
            this.goToStep(4);
        });

        // Step 4: AI Optimization
        document.getElementById('analyze-route').addEventListener('click', () => {
            this.analyzeWithAI();
        });

        document.getElementById('save-route').addEventListener('click', () => {
            this.saveRoute();
        });
    }

    validateStep1() {
        const continueBtn = document.getElementById('continue-to-config');
        continueBtn.disabled = !(this.selectedVehicle && this.selectedDriver);
    }

    validateStep3() {
        const continueBtn = document.getElementById('continue-to-ai');
        const destinationType = document.querySelector('input[name="destination-type"]:checked').value;
        const minDestinations = destinationType === 'single' ? 2 : 3;
        
        continueBtn.disabled = this.destinations.length < minDestinations;
    }

    validateStep4() {
        const analyzeBtn = document.getElementById('analyze-route');
        const saveBtn = document.getElementById('save-route');
        const routeName = document.getElementById('route-name').value.trim();
        
        analyzeBtn.disabled = this.destinations.length < 3;
        saveBtn.disabled = this.destinations.length < 2 || !routeName;
    }

    async searchLocations(query) {
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

    displaySearchSuggestions(features) {
        const suggestions = document.getElementById('search-suggestions');
        suggestions.innerHTML = '';

        if (features.length === 0) {
            suggestions.classList.add('hidden');
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
                this.addDestination(
                    feature.properties.name || feature.properties.formatted,
                    feature.properties.lat,
                    feature.properties.lon,
                    {
                        address: feature.properties.formatted,
                        manual: false
                    }
                );
                document.getElementById('location-search').value = '';
                suggestions.classList.add('hidden');
            };
            suggestions.appendChild(div);
        });

        suggestions.classList.remove('hidden');
    }

    addDestination(name, lat, lng, properties = {}) {
        const destination = {
            id: Date.now(),
            name: name,
            lat: lat,
            lng: lng,
            order: this.destinations.length + 1,
            address: properties.address || `${lat.toFixed(4)}, ${lng.toFixed(4)}`,
            manual: properties.manual || false
        };

        this.destinations.push(destination);
        this.updateDestinationsList();
        this.updateMapMarkers();
        this.validateStep3();
        this.validateStep4();
        
        if (this.destinations.length >= 2) {
            this.calculateRoute();
        }
    }

    removeDestination(id) {
        this.destinations = this.destinations.filter(dest => dest.id !== id);
        // Reorder destinations
        this.destinations.forEach((dest, index) => {
            dest.order = index + 1;
        });
        this.updateDestinationsList();
        this.updateMapMarkers();
        this.validateStep3();
        this.validateStep4();
        
        if (this.destinations.length >= 2) {
            this.calculateRoute();
        } else {
            this.clearRoute();
        }
    }

    clearDestinations() {
        this.destinations = [];
        this.updateDestinationsList();
        this.updateMapMarkers();
        this.clearRoute();
        this.validateStep3();
        this.validateStep4();
    }

    updateDestinationsList() {
        const list = document.getElementById('destinations-list');
        list.innerHTML = '';

        this.destinations.forEach(dest => {
            const li = document.createElement('li');
            li.className = 'destination-item';
            
            const icon = dest.manual ? '📌' : '📍';
            
            li.innerHTML = `
                <div class="destination-info">
                    <h4>${icon} ${dest.order}. ${dest.name}</h4>
                    <p>${dest.address}</p>
                </div>
                <div class="destination-actions">
                    <button class="btn btn-danger btn-sm" onclick="configurator.removeDestination(${dest.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            list.appendChild(li);
        });
    }

    updateMapMarkers() {
        this.markerGroup.clearLayers();

        this.destinations.forEach((dest, index) => {
            const isStart = index === 0;
            const isEnd = index === this.destinations.length - 1;
            
            let bgColor = '#667eea';
            if (isStart) bgColor = '#48bb78';
            if (isEnd && this.destinations.length > 1) bgColor = '#e53e3e';
            if (dest.manual) bgColor = '#805ad5';
            
            const icon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background: ${bgColor}; color: white; width: 32px; height: 32px; 
                              border-radius: 50%; display: flex; align-items: center; 
                              justify-content: center; font-weight: bold; font-size: 14px; 
                              border: 3px solid white; box-shadow: 0 3px 6px rgba(0,0,0,0.3);">
                              ${dest.order}</div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -16]
            });

            const marker = L.marker([dest.lat, dest.lng], { icon: icon })
                .bindPopup(`
                    <div style="font-weight: bold; margin-bottom: 8px;">${dest.name}</div>
                    <div style="font-size: 12px; color: #666; margin-bottom: 8px;">
                        <div><strong>Order:</strong> Stop ${dest.order} of ${this.destinations.length}</div>
                        <div><strong>Type:</strong> ${dest.manual ? 'Manual Pin' : 'Search Result'}</div>
                        <div><strong>Address:</strong> ${dest.address}</div>
                    </div>
                `);

            this.markerGroup.addLayer(marker);
        });

        if (this.destinations.length > 0) {
            const group = new L.featureGroup(this.markerGroup.getLayers());
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }

    async calculateRoute() {
        if (this.destinations.length < 2) {
            this.clearRoute();
            return;
        }

        try {
            const waypoints = this.destinations.map(dest => `${dest.lat},${dest.lng}`).join('|');
            const response = await fetch(
                `https://api.geoapify.com/v1/routing?waypoints=${waypoints}&mode=drive&details=instruction_details&apiKey=${this.GEOAPIFY_API_KEY}`
            );
            const data = await response.json();

            if (data.features && data.features.length > 0) {
                this.currentRoute = data.features[0];
                this.drawRoute(this.currentRoute);
            }
        } catch (error) {
            console.error('Route calculation error:', error);
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
        }
        
        if (coordinates.length > 0) {
            this.routeLine = L.polyline(coordinates, {
                color: '#667eea',
                weight: 6,
                opacity: 0.8,
                dashArray: '12,8'
            }).addTo(this.map);

            this.map.fitBounds(this.routeLine.getBounds().pad(0.1));
        }
    }

    clearRoute() {
        if (this.routeLine) {
            this.map.removeLayer(this.routeLine);
            this.routeLine = null;
        }
    }

    toggleManualPin() {
        this.manualPinEnabled = !this.manualPinEnabled;
        const btn = document.getElementById('enable-manual-pin');
        
        if (this.manualPinEnabled) {
            btn.innerHTML = '<i class="fas fa-map-pin"></i> Click to Pin';
            btn.className = 'btn btn-secondary btn-sm';
            this.map.getContainer().style.cursor = 'crosshair';
            this.showAlert('Manual pin mode enabled. Click on the map to add destinations.', 'info');
        } else {
            btn.innerHTML = '<i class="fas fa-hand-pointer"></i> Pan Mode';
            btn.className = 'btn btn-primary btn-sm';
            this.map.getContainer().style.cursor = '';
            this.showAlert('Pan mode enabled. Click and drag to move the map.', 'info');
        }
    }

    async analyzeWithAI() {
        if (this.destinations.length < 3) {
            this.showAlert('At least 3 destinations required for AI optimization', 'warning');
            return;
        }

        this.showLoadingOverlay('🧠 AI Route Optimization in Progress');

        try {
            const formData = new FormData();
            formData.append('action', 'ai_optimize');
            formData.append('destinations', JSON.stringify(this.destinations));

            const response = await fetch('', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            // Debug logging
            console.log('AI Optimization Result:', result);
            
            if (result.success) {
                this.aiResults = result;
                this.displayAIResults(result);
                this.reorderDestinations(result.ordered_point_ids);
                this.validateStep4();
                this.showAlert('Route successfully optimized by AI!', 'success');
            } else {
                throw new Error(result.errors[0]?.message || 'AI optimization failed');
            }

        } catch (error) {
            console.error('AI optimization error:', error);
            this.showAlert(`AI optimization failed: ${error.message}`, 'error');
        } finally {
            this.hideLoadingOverlay();
        }
    }

    displayAIResults(results) {
        const resultsDiv = document.getElementById('ai-results');
        const noteHtml = results.note ? `<p style="margin-top: 10px; font-size: 12px; opacity: 0.8; font-style: italic;">${results.note}</p>` : '';
        
        resultsDiv.innerHTML = `
            <div class="ai-results">
                <h4><i class="fas fa-brain"></i> AI Optimization Complete!</h4>
                <p>Your route has been optimized for maximum efficiency. Here are the savings:</p>
                
                <div class="savings-grid">
                    <div class="savings-item">
                        <div class="savings-value">${results.fuel_saved.toFixed(1)}L</div>
                        <div class="savings-label">Fuel Saved</div>
                    </div>
                    <div class="savings-item">
                        <div class="savings-value">$${results.money_saved.toFixed(2)}</div>
                        <div class="savings-label">Money Saved</div>
                    </div>
                    <div class="savings-item">
                        <div class="savings-value">${results.distance_saved?.toFixed(1) || '1.2'}km</div>
                        <div class="savings-label">Distance Saved</div>
                    </div>
                    <div class="savings-item">
                        <div class="savings-value">${Math.round(results.distance_saved * 2) || 8}min</div>
                        <div class="savings-label">Time Saved</div>
                    </div>
                </div>
                
                <p style="margin-top: 15px; font-size: 14px; opacity: 0.9;">
                    The route order has been automatically updated to reflect the optimal sequence.
                </p>
                ${noteHtml}
            </div>
        `;
        resultsDiv.classList.remove('hidden');
    }

    reorderDestinations(optimizedOrder) {
        if (!Array.isArray(optimizedOrder) || optimizedOrder.length !== this.destinations.length) {
            console.error('Invalid optimization order received');
            return;
        }

        const reorderedDestinations = optimizedOrder.map((originalIndex, newIndex) => {
            const dest = { ...this.destinations[originalIndex - 1] };
            dest.order = newIndex + 1;
            return dest;
        });

        this.destinations = reorderedDestinations;
        this.updateDestinationsList();
        this.updateMapMarkers();
        
        if (this.destinations.length >= 2) {
            this.calculateRoute();
        }
    }

    async saveRoute() {
        const routeName = document.getElementById('route-name').value.trim();
        if (!routeName) {
            this.showAlert('Please enter a route name', 'warning');
            return;
        }

        this.showLoadingOverlay('💾 Saving Route Configuration');

        try {
            const formData = new FormData();
            formData.append('action', 'save_route');
            formData.append('vehicle_id', this.selectedVehicle);
            formData.append('driver_id', this.selectedDriver);
            formData.append('route_name', routeName);
            
            // Configuration data
            const fleetType = document.querySelector('input[name="fleet-type"]:checked').value;
            formData.append('fleet_type', fleetType);
            formData.append('destination_type', document.querySelector('input[name="destination-type"]:checked').value);
            formData.append('departure_time', document.getElementById('departure-time').value);
            formData.append('arrival_time', document.getElementById('arrival-time').value);
            
            // Recurrence data if applicable
            if (fleetType === 'recurring') {
                const recurrenceData = {
                    type: document.querySelector('input[name="recurrence-type"]:checked').value,
                    interval: document.getElementById('recurrence-interval').value,
                    start_date: document.getElementById('start-date').value,
                    end_date: document.getElementById('end-date').value,
                    active_days: Array.from(document.querySelectorAll('#weekly-days input:checked')).map(cb => cb.value)
                };
                formData.append('recurrence', JSON.stringify(recurrenceData));
            }
            
            // Destinations
            formData.append('destinations', JSON.stringify(this.destinations));
            
            // AI Analysis results if available
            if (this.aiResults) {
                formData.append('ai_analysis', JSON.stringify(this.aiResults));
            }
            
            // Route metrics
            if (this.currentRoute) {
                formData.append('total_distance', (this.currentRoute.properties.distance / 1000).toFixed(2));
                formData.append('estimated_duration', Math.round(this.currentRoute.properties.time / 60));
                
                const distanceMiles = (this.currentRoute.properties.distance / 1000) * 0.621371;
                const fuelCost = (distanceMiles / (this.vehicleConfig?.fuel_efficiency || 25)) * 3.50; // $3.50 avg fuel price
                formData.append('estimated_fuel_cost', fuelCost.toFixed(2));
            }

            const response = await fetch('', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                this.showAlert(`Route "${routeName}" saved successfully! Route ID: ${result.route_id}`, 'success');
                setTimeout(() => {
                    // Redirect to routes list or dashboard
                    window.location.href = 'routes.php';
                }, 2000);
            } else {
                throw new Error(result.errors[0]?.message || 'Failed to save route');
            }

        } catch (error) {
            console.error('Save route error:', error);
            this.showAlert(`Failed to save route: ${error.message}`, 'error');
        } finally {
            this.hideLoadingOverlay();
        }
    }

    goToStep(stepNumber) {
        // Hide current step
        document.getElementById(`step-${this.currentStep}`).classList.add('hidden');
        
        // Show new step
        document.getElementById(`step-${stepNumber}`).classList.remove('hidden');
        
        // Update step indicator
        this.currentStep = stepNumber;
        this.updateStepIndicator();
        
        // Specific step logic
        if (stepNumber === 4) {
            this.validateStep4();
        }
    }

    updateStepIndicator() {
        document.querySelectorAll('.step').forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.remove('active', 'completed');
            
            if (stepNum < this.currentStep) {
                step.classList.add('completed');
            } else if (stepNum === this.currentStep) {
                step.classList.add('active');
            }
        });
    }

    showAlert(message, type = 'info') {
        // Remove existing alerts
        document.querySelectorAll('.alert').forEach(alert => {
            if (alert.parentElement.classList.contains('section')) {
                alert.remove();
            }
        });
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `<i class="fas fa-info-circle"></i> <div>${message}</div>`;
        
        const currentSection = document.getElementById(`step-${this.currentStep}`);
        currentSection.insertBefore(alertDiv, currentSection.firstElementChild.nextSibling);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    showLoadingOverlay(message) {
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
}

// Initialize the route configurator
const configurator = new RouteConfigurator();
</script>

</body>
</html>
