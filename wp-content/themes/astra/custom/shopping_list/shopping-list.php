<?php
function shopping_list_enqueue_assets() {
    // $plugin_url = plugin_dir_url(__FILE__); // This line is not needed here
    wp_enqueue_style('custom-styles', get_stylesheet_directory_uri() . '/custom/custom-styles.css');
    wp_enqueue_script('shopping-list-js', get_stylesheet_directory_uri() . '/custom/shopping_list/shopping-list.js', array(), false, true);
}
    add_action('wp_enqueue_scripts', 'shopping_list_enqueue_assets');
?>