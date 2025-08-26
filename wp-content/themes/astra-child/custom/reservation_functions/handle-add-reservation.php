<?php
// Standalone handler for adding a reservation. No HTML output, only redirect.
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$redirect_url = function_exists('home_url')
    ? home_url('/index.php/admin-dashboard/#reservations')
    : 'http://localhost/najam_vila_aplikacija/index.php/admin-dashboard/#reservations';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['add_reservation_submit'])) {
    header('Location: ' . $redirect_url);
    exit;
}

$firstName = $_POST['guest_first_name'] ?? '';
$lastName = $_POST['guest_last_name'] ?? '';
$arrivalDate = $_POST['arrival_date'] ?? '';
$departureDate = $_POST['departure_date'] ?? '';
$guestName = trim($firstName . ' ' . $lastName);
$adults = isset($_POST['adults']) ? (int) $_POST['adults'] : 0;
$children = isset($_POST['children']) ? (int) $_POST['children'] : 0;
$infants = isset($_POST['infants']) ? (int) $_POST['infants'] : 0;
$pets = isset($_POST['pets']) ? (int) $_POST['pets'] : 0;
$agency = $_POST['agency'] ?? '';
$specialRequests = $_POST['special_requests'] ?? '';
$earnings = isset($_POST['earnings']) ? (float) $_POST['earnings'] : 0;
$propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli('localhost', 'root', '', 'najam_vila_db');
    $conn->set_charset('utf8mb4');

    $stmt = $conn->prepare('INSERT INTO reservations (arrival_date, departure_date, guest_name, adults, children, infants, pets, agency, special_requests, earnings, property_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssiiiissdi', $arrivalDate, $departureDate, $guestName, $adults, $children, $infants, $pets, $agency, $specialRequests, $earnings, $propertyId);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    $_SESSION['user_message'] = 'Reservation added successfully.';
} catch (Throwable $e) {
    error_log('Reservation insert failed: ' . $e->getMessage());
    $_SESSION['user_message'] = 'Error adding reservation: ' . $e->getMessage();
}

header('Location: ' . $redirect_url);
exit;
