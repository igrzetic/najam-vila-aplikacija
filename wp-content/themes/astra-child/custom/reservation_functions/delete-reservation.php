<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $reservationId = intval($_POST['reservation_id']);

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
        http_response_code(500);
        echo "Greška pri spajanju na bazu!";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM reservations WHERE reservation_id = ?");
    $stmt->bind_param("i", $reservationId);

    if ($stmt->execute()) {
        echo "Rezervacija uspješno obrisana.";
    } else {
        http_response_code(500);
        echo "Greška pri brisanju rezervacije.";
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo "Neispravan zahtjev.";
}
?>
