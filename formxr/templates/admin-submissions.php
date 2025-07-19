<?php
/**
 * Admin Submissions Template
 * Complete rewrite with consistent header/footer structure
 */
if (!defined('ABSPATH')) {
    exit;
}

// Include header
include_once FORMXR_PLUGIN_DIR . 'templates/admin-header.php';

global $wpdb;

// Handle single view
$view_single = isset($_GET['view']) && $_GET['view'] === 'single' && isset($_GET['id']);
$single_submission_id = $view_single ? intval($_GET['id']) : 0;

// Handle bulk actions
if (isset($_POST['action']) && $_POST['action'] == 'bulk_delete' && isset($_POST['submission_ids'])) {
    if (wp_verify_nonce($_POST['bulk_nonce'], 'bulk_submissions')) {
        $deleted_count = 0;
        foreach ($_POST['submission_ids'] as $id) {
            $submission_id = intval($id);
            $deleted = $wpdb->delete(
                $wpdb->prefix . 'formxr_submissions',
                array('id' => $submission_id),
                array('%d')
            );
            if ($deleted) {
                $deleted_count++;
            }
        }
        if ($deleted_count > 0) {
            echo '<div class="formxr-alert formxr-alert-success">';
            echo '<span class="formxr-alert-icon">✅</span>';
            echo sprintf(__('%d submissions deleted successfully.', 'formxr'), $deleted_count);
            echo '</div>';
        }
    }
}

// Handle single actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $submission_id = intval($_GET['id']);
    $action = sanitize_text_field($_GET['action']);
    
    if (wp_verify_nonce($_GET['_wpnonce'], 'submission_action')) {
        switch ($action) {
            case 'delete':
                $deleted = $wpdb->delete(
                    $wpdb->prefix . 'formxr_submissions',
                    array('id' => $submission_id),
                    array('%d')
                );
                if ($deleted) {
                    echo '<div class="formxr-alert formxr-alert-success">';
                    echo '<span class="formxr-alert-icon">✅</span>';
                    echo __('Submission deleted successfully.', 'formxr');
                    echo '</div>';
                }
                break;
        }
    }
}

if ($view_single) {
    // Get single submission details
    $submission = $wpdb->get_row($wpdb->prepare("
        SELECT s.*, q.title as questionnaire_title, q.id as questionnaire_id
        FROM {$wpdb->prefix}formxr_submissions s
        LEFT JOIN {$wpdb->prefix}formxr_questionnaires q ON s.questionnaire_id = q.id
        WHERE s.id = %d
    ", $single_submission_id));
    
    if (!$submission) {
        echo '<div class="formxr-alert formxr-alert-error">';
        echo '<span class="formxr-alert-icon">❌</span>';
        echo __('Submission not found.', 'formxr');
        echo '</div>';
        include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
        return;
    }
    
    $submission_data = json_decode($submission->submission_data, true);
    ?>
    
    <div class="formxr-admin-wrap">
        <!-- Page Header -->
        <div class="formxr-page-header">
            <div class="formxr-page-header-content">
                <h1 class="formxr-page-title">
                    <span class="formxr-page-icon">📊</span>
                    <?php _e('Submission Details', 'formxr'); ?>
                </h1>
                <p class="formxr-page-subtitle">
                    <?php printf(__('Viewing submission for "%s"', 'formxr'), esc_html($submission->questionnaire_title)); ?>
                </p>
            </div>
            <div class="formxr-page-actions">
                <a href="<?php echo admin_url('admin.php?page=formxr-submissions'); ?>" class="formxr-btn formxr-btn-secondary">
                    <span class="formxr-btn-icon">←</span>
                    <?php _e('Back to Submissions', 'formxr'); ?>
                </a>
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=formxr-submissions&action=delete&id=' . $submission->id), 'submission_action'); ?>" 
                   class="formxr-btn formxr-btn-error"
                   onclick="return confirm('<?php _e('Are you sure you want to delete this submission?', 'formxr'); ?>')">
                    <span class="formxr-btn-icon">🗑️</span>
                    <?php _e('Delete', 'formxr'); ?>
                </a>
            </div>
        </div>

        <!-- Submission Info Section -->
        <div class="formxr-section">
            <div class="formxr-widget">
                <div class="formxr-widget-header">
                    <h3 class="formxr-widget-title">
                        <span class="formxr-widget-icon">ℹ️</span>
                        <?php _e('Submission Information', 'formxr'); ?>
                    </h3>
                </div>
                <div class="formxr-widget-content">
                    <div class="formxr-grid formxr-grid-2">
                        <div class="formxr-info-item">
                            <strong><?php _e('Questionnaire:', 'formxr'); ?></strong>
                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $submission->questionnaire_id); ?>">
                                <?php echo esc_html($submission->questionnaire_title); ?>
                            </a>
                        </div>
                        <div class="formxr-info-item">
                            <strong><?php _e('Status:', 'formxr'); ?></strong>
                            <span class="formxr-badge formxr-badge-<?php echo $submission->status === 'completed' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($submission->status); ?>
                            </span>
                        </div>
                        <div class="formxr-info-item">
                            <strong><?php _e('Submitted:', 'formxr'); ?></strong>
                            <?php echo date('F j, Y \a\t g:i A', strtotime($submission->submitted_at)); ?>
                        </div>
                        <div class="formxr-info-item">
                            <strong><?php _e('User IP:', 'formxr'); ?></strong>
                            <?php echo esc_html($submission->user_ip ?? 'N/A'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission Data Section -->
        <div class="formxr-section">
            <div class="formxr-widget">
                <div class="formxr-widget-header">
                    <h3 class="formxr-widget-title">
                        <span class="formxr-widget-icon">📋</span>
                        <?php _e('Submission Data', 'formxr'); ?>
                    </h3>
                </div>
                <div class="formxr-widget-content">
                    <?php if (!empty($submission_data)) : ?>
                        <div class="formxr-submission-data">
                            <?php foreach ($submission_data as $field => $value) : ?>
                                <div class="formxr-data-item">
                                    <div class="formxr-data-label">
                                        <strong><?php echo esc_html(ucwords(str_replace('_', ' ', $field))); ?>:</strong>
                                    </div>
                                    <div class="formxr-data-value">
                                        <?php 
                                        if (is_array($value)) {
                                            echo esc_html(implode(', ', $value));
                                        } else {
                                            echo esc_html($value);
                                        }
                                        ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <div class="formxr-empty-state">
                            <div class="formxr-empty-icon">📝</div>
                            <h4><?php _e('No Data Available', 'formxr'); ?></h4>
                            <p><?php _e('This submission has no data to display.', 'formxr'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php
} else {
    // Show submissions list
    $questionnaire_filter = isset($_GET['questionnaire_id']) ? intval($_GET['questionnaire_id']) : 0;
    
    // Build the query
    $where_clause = '';
    if ($questionnaire_filter > 0) {
        $where_clause = $wpdb->prepare(' WHERE s.questionnaire_id = %d', $questionnaire_filter);
    }
    
    // Get submissions with questionnaire info
    $submissions = $wpdb->get_results("
        SELECT s.*, q.title as questionnaire_title
        FROM {$wpdb->prefix}formxr_submissions s
        LEFT JOIN {$wpdb->prefix}formxr_questionnaires q ON s.questionnaire_id = q.id
        $where_clause
        ORDER BY s.submitted_at DESC
    ");
    
    // Get questionnaires for filter dropdown
    $questionnaires = $wpdb->get_results("SELECT id, title FROM {$wpdb->prefix}formxr_questionnaires ORDER BY title");
    
    // Get stats
    $total_submissions = count($submissions);
    $completed_submissions = count(array_filter($submissions, function($s) { return $s->status === 'completed'; }));
    $pending_submissions = count(array_filter($submissions, function($s) { return $s->status === 'pending'; }));
    ?>
    
    <div class="formxr-admin-wrap">
        <!-- Page Header -->
        <div class="formxr-page-header">
            <div class="formxr-page-header-content">
                <h1 class="formxr-page-title">
                    <span class="formxr-page-icon">📊</span>
                    <?php _e('Submissions', 'formxr'); ?>
                    <?php if ($questionnaire_filter > 0) : ?>
                        <span class="formxr-page-filter">
                            - <?php echo esc_html($wpdb->get_var($wpdb->prepare("SELECT title FROM {$wpdb->prefix}formxr_questionnaires WHERE id = %d", $questionnaire_filter))); ?>
                        </span>
                    <?php endif; ?>
                </h1>
                <p class="formxr-page-subtitle">
                    <?php _e('View and manage all questionnaire submissions', 'formxr'); ?>
                </p>
            </div>
            <div class="formxr-page-actions">
                <a href="<?php echo admin_url('admin.php?page=formxr-analytics'); ?>" class="formxr-btn formxr-btn-secondary">
                    <span class="formxr-btn-icon">📈</span>
                    <?php _e('Analytics', 'formxr'); ?>
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <?php if (!empty($questionnaires)) : ?>
            <div class="formxr-section">
                <div class="formxr-filters">
                    <form method="get" action="<?php echo admin_url('admin.php'); ?>">
                        <input type="hidden" name="page" value="formxr-submissions">
                        
                        <div class="formxr-filter-group">
                            <label for="questionnaire_filter" class="formxr-filter-label">
                                <?php _e('Filter by Questionnaire:', 'formxr'); ?>
                            </label>
                            <select name="questionnaire_id" id="questionnaire_filter" class="formxr-select">
                                <option value=""><?php _e('All Questionnaires', 'formxr'); ?></option>
                                <?php foreach ($questionnaires as $questionnaire) : ?>
                                    <option value="<?php echo $questionnaire->id; ?>" <?php selected($questionnaire_filter, $questionnaire->id); ?>>
                                        <?php echo esc_html($questionnaire->title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="formxr-btn formxr-btn-secondary">
                                <?php _e('Filter', 'formxr'); ?>
                            </button>
                            <?php if ($questionnaire_filter > 0) : ?>
                                <a href="<?php echo admin_url('admin.php?page=formxr-submissions'); ?>" class="formxr-btn formxr-btn-secondary">
                                    <?php _e('Clear', 'formxr'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Statistics Section -->
        <div class="formxr-section">
            <div class="formxr-grid formxr-grid-3">
                <div class="formxr-stat-card formxr-stat-card-primary">
                    <div class="formxr-stat-icon">📊</div>
                    <div class="formxr-stat-content">
                        <div class="formxr-stat-number"><?php echo number_format($total_submissions); ?></div>
                        <div class="formxr-stat-label"><?php _e('Total Submissions', 'formxr'); ?></div>
                    </div>
                </div>
                
                <div class="formxr-stat-card formxr-stat-card-success">
                    <div class="formxr-stat-icon">✅</div>
                    <div class="formxr-stat-content">
                        <div class="formxr-stat-number"><?php echo number_format($completed_submissions); ?></div>
                        <div class="formxr-stat-label"><?php _e('Completed', 'formxr'); ?></div>
                    </div>
                </div>
                
                <div class="formxr-stat-card formxr-stat-card-warning">
                    <div class="formxr-stat-icon">⏳</div>
                    <div class="formxr-stat-content">
                        <div class="formxr-stat-number"><?php echo number_format($pending_submissions); ?></div>
                        <div class="formxr-stat-label"><?php _e('Pending', 'formxr'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submissions List Section -->
        <div class="formxr-section">
            <div class="formxr-section-header">
                <h2 class="formxr-section-title"><?php _e('All Submissions', 'formxr'); ?></h2>
            </div>
            
            <?php if (!empty($submissions)) : ?>
                <form method="post" id="formxr-submissions-form">
                    <?php wp_nonce_field('bulk_submissions', 'bulk_nonce'); ?>
                    
                    <!-- Bulk Actions -->
                    <div class="formxr-bulk-actions">
                        <select name="action" id="bulk-action-selector">
                            <option value=""><?php _e('Bulk Actions', 'formxr'); ?></option>
                            <option value="bulk_delete"><?php _e('Delete', 'formxr'); ?></option>
                        </select>
                        <button type="submit" class="formxr-btn formxr-btn-secondary" onclick="return confirm('<?php _e('Are you sure you want to delete the selected submissions?', 'formxr'); ?>')">
                            <?php _e('Apply', 'formxr'); ?>
                        </button>
                    </div>
                    
                    <!-- Submissions Table -->
                    <div class="formxr-table-responsive">
                        <table class="formxr-table">
                            <thead>
                                <tr>
                                    <th class="formxr-table-check">
                                        <input type="checkbox" id="select-all-submissions">
                                    </th>
                                    <th><?php _e('Questionnaire', 'formxr'); ?></th>
                                    <th><?php _e('Status', 'formxr'); ?></th>
                                    <th><?php _e('Submitted', 'formxr'); ?></th>
                                    <th><?php _e('User IP', 'formxr'); ?></th>
                                    <th><?php _e('Actions', 'formxr'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $submission) : ?>
                                    <tr>
                                        <td class="formxr-table-check">
                                            <input type="checkbox" name="submission_ids[]" value="<?php echo $submission->id; ?>">
                                        </td>
                                        <td>
                                            <div class="formxr-table-title">
                                                <a href="<?php echo admin_url('admin.php?page=formxr-submissions&view=single&id=' . $submission->id); ?>" class="formxr-table-link">
                                                    <?php echo esc_html($submission->questionnaire_title); ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="formxr-badge formxr-badge-<?php echo $submission->status === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($submission->status); ?>
                                            </span>
                                        </td>
                                        <td class="formxr-text-muted">
                                            <?php echo human_time_diff(strtotime($submission->submitted_at), current_time('timestamp')) . ' ago'; ?>
                                        </td>
                                        <td class="formxr-text-muted">
                                            <?php echo esc_html($submission->user_ip ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <div class="formxr-table-actions">
                                                <a href="<?php echo admin_url('admin.php?page=formxr-submissions&view=single&id=' . $submission->id); ?>" 
                                                   class="formxr-btn formxr-btn-sm formxr-btn-secondary" 
                                                   title="<?php _e('View Details', 'formxr'); ?>">
                                                    <?php _e('View', 'formxr'); ?>
                                                </a>
                                                
                                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=formxr-submissions&action=delete&id=' . $submission->id), 'submission_action'); ?>" 
                                                   class="formxr-btn formxr-btn-sm formxr-btn-error" 
                                                   title="<?php _e('Delete', 'formxr'); ?>"
                                                   onclick="return confirm('<?php _e('Are you sure you want to delete this submission?', 'formxr'); ?>')">
                                                    <?php _e('Delete', 'formxr'); ?>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            <?php else : ?>
                <div class="formxr-empty-state">
                    <div class="formxr-empty-icon">📊</div>
                    <h4><?php _e('No Submissions Found', 'formxr'); ?></h4>
                    <p><?php _e('Submissions will appear here once users start filling out your questionnaires.', 'formxr'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="formxr-btn formxr-btn-primary">
                        <span class="formxr-btn-icon">📝</span>
                        <?php _e('View Questionnaires', 'formxr'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select all functionality
        const selectAll = document.getElementById('select-all-submissions');
        const checkboxes = document.querySelectorAll('input[name="submission_ids[]"]');
        
        if (selectAll && checkboxes.length > 0) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                    selectAll.checked = allChecked;
                    selectAll.indeterminate = someChecked && !allChecked;
                });
            });
        }
    });
    </script>
    
    <?php
}

// Include footer
include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
?>
