<?php
ob_start(); // phpcs:ignore PEAR.Commenting.FileComment.Missing

$conn = new mysqli("localhost", "root", "", "najam_vila_db");

if ($conn->connect_error) {
    echo "<p>alert('Greška pri spajanju na bazu!');</p>";
    return ob_get_clean();
}

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

echo "<h2>Users list</h2>";
echo "<table class='custom-table'>";
echo "<thead><tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Password</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
         </tr></thead>";
echo "<tbody>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr
        class='user-row' data-id='" . $row['user_id'] . "' data-username='" . $row['username'] . "' data-password='" . $row['password'] . "' data-email='" . $row['email'] . "' data-role='" . $row['role'] . "'
        >";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['password']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td><button class='delete-btn' data-id='" . $row['user_id'] . "'>🗑️</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No users in database.</td></tr>";
}

echo "</tbody>";
echo "</table>";

$conn->close();
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const usersSection = document.getElementById('users');
    if (!usersSection) return;
    usersSection.querySelectorAll('.delete-btn').forEach(function (button) {
        button.addEventListener('click', function (e) {
          e.stopPropagation();
          const userId = this.getAttribute('data-id');
          
          if (confirm('Are you sure you want to delete this user?')) {
            fetch('<?php echo get_stylesheet_directory_uri(); ?>/custom/user_functions/delete-user.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'user_id=' + userId
            })
            .then(res => res.text())
            .then(() => {
              var targetUrl = "<?php echo esc_url( home_url('/index.php/admin-dashboard/#users') ); ?>";
              if (window.location.hash !== '#users') {
                if (window.history && typeof window.history.replaceState === 'function') {
                  window.history.replaceState(null, '', targetUrl);
                } else {
                  window.location.hash = '#users';
                }
              }
              window.location.reload();
            });
          }
        });
      });

      document.querySelectorAll('.user-row').forEach(function (row) {
        row.addEventListener('click', function () {
          document.querySelectorAll('.user-row').forEach(r => r.classList.remove('selected'));
          this.classList.add('selected');
          console.log('Selected row: ', this.getAttribute('data-id'));
          
          const username = this.getAttribute('data-username');
          const password = this.getAttribute('data-password');
          const email = this.getAttribute('data-email');
          const role = this.getAttribute('data-role');
          const id = this.getAttribute('data-id');

          const form = document.querySelector('#users_form');
          if (!form) return;

          form.querySelector('input[name="username"]').value = username;
          form.querySelector('input[name="password"]').value = password;
          form.querySelector('input[name="email"]').value = email;
          form.querySelector('select[name="role"]').value = role;

          // Append a hiden input for user_id or update it
          let hiddenInput = form.querySelector('input[name="user_id"]');
          if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'user_id';
            form.appendChild(hiddenInput);
          }
          hiddenInput.value = id;

          //  Show update button if exists, hide add button if needed
          const addBtn = form.querySelector('button[name="add_user_submit"]');
          const updateBtn = form.querySelector('button[name="update_user_submit"]');
          if (addBtn) addBtn.style.display = 'none';
          if (updateBtn) {
            const newUpdateBtn = updateBtn.cloneNode(true);
            updateBtn.parentNode.replaceChild(newUpdateBtn, updateBtn);
            newUpdateBtn.style.display = 'inline-block';
            newUpdateBtn.textContent = 'Update User';

            newUpdateBtn.addEventListener('click', function (e) {
              e.preventDefault();

              const form = document.querySelector('#users_form');
              if (!form) {
                console.error('Users form (#users_form) not found.');
                alert('Cannot find Users form on the page.');
                return;
              }
              const userId = form.querySelector('input[name="user_id"]').value;
              const username = form.querySelector('input[name="username"]').value;
              const password = form.querySelector('input[name="password"]').value;
              const email = form.querySelector('input[name="email"]').value;
              const role = form.querySelector('select[name="role"]').value;

              if (!userId || !username || !password || !email || !role) {
                alert("Please fill all fields.");
                return;
              }

              const formData = new URLSearchParams();
              formData.append('user_id', userId);
              formData.append('username', username);
              formData.append('password', password);
              formData.append('email', email);
              formData.append('role', role);

              fetch('<?php echo get_stylesheet_directory_uri(); ?>/custom/user_functions/update-user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded'},
                body: formData.toString()
              })
              .then(res => res.text())
              .then(response => {
                alert("User successfully updated.");
                var targetUrl = "<?php echo esc_url( home_url('/index.php/admin-dashboard/#users') ); ?>";
                if (window.location.hash !== '#users') {
                  if (window.history && typeof window.history.replaceState === 'function') {
                    window.history.replaceState(null, '', targetUrl);
                  } else {
                    window.location.hash = '#users';
                  }
                }
                window.location.reload();
              })
              .catch(err => {
                console.error("Error updating user:", err);
                alert("Error updating user.");
              });
            });
          }
        });
      });
    });
    </script>
    <?php
    return ob_get_clean();
?>
