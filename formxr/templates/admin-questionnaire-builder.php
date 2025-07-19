<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Get questionnaire ID if editing
$questionnaire_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$questionnaire = null;
$steps = array();
$questions = array();

if ($questionnaire_id) {
    global $wpdb;
    $questionnaires_table = $wpdb->prefix . 'formxr_questionnaires';
    $steps_table = $wpdb->prefix . 'formxr_steps';
    $questions_table = $wpdb->prefix . 'formxr_questions';
    
    $questionnaire = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $questionnaires_table WHERE id = %d", 
        $questionnaire_id
    ));
    
    if ($questionnaire) {
        $steps = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $steps_table WHERE questionnaire_id = %d ORDER BY step_order", 
            $questionnaire_id
        ));
        
        $questions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $questions_table WHERE questionnaire_id = %d ORDER BY question_order", 
            $questionnaire_id
        ));
    }
}

$is_editing = $questionnaire !== null;
$page_title = $is_editing ? __('Edit Questionnaire', 'formxr') : __('Create New Questionnaire', 'formxr');
?>

<div class="wrap formxr-admin" x-data="questionnaireBuilder(<?php echo esc_attr(json_encode(array(
    'questionnaire' => $questionnaire,
    'steps' => $steps,
    'questions' => $questions,
    'isEditing' => $is_editing
))); ?>)">
    <div class="formxr-page-header">
        <div class="formxr-page-title">
            <h1>
                <span class="formxr-icon"><?php echo $is_editing ? '✏️' : '➕'; ?></span>
                <?php echo esc_html($page_title); ?>
            </h1>
            <p class="formxr-subtitle">
                <?php echo $is_editing ? 
                    __('Modify your questionnaire structure and settings', 'formxr') : 
                    __('Build your interactive questionnaire with custom pricing', 'formxr'); ?>
            </p>
            <div class="formxr-header-actions">
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="btn-formxr btn-outline">
                    <?php _e('← Back to List', 'formxr'); ?>
                </a>
                <button type="button" class="btn-formxr" @click="saveQuestionnaire()" :disabled="saving">
                    <span x-show="!saving"><?php _e('Save Questionnaire', 'formxr'); ?></span>
                    <span x-show="saving"><?php _e('Saving...', 'formxr'); ?></span>
                </button>
            </div>
        </div>
    </div>

    <div class="formxr-container">
        <div class="formxr-builder-grid">
            <!-- Main Content -->
            <div class="formxr-builder-main">
                <!-- Basic Information -->
                <div class="formxr-card">
                    <div class="formxr-card-header">
                        <h2><?php _e('Basic Information', 'formxr'); ?></h2>
                        <p><?php _e('Configure the basic details of your questionnaire', 'formxr'); ?></p>
                    </div>
                    <div class="formxr-card-body">
                        <div class="formxr-form-group">
                            <label for="questionnaire_title" class="formxr-label">
                                <?php _e('Title', 'formxr'); ?>
                                <span class="formxr-required">*</span>
                            </label>
                            <input type="text" 
                                   id="questionnaire_title" 
                                   x-model="questionnaire.title"
                                   class="formxr-input"
                                   placeholder="<?php esc_attr_e('Enter questionnaire title', 'formxr'); ?>"
                                   required>
                        </div>

                        <div class="formxr-form-group">
                            <label for="questionnaire_description" class="formxr-label">
                                <?php _e('Description', 'formxr'); ?>
                            </label>
                            <textarea id="questionnaire_description" 
                                      x-model="questionnaire.description"
                                      class="formxr-textarea"
                                      rows="3"
                                      placeholder="<?php esc_attr_e('Brief description of this questionnaire', 'formxr'); ?>"></textarea>
                        </div>

                        <div class="formxr-form-row">
                            <div class="formxr-form-group">
                                <label for="questionnaire_status" class="formxr-label">
                                    <?php _e('Status', 'formxr'); ?>
                                </label>
                                <select id="questionnaire_status" x-model="questionnaire.status" class="formxr-select">
                                    <option value="active"><?php _e('Active', 'formxr'); ?></option>
                                    <option value="inactive"><?php _e('Inactive', 'formxr'); ?></option>
                                </select>
                            </div>

                            <div class="formxr-form-group">
                                <label class="formxr-label">
                                    <input type="checkbox" x-model="questionnaire.show_progress" class="formxr-checkbox">
                                    <?php _e('Show Progress Bar', 'formxr'); ?>
                                </label>
                                <div class="formxr-help-text"><?php _e('Display step progress to users', 'formxr'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Steps & Questions -->
                <div class="formxr-card">
                    <div class="formxr-card-header">
                        <h2><?php _e('Steps & Questions', 'formxr'); ?></h2>
                        <p><?php _e('Build your questionnaire structure', 'formxr'); ?></p>
                    </div>
                    <div class="formxr-card-body">
                        <!-- Steps List -->
                        <div class="formxr-steps-container">
                            <template x-for="(step, stepIndex) in questionnaire.steps" :key="step.id || stepIndex">
                                <div class="formxr-step-item" :class="{ 'active': activeStep === stepIndex }">
                                    <div class="formxr-step-header" @click="toggleStep(stepIndex)">
                                        <div class="formxr-step-info">
                                            <span class="formxr-step-number" x-text="stepIndex + 1"></span>
                                            <span class="formxr-step-title" x-text="step.title || 'Untitled Step'"></span>
                                            <span class="formxr-step-questions" x-text="'(' + step.questions.length + ' questions)'"></span>
                                        </div>
                                        <div class="formxr-step-actions">
                                            <button type="button" @click.stop="removeStep(stepIndex)" class="formxr-btn-icon formxr-btn-danger">
                                                🗑️
                                            </button>
                                            <span class="formxr-step-toggle">
                                                <span x-show="activeStep !== stepIndex">▼</span>
                                                <span x-show="activeStep === stepIndex">▲</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div x-show="activeStep === stepIndex" x-transition class="formxr-step-content">
                                        <!-- Step Configuration -->
                                        <div class="formxr-step-config">
                                            <div class="formxr-form-row">
                                                <div class="formxr-form-group">
                                                    <label class="formxr-label"><?php _e('Step Title', 'formxr'); ?></label>
                                                    <input type="text" 
                                                           x-model="step.title"
                                                           class="formxr-input"
                                                           placeholder="<?php esc_attr_e('Step title', 'formxr'); ?>">
                                                </div>
                                                <div class="formxr-form-group">
                                                    <label class="formxr-label"><?php _e('Step Description', 'formxr'); ?></label>
                                                    <input type="text" 
                                                           x-model="step.description"
                                                           class="formxr-input"
                                                           placeholder="<?php esc_attr_e('Optional description', 'formxr'); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Questions in this step -->
                                        <div class="formxr-questions-container">
                                            <h4><?php _e('Questions', 'formxr'); ?></h4>
                                            
                                            <template x-for="(question, questionIndex) in step.questions" :key="question.id || questionIndex">
                                                <div class="formxr-question-item">
                                                    <div class="formxr-question-header">
                                                        <div class="formxr-question-info">
                                                            <span class="formxr-question-type" x-text="question.type"></span>
                                                            <span class="formxr-question-text" x-text="question.question_text || 'Untitled Question'"></span>
                                                        </div>
                                                        <div class="formxr-question-actions">
                                                            <button type="button" @click="editQuestion(stepIndex, questionIndex)" class="formxr-btn-icon">
                                                                ✏️
                                                            </button>
                                                            <button type="button" @click="removeQuestion(stepIndex, questionIndex)" class="formxr-btn-icon formxr-btn-danger">
                                                                🗑️
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <button type="button" @click="addQuestion(stepIndex)" class="formxr-btn formxr-btn-outline formxr-btn-sm">
                                                ➕ <?php _e('Add Question', 'formxr'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="addStep()" class="formxr-btn formxr-btn-outline">
                                ➕ <?php _e('Add Step', 'formxr'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="formxr-builder-sidebar">
                <!-- Pricing Configuration -->
                <div class="formxr-card">
                    <div class="formxr-card-header">
                        <h3><?php _e('Pricing Configuration', 'formxr'); ?></h3>
                    </div>
                    <div class="formxr-card-body">
                        <div class="formxr-form-group">
                            <label for="base_price" class="formxr-label">
                                <?php _e('Base Price', 'formxr'); ?>
                            </label>
                            <input type="number" 
                                   id="base_price" 
                                   x-model="questionnaire.base_price"
                                   class="formxr-input"
                                   step="0.01"
                                   min="0"
                                   placeholder="0.00">
                            <div class="formxr-help-text"><?php _e('Starting price before question impacts', 'formxr'); ?></div>
                        </div>

                        <div class="formxr-form-group">
                            <label for="currency" class="formxr-label">
                                <?php _e('Currency', 'formxr'); ?>
                            </label>
                            <select id="currency" x-model="questionnaire.currency" class="formxr-select">
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="CAD">CAD ($)</option>
                                <option value="AUD">AUD ($)</option>
                            </select>
                        </div>

                        <div class="formxr-form-group">
                            <label for="price_type" class="formxr-label">
                                <?php _e('Price Type', 'formxr'); ?>
                            </label>
                            <select id="price_type" x-model="questionnaire.price_type" class="formxr-select">
                                <option value="quote"><?php _e('Quote/Estimate', 'formxr'); ?></option>
                                <option value="fixed"><?php _e('Fixed Price', 'formxr'); ?></option>
                                <option value="range"><?php _e('Price Range', 'formxr'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Email Configuration -->
                <div class="formxr-card">
                    <div class="formxr-card-header">
                        <h3><?php _e('Email Notifications', 'formxr'); ?></h3>
                    </div>
                    <div class="formxr-card-body">
                        <div class="formxr-form-group">
                            <label for="notification_emails" class="formxr-label">
                                <?php _e('Notification Recipients', 'formxr'); ?>
                            </label>
                            <textarea id="notification_emails" 
                                      x-model="questionnaire.notification_emails"
                                      class="formxr-textarea"
                                      rows="3"
                                      placeholder="<?php esc_attr_e('email1@example.com, email2@example.com', 'formxr'); ?>"></textarea>
                            <div class="formxr-help-text"><?php _e('Comma-separated email addresses', 'formxr'); ?></div>
                        </div>

                        <div class="formxr-form-group">
                            <label for="email_subject" class="formxr-label">
                                <?php _e('Email Subject', 'formxr'); ?>
                            </label>
                            <input type="text" 
                                   id="email_subject" 
                                   x-model="questionnaire.email_subject"
                                   class="formxr-input"
                                   placeholder="<?php esc_attr_e('New submission for {questionnaire_title}', 'formxr'); ?>">
                        </div>

                        <div class="formxr-form-group">
                            <label for="email_template" class="formxr-label">
                                <?php _e('Email Template', 'formxr'); ?>
                            </label>
                            <textarea id="email_template" 
                                      x-model="questionnaire.email_template"
                                      class="formxr-textarea"
                                      rows="6"
                                      placeholder="<?php esc_attr_e('New submission received...', 'formxr'); ?>"></textarea>
                            <div class="formxr-help-text">
                                <?php _e('Use placeholders:', 'formxr'); ?> 
                                {user_email}, {calculated_price}, {submission_data}
                            </div>
                        </div>

                        <div class="formxr-form-group">
                            <label class="formxr-label">
                                <input type="checkbox" x-model="questionnaire.send_user_copy" class="formxr-checkbox">
                                <?php _e('Send copy to user', 'formxr'); ?>
                            </label>
                            <div class="formxr-help-text"><?php _e('Send a copy of the submission to the user', 'formxr'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="formxr-card">
                    <div class="formxr-card-body">
                        <div class="formxr-action-group">
                            <button type="button" @click="previewQuestionnaire()" class="formxr-btn formxr-btn-outline formxr-btn-block">
                                👁️ <?php _e('Preview', 'formxr'); ?>
                            </button>
                            <?php if ($is_editing): ?>
                            <div class="formxr-shortcode-info">
                                <label class="formxr-label"><?php _e('Shortcode', 'formxr'); ?></label>
                                <input type="text" 
                                       value="[formxr_form id=&quot;<?php echo $questionnaire_id; ?>&quot;]"
                                       class="formxr-input formxr-shortcode"
                                       readonly
                                       onclick="this.select()">
                                <div class="formxr-help-text"><?php _e('Copy this shortcode to display the form', 'formxr'); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Question Modal -->
    <div x-show="showQuestionModal" 
         x-transition 
         class="formxr-modal-overlay" 
         @click="showQuestionModal = false">
        <div class="formxr-modal formxr-modal-lg" @click.stop>
            <div class="formxr-modal-header">
                <h3 x-text="editingQuestion.id ? '<?php _e('Edit Question', 'formxr'); ?>' : '<?php _e('Add Question', 'formxr'); ?>'"></h3>
                <button type="button" @click="showQuestionModal = false" class="formxr-modal-close">×</button>
            </div>
            <div class="formxr-modal-body">
                <form @submit.prevent="saveQuestion()">
                    <div class="formxr-form-group">
                        <label class="formxr-label">
                            <?php _e('Question Text', 'formxr'); ?>
                            <span class="formxr-required">*</span>
                        </label>
                        <input type="text" 
                               x-model="editingQuestion.question_text"
                               class="formxr-input"
                               required>
                    </div>

                    <div class="formxr-form-group">
                        <label class="formxr-label"><?php _e('Question Type', 'formxr'); ?></label>
                        <select x-model="editingQuestion.type" class="formxr-select">
                            <option value="text"><?php _e('Text Input', 'formxr'); ?></option>
                            <option value="textarea"><?php _e('Text Area', 'formxr'); ?></option>
                            <option value="select"><?php _e('Dropdown', 'formxr'); ?></option>
                            <option value="radio"><?php _e('Radio Buttons', 'formxr'); ?></option>
                            <option value="checkbox"><?php _e('Checkboxes', 'formxr'); ?></option>
                            <option value="number"><?php _e('Number Input', 'formxr'); ?></option>
                            <option value="range"><?php _e('Range Slider', 'formxr'); ?></option>
                        </select>
                    </div>

                    <div x-show="['select', 'radio', 'checkbox'].includes(editingQuestion.type)" class="formxr-form-group">
                        <label class="formxr-label"><?php _e('Options', 'formxr'); ?></label>
                        <textarea x-model="editingQuestion.options" 
                                  class="formxr-textarea"
                                  rows="4"
                                  placeholder="<?php esc_attr_e('Option 1\nOption 2\nOption 3', 'formxr'); ?>"></textarea>
                        <div class="formxr-help-text"><?php _e('One option per line', 'formxr'); ?></div>
                    </div>

                    <div class="formxr-form-group">
                        <label class="formxr-label"><?php _e('Price Impact', 'formxr'); ?></label>
                        <select x-model="editingQuestion.price_impact_type" class="formxr-select">
                            <option value="none"><?php _e('No Impact', 'formxr'); ?></option>
                            <option value="fixed"><?php _e('Fixed Amount', 'formxr'); ?></option>
                            <option value="percentage"><?php _e('Percentage', 'formxr'); ?></option>
                        </select>
                    </div>

                    <div x-show="editingQuestion.price_impact_type !== 'none'" class="formxr-form-group">
                        <label class="formxr-label"><?php _e('Price Impact Value', 'formxr'); ?></label>
                        <input type="number" 
                               x-model="editingQuestion.price_impact_value"
                               class="formxr-input"
                               step="0.01">
                        <div class="formxr-help-text"><?php _e('Positive to add, negative to subtract', 'formxr'); ?></div>
                    </div>

                    <div class="formxr-form-group">
                        <label class="formxr-label">
                            <input type="checkbox" x-model="editingQuestion.required" class="formxr-checkbox">
                            <?php _e('Required Field', 'formxr'); ?>
                        </label>
                    </div>

                    <div class="formxr-modal-actions">
                        <button type="button" @click="showQuestionModal = false" class="formxr-btn formxr-btn-secondary">
                            <?php _e('Cancel', 'formxr'); ?>
                        </button>
                        <button type="submit" class="formxr-btn formxr-btn-primary">
                            <?php _e('Save Question', 'formxr'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function questionnaireBuilder(initialData) {
    return {
        questionnaire: initialData.questionnaire || {
            id: null,
            title: '',
            description: '',
            status: 'active',
            show_progress: true,
            base_price: 0,
            currency: 'USD',
            price_type: 'quote',
            notification_emails: '',
            email_subject: 'New submission for {questionnaire_title}',
            email_template: 'Hello,\n\nA new submission has been received for {questionnaire_title}.\n\nUser Email: {user_email}\nCalculated Price: {calculated_price}\n\nSubmission Details:\n{submission_data}\n\nSubmitted on: {submitted_date}',
            send_user_copy: false,
            steps: []
        },
        saving: false,
        activeStep: 0,
        showQuestionModal: false,
        editingQuestion: {},
        editingStepIndex: null,
        editingQuestionIndex: null,

        init() {
            // Initialize steps if we have existing data
            if (initialData.steps && initialData.steps.length > 0) {
                this.questionnaire.steps = initialData.steps.map(step => ({
                    ...step,
                    questions: initialData.questions.filter(q => q.step_id == step.id) || []
                }));
            }

            // Ensure we have at least one step
            if (this.questionnaire.steps.length === 0) {
                this.addStep();
            }
        },

        addStep() {
            this.questionnaire.steps.push({
                id: null,
                title: `Step ${this.questionnaire.steps.length + 1}`,
                description: '',
                questions: []
            });
            this.activeStep = this.questionnaire.steps.length - 1;
        },

        removeStep(stepIndex) {
            if (this.questionnaire.steps.length > 1) {
                this.questionnaire.steps.splice(stepIndex, 1);
                if (this.activeStep >= this.questionnaire.steps.length) {
                    this.activeStep = this.questionnaire.steps.length - 1;
                }
            }
        },

        toggleStep(stepIndex) {
            this.activeStep = this.activeStep === stepIndex ? -1 : stepIndex;
        },

        addQuestion(stepIndex) {
            this.editingStepIndex = stepIndex;
            this.editingQuestionIndex = null;
            this.editingQuestion = {
                id: null,
                question_text: '',
                type: 'text',
                options: '',
                price_impact_type: 'none',
                price_impact_value: 0,
                required: false
            };
            this.showQuestionModal = true;
        },

        editQuestion(stepIndex, questionIndex) {
            this.editingStepIndex = stepIndex;
            this.editingQuestionIndex = questionIndex;
            this.editingQuestion = { ...this.questionnaire.steps[stepIndex].questions[questionIndex] };
            this.showQuestionModal = true;
        },

        removeQuestion(stepIndex, questionIndex) {
            this.questionnaire.steps[stepIndex].questions.splice(questionIndex, 1);
        },

        saveQuestion() {
            if (this.editingQuestionIndex !== null) {
                // Update existing question
                this.questionnaire.steps[this.editingStepIndex].questions[this.editingQuestionIndex] = { ...this.editingQuestion };
            } else {
                // Add new question
                this.questionnaire.steps[this.editingStepIndex].questions.push({ ...this.editingQuestion });
            }
            this.showQuestionModal = false;
        },

        async saveQuestionnaire() {
            if (this.saving) return;
            
            this.saving = true;
            
            try {
                const formData = new FormData();
                formData.append('action', 'formxr_save_questionnaire');
                formData.append('nonce', formxrAdmin.nonce);
                formData.append('questionnaire', JSON.stringify(this.questionnaire));
                
                const response = await fetch(formxrAdmin.ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Redirect to questionnaires list or update current page
                    if (!this.questionnaire.id) {
                        window.location.href = formxrAdmin.adminUrl + 'admin.php?page=formxr-questionnaires&action=edit&id=' + result.data.id;
                    } else {
                        // Show success message
                        alert('Questionnaire saved successfully!');
                    }
                } else {
                    alert('Error: ' + (result.data || 'Unknown error'));
                }
                
            } catch (error) {
                alert('Network error: ' + error.message);
            } finally {
                this.saving = false;
            }
        },

        previewQuestionnaire() {
            // Implementation for preview functionality
            alert('Preview functionality will be implemented');
        }
    }
}
</script>
