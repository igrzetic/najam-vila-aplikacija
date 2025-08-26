<?php
    ob_start();

    $conn = new mysqli("localhost", "root", "", "najam_vila_db");

    if ($conn->connect_error) {
            echo "<p>alert('Error connecting to database!');</p>";
            return ob_get_clean();
        } 
        
    $sql = "SELECT * FROM rental_objects";
    $result = $conn->query($sql);

    echo "<h2>Rental properties list</h2>";
    echo "<table class='custom-table'>";
    echo "<tr>
            <th>Property ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>Country</th>
            <th>City</th>
            <th>Street</th>
            <th>House number</th>
            <th>Capacity</th>
            <th>Owner ID</th>
            <th>Action</th>
         </tr>";

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr
            class='property-row' data-property-id='" . $row['property_id'] . "' data-property-name='" . $row['property_name'] . "' data-property-type='" . $row['property_type'] . "' data-country='" . $row['country'] . "' data-city='" . $row['city'] . "' data-street='" . $row['street'] . "' data-house_number='" . $row['house_number'] . "' data-capacity='" . $row['capacity'] . "' data-owner_id='" . $row['owner_id'] . "'
            >";
            echo "<td>" . htmlspecialchars($row['property_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['property_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['property_type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['country']) . "</td>";
            echo "<td>" . htmlspecialchars($row['city']) . "</td>";
            echo "<td>" . htmlspecialchars($row['street']) . "</td>";
            echo "<td>" . htmlspecialchars($row['house_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['capacity']) . "</td>";
            echo "<td>" . htmlspecialchars($row['owner_id']) . "</td>";
            echo "<td><button class='delete-btn' data-property-id='" . $row['property_id'] . "'>🗑️</button></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='10'>No rental properties in database.</td></tr>";
    }

    echo "</table>";

    $conn->close();
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      const propertiesSection = document.getElementById('properties');
      if (!propertiesSection) return;
      propertiesSection.querySelectorAll('.delete-btn').forEach(function (button) {
        button.addEventListener('click', function (e) {
          e.stopPropagation();
          const propertyId = this.getAttribute('data-property-id');
          
          if (confirm('Are you sure you want to delete this property?')) {
            fetch('<?php echo get_stylesheet_directory_uri(); ?>/property_functions/delete-property.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'property_id=' + propertyId
            })
            .then(res => res.text())
            .then(() => {
              var targetUrl = "<?php echo esc_url( home_url('/index.php/admin-dashboard/#properties') ); ?>";
              if (window.location.hash !== '#properties') {
                if (window.history && typeof window.history.replaceState === 'function') {
                  window.history.replaceState(null, '', targetUrl);
                } else {
                  window.location.hash = '#properties';
                }
              }
              window.location.reload();
            });
          }
        });
      });

      propertiesSection.querySelectorAll('.property-row').forEach(function (row) {
        row.addEventListener('click', function () {
          propertiesSection.querySelectorAll('.property-row').forEach(r => r.classList.remove('selected'));
          this.classList.add('selected');
          console.log('Selected row: ', this.getAttribute('data-property-id'));
          
          const propertyId = this.getAttribute('data-property-id');
          const propertyName = this.getAttribute('data-property-name');
          const propertyType = this.getAttribute('data-property-type');
          const country = this.getAttribute('data-country');
          const city = this.getAttribute('data-city');
          const street = this.getAttribute('data-street');
          const houseNumber = this.getAttribute('data-house_number');
          const capacity = this.getAttribute('data-capacity');
          const ownerId = this.getAttribute('data-owner_id');

          const form = document.querySelector('#property_form');
          if (!form) return;

          form.querySelector('input[name="property_name"]').value = propertyName;
          form.querySelector('select[name="property_type"]').value = propertyType;
          form.querySelector('select[name="country"]').value = country;
          form.querySelector('input[name="city"]').value = city;
          form.querySelector('input[name="street"]').value = street;
          form.querySelector('input[name="house_number"]').value = houseNumber;
          form.querySelector('input[name="capacity"]').value = capacity;
          form.querySelector('select[name="owner_id"]').value = ownerId;

            // Append a hidden input for property_id or update it
          let hiddenInput = form.querySelector('input[name="property_id"]');
          if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'property_id';
            form.appendChild(hiddenInput);
          }
          hiddenInput.value = propertyId;

          //  Show update button if exists, hide add button if needed
          const addBtn = form.querySelector('button[name="add_property_submit"]');
          const updateBtn = form.querySelector('button[name="update_property_submit"]');
          if (addBtn) addBtn.style.display = 'none';
          if (updateBtn) {
            const newUpdateBtn = updateBtn.cloneNode(true);
            updateBtn.parentNode.replaceChild(newUpdateBtn, updateBtn);
            newUpdateBtn.style.display = 'inline-block';
            newUpdateBtn.textContent = 'Update Property';
            // Ensure this button does not submit any form implicitly
            newUpdateBtn.type = 'button';

            newUpdateBtn.addEventListener('click', function (e) {
              e.preventDefault();

              // Target the correct property form explicitly
              const form = document.querySelector('#property_form');
              const propertyId = form.querySelector('input[name="property_id"]').value;
              const propertyName = form.querySelector('input[name="property_name"]').value;
              const propertyType = form.querySelector('select[name="property_type"]').value;
              const country = form.querySelector('select[name="country"]').value;
              const city = form.querySelector('input[name="city"]').value;
              const street = form.querySelector('input[name="street"]').value;
              const houseNumber = form.querySelector('input[name="house_number"]').value;
              const capacity = form.querySelector('input[name="capacity"]').value;
              const ownerId = form.querySelector('select[name="owner_id"]').value;

              if (!propertyId || !propertyName || !propertyType || !country || !city || !street || !houseNumber || !capacity || !ownerId) {
                alert("Please fill all fields.");
                return;
              }

              const formData = new URLSearchParams();
              formData.append('property_id', propertyId);
              formData.append('property_name', propertyName);
              formData.append('property_type', propertyType);
              formData.append('country', country);
              formData.append('city', city);
              formData.append('street', street);
              formData.append('house_number', houseNumber);
              formData.append('capacity', capacity);
              formData.append('owner_id', ownerId);

              fetch('<?php echo get_stylesheet_directory_uri(); ?>/property_functions/update-property.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded'},
                body: formData.toString()
              })
              .then(res => res.text())
              .then(response => {
                alert("Property successfully updated.");
                var targetUrl = "<?php echo esc_url( home_url('/index.php/admin-dashboard/#properties') ); ?>";
                if (window.location.hash !== '#properties') {
                  if (window.history && typeof window.history.replaceState === 'function') {
                    window.history.replaceState(null, '', targetUrl);
                  } else {
                    window.location.hash = '#properties';
                  }
                }
                window.location.reload();
              })
              .catch(err => {
                console.error("Error updating property:", err);
                alert("Error updating property.");
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
