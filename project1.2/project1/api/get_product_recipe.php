<?php
require_once '../config/db_config.php';

header('Content-Type: application/json');

if (!isset($_GET['product_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Product ID is required']);
    exit;
}

$product_id = (int)$_GET['product_id'];

try {
    // Check database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $query = "SELECT pr.material_id, pr.quantity_required, rm.sku, rm.name, rm.unit_of_measure, rm.cost_per_unit, IFNULL(rmi.quantity_on_hand, 0) AS available_stock FROM product_recipes pr JOIN raw_materials rm ON pr.material_id = rm.material_id LEFT JOIN raw_material_inventory rmi ON rm.material_id = rmi.material_id WHERE pr.product_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $product_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $materials = [];
    while ($row = $result->fetch_assoc()) {
        $materials[] = [
            'material_id' => (int)$row['material_id'],
            'sku' => $row['sku'],
            'name' => $row['name'],
            'quantity_required' => (float)$row['quantity_required'],
            'unit_of_measure' => $row['unit_of_measure'],
            'cost_per_unit' => (float)$row['cost_per_unit'],
            'available_stock' => (float)$row['available_stock']
        ];
    }
    echo json_encode(['success' => true, 'data' => $materials]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();