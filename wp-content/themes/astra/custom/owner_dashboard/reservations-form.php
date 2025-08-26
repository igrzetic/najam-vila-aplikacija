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



        $conn = new mysqli("localhost", "root", "", "najam_vila_db");

        if ($conn->connect_error) {
            echo "<script>alert('Greška pri spajanju na bazu!');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO reservations (arrival_date, departure_date, guest_name, adults, children, infants, pets, agency, special_requests, earnings, property_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssiiisssdi", $arrivalDate, $departureDate, $guestName, $adults, $children, $infants, $pets, $agency, $specialRequests, $earnings, $propertyId);
                if ($stmt->execute()) {
                    echo '<script>
                    window.location.href = "http://localhost/najam_vila_aplikacija/index.php/propertys/";
                    alert("Rezervacija uspješno dodana.");
                    </script>';
                    exit;
                } else {
                    echo '<script>alert("Greška pri dodavanju rezervacije: ' . htmlspecialchars($stmt->error) . '");</script>';
                }
                $stmt->close();
            } else {
                echo "<script>alert('Greška u pripremi upita.');</script>";
            }
            $conn->close();
        }
    }
?>

<form id="reservations_form" method="POST">
    <h2>Dodaj rezervaciju</h2>
    <label>Datum dolaska:</label>
    <input type="date" name="arrival_date" required><br><br>

    <label>Datum odlaska:</label>
    <input type="date" name="departure_date" required><br><br>

    <label>Ime gosta:</label>
    <input type="text" name="guest_first_name" required><br><br>

    <label>Prezime gosta:</label>
    <input type="text" name="guest_last_name" required><br><br>

    <label>Broj odraslih:</label>
    <input type="number" name="adults" min="0" value="0" required><br><br>

    <label>Broj djece:</label>
    <input type="number" name="children" min="0" value="0" required><br><br>

    <label>Broj beba:</label>
    <input type="number" name="infants" min="0" value="0" required><br><br>

    <label>Broj kućnih ljubimaca:</label>
    <input type="number" name="pets" min="0" value="0" required><br><br>

    <label>Naziv agencije:</label>
    <input type="text" name="agency" required><br><br>

    <label>Posebni zahtjevi:</label>
    <textarea name="special_requests" rows="4" cols="50"></textarea><br><br>

    <label>Prihodi:</label>
    <input type="number" name="earnings" step="0.01" min="0" value="0" required><br><br>

    <label>ID objekta:</label>
    <select name="property_id" required>
        <option value="" disabled selected>-- Odaberite objekt --</option>
        <?php
        $conn = new mysqli("localhost", "root", "", "najam_vila_db");
        if ($conn->connect_error) {
            echo "<script>alert('Greška pri spajanju na bazu!');</script>";
        } else {
            $sql = "SELECT property_id, property_name FROM rental_objects";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['property_id']) . "'>" . htmlspecialchars($row['property_name']) . "</option>";
                }
            } else {
                echo "<option value=''>Nema dostupnih objekata</option>";
            }
            $conn->close();
        }
        ?>
    </select><br><br>

    <button type="submit" name="add_reservation_submit">Dodaj rezervaciju</button>
    <button type="submit" name="update_reservation_submit" style="display: none;"></button>
    <br><br><button type="reset">Poništi</button>
</form>

<script>
    // Formatiranje unosa na prvo veliko slovo za text inpute
    document.querySelectorAll('input[type="text"]').forEach(input => {
    input.addEventListener('input', function() {
        if (this.value.length > 0) {
            this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
            }
        });
    });

    document.querySelector('#reservations_form').addEventListener('submit', function(e) {
        e.preventDefault(); // spriječi automatski reload, ručno ćemo poslati nakon provjere

        const formData = new FormData(this);
        console.log("✅ Spriječen reload. Podaci za slanje:");

        formData.forEach((value, key) => {
            console.log(`${key} = ${value} (tip: ${typeof value})`);
        });

        let valid = true;
        let missingFields = [];

        this.querySelectorAll('input, select, textarea').forEach(e1 => {
            e1.style.border = ""; // resetiraj stil
        });

        this.querySelectorAll('[required]').forEach(e1 => {
            if (!e1.value || e1.value.trim() === "") {
                valid = false;
                missingFields.push(e1.previousElementSibling?.innerText || e1.name || 'field');
                e1.style.border = "2px solid red"; // označi obavezna polja
            }
        });

        if (!valid) {
            // spriječi slanje ako nisu ispunjena obavezna polja
            alert("Molimo ispunite sva polja!\nNedostaje: " + missingFields.join(', '));
            return false;
        }

        // Ako je sve u redu, pošalji formu i pusti PHP handler (gore u datoteci) da upiše u bazu
        e.currentTarget.submit();
    });
</script>
<?php
    return ob_get_clean();
?>