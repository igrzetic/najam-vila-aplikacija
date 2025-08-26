<?php
// Standalone handler for adding a property. No HTML output, only redirect.
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$redirect_url = function_exists('home_url')
    ? home_url('/index.php/admin-dashboard/#properties')
    : 'http://localhost/najam_vila_aplikacija/index.php/admin-dashboard/#properties';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['add_property_submit'])) {
    // Fallback: navigate back to dashboard section
    header('Location: ' . $redirect_url);
    exit;
}

$propertyName = $_POST['property_name'] ?? '';
$propertyType = $_POST['property_type'] ?? '';
$country = $_POST['country'] ?? '';
$city = $_POST['city'] ?? '';
$street = $_POST['street'] ?? '';
$houseNumber = $_POST['house_number'] ?? '';
$capacity = isset($_POST['capacity']) ? (int) $_POST['capacity'] : 0;
$ownerId = isset($_POST['owner_id']) ? (int) $_POST['owner_id'] : 0;

$conn = new mysqli('localhost', 'root', '', 'najam_vila_db');
if ($conn->connect_error) {
    $_SESSION['user_message'] = 'Error connecting to database!';
    header('Location: ' . $redirect_url);
    exit;
}

$stmt = $conn->prepare('INSERT INTO rental_objects (property_name, property_type, country, city, street, house_number, capacity, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
if ($stmt) {
    $stmt->bind_param('ssssssii', $propertyName, $propertyType, $country, $city, $street, $houseNumber, $capacity, $ownerId);
    if ($stmt->execute()) {
        $_SESSION['user_message'] = 'Property successfully added.';
    } else {
        $_SESSION['user_message'] = 'Error adding property: ' . $stmt->error;
    }
    $stmt->close();
} else {
    $_SESSION['user_message'] = 'Error preparing query.';
}
$conn->close();

// Redirect back to dashboard Properties tab
header('Location: ' . $redirect_url);
exit;
