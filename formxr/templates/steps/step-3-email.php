<?php
/**
 * Step 3: Email Templates
 * Configure email notifications and templates
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="formxr-step-content">
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title">
                <?php _e('Step 3 - Email Templates', 'formxr'); ?>
            </h2>
            <p class="formxr-section-description">
                <?php _e('Configure email notifications that will be sent when users submit your questionnaire.', 'formxr'); ?>
            </p>
        </div>
        
        <!-- Admin Email Configuration -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Admin Notification', 'formxr'); ?>
                </h3>
            </div>
            <div class="formxr-widget-content">
                <div class="formxr-form-grid">
                    <!-- Admin Email Recipients -->
                    <div class="formxr-form-group formxr-form-group-full">
                        <label for="admin_email" class="formxr-form-label">
                            <?php _e('Admin Email Recipients', 'formxr'); ?>
                        </label>
                        <input type="email" 
                               id="admin_email" 
                               name="admin_email" 
                               class="formxr-form-control" 
                               x-model="emailConfig.adminEmail"
                               placeholder="<?php echo esc_attr(get_option('admin_email')); ?>"
                               multiple>
                        <p class="formxr-form-help">
                            <?php _e('Email address where submission notifications will be sent. Separate multiple emails with commas.', 'formxr'); ?>
                        </p>
                    </div>

                    <!-- Admin Email Subject -->
                    <div class="formxr-form-group formxr-form-group-full">
                        <label for="admin_subject" class="formxr-form-label">
                            <?php _e('Admin Email Subject', 'formxr'); ?>
                        </label>
                        <input type="text" 
                               id="admin_subject" 
                               name="admin_subject" 
                               class="formxr-form-control" 
                               x-model="emailConfig.adminSubject"
                               placeholder="<?php _e('New submission for {questionnaire_title}', 'formxr'); ?>">
                        <p class="formxr-form-help">
                            <?php _e('Subject line for admin notification emails. You can use {questionnaire_title} placeholder.', 'formxr'); ?>
                        </p>
                    </div>

                    <!-- Admin Email Template -->
                    <div class="formxr-form-group formxr-form-group-full">
                        <label for="admin_template" class="formxr-form-label">
                            <?php _e('Admin Email Template', 'formxr'); ?>
                        </label>
                        <textarea id="admin_template" 
                                  name="admin_template" 
                                  class="formxr-form-control" 
                                  rows="8"
                                  x-model="emailConfig.adminTemplate"
                                  placeholder="<?php _e('Enter your admin email template...', 'formxr'); ?>"></textarea>
                        <p class="formxr-form-help">
                            <?php _e('Template for admin notification emails. Available placeholders: {questionnaire_title}, {user_email}, {submission_data}, {calculated_price}, {submitted_date}', 'formxr'); ?>
                        </p>
                        
                        <!-- Template Buttons -->
                        <div class="formxr-template-actions">
                            <button type="button" 
                                    @click="insertDefaultAdminTemplate()" 
                                    class="formxr-btn formxr-btn-sm formxr-btn-secondary">
                                <?php _e('Insert Default Template', 'formxr'); ?>
                            </button>
                            <button type="button" 
                                    @click="previewEmailTemplate('admin')" 
                                    class="formxr-btn formxr-btn-sm formxr-btn-outline">
                                <?php _e('Preview', 'formxr'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Email Configuration -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('User Confirmation Email', 'formxr'); ?>
                </h3>
                <div class="formxr-form-check">
                    <input type="checkbox" 
                           id="enable_user_email" 
                           name="enable_user_email"
                           class="formxr-form-check-input" 
                           x-model="emailConfig.userEmailEnabled"
                           value="1">
                    <label for="enable_user_email" class="formxr-form-check-label">
                        <?php _e('Send confirmation email to user', 'formxr'); ?>
                    </label>
                </div>
            </div>
            <div class="formxr-widget-content" x-show="emailConfig.userEmailEnabled" x-transition>
                <div class="formxr-form-grid">
                    <!-- User Email Subject -->
                    <div class="formxr-form-group formxr-form-group-full">
                        <label for="user_subject" class="formxr-form-label">
                            <?php _e('User Email Subject', 'formxr'); ?>
                        </label>
                        <input type="text" 
                               id="user_subject" 
                               name="user_subject" 
                               class="formxr-form-control" 
                               x-model="emailConfig.userSubject"
                               placeholder="<?php _e('Thank you for your submission', 'formxr'); ?>">
                        <p class="formxr-form-help">
                            <?php _e('Subject line for user confirmation emails.', 'formxr'); ?>
                        </p>
                    </div>

                    <!-- User Email Template -->
                    <div class="formxr-form-group formxr-form-group-full">
                        <label for="user_template" class="formxr-form-label">
                            <?php _e('User Email Template', 'formxr'); ?>
                        </label>
                        <textarea id="user_template" 
                                  name="user_template" 
                                  class="formxr-form-control" 
                                  rows="8"
                                  x-model="emailConfig.userTemplate"
                                  placeholder="<?php _e('Enter your user confirmation template...', 'formxr'); ?>"></textarea>
                        <p class="formxr-form-help">
                            <?php _e('Template for user confirmation emails. Same placeholders available as admin template.', 'formxr'); ?>
                        </p>
                        
                        <!-- Template Buttons -->
                        <div class="formxr-template-actions">
                            <button type="button" 
                                    @click="insertDefaultUserTemplate()" 
                                    class="formxr-btn formxr-btn-sm formxr-btn-secondary">
                                <?php _e('Insert Default Template', 'formxr'); ?>
                            </button>
                            <button type="button" 
                                    @click="previewEmailTemplate('user')" 
                                    class="formxr-btn formxr-btn-sm formxr-btn-outline">
                                <?php _e('Preview', 'formxr'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Settings -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Email Settings', 'formxr'); ?>
                </h3>
            </div>
            <div class="formxr-widget-content">
                <div class="formxr-form-grid">
                    <div class="formxr-form-group">
                        <div class="formxr-form-check">
                            <input type="checkbox" 
                                   id="enable_notifications" 
                                   name="enable_notifications"
                                   class="formxr-form-check-input" 
                                   x-model="emailConfig.notificationsEnabled"
                                   value="1">
                            <label for="enable_notifications" class="formxr-form-check-label">
                                <?php _e('Enable email notifications', 'formxr'); ?>
                            </label>
                        </div>
                        <p class="formxr-form-help">
                            <?php _e('Uncheck to disable all email notifications for this questionnaire.', 'formxr'); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Test Email -->
                <div class="formxr-test-email-section">
                    <h4><?php _e('Test Email Configuration', 'formxr'); ?></h4>
                    <div class="formxr-form-row">
                        <div class="formxr-form-group">
                            <input type="email" 
                                   id="test_email" 
                                   x-model="emailConfig.testEmail"
                                   class="formxr-form-control" 
                                   placeholder="<?php _e('Enter test email address', 'formxr'); ?>">
                        </div>
                        <button type="button" 
                                @click="sendTestEmail()" 
                                :disabled="!emailConfig.testEmail || testingEmail"
                                class="formxr-btn formxr-btn-secondary">
                            <span x-show="!testingEmail"><?php _e('Send Test Email', 'formxr'); ?></span>
                            <span x-show="testingEmail"><?php _e('Sending...', 'formxr'); ?></span>
                        </button>
                    </div>
                    <p class="formxr-form-help">
                        <?php _e('Send a test email to verify your email configuration is working correctly.', 'formxr'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Available Placeholders Reference -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Available Placeholders', 'formxr'); ?>
                </h3>
            </div>
            <div class="formxr-widget-content">
                <div class="formxr-placeholders-grid">
                    <div class="formxr-placeholder-item">
                        <code>{questionnaire_title}</code>
                        <span><?php _e('The title of your questionnaire', 'formxr'); ?></span>
                    </div>
                    <div class="formxr-placeholder-item">
                        <code>{user_email}</code>
                        <span><?php _e('Email address of the person who submitted', 'formxr'); ?></span>
                    </div>
                    <div class="formxr-placeholder-item">
                        <code>{calculated_price}</code>
                        <span><?php _e('Calculated price based on responses (if pricing enabled)', 'formxr'); ?></span>
                    </div>
                    <div class="formxr-placeholder-item">
                        <code>{submission_data}</code>
                        <span><?php _e('All questions and answers from the submission', 'formxr'); ?></span>
                    </div>
                    <div class="formxr-placeholder-item">
                        <code>{submitted_date}</code>
                        <span><?php _e('Date and time when the form was submitted', 'formxr'); ?></span>
                    </div>
                    <div class="formxr-placeholder-item">
                        <code>{site_name}</code>
                        <span><?php _e('Your website name', 'formxr'); ?></span>
                    </div>
                    <div class="formxr-placeholder-item">
                        <code>{site_url}</code>
                        <span><?php _e('Your website URL', 'formxr'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.formxr-template-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.formxr-test-email-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e0e0e0;
}

.formxr-test-email-section h4 {
    margin: 0 0 1rem 0;
    color: #495057;
}

.formxr-placeholders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.formxr-placeholder-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.formxr-placeholder-item code {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 0.875rem;
    color: #e83e8c;
}

.formxr-placeholder-item span {
    font-size: 0.875rem;
    color: #6c757d;
}
</style>

<script>
// Add these methods to the main questionnaireCreator function
const emailConfig = {
    adminEmail: '<?php echo esc_js(get_option('admin_email')); ?>',
    adminSubject: 'New submission for {questionnaire_title}',
    adminTemplate: '',
    userEmailEnabled: false,
    userSubject: 'Thank you for your submission',
    userTemplate: '',
    notificationsEnabled: true,
    testEmail: '',
    testingEmail: false
};

function insertDefaultAdminTemplate() {
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
}

function insertDefaultUserTemplate() {
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
}

function previewEmailTemplate(type) {
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
}

async function sendTestEmail() {
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
}
</script>
