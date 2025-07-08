<?php
header('Content-Type: application/json');

try {
    require_once '../config/db_config.php';
    
    $query = "SELECT * FROM raw_materials";
    $result = $conn->query($query);
    
    $materials = [];
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $materials
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>