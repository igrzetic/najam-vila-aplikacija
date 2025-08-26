<?php
function damage_report_shortcode() {
    ob_start();
    ?>
    <form id="damage-report-form" method="post" enctype="multipart/form-data">
        <h2>Create damage report</h2>
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

        <label for="damage_description">Damage Description:</label>
        <textarea id="damage_description" name="damage_description" required></textarea>

        <label for="severity">Severity:</label>
        <select name="severity" id="severity" required>
            <?php
            // Options reflect damage_reports.severity enum: ('low','medium','high')
            $severity_options = [
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
            ];
            foreach ($severity_options as $value => $label) {
                echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
            }
            ?>
        </select>
        <label for="damage_images">Damage Images:</label>
        <div class="dr-file">
            <input type="file" id="damage_images" class="dr-file-input" name="damage_images[]" accept="image/jpeg,image/png,image/webp" multiple>
            <label for="damage_images" class="dr-file-btn">Choose images</label>
            <span class="dr-file-info">No files chosen</span>
        </div>
        <p class="dr-help">Supported types: JPG, PNG, WEBP. Max size 5MB per image. You can select multiple files.</p>

        <?php // Fallback hidden nonce (JS will also pass localized nonce)
        if (function_exists('wp_nonce_field')) {
            wp_nonce_field('submit_damage_report', 'damage_report_nonce');
        }
        ?>

        <button type="submit">Submit Damage Report</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('damage_report', 'damage_report_shortcode');