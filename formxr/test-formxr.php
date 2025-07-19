<?php
/**
 * Test Page for FormXR Plugin
 * This page helps verify that CSS is loading and database tables exist
 */

// Check if this is being accessed directly
if (!defined('ABSPATH')) {
    // Include WordPress
    require_once('../../../wp-config.php');
}

// Only allow admin users
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

global $wpdb;
?>
<!DOCTYPE html>
<html>
<head>
    <title>FormXR Test Page</title>
    <?php 
    // Load WordPress head to get our CSS
    wp_head(); 
    ?>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>FormXR Plugin Test Page</h1>
    
    <div class="test-section">
        <h2>New Color Scheme Test</h2>
        <div class="formxr-container">
            <!-- Primary Cards -->
            <div class="formxr-grid formxr-grid-2" style="margin-bottom: 2rem;">
                <div class="formxr-card formxr-card-primary">
                    <div class="formxr-card-header">Primary Card (#2AACE2)</div>
                    <div class="formxr-card-body">
                        <p>This card showcases the primary blue color with sharp, clean edges.</p>
                        <button class="formxr-btn formxr-btn-primary">Primary Button</button>
                    </div>
                </div>
                
                <div class="formxr-card formxr-card-secondary">
                    <div class="formxr-card-header">Secondary Card (#8062AA)</div>
                    <div class="formxr-card-body">
                        <p>This card showcases the secondary purple color.</p>
                        <button class="formxr-btn formxr-btn-secondary">Secondary Button</button>
                    </div>
                </div>
            </div>
            
            <!-- Button Showcase -->
            <div class="formxr-card" style="margin-bottom: 2rem;">
                <div class="formxr-card-header">Button Color Palette</div>
                <div class="formxr-card-body">
                    <div class="formxr-flex formxr-flex-wrap" style="gap: 1rem; margin-bottom: 1rem;">
                        <button class="formxr-btn formxr-btn-primary">Primary (#2AACE2)</button>
                        <button class="formxr-btn formxr-btn-secondary">Secondary (#8062AA)</button>
                        <button class="formxr-btn formxr-btn-accent-1">Accent 1 (#4555A5)</button>
                        <button class="formxr-btn formxr-btn-accent-2">Accent 2 (#13BCD4)</button>
                    </div>
                    <div class="formxr-flex formxr-flex-wrap" style="gap: 1rem;">
                        <button class="formxr-btn formxr-btn-accent-3">Accent 3 (#F36E24)</button>
                        <button class="formxr-btn formxr-btn-accent-4">Accent 4 (#EF4681)</button>
                        <button class="formxr-btn formxr-btn-outline">Outline</button>
                        <button class="formxr-btn formxr-btn-success">Success</button>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Grid -->
            <div class="formxr-grid formxr-grid-4" style="margin-bottom: 2rem;">
                <div class="formxr-stat-card">
                    <span class="formxr-stat-number">123</span>
                    <span class="formxr-stat-label">Primary Stats</span>
                </div>
                <div class="formxr-stat-card formxr-stat-card-secondary">
                    <span class="formxr-stat-number">456</span>
                    <span class="formxr-stat-label">Secondary Stats</span>
                </div>
                <div class="formxr-stat-card formxr-stat-card-accent-1">
                    <span class="formxr-stat-number">789</span>
                    <span class="formxr-stat-label">Accent Stats</span>
                </div>
                <div class="formxr-stat-card formxr-stat-card-accent-2">
                    <span class="formxr-stat-number">999</span>
                    <span class="formxr-stat-label">More Stats</span>
                </div>
            </div>
            
            <!-- Form Example -->
            <div class="formxr-card">
                <div class="formxr-card-header">Form Example</div>
                <div class="formxr-card-body">
                    <div class="formxr-form-group">
                        <label class="formxr-form-label">Sample Input Field</label>
                        <input type="text" class="formxr-form-control" placeholder="Type something..." />
                    </div>
                    
                    <div class="formxr-form-group">
                        <label class="formxr-form-label">Sample Textarea</label>
                        <textarea class="formxr-form-control" placeholder="Write your message..."></textarea>
                    </div>
                    
                    <div class="formxr-form-group">
                        <div class="formxr-form-check">
                            <input type="checkbox" class="formxr-form-check-input" id="sample-check">
                            <label class="formxr-form-check-label" for="sample-check">I agree to the terms</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="test-section">
        <h2>Database Test</h2>
        <?php
        // Check database tables
        $tables = [
            'formxr_questionnaires',
            'formxr_steps', 
            'formxr_questions',
            'formxr_submissions'
        ];
        
        echo "<ul>";
        foreach ($tables as $table) {
            $full_table = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table'") == $full_table;
            $class = $exists ? 'success' : 'error';
            $status = $exists ? 'EXISTS' : 'MISSING';
            echo "<li class='$class'>$full_table: $status</li>";
            
            if ($exists && $table == 'formxr_steps') {
                $columns = $wpdb->get_col("SHOW COLUMNS FROM `$full_table`");
                echo "<ul><li class='info'>Columns: " . implode(', ', $columns) . "</li></ul>";
            }
        }
        echo "</ul>";
        ?>
    </div>
    
    <div class="test-section">
        <h2>Plugin Constants</h2>
        <ul>
            <li class="<?php echo defined('FORMXR_PLUGIN_URL') ? 'success' : 'error'; ?>">
                FORMXR_PLUGIN_URL: <?php echo defined('FORMXR_PLUGIN_URL') ? FORMXR_PLUGIN_URL : 'NOT DEFINED'; ?>
            </li>
            <li class="<?php echo defined('FORMXR_VERSION') ? 'success' : 'error'; ?>">
                FORMXR_VERSION: <?php echo defined('FORMXR_VERSION') ? FORMXR_VERSION : 'NOT DEFINED'; ?>
            </li>
        </ul>
    </div>
    
    <div class="test-section">
        <h2>CSS File Existence</h2>
        <?php
        if (defined('FORMXR_PLUGIN_PATH')) {
            $css_files = [
                'admin-core.css' => FORMXR_PLUGIN_PATH . 'assets/css/admin-core.css',
                'admin-components.css' => FORMXR_PLUGIN_PATH . 'assets/css/admin-components.css'
            ];
            
            echo "<ul>";
            foreach ($css_files as $name => $path) {
                $exists = file_exists($path);
                $size = $exists ? filesize($path) : 0;
                $class = $exists && $size > 0 ? 'success' : 'error';
                $status = $exists ? "EXISTS ({$size} bytes)" : "MISSING";
                echo "<li class='$class'>$name: $status</li>";
            }
            echo "</ul>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>Force Database Creation</h2>
        <?php
        if (isset($_GET['create_db']) && $_GET['create_db'] == '1') {
            // Force database creation
            $formxr = new FormXR_Plugin();
            $formxr->activate();
            echo "<p class='success'>Database activation triggered!</p>";
            echo "<script>setTimeout(function(){ location.href = location.href.split('?')[0]; }, 2000);</script>";
        } else {
            echo "<p><a href='?create_db=1' class='formxr-btn formxr-btn-primary'>Force Database Creation</a></p>";
        }
        ?>
    </div>
    
    <?php wp_footer(); ?>
</body>
</html>
