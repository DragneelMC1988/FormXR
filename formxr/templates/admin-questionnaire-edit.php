<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get questionnaire ID if editing
$questionnaire_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$questionnaire = null;

if ($questionnaire_id) {
    global $wpdb;
    $questionnaires_table = $wpdb->prefix . 'formxr_questionnaires';
    $questionnaire = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $questionnaires_table WHERE id = %d", 
        $questionnaire_id
    ));
}

if (!$questionnaire) {
    wp_die(__('Questionnaire not found', 'formxr'));
}
?>

<div class="wrap formxr-admin">
    <div class="formxr-page-header">
        <div class="formxr-page-title">
            <div>
                <h1>
                    <span class="formxr-icon">✏️</span>
                    <?php _e('Edit Questionnaire', 'formxr'); ?>: <?php echo esc_html($questionnaire->title); ?>
                </h1>
                <p class="formxr-subtitle"><?php _e('Edit questionnaire details', 'formxr'); ?></p>
            </div>
            <div class="formxr-header-actions">
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="btn-formxr btn-outline">
                    <?php _e('← Back to List', 'formxr'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=builder&id=' . $questionnaire_id); ?>" class="btn-formxr">
                    <?php _e('Open Builder', 'formxr'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="formxr-container">
        <div class="formxr-card">
            <div class="formxr-card-header">
                <h2><?php _e('Questionnaire Details', 'formxr'); ?></h2>
            </div>
            <div class="formxr-card-body">
                <form method="post" action="">
                    <?php wp_nonce_field('formxr_edit_questionnaire', 'formxr_nonce'); ?>
                    
                    <div class="formxr-form-group">
                        <label for="title" class="formxr-label"><?php _e('Title', 'formxr'); ?></label>
                        <input type="text" id="title" name="title" value="<?php echo esc_attr($questionnaire->title); ?>" class="formxr-input" required>
                    </div>
                    
                    <div class="formxr-form-group">
                        <label for="description" class="formxr-label"><?php _e('Description', 'formxr'); ?></label>
                        <textarea id="description" name="description" class="formxr-textarea" rows="3"><?php echo esc_textarea($questionnaire->description); ?></textarea>
                    </div>
                    
                    <div class="formxr-form-group">
                        <label for="status" class="formxr-label"><?php _e('Status', 'formxr'); ?></label>
                        <select id="status" name="status" class="formxr-select">
                            <option value="active" <?php selected($questionnaire->status, 'active'); ?>><?php _e('Active', 'formxr'); ?></option>
                            <option value="inactive" <?php selected($questionnaire->status, 'inactive'); ?>><?php _e('Inactive', 'formxr'); ?></option>
                        </select>
                    </div>
                    
                    <div class="formxr-form-actions">
                        <button type="submit" name="save_questionnaire" class="formxr-btn formxr-btn-primary">
                            <?php _e('Save Changes', 'formxr'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
