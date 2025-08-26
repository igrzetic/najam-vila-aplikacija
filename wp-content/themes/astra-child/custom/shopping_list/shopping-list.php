<?php
/* phpcs:disable Generic.Files.LineLength */
/**
 * Shopping List assets and shortcode for the Astra Child theme.
 *
 * Enqueues the CSS/JS, passes config to JS, and provides a [shopping_list] shortcode
 * to render the UI that the JavaScript binds to.
 *
 * PHP version 8.2
 *
 * @category Theme
 * @package  AstraChild\ShoppingList
 * @author   Maintainer <maintainer@example.com>
 * @license  https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2 or later
 * @link     https://example.com
 */

/* phpcs:disable PEAR.NamingConventions.ValidFunctionName.FunctionNoCapital, PEAR.NamingConventions.ValidFunctionName.FunctionNameInvalid, Generic.Files.LineLength */

/**
 * Enqueue Shopping List assets and localize configuration for JS.
 *
 * @return void
 */
function shopping_list_enqueue_assets()
{
    wp_enqueue_style(
        'custom-styles',
        get_stylesheet_directory_uri() . '/custom/custom-styles.css'
    );

    wp_enqueue_script(
        'shopping-list-js',
        get_stylesheet_directory_uri() . '/custom/shopping_list/shopping-list.js',
        array('jquery'),
        false,
        true
    );

    // Enqueue script for the View Shopping List UI
    wp_enqueue_script(
        'view-shopping-list-js',
        get_stylesheet_directory_uri() . '/custom/shopping_list/view-shopping-list.js',
        array(),
        false,
        true
    );

    // Provide config to the View Shopping List JS (use theme URI to avoid 404 in subdir installs)
    wp_localize_script(
        'view-shopping-list-js',
        'ViewShoppingListConfig',
        array(
            'fetchUrl' => get_stylesheet_directory_uri() . '/custom/shopping_list/fetch-list-items.php',
            'updateUrl' => get_stylesheet_directory_uri() . '/custom/shopping_list/update-list-items.php',
        )
    );

    // Pull app_user from PHP session if present
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    $session_user_id = 0;
    $session_user_role = null;
    if (!empty($_SESSION['app_user']) && is_array($_SESSION['app_user'])) {
        $session_user_id = isset($_SESSION['app_user']['user_id']) ? (int) $_SESSION['app_user']['user_id'] : 0;
        $session_user_role = isset($_SESSION['app_user']['role']) ? (string) $_SESSION['app_user']['role'] : null;
    }

    // Provide config to JS.
    wp_localize_script(
        'shopping-list-js',
        'ShoppingListConfig',
        array(
            'saveUrl' => get_stylesheet_directory_uri()
                . '/custom/shopping_list/save-shopping-list.php',
            'userId'  => $session_user_id ?: (get_current_user_id() ?: 0),
            'userRole' => $session_user_role,
            'dashboardUrl' => esc_url( home_url('/index.php/admin-dashboard/#shopping-list') ),
        )
    );
}
add_action('wp_enqueue_scripts', 'shopping_list_enqueue_assets');

/**
 * Shortcode callback to render Shopping List UI.
 *
 * Usage: [shopping_list]
 *
 * @return string HTML markup for the shopping list UI.
 */
function shopping_list_form_shortcode($atts = [])
{
    // Allow passing property_id via shortcode attribute or URL (?property_id=)
    $atts = shortcode_atts([
        'property_id' => 0,
    ], $atts, 'shopping_list');

    $prop_id = (int) $atts['property_id'];
    if ($prop_id <= 0 && isset($_GET['property_id'])) {
        $prop_id = (int) $_GET['property_id'];
    }

    // If property not fixed, fetch properties for a selector
    $properties = [];
    if ($prop_id <= 0) {
        $conn = @new mysqli('localhost', 'root', '', 'najam_vila_db');
        if (!$conn->connect_error) {
            $conn->set_charset('utf8mb4');
            // Using the new table name `propertys` as requested
            $sql = "SELECT property_id, property_name FROM propertys ORDER BY property_name";
            if ($res = $conn->query($sql)) {
                while ($row = $res->fetch_assoc()) {
                    $properties[] = [
                        'id' => (int) $row['property_id'],
                        'name' => (string) $row['property_name'],
                    ];
                }
                $res->free();
            }
            $conn->close();
        }
    }

    ob_start();
    ?>
    <form id="shopping-list-form">
        <h2>Create new shopping list</h2>
        <label for="property_id">Property ID:</label>
        <select id="property_id" name="property_id" required>
            <option value="" disabled selected>-- Select property --</option>
            <?php
            $conn = new mysqli("localhost", "root", "", "najam_vila_db");
            if (!$conn->connect_error) {
                $sql = "SELECT property_id, property_name FROM rental_objects";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value={$row['property_id']}>{$row['property_id']} - {$row['property_name']}</option>";
                    }
                }
                $conn->close();
            }
            ?>
        </select>
        
        <label for="list-name">List name:</label>
        <input type="text" id="list-name" name="list-name" placeholder="Enter list name">

        <div class="item-inputs">
            <input type="text" id="item-name" placeholder="Item name">
            <input type="number" id="item-quantity" placeholder="Quantity" min="1">
            <input type="number" id="item-price" placeholder="Price" min="0" step="0.01">
            <button type="button" id="add-item">+</button>
        </div>

        <ul id="items-list"></ul>

        <div class="totals">
            <span>Total Items: <span id="total-items">0</span></span>
            <span>Total Price: <span id="total-price">0.00</span> €</span>
        </div>

        <button type="submit" id="save-list">Save List</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('shopping_list', 'shopping_list_form_shortcode');

 /* phpcs:enable */
