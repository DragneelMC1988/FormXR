<?php
/**
 * Admin Header Template
 * Shared header for all admin pages
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="formxr-admin-header">
    <div class="formxr-header-brand">
        <h1 class="formxr-logo">
            <span class="formxr-logo-icon">📋</span>
            FormXR
            <span class="formxr-version">v<?php echo FORMXR_VERSION; ?></span>
        </h1>
    </div>
    
    <div class="formxr-header-nav">
        <?php
        $current_page = isset($_GET['page']) ? $_GET['page'] : 'formxr';
        $current_action = isset($_GET['action']) ? $_GET['action'] : '';
        $nav_items = array(
            'formxr' => array('title' => __('Dashboard', 'formxr'), 'icon' => '🏠'),
            'formxr-questionnaires' => array('title' => __('Questionnaires', 'formxr'), 'icon' => '📝'),
            'formxr-questionnaires&action=new' => array('title' => __('New Questionnaire', 'formxr'), 'icon' => '➕'),
            'formxr-submissions' => array('title' => __('Submissions', 'formxr'), 'icon' => '📊'),
            'formxr-analytics' => array('title' => __('Analytics', 'formxr'), 'icon' => '📈'),
            'formxr-settings' => array('title' => __('Settings', 'formxr'), 'icon' => '⚙️')
        );
        
        foreach ($nav_items as $page_slug => $item) {
            // Handle active state for regular pages and action pages
            $is_active = false;
            if (strpos($page_slug, '&action=') !== false) {
                // For action pages like "formxr-questionnaires&action=new"
                $parts = explode('&action=', $page_slug);
                $page_part = $parts[0];
                $action_part = $parts[1];
                $is_active = ($current_page === $page_part && $current_action === $action_part);
            } else {
                // For regular pages
                $is_active = ($current_page === $page_slug && empty($current_action));
            }
            
            $active_class = $is_active ? 'active' : '';
            echo sprintf(
                '<a href="%s" class="formxr-nav-item %s">
                    <span class="formxr-nav-icon">%s</span>
                    <span class="formxr-nav-text">%s</span>
                </a>',
                admin_url('admin.php?page=' . $page_slug),
                $active_class,
                $item['icon'],
                $item['title']
            );
        }
        ?>
    </div>
</div>
