<?php
function view_shopping_list_shortcode()
{
    ob_start();
    ?>
    <form id="view-shopping-list-form">
        <h2>View shopping list</h2>
        <label for="list-name">List name:</label>
        <select id="list-name" name="list-name" required>
            <option value="" disabled selected>-- Odaberite listu --</option>
            <?php
            $conn = new mysqli("localhost", "root", "", "najam_vila_db");
            if (!$conn->connect_error) {
                $sql = "SELECT list_id, list_name FROM shopping_lists";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value={$row['list_id']}>{$row['list_id']} - {$row['list_name']}</option>";
                    }
                }
                $conn->close();
            }
            ?>
        </select>

        <button type="button" id="view-list">View List</button>

        <ul id="items-list"></ul>

        <div class="totals">
            <span>Total Items: <span id="total-items">0</span></span>
            <span>Total Price: <span id="total-price">0.00</span> €</span>
        </div>

        <button type="button" id="update-list">Update List</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('view_shopping_list', 'view_shopping_list_shortcode');
