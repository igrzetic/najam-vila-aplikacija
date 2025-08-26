<?php
/**
 * Enqueue scripts and styles for Damage Report feature
 */

if (!defined('ABSPATH')) { exit; }

add_action('wp_enqueue_scripts', function(){
    // Only enqueue on pages that contain the shortcode to avoid global load
    if (!is_singular()) { return; }
    global $post;
    if (!isset($post->post_content)) { return; }
    $content = $post->post_content;
    $has_shortcode = (strpos($content, '[damage_report]') !== false)
        || (strpos($content, '[all_damage_reports]') !== false)
        || (strpos($content, '[damage_report_images_table]') !== false);
    if (!$has_shortcode) { return; }

    $handle = 'damage-report-js';
    // CSS
    wp_enqueue_style(
        'damage-report-css',
        get_stylesheet_directory_uri() . '/damage_report/damage-report.css',
        [],
        filemtime(get_stylesheet_directory() . '/damage_report/damage-report.css')
    );

    wp_enqueue_script(
        $handle,
        get_stylesheet_directory_uri() . '/damage_report/damage-report.js',
        ['jquery'],
        filemtime(get_stylesheet_directory() . '/damage_report/damage-report.js'),
        true
    );

    wp_localize_script($handle, 'DamageReport', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('submit_damage_report'),
        // Provide session-based user id like Shopping List does
        'userId'  => (function(){
            $uid = 0;
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            if (!empty($_SESSION['app_user']['user_id'])) {
                $uid = (int) $_SESSION['app_user']['user_id'];
            } elseif (function_exists('get_current_user_id')) {
                $uid = (int) get_current_user_id();
            }
            return $uid;
        })(),
    ]);
});
