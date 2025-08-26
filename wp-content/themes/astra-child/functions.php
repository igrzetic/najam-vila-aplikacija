<?php
/**
 * Enqueue parent and child styles for the Astra child theme.
 *
 * @package AstraChild
 */
add_action(
    'wp_enqueue_scripts',
    function () {
        // If using a blank template, keep assets minimal and conditional.
        if (is_page_template('page-login.php')) {
            // Login page: only our custom CSS (no Bootstrap).
            wp_enqueue_style(
                'custom-styles',
                get_stylesheet_directory_uri() . '/custom/custom-styles.css',
                array(),
                wp_get_theme()->get('Version')
            );
            return;
        }

        if (is_page_template('page-admin.php')) {
            // Admin blank page: load Bootstrap 5 + Font Awesome + our custom CSS (after Bootstrap).
            wp_enqueue_style(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                array(),
                '5.3.3'
            );
            // Roboto font used by the navbar demo
            wp_enqueue_style(
                'google-fonts-roboto',
                'https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap',
                array(),
                null
            );
            wp_enqueue_style(
                'font-awesome-6',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
                array(),
                '6.5.2'
            );
            wp_enqueue_style(
                'custom-styles',
                get_stylesheet_directory_uri() . '/custom/custom-styles.css',
                array('bootstrap-5'),
                wp_get_theme()->get('Version')
            );

            // Bootstrap Bundle (includes Popper). No jQuery required in v5.
            wp_enqueue_script(
                'bootstrap-5-bundle',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
                array(),
                '5.3.3',
                true
            );
            // Admin navbar interactions
            wp_enqueue_script(
                'admin-navbar',
                get_stylesheet_directory_uri() . '/custom/admin-navbar.js',
                array('bootstrap-5-bundle'),
                wp_get_theme()->get('Version'),
                true
            );
            return;
        }

        // Parent Astra CSS
        wp_enqueue_style('astra-parent', get_template_directory_uri() . '/style.css');

        // Child theme style.css (kept minimal; dependency ensures proper order)
        wp_enqueue_style(
            'astra-child',
            get_stylesheet_directory_uri() . '/style.css',
            array('astra-parent'),
            wp_get_theme()->get('Version')
        );
    },
    20
);

// Load Shopping List enqueue and localization
require_once get_stylesheet_directory() . '/custom/shopping_list/shopping-list.php';
// Load View Shopping List enqueue and localization
require_once get_stylesheet_directory() . '/custom/shopping_list/view-shopping-list.php';

// Load Damage Report enqueue and localization
require_once get_stylesheet_directory() . '/damage_report/damage-report.php';
require_once get_stylesheet_directory() . '/damage_report/damage-report-enqueue.php';
require_once get_stylesheet_directory() . '/damage_report/damage-report-handler.php';

// Register [list_items_table] shortcode (child theme)
add_action('init', function () {
    if (!shortcode_exists('list_items_table')) {
        add_shortcode('list_items_table', function ($atts) {
            $atts = shortcode_atts([
                'list_id' => null,
            ], $atts, 'list_items_table');

            // Make $list_id available to the included template
            $list_id = ($atts['list_id'] !== null && $atts['list_id'] !== '') ? (int) $atts['list_id'] : null;

            return include get_stylesheet_directory() . '/custom/shopping_list/list-items-table.php';
        });
    }
});

// register [all_damage_reports] shortcode for the damage reports table
add_action('init', function () {
    if (!shortcode_exists('all_damage_reports')) {
        add_shortcode('all_damage_reports', function () {
            return include get_stylesheet_directory() . '/damage_report/all-damage-reports-table.php';
        });
    }
});

// register [damage_report_images_table] shortcode for the damage report images table
add_action('init', function () {
    if (!shortcode_exists('damage_report_images_table')) {
        add_shortcode('damage_report_images_table', function () {
            return include get_stylesheet_directory() . '/damage_report/damage-report-images-table.php';
        });
    }
});
