<?php
ob_start();
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login_submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");
    if ($conn->connect_error) {
        die("Connection error: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT user_id, username, role FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Store essential user data in PHP session
        $_SESSION['app_user'] = [
            'user_id' => (int)$user['user_id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];

        // Build redirect by role (normalize, accept 'admin' / 'administrator')
        $role = strtolower(trim((string)$user['role']));
        $is_admin = in_array($role, ['admin', 'administrator'], true);
        $redirect = ($is_admin)
            ? esc_url( home_url('/index.php/admin-dashboard/') )
            : esc_url( home_url('/index.php/users/') );

        // Console log the user info for debugging and then redirect
        echo '<script>
        try {
            const u = ' . json_encode(['user_id' => (int)$user['user_id'], 'role' => $user['role']], JSON_UNESCAPED_SLASHES) . ';
            console.log("[Login] Logged in user:", u);
        } catch (e) {}
        alert("Login successful!");
        window.location.href = ' . json_encode($redirect, JSON_UNESCAPED_SLASHES) . ';
        </script>';
    } else {
        $home = esc_url( home_url('/') );
        echo '<script>
        window.location.href = ' . json_encode($home, JSON_UNESCAPED_SLASHES) . ';
        alert("Login failed! Please check your credentials.");
        </script>';
    }

    $stmt->close();
    $conn->close();
}
?>

<div class="custom-login">
  <div class="ring">
    <i style="--clr:#00ff0a;"></i>
    <i style="--clr:#ff0057;"></i>
    <i style="--clr:#fffd44;"></i>

    <div class="login">
      <h2>Login</h2>

      <form id="login-form" method="POST">
        <div class="inputBx">
          <input type="text" name="username" placeholder="Username" required>
        </div>

        <div class="inputBx">
          <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="inputBx">
          <input type="submit" name="login_submit" value="Sign in">
        </div>
      </form>
    </div>
  </div>
</div>

<?php return ob_get_clean(); ?>
