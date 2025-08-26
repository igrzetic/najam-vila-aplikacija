<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyId = $_POST['property_id'] ?? null;
    $propertyName = $_POST['property_name'] ?? '';
    $propertyType = $_POST['property_type'] ?? '';
    $country = $_POST['country'] ?? '';
    $city = $_POST['city'] ?? '';
    $street = $_POST['street'] ?? '';
    $houseNumber = $_POST['house_number'] ?? '';
    $capacity = $_POST['capacity'] ?? '';
    $ownerId = $_POST['owner_id'] ?? '';

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
        http_response_code(500);
        echo "Greška pri spajanju na bazu!";
        exit;
    }

    $stmt = $conn->prepare("UPDATE rental_objects SET property_name = ?, property_type = ?, country = ?, city = ?, street = ?, house_number = ?, capacity = ?, owner_id = ? WHERE property_id = ?");
    if ($stmt) {
        $stmt->bind_param("ssssssiii", $propertyName, $propertyType, $country, $city, $street, $houseNumber, $capacity, $ownerId, $propertyId);

        if ($stmt->execute()) {
            echo "Objekt uspješno ažuriran.";
        } else {
            http_response_code(500);
            echo "Greška pri ažuriranju objekta.";
        }

        $stmt->close();
    } else {
        http_response_code(500);
        echo "Greška u pripremi upita.";
    }

    $conn->close();
} else {
    http_response_code(400);
    echo "Neispravan zahtjev.";
}
?>
