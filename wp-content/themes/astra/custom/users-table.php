<?php
    ob_start();

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
            echo "<p>alert('Greška pri spajanju na bazu!');</p>";
            return ob_get_clean();
        } 
    
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);

    echo "<h2>Popis korisnika</h2>";
    echo "<table class='custom-table'>";
    echo "<tr>
            <th>ID</th>
            <th>Korisničko ime</th>
            <th>Lozinka</th>
            <th>Uloga</th>
            <th>Akcija</th>
         </tr>";

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr
            class='user-row' data-id='" . $row['user_id'] . "' data-username='" . $row['username'] . "' data-password='" . $row['password'] . "' data-role='" . $row['role'] . "'
            >";
            echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['password']) . "</td>";
            echo "<td>" . htmlspecialchars($row['role']) . "</td>";
            echo "<td><button class='delete-btn' data-id='" . $row['user_id'] . "'>🗑️</button></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Nema korisnika u bazi.</td></tr>";
    }

    echo "</table>";

    $conn->close();
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.delete-btn').forEach(function (button) {
        button.addEventListener('click', function () {
          const userId = this.getAttribute('data-id');
          
          if (confirm('Jeste li sigurni da želite obrisati ovog korisnika?')) {
            fetch('<?php echo get_stylesheet_directory_uri(); ?>/custom/user_functions/delete-user.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'user_id=' + userId
            })
            .then(res => res.text())
            .then(response => location.reload());
          }
        });
      });

      document.querySelectorAll('.user-row').forEach(function (row) {
        row.addEventListener('click', function () {
          document.querySelectorAll('.user-row').forEach(r => r.classList.remove('selected'));
          this.classList.add('selected');
          console.log('Odabrani redak: ', this.getAttribute('data-id'));
          
          const username = this.getAttribute('data-username');
          const password = this.getAttribute('data-password');
          const role = this.getAttribute('data-role');
          const id = this.getAttribute('data-id');

          const form = document.querySelector('#users_form');
          if (!form) return;

          form.querySelector('input[name="username"]').value = username;
          form.querySelector('input[name="password"]').value = password;
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
            newUpdateBtn.textContent = 'Ažuriraj korisnika';

            newUpdateBtn.addEventListener('click', function (e) {
              e.preventDefault();

              const form = document.querySelector('form');
              const userId = form.querySelector('input[name="user_id"]')?.value;
              const username = form.querySelector('input[name="username"]')?.value;
              const password = form.querySelector('input[name="password"]')?.value;
              const role = form.querySelector('select[name="role"]')?.value;

              if (!userId || !username || !password || !role) {
                alert("Molimo unesite sve podatke.");
                return;
              }

              const formData = new URLSearchParams();
              formData.append('user_id', userId);
              formData.append('username', username);
              formData.append('password', password);
              formData.append('role', role);

              fetch('<?php echo get_stylesheet_directory_uri(); ?>/custom/user_functions/update-user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded'},
                body: formData.toString()
              })
              .then(res => res.text())
              .then(response => {
                alert("Korisnik uspješno ažuriran.");
                location.reload();
              })
              .catch(err => {
                console.error("Greška pri ažuriranju korisnika:", err);
                alert("Greška pri ažuriranju korisnika.");
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