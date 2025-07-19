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
        $nav_items = array(
            'formxr' => array('title' => __('Dashboard', 'formxr'), 'icon' => '🏠'),
            'formxr-questionnaires' => array('title' => __('Questionnaires', 'formxr'), 'icon' => '📝'),
            'formxr-new-questionnaire' => array('title' => __('New Questionnaire', 'formxr'), 'icon' => '➕'),
            'formxr-submissions' => array('title' => __('Submissions', 'formxr'), 'icon' => '📊'),
            'formxr-analytics' => array('title' => __('Analytics', 'formxr'), 'icon' => '📈'),
            'formxr-settings' => array('title' => __('Settings', 'formxr'), 'icon' => '⚙️')
        );
        
        foreach ($nav_items as $page_slug => $item) {
            $active_class = ($current_page === $page_slug) ? 'active' : '';
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
    
    <div class="formxr-header-actions">
        <a href="<?php echo admin_url('admin.php?page=formxr-new-questionnaire'); ?>" class="formxr-btn formxr-btn-primary">
            <span class="formxr-btn-icon">➕</span>
            <?php _e('New Questionnaire', 'formxr'); ?>
        </a>
    </div>
</div>
