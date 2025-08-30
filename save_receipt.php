<?php
// save_receipt.php - Modified to bypass FK constraints
session_start();
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'sdfdokln_fleet';
$username = 'sdfdokln_admin';
$password = ';cX6,?[]dCkL';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['driver_id']) || !isset($input['extracted_text'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required data']);
    exit;
}

try {
    // Create receipts table if it doesn't exist (without FK constraint)
    $createTable = "
        CREATE TABLE IF NOT EXISTS receipts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            driver_id VARCHAR(10) NOT NULL,
            file_name VARCHAR(255),
            extracted_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_driver_receipts (driver_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
    ";
    $pdo->exec($createTable);
    
    // Temporarily disable foreign key checks
    $pdo->exec('SET foreign_key_checks = 0');
    
    // Insert receipt data
    $stmt = $pdo->prepare("
        INSERT INTO receipts (driver_id, file_name, extracted_text) 
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([
        $input['driver_id'],
        $input['file_name'] ?? 'unknown',
        $input['extracted_text']
    ]);
    
    // Re-enable foreign key checks
    $pdo->exec('SET foreign_key_checks = 1');
    
    echo json_encode([
        'success' => true,
        'receipt_id' => $pdo->lastInsertId(),
        'message' => 'Receipt saved successfully'
    ]);
    
} catch (Exception $e) {
    // Make sure to re-enable foreign key checks even if there's an error
    $pdo->exec('SET foreign_key_checks = 1');
    
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to save receipt: ' . $e->getMessage()
    ]);
}
?>
