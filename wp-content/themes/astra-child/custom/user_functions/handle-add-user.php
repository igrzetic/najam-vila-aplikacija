<?php
/**
 * Users handler: process Add User POST and redirect with flash message.
 * No HTML output is produced here to avoid breaking the admin layout.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = function_exists('home_url')
    ? home_url('/index.php/admin-dashboard/')
    : 'http://localhost/najam_vila_aplikacija/index.php/admin-dashboard/';

$redirect_url = rtrim($base_url, '/') . '/#users';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['add_user_submit'])) {
    header('Location: ' . $redirect_url);
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$email    = $_POST['email'] ?? '';
$role     = $_POST['role'] ?? '';

$conn = new mysqli('localhost', 'root', '', 'najam_vila_db');
if ($conn->connect_error) {
    $_SESSION['user_message'] = 'Error connecting to database!';
    header('Location: ' . $redirect_url);
    exit;
}

$stmt = $conn->prepare('INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)');
if ($stmt) {
    $stmt->bind_param('ssss', $username, $password, $email, $role);
    if ($stmt->execute()) {
        $_SESSION['user_message'] = 'User successfully added.';
    } else {
        $_SESSION['user_message'] = 'Error adding user.';
    }
    $stmt->close();
} else {
    $_SESSION['user_message'] = 'Error preparing query.';
}
$conn->close();

header('Location: ' . $redirect_url);
exit;
