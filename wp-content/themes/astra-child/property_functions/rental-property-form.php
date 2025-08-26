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

    // Only echo the flash message if set
    echo $message;
?>

    <form id="property_form" method="POST" action="<?php echo esc_url( get_stylesheet_directory_uri() . '/property_functions/handle-add-property.php' ); ?>">
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
