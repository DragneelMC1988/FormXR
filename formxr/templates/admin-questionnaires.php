<?php
/**
 * Admin Questionnaires Template
 * Complete rewrite with consistent header/footer structure
 */
if (!defined('ABSPATH')) {
    exit;
}

// Include header
include_once FORMXR_PLUGIN_DIR . 'templates/admin-header.php';

global $wpdb;

// Handle bulk actions
if (isset($_POST['action']) && $_POST['action'] == 'bulk_delete' && isset($_POST['questionnaire_ids'])) {
    if (wp_verify_nonce($_POST['bulk_nonce'], 'bulk_questionnaires')) {
        $deleted_count = 0;
        foreach ($_POST['questionnaire_ids'] as $id) {
            $questionnaire_id = intval($id);
            // Delete questionnaire
            $deleted = $wpdb->delete(
                $wpdb->prefix . 'formxr_questionnaires',
                array('id' => $questionnaire_id),
                array('%d')
            );
            if ($deleted) {
                $deleted_count++;
            }
        }
        if ($deleted_count > 0) {
            echo '<div class="formxr-alert formxr-alert-success">';
            echo '<span class="formxr-alert-icon">✅</span>';
            echo sprintf(__('%d questionnaires deleted successfully.', 'formxr'), $deleted_count);
            echo '</div>';
        }
    }
}

// Handle single actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $questionnaire_id = intval($_GET['id']);
    $action = sanitize_text_field($_GET['action']);
    
    if (wp_verify_nonce($_GET['_wpnonce'], 'questionnaire_action')) {
        switch ($action) {
            case 'delete':
                $deleted = $wpdb->delete(
                    $wpdb->prefix . 'formxr_questionnaires',
                    array('id' => $questionnaire_id),
                    array('%d')
                );
                if ($deleted) {
                    echo '<div class="formxr-alert formxr-alert-success">';
                    echo '<span class="formxr-alert-icon">✅</span>';
                    echo __('Questionnaire deleted successfully.', 'formxr');
                    echo '</div>';
                }
                break;
                
            case 'activate':
                $updated = $wpdb->update(
                    $wpdb->prefix . 'formxr_questionnaires',
                    array('status' => 'active'),
                    array('id' => $questionnaire_id),
                    array('%s'),
                    array('%d')
                );
                if ($updated) {
                    echo '<div class="formxr-alert formxr-alert-success">';
                    echo '<span class="formxr-alert-icon">✅</span>';
                    echo __('Questionnaire activated successfully.', 'formxr');
                    echo '</div>';
                }
                break;
                
            case 'deactivate':
                $updated = $wpdb->update(
                    $wpdb->prefix . 'formxr_questionnaires',
                    array('status' => 'inactive'),
                    array('id' => $questionnaire_id),
                    array('%s'),
                    array('%d')
                );
                if ($updated) {
                    echo '<div class="formxr-alert formxr-alert-success">';
                    echo '<span class="formxr-alert-icon">✅</span>';
                    echo __('Questionnaire deactivated successfully.', 'formxr');
                    echo '</div>';
                }
                break;
        }
    }
}

// Get questionnaires with submission counts
$questionnaires = $wpdb->get_results("
    SELECT q.*, 
           COUNT(s.id) as submission_count
    FROM {$wpdb->prefix}formxr_questionnaires q
    LEFT JOIN {$wpdb->prefix}formxr_submissions s ON q.id = s.questionnaire_id
    GROUP BY q.id
    ORDER BY q.created_at DESC
");

// Get totals for stats
$total_questionnaires = count($questionnaires);
$active_questionnaires = count(array_filter($questionnaires, function($q) { return $q->status === 'active'; }));
$total_submissions = array_sum(array_column($questionnaires, 'submission_count'));
?>

<div class="formxr-admin-wrap">
    <!-- Page Header -->
    <div class="formxr-page-header">
        <div class="formxr-page-header-content">
            <h1 class="formxr-page-title">
                <span class="formxr-page-icon">📝</span>
                <?php _e('Questionnaires', 'formxr'); ?>
            </h1>
            <p class="formxr-page-subtitle">
                <?php _e('Manage your questionnaires and track their performance', 'formxr'); ?>
            </p>
        </div>
        <div class="formxr-page-actions">
            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=wizard'); ?>" class="formxr-btn formxr-btn-primary">
                <span class="formxr-btn-icon">🧙‍♂️</span>
                <?php _e('Create with Wizard', 'formxr'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="formxr-btn formxr-btn-secondary">
                <span class="formxr-btn-icon">➕</span>
                <?php _e('Quick Create', 'formxr'); ?>
            </a>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="formxr-section">
        <div class="formxr-grid formxr-grid-3">
            <div class="formxr-stat-card formxr-stat-card-primary">
                <div class="formxr-stat-icon">📝</div>
                <div class="formxr-stat-content">
                    <div class="formxr-stat-number"><?php echo number_format($total_questionnaires); ?></div>
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
                    <div class="formxr-stat-number"><?php echo number_format($total_submissions); ?></div>
                    <div class="formxr-stat-label"><?php _e('Total Submissions', 'formxr'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Questionnaires List Section -->
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title"><?php _e('All Questionnaires', 'formxr'); ?></h2>
        </div>
        
        <?php if (!empty($questionnaires)) : ?>
            <form method="post" id="formxr-questionnaires-form">
                <?php wp_nonce_field('bulk_questionnaires', 'bulk_nonce'); ?>
                
                <!-- Bulk Actions -->
                <div class="formxr-bulk-actions">
                    <select name="action" id="bulk-action-selector">
                        <option value=""><?php _e('Bulk Actions', 'formxr'); ?></option>
                        <option value="bulk_delete"><?php _e('Delete', 'formxr'); ?></option>
                    </select>
                    <button type="submit" class="formxr-btn formxr-btn-secondary" onclick="return confirm('<?php _e('Are you sure you want to delete the selected questionnaires?', 'formxr'); ?>')">
                        <?php _e('Apply', 'formxr'); ?>
                    </button>
                </div>
                
                <!-- Questionnaires Table -->
                <div class="formxr-table-responsive">
                    <table class="formxr-table">
                        <thead>
                            <tr>
                                <th class="formxr-table-check">
                                    <input type="checkbox" id="select-all-questionnaires">
                                </th>
                                <th><?php _e('Title', 'formxr'); ?></th>
                                <th><?php _e('Status', 'formxr'); ?></th>
                                <th><?php _e('Price', 'formxr'); ?></th>
                                <th><?php _e('Submissions', 'formxr'); ?></th>
                                <th><?php _e('Created', 'formxr'); ?></th>
                                <th><?php _e('Actions', 'formxr'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questionnaires as $questionnaire) : ?>
                                <tr>
                                    <td class="formxr-table-check">
                                        <input type="checkbox" name="questionnaire_ids[]" value="<?php echo $questionnaire->id; ?>">
                                    </td>
                                    <td>
                                        <div class="formxr-table-title">
                                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $questionnaire->id); ?>" class="formxr-table-link">
                                                <?php echo esc_html($questionnaire->title); ?>
                                            </a>
                                            <?php if (!empty($questionnaire->description)) : ?>
                                                <p class="formxr-table-description"><?php echo esc_html(wp_trim_words($questionnaire->description, 10)); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="formxr-badge formxr-badge-<?php echo $questionnaire->status === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($questionnaire->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($questionnaire->price > 0) : ?>
                                            <span class="formxr-price">$<?php echo number_format($questionnaire->price, 2); ?></span>
                                        <?php else : ?>
                                            <span class="formxr-text-muted"><?php _e('Free', 'formxr'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=formxr-submissions&questionnaire_id=' . $questionnaire->id); ?>" class="formxr-submissions-link">
                                            <?php echo number_format($questionnaire->submission_count); ?>
                                        </a>
                                    </td>
                                    <td class="formxr-text-muted">
                                        <?php echo human_time_diff(strtotime($questionnaire->created_at), current_time('timestamp')) . ' ago'; ?>
                                    </td>
                                    <td>
                                        <div class="formxr-table-actions">
                                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $questionnaire->id); ?>" 
                                               class="formxr-btn formxr-btn-sm formxr-btn-secondary" 
                                               title="<?php _e('Edit', 'formxr'); ?>">
                                                <?php _e('Edit', 'formxr'); ?>
                                            </a>
                                            
                                            <?php if ($questionnaire->status === 'active') : ?>
                                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=formxr-questionnaires&action=deactivate&id=' . $questionnaire->id), 'questionnaire_action'); ?>" 
                                                   class="formxr-btn formxr-btn-sm formxr-btn-warning" 
                                                   title="<?php _e('Deactivate', 'formxr'); ?>">
                                                    <?php _e('Deactivate', 'formxr'); ?>
                                                </a>
                                            <?php else : ?>
                                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=formxr-questionnaires&action=activate&id=' . $questionnaire->id), 'questionnaire_action'); ?>" 
                                                   class="formxr-btn formxr-btn-sm formxr-btn-success" 
                                                   title="<?php _e('Activate', 'formxr'); ?>">
                                                    <?php _e('Activate', 'formxr'); ?>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=formxr-questionnaires&action=delete&id=' . $questionnaire->id), 'questionnaire_action'); ?>" 
                                               class="formxr-btn formxr-btn-sm formxr-btn-error" 
                                               title="<?php _e('Delete', 'formxr'); ?>"
                                               onclick="return confirm('<?php _e('Are you sure you want to delete this questionnaire?', 'formxr'); ?>')">
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
                <div class="formxr-empty-icon">📝</div>
                <h4><?php _e('No Questionnaires Found', 'formxr'); ?></h4>
                <p><?php _e('Create your first questionnaire to get started with FormXR.', 'formxr'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="formxr-btn formxr-btn-primary">
                    <span class="formxr-btn-icon">➕</span>
                    <?php _e('Create Your First Questionnaire', 'formxr'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAll = document.getElementById('select-all-questionnaires');
    const checkboxes = document.querySelectorAll('input[name="questionnaire_ids[]"]');
    
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
// Include footer
include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
?>
