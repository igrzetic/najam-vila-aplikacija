<?php
    ob_start();

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
        echo "<p>alert('Greška pri spajanju na bazu!');</p>";
        return ob_get_clean();
    }

    $sql = "SELECT * FROM reservations";
    $result = $conn->query($sql);

    echo "<h2>Popis rezervacija</h2>";
    echo "<table class='custom-table'>";
    echo "<tr>
            <th>ID</th>
            <th>Datum dolaska</th>
            <th>Datum odlaska</th>
            <th>Gost</th>
            <th>Broj odraslih</th>
            <th>Broj djece</th>
            <th>Broj beba</th>
            <th>Broj kućnih ljubimaca</th>
            <th>Agencija</th>
            <th>Posebni zahtjevi</th>
            <th>Prihodi</th>
            <th>ID objekta</th>
            <th>Akcija</th>
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
        echo "<tr><td colspan='13'>Nema rezervacija u bazi.</td></tr>";
    }

    echo "</table>";

    $conn->close();
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Brisanje rezervacije
    document.querySelectorAll('.delete-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const reservationId = this.getAttribute('data-reservation-id');
            
            if (confirm('Jeste li sigurni da želite obrisati ovu rezervaciju?')) {
                fetch('<?php echo get_stylesheet_directory_uri(); ?>/custom/owner_dashboard/delete-reservation.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'reservation_id=' + reservationId
                })
                .then(res => res.text())
                .then(response => location.reload());
            }
        });
    });

    // Popunjavanje forme klikom na red
    document.querySelectorAll('.reservation-row').forEach(function (row) {
        row.addEventListener('click', function () {
            document.querySelectorAll('.reservation-row').forEach(r => r.classList.remove('selected'));
            this.classList.add('selected');
            console.log('Odabrani redak: ', this.getAttribute('data-reservation-id'));

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
                newUpdateBtn.textContent = 'Ažuriraj Rezervaciju';
                newUpdateBtn.type = "button";

                newUpdateBtn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const form = document.querySelector('form');
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
                        alert("Molimo unesite sve podatke!\nNedostaju: " + missingFields.join(", "));
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

                    fetch('<?php echo get_stylesheet_directory_uri(); ?>/custom/owner_dashboard/update-reservation.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: formData.toString()
                    })
                    .then(res => res.text())
                    .then(response => {
                        alert("Rezervacija uspješno ažurirana.");
                        location.reload();
                    })
                    .catch(err => {
                        console.error("Greška pri ažuriranju rezervacije:", err);
                        alert("Greška pri ažuriranju rezervacije.");
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