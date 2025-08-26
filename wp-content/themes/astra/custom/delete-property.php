<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    $propertyId = intval($_POST['property_id']);

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
        http_response_code(500);
        echo "Greška pri spajanju na bazu!";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM rental_objects WHERE property_id = ?");
    $stmt->bind_param("i", $propertyId);

    if ($stmt->execute()) {
        echo "Objekt uspješno obrisan.";
    } else {
        http_response_code(500);
        echo "Greška pri brisanju objekta.";
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400);
    echo "Neispravan zahtjev.";
}
?>