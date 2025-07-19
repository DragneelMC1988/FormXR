<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check for SMTP plugins
$smtp_plugins = array();
if (is_plugin_active('wp-mail-smtp/wp_mail_smtp.php')) {
    $smtp_plugins[] = 'WP Mail SMTP';
}
if (is_plugin_active('easy-wp-smtp/easy-wp-smtp.php')) {
    $smtp_plugins[] = 'Easy WP SMTP';
}
if (is_plugin_active('post-smtp/postman-smtp.php')) {
    $smtp_plugins[] = 'Post SMTP';
}

// Get current settings
$email_method = get_option('formxr_email_method', 'wp_mail');
$smtp_host = get_option('formxr_smtp_host', '');
$smtp_port = get_option('formxr_smtp_port', '587');
$smtp_username = get_option('formxr_smtp_username', '');
$smtp_password = get_option('formxr_smtp_password', '');
$smtp_secure = get_option('formxr_smtp_secure', 'tls');
$from_email = get_option('formxr_from_email', get_option('admin_email'));
$from_name = get_option('formxr_from_name', get_bloginfo('name'));
$test_email = get_option('formxr_test_email', get_option('admin_email'));
$currency = get_option('formxr_currency', 'USD');
$enable_notifications = get_option('formxr_enable_notifications', 1);

// Handle settings save
if (isset($_POST['submit']) && wp_verify_nonce($_POST['formxr_settings_nonce'], 'formxr_settings')) {
    // Email settings
    update_option('formxr_email_method', sanitize_text_field($_POST['email_method']));
    update_option('formxr_smtp_host', sanitize_text_field($_POST['smtp_host']));
    update_option('formxr_smtp_port', intval($_POST['smtp_port']));
    update_option('formxr_smtp_username', sanitize_text_field($_POST['smtp_username']));
    update_option('formxr_smtp_password', sanitize_text_field($_POST['smtp_password']));
    update_option('formxr_smtp_secure', sanitize_text_field($_POST['smtp_secure']));
    update_option('formxr_from_email', sanitize_email($_POST['from_email']));
    update_option('formxr_from_name', sanitize_text_field($_POST['from_name']));
    update_option('formxr_test_email', sanitize_email($_POST['test_email']));
    
    // General settings
    update_option('formxr_currency', sanitize_text_field($_POST['currency']));
    update_option('formxr_enable_notifications', isset($_POST['enable_notifications']) ? 1 : 0);
    
    // Refresh variables
    $email_method = get_option('formxr_email_method', 'wp_mail');
    $smtp_host = get_option('formxr_smtp_host', '');
    $smtp_port = get_option('formxr_smtp_port', '587');
    $smtp_username = get_option('formxr_smtp_username', '');
    $smtp_password = get_option('formxr_smtp_password', '');
    $smtp_secure = get_option('formxr_smtp_secure', 'tls');
    $from_email = get_option('formxr_from_email', get_option('admin_email'));
    $from_name = get_option('formxr_from_name', get_bloginfo('name'));
    $test_email = get_option('formxr_test_email', get_option('admin_email'));
    $currency = get_option('formxr_currency', 'USD');
    $enable_notifications = get_option('formxr_enable_notifications', 1);
    
    $success_message = __('Settings saved successfully!', 'formxr');
}
?>   
<div class="wrap" x-data="settingsPage()">
    <div class="formxr-page-header">
        <div class="formxr-page-title">
            <h1>
                <span class="dashicons dashicons-admin-tools"></span>
                <?php _e('FormXR Settings', 'formxr'); ?>
            </h1>
            <div class="formxr-header-actions">
                <a href="<?php echo admin_url('admin.php?page=formxr'); ?>" class="btn-formxr btn-outline">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <?php _e('Back to Dashboard', 'formxr'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="formxr-container">
        <?php if (isset($success_message)): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($success_message); ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field('formxr_settings', 'formxr_settings_nonce'); ?>

            <!-- Email Configuration -->
            <div class="formxr-card">
                <div class="formxr-card-header">
                    <h2>📧 <?php _e('Email Configuration', 'formxr'); ?></h2>
                    <p><?php _e('Configure how FormXR sends notification emails', 'formxr'); ?></p>
                </div>
                <div class="formxr-card-body">
                    <div class="formxr-form-group">
                        <label class="formxr-label"><?php _e('Email Delivery Method', 'formxr'); ?></label>
                        <div class="formxr-email-methods">
                            <div class="formxr-email-method <?php echo $email_method === 'wp_mail' ? 'active' : ''; ?>">
                                <input type="radio" id="wp_mail" name="email_method" value="wp_mail" <?php checked($email_method, 'wp_mail'); ?>>
                                <h4><?php _e('WordPress Default (PHP mail)', 'formxr'); ?></h4>
                                <p><?php _e('Use WordPress built-in mail function. May not work on all servers.', 'formxr'); ?></p>
                            </div>
                            
                            <?php if (!empty($smtp_plugins)): ?>
                                <div class="formxr-email-method detected <?php echo $email_method === 'plugin' ? 'active' : ''; ?>">
                                    <input type="radio" id="plugin" name="email_method" value="plugin" <?php checked($email_method, 'plugin'); ?>>
                                    <h4><?php _e('Use SMTP Plugin', 'formxr'); ?></h4>
                                    <p><?php printf(__('Use existing SMTP plugin: %s', 'formxr'), implode(', ', $smtp_plugins)); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="formxr-email-method <?php echo $email_method === 'formxr_smtp' ? 'active' : ''; ?>">
                                <input type="radio" id="formxr_smtp" name="email_method" value="formxr_smtp" <?php checked($email_method, 'formxr_smtp'); ?>>
                                <h4><?php _e('FormXR Built-in SMTP', 'formxr'); ?></h4>
                                <p><?php _e('Configure SMTP settings specifically for FormXR.', 'formxr'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- General Settings -->
            <div class="formxr-card">
                <div class="formxr-card-header">
                    <h2>🎯 <?php _e('General Settings', 'formxr'); ?></h2>
                    <p><?php _e('Configure general plugin behavior and defaults', 'formxr'); ?></p>
                </div>
                <div class="formxr-card-body">
                    <div class="formxr-form-grid">
                        <div class="formxr-form-group">
                            <label for="currency" class="formxr-label"><?php _e('Default Currency', 'formxr'); ?></label>
                            <select id="currency" name="currency" class="formxr-select">
                                <option value="USD" <?php selected($currency, 'USD'); ?>>USD ($)</option>
                                <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR (€)</option>
                                <option value="GBP" <?php selected($currency, 'GBP'); ?>>GBP (£)</option>
                                <option value="CHF" <?php selected($currency, 'CHF'); ?>>CHF</option>
                                <option value="SEK" <?php selected($currency, 'SEK'); ?>>SEK</option>
                                <option value="NOK" <?php selected($currency, 'NOK'); ?>>NOK</option>
                                <option value="DKK" <?php selected($currency, 'DKK'); ?>>DKK</option>
                            </select>
                        </div>
                        
                        <div class="formxr-form-group">
                            <label class="formxr-label"><?php _e('Enable Email Notifications', 'formxr'); ?></label>
                            <label class="formxr-checkbox-label">
                                <input type="checkbox" name="enable_notifications" value="1" <?php checked($enable_notifications, 1); ?>>
                                <?php _e('Send email notifications when forms are submitted', 'formxr'); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SMTP Configuration (shown when FormXR SMTP is selected) -->
            <div class="formxr-card" x-show="document.querySelector('input[name=email_method]:checked')?.value === 'formxr_smtp'">
                <div class="formxr-card-header">
                    <h2>📧 <?php _e('SMTP Configuration', 'formxr'); ?></h2>
                    <p><?php _e('Configure your email server settings for sending notifications', 'formxr'); ?></p>
                </div>
                <div class="formxr-card-body">
                    <div class="formxr-form-grid">
                        <div class="formxr-form-group">
                            <label for="smtp_host" class="formxr-label"><?php _e('SMTP Host', 'formxr'); ?> <span class="formxr-required">*</span></label>
                            <input type="text" id="smtp_host" name="smtp_host" value="<?php echo esc_attr($smtp_host); ?>" class="formxr-input" placeholder="smtp.gmail.com">
                            <p class="formxr-help-text"><?php _e('Your SMTP server hostname', 'formxr'); ?></p>
                        </div>
                        
                        <div class="formxr-form-group">
                            <label for="smtp_port" class="formxr-label"><?php _e('SMTP Port', 'formxr'); ?> <span class="formxr-required">*</span></label>
                            <input type="number" id="smtp_port" name="smtp_port" value="<?php echo esc_attr($smtp_port); ?>" class="formxr-input" placeholder="587">
                            <p class="formxr-help-text"><?php _e('Common ports: 587 (TLS), 465 (SSL), 25 (none)', 'formxr'); ?></p>
                        </div>
                        
                        <div class="formxr-form-group">
                            <label for="smtp_secure" class="formxr-label"><?php _e('Encryption', 'formxr'); ?></label>
                            <select id="smtp_secure" name="smtp_secure" class="formxr-select">
                                <option value="tls" <?php selected($smtp_secure, 'tls'); ?>>TLS</option>
                                <option value="ssl" <?php selected($smtp_secure, 'ssl'); ?>>SSL</option>
                                <option value="" <?php selected($smtp_secure, ''); ?>><?php _e('None', 'formxr'); ?></option>
                            </select>
                            <p class="formxr-help-text"><?php _e('Recommended: TLS for port 587, SSL for port 465', 'formxr'); ?></p>
                        </div>
                        
                        <div class="formxr-form-group">
                            <label for="smtp_username" class="formxr-label"><?php _e('SMTP Username', 'formxr'); ?></label>
                            <input type="text" id="smtp_username" name="smtp_username" value="<?php echo esc_attr($smtp_username); ?>" class="formxr-input">
                            <p class="formxr-help-text"><?php _e('Usually your email address', 'formxr'); ?></p>
                        </div>
                        
                        <div class="formxr-form-group">
                            <label for="smtp_password" class="formxr-label"><?php _e('SMTP Password', 'formxr'); ?></label>
                            <input type="password" id="smtp_password" name="smtp_password" value="<?php echo esc_attr($smtp_password); ?>" class="formxr-input">
                            <p class="formxr-help-text"><?php _e('For Gmail, use an App Password', 'formxr'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="formxr-card">
                <div class="formxr-card-header">
                    <h2>✉️ <?php _e('Email Settings', 'formxr'); ?></h2>
                    <p><?php _e('Configure the sender information for outgoing emails', 'formxr'); ?></p>
                </div>
                <div class="formxr-card-body">
                    <div class="formxr-form-grid">
                        <div class="formxr-form-group">
                            <label for="from_email" class="formxr-label"><?php _e('From Email', 'formxr'); ?> <span class="formxr-required">*</span></label>
                            <input type="email" id="from_email" name="from_email" value="<?php echo esc_attr($from_email); ?>" class="formxr-input" required>
                            <p class="formxr-help-text"><?php _e('The email address that notifications will be sent from', 'formxr'); ?></p>
                        </div>
                        
                        <div class="formxr-form-group">
                            <label for="from_name" class="formxr-label"><?php _e('From Name', 'formxr'); ?></label>
                            <input type="text" id="from_name" name="from_name" value="<?php echo esc_attr($from_name); ?>" class="formxr-input">
                            <p class="formxr-help-text"><?php _e('The name that will appear as the sender', 'formxr'); ?></p>
                        </div>
                        
                        <div class="formxr-form-group">
                            <label for="formxr_test_email" class="formxr-label"><?php _e('Test Email Address', 'formxr'); ?></label>
                            <input type="email" id="formxr_test_email" name="test_email" value="<?php echo esc_attr($test_email); ?>" class="formxr-input">
                            <p class="formxr-help-text"><?php _e('Email address to send test emails to', 'formxr'); ?></p>
                        </div>
                    </div>
                    
                    <div class="formxr-button-group">
                        <button type="button" @click="testEmail()" :disabled="testing" class="btn-formxr btn-outline">
                            <span x-show="!testing">📧 <?php _e('Test Email', 'formxr'); ?></span>
                            <span x-show="testing">⏳ <?php _e('Sending...', 'formxr'); ?></span>
                        </button>
                        
                        <button type="submit" name="submit" class="btn-formxr">
                            💾 <?php _e('Save Settings', 'formxr'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Email Templates Info -->
            <div class="formxr-card">
                <div class="formxr-card-header">
                    <h2>📝 <?php _e('Email Templates', 'formxr'); ?></h2>
                    <p><?php _e('Email templates are configured per questionnaire', 'formxr'); ?></p>
                </div>
                <div class="formxr-card-body">
                    <div class="formxr-info-box">
                        <div class="formxr-info-icon">📧</div>
                        <div class="formxr-info-content">
                            <h3><?php _e('Template Customization', 'formxr'); ?></h3>
                            <p><?php _e('Each questionnaire can have its own email template and recipient list. You can configure these in the questionnaire builder.', 'formxr'); ?></p>
                            <div class="formxr-info-actions">
                                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="btn-formxr btn-outline">
                                    🔧 <?php _e('Manage Questionnaires', 'formxr'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="formxr-info-box">
                        <div class="formxr-info-icon">🎯</div>
                        <div class="formxr-info-content">
                            <h3><?php _e('Available Placeholders', 'formxr'); ?></h3>
                            <p><?php _e('Use these placeholders in your email templates:', 'formxr'); ?></p>
                            <div class="formxr-placeholder-grid">
                                <code>{{user_email}}</code>
                                <code>{{calculated_price}}</code>
                                <code>{{questionnaire_title}}</code>
                                <code>{{submission_data}}</code>
                                <code>{{submitted_date}}</code>
                                <code>{{site_name}}</code>
                                <code>{{site_url}}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Test Email Modal -->
    <div x-show="showTestModal" class="formxr-modal-overlay" @click="showTestModal = false">
        <div class="formxr-modal" @click.stop>
            <div class="formxr-modal-header">
                <h3><?php _e('Email Test Result', 'formxr'); ?></h3>
                <button type="button" class="formxr-modal-close" @click="showTestModal = false">×</button>
            </div>
            <div class="formxr-modal-body">
                <div :class="testResult.success ? 'formxr-alert formxr-alert-success' : 'formxr-alert formxr-alert-error'">
                    <div class="formxr-alert-icon" x-text="testResult.success ? '✅' : '❌'"></div>
                    <div class="formxr-alert-content">
                        <h4 x-text="testResult.success ? '<?php _e('Success!', 'formxr'); ?>' : '<?php _e('Failed', 'formxr'); ?>'"></h4>
                        <p x-text="testResult.message"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function settingsPage() {
    return {
        saving: false,
        testing: false,
        showTestModal: false,
        testResult: { success: false, message: '' },

        async testEmail() {
            if (this.testing) return;
            
            this.testing = true;
            
            try {
                // Check if required objects exist
                if (typeof formxr_admin_ajax === 'undefined') {
                    throw new Error('Admin AJAX object not loaded');
                }
                
                const testEmailInput = document.getElementById('formxr_test_email');
                if (!testEmailInput || !testEmailInput.value.trim()) {
                    throw new Error('Please enter a valid email address');
                }
                
                const formData = new FormData();
                formData.append('action', 'formxr_test_email');
                formData.append('nonce', formxr_admin_ajax.nonce);
                formData.append('test_email', testEmailInput.value.trim());
                
                const response = await fetch(formxr_admin_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                
                this.testResult = {
                    success: result.success || false,
                    message: result.data?.message || result.data || (result.success ? 'Test completed' : 'Unknown error occurred')
                };
                
                this.showTestModal = true;
                
            } catch (error) {
                console.error('Test email error:', error);
                this.testResult = {
                    success: false,
                    message: 'Error: ' + error.message
                };
                this.showTestModal = true;
            } finally {
                this.testing = false;
            }
        }
    }
}
</script>
</div>
