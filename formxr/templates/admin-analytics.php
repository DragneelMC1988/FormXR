<?php
/**
 * Admin Analytics Template
 * Complete rewrite with consistent header/footer structure
 */
if (!defined('ABSPATH')) {
    exit;
}

// Include header
include_once FORMXR_PLUGIN_DIR . 'templates/admin-header.php';

global $wpdb;

// Get date range from URL parameters
$date_range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : '30';
$start_date = '';
$end_date = date('Y-m-d');

switch ($date_range) {
    case '7':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        break;
    case '30':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        break;
    case '90':
        $start_date = date('Y-m-d', strtotime('-90 days'));
        break;
    case '365':
        $start_date = date('Y-m-d', strtotime('-365 days'));
        break;
    case 'all':
        $start_date = '2020-01-01'; // Far enough back
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-30 days'));
        break;
}

// Get analytics data
$total_questionnaires = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formxr_questionnaires");
$active_questionnaires = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formxr_questionnaires WHERE status = 'active'");
$total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formxr_submissions WHERE DATE(submitted_at) BETWEEN '$start_date' AND '$end_date'");
$completed_submissions = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formxr_submissions WHERE status = 'completed' AND DATE(submitted_at) BETWEEN '$start_date' AND '$end_date'");

// Get top performing questionnaires
$top_questionnaires = $wpdb->get_results($wpdb->prepare("
    SELECT q.id, q.title, q.price, COUNT(s.id) as submission_count, 
           (q.price * COUNT(s.id)) as total_revenue
    FROM {$wpdb->prefix}formxr_questionnaires q
    LEFT JOIN {$wpdb->prefix}formxr_submissions s ON q.id = s.questionnaire_id 
    AND DATE(s.submitted_at) BETWEEN %s AND %s
    GROUP BY q.id
    ORDER BY submission_count DESC
    LIMIT 10
", $start_date, $end_date));

// Get daily submission data for chart
$daily_data = $wpdb->get_results($wpdb->prepare("
    SELECT DATE(submitted_at) as date, COUNT(*) as count
    FROM {$wpdb->prefix}formxr_submissions
    WHERE DATE(submitted_at) BETWEEN %s AND %s
    GROUP BY DATE(submitted_at)
    ORDER BY date ASC
", $start_date, $end_date));

// Get submission status breakdown
$status_breakdown = $wpdb->get_results($wpdb->prepare("
    SELECT status, COUNT(*) as count
    FROM {$wpdb->prefix}formxr_submissions
    WHERE DATE(submitted_at) BETWEEN %s AND %s
    GROUP BY status
", $start_date, $end_date));

// Calculate revenue
$total_revenue = $wpdb->get_var($wpdb->prepare("
    SELECT SUM(q.price)
    FROM {$wpdb->prefix}formxr_submissions s
    LEFT JOIN {$wpdb->prefix}formxr_questionnaires q ON s.questionnaire_id = q.id
    WHERE s.status = 'completed' AND DATE(s.submitted_at) BETWEEN %s AND %s
", $start_date, $end_date));

$total_revenue = $total_revenue ? $total_revenue : 0;

// Calculate conversion rate
$conversion_rate = $total_submissions > 0 ? ($completed_submissions / $total_submissions) * 100 : 0;

// Get previous period for comparison
$previous_start = date('Y-m-d', strtotime($start_date . ' -' . $date_range . ' days'));
$previous_end = date('Y-m-d', strtotime($end_date . ' -' . $date_range . ' days'));

$previous_submissions = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) FROM {$wpdb->prefix}formxr_submissions 
    WHERE DATE(submitted_at) BETWEEN %s AND %s
", $previous_start, $previous_end));

$submission_change = $previous_submissions > 0 ? (($total_submissions - $previous_submissions) / $previous_submissions) * 100 : 0;
?>

<div class="formxr-admin-wrap">
    <!-- Page Header -->
    <div class="formxr-page-header">
        <div class="formxr-page-header-content">
            <h1 class="formxr-page-title">
                <span class="formxr-page-icon">📈</span>
                <?php _e('Analytics', 'formxr'); ?>
            </h1>
            <p class="formxr-page-subtitle">
                <?php _e('Track your questionnaire performance and submission trends', 'formxr'); ?>
            </p>
        </div>
        <div class="formxr-page-actions">
            <a href="<?php echo admin_url('admin.php?page=formxr-submissions'); ?>" class="formxr-btn formxr-btn-secondary">
                <span class="formxr-btn-icon">📊</span>
                <?php _e('View Submissions', 'formxr'); ?>
            </a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="formxr-section">
        <div class="formxr-filters">
            <div class="formxr-filter-group">
                <label class="formxr-filter-label">
                    <?php _e('Date Range:', 'formxr'); ?>
                </label>
                <div class="formxr-button-group">
                    <a href="<?php echo admin_url('admin.php?page=formxr-analytics&range=7'); ?>" 
                       class="formxr-btn <?php echo $date_range == '7' ? 'formxr-btn-primary' : 'formxr-btn-secondary'; ?>">
                        <?php _e('7 Days', 'formxr'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=formxr-analytics&range=30'); ?>" 
                       class="formxr-btn <?php echo $date_range == '30' ? 'formxr-btn-primary' : 'formxr-btn-secondary'; ?>">
                        <?php _e('30 Days', 'formxr'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=formxr-analytics&range=90'); ?>" 
                       class="formxr-btn <?php echo $date_range == '90' ? 'formxr-btn-primary' : 'formxr-btn-secondary'; ?>">
                        <?php _e('90 Days', 'formxr'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=formxr-analytics&range=365'); ?>" 
                       class="formxr-btn <?php echo $date_range == '365' ? 'formxr-btn-primary' : 'formxr-btn-secondary'; ?>">
                        <?php _e('1 Year', 'formxr'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=formxr-analytics&range=all'); ?>" 
                       class="formxr-btn <?php echo $date_range == 'all' ? 'formxr-btn-primary' : 'formxr-btn-secondary'; ?>">
                        <?php _e('All Time', 'formxr'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Section -->
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title"><?php _e('Key Metrics', 'formxr'); ?></h2>
        </div>
        <div class="formxr-grid formxr-grid-4">
            <div class="formxr-stat-card formxr-stat-card-primary">
                <div class="formxr-stat-icon">📊</div>
                <div class="formxr-stat-content">
                    <div class="formxr-stat-number"><?php echo number_format($total_submissions); ?></div>
                    <div class="formxr-stat-label"><?php _e('Total Submissions', 'formxr'); ?></div>
                    <?php if ($submission_change != 0) : ?>
                        <div class="formxr-stat-change <?php echo $submission_change > 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $submission_change > 0 ? '↗' : '↘'; ?> <?php echo abs(round($submission_change, 1)); ?>%
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="formxr-stat-card formxr-stat-card-success">
                <div class="formxr-stat-icon">✅</div>
                <div class="formxr-stat-content">
                    <div class="formxr-stat-number"><?php echo number_format($completed_submissions); ?></div>
                    <div class="formxr-stat-label"><?php _e('Completed', 'formxr'); ?></div>
                </div>
            </div>
            
            <div class="formxr-stat-card formxr-stat-card-info">
                <div class="formxr-stat-icon">🎯</div>
                <div class="formxr-stat-content">
                    <div class="formxr-stat-number"><?php echo round($conversion_rate, 1); ?>%</div>
                    <div class="formxr-stat-label"><?php _e('Completion Rate', 'formxr'); ?></div>
                </div>
            </div>
            
            <div class="formxr-stat-card formxr-stat-card-warning">
                <div class="formxr-stat-icon">💰</div>
                <div class="formxr-stat-content">
                    <div class="formxr-stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
                    <div class="formxr-stat-label"><?php _e('Revenue', 'formxr'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="formxr-grid formxr-grid-2">
        <!-- Submissions Over Time Chart -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <span class="formxr-widget-icon">📈</span>
                    <?php _e('Submissions Over Time', 'formxr'); ?>
                </h3>
            </div>
            <div class="formxr-widget-content">
                <div class="formxr-chart-container">
                    <canvas id="submissionsChart" width="400" height="200"></canvas>
                </div>
                <?php if (empty($daily_data)) : ?>
                    <div class="formxr-empty-state">
                        <div class="formxr-empty-icon">📈</div>
                        <h4><?php _e('No Data Available', 'formxr'); ?></h4>
                        <p><?php _e('No submissions found for the selected time period.', 'formxr'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status Breakdown Chart -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <span class="formxr-widget-icon">🥧</span>
                    <?php _e('Submission Status', 'formxr'); ?>
                </h3>
            </div>
            <div class="formxr-widget-content">
                <div class="formxr-chart-container">
                    <canvas id="statusChart" width="400" height="200"></canvas>
                </div>
                <?php if (empty($status_breakdown)) : ?>
                    <div class="formxr-empty-state">
                        <div class="formxr-empty-icon">🥧</div>
                        <h4><?php _e('No Data Available', 'formxr'); ?></h4>
                        <p><?php _e('No submissions found for the selected time period.', 'formxr'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Performing Questionnaires -->
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title"><?php _e('Top Performing Questionnaires', 'formxr'); ?></h2>
        </div>
        
        <?php if (!empty($top_questionnaires)) : ?>
            <div class="formxr-table-responsive">
                <table class="formxr-table">
                    <thead>
                        <tr>
                            <th><?php _e('Questionnaire', 'formxr'); ?></th>
                            <th><?php _e('Price', 'formxr'); ?></th>
                            <th><?php _e('Submissions', 'formxr'); ?></th>
                            <th><?php _e('Revenue', 'formxr'); ?></th>
                            <th><?php _e('Actions', 'formxr'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_questionnaires as $index => $questionnaire) : ?>
                            <tr>
                                <td>
                                    <div class="formxr-rank-item">
                                        <span class="formxr-rank">#<?php echo $index + 1; ?></span>
                                        <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $questionnaire->id); ?>" class="formxr-table-link">
                                            <?php echo esc_html($questionnaire->title); ?>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($questionnaire->price > 0) : ?>
                                        <span class="formxr-price">$<?php echo number_format($questionnaire->price, 2); ?></span>
                                    <?php else : ?>
                                        <span class="formxr-text-muted"><?php _e('Free', 'formxr'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="formxr-badge formxr-badge-primary">
                                        <?php echo number_format($questionnaire->submission_count); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="formxr-revenue">$<?php echo number_format($questionnaire->total_revenue, 2); ?></span>
                                </td>
                                <td>
                                    <div class="formxr-table-actions">
                                        <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $questionnaire->id); ?>" 
                                           class="formxr-btn formxr-btn-sm formxr-btn-secondary">
                                            <?php _e('Edit', 'formxr'); ?>
                                        </a>
                                        <a href="<?php echo admin_url('admin.php?page=formxr-submissions&questionnaire_id=' . $questionnaire->id); ?>" 
                                           class="formxr-btn formxr-btn-sm formxr-btn-info">
                                            <?php _e('View Submissions', 'formxr'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="formxr-empty-state">
                <div class="formxr-empty-icon">📊</div>
                <h4><?php _e('No Performance Data', 'formxr'); ?></h4>
                <p><?php _e('Create some questionnaires and start collecting submissions to see performance analytics.', 'formxr'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="formxr-btn formxr-btn-primary">
                    <span class="formxr-btn-icon">➕</span>
                    <?php _e('Create Questionnaire', 'formxr'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Additional Insights -->
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title"><?php _e('Insights & Recommendations', 'formxr'); ?></h2>
        </div>
        
        <div class="formxr-grid formxr-grid-2">
            <div class="formxr-widget">
                <div class="formxr-widget-header">
                    <h3 class="formxr-widget-title">
                        <span class="formxr-widget-icon">💡</span>
                        <?php _e('Quick Insights', 'formxr'); ?>
                    </h3>
                </div>
                <div class="formxr-widget-content">
                    <div class="formxr-insight-list">
                        <div class="formxr-insight-item">
                            <div class="formxr-insight-icon">🏆</div>
                            <div class="formxr-insight-content">
                                <h4><?php _e('Best Performing Day', 'formxr'); ?></h4>
                                <p>
                                    <?php 
                                    if (!empty($daily_data)) {
                                        $best_day = array_reduce($daily_data, function($carry, $item) {
                                            return (!$carry || $item->count > $carry->count) ? $item : $carry;
                                        });
                                        echo date('F j, Y', strtotime($best_day->date)) . ' (' . $best_day->count . ' submissions)';
                                    } else {
                                        _e('No data available', 'formxr');
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="formxr-insight-item">
                            <div class="formxr-insight-icon">📊</div>
                            <div class="formxr-insight-content">
                                <h4><?php _e('Average Daily Submissions', 'formxr'); ?></h4>
                                <p>
                                    <?php 
                                    $days = max(1, intval($date_range));
                                    $avg_daily = $total_submissions / $days;
                                    echo number_format($avg_daily, 1) . ' ' . __('per day', 'formxr');
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="formxr-insight-item">
                            <div class="formxr-insight-icon">💰</div>
                            <div class="formxr-insight-content">
                                <h4><?php _e('Average Revenue per Submission', 'formxr'); ?></h4>
                                <p>
                                    <?php 
                                    $avg_revenue = $completed_submissions > 0 ? $total_revenue / $completed_submissions : 0;
                                    echo '$' . number_format($avg_revenue, 2);
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-header">
                    <h3 class="formxr-widget-title">
                        <span class="formxr-widget-icon">🎯</span>
                        <?php _e('Recommendations', 'formxr'); ?>
                    </h3>
                </div>
                <div class="formxr-widget-content">
                    <div class="formxr-recommendation-list">
                        <?php if ($conversion_rate < 50) : ?>
                            <div class="formxr-recommendation-item">
                                <div class="formxr-recommendation-icon">⚠️</div>
                                <div class="formxr-recommendation-content">
                                    <h4><?php _e('Low Completion Rate', 'formxr'); ?></h4>
                                    <p><?php _e('Your completion rate is below 50%. Consider simplifying your questionnaires or reducing the number of required fields.', 'formxr'); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($active_questionnaires < 3) : ?>
                            <div class="formxr-recommendation-item">
                                <div class="formxr-recommendation-icon">📝</div>
                                <div class="formxr-recommendation-content">
                                    <h4><?php _e('Create More Questionnaires', 'formxr'); ?></h4>
                                    <p><?php _e('You have fewer than 3 active questionnaires. Creating more diverse questionnaires can help increase engagement.', 'formxr'); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($total_submissions > 50 && $total_revenue == 0) : ?>
                            <div class="formxr-recommendation-item">
                                <div class="formxr-recommendation-icon">💡</div>
                                <div class="formxr-recommendation-content">
                                    <h4><?php _e('Monetization Opportunity', 'formxr'); ?></h4>
                                    <p><?php _e('You have good submission volume. Consider adding premium questionnaires to generate revenue.', 'formxr'); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (empty($daily_data)) : ?>
                            <div class="formxr-recommendation-item">
                                <div class="formxr-recommendation-icon">🚀</div>
                                <div class="formxr-recommendation-content">
                                    <h4><?php _e('Get Started', 'formxr'); ?></h4>
                                    <p><?php _e('Create your first questionnaire and start collecting valuable feedback from your audience.', 'formxr'); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($daily_data)) : ?>
    // Submissions Over Time Chart
    const submissionsCtx = document.getElementById('submissionsChart').getContext('2d');
    new Chart(submissionsCtx, {
        type: 'line',
        data: {
            labels: [<?php echo implode(',', array_map(function($item) { return '"' . date('M j', strtotime($item->date)) . '"'; }, $daily_data)); ?>],
            datasets: [{
                label: '<?php _e('Submissions', 'formxr'); ?>',
                data: [<?php echo implode(',', array_column($daily_data, 'count')); ?>],
                borderColor: '#2AACE2',
                backgroundColor: 'rgba(42, 172, 226, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
    <?php if (!empty($status_breakdown)) : ?>
    // Status Breakdown Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php echo implode(',', array_map(function($item) { return '"' . ucfirst($item->status) . '"'; }, $status_breakdown)); ?>],
            datasets: [{
                data: [<?php echo implode(',', array_column($status_breakdown, 'count')); ?>],
                backgroundColor: ['#2AACE2', '#8062AA', '#F36E24', '#EF4681'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<?php
// Include footer
include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
?>
