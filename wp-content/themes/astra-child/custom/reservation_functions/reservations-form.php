<?php
    ob_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_reservation_submit'])) {
        $firstName = $_POST['guest_first_name'] ?? '';
        $lastName = $_POST['guest_last_name'] ?? '';
        
        $arrivalDate = $_POST['arrival_date'] ?? '';
        $departureDate = $_POST['departure_date'] ?? '';
        $guestName = trim($firstName . ' ' . $lastName);
        $adults = isset($_POST['adults']) ? (int) $_POST['adults'] : 0;
        $children = isset($_POST['children']) ? (int) $_POST['children'] : 0;
        $infants = isset($_POST['infants']) ? (int) $_POST['infants'] : 0;
        $pets = isset($_POST['pets']) ? (int) $_POST['pets'] : 0;
        $agency = $_POST['agency'] ?? '';
        $specialRequests = $_POST['special_requests'] ?? '';
        $earnings = isset($_POST['earnings']) ? (float) $_POST['earnings'] : 0;
        $propertyId = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;

        // Enable detailed mysqli errors for debugging
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $conn = new mysqli("localhost", "root", "", "najam_vila_db");
            $conn->set_charset('utf8mb4');

            $stmt = $conn->prepare("INSERT INTO reservations (arrival_date, departure_date, guest_name, adults, children, infants, pets, agency, special_requests, earnings, property_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // Types: s s s i i i i s s d i (arrival, departure, guest, adults, children, infants, pets, agency, requests, earnings, property_id)
            $stmt->bind_param("sssiiiissdi", $arrivalDate, $departureDate, $guestName, $adults, $children, $infants, $pets, $agency, $specialRequests, $earnings, $propertyId);
            $stmt->execute();

            // Success: redirect to admin dashboard -> #reservations
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            $_SESSION['user_message'] = 'Reservation added successfully.';
            $redirect_url = function_exists('home_url')
                ? home_url('/index.php/admin-dashboard/#reservations')
                : 'http://localhost/najam_vila_aplikacija/index.php/admin-dashboard/#reservations';
            $stmt->close();
            $conn->close();
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
        } catch (Throwable $e) {
            // Surface detailed error to the UI and log it
            error_log("Reservation insert failed: " . $e->getMessage());
            $safeMsg = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            echo '<script>alert("Error adding reservation: ' . $safeMsg . '");</script>';
        }
    }
?>

<form id="reservations_form" method="POST">
    <h2>Add reservation</h2>

    <div class="form-field">
        <label>Arrival date:</label>
        <input type="date" name="arrival_date" required>
    </div>

    <div class="form-field">
        <label>Departure date:</label>
        <input type="date" name="departure_date" required>
    </div>

    <div class="form-field">
        <label>Guest first name:</label>
        <input type="text" name="guest_first_name" required>
    </div>

    <div class="form-field">
        <label>Guest last name:</label>
        <input type="text" name="guest_last_name" required>
    </div>

    <div class="form-field">
        <label>Adults number:</label>
        <input type="number" name="adults" min="0" value="0" required>
    </div>

    <div class="form-field">
        <label>Children number:</label>
        <input type="number" name="children" min="0" value="0" required>
    </div>

    <div class="form-field">
        <label>Infants number:</label>
        <input type="number" name="infants" min="0" value="0" required>
    </div>

    <div class="form-field">
        <label>Pets number:</label>
        <input type="number" name="pets" min="0" value="0" required>
    </div>

    <div class="form-field">
        <label>Agency name:</label>
        <input type="text" name="agency" required>
    </div>

    <div class="form-field">
        <label>Incoms:</label>
        <input type="number" name="earnings" step="0.01" min="0" value="0" required>
    </div>

    <div class="form-field">
        <label>Property ID:</label>
        <select name="property_id" required>
            <option value="" disabled selected>-- Select property --</option>
            <?php
            $conn = new mysqli("localhost", "root", "", "najam_vila_db");
            if ($conn->connect_error) {
                echo "<script>alert('Error connecting to database!');</script>";
            } else {
                $sql = "SELECT property_id, property_name FROM rental_objects";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['property_id']) . "'>" . htmlspecialchars($row['property_name']) . "</option>";
                    }
                } else {
                    echo "<option value=''>No available objects</option>";
                }
                $conn->close();
            }
            ?>
        </select>
    </div>

    <div class="form-field full">
        <label>Special requests:</label>
        <textarea name="special_requests" rows="4" cols="50"></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" name="add_reservation_submit">Add reservation</button>
        <button type="submit" name="update_reservation_submit" style="display: none;">Update reservation</button>
        <button type="reset">Cancel</button>
    </div>
</form>

<script>
    // Capitalize first letter for text inputs
    document.querySelectorAll('input[type="text"]').forEach(input => {
    input.addEventListener('input', function() {
        if (this.value.length > 0) {
            this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
            }
        });
    });

    // Keep date constraints in sync: departure >= arrival
    const arrivalInput = document.querySelector('input[name="arrival_date"]');
    const departureInput = document.querySelector('input[name="departure_date"]');

    if (arrivalInput && departureInput) {
        // Set min on departure when arrival changes
        arrivalInput.addEventListener('change', function () {
            departureInput.min = this.value || '';
        });

        // Prevent choosing a departure earlier than arrival
        departureInput.addEventListener('change', function () {
            if (arrivalInput.value && departureInput.value) {
                const a = new Date(arrivalInput.value);
                const d = new Date(departureInput.value);
                if (a > d) {
                    alert('Departure date cannot be earlier than arrival date.');
                    this.value = '';
                }
            }
        });
    }


</script>
<?php
    return ob_get_clean();
?>
