<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Validate required fields
    $required_fields = ['email', 'fullName', 'country', 'phone', 'company', 'password', 'businessType', 'fleetSize'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Check if email already exists
    $check_email = "SELECT id FROM users WHERE email = ?";
    $stmt = $db->prepare($check_email);
    $stmt->execute([$_POST['email']]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception("Email already registered");
    }

    // Validate password strength
    if (strlen($_POST['password']) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }

    // Validate terms acceptance
    if (!isset($_POST['terms']) || $_POST['terms'] !== 'true') {
        throw new Exception("You must accept the terms and conditions");
    }

    // Start transaction
    $db->beginTransaction();

    // Hash password
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert user
    $user_query = "INSERT INTO users (email, full_name, country, phone, password_hash) VALUES (?, ?, ?, ?, ?)";
    $user_stmt = $db->prepare($user_query);
    $user_stmt->execute([
        $_POST['email'],
        $_POST['fullName'],
        $_POST['country'],
        $_POST['phone'],
        $password_hash
    ]);

    // GET USER ID - This is crucial!
    $user_id = $db->lastInsertId();

    // Generate company ID - This is crucial!
    $company_id = Database::generateCompanyId();

    // Parse selected features
    $selected_features = [];
    if (!empty($_POST['selectedFeatures'])) {
        $selected_features = json_decode($_POST['selectedFeatures'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $selected_features = [];
        }
    }

    // Insert company
    $company_query = "INSERT INTO companies (company_id, user_id, company_name, business_type, fleet_size, selected_features) VALUES (?, ?, ?, ?, ?, ?)";
    $company_stmt = $db->prepare($company_query);
    $company_stmt->execute([
        $company_id,
        $user_id,
        $_POST['company'],
        $_POST['businessType'],
        $_POST['fleetSize'],
        json_encode($selected_features)
    ]);

    // Commit transaction
    $db->commit();

    // Success response - Variables are NOW defined!
    echo json_encode(array(
        'success' => true,
        'message' => 'Registration successful',
        'data' => array(
            'company_id' => $company_id,
            'user_id' => $user_id,
            'email' => $_POST['email'],
            'company_name' => $_POST['company']
        )
    ));

} catch(Exception $e) {
    // Rollback transaction on error
    if (isset($db) && $db->inTransaction()) {
        $db->rollback();
    }
    
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}
?>
