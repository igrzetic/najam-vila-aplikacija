<?php
ob_start();

$conn = new mysqli("localhost", "root", "", "najam_vila_db");

if ($conn->connect_error) {
    echo "<p>alert('Error connecting to database!');</p>";
    return ob_get_clean();
}
    
$sql = "SELECT image_id, report_id, image_url, created_at FROM damage_report_images";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Damage report images</h2>";
    echo "<table class='custom-table'>";
    echo "<tr>
            <th>Image ID</th>
            <th>Report ID</th>
            <th>Image URL</th>
            <th>Created at</th>
         </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['image_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['report_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['image_url']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No damage report images found.</p>";
}
$conn->close();
?>

<?php
return ob_get_clean();
?>
