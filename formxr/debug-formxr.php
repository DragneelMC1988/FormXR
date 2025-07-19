<?php
/*
 * Debug Script - Check FormXR Database and CSS
 */

// Only run if WordPress is loaded
if (!defined('ABSPATH')) {
    die('Cannot access directly');
}

// Force load WordPress if not already loaded
if (!function_exists('wp_head')) {
    // This file should be included from WordPress admin
    echo "This file must be run from WordPress admin context\n";
    exit;
}

global $wpdb;

echo "<h2>FormXR Debug Information</h2>\n";

// Check plugin constants
echo "<h3>Plugin Constants</h3>\n";
echo "<ul>\n";
echo "<li>FORMXR_PLUGIN_URL: " . (defined('FORMXR_PLUGIN_URL') ? FORMXR_PLUGIN_URL : 'NOT DEFINED') . "</li>\n";
echo "<li>FORMXR_PLUGIN_PATH: " . (defined('FORMXR_PLUGIN_PATH') ? FORMXR_PLUGIN_PATH : 'NOT DEFINED') . "</li>\n";
echo "<li>FORMXR_VERSION: " . (defined('FORMXR_VERSION') ? FORMXR_VERSION : 'NOT DEFINED') . "</li>\n";
echo "</ul>\n";

// Check CSS file existence
echo "<h3>CSS Files</h3>\n";
if (defined('FORMXR_PLUGIN_PATH')) {
    $css_files = [
        'admin-core.css' => FORMXR_PLUGIN_PATH . 'assets/css/admin-core.css',
        'admin-components.css' => FORMXR_PLUGIN_PATH . 'assets/css/admin-components.css'
    ];
    
    echo "<ul>\n";
    foreach ($css_files as $name => $path) {
        $exists = file_exists($path);
        $size = $exists ? filesize($path) : 0;
        echo "<li>$name: " . ($exists ? "EXISTS ({$size} bytes)" : "MISSING") . "</li>\n";
    }
    echo "</ul>\n";
}

// Check database tables
echo "<h3>Database Tables</h3>\n";
$tables = [
    'formxr_questionnaires',
    'formxr_steps', 
    'formxr_questions',
    'formxr_submissions'
];

echo "<ul>\n";
foreach ($tables as $table) {
    $full_table = $wpdb->prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") == $full_table;
    echo "<li>$full_table: " . ($exists ? "EXISTS" : "MISSING") . "</li>\n";
    
    if ($exists && $table == 'formxr_steps') {
        $columns = $wpdb->get_col("SHOW COLUMNS FROM `$full_table`");
        echo "<ul><li>Columns: " . implode(', ', $columns) . "</li></ul>\n";
    }
}
echo "</ul>\n";

// Check options
echo "<h3>Plugin Options</h3>\n";
echo "<ul>\n";
echo "<li>formxr_db_version: " . get_option('formxr_db_version', 'NOT SET') . "</li>\n";
echo "</ul>\n";

// Force reactivation
echo "<h3>Force Plugin Reactivation</h3>\n";
if (current_user_can('activate_plugins')) {
    // Trigger activation hook manually
    do_action('activate_formxr/formxr.php');
    echo "<p>Activation hook triggered manually.</p>\n";
} else {
    echo "<p>No permission to trigger activation.</p>\n";
}
?>
