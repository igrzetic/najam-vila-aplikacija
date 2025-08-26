<?php
/**
 * AJAX handler for submitting Damage Reports
 */

if (!defined('ABSPATH')) { exit; }

add_action('wp_ajax_submit_damage_report', 'astra_child_submit_damage_report');
add_action('wp_ajax_nopriv_submit_damage_report', 'astra_child_submit_damage_report');


function astra_child_submit_damage_report() {
    // Nonce check
    if (!isset($_POST['damage_report_nonce']) || !wp_verify_nonce($_POST['damage_report_nonce'], 'submit_damage_report')) {
        wp_send_json([ 'success' => false, 'message' => 'Invalid request (nonce).' ], 400);
    }

    // Require login to capture user_id - allow fallback to app session
    $wp_user_id = (int) get_current_user_id();
    if (!$wp_user_id) {
        // Try session-based auth used by Shopping List
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        if (!empty($_SESSION['app_user']) && is_array($_SESSION['app_user'])) {
            $maybe_id = isset($_SESSION['app_user']['user_id']) ? (int) $_SESSION['app_user']['user_id'] : 0;
            if ($maybe_id > 0) {
                $wp_user_id = $maybe_id;
            }
        }
    }
    if (!$wp_user_id) {
        // Final fallback: accept posted user_id (provided by JS) if > 0
        $posted_uid = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        if ($posted_uid > 0) {
            $wp_user_id = $posted_uid;
        }
    }
    if (!$wp_user_id) {
        wp_send_json([ 'success' => false, 'message' => 'You must be logged in to submit a damage report.' ], 401);
    }

    // Inputs
    $property_id = isset($_POST['property_id']) ? (int) $_POST['property_id'] : 0;
    $description = isset($_POST['damage_description']) ? sanitize_textarea_field($_POST['damage_description']) : '';
    $severity    = isset($_POST['severity']) ? sanitize_text_field($_POST['severity']) : '';

    if ($property_id <= 0 || $description === '' || $severity === '') {
        wp_send_json([ 'success' => false, 'message' => 'Missing required fields.' ], 400);
    }

    // Basic allowlist for severity
    $allowed_severity = ['low','medium','high'];
    if (!in_array($severity, $allowed_severity, true)) {
        wp_send_json([ 'success' => false, 'message' => 'Invalid severity.' ], 400);
    }

    // DB insert via mysqli (consistent with project style)
    $conn = new mysqli('localhost', 'root', '', 'najam_vila_db');
    if ($conn->connect_error) {
        wp_send_json([ 'success' => false, 'message' => 'Database connection error.' ], 500);
    }

    // Insert report
    $stmt = $conn->prepare("INSERT INTO damage_reports (property_id, user_id, description, severity, created_at) VALUES (?, ?, ?, ?, NOW())");
    if (!$stmt) {
        $conn->close();
        wp_send_json([ 'success' => false, 'message' => 'Failed to prepare insert.' ], 500);
    }
    $stmt->bind_param('iiss', $property_id, $wp_user_id, $description, $severity);
    $ok = $stmt->execute();
    if (!$ok) {
        $stmt->close();
        $conn->close();
        wp_send_json([ 'success' => false, 'message' => 'Failed to save report.' ], 500);
    }
    $report_id = (int) $stmt->insert_id;
    $stmt->close();

    // Handle images (multiple)
    $uploaded_urls = [];
    if (!empty($_FILES['damage_images']) && !empty($_FILES['damage_images']['name'])) {
        // Normalize to array structure
        $files = $_FILES['damage_images'];
        $count = is_array($files['name']) ? count($files['name']) : 0;
        if ($count > 0) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $overrides = [ 'test_form' => false ];

            for ($i = 0; $i < $count; $i++) {
                if (empty($files['name'][$i]) || $files['error'][$i] !== UPLOAD_ERR_OK) { continue; }
                $file_array = [
                    'name'     => sanitize_file_name($files['name'][$i]),
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
                $upload = wp_handle_upload($file_array, $overrides);
                if (!isset($upload['error']) && isset($upload['url'])) {
                    $url = esc_url_raw($upload['url']);
                    $uploaded_urls[] = $url;
                    // Insert row for each image
                    $stmt2 = $conn->prepare("INSERT INTO damage_report_images (report_id, image_url, created_at) VALUES (?, ?, NOW())");
                    if ($stmt2) {
                        $stmt2->bind_param('is', $report_id, $url);
                        $stmt2->execute();
                        $stmt2->close();
                    }
                }
            }
        }
    }

    // Email notification to property owner (lookup via property_id -> rental_objects.owner_id -> users.email)
    $to_email = '';
    $subject = sprintf('New Damage Report (Property #%d)', $property_id);
    $body = "A new damage report has been submitted.\n\n" .
            sprintf("Property ID: %d\n", $property_id) .
            sprintf("User ID: %d\n", $wp_user_id) .
            sprintf("Severity: %s\n\n", $severity) .
            "Description:\n" . $description . "\n\n" .
            (count($uploaded_urls) ? ("Images:\n" . implode("\n", $uploaded_urls) . "\n") : "");

    // Find owner's email
    $stmtEmail = $conn->prepare('SELECT u.email FROM rental_objects ro JOIN users u ON ro.owner_id = u.user_id WHERE ro.property_id = ? LIMIT 1');
    if ($stmtEmail) {
        $stmtEmail->bind_param('i', $property_id);
        if ($stmtEmail->execute()) {
            $resE = $stmtEmail->get_result();
            if ($resE && ($rowE = $resE->fetch_assoc()) && !empty($rowE['email'])) {
                $to_email = (string) $rowE['email'];
            }
            if ($resE instanceof mysqli_result) { $resE->free(); }
        }
        $stmtEmail->close();
    }

    if ($to_email === '') {
        $to_email = get_option('admin_email');
    }
    if ($to_email) {
        wp_mail($to_email, $subject, $body);
    }

    // Close DB connection after all queries are done
    $conn->close();

    wp_send_json([ 'success' => true, 'report_id' => $report_id, 'images' => $uploaded_urls, 'to_email' => $to_email ]);
}
