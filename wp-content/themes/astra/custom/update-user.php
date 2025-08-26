 <?php
 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
        http_response_code(500);
        echo "Greška pri spajanju na bazu!";
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("sssi", $username, $password, $role, $userId);

        if ($stmt->execute()) {
            echo "Korisnik uspješno ažuriran.";
        } else {
            http_response_code(500);
            echo "Greška pri ažuriranju korisnika.";
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