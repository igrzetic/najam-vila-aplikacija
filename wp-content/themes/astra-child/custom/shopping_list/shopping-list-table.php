<?php
ob_start();

$conn = new mysqli("localhost", "root", "", "najam_vila_db");

if ($conn->connect_error) {
    echo "<p>alert('Database connection error!');</p>";
    return ob_get_clean();
}
    
$sql = "SELECT * FROM shopping_lists";
$result = $conn->query($sql);

    echo "<h2>All shopping lists</h2>";
    echo "<table class='custom-table'>";
    echo "<tr>
            <th>List ID</th>
            <th>User ID</th>
            <th>Property ID</th>
            <th>List name</th>
            <th>Created at</th>
            <th>Action</th>
         </tr>";
    
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='shopping-list-row' 
        data-list-id='" . $row['list_id'] . "'
        data-user-id='" . $row['user_id'] . "'
        data-property-id='" . $row['property_id'] . "'
        data-list-name='" . $row['list_name'] . "'
        data-created-at='" . $row['created_at'] . "'>";
        echo "<td>" . htmlspecialchars($row['list_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['property_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['list_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "<td><button class='delete-btn' data-list-id='" . $row['list_id'] . "'>🗑️</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No lists created.</td></tr>";
}
echo "</table>";
$conn->close();
?>
            
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Delete list
        document.querySelectorAll('.delete-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const listId = this.getAttribute('data-list-id');
                
                if (confirm('Are you sure you want to delete this list?')) {
                    fetch('<?php echo get_stylesheet_directory_uri(); ?>/custom/shopping_list/delete-shopping-list.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'list_id=' + listId
                    })
                    .then(res => res.text())
                    .then(() => {
                        window.location.href = "<?php echo esc_url( home_url('/index.php/admin-dashboard/#shopping-list') ); ?>";
                    });
                }
            });
        });
    });
</script>
<?php
return ob_get_clean();
?>