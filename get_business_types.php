<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT * FROM business_types ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $business_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return in the format JavaScript expects
    echo json_encode(array(
        'success' => true,
        'data' => $business_types
    ));

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ));
}
?>
