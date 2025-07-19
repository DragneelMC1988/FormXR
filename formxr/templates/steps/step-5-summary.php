<?php
/**
 * Step 5: Summary and Shortcode
 * Final review and questionnaire completion
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="formxr-step-content">
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title">
                <?php _e('Step 5 - Summary & Shortcode', 'formxr'); ?>
            </h2>
            <p class="formxr-section-description">
                <?php _e('Review your questionnaire configuration and get the shortcode to display it on your website.', 'formxr'); ?>
            </p>
        </div>
        
        <!-- Configuration Summary -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Questionnaire Summary', 'formxr'); ?>
                </h3>
            </div>
            <div class="formxr-widget-content">
                <div class="formxr-summary-grid">
                    <!-- Basic Information -->
                    <div class="formxr-summary-section">
                        <h4><?php _e('Basic Information', 'formxr'); ?></h4>
                        <div class="formxr-summary-item">
                            <span class="formxr-label"><?php _e('Title:', 'formxr'); ?></span>
                            <span class="formxr-value" x-text="basicInfo.title || '<?php _e('(No title set)', 'formxr'); ?>'"></span>
                        </div>
                        <div class="formxr-summary-item">
                            <span class="formxr-label"><?php _e('Description:', 'formxr'); ?></span>
                            <span class="formxr-value" x-text="basicInfo.description || '<?php _e('(No description)', 'formxr'); ?>'"></span>
                        </div>
                        <div class="formxr-summary-item">
                            <span class="formxr-label"><?php _e('Pricing:', 'formxr'); ?></span>
                            <span class="formxr-value">
                                <span class="formxr-badge" 
                                      :class="basicInfo.enablePricing ? 'formxr-badge-success' : 'formxr-badge-secondary'"
                                      x-text="basicInfo.enablePricing ? '<?php _e('Enabled', 'formxr'); ?>' : '<?php _e('Disabled', 'formxr'); ?>'"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Questions Summary -->
                    <div class="formxr-summary-section">
                        <h4><?php _e('Questions Configuration', 'formxr'); ?></h4>
                        <div class="formxr-summary-item">
                            <span class="formxr-label"><?php _e('Structure:', 'formxr'); ?></span>
                            <span class="formxr-value">
                                <span class="formxr-badge" 
                                      :class="questionsConfig.enableMultiStep ? 'formxr-badge-info' : 'formxr-badge-secondary'"
                                      x-text="questionsConfig.enableMultiStep ? '<?php _e('Multi-Step', 'formxr'); ?>' : '<?php _e('Single Page', 'formxr'); ?>'"></span>
                            </span>
                        </div>
                        <div class="formxr-summary-item" x-show="questionsConfig.enableMultiStep">
                            <span class="formxr-label"><?php _e('Number of Steps:', 'formxr'); ?></span>
                            <span class="formxr-value" x-text="questionsConfig.steps.length"></span>
                        </div>
                        <div class="formxr-summary-item">
                            <span class="formxr-label"><?php _e('Total Questions:', 'formxr'); ?></span>
                            <span class="formxr-value" x-text="getTotalQuestions()"></span>
                        </div>
                    </div>

                    <!-- Email Configuration -->
                    <div class="formxr-summary-section">
                        <h4><?php _e('Email Configuration', 'formxr'); ?></h4>
                        <div class="formxr-summary-item">
                            <span class="formxr-label"><?php _e('Notifications:', 'formxr'); ?></span>
                            <span class="formxr-value">
                                <span class="formxr-badge" 
                                      :class="emailConfig.notificationsEnabled ? 'formxr-badge-success' : 'formxr-badge-warning'"
                                      x-text="emailConfig.notificationsEnabled ? '<?php _e('Enabled', 'formxr'); ?>' : '<?php _e('Disabled', 'formxr'); ?>'"></span>
                            </span>
                        </div>
                        <div class="formxr-summary-item" x-show="emailConfig.notificationsEnabled">
                            <span class="formxr-label"><?php _e('Admin Email:', 'formxr'); ?></span>
                            <span class="formxr-value" x-text="emailConfig.adminEmail || '<?php echo esc_js(get_option('admin_email')); ?>'"></span>
                        </div>
                        <div class="formxr-summary-item" x-show="emailConfig.notificationsEnabled">
                            <span class="formxr-label"><?php _e('User Confirmation:', 'formxr'); ?></span>
                            <span class="formxr-value">
                                <span class="formxr-badge" 
                                      :class="emailConfig.userEmailEnabled ? 'formxr-badge-success' : 'formxr-badge-secondary'"
                                      x-text="emailConfig.userEmailEnabled ? '<?php _e('Enabled', 'formxr'); ?>' : '<?php _e('Disabled', 'formxr'); ?>'"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Conditions Summary -->
                    <div class="formxr-summary-section" x-show="basicInfo.enablePricing || conditionsConfig.displayConditions.length > 0">
                        <h4><?php _e('Conditions', 'formxr'); ?></h4>
                        <div class="formxr-summary-item" x-show="basicInfo.enablePricing">
                            <span class="formxr-label"><?php _e('Pricing Conditions:', 'formxr'); ?></span>
                            <span class="formxr-value" x-text="conditionsConfig.pricingConditions.length + ' <?php _e('configured', 'formxr'); ?>'"></span>
                        </div>
                        <div class="formxr-summary-item">
                            <span class="formxr-label"><?php _e('Display Conditions:', 'formxr'); ?></span>
                            <span class="formxr-value" x-text="conditionsConfig.displayConditions.length + ' <?php _e('configured', 'formxr'); ?>'"></span>
                        </div>
                        <div class="formxr-summary-item" x-show="basicInfo.enablePricing">
                            <span class="formxr-label"><?php _e('Base Price:', 'formxr'); ?></span>
                            <span class="formxr-value" x-text="'$' + (conditionsConfig.basePrice || '0.00')"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Questionnaire -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Create Questionnaire', 'formxr'); ?>
                </h3>
            </div>
            <div class="formxr-widget-content">
                <div class="formxr-creation-actions">
                    <button type="button" 
                            @click="createQuestionnaire()" 
                            :disabled="!isValid() || creating"
                            class="formxr-btn formxr-btn-primary formxr-btn-large">
                        <span x-show="!creating">
                            <span class="formxr-btn-icon">✨</span>
                            <?php _e('Create Questionnaire', 'formxr'); ?>
                        </span>
                        <span x-show="creating">
                            <span class="formxr-btn-icon">⏳</span>
                            <?php _e('Creating...', 'formxr'); ?>
                        </span>
                    </button>
                </div>
                
                <!-- Validation Errors -->
                <div x-show="!isValid()" class="formxr-validation-errors">
                    <h4><?php _e('Please fix the following issues:', 'formxr'); ?></h4>
                    <ul>
                        <li x-show="!basicInfo.title.trim()"><?php _e('Questionnaire title is required', 'formxr'); ?></li>
                        <li x-show="getTotalQuestions() === 0"><?php _e('At least one question is required', 'formxr'); ?></li>
                        <li x-show="emailConfig.notificationsEnabled && !emailConfig.adminEmail.trim()"><?php _e('Admin email is required when notifications are enabled', 'formxr'); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Success State -->
        <div class="formxr-widget" x-show="questionnaire.id" x-transition>
            <div class="formxr-widget-content">
                <div class="formxr-success-state">
                    <div class="formxr-success-icon">🎉</div>
                    <h3><?php _e('Questionnaire Created Successfully!', 'formxr'); ?></h3>
                    <p><?php _e('Your questionnaire has been created and is ready to use.', 'formxr'); ?></p>
                    
                    <!-- Shortcode -->
                    <div class="formxr-shortcode-section">
                        <h4><?php _e('Shortcode', 'formxr'); ?></h4>
                        <div class="formxr-shortcode-box">
                            <input type="text" 
                                   :value="'[formxr_form id=&quot;' + questionnaire.id + '&quot;]'"
                                   class="formxr-shortcode-input"
                                   readonly
                                   @click="$event.target.select()">
                            <button type="button" 
                                    @click="copyShortcode()" 
                                    class="formxr-btn formxr-btn-sm formxr-btn-secondary">
                                <?php _e('Copy', 'formxr'); ?>
                            </button>
                        </div>
                        <p class="formxr-form-help">
                            <?php _e('Copy this shortcode and paste it into any page or post where you want to display your questionnaire.', 'formxr'); ?>
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="formxr-success-actions">
                        <a :href="'<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id='); ?>' + questionnaire.id" 
                           class="formxr-btn formxr-btn-primary">
                            <span class="formxr-btn-icon">✏️</span>
                            <?php _e('Edit Questionnaire', 'formxr'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" 
                           class="formxr-btn formxr-btn-secondary">
                            <span class="formxr-btn-icon">📝</span>
                            <?php _e('View All Questionnaires', 'formxr'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>" 
                           class="formxr-btn formxr-btn-outline">
                            <span class="formxr-btn-icon">➕</span>
                            <?php _e('Create Another', 'formxr'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="formxr-widget" x-show="!questionnaire.id">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Preview', 'formxr'); ?>
                </h3>
            </div>
            <div class="formxr-widget-content">
                <div class="formxr-questionnaire-preview">
                    <div class="formxr-preview-header">
                        <h3 x-text="basicInfo.title || '<?php _e('Questionnaire Preview', 'formxr'); ?>'"></h3>
                        <p x-show="basicInfo.description" x-text="basicInfo.description"></p>
                        <div class="formxr-preview-badges">
                            <span x-show="questionsConfig.enableMultiStep" class="formxr-badge formxr-badge-info">
                                <?php _e('Multi-Step', 'formxr'); ?>
                            </span>
                            <span x-show="basicInfo.enablePricing" class="formxr-badge formxr-badge-success">
                                <?php _e('Pricing Enabled', 'formxr'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="formxr-preview-questions">
                        <template x-for="(step, stepIndex) in questionsConfig.steps" :key="stepIndex">
                            <div class="formxr-preview-step">
                                <h4 x-show="questionsConfig.enableMultiStep" x-text="step.title || 'Step ' + (stepIndex + 1)"></h4>
                                <p x-show="questionsConfig.enableMultiStep && step.description" x-text="step.description"></p>
                                
                                <template x-for="(question, questionIndex) in step.questions" :key="questionIndex">
                                    <div class="formxr-preview-question">
                                        <label class="formxr-preview-label">
                                            <span x-text="question.label || 'Question ' + (questionIndex + 1)"></span>
                                            <span x-show="question.required" class="formxr-required">*</span>
                                        </label>
                                        
                                        <!-- Different field types preview -->
                                        <div class="formxr-preview-field">
                                            <input x-show="['text', 'email'].includes(question.type)" 
                                                   type="text" 
                                                   class="formxr-preview-input"
                                                   disabled
                                                   :placeholder="question.type === 'email' ? 'email@example.com' : 'Enter your answer...'">
                                            
                                            <textarea x-show="question.type === 'textarea'" 
                                                      class="formxr-preview-textarea"
                                                      disabled
                                                      placeholder="Enter your answer..."></textarea>
                                            
                                            <select x-show="question.type === 'select'" 
                                                    class="formxr-preview-select"
                                                    disabled>
                                                <option>Select an option...</option>
                                            </select>
                                            
                                            <div x-show="['radio', 'checkbox'].includes(question.type)" 
                                                 class="formxr-preview-options">
                                                <template x-for="(option, optionIndex) in (question.options || 'Option 1\nOption 2').split('\n')" :key="optionIndex">
                                                    <label class="formxr-preview-option">
                                                        <input :type="question.type" 
                                                               :name="'preview_' + stepIndex + '_' + questionIndex"
                                                               disabled>
                                                        <span x-text="option.trim()"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.formxr-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.formxr-summary-section h4 {
    margin: 0 0 1rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e0e0e0;
    color: #495057;
}

.formxr-summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.formxr-summary-item:last-child {
    border-bottom: none;
}

.formxr-summary-item .formxr-label {
    font-weight: 500;
    color: #6c757d;
}

.formxr-summary-item .formxr-value {
    color: #2c3e50;
}

.formxr-creation-actions {
    text-align: center;
    margin-bottom: 1rem;
}

.formxr-validation-errors {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 6px;
    padding: 1rem;
    color: #721c24;
}

.formxr-validation-errors h4 {
    margin: 0 0 0.5rem 0;
}

.formxr-validation-errors ul {
    margin: 0;
    padding-left: 1.5rem;
}

.formxr-success-state {
    text-align: center;
    padding: 2rem;
}

.formxr-success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.formxr-success-state h3 {
    margin: 0 0 0.5rem 0;
    color: #28a745;
}

.formxr-shortcode-section {
    margin: 2rem 0;
    text-align: left;
}

.formxr-shortcode-box {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.formxr-shortcode-input {
    flex: 1;
    padding: 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background: #f8f9fa;
    font-family: monospace;
    font-size: 0.875rem;
}

.formxr-success-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.formxr-questionnaire-preview {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 1.5rem;
    background: #f8f9fa;
}

.formxr-preview-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.formxr-preview-header h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
}

.formxr-preview-badges {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
}

.formxr-preview-step {
    margin-bottom: 2rem;
}

.formxr-preview-step h4 {
    margin: 0 0 0.5rem 0;
    color: #495057;
}

.formxr-preview-question {
    margin-bottom: 1.5rem;
}

.formxr-preview-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #495057;
}

.formxr-preview-input,
.formxr-preview-textarea,
.formxr-preview-select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    background: white;
}

.formxr-preview-textarea {
    resize: vertical;
    min-height: 80px;
}

.formxr-preview-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.formxr-preview-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: not-allowed;
}

.formxr-btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}
</style>

<script>
// Add these methods to the main questionnaireCreator function
const questionnaire = {
    id: null,
    created: false
};

const creating = false;

function getTotalQuestions() {
    let total = 0;
    this.questionsConfig.steps.forEach(step => {
        total += step.questions.length;
    });
    return total;
}

function isValid() {
    return this.basicInfo.title.trim() !== '' && 
           this.getTotalQuestions() > 0 &&
           (!this.emailConfig.notificationsEnabled || this.emailConfig.adminEmail.trim() !== '');
}

async function createQuestionnaire() {
    if (!this.isValid() || this.creating) return;
    
    this.creating = true;
    
    try {
        // Prepare data for submission
        const formData = new FormData();
        formData.append('action', 'formxr_create_questionnaire');
        formData.append('nonce', formxrAdmin.nonce);
        
        // Add all configuration data
        formData.append('basic_info', JSON.stringify(this.basicInfo));
        formData.append('questions_config', JSON.stringify(this.questionsConfig));
        formData.append('email_config', JSON.stringify(this.emailConfig));
        formData.append('conditions_config', JSON.stringify(this.conditionsConfig));
        
        const response = await fetch(formxrAdmin.ajaxUrl, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            this.questionnaire.id = result.data.questionnaire_id;
            this.questionnaire.created = true;
            
            // Show success message
            this.showNotification('Questionnaire created successfully!', 'success');
        } else {
            throw new Error(result.data || 'Failed to create questionnaire');
        }
    } catch (error) {
        this.showNotification('Error: ' + error.message, 'error');
    } finally {
        this.creating = false;
    }
}

function copyShortcode() {
    const shortcode = `[formxr_form id="${this.questionnaire.id}"]`;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(shortcode).then(() => {
            this.showNotification('Shortcode copied to clipboard!', 'success');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = shortcode;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        this.showNotification('Shortcode copied to clipboard!', 'success');
    }
}

function showNotification(message, type) {
    // Simple notification - could be enhanced
    alert(message);
}
</script>
