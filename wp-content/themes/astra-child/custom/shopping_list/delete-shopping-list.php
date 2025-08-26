<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['list_id'])) {
    $listId = intval($_POST['list_id']);

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
        http_response_code(500);
        echo "Error connecting to the database!";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM shopping_lists WHERE list_id = ?");
    $stmt->bind_param("i", $listId);

    if ($stmt->execute()) {
        echo "List successfully deleted.";
    } else {
        http_response_code(500);
        echo "Error deleting list.";
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo "Invalid request.";
}
?>