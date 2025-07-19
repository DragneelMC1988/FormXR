<?php
/**
 * Admin New Questionnaire Template
 * Complete rewrite with consistent header/footer structure
 */
if (!defined('ABSPATH')) {
    exit;
}

// Include header
include_once FORMXR_PLUGIN_DIR . 'templates/admin-header.php';

// Handle form submission
if (isset($_POST['save_questionnaire']) && wp_verify_nonce($_POST['formxr_questionnaire_nonce'], 'formxr_save_questionnaire')) {
    global $wpdb;
    
    $title = sanitize_text_field($_POST['title']);
    $description = sanitize_textarea_field($_POST['description']);
    $price = floatval($_POST['price']);
    $status = sanitize_text_field($_POST['status']);
    $questions = isset($_POST['questions']) ? $_POST['questions'] : array();
    
    // Insert questionnaire
    $result = $wpdb->insert(
        $wpdb->prefix . 'formxr_questionnaires',
        array(
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'status' => $status,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ),
        array('%s', '%s', '%f', '%s', '%s', '%s')
    );
    
    if ($result) {
        $questionnaire_id = $wpdb->insert_id;
        
        // Insert questions
        if (!empty($questions)) {
            foreach ($questions as $index => $question) {
                if (!empty($question['question'])) {
                    $wpdb->insert(
                        $wpdb->prefix . 'formxr_questions',
                        array(
                            'questionnaire_id' => $questionnaire_id,
                            'question' => sanitize_text_field($question['question']),
                            'type' => sanitize_text_field($question['type']),
                            'options' => isset($question['options']) ? sanitize_textarea_field($question['options']) : '',
                            'required' => isset($question['required']) ? 1 : 0,
                            'order_num' => $index + 1
                        ),
                        array('%d', '%s', '%s', '%s', '%d', '%d')
                    );
                }
            }
        }
        
        echo '<div class="formxr-alert formxr-alert-success">';
        echo '<span class="formxr-alert-icon">✅</span>';
        echo sprintf(__('Questionnaire "%s" created successfully! ', 'formxr'), esc_html($title));
        echo '<strong>' . sprintf(__('Shortcode: [formxr_form id="%d"]', 'formxr'), $questionnaire_id) . '</strong>';
        echo '</div>';
        
        $show_success = true;
        $new_questionnaire_id = $questionnaire_id;
    } else {
        echo '<div class="formxr-alert formxr-alert-error">';
        echo '<span class="formxr-alert-icon">❌</span>';
        echo __('Error creating questionnaire. Please try again.', 'formxr');
        echo '</div>';
    }
}
?>

<div class="formxr-admin-wrap" x-data="questionnaireBuilder()" x-cloak>
    <!-- Page Header -->
    <div class="formxr-page-header">
        <div class="formxr-page-header-content">
            <h1 class="formxr-page-title">
                <span class="formxr-page-icon">➕</span>
                <?php _e('Create New Questionnaire', 'formxr'); ?>
            </h1>
            <p class="formxr-page-subtitle">
                <?php _e('Build a new questionnaire with custom questions and settings', 'formxr'); ?>
            </p>
        </div>
        <div class="formxr-page-actions">
            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="formxr-btn formxr-btn-secondary">
                <span class="formxr-btn-icon">←</span>
                <?php _e('Back to Questionnaires', 'formxr'); ?>
            </a>
        </div>
    </div>

    <?php if (isset($show_success) && $show_success) : ?>
        <!-- Success Actions -->
        <div class="formxr-section">
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <div class="formxr-success-actions">
                        <h3><?php _e('What would you like to do next?', 'formxr'); ?></h3>
                        <div class="formxr-button-group">
                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $new_questionnaire_id); ?>" class="formxr-btn formxr-btn-primary">
                                <span class="formxr-btn-icon">✏️</span>
                                <?php _e('Edit Questionnaire', 'formxr'); ?>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" class="formxr-btn formxr-btn-secondary">
                                <span class="formxr-btn-icon">➕</span>
                                <?php _e('Create Another', 'formxr'); ?>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="formxr-btn formxr-btn-secondary">
                                <span class="formxr-btn-icon">📝</span>
                                <?php _e('View All Questionnaires', 'formxr'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form method="post" action="" class="formxr-questionnaire-form">
        <?php wp_nonce_field('formxr_save_questionnaire', 'formxr_questionnaire_nonce'); ?>
        
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
                                   x-model="questionnaire.title"
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
                                      x-model="questionnaire.description"
                                      placeholder="<?php _e('Optional description for your questionnaire...', 'formxr'); ?>"></textarea>
                            <p class="formxr-form-help"><?php _e('Provide additional context or instructions for users.', 'formxr'); ?></p>
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
                                       x-model="questionnaire.price"
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
                            <select id="status" name="status" class="formxr-form-control" x-model="questionnaire.status">
                                <option value="active"><?php _e('Active', 'formxr'); ?></option>
                                <option value="inactive"><?php _e('Inactive', 'formxr'); ?></option>
                                <option value="draft"><?php _e('Draft', 'formxr'); ?></option>
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
                <template x-for="(question, index) in questionnaire.questions" :key="index">
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
                                        x-show="index < questionnaire.questions.length - 1"
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
                <div x-show="questionnaire.questions.length === 0" class="formxr-empty-state">
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
                <button type="submit" name="save_questionnaire" class="formxr-btn formxr-btn-primary formxr-btn-large">
                    <span class="formxr-btn-icon">💾</span>
                    <?php _e('Create Questionnaire', 'formxr'); ?>
                </button>
                
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="formxr-btn formxr-btn-secondary formxr-btn-large">
                    <span class="formxr-btn-icon">✖️</span>
                    <?php _e('Cancel', 'formxr'); ?>
                </a>
            </div>
        </div>
    </form>
</div>

<script>
function questionnaireBuilder() {
    return {
        questionnaire: {
            title: '',
            description: '',
            price: 0,
            status: 'active',
            questions: []
        },
        
        addQuestion() {
            this.questionnaire.questions.push({
                question: '',
                type: 'text',
                options: '',
                required: false
            });
        },
        
        removeQuestion(index) {
            if (confirm('<?php _e('Are you sure you want to remove this question?', 'formxr'); ?>')) {
                this.questionnaire.questions.splice(index, 1);
            }
        },
        
        moveQuestionUp(index) {
            if (index > 0) {
                const question = this.questionnaire.questions.splice(index, 1)[0];
                this.questionnaire.questions.splice(index - 1, 0, question);
            }
        },
        
        moveQuestionDown(index) {
            if (index < this.questionnaire.questions.length - 1) {
                const question = this.questionnaire.questions.splice(index, 1)[0];
                this.questionnaire.questions.splice(index + 1, 0, question);
            }
        }
    }
}
</script>

<?php
// Include footer
include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
?>
