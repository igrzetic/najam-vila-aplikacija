<?php
// Load WordPress to access nonce verification helpers
require_once dirname(__FILE__, 5) . '/wp-load.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

if (!isset($_POST['report_id'], $_POST['nonce'])) {
    http_response_code(400);
    echo 'Missing parameters';
    exit;
}

$nonce = sanitize_text_field(wp_unslash($_POST['nonce']));
if (!wp_verify_nonce($nonce, 'delete_damage_report')) {
    http_response_code(403);
    echo 'Invalid security token';
    exit;
}

$reportId = (int) $_POST['report_id'];

$conn = new mysqli('localhost', 'root', '', 'najam_vila_db');
if ($conn->connect_error) {
    http_response_code(500);
    echo 'Error connecting to the database!';
    exit;
}

$stmt = $conn->prepare('DELETE FROM damage_reports WHERE report_id = ?');
$stmt->bind_param('i', $reportId);

if ($stmt->execute()) {
    echo 'Report successfully deleted.';
} else {
    http_response_code(500);
    echo 'Error deleting report.';
}

$stmt->close();
$conn->close();
?>