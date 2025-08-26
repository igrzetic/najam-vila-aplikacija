<?php
 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? '';

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
        http_response_code(500);
        echo "Error connecting to database!";
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, email = ?, role = ? WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ssssi", $username, $password, $email, $role, $userId);

        if ($stmt->execute()) {
            echo "User successfully updated.";
        } else {
            http_response_code(500);
            echo "Error updating user.";
        }

        $stmt->close();
    } else {
        http_response_code(500);
        echo "Error preparing statement.";
    }

    $conn->close();
 } else {
    http_response_code(400);
    echo "Invalid request.";
 }
