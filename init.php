<?php
/*
Plugin Name: Default RankMath SEO Settings
Description: Applies default SEO titles, descriptions, and focus keywords to posts and pages using RankMath SEO. Includes a checkbox to apply settings only for published posts/pages or to all. Does not overwrite existing SEO values.
Version: 1.0
Author: BestDigital.ro
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Add a menu item in the WordPress admin
function drss_add_admin_menu() {
    add_menu_page(
        'Default RankMath SEO', // Page title
        'Default RankMath SEO', // Menu title
        'manage_options',      // Capability
        'drss-settings',       // Menu slug
        'drss_admin_page',     // Callback function
        'dashicons-admin-post', // Icon
        100                    // Position
    );
}
add_action('admin_menu', 'drss_add_admin_menu');

// Admin page content
function drss_admin_page() {
    // Output the admin page HTML using echo
    echo '<div class="wrap">';
    echo '<h1>Default RankMath SEO Settings</h1>';
    echo '<p>Click the button below to apply default SEO titles, descriptions, and focus keywords to posts and pages.</p>';
    echo '<form method="post" action="">';

    // Add a nonce field for security
    wp_nonce_field('drss_run_queries_action', 'drss_nonce');

    // Checkbox to apply settings only for published posts/pages
    echo '<label for="drss_published_only">';
    echo '<input type="checkbox" name="drss_published_only" id="drss_published_only" value="1" checked> Apply only to published posts/pages';
    echo '</label><br><br>';

    echo '<input type="hidden" name="drss_action" value="run_queries">';
    echo '<button type="submit" class="button button-primary">Run Queries</button>';
    echo '</form>';

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['drss_action']) && $_POST['drss_action'] === 'run_queries') {
        // Verify nonce
        if (!isset($_POST['drss_nonce']) || !wp_verify_nonce($_POST['drss_nonce'], 'drss_run_queries_action')) {
            echo '<div class="notice notice-error"><p>Security check failed. Please try again.</p></div>';
        } else {
            // Check if the "published only" checkbox is checked
            $published_only = isset($_POST['drss_published_only']) && $_POST['drss_published_only'] === '1';

            // Run the queries
            drss_run_queries($published_only);
            echo '<div class="notice notice-success"><p>Queries executed successfully!</p></div>';
        }
    }

    echo '</div>';
}

// Function to run the queries
function drss_run_queries($published_only = true) {
    global $wpdb;

    // Define post types to include (posts and pages)
    $post_types = ['post', 'page']; // Add more post types if needed

    // Convert post types to a string for the SQL query
    $post_types_string = "'" . implode("', '", $post_types) . "'";

    if ($published_only) {
        // Run queries for published posts/pages only
        // Insert default SEO title for posts/pages without a RankMath title
        $wpdb->query(
            "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
            SELECT p.ID, 'rank_math_title', p.post_title
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_title'
            WHERE pm.meta_id IS NULL AND p.post_type IN ({$post_types_string}) AND p.post_status = 'publish'"
        );

        // Insert default SEO description for posts/pages without a RankMath description
        $wpdb->query(
            "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
            SELECT p.ID, 'rank_math_description', p.post_title
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_description'
            WHERE pm.meta_id IS NULL AND p.post_type IN ({$post_types_string}) AND p.post_status = 'publish'"
        );

        // Insert default focus keyword for posts/pages without a RankMath focus keyword
        $wpdb->query(
            "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
            SELECT p.ID, 'rank_math_focus_keyword', p.post_title
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_focus_keyword'
            WHERE pm.meta_id IS NULL AND p.post_type IN ({$post_types_string}) AND p.post_status = 'publish'"
        );
    } else {
        // Run queries for all posts/pages (regardless of status)
        // Insert default SEO title for posts/pages without a RankMath title
        $wpdb->query(
            "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
            SELECT p.ID, 'rank_math_title', p.post_title
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_title'
            WHERE pm.meta_id IS NULL AND p.post_type IN ({$post_types_string})"
        );

        // Insert default SEO description for posts/pages without a RankMath description
        $wpdb->query(
            "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
            SELECT p.ID, 'rank_math_description', p.post_title
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_description'
            WHERE pm.meta_id IS NULL AND p.post_type IN ({$post_types_string})"
        );

        // Insert default focus keyword for posts/pages without a RankMath focus keyword
        $wpdb->query(
            "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value)
            SELECT p.ID, 'rank_math_focus_keyword', p.post_title
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'rank_math_focus_keyword'
            WHERE pm.meta_id IS NULL AND p.post_type IN ({$post_types_string})"
        );
    }
}