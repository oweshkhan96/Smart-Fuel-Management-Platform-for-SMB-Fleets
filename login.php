<?php
session_start();

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
    if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception('Email and password are required');
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Get user from database
    $query = "SELECT u.id, u.email, u.full_name, u.password_hash, u.is_active, 
                     c.company_id, c.company_name 
              FROM users u 
              LEFT JOIN companies c ON u.id = c.user_id 
              WHERE u.email = ? LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists
    if (!$user) {
        throw new Exception('Invalid email or password');
    }

    // Check if account is active
    if (!$user['is_active']) {
        throw new Exception('Your account has been deactivated. Please contact support.');
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        throw new Exception('Invalid email or password');
    }

    // Create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['company_id'] = $user['company_id'];
    $_SESSION['company_name'] = $user['company_name'];
    $_SESSION['login_time'] = time();
    $_SESSION['is_logged_in'] = true;

    // Handle "Remember Me" functionality
    if (isset($_POST['rememberMe']) && $_POST['rememberMe'] === 'true') {
        $remember_token = bin2hex(random_bytes(32));
        
        // Store remember token in database (you'll need to add this column)
        $update_token = "UPDATE users SET remember_token = ?, remember_expires = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?";
        $token_stmt = $db->prepare($update_token);
        $token_stmt->execute([$remember_token, $user['id']]);
        
        // Set cookie for 30 days
        setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }

    // Update last login time
    $update_login = "UPDATE users SET last_login = NOW() WHERE id = ?";
    $login_stmt = $db->prepare($update_login);
    $login_stmt->execute([$user['id']]);

    // Success response
    echo json_encode(array(
        'success' => true,
        'message' => 'Login successful',
        'data' => array(
            'user_id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'company_id' => $user['company_id'],
            'company_name' => $user['company_name']
        ),
        'redirect' => 'dashboard.php'
    ));

} catch(Exception $e) {
    error_log("Login error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}
?>
