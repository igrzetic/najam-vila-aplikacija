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
            $message = "<script>alert('Error connecting to database!');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO rental_objects (property_name, property_type, country, city, street, house_number, capacity, owner_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssssssii", $propertyName, $propertyType, $country, $city, $street, $houseNumber, $capacity, $ownerId);
                if ($stmt->execute()) {
                    // Flash message + server-side redirect to admin dashboard -> #properties
                    $_SESSION['user_message'] = 'Property successfully added.';
                    $redirect_url = function_exists('home_url')
                        ? home_url('/index.php/admin-dashboard/#properties')
                        : 'http://localhost/najam_vila_aplikacija/index.php/admin-dashboard/#properties';
                    if (!headers_sent()) {
                        if (function_exists('wp_safe_redirect')) {
                            wp_safe_redirect($redirect_url);
                        } else {
                            header('Location: ' . $redirect_url);
                        }
                        exit;
                    } else {
                        echo '<script>window.location.href = "' . $redirect_url . '";</script>';
                        exit;
                    }
                } else {
                    // On failure, set flash and redirect back to Properties tab
                    $_SESSION['user_message'] = 'Error adding property: ' . $stmt->error;
                    $redirect_url = function_exists('home_url')
                        ? home_url('/index.php/admin-dashboard/#properties')
                        : 'http://localhost/najam_vila_aplikacija/index.php/admin-dashboard/#properties';
                    if (!headers_sent()) {
                        if (function_exists('wp_safe_redirect')) {
                            wp_safe_redirect($redirect_url);
                        } else {
                            header('Location: ' . $redirect_url);
                        }
                        exit;
                    } else {
                        echo '<script>window.location.href = "' . $redirect_url . '";</script>';
                        exit;
                    }
                }
                $stmt->close();
            } else {
                $message = "<script>alert('Error preparing query.');</script>";
            }
            $conn->close();
        }
    }
    // Only echo the message if we did not just redirect.
    echo $message;
?>

    <form id="property_form" method="POST">
        <h2>Add Property</h2>
        <label>Property name:</label>
        <input type="text" name="property_name" required>

        <label>Property type:</label>
        <select name="property_type" required>
            <option value="" disabled selected>-- Select property type --</option>
            <option value="apartment">Apartment</option>
            <option value="house">House</option>
            <option value="villa">Villa</option>
            <option value="holiday house">Holiday house</option>
            <option value="studio">Studio</option>
            <option value="mobile house">Mobile house</option>
        </select>

        <label>Country:</label>
        <select name="country" required>
            <option value="" disabled selected>-- Select country --</option>
        </select>

        <label>City:</label>
        <input type="text" name="city" required>

        <label>Street:</label>
        <input type="text" name="street" required>

        <label>House number:</label>
        <input type="text" name="house_number" required>

        <label>Capacity:</label>
        <input type="number" name="capacity" required min="1">

        <label>Owner ID:</label>
        <select name="owner_id" required>
        <option value="" disabled selected>-- Select owner --</option>
        <?php
        // Fetch all owners (role = 'owner')
        $conn = new mysqli("localhost", "root", "", "najam_vila_db");
        if (!$conn->connect_error) {
            $owners = $conn->query("SELECT user_id, username FROM users WHERE role = 'owner'");
            while ($row = $owners->fetch_assoc()) {
                echo "<option value='{$row['user_id']}'>{$row['user_id']} - {$row['username']}</option>";
            }
            $conn->close();
        }
        ?>
    </select>
        
        <button type="submit" name="add_property_submit">Add Property</button>
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
                    throw new Error("Invalid data format");
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
                console.error('Error fetching countries:', error);
            });
    });

    // Capitalize first letter for text inputs
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
