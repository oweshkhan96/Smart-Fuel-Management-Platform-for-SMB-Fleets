<?php
require_once 'session.php';
requireLogin();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $database = new Database();
    $db = $database->getConnection();
    $user = getCurrentUser();
    
    if (!$user['company_id']) {
        throw new Exception("Company ID not found in session");
    }
    
    switch($method) {
        case 'GET':
            // Remove the drivers endpoint check
            getAllVehicles($db, $user['company_id']);
            break;
            
        case 'POST':
            createVehicle($db, $_POST, $user['company_id']);
            break;
            
        case 'PUT':
            parse_str(file_get_contents("php://input"), $put_data);
            $put_data = array_merge($_POST, $put_data);
            updateVehicle($db, $put_data, $user['company_id']);
            break;
            
        case 'DELETE':
            $delete_data = $_POST;
            if (empty($delete_data)) {
                parse_str(file_get_contents("php://input"), $delete_data);
            }
            deleteVehicle($db, $delete_data['id'], $user['company_id']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
    
} catch(Exception $e) {
    error_log("Vehicles API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

function getAllVehicles($db, $companyId) {
    try {
        // Remove the JOIN with drivers table
        $query = "SELECT * FROM vehicles WHERE company_id = ? ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$companyId]);
        
        $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $vehicles
        ]);
    } catch(Exception $e) {
        throw new Exception("Error fetching vehicles: " . $e->getMessage());
    }
}

// Remove the getAvailableDrivers function completely

function createVehicle($db, $data, $companyId) {
    try {
        $required_fields = ['vehicleName', 'vehicleType', 'make', 'model', 'year', 'licensePlate', 'fuelType', 'status'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        // Check if license plate already exists
        $check_plate = "SELECT id FROM vehicles WHERE license_plate = ?";
        $stmt = $db->prepare($check_plate);
        $stmt->execute([$data['licensePlate']]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("License plate already exists");
        }
        
        // Generate unique vehicle ID
        $vehicleId = generateVehicleId($db);
        
        $query = "INSERT INTO vehicles (
            vehicle_id, company_id, vehicle_name, vehicle_type, make, model, year,
            color, license_plate, vin_number, fuel_type, engine_capacity, transmission,
            seating_capacity, current_mileage, odometer_reading, fuel_efficiency,
            insurance_policy, insurance_expiry, registration_expiry, last_service_date,
            next_service_due, service_interval_km, purchase_date, purchase_price,
            current_value, status, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $vehicleId,
            $companyId,
            $data['vehicleName'],
            $data['vehicleType'],
            $data['make'],
            $data['model'],
            $data['year'],
            $data['color'] ?? '',
            $data['licensePlate'],
            $data['vinNumber'] ?? null,
            $data['fuelType'],
            !empty($data['engineCapacity']) ? $data['engineCapacity'] : null,
            $data['transmission'] ?? 'Manual',
            !empty($data['seatingCapacity']) ? $data['seatingCapacity'] : 5,
            !empty($data['currentMileage']) ? $data['currentMileage'] : 0.00,
            !empty($data['odometerReading']) ? $data['odometerReading'] : 0.00,
            !empty($data['fuelEfficiency']) ? $data['fuelEfficiency'] : 0.00,
            $data['insurancePolicy'] ?? '',
            !empty($data['insuranceExpiry']) ? $data['insuranceExpiry'] : null,
            !empty($data['registrationExpiry']) ? $data['registrationExpiry'] : null,
            !empty($data['lastServiceDate']) ? $data['lastServiceDate'] : null,
            !empty($data['nextServiceDue']) ? $data['nextServiceDue'] : null,
            !empty($data['serviceIntervalKm']) ? $data['serviceIntervalKm'] : 10000,
            !empty($data['purchaseDate']) ? $data['purchaseDate'] : null,
            !empty($data['purchasePrice']) ? $data['purchasePrice'] : null,
            !empty($data['currentValue']) ? $data['currentValue'] : null,
            $data['status'],
            $data['notes'] ?? ''
            // Remove assigned_driver_id from here
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Vehicle created successfully',
            'data' => ['vehicle_id' => $vehicleId, 'id' => $db->lastInsertId()]
        ]);
        
    } catch(Exception $e) {
        throw new Exception("Error creating vehicle: " . $e->getMessage());
    }
}

function updateVehicle($db, $data, $companyId) {
    try {
        if (empty($data['id'])) {
            throw new Exception("Vehicle ID is required for update");
        }
        
        $required_fields = ['vehicleName', 'vehicleType', 'make', 'model', 'year', 'licensePlate', 'fuelType', 'status'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        // Check if vehicle exists and belongs to company
        $check_vehicle = "SELECT id FROM vehicles WHERE id = ? AND company_id = ?";
        $stmt = $db->prepare($check_vehicle);
        $stmt->execute([$data['id'], $companyId]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Vehicle not found or access denied");
        }
        
        // Check if license plate already exists for another vehicle
        $check_plate = "SELECT id FROM vehicles WHERE license_plate = ? AND id != ?";
        $stmt = $db->prepare($check_plate);
        $stmt->execute([$data['licensePlate'], $data['id']]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("License plate already exists for another vehicle");
        }
        
        $query = "UPDATE vehicles SET 
            vehicle_name = ?, vehicle_type = ?, make = ?, model = ?, year = ?,
            color = ?, license_plate = ?, vin_number = ?, fuel_type = ?, engine_capacity = ?,
            transmission = ?, seating_capacity = ?, current_mileage = ?, odometer_reading = ?,
            fuel_efficiency = ?, insurance_policy = ?, insurance_expiry = ?, registration_expiry = ?,
            last_service_date = ?, next_service_due = ?, service_interval_km = ?, purchase_date = ?,
            purchase_price = ?, current_value = ?, status = ?, notes = ?, updated_at = NOW()
            WHERE id = ? AND company_id = ?";
            // Remove assigned_driver_id from the query
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $data['vehicleName'],
            $data['vehicleType'],
            $data['make'],
            $data['model'],
            $data['year'],
            $data['color'] ?? '',
            $data['licensePlate'],
            $data['vinNumber'] ?? null,
            $data['fuelType'],
            !empty($data['engineCapacity']) ? $data['engineCapacity'] : null,
            $data['transmission'] ?? 'Manual',
            !empty($data['seatingCapacity']) ? $data['seatingCapacity'] : 5,
            !empty($data['currentMileage']) ? $data['currentMileage'] : 0.00,
            !empty($data['odometerReading']) ? $data['odometerReading'] : 0.00,
            !empty($data['fuelEfficiency']) ? $data['fuelEfficiency'] : 0.00,
            $data['insurancePolicy'] ?? '',
            !empty($data['insuranceExpiry']) ? $data['insuranceExpiry'] : null,
            !empty($data['registrationExpiry']) ? $data['registrationExpiry'] : null,
            !empty($data['lastServiceDate']) ? $data['lastServiceDate'] : null,
            !empty($data['nextServiceDue']) ? $data['nextServiceDue'] : null,
            !empty($data['serviceIntervalKm']) ? $data['serviceIntervalKm'] : 10000,
            !empty($data['purchaseDate']) ? $data['purchaseDate'] : null,
            !empty($data['purchasePrice']) ? $data['purchasePrice'] : null,
            !empty($data['currentValue']) ? $data['currentValue'] : null,
            $data['status'],
            $data['notes'] ?? '',
            $data['id'],
            $companyId
            // Remove assigned_driver_id parameter
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Vehicle updated successfully'
        ]);
        
    } catch(Exception $e) {
        throw new Exception("Error updating vehicle: " . $e->getMessage());
    }
}

function deleteVehicle($db, $id, $companyId) {
    try {
        if (empty($id)) {
            throw new Exception("Vehicle ID is required for deletion");
        }
        
        $query = "DELETE FROM vehicles WHERE id = ? AND company_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id, $companyId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Vehicle deleted successfully'
            ]);
        } else {
            throw new Exception("Vehicle not found or already deleted");
        }
        
    } catch(Exception $e) {
        throw new Exception("Error deleting vehicle: " . $e->getMessage());
    }
}

function generateVehicleId($db) {
    do {
        $vehicleId = 'VH' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $check = "SELECT id FROM vehicles WHERE vehicle_id = ?";
        $stmt = $db->prepare($check);
        $stmt->execute([$vehicleId]);
    } while ($stmt->rowCount() > 0);
    
    return $vehicleId;
}
?>
