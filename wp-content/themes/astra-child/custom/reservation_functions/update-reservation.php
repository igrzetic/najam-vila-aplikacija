<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reservationId = isset($_POST['reservation_id']) ? (int) $_POST['reservation_id'] : 0;
        $arrivalDate = $_POST['arrival_date'] ?? '';
        $departureDate = $_POST['departure_date'] ?? '';
        $guestName = $_POST['guest_name'] ?? '';
        $adults = isset($_POST['adults']) ? (int) $_POST['adults'] : 0;
        $children = isset($_POST['children']) ? (int) $_POST['children'] : 0;
        $infants = isset($_POST['infants']) ? (int) $_POST['infants'] : 0;
        $pets = isset($_POST['pets']) ? (int) $_POST['pets'] : 0;
        $agency = $_POST['agency'] ?? '';
        $specialRequests = $_POST['special_requests'] ?? '';
        $earnings = isset($_POST['earnings']) ? (float) $_POST['earnings'] : 0;
        $propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;

        $conn = new mysqli("localhost", "root", "", "najam_vila_db");

        if ($conn->connect_error) {
            http_response_code(500);
            echo "Greška pri spajanju na bazu!";
            exit;
        }

        $stmt = $conn->prepare("UPDATE reservations SET arrival_date = ?, departure_date = ?, guest_name = ?, adults = ?, children = ?, infants = ?, pets = ?, agency = ?, special_requests = ?, earnings = ?, property_id = ? WHERE reservation_id = ?");

        if ($stmt) {
            $stmt->bind_param(
                "sssiiiissdii", 
                $arrivalDate, 
                $departureDate, 
                $guestName, 
                $adults, 
                $children, 
                $infants, 
                $pets, 
                $agency, 
                $specialRequests, 
                $earnings, 
                $propertyId, 
                $reservationId
            );
            if ($stmt->execute()) {
                echo '<script>alert("Rezervacija uspješno ažurirana.");</script>';
            } else {
                echo '<script>alert("Greška pri ažuriranju rezervacije: ' . htmlspecialchars($stmt->error) . '");</script>';
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
