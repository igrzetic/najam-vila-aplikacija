<?php
ob_start();

$conn = new mysqli("localhost", "root", "", "najam_vila_db");

if ($conn->connect_error) {
    echo "<p>alert('Error connecting to database!');</p>";
    return ob_get_clean();
}

$sql = "SELECT report_id, property_id, user_id, description, status, severity, created_at, updated_at FROM damage_reports ORDER BY created_at DESC";
$result = $conn->query($sql);

echo "<h2>All damage reports</h2>";
echo "<table class='custom-table'>";
echo "<tr>
            <th>Report ID</th>
            <th>Property ID</th>
            <th>User ID</th>
            <th>Damage Description</th>
            <th>Status</th>
            <th>Severity</th>
            <th>Created at</th>
            <th>Updated at</th>
            <th>Action</th>
         </tr>";
    
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='damage-report-row' 
        data-report-id='" . $row['report_id'] . "'
        data-property-id='" . $row['property_id'] . "'
        data-user-id='" . $row['user_id'] . "'
        data-damage-description='" . $row['description'] . "'
        data-status='" . $row['status'] . "'
        data-severity='" . $row['severity'] . "'
        data-created-at='" . $row['created_at'] . "'
        data-updated-at='" . $row['updated_at'] . "'>";
        echo "<td>" . htmlspecialchars($row['report_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['property_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['severity']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "<td>" . htmlspecialchars($row['updated_at']) . "</td>";
        $nonce = wp_create_nonce('delete_damage_report');
        echo "<td><button type='button' class='button delete-damage-report-btn' data-nonce='" . esc_attr($nonce) . "' data-report-id='" . (int)$row['report_id'] . "'>Delete</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No damage reports found.</td></tr>";
}

echo "</table>";
$conn->close();
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Delete damage report
    document.querySelectorAll('.delete-damage-report-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const reportId = this.getAttribute('data-report-id');
            const nonce = this.getAttribute('data-nonce');
            
            if (confirm('Are you sure you want to delete this report?')) {
                fetch('<?php echo get_stylesheet_directory_uri(); ?>/damage_report/delete-damage-report.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'report_id=' + encodeURIComponent(reportId) + '&nonce=' + encodeURIComponent(nonce)
                })
                .then(res => {
                    if (!res.ok) { return res.text().then(t => { throw new Error(t || 'Delete failed'); }); }
                    return res.text();
                })
                .then(() => {
                    window.location.href = "<?php echo esc_url( home_url('/index.php/admin-dashboard/#damage-report') ); ?>";
                })
                .catch(err => alert(err.message));
            }
        });
    });
});
</script>

<?php
return ob_get_clean();
?>