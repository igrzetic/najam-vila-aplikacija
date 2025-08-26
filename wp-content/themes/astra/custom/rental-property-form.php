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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_property_submit'])) {
        $propertyName = $_POST['property_name'] ?? '';
        $propertyType = $_POST['property_type'] ?? '';
        $country = $_POST['country'] ?? '';
        $city = $_POST['city'] ?? '';
        $street = $_POST['street'] ?? '';
        $houseNumber = $_POST['house_number'] ?? '';
        $capacity = isset($_POST['capacity']) ? (int) $_POST['capacity'] : 0;
        $ownerId = isset($_POST['owner_id']) ? (int) $_POST['owner_id'] : 0;

        $conn = new mysqli("localhost", "root", "", "najam_vila_db");

        if ($conn->connect_error) {
            $message = "<script>alert('Greška pri spajanju na bazu!');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO rental_objects (property_name, property_type, country, city, street, house_number, capacity, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssssssii", $propertyName, $propertyType, $country, $city, $street, $houseNumber, $capacity, $ownerId);
                if ($stmt->execute()) {
                    // Flash message + server-side redirect to avoid blank content after JS + exit
                    $_SESSION['user_message'] = 'Objekt uspješno dodan.';
                    $redirect_url = function_exists('home_url')
                        ? home_url('/index.php/propertys/')
                        : 'http://localhost/najam_vila_aplikacija/index.php/propertys/';
                    if (function_exists('wp_safe_redirect')) {
                        wp_safe_redirect($redirect_url);
                    } else {
                        header('Location: ' . $redirect_url);
                    }
                    exit;
                } else {
                    echo '<script>
                    "Error: " . $stmt->error;
                    alert("Greška pri dodavanju objekta.");
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

    <form id="property_form" method="POST">
        <h2>Dodaj objekt</h2>
        <label>Naziv objekta:</label>
        <input type="text" name="property_name" required><br><br>

        <label>Tip objekta:</label>
        <select name="property_type" required>
            <option value="" disabled selected>-- Odaberite tip objekta --</option>
            <option value="apartman">Apartman</option>
            <option value="kuća">Kuća</option>
            <option value="vila">Vila</option>
            <option value="kuća za odmor">Kuća za odmor</option>
            <option value="studio">Studio</option>
            <option value="mobilna kućica">Mobilna kućica</option>
        </select><br><br>

        <label>Država:</label>
        <select name="country" required>
            <option value="" disabled selected>-- Odaberite državu --</option>
        </select><br><br>

        <label>Grad:</label>
        <input type="text" name="city" required><br><br>

        <label>Ulica:</label>
        <input type="text" name="street" required><br><br>

        <label>Kućni broj:</label>
        <input type="text" name="house_number" required><br><br>

        <label>Kapacitet:</label>
        <input type="number" name="capacity" required min="1"><br><br>

        <label>ID Vlasnika:</label>
        <select name="owner_id" required>
        <option value="" disabled selected>-- Odaberite vlasnika --</option>
        <?php
        // Dohvati sve vlasnike (role = 'owner')
        $conn = new mysqli("localhost", "root", "", "najam_vila_db");
        if (!$conn->connect_error) {
            $owners = $conn->query("SELECT user_id, username FROM users WHERE role = 'owner'");
            while ($row = $owners->fetch_assoc()) {
                echo "<option value='{$row['user_id']}'>{$row['username']}</option>";
            }
            $conn->close();
        }
        ?>
    </select><br><br>
        
        <button type="submit" name="add_property_submit">Dodaj objekt</button>
        <button type="submit" name="update_property_submit" style="display: none;"></button>
    </form>

<script>
// document.querySelector('#property_form').addEventListener('submit', function(e) {
//     e.preventDefault(); // spriječi automatski reload
//
//     const formData = new FormData(this);
//     console.log("✅ Spriječen reload. Podaci za slanje:");
//
//     formData.forEach((value, key) => {
//         console.log(`${key} = ${value} (tip: ${typeof value})`);
//     });
// });

    document.addEventListener('DOMContentLoaded', function() {
        fetch('https://restcountries.com/v3.1/all?fields=name')
            .then(response => response.json())
            .then(data => {
                if (!Array.isArray(data)) {
                    throw new Error("Neispravan format podataka");
                }
                const countrySelect = document.querySelector('select[name="country"]');
                const countries = data.map(country => country.name.common);
                countries.sort((a, b) => a.localeCompare(b));
                countries.forEach(country => {
                    const option = document.createElement('option');
                    option.value = country;
                    option.textContent = country;
                    countrySelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Greška pri dohvaćanju država:', error);
            });
    });

    // Formatiranje unosa na prvo veliko slovo za text inpute
    document.querySelectorAll('input[type="text"]').forEach(input => {
    input.addEventListener('input', function() {
        if (this.value.length > 0) {
            this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
        }
    });
});
</script>
<?php
    return ob_get_clean();
?>
