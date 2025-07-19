<?php
/**
 * Admin Edit Questionnaire Template
 * Complete rewrite with consistent header/footer structure
 */
if (!defined('ABSPATH')) {
    exit;
}

// Include header
include_once FORMXR_PLUGIN_DIR . 'templates/admin-header.php';

global $wpdb;

// Get questionnaire ID
$questionnaire_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$questionnaire_id) {
    echo '<div class="formxr-alert formxr-alert-error">';
    echo '<span class="formxr-alert-icon">❌</span>';
    echo __('Invalid questionnaire ID.', 'formxr');
    echo '</div>';
    include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
    return;
}

// Get questionnaire data
$questionnaire = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}formxr_questionnaires 
    WHERE id = %d
", $questionnaire_id));

if (!$questionnaire) {
    echo '<div class="formxr-alert formxr-alert-error">';
    echo '<span class="formxr-alert-icon">❌</span>';
    echo __('Questionnaire not found.', 'formxr');
    echo '</div>';
    include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
    return;
}

// Get questions through steps
$questions = $wpdb->get_results($wpdb->prepare("
    SELECT q.*, s.step_number, s.title as step_title 
    FROM {$wpdb->prefix}formxr_questions q
    JOIN {$wpdb->prefix}formxr_steps s ON q.step_id = s.id
    WHERE s.questionnaire_id = %d 
    ORDER BY s.step_order ASC, q.question_order ASC
", $questionnaire_id));

// Handle form submission
if (isset($_POST['update_questionnaire']) && wp_verify_nonce($_POST['formxr_questionnaire_nonce'], 'formxr_update_questionnaire')) {
    $title = sanitize_text_field($_POST['title']);
    $description = sanitize_textarea_field($_POST['description']);
    $base_price = floatval($_POST['price']); // Keep using 'price' field for backward compatibility
    $pricing_enabled = isset($_POST['pricing_enabled']) ? 1 : 0;
    $show_price_frontend = isset($_POST['show_price_frontend']) ? 1 : 0;
    $status = sanitize_text_field($_POST['status']);
    $questions_data = isset($_POST['questions']) ? $_POST['questions'] : array();
    
    // Update questionnaire
    $result = $wpdb->update(
        $wpdb->prefix . 'formxr_questionnaires',
        array(
            'title' => $title,
            'description' => $description,
            'base_price' => $base_price,
            'pricing_enabled' => $pricing_enabled,
            'show_price_frontend' => $show_price_frontend,
            'status' => $status,
            'updated_at' => current_time('mysql')
        ),
        array('id' => $questionnaire_id),
        array('%s', '%s', '%f', '%d', '%d', '%s', '%s'),
        array('%d')
    );
    
    if ($result !== false) {
        // Delete existing questions through steps
        $steps = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}formxr_steps WHERE questionnaire_id = %d",
            $questionnaire_id
        ));
        foreach ($steps as $step_id) {
            $wpdb->delete($wpdb->prefix . 'formxr_questions', array('step_id' => $step_id), array('%d'));
        }
        
        // For the old edit system, we'll create a default step and add questions to it
        $default_step_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}formxr_steps WHERE questionnaire_id = %d LIMIT 1",
            $questionnaire_id
        ));
        
        if (!$default_step_id) {
            // Create a default step
            $wpdb->insert(
                $wpdb->prefix . 'formxr_steps',
                array(
                    'questionnaire_id' => $questionnaire_id,
                    'step_number' => 1,
                    'title' => 'Questions',
                    'description' => '',
                    'can_skip' => 0,
                    'step_order' => 0
                ),
                array('%d', '%d', '%s', '%s', '%d', '%d')
            );
            $default_step_id = $wpdb->insert_id;
        }
        
        // Insert updated questions
        if (!empty($questions_data)) {
            foreach ($questions_data as $index => $question) {
                if (!empty($question['question'])) {
                    $wpdb->insert(
                        $wpdb->prefix . 'formxr_questions',
                        array(
                            'step_id' => $default_step_id,
                            'question_text' => sanitize_text_field($question['question']),
                            'question_type' => sanitize_text_field($question['type']),
                            'options' => isset($question['options']) ? sanitize_textarea_field($question['options']) : '',
                            'is_required' => isset($question['required']) ? 1 : 0,
                            'question_order' => $index + 1
                        ),
                        array('%d', '%s', '%s', '%s', '%d', '%d')
                    );
                }
            }
        }
        
        echo '<div class="formxr-alert formxr-alert-success">';
        echo '<span class="formxr-alert-icon">✅</span>';
        echo __('Questionnaire updated successfully!', 'formxr');
        echo '</div>';
        
        // Refresh data
        $questionnaire = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}formxr_questionnaires 
            WHERE id = %d
        ", $questionnaire_id));
        
        $questions = $wpdb->get_results($wpdb->prepare("
            SELECT 
                q.question_text as question,
                q.question_type as type, 
                q.options,
                q.is_required as required,
                s.step_number, 
                s.title as step_title 
            FROM {$wpdb->prefix}formxr_questions q
            JOIN {$wpdb->prefix}formxr_steps s ON q.step_id = s.id
            WHERE s.questionnaire_id = %d 
            ORDER BY s.step_order ASC, q.question_order ASC
        ", $questionnaire_id));
        
    } else {
        echo '<div class="formxr-alert formxr-alert-error">';
        echo '<span class="formxr-alert-icon">❌</span>';
        echo __('Error updating questionnaire. Please try again.', 'formxr');
        echo '</div>';
    }
}

// Get submission count
$submission_count = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) FROM {$wpdb->prefix}formxr_submissions 
    WHERE questionnaire_id = %d
", $questionnaire_id));
?>

<div class="formxr-admin-wrap" x-data="questionnaireEditor(<?php echo htmlspecialchars(json_encode($questions), ENT_QUOTES, 'UTF-8'); ?>)" x-cloak>
    <!-- Page Header -->
    <div class="formxr-page-header">
        <div class="formxr-page-header-content">
            <h1 class="formxr-page-title">
                <span class="formxr-page-icon">✏️</span>
                <?php _e('Edit Questionnaire', 'formxr'); ?>
            </h1>
            <p class="formxr-page-subtitle">
                <?php printf(__('Editing: %s', 'formxr'), esc_html($questionnaire->title)); ?>
            </p>
        </div>
        <div class="formxr-page-actions">
            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="formxr-btn formxr-btn-secondary">
                <span class="formxr-btn-icon">←</span>
                <?php _e('Back to Questionnaires', 'formxr'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=formxr-submissions&questionnaire_id=' . $questionnaire_id); ?>" class="formxr-btn formxr-btn-info">
                <span class="formxr-btn-icon">📊</span>
                <?php printf(__('View Submissions (%d)', 'formxr'), $submission_count); ?>
            </a>
        </div>
    </div>

    <!-- Questionnaire Info Section -->
    <div class="formxr-section">
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <span class="formxr-widget-icon">ℹ️</span>
                    <?php _e('Questionnaire Information', 'formxr'); ?>
                </h3>
            </div>
            <div class="formxr-widget-content">
                <div class="formxr-grid formxr-grid-3">
                    <div class="formxr-info-item">
                        <strong><?php _e('Created:', 'formxr'); ?></strong>
                        <?php echo date('F j, Y \a\t g:i A', strtotime($questionnaire->created_at)); ?>
                    </div>
                    <div class="formxr-info-item">
                        <strong><?php _e('Last Updated:', 'formxr'); ?></strong>
                        <?php echo date('F j, Y \a\t g:i A', strtotime($questionnaire->updated_at)); ?>
                    </div>
                    <div class="formxr-info-item">
                        <strong><?php _e('Shortcode:', 'formxr'); ?></strong>
                        <code>[formxr_form id="<?php echo $questionnaire_id; ?>"]</code>
                        <button type="button" onclick="copyToClipboard('[formxr_form id=&quot;<?php echo $questionnaire_id; ?>&quot;]')" class="formxr-btn formxr-btn-sm formxr-btn-secondary">
                            <?php _e('Copy', 'formxr'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="" class="formxr-questionnaire-form">
        <?php wp_nonce_field('formxr_update_questionnaire', 'formxr_questionnaire_nonce'); ?>
        
        <!-- Basic Information Section -->
        <div class="formxr-section">
            <div class="formxr-section-header">
                <h2 class="formxr-section-title"><?php _e('Basic Information', 'formxr'); ?></h2>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <div class="formxr-form-grid">
                        <!-- Title -->
                        <div class="formxr-form-group formxr-form-group-full">
                            <label for="title" class="formxr-form-label">
                                <?php _e('Questionnaire Title', 'formxr'); ?> <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   class="formxr-form-control" 
                                   value="<?php echo esc_attr($questionnaire->title); ?>"
                                   placeholder="<?php _e('Enter questionnaire title...', 'formxr'); ?>" 
                                   required>
                            <p class="formxr-form-help"><?php _e('Give your questionnaire a clear, descriptive title.', 'formxr'); ?></p>
                        </div>

                        <!-- Description -->
                        <div class="formxr-form-group formxr-form-group-full">
                            <label for="description" class="formxr-form-label">
                                <?php _e('Description', 'formxr'); ?>
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      class="formxr-form-control" 
                                      rows="3"
                                      placeholder="<?php _e('Optional description for your questionnaire...', 'formxr'); ?>"><?php echo esc_textarea($questionnaire->description); ?></textarea>
                            <p class="formxr-form-help"><?php _e('Provide additional context or instructions for users.', 'formxr'); ?></p>
                        </div>

                        <!-- Enable Pricing -->
                        <div class="formxr-form-group formxr-form-group-full">
                            <div class="formxr-form-check">
                                <input type="checkbox" 
                                       id="pricing_enabled" 
                                       name="pricing_enabled"
                                       class="formxr-form-check-input" 
                                       value="1"
                                       <?php checked(!empty($questionnaire->pricing_enabled)); ?>>
                                <label for="pricing_enabled" class="formxr-form-check-label">
                                    <strong><?php _e('Enable Pricing', 'formxr'); ?></strong>
                                </label>
                            </div>
                            <p class="formxr-form-help">
                                <?php _e('Check this if you want to calculate prices based on user responses.', 'formxr'); ?>
                            </p>
                        </div>

                        <!-- Show Price in Frontend -->
                        <div class="formxr-form-group formxr-form-group-full" id="show_price_frontend_group" style="<?php echo empty($questionnaire->pricing_enabled) ? 'display: none;' : ''; ?>">
                            <div class="formxr-form-check">
                                <input type="checkbox" 
                                       id="show_price_frontend" 
                                       name="show_price_frontend"
                                       class="formxr-form-check-input" 
                                       value="1"
                                       <?php checked(!empty($questionnaire->show_price_frontend)); ?>>
                                <label for="show_price_frontend" class="formxr-form-check-label">
                                    <strong><?php _e('Show Price in Frontend', 'formxr'); ?></strong>
                                </label>
                            </div>
                            <p class="formxr-form-help">
                                <?php _e('When enabled, price calculations will be visible to users. When disabled, prices are calculated and stored for analytics/submissions but hidden from users.', 'formxr'); ?>
                            </p>
                        </div>

                        <!-- Price -->
                        <div class="formxr-form-group">
                            <label for="price" class="formxr-form-label">
                                <?php _e('Price', 'formxr'); ?>
                            </label>
                            <div class="formxr-input-group">
                                <span class="formxr-input-prefix">$</span>
                                <input type="number" 
                                       id="price" 
                                       name="price" 
                                       class="formxr-form-control" 
                                       value="<?php echo esc_attr($questionnaire->base_price ?? 0); ?>"
                                       min="0" 
                                       step="0.01" 
                                       placeholder="0.00">
                            </div>
                            <p class="formxr-form-help"><?php _e('Set to 0 for a free questionnaire.', 'formxr'); ?></p>
                        </div>

                        <!-- Status -->
                        <div class="formxr-form-group">
                            <label for="status" class="formxr-form-label">
                                <?php _e('Status', 'formxr'); ?>
                            </label>
                            <select id="status" name="status" class="formxr-form-control">
                                <option value="active" <?php selected($questionnaire->status, 'active'); ?>><?php _e('Active', 'formxr'); ?></option>
                                <option value="inactive" <?php selected($questionnaire->status, 'inactive'); ?>><?php _e('Inactive', 'formxr'); ?></option>
                                <option value="draft" <?php selected($questionnaire->status, 'draft'); ?>><?php _e('Draft', 'formxr'); ?></option>
                            </select>
                            <p class="formxr-form-help"><?php _e('Only active questionnaires can receive submissions.', 'formxr'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="formxr-section">
            <div class="formxr-section-header">
                <h2 class="formxr-section-title"><?php _e('Questions', 'formxr'); ?></h2>
                <button type="button" 
                        @click="addQuestion()" 
                        class="formxr-btn formxr-btn-primary">
                    <span class="formxr-btn-icon">➕</span>
                    <?php _e('Add Question', 'formxr'); ?>
                </button>
            </div>
            
            <div class="formxr-questions-container">
                <template x-for="(question, index) in questions" :key="index">
                    <div class="formxr-widget formxr-question-item">
                        <div class="formxr-widget-header">
                            <h3 class="formxr-widget-title">
                                <span class="formxr-widget-icon">❓</span>
                                <?php _e('Question', 'formxr'); ?> <span x-text="index + 1"></span>
                            </h3>
                            <div class="formxr-widget-actions">
                                <button type="button" 
                                        @click="moveQuestionUp(index)" 
                                        x-show="index > 0"
                                        class="formxr-btn formxr-btn-sm formxr-btn-secondary" 
                                        title="<?php _e('Move Up', 'formxr'); ?>">
                                    ↑
                                </button>
                                <button type="button" 
                                        @click="moveQuestionDown(index)" 
                                        x-show="index < questions.length - 1"
                                        class="formxr-btn formxr-btn-sm formxr-btn-secondary" 
                                        title="<?php _e('Move Down', 'formxr'); ?>">
                                    ↓
                                </button>
                                <button type="button" 
                                        @click="removeQuestion(index)" 
                                        class="formxr-btn formxr-btn-sm formxr-btn-error" 
                                        title="<?php _e('Remove Question', 'formxr'); ?>">
                                    🗑️
                                </button>
                            </div>
                        </div>
                        
                        <div class="formxr-widget-content">
                            <div class="formxr-form-grid">
                                <!-- Question Text -->
                                <div class="formxr-form-group formxr-form-group-full">
                                    <label class="formxr-form-label">
                                        <?php _e('Question Text', 'formxr'); ?> <span class="required">*</span>
                                    </label>
                                    <input type="text" 
                                           :name="'questions[' + index + '][question]'" 
                                           class="formxr-form-control" 
                                           x-model="question.question"
                                           placeholder="<?php _e('Enter your question...', 'formxr'); ?>" 
                                           required>
                                </div>

                                <!-- Question Type -->
                                <div class="formxr-form-group">
                                    <label class="formxr-form-label">
                                        <?php _e('Question Type', 'formxr'); ?>
                                    </label>
                                    <select :name="'questions[' + index + '][type]'" 
                                            class="formxr-form-control" 
                                            x-model="question.type">
                                        <option value="text"><?php _e('Text Input', 'formxr'); ?></option>
                                        <option value="textarea"><?php _e('Textarea', 'formxr'); ?></option>
                                        <option value="email"><?php _e('Email', 'formxr'); ?></option>
                                        <option value="number"><?php _e('Number', 'formxr'); ?></option>
                                        <option value="select"><?php _e('Dropdown', 'formxr'); ?></option>
                                        <option value="radio"><?php _e('Radio Buttons', 'formxr'); ?></option>
                                        <option value="checkbox"><?php _e('Checkboxes', 'formxr'); ?></option>
                                        <option value="date"><?php _e('Date', 'formxr'); ?></option>
                                        <option value="file"><?php _e('File Upload', 'formxr'); ?></option>
                                    </select>
                                </div>

                                <!-- Required -->
                                <div class="formxr-form-group">
                                    <div class="formxr-form-check">
                                        <input type="checkbox" 
                                               :id="'required_' + index" 
                                               :name="'questions[' + index + '][required]'" 
                                               class="formxr-form-check-input" 
                                               x-model="question.required">
                                        <label :for="'required_' + index" class="formxr-form-check-label">
                                            <?php _e('Required Question', 'formxr'); ?>
                                        </label>
                                    </div>
                                </div>

                                <!-- Options (for select, radio, checkbox) -->
                                <div class="formxr-form-group formxr-form-group-full" 
                                     x-show="['select', 'radio', 'checkbox'].includes(question.type)">
                                    <label class="formxr-form-label">
                                        <?php _e('Options', 'formxr'); ?>
                                    </label>
                                    <textarea :name="'questions[' + index + '][options]'" 
                                              class="formxr-form-control" 
                                              rows="3"
                                              x-model="question.options"
                                              placeholder="<?php _e('Enter one option per line...', 'formxr'); ?>"></textarea>
                                    <p class="formxr-form-help"><?php _e('Enter each option on a new line.', 'formxr'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="questions.length === 0" class="formxr-empty-state">
                    <div class="formxr-empty-icon">❓</div>
                    <h4><?php _e('No Questions Added', 'formxr'); ?></h4>
                    <p><?php _e('Add your first question to get started building your questionnaire.', 'formxr'); ?></p>
                    <button type="button" @click="addQuestion()" class="formxr-btn formxr-btn-primary">
                        <span class="formxr-btn-icon">➕</span>
                        <?php _e('Add First Question', 'formxr'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Save Section -->
        <div class="formxr-section">
            <div class="formxr-form-actions">
                <button type="submit" name="update_questionnaire" class="formxr-btn formxr-btn-primary formxr-btn-large">
                    <span class="formxr-btn-icon">💾</span>
                    <?php _e('Update Questionnaire', 'formxr'); ?>
                </button>
                
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="formxr-btn formxr-btn-secondary formxr-btn-large">
                    <span class="formxr-btn-icon">✖️</span>
                    <?php _e('Cancel', 'formxr'); ?>
                </a>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=formxr-questionnaires&action=delete&id=' . $questionnaire_id), 'questionnaire_action'); ?>" 
                   class="formxr-btn formxr-btn-error formxr-btn-large" 
                   onclick="return confirm('<?php _e('Are you sure you want to delete this questionnaire? This action cannot be undone.', 'formxr'); ?>')">
                    <span class="formxr-btn-icon">🗑️</span>
                    <?php _e('Delete Questionnaire', 'formxr'); ?>
                </a>
            </div>
        </div>
    </form>
</div>

<script>
function questionnaireEditor(existingQuestions) {
    return {
        questions: existingQuestions.map(q => ({
            question: q.question,
            type: q.type,
            options: q.options || '',
            required: q.required == 1
        })),
        
        addQuestion() {
            this.questions.push({
                question: '',
                type: 'text',
                options: '',
                required: false
            });
        },
        
        removeQuestion(index) {
            if (confirm('<?php _e('Are you sure you want to remove this question?', 'formxr'); ?>')) {
                this.questions.splice(index, 1);
            }
        },
        
        moveQuestionUp(index) {
            if (index > 0) {
                const question = this.questions.splice(index, 1)[0];
                this.questions.splice(index - 1, 0, question);
            }
        },
        
        moveQuestionDown(index) {
            if (index < this.questions.length - 1) {
                const question = this.questions.splice(index, 1)[0];
                this.questions.splice(index + 1, 0, question);
            }
        }
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('<?php _e('Shortcode copied to clipboard!', 'formxr'); ?>');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}

// Handle pricing enabled checkbox to show/hide show_price_frontend option
document.addEventListener('DOMContentLoaded', function() {
    const pricingEnabled = document.getElementById('pricing_enabled');
    const showPriceFrontendGroup = document.getElementById('show_price_frontend_group');
    
    if (pricingEnabled && showPriceFrontendGroup) {
        pricingEnabled.addEventListener('change', function() {
            if (this.checked) {
                showPriceFrontendGroup.style.display = 'block';
            } else {
                showPriceFrontendGroup.style.display = 'none';
                // Uncheck show_price_frontend when pricing is disabled
                const showPriceFrontend = document.getElementById('show_price_frontend');
                if (showPriceFrontend) {
                    showPriceFrontend.checked = false;
                }
            }
        });
    }
});
</script>

<?php
// Include footer
include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
?>
