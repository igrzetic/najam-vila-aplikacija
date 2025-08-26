<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$listId = isset($_GET['list_id']) ? (int)$_GET['list_id'] : 0;
if ($listId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid list ID']);
    exit;
}

$mysqli = new mysqli('localhost', 'root', '', 'najam_vila_db');
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection error']);
    exit;
}

$mysqli->set_charset('utf8mb4');

// Fetch items for the specified list (explicit columns, stable order)
$stmt = $mysqli->prepare('SELECT item_id, item_name, quantity, price, status FROM list_items WHERE list_id = ? ORDER BY item_id ASC');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}
$stmt->bind_param('i', $listId);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    exit;
}
$result = $stmt->get_result();
if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Result failed: ' . $stmt->error]);
    exit;
}

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

$stmt->close();
$mysqli->close();

// Return items as JSON
http_response_code(200);
echo json_encode(['success' => true, 'items' => $items]);
exit;
