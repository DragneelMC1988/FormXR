<?php
/**
 * Admin New Questionnaire Template - 5 Step Process
 * Step 1: Basic Info
 * Step 2: Multistep or Single Step Configuration
 * Step 3: Email Templates
 * Step 4: Conditions
 * Step 5: Shortcode and Summary
 */
if (!defined('ABSPATH')) {
    exit;
}

// Get current step from URL or default to 1
$current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$current_step = max(1, min(5, $current_step)); // Ensure step is between 1-5

// Get questionnaire ID if continuing/editing
$questionnaire_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Load existing data if editing
$questionnaire_data = array();
if ($questionnaire_id) {
    global $wpdb;
    $questionnaire = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}formxr_questionnaires WHERE id = %d",
        $questionnaire_id
    ));
    if ($questionnaire) {
        $questionnaire_data = (array) $questionnaire;
    }
}

// Include header
include_once FORMXR_PLUGIN_DIR . 'templates/admin-header.php';
?>

<div class="formxr-admin-wrap" x-data="questionnaireCreator()" x-init="init()">
    <!-- Page Header -->
    <div class="formxr-page-header">
        <div class="formxr-page-header-content">
            <h1 class="formxr-page-title">
                <span class="formxr-page-icon">➕</span>
                <?php _e('Create New Questionnaire', 'formxr'); ?>
            </h1>
            <p class="formxr-page-subtitle">
                <?php printf(__('Step %d of 5 - Build your questionnaire with a structured approach', 'formxr'), $current_step); ?>
            </p>
        </div>
        <div class="formxr-page-actions">
            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="formxr-btn formxr-btn-secondary">
                <span class="formxr-btn-icon">←</span>
                <?php _e('Back to Questionnaires', 'formxr'); ?>
            </a>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="formxr-progress-container">
        <div class="formxr-progress-bar">
            <?php for ($i = 1; $i <= 5; $i++) : ?>
                <div class="formxr-progress-step <?php echo $i <= $current_step ? 'completed' : ''; ?> <?php echo $i === $current_step ? 'active' : ''; ?>">
                    <div class="formxr-step-number"><?php echo $i; ?></div>
                    <div class="formxr-step-label">
                        <?php
                        switch ($i) {
                            case 1: _e('Basic Info', 'formxr'); break;
                            case 2: _e('Questions', 'formxr'); break;
                            case 3: _e('Email Templates', 'formxr'); break;
                            case 4: _e('Conditions', 'formxr'); break;
                            case 5: _e('Summary', 'formxr'); break;
                        }
                        ?>
                    </div>
                </div>
                <?php if ($i < 5) : ?>
                    <div class="formxr-progress-line <?php echo $i < $current_step ? 'completed' : ''; ?>"></div>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>

    <div class="formxr-step-form">
        <div class="formxr-container">
            <?php
            // Include the appropriate step template
            switch ($current_step) {
                case 1:
                    include FORMXR_PLUGIN_DIR . 'templates/steps/step-1-basic.php';
                    break;
                case 2:
                    include FORMXR_PLUGIN_DIR . 'templates/steps/step-2-questions.php';
                    break;
                case 3:
                    include FORMXR_PLUGIN_DIR . 'templates/steps/step-3-email.php';
                    break;
                case 4:
                    include FORMXR_PLUGIN_DIR . 'templates/steps/step-4-conditions.php';
                    break;
                case 5:
                    include FORMXR_PLUGIN_DIR . 'templates/steps/step-5-summary.php';
                    break;
            }
            ?>
        </div>

        <!-- Navigation -->
        <div class="formxr-step-navigation">
            <?php if ($current_step > 1) : ?>
                <button type="button" @click="goToPreviousStep()" class="formxr-btn formxr-btn-secondary">
                    <span class="formxr-btn-icon">←</span>
                    <?php _e('Previous', 'formxr'); ?>
                </button>
            <?php endif; ?>
            
            <?php if ($current_step < 5) : ?>
                <button type="button" @click="goToNextStep()" class="formxr-btn formxr-btn-primary">
                    <?php _e('Next Step', 'formxr'); ?>
                    <span class="formxr-btn-icon">→</span>
                </button>
            <?php else : ?>
                <button type="button" @click="createQuestionnaire()" class="formxr-btn formxr-btn-primary" :disabled="creating">
                    <span x-show="!creating"><?php _e('Complete Questionnaire', 'formxr'); ?></span>
                    <span x-show="creating"><?php _e('Creating...', 'formxr'); ?></span>
                    <span class="formxr-btn-icon">✓</span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function questionnaireCreator() {
    return {
        currentStep: <?php echo $current_step; ?>,
        questionnaireId: <?php echo $questionnaire_id; ?>,
        creating: false,
        
        // Step 1 - Basic Info
        basicInfo: {
            title: '<?php echo esc_js($questionnaire_data['title'] ?? ''); ?>',
            description: '<?php echo esc_js($questionnaire_data['description'] ?? ''); ?>',
            enablePricing: <?php echo !empty($questionnaire_data['pricing_enabled']) ? 'true' : 'false'; ?>,
            showPriceFrontend: <?php echo !empty($questionnaire_data['show_price_frontend']) ? 'true' : 'false'; ?>
        },
        
        // Step 2 - Questions Configuration
        questionsConfig: {
            enableMultiStep: <?php echo !empty($questionnaire_data['enable_multi_step']) ? 'true' : 'false'; ?>,
            steps: [],
            currentStepIndex: 0
        },
        
        // Step 3 - Email Configuration
        emailConfig: {
            adminEmail: '<?php echo esc_js(get_option('admin_email')); ?>',
            adminSubject: 'New submission for {questionnaire_title}',
            adminTemplate: '',
            userEmailEnabled: false,
            userSubject: 'Thank you for your submission',
            userTemplate: '',
            notificationsEnabled: true,
            testEmail: '',
            testingEmail: false
        },
        
        // Step 4 - Conditions Configuration
        conditionsConfig: {
            basePrice: 100,
            minPrice: 0,
            maxPrice: 10000,
            pricingConditions: [],
            displayConditions: []
        },
        
        // Step 5 - Questionnaire object
        questionnaire: {
            id: null,
            created: false
        },
        
        // Initialize component
        init() {
            console.log('FormXR Questionnaire Creator initialized');
            console.log('Current step:', this.currentStep);
            
            // Load data from localStorage if available
            this.loadDataFromStorage();
            
            // Initialize default step if none exist
            if (this.questionsConfig.steps.length === 0) {
                this.addStep();
            }
            
            // Set default email templates
            this.insertDefaultAdminTemplate();
            this.insertDefaultUserTemplate();
            
            // Auto-save data on changes
            this.$watch('basicInfo', () => this.saveDataToStorage());
            this.$watch('questionsConfig', () => this.saveDataToStorage());
            this.$watch('emailConfig', () => this.saveDataToStorage());
            this.$watch('conditionsConfig', () => this.saveDataToStorage());
        },
        
        // Data persistence methods
        saveDataToStorage() {
            const data = {
                basicInfo: this.basicInfo,
                questionsConfig: this.questionsConfig,
                emailConfig: this.emailConfig,
                conditionsConfig: this.conditionsConfig
            };
            localStorage.setItem('formxr_questionnaire_draft', JSON.stringify(data));
        },
        
        loadDataFromStorage() {
            const savedData = localStorage.getItem('formxr_questionnaire_draft');
            if (savedData) {
                try {
                    const data = JSON.parse(savedData);
                    if (data.basicInfo) this.basicInfo = { ...this.basicInfo, ...data.basicInfo };
                    if (data.questionsConfig) this.questionsConfig = { ...this.questionsConfig, ...data.questionsConfig };
                    if (data.emailConfig) this.emailConfig = { ...this.emailConfig, ...data.emailConfig };
                    if (data.conditionsConfig) this.conditionsConfig = { ...this.conditionsConfig, ...data.conditionsConfig };
                } catch (e) {
                    console.warn('Failed to load saved data:', e);
                }
            }
        },
        
        clearStoredData() {
            localStorage.removeItem('formxr_questionnaire_draft');
        },
        
        // Navigation methods
        goToNextStep() {
            if (this.currentStep < 5) {
                if (!this.validateCurrentStep()) {
                    let message = '';
                    switch (this.currentStep) {
                        case 1:
                            message = 'Please enter a questionnaire title before proceeding.';
                            break;
                        case 2:
                            message = 'Please add at least one question with a label before proceeding.';
                            break;
                        case 3:
                            message = 'Please enter an admin email address for notifications.';
                            break;
                        default:
                            message = 'Please complete all required fields before proceeding.';
                    }
                    this.showNotification(message, 'error');
                    return;
                }
                
                const nextStep = this.currentStep + 1;
                window.location.href = `${window.location.pathname}?page=formxr-questionnaires&action=new&step=${nextStep}`;
            }
        },
        
        goToPreviousStep() {
            if (this.currentStep > 1) {
                const prevStep = this.currentStep - 1;
                window.location.href = `${window.location.pathname}?page=formxr-questionnaires&action=new&step=${prevStep}`;
            }
        },
        
        // Step management
        addStep() {
            this.questionsConfig.steps.push({
                title: `Step ${this.questionsConfig.steps.length + 1}`,
                description: '',
                questions: []
            });
        },
        
        removeStep(index) {
            if (this.questionsConfig.steps.length > 1) {
                this.questionsConfig.steps.splice(index, 1);
            }
        },
        
        // Question management
        addQuestion(stepIndex) {
            if (!this.questionsConfig.steps[stepIndex]) {
                this.addStep();
            }
            this.questionsConfig.steps[stepIndex].questions.push({
                label: '',
                type: 'text',
                required: false,
                options: '',
                price: 0,
                optionPrices: {}
            });
        },
        
        removeQuestion(stepIndex, questionIndex) {
            this.questionsConfig.steps[stepIndex].questions.splice(questionIndex, 1);
        },
        
        // Question pricing methods
        updateOptionPrices(stepIndex, questionIndex) {
            const question = this.questionsConfig.steps[stepIndex].questions[questionIndex];
            if (question.options) {
                const options = question.options.split('\n').filter(opt => opt.trim() !== '');
                const newPrices = {};
                options.forEach(option => {
                    const trimmedOption = option.trim();
                    newPrices[trimmedOption] = question.optionPrices[trimmedOption] || 0;
                });
                question.optionPrices = newPrices;
            }
        },
        
        getQuestionOptions(stepIndex, questionIndex) {
            const question = this.questionsConfig.steps[stepIndex].questions[questionIndex];
            if (!question.options) return [];
            return question.options.split('\n').filter(opt => opt.trim() !== '').map(opt => opt.trim());
        },
        
        // Multi-step toggle
        toggleMultiStep() {
            if (!this.questionsConfig.enableMultiStep) {
                // Convert to single step - merge all questions into first step
                if (this.questionsConfig.steps.length > 1) {
                    const allQuestions = [];
                    this.questionsConfig.steps.forEach(step => {
                        allQuestions.push(...step.questions);
                    });
                    this.questionsConfig.steps = [{
                        title: 'Questions',
                        description: '',
                        questions: allQuestions
                    }];
                }
            } else {
                // Ensure we have at least one step
                if (this.questionsConfig.steps.length === 0) {
                    this.addStep();
                }
            }
        },
        
        // Email template methods
        insertDefaultAdminTemplate() {
            this.emailConfig.adminTemplate = `Hello,

A new submission has been received for your questionnaire: {questionnaire_title}

Submission Details:
- User Email: {user_email}
- Submitted Date: {submitted_date}
${this.basicInfo.enablePricing ? '- Calculated Price: {calculated_price}' : ''}

Response Data:
{submission_data}

You can view all submissions in your FormXR dashboard.

Best regards,
{site_name}
{site_url}`;
        },
        
        insertDefaultUserTemplate() {
            this.emailConfig.userTemplate = `Dear Customer,

Thank you for completing our questionnaire: {questionnaire_title}

We have received your submission and will review it shortly.

Submission Summary:
- Submitted Date: {submitted_date}
${this.basicInfo.enablePricing ? '- Estimated Price: {calculated_price}' : ''}

Your Responses:
{submission_data}

If you have any questions, please don't hesitate to contact us.

Best regards,
{site_name}
{site_url}`;
        },
        
        previewEmailTemplate(type) {
            const template = type === 'admin' ? this.emailConfig.adminTemplate : this.emailConfig.userTemplate;
            const subject = type === 'admin' ? this.emailConfig.adminSubject : this.emailConfig.userSubject;
            
            // Simple preview - could be enhanced with a modal
            const preview = template
                .replace(/{questionnaire_title}/g, this.basicInfo.title || 'Sample Questionnaire')
                .replace(/{user_email}/g, 'user@example.com')
                .replace(/{submitted_date}/g, new Date().toLocaleString())
                .replace(/{calculated_price}/g, '$500.00')
                .replace(/{submission_data}/g, 'Question 1: Sample Answer\nQuestion 2: Another Answer')
                .replace(/{site_name}/g, '<?php echo esc_js(get_bloginfo('name')); ?>')
                .replace(/{site_url}/g, '<?php echo esc_js(home_url()); ?>');
            
            alert(`Subject: ${subject}\n\n${preview}`);
        },
        
        async sendTestEmail() {
            if (!this.emailConfig.testEmail) return;
            
            this.emailConfig.testingEmail = true;
            
            try {
                const formData = new FormData();
                formData.append('action', 'formxr_test_email');
                formData.append('nonce', formxrAdmin.nonce);
                formData.append('test_email', this.emailConfig.testEmail);
                
                const response = await fetch(formxrAdmin.ajaxUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Test email sent successfully!');
                } else {
                    alert('Failed to send test email: ' + (result.data || 'Unknown error'));
                }
            } catch (error) {
                alert('Error sending test email: ' + error.message);
            } finally {
                this.emailConfig.testingEmail = false;
            }
        },
        
        // Conditions methods
        addPricingCondition() {
            this.conditionsConfig.pricingConditions.push({
                question: '',
                operator: 'equals',
                value: '',
                action: 'add',
                amount: 0
            });
        },
        
        removePricingCondition(index) {
            this.conditionsConfig.pricingConditions.splice(index, 1);
        },
        
        addDisplayCondition() {
            this.conditionsConfig.displayConditions.push({
                targetQuestion: '',
                sourceQuestion: '',
                operator: 'equals',
                value: ''
            });
        },
        
        removeDisplayCondition(index) {
            this.conditionsConfig.displayConditions.splice(index, 1);
        },
        
        // Summary and creation methods
        getTotalQuestions() {
            let total = 0;
            this.questionsConfig.steps.forEach(step => {
                total += step.questions.length;
            });
            return total;
        },
        
        isValid() {
            return this.basicInfo.title.trim() !== '' && 
                   this.getTotalQuestions() > 0 &&
                   (!this.emailConfig.notificationsEnabled || this.emailConfig.adminEmail.trim() !== '');
        },
        
        async createQuestionnaire() {
            if (this.creating) return;
            
            // Validate all data
            const errors = this.getValidationErrors();
            if (errors.length > 0) {
                this.showNotification('Please fix the following issues:\n' + errors.join('\n'), 'error');
                return;
            }
            
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
                
                // Check if response is JSON by trying to read as text first
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error('JSON parse error:', jsonError);
                    console.error('Response was:', responseText);
                    throw new Error('Server returned invalid response. Check console for details.');
                }
                
                if (result.success) {
                    this.questionnaire.id = result.data.questionnaire_id;
                    this.questionnaire.created = true;
                    
                    // Clear stored draft data
                    this.clearStoredData();
                    
                    // Show success message
                    this.showNotification('Questionnaire created successfully! Redirecting...', 'success');
                    
                    // Redirect to questionnaires list after 2 seconds
                    setTimeout(() => {
                        window.location.href = result.data.view_url || '<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>';
                    }, 2000);
                } else {
                    throw new Error(result.data || 'Failed to create questionnaire');
                }
            } catch (error) {
                this.showNotification('Error: ' + error.message, 'error');
            } finally {
                this.creating = false;
            }
        },
        
        copyShortcode() {
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
        },
        
        showNotification(message, type) {
            // Simple notification - could be enhanced
            alert(message);
        },
        
        // Validation
        validateCurrentStep() {
            switch (this.currentStep) {
                case 1:
                    return this.basicInfo.title.trim() !== '';
                case 2:
                    // Ensure we have at least one step with at least one question with a label
                    return this.questionsConfig.steps.length > 0 && 
                           this.questionsConfig.steps.some(step => 
                               step.questions.length > 0 && 
                               step.questions.some(q => q.label && q.label.trim() !== '')
                           );
                case 3:
                    return !this.emailConfig.notificationsEnabled || this.emailConfig.adminEmail.trim() !== '';
                case 4:
                    return true; // Conditions are optional
                case 5:
                    return this.isValid();
                default:
                    return true;
            }
        },
        
        // Enhanced validation for final submission
        getValidationErrors() {
            const errors = [];
            
            if (!this.basicInfo.title.trim()) {
                errors.push('Questionnaire title is required');
            }
            
            if (this.getTotalQuestions() === 0) {
                errors.push('At least one question is required');
            }
            
            // Check if any questions have empty labels
            let hasEmptyQuestions = false;
            this.questionsConfig.steps.forEach(step => {
                step.questions.forEach(question => {
                    if (!question.label || question.label.trim() === '') {
                        hasEmptyQuestions = true;
                    }
                });
            });
            
            if (hasEmptyQuestions) {
                errors.push('All questions must have labels');
            }
            
            if (this.emailConfig.notificationsEnabled && !this.emailConfig.adminEmail.trim()) {
                errors.push('Admin email is required when notifications are enabled');
            }
            
            return errors;
        }
    };
}
</script>

<style>
.formxr-progress-container {
    margin: 2rem 0;
    padding: 0 2rem;
}

.formxr-progress-bar {
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
}

.formxr-step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.formxr-progress-step.active .formxr-step-number {
    background: #007cba;
    color: white;
}

.formxr-progress-step.completed .formxr-step-number {
    background: #46b450;
    color: white;
}

.formxr-step-label {
    font-size: 0.85rem;
    text-align: center;
    color: #666;
    min-width: 80px;
}

.formxr-progress-line {
    flex: 1;
    height: 2px;
    background: #e0e0e0;
    margin: 0 1rem;
    transition: all 0.3s ease;
}

.formxr-progress-line.completed {
    background: #46b450;
}

.formxr-step-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding: 2rem;
    background: #f9f9f9;
    border-top: 1px solid #e0e0e0;
}

@media (max-width: 768px) {
    .formxr-progress-bar {
        flex-direction: column;
        gap: 1rem;
    }
    
    .formxr-progress-line {
        width: 2px;
        height: 20px;
        margin: 0;
    }
}
</style>

<script>
// WordPress AJAX data for JavaScript
const formxrAdmin = {
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('formxr_admin_nonce'); ?>'
};
</script>

<?php
// Include footer
include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
?>
