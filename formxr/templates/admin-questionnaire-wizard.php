<?php
/**
 * Admin Questionnaire Creation Wizard Template
 * Multi-step questionnaire creation process
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
    
    // Email template settings
    $admin_email_template = sanitize_textarea_field($_POST['admin_email_template']);
    $user_email_template = sanitize_textarea_field($_POST['user_email_template']);
    $admin_email_subject = sanitize_text_field($_POST['admin_email_subject']);
    $user_email_subject = sanitize_text_field($_POST['user_email_subject']);
    
    // Insert questionnaire
    $result = $wpdb->insert(
        $wpdb->prefix . 'formxr_questionnaires',
        array(
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'status' => $status,
            'admin_email_template' => $admin_email_template,
            'user_email_template' => $user_email_template,
            'admin_email_subject' => $admin_email_subject,
            'user_email_subject' => $user_email_subject,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ),
        array('%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
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

<div class="formxr-admin-wrap" x-data="questionnaireWizard()" x-cloak>
    <!-- Page Header -->
    <div class="formxr-page-header">
        <div class="formxr-page-header-content">
            <h1 class="formxr-page-title">
                <span class="formxr-page-icon">🧙‍♂️</span>
                <?php _e('Questionnaire Creation Wizard', 'formxr'); ?>
            </h1>
            <p class="formxr-page-subtitle">
                <?php _e('Create a new questionnaire using our step-by-step wizard', 'formxr'); ?>
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
        <!-- Success State -->
        <div class="formxr-section">
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <div class="formxr-success-actions">
                        <h3><?php _e('🎉 Questionnaire Created Successfully!', 'formxr'); ?></h3>
                        <p><?php _e('Your questionnaire has been created and is ready to use. What would you like to do next?', 'formxr'); ?></p>
                        <div class="formxr-button-group">
                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $new_questionnaire_id); ?>" class="formxr-btn formxr-btn-primary">
                                <span class="formxr-btn-icon">✏️</span>
                                <?php _e('Edit Questionnaire', 'formxr'); ?>
                            </a>
                            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=wizard'); ?>" class="formxr-btn formxr-btn-secondary">
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
    <?php else: ?>

    <!-- Wizard Progress -->
    <div class="formxr-section">
        <div class="formxr-wizard-progress">
            <div class="formxr-progress-steps">
                <div class="formxr-progress-step" :class="{ 'active': currentStep === 1, 'completed': currentStep > 1 }">
                    <div class="formxr-step-number">1</div>
                    <div class="formxr-step-label"><?php _e('Basic Info', 'formxr'); ?></div>
                </div>
                <div class="formxr-progress-line" :class="{ 'completed': currentStep > 1 }"></div>
                <div class="formxr-progress-step" :class="{ 'active': currentStep === 2, 'completed': currentStep > 2 }">
                    <div class="formxr-step-number">2</div>
                    <div class="formxr-step-label"><?php _e('Questions', 'formxr'); ?></div>
                </div>
                <div class="formxr-progress-line" :class="{ 'completed': currentStep > 2 }"></div>
                <div class="formxr-progress-step" :class="{ 'active': currentStep === 3, 'completed': currentStep > 3 }">
                    <div class="formxr-step-number">3</div>
                    <div class="formxr-step-label"><?php _e('Email Templates', 'formxr'); ?></div>
                </div>
                <div class="formxr-progress-line" :class="{ 'completed': currentStep > 3 }"></div>
                <div class="formxr-progress-step" :class="{ 'active': currentStep === 4, 'completed': currentStep > 4 }">
                    <div class="formxr-step-number">4</div>
                    <div class="formxr-step-label"><?php _e('Conditions', 'formxr'); ?></div>
                </div>
                <div class="formxr-progress-line" :class="{ 'completed': currentStep > 4 }"></div>
                <div class="formxr-progress-step" :class="{ 'active': currentStep === 5 }">
                    <div class="formxr-step-number">5</div>
                    <div class="formxr-step-label"><?php _e('Summary', 'formxr'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="" class="formxr-questionnaire-form">
        <?php wp_nonce_field('formxr_save_questionnaire', 'formxr_questionnaire_nonce'); ?>
        
        <!-- Step 1: Basic Information -->
        <div class="formxr-section" x-show="currentStep === 1" x-transition>
            <div class="formxr-section-header">
                <h2 class="formxr-section-title">
                    <span class="formxr-step-icon">📝</span>
                    <?php _e('Step 1: Basic Information', 'formxr'); ?>
                </h2>
                <p class="formxr-section-description"><?php _e('Let\'s start with the basic details of your questionnaire.', 'formxr'); ?></p>
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
                                   placeholder="<?php _e('Enter a descriptive title for your questionnaire...', 'formxr'); ?>" 
                                   required>
                            <p class="formxr-form-help"><?php _e('This will be the main title displayed to users.', 'formxr'); ?></p>
                        </div>

                        <!-- Description -->
                        <div class="formxr-form-group formxr-form-group-full">
                            <label for="description" class="formxr-form-label">
                                <?php _e('Description', 'formxr'); ?>
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      class="formxr-form-control" 
                                      rows="4"
                                      x-model="questionnaire.description"
                                      placeholder="<?php _e('Provide a brief description of what this questionnaire is about...', 'formxr'); ?>"></textarea>
                            <p class="formxr-form-help"><?php _e('Help users understand the purpose of your questionnaire.', 'formxr'); ?></p>
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
                                <option value="draft"><?php _e('Draft (Hidden)', 'formxr'); ?></option>
                                <option value="active"><?php _e('Active (Published)', 'formxr'); ?></option>
                                <option value="inactive"><?php _e('Inactive (Disabled)', 'formxr'); ?></option>
                            </select>
                            <p class="formxr-form-help"><?php _e('You can always change this later.', 'formxr'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Questions -->
        <div class="formxr-section" x-show="currentStep === 2" x-transition>
            <div class="formxr-section-header">
                <h2 class="formxr-section-title">
                    <span class="formxr-step-icon">❓</span>
                    <?php _e('Step 2: Add Questions', 'formxr'); ?>
                </h2>
                <p class="formxr-section-description"><?php _e('Build your questionnaire by adding questions. You can always modify these later.', 'formxr'); ?></p>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-header">
                    <h3 class="formxr-widget-title"><?php _e('Questions', 'formxr'); ?></h3>
                    <button type="button" @click="addQuestion()" class="formxr-btn formxr-btn-primary formxr-btn-sm">
                        <span class="formxr-btn-icon">➕</span>
                        <?php _e('Add Question', 'formxr'); ?>
                    </button>
                </div>
                
                <div class="formxr-widget-content">
                    <div class="formxr-questions-list">
                        <template x-for="(question, index) in questionnaire.questions" :key="index">
                            <div class="formxr-question-item">
                                <div class="formxr-question-header">
                                    <div class="formxr-question-number" x-text="'Question ' + (index + 1)"></div>
                                    <div class="formxr-question-actions">
                                        <button type="button" @click="moveQuestionUp(index)" :disabled="index === 0" class="formxr-btn formxr-btn-xs formxr-btn-ghost">↑</button>
                                        <button type="button" @click="moveQuestionDown(index)" :disabled="index === questionnaire.questions.length - 1" class="formxr-btn formxr-btn-xs formxr-btn-ghost">↓</button>
                                        <button type="button" @click="removeQuestion(index)" class="formxr-btn formxr-btn-xs formxr-btn-danger">✗</button>
                                    </div>
                                </div>
                                
                                <div class="formxr-question-content">
                                    <div class="formxr-form-grid">
                                        <div class="formxr-form-group formxr-form-group-full">
                                            <label class="formxr-form-label">Question Text</label>
                                            <input type="text" 
                                                   :name="'questions[' + index + '][question]'" 
                                                   class="formxr-form-control" 
                                                   x-model="question.question"
                                                   placeholder="Enter your question here...">
                                        </div>
                                        
                                        <div class="formxr-form-group">
                                            <label class="formxr-form-label">Question Type</label>
                                            <select :name="'questions[' + index + '][type]'" class="formxr-form-control" x-model="question.type">
                                                <option value="text">Text Input</option>
                                                <option value="textarea">Long Text</option>
                                                <option value="email">Email</option>
                                                <option value="number">Number</option>
                                                <option value="radio">Multiple Choice</option>
                                                <option value="checkbox">Checkboxes</option>
                                                <option value="select">Dropdown</option>
                                                <option value="date">Date</option>
                                            </select>
                                        </div>
                                        
                                        <div class="formxr-form-group">
                                            <div class="formxr-form-check">
                                                <input type="checkbox" 
                                                       :name="'questions[' + index + '][required]'" 
                                                       :id="'required_' + index"
                                                       class="formxr-form-check-input" 
                                                       x-model="question.required"
                                                       value="1">
                                                <label :for="'required_' + index" class="formxr-form-check-label">Required</label>
                                            </div>
                                        </div>
                                        
                                        <div class="formxr-form-group formxr-form-group-full" x-show="['radio', 'checkbox', 'select'].includes(question.type)">
                                            <label class="formxr-form-label">Options (one per line)</label>
                                            <textarea :name="'questions[' + index + '][options]'" 
                                                      class="formxr-form-control" 
                                                      rows="3"
                                                      x-model="question.options"
                                                      placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div x-show="questionnaire.questions.length === 0" class="formxr-empty-state">
                            <div class="formxr-empty-icon">❓</div>
                            <h4><?php _e('No Questions Added Yet', 'formxr'); ?></h4>
                            <p><?php _e('Add your first question to start building your questionnaire.', 'formxr'); ?></p>
                            <button type="button" @click="addQuestion()" class="formxr-btn formxr-btn-primary">
                                <span class="formxr-btn-icon">➕</span>
                                <?php _e('Add First Question', 'formxr'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Email Templates -->
        <div class="formxr-section" x-show="currentStep === 3" x-transition>
            <div class="formxr-section-header">
                <h2 class="formxr-section-title">
                    <span class="formxr-step-icon">📧</span>
                    <?php _e('Step 3: Email Templates', 'formxr'); ?>
                </h2>
                <p class="formxr-section-description"><?php _e('Customize the email templates that will be sent to admins and users.', 'formxr'); ?></p>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <div class="formxr-form-grid">
                        <!-- Admin Email Template -->
                        <div class="formxr-form-group formxr-form-group-full">
                            <label for="admin_email_subject" class="formxr-form-label">
                                <?php _e('Admin Email Subject', 'formxr'); ?>
                            </label>
                            <input type="text" 
                                   id="admin_email_subject" 
                                   name="admin_email_subject" 
                                   class="formxr-form-control" 
                                   x-model="questionnaire.adminEmailSubject"
                                   placeholder="<?php _e('New questionnaire submission', 'formxr'); ?>">
                            <p class="formxr-form-help"><?php _e('Subject line for emails sent to administrators.', 'formxr'); ?></p>
                        </div>

                        <div class="formxr-form-group formxr-form-group-full">
                            <label for="admin_email_template" class="formxr-form-label">
                                <?php _e('Admin Email Template', 'formxr'); ?>
                            </label>
                            <textarea id="admin_email_template" 
                                      name="admin_email_template" 
                                      class="formxr-form-control" 
                                      rows="8"
                                      x-model="questionnaire.adminEmailTemplate"
                                      placeholder="<?php _e('Admin email template...', 'formxr'); ?>"></textarea>
                            <p class="formxr-form-help"><?php _e('Use {{questionnaire_title}}, {{user_email}}, {{submission_data}} placeholders.', 'formxr'); ?></p>
                        </div>

                        <!-- User Email Template -->
                        <div class="formxr-form-group formxr-form-group-full">
                            <label for="user_email_subject" class="formxr-form-label">
                                <?php _e('User Email Subject', 'formxr'); ?>
                            </label>
                            <input type="text" 
                                   id="user_email_subject" 
                                   name="user_email_subject" 
                                   class="formxr-form-control" 
                                   x-model="questionnaire.userEmailSubject"
                                   placeholder="<?php _e('Thank you for your submission', 'formxr'); ?>">
                            <p class="formxr-form-help"><?php _e('Subject line for confirmation emails sent to users.', 'formxr'); ?></p>
                        </div>

                        <div class="formxr-form-group formxr-form-group-full">
                            <label for="user_email_template" class="formxr-form-label">
                                <?php _e('User Confirmation Email Template', 'formxr'); ?>
                            </label>
                            <textarea id="user_email_template" 
                                      name="user_email_template" 
                                      class="formxr-form-control" 
                                      rows="8"
                                      x-model="questionnaire.userEmailTemplate"
                                      placeholder="<?php _e('User confirmation email template...', 'formxr'); ?>"></textarea>
                            <p class="formxr-form-help"><?php _e('Use {{questionnaire_title}}, {{user_name}}, {{submission_data}} placeholders.', 'formxr'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Conditions -->
        <div class="formxr-section" x-show="currentStep === 4" x-transition>
            <div class="formxr-section-header">
                <h2 class="formxr-section-title">
                    <span class="formxr-step-icon">⚙️</span>
                    <?php _e('Step 4: Conditions & Settings', 'formxr'); ?>
                </h2>
                <p class="formxr-section-description"><?php _e('Set up conditional logic and advanced settings for your questionnaire.', 'formxr'); ?></p>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <div class="formxr-info-box">
                        <div class="formxr-info-icon">💡</div>
                        <div class="formxr-info-content">
                            <h4><?php _e('Coming Soon!', 'formxr'); ?></h4>
                            <p><?php _e('Conditional logic and advanced settings will be available in a future update. For now, you can skip this step.', 'formxr'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5: Summary -->
        <div class="formxr-section" x-show="currentStep === 5" x-transition>
            <div class="formxr-section-header">
                <h2 class="formxr-section-title">
                    <span class="formxr-step-icon">📋</span>
                    <?php _e('Step 5: Review & Create', 'formxr'); ?>
                </h2>
                <p class="formxr-section-description"><?php _e('Review your questionnaire details and create it.', 'formxr'); ?></p>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <div class="formxr-summary-grid">
                        <!-- Basic Info Summary -->
                        <div class="formxr-summary-section">
                            <h4><?php _e('Basic Information', 'formxr'); ?></h4>
                            <div class="formxr-summary-item">
                                <strong><?php _e('Title:', 'formxr'); ?></strong>
                                <span x-text="questionnaire.title || 'Not set'"></span>
                            </div>
                            <div class="formxr-summary-item">
                                <strong><?php _e('Description:', 'formxr'); ?></strong>
                                <span x-text="questionnaire.description || 'None'"></span>
                            </div>
                            <div class="formxr-summary-item">
                                <strong><?php _e('Price:', 'formxr'); ?></strong>
                                <span x-text="'$' + (questionnaire.price || '0.00')"></span>
                            </div>
                            <div class="formxr-summary-item">
                                <strong><?php _e('Status:', 'formxr'); ?></strong>
                                <span x-text="questionnaire.status"></span>
                            </div>
                        </div>

                        <!-- Questions Summary -->
                        <div class="formxr-summary-section">
                            <h4><?php _e('Questions', 'formxr'); ?></h4>
                            <div class="formxr-summary-item">
                                <strong><?php _e('Total Questions:', 'formxr'); ?></strong>
                                <span x-text="questionnaire.questions.length"></span>
                            </div>
                            <div class="formxr-summary-questions">
                                <template x-for="(question, index) in questionnaire.questions" :key="index">
                                    <div class="formxr-summary-question">
                                        <span x-text="(index + 1) + '. ' + (question.question || 'Untitled Question')"></span>
                                        <span class="formxr-question-type" x-text="'(' + question.type + ')'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Shortcode Preview -->
                        <div class="formxr-summary-section formxr-summary-full">
                            <h4><?php _e('Shortcode Preview', 'formxr'); ?></h4>
                            <div class="formxr-shortcode-preview">
                                <code>[formxr_form id="<?php _e('Will be generated after creation', 'formxr'); ?>"]</code>
                            </div>
                            <p class="formxr-form-help"><?php _e('Use this shortcode to display your questionnaire on any page or post.', 'formxr'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="formxr-section">
            <div class="formxr-wizard-navigation">
                <button type="button" @click="previousStep()" x-show="currentStep > 1" class="formxr-btn formxr-btn-secondary">
                    <span class="formxr-btn-icon">←</span>
                    <?php _e('Previous', 'formxr'); ?>
                </button>
                
                <div class="formxr-wizard-nav-center">
                    <span x-text="'Step ' + currentStep + ' of 5'"></span>
                </div>
                
                <button type="button" @click="nextStep()" x-show="currentStep < 5" class="formxr-btn formxr-btn-primary">
                    <?php _e('Next', 'formxr'); ?>
                    <span class="formxr-btn-icon">→</span>
                </button>
                
                <button type="submit" name="save_questionnaire" x-show="currentStep === 5" class="formxr-btn formxr-btn-primary formxr-btn-large">
                    <span class="formxr-btn-icon">🚀</span>
                    <?php _e('Create Questionnaire', 'formxr'); ?>
                </button>
            </div>
        </div>
    </form>

    <?php endif; ?>
</div>

<script>
function questionnaireWizard() {
    return {
        currentStep: 1,
        questionnaire: {
            title: '',
            description: '',
            price: 0,
            status: 'draft',
            questions: [],
            adminEmailSubject: 'New questionnaire submission',
            adminEmailTemplate: 'Hello!\n\nA new questionnaire submission has been received:\n\nQuestionnaire: {{questionnaire_title}}\nUser Email: {{user_email}}\n\nSubmission Details:\n{{submission_data}}\n\nBest regards,\nYour Website',
            userEmailSubject: 'Thank you for your submission',
            userEmailTemplate: 'Dear {{user_name}},\n\nThank you for completing our questionnaire "{{questionnaire_title}}".\n\nWe have received your submission and will process it shortly.\n\nBest regards,\nOur Team'
        },
        
        nextStep() {
            if (this.currentStep < 5) {
                if (this.validateCurrentStep()) {
                    this.currentStep++;
                }
            }
        },
        
        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        
        validateCurrentStep() {
            if (this.currentStep === 1) {
                if (!this.questionnaire.title.trim()) {
                    alert('<?php _e('Please enter a questionnaire title.', 'formxr'); ?>');
                    return false;
                }
            }
            return true;
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

<style>
/* Wizard-specific styles */
.formxr-wizard-progress {
    margin-bottom: 2rem;
}

.formxr-progress-steps {
    display: flex;
    align-items: center;
    justify-content: center;
    max-width: 800px;
    margin: 0 auto;
}

.formxr-progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

.formxr-step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--formxr-light);
    border: 2px solid var(--formxr-text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: var(--formxr-text-muted);
    transition: var(--formxr-transition);
}

.formxr-step-label {
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: var(--formxr-text-muted);
    text-align: center;
}

.formxr-progress-step.active .formxr-step-number {
    background: var(--formxr-primary);
    border-color: var(--formxr-primary);
    color: white;
}

.formxr-progress-step.active .formxr-step-label {
    color: var(--formxr-primary);
    font-weight: 600;
}

.formxr-progress-step.completed .formxr-step-number {
    background: var(--formxr-success);
    border-color: var(--formxr-success);
    color: white;
}

.formxr-progress-step.completed .formxr-step-label {
    color: var(--formxr-success);
}

.formxr-progress-line {
    flex: 1;
    height: 2px;
    background: var(--formxr-text-muted);
    margin: 0 1rem;
    position: relative;
    z-index: 1;
}

.formxr-progress-line.completed {
    background: var(--formxr-success);
}

.formxr-wizard-navigation {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 2rem 0;
}

.formxr-wizard-nav-center {
    font-weight: 600;
    color: var(--formxr-text-muted);
}

.formxr-step-icon {
    font-size: 1.25rem;
    margin-right: 0.5rem;
}

.formxr-summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.formxr-summary-section {
    padding: 1.5rem;
    background: var(--formxr-light);
    border-radius: var(--formxr-border-radius);
}

.formxr-summary-full {
    grid-column: 1 / -1;
}

.formxr-summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #eee;
}

.formxr-summary-item:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.formxr-summary-questions {
    margin-top: 1rem;
}

.formxr-summary-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.formxr-question-type {
    font-size: 0.75rem;
    color: var(--formxr-text-muted);
    text-transform: uppercase;
}

.formxr-shortcode-preview {
    background: var(--formxr-dark);
    color: white;
    padding: 1rem;
    border-radius: var(--formxr-border-radius);
    margin-bottom: 1rem;
}

.formxr-shortcode-preview code {
    color: #7dd3fc;
    font-family: 'Monaco', 'Consolas', monospace;
}

.formxr-info-box {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    background: #fef3c7;
    border: 1px solid #f59e0b;
    border-radius: var(--formxr-border-radius);
}

.formxr-info-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.formxr-info-content h4 {
    margin: 0 0 0.5rem 0;
    color: #92400e;
}

.formxr-info-content p {
    margin: 0;
    color: #92400e;
}

@media (max-width: 768px) {
    .formxr-progress-steps {
        flex-direction: column;
        gap: 1rem;
    }
    
    .formxr-progress-line {
        display: none;
    }
    
    .formxr-summary-grid {
        grid-template-columns: 1fr;
    }
    
    .formxr-wizard-navigation {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

<?php
// Include footer
include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
?>
