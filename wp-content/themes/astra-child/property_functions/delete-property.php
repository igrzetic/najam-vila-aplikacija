<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    $propertyId = intval($_POST['property_id']);

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
        http_response_code(500);
        echo "Error connecting to database!";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM rental_objects WHERE property_id = ?");
    $stmt->bind_param("i", $propertyId);

    if ($stmt->execute()) {
        echo "Property successfully deleted.";
    } else {
        http_response_code(500);
        echo "Error deleting property.";
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo "Invalid request.";
}
?>
