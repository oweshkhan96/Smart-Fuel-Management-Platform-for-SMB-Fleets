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
            getAllDrivers($db, $user['company_id']);
            break;
            
        case 'POST':
            createDriver($db, $_POST, $user['company_id']);
            break;
            
        case 'PUT':
            parse_str(file_get_contents("php://input"), $put_data);
            // Merge with $_POST in case form data is sent
            $put_data = array_merge($_POST, $put_data);
            updateDriver($db, $put_data, $user['company_id']);
            break;
            
        case 'DELETE':
            $delete_data = $_POST;
            if (empty($delete_data)) {
                parse_str(file_get_contents("php://input"), $delete_data);
            }
            deleteDriver($db, $delete_data['id'], $user['company_id']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
    
} catch(Exception $e) {
    error_log("Drivers API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

function getAllDrivers($db, $companyId) {
    try {
        $query = "SELECT * FROM drivers WHERE company_id = ? ORDER BY created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute([$companyId]);
        
        $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $drivers
        ]);
    } catch(Exception $e) {
        throw new Exception("Error fetching drivers: " . $e->getMessage());
    }
}

function createDriver($db, $data, $companyId) {
    try {
        // Validate required fields
        $required_fields = ['fullName', 'email', 'phone', 'licenseNumber', 'licenseExpiry', 'dateOfBirth', 'status'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Check if email already exists
        $check_email = "SELECT id FROM drivers WHERE email = ? AND company_id = ?";
        $stmt = $db->prepare($check_email);
        $stmt->execute([$data['email'], $companyId]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already exists for another driver");
        }
        
        // Check if license number already exists
        $check_license = "SELECT id FROM drivers WHERE license_number = ? AND company_id = ?";
        $stmt = $db->prepare($check_license);
        $stmt->execute([$data['licenseNumber'], $companyId]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("License number already exists for another driver");
        }
        
        // Generate unique driver ID
        $driverId = generateDriverId($db);
        
        $query = "INSERT INTO drivers (
            driver_id, company_id, full_name, email, phone, 
            license_number, license_expiry, date_of_birth, 
            address, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $driverId,
            $companyId,
            $data['fullName'],
            $data['email'],
            $data['phone'],
            $data['licenseNumber'],
            $data['licenseExpiry'],
            $data['dateOfBirth'],
            $data['address'] ?? '',
            $data['status']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Driver created successfully',
            'data' => ['driver_id' => $driverId, 'id' => $db->lastInsertId()]
        ]);
        
    } catch(Exception $e) {
        throw new Exception("Error creating driver: " . $e->getMessage());
    }
}

function updateDriver($db, $data, $companyId) {
    try {
        // Validate required fields
        if (empty($data['id'])) {
            throw new Exception("Driver ID is required for update");
        }
        
        $required_fields = ['fullName', 'email', 'phone', 'licenseNumber', 'licenseExpiry', 'dateOfBirth', 'status'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }
        
        // Check if driver exists and belongs to company
        $check_driver = "SELECT id FROM drivers WHERE id = ? AND company_id = ?";
        $stmt = $db->prepare($check_driver);
        $stmt->execute([$data['id'], $companyId]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception("Driver not found or access denied");
        }
        
        // Check if email already exists for another driver
        $check_email = "SELECT id FROM drivers WHERE email = ? AND company_id = ? AND id != ?";
        $stmt = $db->prepare($check_email);
        $stmt->execute([$data['email'], $companyId, $data['id']]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already exists for another driver");
        }
        
        // Check if license number already exists for another driver
        $check_license = "SELECT id FROM drivers WHERE license_number = ? AND company_id = ? AND id != ?";
        $stmt = $db->prepare($check_license);
        $stmt->execute([$data['licenseNumber'], $companyId, $data['id']]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("License number already exists for another driver");
        }
        
        $query = "UPDATE drivers SET 
            full_name = ?, email = ?, phone = ?, license_number = ?, 
            license_expiry = ?, date_of_birth = ?, address = ?, 
            status = ?, updated_at = NOW()
            WHERE id = ? AND company_id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $data['fullName'],
            $data['email'],
            $data['phone'],
            $data['licenseNumber'],
            $data['licenseExpiry'],
            $data['dateOfBirth'],
            $data['address'] ?? '',
            $data['status'],
            $data['id'],
            $companyId
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Driver updated successfully'
        ]);
        
    } catch(Exception $e) {
        throw new Exception("Error updating driver: " . $e->getMessage());
    }
}

function deleteDriver($db, $id, $companyId) {
    try {
        if (empty($id)) {
            throw new Exception("Driver ID is required for deletion");
        }
        
        // Check if driver exists
        $check_driver = "SELECT id, full_name FROM drivers WHERE id = ? AND company_id = ?";
        $stmt = $db->prepare($check_driver);
        $stmt->execute([$id, $companyId]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$driver) {
            throw new Exception("Driver not found or access denied");
        }
        
        $query = "DELETE FROM drivers WHERE id = ? AND company_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id, $companyId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Driver deleted successfully'
            ]);
        } else {
            throw new Exception("Failed to delete driver");
        }
        
    } catch(Exception $e) {
        throw new Exception("Error deleting driver: " . $e->getMessage());
    }
}

function generateDriverId($db) {
    do {
        $driverId = 'DRV' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        $check = "SELECT id FROM drivers WHERE driver_id = ?";
        $stmt = $db->prepare($check);
        $stmt->execute([$driverId]);
    } while ($stmt->rowCount() > 0);
    
    return $driverId;
}
?>
