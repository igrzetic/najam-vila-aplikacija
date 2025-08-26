<?php
/**
 * JSON endpoint to save a shopping list with items.
 *
 * Accepts POST JSON and returns a JSON response in the shape:
 * { success: bool, list_id?: int, error?: string }
 *
 * PHP version 8.2
 *
 * @category Theme
 * @package  AstraChild\ShoppingList
 * @author   Maintainer <maintainer@example.com>
 * @license  https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2 or later
 * @link     https://example.com
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

$user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0; // 0 => guest
// Prefer session user if available
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}
if (!empty($_SESSION['app_user']) && is_array($_SESSION['app_user'])) {
    $sess_uid = isset($_SESSION['app_user']['user_id']) ? (int) $_SESSION['app_user']['user_id'] : 0;
    if ($sess_uid > 0) {
        $user_id = $sess_uid;
    }
}
$list_name = isset($data['list_name']) ? trim((string)$data['list_name']) : '';
$items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
// Optional property id (may be required if DB schema enforces it)
$req_property_id = isset($data['property_id']) ? (int)$data['property_id'] : 0;

if ($list_name === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'List name is required']);
    exit;
}
if (empty($items)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'At least one item is required']);
    exit;
}

$mysqli = new mysqli('localhost', 'root', '', 'najam_vila_db');
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection error']);
    exit;
}

$mysqli->set_charset('utf8mb4');
$mysqli->begin_transaction();

try {
    // Insert list; support schemas with or without property_id column
    $list_id = 0;
    $inserted = false;
    // Try extended insert first (with property_id)
    $stmtList = $mysqli->prepare('INSERT INTO shopping_lists (user_id, list_name, property_id) VALUES (NULLIF(?, 0), ?, NULLIF(?, 0))');
    if ($stmtList) {
        $stmtList->bind_param('isi', $user_id, $list_name, $req_property_id);
        if ($stmtList->execute()) {
            $list_id = $stmtList->insert_id;
            $inserted = true;
        }
        $stmtList->close();
    }
    if (!$inserted) {
        // Fallback to legacy schema without property_id
        $stmtList = $mysqli->prepare('INSERT INTO shopping_lists (user_id, list_name) VALUES (NULLIF(?, 0), ?)');
        if (!$stmtList) {
            throw new Exception('Prepare list failed: ' . $mysqli->error);
        }
        $stmtList->bind_param('is', $user_id, $list_name);
        if (!$stmtList->execute()) {
            throw new Exception('Insert list failed: ' . $stmtList->error);
        }
        $list_id = $stmtList->insert_id;
        $stmtList->close();
    }

    // Insert items only if the list_items table exists
    $hasItemsTable = false;
    $chk = $mysqli->query("SHOW TABLES LIKE 'list_items'");
    if ($chk && $chk->num_rows > 0) {
        $hasItemsTable = true;
    }
    if ($chk instanceof mysqli_result) {
        $chk->free();
    }

    if ($hasItemsTable) {
        $stmtItem = $mysqli->prepare('INSERT INTO list_items (list_id, item_name, quantity, price, status) VALUES (?, ?, ?, ?, ?)');
        if (!$stmtItem) {
            throw new Exception('Prepare item failed: ' . $mysqli->error);
        }

        foreach ($items as $it) {
            $name = isset($it['name']) ? trim((string)$it['name']) : '';
            $qty = isset($it['qty']) ? (int)$it['qty'] : 0;
            $price = isset($it['price']) ? (float)$it['price'] : 0.0;
            $status = isset($it['status']) && in_array($it['status'], ['pending','purchased'], true) ? $it['status'] : 'pending';

            if ($name === '' || $qty < 0 || $price < 0) {
                throw new Exception('Invalid item data');
            }

            $stmtItem->bind_param('isids', $list_id, $name, $qty, $price, $status);
            if (!$stmtItem->execute()) {
                throw new Exception('Insert item failed: ' . $stmtItem->error);
            }
        }
        $stmtItem->close();
    }

    $mysqli->commit();

    // Build notification email
    $creator = 'Guest';
    if (!empty($_SESSION['app_user']['username'])) {
        $creator = (string) $_SESSION['app_user']['username'];
    } elseif ($user_id > 0) {
        // Try to fetch username from users table
        $stmtUser = $mysqli->prepare('SELECT username FROM users WHERE user_id = ?');
        if ($stmtUser) {
            $stmtUser->bind_param('i', $user_id);
            if ($stmtUser->execute()) {
                $resUser = $stmtUser->get_result();
                if ($resUser && $rowU = $resUser->fetch_assoc()) {
                    $creator = (string) $rowU['username'];
                }
                if ($resUser instanceof mysqli_result) { $resUser->free(); }
            }
            $stmtUser->close();
        }
    }

    $lines = [];
    $lines[] = 'A new shopping list has been created.';
    $lines[] = 'Created by: ' . $creator;
    $lines[] = 'List name: ' . $list_name;
    $lines[] = 'List ID: ' . $list_id;
    $lines[] = 'Total items: ' . count($items);
    $lines[] = '---- Items ----';
    foreach ($items as $it) {
        $n = isset($it['name']) ? (string)$it['name'] : '';
        $q = isset($it['qty']) ? (int)$it['qty'] : 0;
        $p = isset($it['price']) ? (float)$it['price'] : 0.0;
        $s = isset($it['status']) ? (string)$it['status'] : 'pending';
        $lines[] = "- {$n} | qty: {$q} | price: " . number_format($p, 2) . " | status: {$s}";
    }
    // Try to build a link to the list within the app (page can read ?shopping_list_id=)
    $baseUrl = 'http://localhost';
    if (function_exists('home_url')) {
        $baseUrl = rtrim(home_url('/'), '/');
    }
    $listUrl = $baseUrl . '/?shopping_list_id=' . $list_id;
    $lines[] = 'Link: ' . $listUrl;

    $body = implode("\n", $lines);
    $subject = 'New shopping list #' . $list_id . ' - ' . $list_name;

    // Try to determine recipient based on property owner
    $to = '';
    $property_id = $req_property_id;
    if ($property_id > 0) {
        $stmtE = $mysqli->prepare('SELECT u.email FROM rental_objects ro JOIN users u ON ro.owner_id = u.user_id WHERE ro.property_id = ? LIMIT 1');
        if ($stmtE) {
            $stmtE->bind_param('i', $property_id);
            if ($stmtE->execute()) {
                $resE = $stmtE->get_result();
                if ($resE && ($rowE = $resE->fetch_assoc()) && !empty($rowE['email'])) {
                    $to = (string) $rowE['email'];
                }
                if ($resE instanceof mysqli_result) { $resE->free(); }
            }
            $stmtE->close();
        }
    }
    if ($to === '') {
        // Fallback
        if (function_exists('get_option')) {
            $to = get_option('admin_email');
        }
        if (!$to) { $to = 'noreply@localhost'; }
    }

    // Try to load WordPress to use wp_mail if possible
    // This file lives in: wp-content/themes/astra-child/custom/shopping_list/
    // Go up 6 levels to reach the WP root (which contains wp-load.php)
    $wpLoad = dirname(__FILE__, 6) . '/wp-load.php';
    if (file_exists($wpLoad)) {
        require_once $wpLoad;
    }

    $sent = false;
    if (function_exists('wp_mail')) {
        $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];
        $sent = wp_mail($to, $subject, $body, $headers);
    } else {
        // Fallback to PHP mail()
        $headers = "MIME-Version: 1.0\r\n" .
                   "Content-type: text/plain; charset=UTF-8\r\n" .
                   "From: noreply@localhost\r\n";
        // @ to avoid warnings on local dev if mail is not configured
        $sent = @mail($to, $subject, $body, $headers);
    }

    echo json_encode(['success' => true, 'list_id' => $list_id, 'email_sent' => (bool)$sent, 'to_email' => $to]);
} catch (Throwable $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $mysqli->close();
}
