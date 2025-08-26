<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    ob_start();

    $message = "";

    if (isset($_SESSION['user_message'])) {
        $message = "<script>alert('" . $_SESSION['user_message'] ."');</script>";
        unset($_SESSION['user_message']);
    }

    echo $message;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user_submit'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        $conn = new mysqli("localhost", "root", "", "najam_vila_db");

        if ($conn->connect_error) {
            $message = "<script>alert('Greška pri spajanju na bazu!');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $username, $password, $role);
                if ($stmt->execute()) {
                    echo '<script>
                    window.location.href = "http://localhost/najam_vila_aplikacija/index.php/users/";
                    alert("Korisnik uspješno dodan.");
                    </script>';
                    exit;
                } else {
                    echo '<script>
                    alert("Greška pri dodavanju korisnika.");
                    </script>';
                }
                $stmt->close();
            } else {
                $message = "<script>alert('Greška u pripremi upita.');</script>";
            }
            $conn->close();
        }
    }
?>

    <form id="users_form" method="POST">
        <h2>Dodaj korisnika</h2>
        <label>Korisničko ime:</label>
        <input type="text" name="username" required><br><br>

        <label>Lozinka:</label>
        <input type="password" name="password" required><br><br>

        <label>Uloga:</label>
        <select name="role" required>
            <option value="" disabled selected>-- Odaberite tip korisnika --</option>
            <option value="admin">Admin</option>
            <option value="owner">Owner</option>
            <option value="services">Services</option>
        </select><br><br>
        
        <button type="submit" name="add_user_submit">Dodaj korisnika</button>
        <button type="submit" name="update_user_submit" style="display: none;"></button>
    </form>
<?php
    return ob_get_clean();
?>
