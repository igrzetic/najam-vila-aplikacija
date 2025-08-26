<?php
ob_start();

$conn = new mysqli("localhost", "root", "", "najam_vila_db");

if ($conn->connect_error) {
    echo "<p>alert('Error connecting to database!');</p>";
    return ob_get_clean();
}
// Build query with optional list_id filter
$sql = "SELECT item_id, list_id, item_name, quantity, price, status, added_at FROM list_items";
if (isset($list_id) && $list_id !== null && $list_id !== '') {
    $sql .= " WHERE list_id = ?";
}

$sql .= " ORDER BY added_at DESC, item_id DESC";

if (strpos($sql, '?') !== false) {
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $lid = (int) $list_id;
        $stmt->bind_param('i', $lid);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = false;
    }
} else {
    $result = $conn->query($sql);
}

echo "<h2>All shopping list items</h2>";
echo "<table class='custom-table'>";
echo "<tr>
            <th>List ID</th>
            <th>Item ID</th>
            <th>Item name</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Status</th>
            <th>Added at</th>
         </tr>";
    
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='shopping-list-item-row' 
        data-item-id='" . $row['item_id'] . "'
        data-list-id='" . $row['list_id'] . "'
        data-item-name='" . $row['item_name'] . "'
        data-quantity='" . $row['quantity'] . "'
        data-price='" . $row['price'] . "'
        data-status='" . $row['status'] . "'
        data-added-at='" . $row['added_at'] . "'>";
        echo "<td>" . htmlspecialchars($row['list_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
        echo "<td>" . htmlspecialchars($row['price']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['added_at']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8'>No items created.</td></tr>";
}
echo "</table>";
$conn->close();
?>

<?php
return ob_get_clean();
?>
