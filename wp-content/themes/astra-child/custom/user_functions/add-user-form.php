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
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? '';

        $conn = new mysqli("localhost", "root", "", "najam_vila_db");

        if ($conn->connect_error) {
            $message = "<script>alert('Error connecting to database!');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $username, $password, $email, $role);
                if ($stmt->execute()) {
                    $redirect_url = function_exists('home_url')
                        ? rtrim(home_url('/index.php/admin-dashboard/'), '/') . '/#users'
                        : 'http://localhost/najam_vila_aplikacija/index.php/admin-dashboard/#users';
                    if (!headers_sent()) {
                        if (function_exists('wp_safe_redirect')) {
                            wp_safe_redirect($redirect_url);
                        } else {
                            header('Location: ' . $redirect_url);
                        }
                        exit;
                    } else {
                        echo '<script>window.location.href = ' . json_encode($redirect_url, JSON_UNESCAPED_SLASHES) . ';</script>';
                        exit;
                    }
                } else {
                    echo '<script>
                    alert("Error adding user.");
                    </script>';
                }
                $stmt->close();
            } else {
                $message = "<script>alert('Error preparing query.');</script>";
            }
            $conn->close();
        }
    }
?>

    <form id="users_form" method="POST">
        <h2>Add User</h2>
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Role:</label>
        <select name="role" required>
            <option value="" disabled selected>-- Select user type --</option>
            <option value="admin">Admin</option>
            <option value="owner">Owner</option>
            <option value="services">Services</option>
        </select>
        
        <button type="submit" name="add_user_submit">Add User</button>
        <button type="submit" name="update_user_submit" style="display: none;"></button>
    </form>
<?php
    return ob_get_clean();
?>

