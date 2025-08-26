<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
        http_response_code(500);
        echo "Greška pri spajanju na bazu!";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo "Korisnik uspješno obrisan.";
    } else {
        http_response_code(500);
        echo "Greška pri brisanju korisnika.";
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo "Neispravan zahtjev.";
}
