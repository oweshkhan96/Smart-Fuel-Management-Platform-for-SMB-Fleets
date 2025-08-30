<?php
// Include this at the top of pages that require authentication

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
        // Check for remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            validateRememberToken($_COOKIE['remember_token']);
        } else {
            redirectToLogin();
        }
    }
    
    // Check session timeout (optional - 2 hours)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 7200)) {
        session_destroy();
        redirectToLogin();
    }
}

function validateRememberToken($token) {
    require_once 'config.php';
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT u.id, u.email, u.full_name, c.company_id, c.company_name 
                  FROM users u 
                  LEFT JOIN companies c ON u.id = c.user_id 
                  WHERE u.remember_token = ? AND u.remember_expires > NOW() AND u.is_active = 1 
                  LIMIT 1";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Recreate session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['company_id'] = $user['company_id'];
            $_SESSION['company_name'] = $user['company_name'];
            $_SESSION['login_time'] = time();
            $_SESSION['is_logged_in'] = true;
            
            // Update last login
            $update_login = "UPDATE users SET last_login = NOW() WHERE id = ?";
            $login_stmt = $db->prepare($update_login);
            $login_stmt->execute([$user['id']]);
            
        } else {
            // Invalid token, clear cookie
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            redirectToLogin();
        }
        
    } catch(Exception $e) {
        error_log("Remember token validation error: " . $e->getMessage());
        redirectToLogin();
    }
}

function redirectToLogin() {
    header('Location: signin.html');
    exit;
}

function logout() {
    // Clear remember me cookie if exists
    if (isset($_COOKIE['remember_token'])) {
        require_once 'config.php';
        
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Clear remember token from database
            if (isset($_SESSION['user_id'])) {
                $clear_token = "UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE id = ?";
                $stmt = $db->prepare($clear_token);
                $stmt->execute([$_SESSION['user_id']]);
            }
        } catch(Exception $e) {
            error_log("Logout error: " . $e->getMessage());
        }
        
        // Clear cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // Destroy session
    session_destroy();
    
    // Redirect to login
    header('Location: signin.html');
    exit;
}

function getCurrentUser() {
    return array(
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'company_id' => $_SESSION['company_id'] ?? null,
        'company_name' => $_SESSION['company_name'] ?? null
    );
}
?>
