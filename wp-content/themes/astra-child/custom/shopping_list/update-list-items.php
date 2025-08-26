<?php
// update-list-items.php
// Updates statuses for items in a given shopping list and optionally deletes items.
// Expects JSON body: {
//   list_id: number,
//   items: [{ item_id: number, status: "pending"|"purchased" }, ...],
//   deleted_item_ids: [number, ...]
// }

header('Content-Type: application/json; charset=utf-8');

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

// Read JSON input
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

$list_id = isset($payload['list_id']) ? intval($payload['list_id']) : 0;
$items = isset($payload['items']) && is_array($payload['items']) ? $payload['items'] : [];
$deleted_ids = isset($payload['deleted_item_ids']) && is_array($payload['deleted_item_ids']) ? $payload['deleted_item_ids'] : [];

if ($list_id <= 0 || (empty($items) && empty($deleted_ids))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing list_id and no changes provided']);
    exit;
}

// DB config (same as other endpoints)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'najam_vila_db';

$mysqli = @new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection failed', 'details' => $mysqli->connect_error]);
    exit;
}

// Start transaction
$mysqli->begin_transaction();
try {
    // Prepared statement: ensure we update only rows from this list
    $updated = 0;
    if (!empty($items)) {
        $stmt = $mysqli->prepare('UPDATE list_items SET status = ? WHERE item_id = ? AND list_id = ?');
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $mysqli->error);
        }
        foreach ($items as $it) {
            if (!is_array($it)) { continue; }
            $item_id = isset($it['item_id']) ? intval($it['item_id']) : 0;
            $status = isset($it['status']) ? strtolower(trim($it['status'])) : '';
            if ($item_id <= 0) { continue; }
            if ($status !== 'pending' && $status !== 'purchased') { continue; }

            $stmt->bind_param('sii', $status, $item_id, $list_id);
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            $updated += $stmt->affected_rows >= 0 ? 1 : 0; // count attempts
        }
        $stmt->close();
    }

    // Deletions
    $deleted = 0;
    if (!empty($deleted_ids)) {
        $del = $mysqli->prepare('DELETE FROM list_items WHERE item_id = ? AND list_id = ?');
        if (!$del) {
            throw new Exception('Prepare delete failed: ' . $mysqli->error);
        }
        foreach ($deleted_ids as $id) {
            $iid = intval($id);
            if ($iid <= 0) { continue; }
            $del->bind_param('ii', $iid, $list_id);
            if (!$del->execute()) {
                throw new Exception('Delete execute failed: ' . $del->error);
            }
            $deleted += $del->affected_rows > 0 ? 1 : 0;
        }
        $del->close();
    }
    $mysqli->commit();

    echo json_encode([
        'success' => true,
        'updated' => $updated,
        'deleted' => $deleted,
    ]);
} catch (Throwable $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Update failed', 'details' => $e->getMessage()]);
} finally {
    $mysqli->close();
}
