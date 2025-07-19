<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Handle bulk actions
if (isset($_POST['action']) && $_POST['action'] == 'bulk_delete' && isset($_POST['questionnaire_ids'])) {
    if (wp_verify_nonce($_POST['bulk_nonce'], 'bulk_questionnaires')) {
        $deleted_count = 0;
        foreach ($_POST['questionnaire_ids'] as $id) {
            if ($this->delete_questionnaire(intval($id))) {
                $deleted_count++;
            }
        }
        echo '<div class="notice notice-success"><p>' . sprintf(__('%d questionnaires deleted successfully.', 'formxr'), $deleted_count) . '</p></div>';
    }
}

// Get questionnaires
$questionnaires_table = $wpdb->prefix . 'formxr_questionnaires';
$submissions_table = $wpdb->prefix . 'formxr_submissions';

$questionnaires = $wpdb->get_results("
    SELECT q.*, 
           COUNT(s.id) as submission_count,
           MAX(s.submitted_at) as last_submission
    FROM $questionnaires_table q
    LEFT JOIN $submissions_table s ON q.id = s.questionnaire_id
    GROUP BY q.id
    ORDER BY q.updated_at DESC
");
?>

<div class="wrap">
    <div class="formxr-page-header">
        <div class="formxr-page-title">
            <h1>
                <span class="dashicons dashicons-feedback"></span>
                <?php _e('Questionnaires', 'formxr'); ?>
            </h1>
            <div class="formxr-header-actions">
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="btn-formxr">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('New Questionnaire', 'formxr'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="formxr-container">
    
    <?php if ($questionnaires): ?>
    <form method="post">
        <?php wp_nonce_field('bulk_questionnaires', 'bulk_nonce'); ?>
        
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e('Select bulk action', 'formxr'); ?></label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="-1"><?php _e('Bulk Actions', 'formxr'); ?></option>
                    <option value="bulk_delete"><?php _e('Delete', 'formxr'); ?></option>
                </select>
                <input type="submit" id="doaction" class="button action" value="<?php _e('Apply', 'formxr'); ?>">
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped questionnaires">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <input id="cb-select-all-1" type="checkbox" />
                    </td>
                    <th scope="col" class="manage-column column-title"><?php _e('Title', 'formxr'); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e('Status', 'formxr'); ?></th>
                    <th scope="col" class="manage-column column-submissions"><?php _e('Submissions', 'formxr'); ?></th>
                    <th scope="col" class="manage-column column-pricing"><?php _e('Pricing', 'formxr'); ?></th>
                    <th scope="col" class="manage-column column-shortcode"><?php _e('Shortcode', 'formxr'); ?></th>
                    <th scope="col" class="manage-column column-updated"><?php _e('Last Updated', 'formxr'); ?></th>
                    <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'formxr'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questionnaires as $questionnaire): ?>
                <tr>
                    <th scope="row" class="check-column">
                        <input type="checkbox" name="questionnaire_ids[]" value="<?php echo esc_attr($questionnaire->id); ?>" />
                    </th>
                    <td class="column-title">
                        <strong>
                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $questionnaire->id); ?>">
                                <?php echo esc_html($questionnaire->title); ?>
                            </a>
                        </strong>
                        <?php if ($questionnaire->description): ?>
                        <p class="description"><?php echo esc_html(wp_trim_words($questionnaire->description, 15)); ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="column-status">
                        <span class="status-badge status-<?php echo esc_attr($questionnaire->status); ?>">
                            <?php echo esc_html(ucfirst($questionnaire->status)); ?>
                        </span>
                    </td>
                    <td class="column-submissions">
                        <a href="<?php echo admin_url('admin.php?page=formxr-submissions&questionnaire_id=' . $questionnaire->id); ?>">
                            <?php echo esc_html($questionnaire->submission_count); ?>
                        </a>
                        <?php if ($questionnaire->last_submission): ?>
                        <br><small><?php printf(__('Last: %s', 'formxr'), date('M j, Y', strtotime($questionnaire->last_submission))); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="column-pricing">
                        <?php if ($questionnaire->pricing_enabled): ?>
                        <span class="pricing-enabled">
                            <span class="dashicons dashicons-money-alt"></span>
                            <?php _e('Enabled', 'formxr'); ?>
                        </span>
                        <?php else: ?>
                        <span class="pricing-disabled"><?php _e('Disabled', 'formxr'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="column-shortcode">
                        <code onclick="this.select()">[formxr_form id="<?php echo esc_attr($questionnaire->id); ?>"]</code>
                    </td>
                    <td class="column-updated">
                        <?php echo date('M j, Y', strtotime($questionnaire->updated_at)); ?>
                        <br><small><?php echo date('g:i A', strtotime($questionnaire->updated_at)); ?></small>
                    </td>
                    <td class="column-actions">
                        <div class="row-actions">
                            <span class="edit">
                                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=builder&id=' . $questionnaire->id); ?>">
                                    <?php _e('Edit', 'formxr'); ?>
                                </a> |
                            </span>
                            <span class="view">
                                <a href="<?php echo admin_url('admin.php?page=formxr-submissions&questionnaire_id=' . $questionnaire->id); ?>">
                                    <?php _e('Submissions', 'formxr'); ?>
                                </a> |
                            </span>
                            <span class="duplicate">
                                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=duplicate&id=' . $questionnaire->id); ?>">
                                    <?php _e('Duplicate', 'formxr'); ?>
                                </a> |
                            </span>
                            <span class="trash">
                                <a href="javascript:void(0)" onclick="deleteQuestionnaire(<?php echo $questionnaire->id; ?>)" class="submitdelete">
                                    <?php _e('Delete', 'formxr'); ?>
                                </a>
                            </span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
    
    <?php else: ?>
    <div class="no-questionnaires">
        <div class="empty-state">
            <span class="dashicons dashicons-feedback"></span>
            <h2><?php _e('No questionnaires found', 'formxr'); ?></h2>
            <p><?php _e('Create your first questionnaire to start collecting responses.', 'formxr'); ?></p>
            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="button button-primary button-large">
                <?php _e('Create New Questionnaire', 'formxr'); ?>
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    </div> <!-- End formxr-container -->
</div> <!-- End wrap -->

<script>
function deleteQuestionnaire(id) {
    if (confirm(formxr_admin_ajax.strings.confirm_delete)) {
        jQuery.post(formxr_admin_ajax.ajax_url, {
            action: 'formxr_delete_questionnaire',
            questionnaire_id: id,
            nonce: formxr_admin_ajax.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.data || formxr_admin_ajax.strings.error);
            }
        });
    }
}
</script>
