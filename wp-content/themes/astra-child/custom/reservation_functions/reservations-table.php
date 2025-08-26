<?php
ob_start(); // phpcs:ignore PEAR.Commenting.FileComment.Missing

$conn = new mysqli("localhost", "root", "", "najam_vila_db");

if ($conn->connect_error) {
    echo "<p>alert('Error connecting to database!');</p>";
    return ob_get_clean();
}

$sql = "SELECT * FROM reservations";
$result = $conn->query($sql);

    echo "<h2>Reservation list</h2>";
    echo "<table class='custom-table'>";
    echo "<tr>
            <th>Reservation ID</th>
            <th>Arrival date</th>
            <th>Departure date</th>
            <th>Guest name</th>
            <th>Adults</th>
            <th>Children</th>
            <th>Infants</th>
            <th>Pets</th>
            <th>Agency</th>
            <th>Special requests</th>
            <th>Earnings</th>
            <th>Property ID</th>
            <th>Action</th>
         </tr>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='reservation-row' 
        data-reservation-id='" . $row['reservation_id'] 
        . "' data-arrival-date='" . $row['arrival_date'] 
        . "' data-departure-date='" . $row['departure_date'] 
        . "' data-guest-name='" . $row['guest_name'] 
        . "' data-adults='" . $row['adults'] 
        . "' data-children='" . $row['children'] 
        . "' data-infants='" . $row['infants'] 
        . "' data-pets='" . $row['pets'] 
        . "' data-agency='" . $row['agency'] 
        . "' data-special-requests='" . $row['special_requests'] 
        . "' data-earnings='" . $row['earnings'] 
        . "' data-property-id='" . $row['property_id'] . "'>";
        echo "<td>" . htmlspecialchars($row['reservation_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['arrival_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['departure_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['guest_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['adults']) . "</td>";
        echo "<td>" . htmlspecialchars($row['children']) . "</td>";
        echo "<td>" . htmlspecialchars($row['infants']) . "</td>";
        echo "<td>" . htmlspecialchars($row['pets']) . "</td>";
        echo "<td>" . htmlspecialchars($row['agency']) . "</td>";
        echo "<td>" . htmlspecialchars($row['special_requests']) . "</td>";
        echo "<td>" . htmlspecialchars($row['earnings']) . "</td>";
        echo "<td>" . htmlspecialchars($row['property_id']) . "</td>";
        echo "<td><button class='delete-btn' data-reservation-id='" . $row['reservation_id'] . "'>🗑️</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='13'>No reservations in database.</td></tr>";
}
    echo "</table>";
    $conn->close();
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Brisanje rezervacije
    const reservationsSection = document.getElementById('reservations');
    if (!reservationsSection) return;
    reservationsSection.querySelectorAll('.delete-btn').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.stopPropagation();
            const reservationId = this.getAttribute('data-reservation-id');
            
            if (confirm('Are you sure you want to delete this reservation?')) {
                fetch('<?php echo get_stylesheet_directory_uri(); ?>/custom/reservation_functions/delete-reservation.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'reservation_id=' + reservationId
                })
                .then(res => res.text())
                .then(() => {
                    var targetUrl = "<?php echo esc_url( home_url('/index.php/admin-dashboard/#reservations') ); ?>";
                    if (window.location.hash !== '#reservations') {
                        if (window.history && typeof window.history.replaceState === 'function') {
                            window.history.replaceState(null, '', targetUrl);
                        } else {
                            window.location.hash = '#reservations';
                        }
                    }
                    window.location.reload();
                });
            }
        });
    });

    // Popunjavanje forme klikom na red
    reservationsSection.querySelectorAll('.reservation-row').forEach(function (row) {
        row.addEventListener('click', function () {
            reservationsSection.querySelectorAll('.reservation-row').forEach(r => r.classList.remove('selected'));
            this.classList.add('selected');
            console.log('Selected row: ', this.getAttribute('data-reservation-id'));

            const reservationId = this.getAttribute('data-reservation-id');
            const arrivalDate = this.getAttribute('data-arrival-date');
            const departureDate = this.getAttribute('data-departure-date');
            const guestName = this.getAttribute('data-guest-name');
            const adults = this.getAttribute('data-adults');
            const children = this.getAttribute('data-children');
            const infants = this.getAttribute('data-infants');
            const pets = this.getAttribute('data-pets');
            const agency = this.getAttribute('data-agency');
            const specialRequests = this.getAttribute('data-special-requests');
            const earnings = this.getAttribute('data-earnings');
            const propertyId = this.getAttribute('data-property-id');

            const form = document.querySelector('#reservations_form');
            if (!form) return;

            form.querySelector('input[name="arrival_date"]').value = arrivalDate;
            form.querySelector('input[name="departure_date"]').value = departureDate;
            const guestNameParts = guestName.split(' ');
            form.querySelector('input[name="guest_first_name"]').value = guestNameParts[0] || '';
            form.querySelector('input[name="guest_last_name"]').value = guestNameParts[1] || '';
            form.querySelector('input[name="adults"]').value = adults;
            form.querySelector('input[name="children"]').value = children;
            form.querySelector('input[name="infants"]').value = infants;
            form.querySelector('input[name="pets"]').value = pets;
            form.querySelector('input[name="agency"]').value = agency;
            form.querySelector('textarea[name="special_requests"]').value = specialRequests;
            form.querySelector('input[name="earnings"]').value = earnings;
            form.querySelector('select[name="property_id"]').value = propertyId;

            // Skriveno polje za ID rezervacije
            let hiddenInput = form.querySelector('input[name="reservation_id"]');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'reservation_id';
                form.appendChild(hiddenInput);
            }
            hiddenInput.value = reservationId;

            // Prikaz update gumba
            const addBtn = form.querySelector('button[name="add_reservation_submit"]');
            const updateBtn = form.querySelector('button[name="update_reservation_submit"]');
            if (addBtn) addBtn.style.display = 'none';
            if (updateBtn) {
                const newUpdateBtn = updateBtn.cloneNode(true);
                updateBtn.parentNode.replaceChild(newUpdateBtn, updateBtn);
                newUpdateBtn.style.display = 'inline-block';
                newUpdateBtn.textContent = 'Update Reservation';
                newUpdateBtn.type = "button";

                newUpdateBtn.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Target the reservations form explicitly to avoid picking up another form on the page
                    const form = document.querySelector('#reservations_form');
                    const reservationId = form.querySelector('input[name="reservation_id"]')?.value;
                    const arrivalDate = form.querySelector('input[name="arrival_date"]')?.value;
                    const departureDate = form.querySelector('input[name="departure_date"]')?.value;
                    const guestFirstName = form.querySelector('input[name="guest_first_name"]')?.value;
                    const guestLastName = form.querySelector('input[name="guest_last_name"]')?.value;
                    const adults = form.querySelector('input[name="adults"]')?.value;
                    const children = form.querySelector('input[name="children"]')?.value;
                    const infants = form.querySelector('input[name="infants"]')?.value;
                    const pets = form.querySelector('input[name="pets"]')?.value;
                    const agency = form.querySelector('input[name="agency"]')?.value;
                    const specialRequests = form.querySelector('textarea[name="special_requests"]')?.value;
                    const earnings = form.querySelector('input[name="earnings"]')?.value;
                    const propertyId = form.querySelector('select[name="property_id"]')?.value;

                    let missingFields = [];
                    if (!reservationId) missingFields.push("ID rezervacije");
                    if (!arrivalDate) missingFields.push("Datum dolaska");
                    if (!departureDate) missingFields.push("Datum odlaska");
                    if (!guestFirstName) missingFields.push("Ime gosta");
                    if (!guestLastName) missingFields.push("Prezime gosta");
                    if (!adults) missingFields.push("Broj odraslih");
                    if (!children) missingFields.push("Broj djece");
                    if (!infants) missingFields.push("Broj beba");
                    if (!pets) missingFields.push("Broj kućnih ljubimaca");
                    if (!agency) missingFields.push("Agencija");
                    if (!earnings) missingFields.push("Prihodi");
                    if (!propertyId) missingFields.push("ID objekta");

                    if (missingFields.length > 0) {
                        alert("Please fill all fields!\nMissing: " + missingFields.join(", "));
                        return;
                    }

                    const formData = new URLSearchParams();
                    formData.append('reservation_id', reservationId);
                    formData.append('arrival_date', arrivalDate);
                    formData.append('departure_date', departureDate);
                    formData.append('guest_name', guestFirstName + ' ' + guestLastName);
                    formData.append('adults', adults);
                    formData.append('children', children);
                    formData.append('infants', infants);
                    formData.append('pets', pets);
                    formData.append('agency', agency);
                    formData.append('special_requests', specialRequests);
                    formData.append('earnings', earnings);
                    formData.append('property_id', propertyId);

                    fetch('<?php echo get_stylesheet_directory_uri(); ?>/custom/reservation_functions/update-reservation.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData.toString()
                    })
                    .then(res => res.text())
                    .then(response => {
                        alert("Reservation successfully updated.");
                        var targetUrl = "<?php echo esc_url( home_url('/index.php/admin-dashboard/#reservations') ); ?>";
                        if (window.location.hash !== '#reservations') {
                            if (window.history && typeof window.history.replaceState === 'function') {
                                window.history.replaceState(null, '', targetUrl);
                            } else {
                                window.location.hash = '#reservations';
                            }
                        }
                        window.location.reload();
                    })
                    .catch(err => {
                        console.error("Error updating reservation:", err);
                        alert("Error updating reservation.");
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
