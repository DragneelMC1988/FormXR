<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$submissions_table = $wpdb->prefix . 'formxr_submissions';
$questionnaires_table = $wpdb->prefix . 'formxr_questionnaires';

// Get analytics data
$total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table");
$questionnaires_count = $wpdb->get_var("SELECT COUNT(*) FROM $questionnaires_table");

// Time-based analytics
$submissions_today = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE DATE(submitted_at) = CURDATE()");
$submissions_yesterday = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE DATE(submitted_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
$submissions_week = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$submissions_month = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$submissions_last_month = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND submitted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");

// Revenue analytics
$total_revenue_potential = $wpdb->get_var("SELECT SUM(calculated_price) FROM $submissions_table");
$avg_price = $wpdb->get_var("SELECT AVG(calculated_price) FROM $submissions_table");
$highest_price = $wpdb->get_var("SELECT MAX(calculated_price) FROM $submissions_table");
$lowest_price = $wpdb->get_var("SELECT MIN(calculated_price) FROM $submissions_table");

// Monthly revenue potential
$monthly_revenue = $wpdb->get_var("SELECT SUM(calculated_price) FROM $submissions_table WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$last_month_revenue = $wpdb->get_var("SELECT SUM(calculated_price) FROM $submissions_table WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 60 DAY) AND submitted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");

// Pricing type distribution
$monthly_pricing = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE price_type = 'monthly'");
$onetime_pricing = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE price_type = 'onetime'");

// Calculate changes
$submissions_change = $submissions_last_month > 0 ? (($submissions_month - $submissions_last_month) / $submissions_last_month) * 100 : 0;
$revenue_change = $last_month_revenue > 0 ? (($monthly_revenue - $last_month_revenue) / $last_month_revenue) * 100 : 0;
$avg_change = $submissions_yesterday > 0 ? (($submissions_today - $submissions_yesterday) / $submissions_yesterday) * 100 : 0;

// Top performing questionnaires
$top_questionnaires = $wpdb->get_results("
    SELECT q.title, COUNT(s.id) as submission_count, AVG(s.calculated_price) as avg_price
    FROM $questionnaires_table q
    LEFT JOIN $submissions_table s ON q.id = s.questionnaire_id
    GROUP BY q.id
    ORDER BY submission_count DESC
    LIMIT 5
");
?>

<div class="wrap formxr-container">
    <div class="formxr-page-header">
        <div class="formxr-page-title">
            <h1>
                <span class="dashicons dashicons-chart-bar"></span>
                <?php _e('Analytics & Insights', 'formxr'); ?>
            </h1>
            <div class="formxr-header-actions">
                <a href="<?php echo admin_url('admin-ajax.php?action=formxr_export_csv'); ?>" class="btn-formxr">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Export Data', 'formxr'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="btn-formxr btn-outline">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('New Form', 'formxr'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="formxr-dashboard-grid">
        <!-- Key Metrics -->
        <div class="formxr-card metric-card submissions-metric">
            <div class="formxr-card-body">
                <div class="metric-number"><?php echo number_format($total_submissions); ?></div>
                <div class="metric-label"><?php _e('Total Submissions', 'formxr'); ?></div>
                <div class="metric-change <?php echo $submissions_change >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $submissions_change >= 0 ? '+' : ''; ?><?php echo number_format($submissions_change, 1); ?>% <?php _e('vs last month', 'formxr'); ?>
                </div>
            </div>
        </div>

        <div class="formxr-card metric-card revenue-metric">
            <div class="formxr-card-body">
                <div class="metric-number">$<?php echo number_format($total_revenue_potential ?: 0, 0); ?></div>
                <div class="metric-label"><?php _e('Revenue Potential', 'formxr'); ?></div>
                <div class="metric-change <?php echo $revenue_change >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $revenue_change >= 0 ? '+' : ''; ?><?php echo number_format($revenue_change, 1); ?>% <?php _e('this month', 'formxr'); ?>
                </div>
            </div>
        </div>

        <div class="formxr-card metric-card average-metric">
            <div class="formxr-card-body">
                <div class="metric-number">$<?php echo number_format($avg_price ?: 0, 0); ?></div>
                <div class="metric-label"><?php _e('Average Quote', 'formxr'); ?></div>
                <div class="metric-change <?php echo $avg_change >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $avg_change >= 0 ? '+' : ''; ?><?php echo number_format($avg_change, 1); ?>% <?php _e('vs yesterday', 'formxr'); ?>
                </div>
            </div>
        </div>

        <div class="formxr-card metric-card conversion-metric">
            <div class="formxr-card-body">
                <div class="metric-number"><?php echo $questionnaires_count; ?></div>
                <div class="metric-label"><?php _e('Active Forms', 'formxr'); ?></div>
                <div class="metric-change neutral">
                    <?php _e('Currently Active', 'formxr'); ?>
                </div>
            </div>
        </div>

        <!-- Top Performing Questionnaires -->
        <div class="formxr-card" style="grid-column: 1 / -1;">
            <div class="formxr-card-header">
                <h2><?php _e('Top Performing Forms', 'formxr'); ?></h2>
                <p><?php _e('Forms with the most submissions', 'formxr'); ?></p>
            </div>
            <div class="formxr-card-body">
                <?php if (!empty($top_questionnaires)): ?>
                    <div class="questionnaire-performance">
                        <?php foreach ($top_questionnaires as $questionnaire): ?>
                            <div class="questionnaire-item">
                                <div class="questionnaire-info">
                                    <h4><?php echo esc_html($questionnaire->title); ?></h4>
                                    <div class="questionnaire-stats">
                                        <span><?php echo $questionnaire->submission_count; ?> <?php _e('submissions', 'formxr'); ?></span>
                                        <span>$<?php echo number_format($questionnaire->avg_price ?: 0, 0); ?> <?php _e('avg', 'formxr'); ?></span>
                                    </div>
                                </div>
                                <div class="performance-bar">
                                    <?php 
                                    $total_max = $top_questionnaires[0]->submission_count;
                                    $percentage = $total_max > 0 ? ($questionnaire->submission_count / $total_max) * 100 : 0;
                                    ?>
                                    <div class="bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p><?php _e('No questionnaire data available yet.', 'formxr'); ?></p>
                        <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="btn-formxr">
                            <?php _e('Create Your First Form', 'formxr'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Revenue Insights -->
        <div class="formxr-card">
            <div class="formxr-card-header">
                <h2><?php _e('Revenue Insights', 'formxr'); ?></h2>
                <p><?php _e('Price range analysis', 'formxr'); ?></p>
            </div>
            <div class="formxr-card-body">
                <?php if ($total_submissions > 0): ?>
                    <div class="revenue-stats">
                        <div class="stat-row">
                            <span class="stat-label"><?php _e('Highest Quote:', 'formxr'); ?></span>
                            <span class="stat-value">$<?php echo number_format($highest_price ?: 0, 0); ?></span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label"><?php _e('Lowest Quote:', 'formxr'); ?></span>
                            <span class="stat-value">$<?php echo number_format($lowest_price ?: 0, 0); ?></span>
                        </div>
                        <div class="stat-row highlight">
                            <span class="stat-label"><?php _e('This Month:', 'formxr'); ?></span>
                            <span class="stat-value">$<?php echo number_format($monthly_revenue ?: 0, 0); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p><?php _e('No revenue data available yet.', 'formxr'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pricing Type Distribution -->
        <div class="formxr-card">
            <div class="formxr-card-header">
                <h2><?php _e('Pricing Types', 'formxr'); ?></h2>
                <p><?php _e('Distribution of pricing preferences', 'formxr'); ?></p>
            </div>
            <div class="formxr-card-body">
                <div class="pricing-distribution">
                    <div class="pricing-type-card monthly-card">
                        <h3><?php echo $monthly_pricing; ?></h3>
                        <p><?php _e('Monthly Pricing', 'formxr'); ?></p>
                    </div>
                    <div class="pricing-type-card onetime-card">
                        <h3><?php echo $onetime_pricing; ?></h3>
                        <p><?php _e('One-time Pricing', 'formxr'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2>
                <span class="dashicons dashicons-admin-tools"></span>
                <?php _e('Quick Actions', 'formxr'); ?>
            </h2>
        </div>
        <div class="formxr-section-content">
            <div class="formxr-actions-grid">
                <a href="<?php echo admin_url('admin-ajax.php?action=formxr_export_csv'); ?>" class="btn-formxr">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Export All Data', 'formxr'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=formxr-submissions'); ?>" class="btn-formxr btn-outline">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php _e('View Submissions', 'formxr'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=formxr-settings'); ?>" class="btn-formxr btn-green">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <?php _e('Adjust Settings', 'formxr'); ?>
                </a>
            </div>
        </div>
    </div>
    
</div> <!-- End formxr-container -->
