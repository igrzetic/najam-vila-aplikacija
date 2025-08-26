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
?>

    <form id="users_form" method="POST" action="<?php echo esc_url( get_stylesheet_directory_uri() . '/custom/user_functions/handle-add-user.php' ); ?>">
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

