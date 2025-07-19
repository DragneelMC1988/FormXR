<?php
/**
 * Admin Main Dashboard Template
 * Complete rewrite with consistent header/footer structure
 */
if (!defined('ABSPATH')) {
    exit;
}

// Include header
include_once FORMXR_PLUGIN_DIR . 'templates/admin-header.php';

// Get statistics
global $wpdb;
$questionnaires_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formxr_questionnaires");
$submissions_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formxr_submissions");
$total_revenue = $wpdb->get_var("SELECT SUM(price) FROM {$wpdb->prefix}formxr_questionnaires");
$active_questionnaires = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formxr_questionnaires WHERE status = 'active'");

// Recent questionnaires
$recent_questionnaires = $wpdb->get_results("
    SELECT * FROM {$wpdb->prefix}formxr_questionnaires 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Recent submissions
$recent_submissions = $wpdb->get_results("
    SELECT s.*, q.title as questionnaire_title 
    FROM {$wpdb->prefix}formxr_submissions s
    LEFT JOIN {$wpdb->prefix}formxr_questionnaires q ON s.questionnaire_id = q.id
    ORDER BY s.submitted_at DESC 
    LIMIT 10
");
?>

<div class="formxr-admin-wrap">
    <!-- Page Header -->
    <div class="formxr-page-header">
        <div class="formxr-page-header-content">
            <h1 class="formxr-page-title">
                <span class="formxr-page-icon">🏠</span>
                <?php _e('Dashboard', 'formxr'); ?>
            </h1>
            <p class="formxr-page-subtitle">
                <?php _e('Welcome to FormXR - Your powerful questionnaire management system', 'formxr'); ?>
            </p>
        </div>
        <div class="formxr-page-actions">
            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="formxr-btn formxr-btn-primary">
                <span class="formxr-btn-icon">➕</span>
                <?php _e('New Questionnaire', 'formxr'); ?>
            </a>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title"><?php _e('Overview', 'formxr'); ?></h2>
        </div>
        <div class="formxr-grid formxr-grid-4">
            <div class="formxr-stat-card formxr-stat-card-primary">
                <div class="formxr-stat-icon">📝</div>
                <div class="formxr-stat-content">
                    <div class="formxr-stat-number"><?php echo number_format($questionnaires_count); ?></div>
                    <div class="formxr-stat-label"><?php _e('Total Questionnaires', 'formxr'); ?></div>
                </div>
            </div>
            
            <div class="formxr-stat-card formxr-stat-card-success">
                <div class="formxr-stat-icon">✅</div>
                <div class="formxr-stat-content">
                    <div class="formxr-stat-number"><?php echo number_format($active_questionnaires); ?></div>
                    <div class="formxr-stat-label"><?php _e('Active Questionnaires', 'formxr'); ?></div>
                </div>
            </div>
            
            <div class="formxr-stat-card formxr-stat-card-info">
                <div class="formxr-stat-icon">📊</div>
                <div class="formxr-stat-content">
                    <div class="formxr-stat-number"><?php echo number_format($submissions_count); ?></div>
                    <div class="formxr-stat-label"><?php _e('Total Submissions', 'formxr'); ?></div>
                </div>
            </div>
            
            <div class="formxr-stat-card formxr-stat-card-warning">
                <div class="formxr-stat-icon">💰</div>
                <div class="formxr-stat-content">
                    <div class="formxr-stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="formxr-stat-label"><?php _e('Total Revenue', 'formxr'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Grid Section -->
    <div class="formxr-grid formxr-grid-2">
        <!-- Recent Questionnaires Widget -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <span class="formxr-widget-icon">📝</span>
                    <?php _e('Recent Questionnaires', 'formxr'); ?>
                </h3>
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="formxr-widget-action">
                    <?php _e('View All', 'formxr'); ?>
                </a>
            </div>
            <div class="formxr-widget-content">
                <?php if (!empty($recent_questionnaires)) : ?>
                    <div class="formxr-questionnaire-list">
                        <?php foreach ($recent_questionnaires as $questionnaire) : ?>
                            <div class="formxr-questionnaire-item">
                                <div class="formxr-questionnaire-info">
                                    <h4 class="formxr-questionnaire-title">
                                        <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $questionnaire->id); ?>">
                                            <?php echo esc_html($questionnaire->title); ?>
                                        </a>
                                    </h4>
                                    <div class="formxr-questionnaire-meta">
                                        <span class="formxr-badge formxr-badge-<?php echo $questionnaire->status === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($questionnaire->status); ?>
                                        </span>
                                        <?php if ($questionnaire->price > 0) : ?>
                                            <span class="formxr-questionnaire-price">$<?php echo number_format($questionnaire->price, 2); ?></span>
                                        <?php endif; ?>
                                        <span class="formxr-text-muted"><?php echo human_time_diff(strtotime($questionnaire->created_at), current_time('timestamp')) . ' ago'; ?></span>
                                    </div>
                                </div>
                                <div class="formxr-questionnaire-actions">
                                    <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $questionnaire->id); ?>" class="formxr-btn formxr-btn-sm formxr-btn-secondary">
                                        <?php _e('Edit', 'formxr'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="formxr-empty-state">
                        <div class="formxr-empty-icon">📝</div>
                        <h4><?php _e('No Questionnaires Yet', 'formxr'); ?></h4>
                        <p><?php _e('Create your first questionnaire to get started.', 'formxr'); ?></p>
                        <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="formxr-btn formxr-btn-primary">
                            <?php _e('Create Questionnaire', 'formxr'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Submissions Widget -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <span class="formxr-widget-icon">📊</span>
                    <?php _e('Recent Submissions', 'formxr'); ?>
                </h3>
                <a href="<?php echo admin_url('admin.php?page=formxr-submissions'); ?>" class="formxr-widget-action">
                    <?php _e('View All', 'formxr'); ?>
                </a>
            </div>
            <div class="formxr-widget-content">
                <?php if (!empty($recent_submissions)) : ?>
                    <div class="formxr-table-responsive">
                        <table class="formxr-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Questionnaire', 'formxr'); ?></th>
                                    <th><?php _e('Status', 'formxr'); ?></th>
                                    <th><?php _e('Date', 'formxr'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recent_submissions, 0, 5) as $submission) : ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=formxr-submissions&view=single&id=' . $submission->id); ?>">
                                                <?php echo esc_html($submission->questionnaire_title); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="formxr-badge formxr-badge-<?php echo $submission->status === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($submission->status); ?>
                                            </span>
                                        </td>
                                        <td class="formxr-text-muted">
                                            <?php echo human_time_diff(strtotime($submission->submitted_at), current_time('timestamp')) . ' ago'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="formxr-empty-state">
                        <div class="formxr-empty-icon">📊</div>
                        <h4><?php _e('No Submissions Yet', 'formxr'); ?></h4>
                        <p><?php _e('Submissions will appear here once users start filling out your questionnaires.', 'formxr'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- System Status Section -->
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title"><?php _e('System Status', 'formxr'); ?></h2>
            <a href="<?php echo admin_url('admin.php?page=formxr-settings'); ?>" class="formxr-btn formxr-btn-secondary">
                <?php _e('Settings', 'formxr'); ?>
            </a>
        </div>
        <div class="formxr-widget">
            <div class="formxr-widget-content">
                <div class="formxr-status-list">
                    <div class="formxr-status-item">
                        <div class="formxr-status-icon success">✅</div>
                        <div class="formxr-status-content">
                            <h4><?php _e('Plugin Status', 'formxr'); ?></h4>
                            <p><?php _e('FormXR is running properly.', 'formxr'); ?></p>
                        </div>
                    </div>
                    
                    <div class="formxr-status-item">
                        <div class="formxr-status-icon success">✅</div>
                        <div class="formxr-status-content">
                            <h4><?php _e('Database Status', 'formxr'); ?></h4>
                            <p><?php _e('All database tables are properly configured.', 'formxr'); ?></p>
                        </div>
                    </div>
                    
                    <div class="formxr-status-item">
                        <div class="formxr-status-icon <?php echo version_compare(phpversion(), '7.4', '>=') ? 'success' : 'warning'; ?>">
                            <?php echo version_compare(phpversion(), '7.4', '>=') ? '✅' : '⚠️'; ?>
                        </div>
                        <div class="formxr-status-content">
                            <h4><?php _e('PHP Version', 'formxr'); ?></h4>
                            <p><?php printf(__('PHP %s - %s', 'formxr'), phpversion(), version_compare(phpversion(), '7.4', '>=') ? __('Good', 'formxr') : __('Update Recommended', 'formxr')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
?>
