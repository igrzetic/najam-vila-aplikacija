<?php
ob_start();
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");
    if ($conn->connect_error) {
        die("Greška pri spajanju: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        echo '<script>
        window.location.href = "http://localhost/najam_vila_aplikacija/index.php/users/";
        alert("Prijava uspješna!");
        </script>';
    } else {
        echo '<script>
        window.location.href = "http://localhost/najam_vila_aplikacija/";
        alert("Prijava neuspješna! Provjerite podatke.");
        </script>';
    }

    $stmt->close();
    $conn->close();
}
?>

    <form method="POST">
        <h2>Prijava</h2>
        <label>Korisničko ime:</label>
        <input type="text" name="username" required><br><br>
        <label>Lozinka:</label>
        <input type="password" name="password" required><br><br>
        <button type="submit" name="login_submit">Prijavi se</button>
    </form>

<?php return ob_get_clean(); ?>