<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get dashboard statistics
global $wpdb;
$submissions_table = $wpdb->prefix . 'formxr_submissions';
$questionnaires_table = $wpdb->prefix . 'formxr_questionnaires';
$questions_table = $wpdb->prefix . 'formxr_questions';

// Check if tables exist
$submissions_exists = $wpdb->get_var("SHOW TABLES LIKE '$submissions_table'") == $submissions_table;
$questionnaires_exists = $wpdb->get_var("SHOW TABLES LIKE '$questionnaires_table'") == $questionnaires_table;
$questions_exists = $wpdb->get_var("SHOW TABLES LIKE '$questions_table'") == $questions_table;

if (!$submissions_exists || !$questionnaires_exists || !$questions_exists) {
    // Tables don't exist, show setup message
    $total_submissions = 0;
    $submissions_today = 0;
    $submissions_week = 0;
    $submissions_month = 0;
    $avg_price = 0;
    $total_revenue_potential = 0;
    $highest_price = 0;
    $lowest_price = 0;
    $recent_submissions = array();
    $questionnaires_count = 0;
    $questions_count = 0;
    $show_setup_notice = true;
    
    // Get currency setting
    $currency = get_option('formxr_currency', 'USD');
} else {
    $questionnaires_count = $wpdb->get_var("SELECT COUNT(*) FROM $questionnaires_table");
    $questions_count = $wpdb->get_var("SELECT COUNT(*) FROM $questions_table");
    $total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table");
    $submissions_today = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE DATE(submitted_at) = CURDATE()");
    $submissions_week = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $submissions_month = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");

    $avg_price = $wpdb->get_var("SELECT AVG(calculated_price) FROM $submissions_table");
    $total_revenue_potential = $wpdb->get_var("SELECT SUM(calculated_price) FROM $submissions_table");
    
    // Get currency setting
    $currency = get_option('formxr_currency', 'USD');
    $highest_price = $wpdb->get_var("SELECT MAX(calculated_price) FROM $submissions_table");
    $lowest_price = $wpdb->get_var("SELECT MIN(calculated_price) FROM $submissions_table");

    // Get recent submissions
    $recent_submissions = $wpdb->get_results("
        SELECT s.*, q.title as questionnaire_title 
        FROM $submissions_table s 
        LEFT JOIN $questionnaires_table q ON s.questionnaire_id = q.id 
        ORDER BY s.submitted_at DESC 
        LIMIT 5
    ");
    
    $show_setup_notice = false;
}
?>


<div class="wrap formxr-dashboard">
    <div class="formxr-page-header">
        <div class="formxr-page-title">
            <div>
                <h1>
                    <span class="formxr-icon">📊</span>
                    <?php _e('FormXR Dashboard', 'formxr'); ?>
                </h1>
                <p class="formxr-subtitle"><?php _e('Overview of your questionnaires and submissions', 'formxr'); ?></p>
            </div>
            <div class="formxr-header-actions">
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="btn-formxr">
                    ➕ <?php _e('New Questionnaire', 'formxr'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="formxr-container">
        <?php if ($show_setup_notice): ?>
        <!-- Setup Notice -->
        <div class="formxr-setup-notice">
            <div class="formxr-notice-content">
                <div class="formxr-notice-icon">🚀</div>
                <div class="formxr-notice-text">
                    <h2><?php _e('Welcome to FormXR!', 'formxr'); ?></h2>
                    <p><?php _e('Get started by creating your first questionnaire or configuring your settings.', 'formxr'); ?></p>
                    <div class="formxr-notice-actions">
                        <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="btn-formxr">
                            <?php _e('Create First Questionnaire', 'formxr'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=formxr-settings'); ?>" class="btn-formxr btn-outline">
                            <?php _e('Configure Settings', 'formxr'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="formxr-stats-grid">
            <div class="formxr-stat-card">
                <div class="formxr-stat-icon">📋</div>
                <div class="formxr-stat-content">
                    <h3><?php echo number_format($questionnaires_count); ?></h3>
                    <p><?php _e('Questionnaires', 'formxr'); ?></p>
                    <div class="formxr-stat-trend">
                        <span class="formxr-trend-item"><?php echo $questions_count; ?> <?php _e('questions total', 'formxr'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="formxr-stat-card">
                <div class="formxr-stat-icon">📝</div>
                <div class="formxr-stat-content">
                    <h3><?php echo number_format($total_submissions); ?></h3>
                    <p><?php _e('Total Submissions', 'formxr'); ?></p>
                    <div class="formxr-stat-trend">
                        <span class="formxr-trend-item"><?php _e('Today:', 'formxr'); ?> <strong><?php echo $submissions_today; ?></strong></span>
                        <span class="formxr-trend-item"><?php _e('This Week:', 'formxr'); ?> <strong><?php echo $submissions_week; ?></strong></span>
                    </div>
                </div>
            </div>
            
            <div class="formxr-stat-card">
                <div class="formxr-stat-icon">💰</div>
                <div class="formxr-stat-content">
                    <h3><?php echo $avg_price ? number_format($avg_price, 0) : '0'; ?> <?php echo $currency; ?></h3>
                    <p><?php _e('Average Price', 'formxr'); ?></p>
                    <div class="formxr-stat-trend">
                        <span class="formxr-trend-item"><?php _e('Potential:', 'formxr'); ?> <strong><?php echo number_format($total_revenue_potential ?: 0, 0); ?> <?php echo $currency; ?></strong></span>
                    </div>
                </div>
            </div>
            
            <div class="formxr-stat-card">
                <div class="formxr-stat-icon">📈</div>
                <div class="formxr-stat-content">
                    <h3><?php echo $submissions_month; ?></h3>
                    <p><?php _e('This Month', 'formxr'); ?></p>
                    <div class="formxr-stat-trend">
                        <span class="formxr-trend-item"><?php _e('Weekly Avg:', 'formxr'); ?> <strong><?php echo $submissions_month ? round($submissions_month / 4, 1) : '0'; ?></strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="formxr-main-grid">
            <!-- Left Column -->
            <div class="formxr-main-left">
                <!-- Quick Actions -->
                <div class="formxr-card">
                    <div class="formxr-card-header">
                        <h2><?php _e('Quick Actions', 'formxr'); ?></h2>
                        <p><?php _e('Common tasks and shortcuts', 'formxr'); ?></p>
                    </div>
                    <div class="formxr-card-body">
                        <div class="formxr-actions-grid">
                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="formxr-action-card">
                                <div class="formxr-action-icon">➕</div>
                                <h4><?php _e('New Questionnaire', 'formxr'); ?></h4>
                                <p><?php _e('Create a new questionnaire', 'formxr'); ?></p>
                            </a>
                            
                            <a href="<?php echo admin_url('admin.php?page=formxr-settings'); ?>" class="formxr-action-card">
                                <div class="formxr-action-icon">⚙️</div>
                                <h4><?php _e('Settings', 'formxr'); ?></h4>
                                <p><?php _e('Configure SMTP & options', 'formxr'); ?></p>
                            </a>
                            
                            <a href="<?php echo admin_url('admin.php?page=formxr-submissions'); ?>" class="formxr-action-card">
                                <div class="formxr-action-icon">📊</div>
                                <h4><?php _e('View Submissions', 'formxr'); ?></h4>
                                <p><?php _e('Review form responses', 'formxr'); ?></p>
                            </a>
                            
                            <a href="<?php echo admin_url('admin-ajax.php?action=formxr_export_csv'); ?>" class="formxr-action-card">
                                <div class="formxr-action-icon">📥</div>
                                <h4><?php _e('Export Data', 'formxr'); ?></h4>
                                <p><?php _e('Download submissions CSV', 'formxr'); ?></p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <?php if (!empty($recent_submissions)): ?>
                <div class="formxr-card">
                    <div class="formxr-card-header">
                        <h2><?php _e('Recent Submissions', 'formxr'); ?></h2>
                        <p><?php _e('Latest form responses', 'formxr'); ?></p>
                    </div>
                    <div class="formxr-card-body">
                        <div class="formxr-submissions-list">
                            <?php foreach ($recent_submissions as $submission): ?>
                                <div class="formxr-submission-item">
                                    <div class="formxr-submission-info">
                                        <div class="formxr-submission-price">
                                            <strong><?php echo number_format($submission->calculated_price, 0); ?> <?php echo $currency; ?></strong>
                                        </div>
                                        <div class="formxr-submission-meta">
                                            <?php if ($submission->questionnaire_title): ?>
                                                <span class="formxr-submission-questionnaire"><?php echo esc_html($submission->questionnaire_title); ?></span>
                                            <?php endif; ?>
                                            <?php if ($submission->user_email): ?>
                                                <span class="formxr-submission-email"><?php echo esc_html($submission->user_email); ?></span>
                                            <?php endif; ?>
                                            <span class="formxr-submission-date"><?php echo human_time_diff(strtotime($submission->submitted_at), current_time('timestamp')) . ' ' . __('ago', 'formxr'); ?></span>
                                        </div>
                                    </div>
                                    <a href="<?php echo admin_url('admin.php?page=formxr-submissions&view=' . $submission->id); ?>" class="formxr-submission-view">
                                        👁️
                                    </a>
                                </div>
                            <?php endforeach; ?>
                            <div class="formxr-submissions-actions">
                                <a href="<?php echo admin_url('admin.php?page=formxr-submissions'); ?>" class="btn-formxr btn-outline">
                                    <?php _e('View All Submissions', 'formxr'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right Column -->
            <div class="formxr-main-right">
                <!-- Questionnaires Overview -->
                <div class="formxr-card">
                    <div class="formxr-card-header">
                        <h2><?php _e('Questionnaires', 'formxr'); ?></h2>
                        <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="btn-formxr btn-cyan"><?php _e('Add New', 'formxr'); ?></a>
                    </div>
                    <div class="formxr-card-body">
                        <?php if ($questionnaires_count > 0): ?>
                            <?php
                            $questionnaires = $wpdb->get_results("SELECT * FROM $questionnaires_table ORDER BY created_at DESC LIMIT 5");
                            ?>
                            <div class="formxr-questionnaires-list">
                                <?php foreach ($questionnaires as $questionnaire): ?>
                                    <div class="formxr-questionnaire-item">
                                        <div class="formxr-questionnaire-info">
                                            <h4>
                                                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=builder&id=' . $questionnaire->id); ?>">
                                                    <?php echo esc_html($questionnaire->title); ?>
                                                </a>
                                            </h4>
                                            <div class="formxr-questionnaire-meta">
                                                <span class="formxr-status formxr-status-<?php echo $questionnaire->status; ?>">
                                                    <?php echo ucfirst($questionnaire->status); ?>
                                                </span>
                                                <span class="formxr-currency"><?php echo $questionnaire->currency; ?></span>
                                                <?php if ($questionnaire->base_price > 0): ?>
                                                    <span class="formxr-price"><?php echo number_format($questionnaire->base_price, 0); ?> <?php echo $questionnaire->currency; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="formxr-questionnaire-actions">
                                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=builder&id=' . $questionnaire->id); ?>" class="formxr-btn-icon" title="<?php _e('Edit', 'formxr'); ?>">
                                                ✏️
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="formxr-questionnaires-actions">
                                    <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="btn-formxr btn-outline">
                                        <?php _e('Manage All Questionnaires', 'formxr'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="formxr-empty-state">
                                <div class="formxr-empty-icon">📋</div>
                                <h3><?php _e('No Questionnaires Yet', 'formxr'); ?></h3>
                                <p><?php _e('Create your first questionnaire to get started with FormXR.', 'formxr'); ?></p>
                                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="btn-formxr">
                                    ➕ <?php _e('Create First Questionnaire', 'formxr'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- System Status -->
                <div class="formxr-card">
                    <div class="formxr-card-header">
                        <h2><?php _e('System Status', 'formxr'); ?></h2>
                    </div>
                    <div class="formxr-card-body">
                        <div class="formxr-status-list">
                            <div class="formxr-status-item">
                                <div class="formxr-status-icon <?php echo $questionnaires_exists ? 'success' : 'warning'; ?>">
                                    <?php echo $questionnaires_exists ? '✅' : '⚠️'; ?>
                                </div>
                                <div class="formxr-status-content">
                                    <h4><?php _e('Database Tables', 'formxr'); ?></h4>
                                    <p><?php echo $questionnaires_exists ? __('All tables created successfully', 'formxr') : __('Database tables need to be created', 'formxr'); ?></p>
                                </div>
                            </div>
                            
                            <div class="formxr-status-item">
                                <div class="formxr-status-icon <?php echo get_option('formxr_smtp_host') ? 'success' : 'warning'; ?>">
                                    <?php echo get_option('formxr_smtp_host') ? '✅' : '⚠️'; ?>
                                </div>
                                <div class="formxr-status-content">
                                    <h4><?php _e('Email Configuration', 'formxr'); ?></h4>
                                    <p>
                                        <?php echo get_option('formxr_smtp_host') ? 
                                            __('SMTP configured and ready', 'formxr') : 
                                            __('SMTP needs configuration', 'formxr'); ?>
                                    </p>
                                    <?php if (!get_option('formxr_smtp_host')): ?>
                                        <a href="<?php echo admin_url('admin.php?page=formxr-settings'); ?>" class="btn-formxr btn-cyan">
                                            <?php _e('Configure Now', 'formxr'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    