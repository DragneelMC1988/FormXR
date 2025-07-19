<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$submissions_table = $wpdb->prefix . 'formxr_submissions';
$questionnaires_table = $wpdb->prefix . 'formxr_questionnaires';

// Handle actions
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$submission_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action === 'delete' && $submission_id) {
    $wpdb->delete($submissions_table, array('id' => $submission_id));
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Submission deleted successfully.', 'formxr') . '</p></div>';
}

// Pagination
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Search functionality
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$search_query = '';
if ($search) {
    $search_query = $wpdb->prepare(" WHERE s.customer_name LIKE %s OR s.customer_email LIKE %s", 
        '%' . $wpdb->esc_like($search) . '%', 
        '%' . $wpdb->esc_like($search) . '%'
    );
}

// Get submissions with questionnaire data
$submissions = $wpdb->get_results($wpdb->prepare("
    SELECT s.*, q.title as questionnaire_title 
    FROM $submissions_table s 
    LEFT JOIN $questionnaires_table q ON s.questionnaire_id = q.id 
    $search_query
    ORDER BY s.submitted_at DESC 
    LIMIT %d OFFSET %d
", $per_page, $offset));

// Get total count for pagination
$total_submissions = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table s $search_query");
$total_pages = ceil($total_submissions / $per_page);

// If viewing single submission
if ($action === 'view' && $submission_id) {
    $submission = $wpdb->get_row($wpdb->prepare("
        SELECT s.*, q.title as questionnaire_title, q.questions 
        FROM $submissions_table s 
        LEFT JOIN $questionnaires_table q ON s.questionnaire_id = q.id 
        WHERE s.id = %d
    ", $submission_id));
    
    if ($submission) {
        include 'admin-submission-detail.php';
        return;
    }
}
?>

<div class="wrap formxr-container">
    <div class="formxr-page-header">
        <div class="formxr-page-title">
            <h1>
                <span class="dashicons dashicons-feedback"></span>
                <?php _e('Form Submissions', 'formxr'); ?>
            </h1>
            <div class="formxr-header-actions">
                <a href="<?php echo admin_url('admin-ajax.php?action=formxr_export_csv'); ?>" class="btn-formxr">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Export CSV', 'formxr'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="btn-formxr btn-outline">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('New Form', 'formxr'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="formxr-section">
        <div class="formxr-section-content">
            <form method="get" class="formxr-search-form">
                <input type="hidden" name="page" value="formxr-submissions">
                <div class="search-box">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" 
                           placeholder="<?php _e('Search by customer name or email...', 'formxr'); ?>" class="formxr-search-input">
                    <button type="submit" class="btn-formxr">
                        <span class="dashicons dashicons-search"></span>
                        <?php _e('Search', 'formxr'); ?>
                    </button>
                    <?php if ($search): ?>
                        <a href="<?php echo admin_url('admin.php?page=formxr-submissions'); ?>" class="btn-formxr btn-outline">
                            <?php _e('Clear', 'formxr'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="formxr-stats-grid">
        <div class="formxr-card stat-card">
            <div class="stat-number"><?php echo number_format($total_submissions); ?></div>
            <div class="stat-label"><?php _e('Total Submissions', 'formxr'); ?></div>
        </div>
        <div class="formxr-card stat-card">
            <div class="stat-number">
                $<?php 
                $total_value = $wpdb->get_var("SELECT SUM(calculated_price) FROM $submissions_table");
                echo number_format($total_value ?: 0, 0); 
                ?>
            </div>
            <div class="stat-label"><?php _e('Total Value', 'formxr'); ?></div>
        </div>
        <div class="formxr-card stat-card">
            <div class="stat-number">
                $<?php 
                $avg_value = $wpdb->get_var("SELECT AVG(calculated_price) FROM $submissions_table");
                echo number_format($avg_value ?: 0, 0); 
                ?>
            </div>
            <div class="stat-label"><?php _e('Average Quote', 'formxr'); ?></div>
        </div>
        <div class="formxr-card stat-card">
            <div class="stat-number">
                <?php 
                $today_count = $wpdb->get_var("SELECT COUNT(*) FROM $submissions_table WHERE DATE(submitted_at) = CURDATE()");
                echo $today_count ?: 0; 
                ?>
            </div>
            <div class="stat-label"><?php _e('Today', 'formxr'); ?></div>
        </div>
    </div>

    <!-- Submissions Table -->
    <div class="formxr-section">
        <div class="formxr-section-content">
            <?php if (empty($submissions)): ?>
                <div class="formxr-empty-state">
                    <div class="empty-icon">
                        <span class="dashicons dashicons-feedback"></span>
                    </div>
                    <h3><?php _e('No submissions yet', 'formxr'); ?></h3>
                    <p><?php _e('Once customers start submitting your forms, they\'ll appear here.', 'formxr'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="btn-formxr">
                        <?php _e('Create Your First Form', 'formxr'); ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="formxr-table-container">
                    <table class="formxr-table">
                        <thead>
                            <tr>
                                <th><?php _e('Customer', 'formxr'); ?></th>
                                <th><?php _e('Form', 'formxr'); ?></th>
                                <th><?php _e('Quote Price', 'formxr'); ?></th>
                                <th><?php _e('Pricing Type', 'formxr'); ?></th>
                                <th><?php _e('Submitted', 'formxr'); ?></th>
                                <th><?php _e('Actions', 'formxr'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <td>
                                        <div class="customer-info">
                                            <strong><?php echo esc_html($submission->customer_name); ?></strong>
                                            <br>
                                            <small><?php echo esc_html($submission->customer_email); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($submission->questionnaire_title ?: 'Unknown Form'); ?></strong>
                                    </td>
                                    <td>
                                        <span class="price-amount">
                                            $<?php echo number_format($submission->calculated_price ?: 0, 0); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="pricing-type <?php echo esc_attr($submission->price_type); ?>">
                                            <?php echo ucfirst($submission->price_type ?: 'unknown'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="submission-date">
                                            <?php echo date('M j, Y', strtotime($submission->submitted_at)); ?>
                                            <br>
                                            <small><?php echo date('g:i A', strtotime($submission->submitted_at)); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            <a href="<?php echo admin_url('admin.php?page=formxr-submissions&action=view&id=' . $submission->id); ?>" 
                                               class="btn-formxr btn-small">
                                                <span class="dashicons dashicons-visibility"></span>
                                                <?php _e('View', 'formxr'); ?>
                                            </a>
                                            <a href="<?php echo admin_url('admin.php?page=formxr-submissions&action=delete&id=' . $submission->id); ?>" 
                                               class="btn-formxr btn-small btn-danger"
                                               onclick="return confirm('<?php _e('Are you sure you want to delete this submission?', 'formxr'); ?>')">
                                                <span class="dashicons dashicons-trash"></span>
                                                <?php _e('Delete', 'formxr'); ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="formxr-pagination">
                        <?php
                        $base_url = admin_url('admin.php?page=formxr-submissions');
                        if ($search) {
                            $base_url .= '&s=' . urlencode($search);
                        }
                        
                        // Previous page
                        if ($current_page > 1): ?>
                            <a href="<?php echo $base_url . '&paged=' . ($current_page - 1); ?>" class="pagination-link">
                                &laquo; <?php _e('Previous', 'formxr'); ?>
                            </a>
                        <?php endif;
                        
                        // Page numbers
                        for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                            <a href="<?php echo $base_url . '&paged=' . $i; ?>" 
                               class="pagination-link <?php echo $i === $current_page ? 'current' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor;
                        
                        // Next page
                        if ($current_page < $total_pages): ?>
                            <a href="<?php echo $base_url . '&paged=' . ($current_page + 1); ?>" class="pagination-link">
                                <?php _e('Next', 'formxr'); ?> &raquo;
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
