<?php
/*
Plugin Name: RepairPlugin Pro
Description: A plugin to manage repair shops with enhanced locations.
Version: 1.0
*/

if (!defined('ABSPATH')) {
    exit; 
}

// Register the admin menu
function repair_plugin_pro_menu() {
    add_menu_page(
        'RepairPlugin Pro',
        'RepairPlugin Pro',
        'manage_options',
        'repair-plugin-pro',
        'repair_plugin_pro_admin_page',
        'dashicons-admin-tools',
        6
    );
}

add_action('admin_menu', 'repair_plugin_pro_menu');

// Enqueue CSS
function repair_plugin_pro_enqueue_scripts($hook) {
    if ($hook != 'toplevel_page_repair-plugin-pro') {
        return;
    }
    wp_enqueue_style('repair-plugin-pro-css', plugin_dir_url(__FILE__) . 'style.css');
}

add_action('admin_enqueue_scripts', 'repair_plugin_pro_enqueue_scripts');

// Admin page content
function repair_plugin_pro_admin_page() {
    include plugin_dir_path(__FILE__) . 'admin-page.php';
}
