<?php
/**
 * Admin Settings Template
 * Complete rewrite with consistent header/footer structure
 */
if (!defined('ABSPATH')) {
    exit;
}

// Include header
include_once FORMXR_PLUGIN_DIR . 'templates/admin-header.php';

// Handle form submission
if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['formxr_settings_nonce'], 'formxr_save_settings')) {
    // Save general settings
    update_option('formxr_currency', sanitize_text_field($_POST['currency']));
    update_option('formxr_enable_captcha', isset($_POST['enable_captcha']) ? 1 : 0);
    update_option('formxr_email_notifications', isset($_POST['email_notifications']) ? 1 : 0);
    update_option('formxr_admin_email', sanitize_email($_POST['admin_email']));
    update_option('formxr_success_message', sanitize_textarea_field($_POST['success_message']));
    update_option('formxr_error_message', sanitize_textarea_field($_POST['error_message']));
    update_option('formxr_from_name', sanitize_text_field($_POST['from_name']));
    update_option('formxr_from_email', sanitize_email($_POST['from_email']));
    
    // Save styling settings
    update_option('formxr_primary_color', sanitize_hex_color($_POST['primary_color']));
    update_option('formxr_secondary_color', sanitize_hex_color($_POST['secondary_color']));
    update_option('formxr_custom_css', sanitize_textarea_field($_POST['custom_css']));
    
    echo '<div class="formxr-alert formxr-alert-success">';
    echo '<span class="formxr-alert-icon">✅</span>';
    echo __('Settings saved successfully!', 'formxr');
    echo '</div>';
}

// Handle test email
if (isset($_POST['send_test_email']) && wp_verify_nonce($_POST['formxr_test_email_nonce'], 'formxr_send_test_email')) {
    $test_email = sanitize_email($_POST['test_email']);
    $from_name = get_option('formxr_from_name', get_bloginfo('name'));
    $from_email = get_option('formxr_from_email', get_option('admin_email'));
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_email . '>'
    );
    
    $subject = __('FormXR Test Email', 'formxr');
    $message = '<h2>' . __('FormXR Test Email', 'formxr') . '</h2>';
    $message .= '<p>' . __('This is a test email from your FormXR plugin.', 'formxr') . '</p>';
    $message .= '<p>' . sprintf(__('Sent from: %s', 'formxr'), get_bloginfo('name')) . '</p>';
    $message .= '<p>' . sprintf(__('Sent at: %s', 'formxr'), current_time('mysql')) . '</p>';
    
    $sent = wp_mail($test_email, $subject, $message, $headers);
    
    if ($sent) {
        echo '<div class="formxr-alert formxr-alert-success">';
        echo '<span class="formxr-alert-icon">✅</span>';
        echo sprintf(__('Test email sent successfully to %s!', 'formxr'), esc_html($test_email));
        echo '</div>';
    } else {
        echo '<div class="formxr-alert formxr-alert-error">';
        echo '<span class="formxr-alert-icon">❌</span>';
        echo __('Failed to send test email. Please check your email settings.', 'formxr');
        echo '</div>';
    }
}

// Get current settings
$currency = get_option('formxr_currency', 'USD');
$enable_captcha = get_option('formxr_enable_captcha', 0);
$email_notifications = get_option('formxr_email_notifications', 1);
$admin_email = get_option('formxr_admin_email', get_option('admin_email'));
$from_name = get_option('formxr_from_name', get_bloginfo('name'));
$from_email = get_option('formxr_from_email', get_option('admin_email'));
$success_message = get_option('formxr_success_message', __('Thank you for your submission!', 'formxr'));
$error_message = get_option('formxr_error_message', __('An error occurred. Please try again.', 'formxr'));
$primary_color = get_option('formxr_primary_color', '#2AACE2');
$secondary_color = get_option('formxr_secondary_color', '#8062AA');
$custom_css = get_option('formxr_custom_css', '');

// Currency options
$currencies = array(
    'USD' => __('US Dollar ($)', 'formxr'),
    'EUR' => __('Euro (€)', 'formxr'),
    'GBP' => __('British Pound (£)', 'formxr'),
    'JPY' => __('Japanese Yen (¥)', 'formxr'),
    'CAD' => __('Canadian Dollar (C$)', 'formxr'),
    'AUD' => __('Australian Dollar (A$)', 'formxr'),
    'CHF' => __('Swiss Franc (CHF)', 'formxr'),
    'CNY' => __('Chinese Yuan (¥)', 'formxr'),
    'SEK' => __('Swedish Krona (kr)', 'formxr'),
    'NZD' => __('New Zealand Dollar (NZ$)', 'formxr'),
);
?>

<div class="formxr-admin-wrap">
    <!-- Page Header -->
    <div class="formxr-page-header">
        <div class="formxr-page-header-content">
            <h1 class="formxr-page-title">
                <span class="formxr-page-icon">⚙️</span>
                <?php _e('Settings', 'formxr'); ?>
            </h1>
            <p class="formxr-page-subtitle">
                <?php _e('Configure your FormXR plugin settings and preferences', 'formxr'); ?>
            </p>
        </div>
        <div class="formxr-page-actions">
            <button type="submit" form="formxr-settings-form" class="formxr-btn formxr-btn-primary">
                <span class="formxr-btn-icon">💾</span>
                <?php _e('Save Settings', 'formxr'); ?>
            </button>
        </div>
    </div>

    <form id="formxr-settings-form" method="post" action="">
        <?php wp_nonce_field('formxr_save_settings', 'formxr_settings_nonce'); ?>
        
        <!-- General Settings Section -->
        <div class="formxr-section">
            <div class="formxr-section-header">
                <h2 class="formxr-section-title"><?php _e('General Settings', 'formxr'); ?></h2>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <div class="formxr-form-grid">
                        <!-- Currency -->
                        <div class="formxr-form-group">
                            <label for="currency" class="formxr-form-label">
                                <?php _e('Default Currency', 'formxr'); ?>
                            </label>
                            <select id="currency" name="currency" class="formxr-form-control">
                                <?php foreach ($currencies as $code => $name) : ?>
                                    <option value="<?php echo $code; ?>" <?php selected($currency, $code); ?>>
                                        <?php echo esc_html($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="formxr-form-help"><?php _e('Select the default currency for questionnaires.', 'formxr'); ?></p>
                        </div>

                        <!-- Admin Email -->
                        <div class="formxr-form-group">
                            <label for="admin_email" class="formxr-form-label">
                                <?php _e('Admin Email', 'formxr'); ?>
                            </label>
                            <input type="email" 
                                   id="admin_email" 
                                   name="admin_email" 
                                   class="formxr-form-control" 
                                   value="<?php echo esc_attr($admin_email); ?>" 
                                   placeholder="admin@example.com">
                            <p class="formxr-form-help"><?php _e('Email address for notifications and alerts.', 'formxr'); ?></p>
                        </div>

                        <!-- From Name -->
                        <div class="formxr-form-group">
                            <label for="from_name" class="formxr-form-label">
                                <?php _e('From Name', 'formxr'); ?>
                            </label>
                            <input type="text" 
                                   id="from_name" 
                                   name="from_name" 
                                   class="formxr-form-control" 
                                   value="<?php echo esc_attr($from_name); ?>" 
                                   placeholder="<?php echo esc_attr(get_bloginfo('name')); ?>">
                            <p class="formxr-form-help"><?php _e('Name that will appear in outgoing emails.', 'formxr'); ?></p>
                        </div>

                        <!-- From Email -->
                        <div class="formxr-form-group">
                            <label for="from_email" class="formxr-form-label">
                                <?php _e('From Email', 'formxr'); ?>
                            </label>
                            <input type="email" 
                                   id="from_email" 
                                   name="from_email" 
                                   class="formxr-form-control" 
                                   value="<?php echo esc_attr($from_email); ?>" 
                                   placeholder="<?php echo esc_attr(get_option('admin_email')); ?>">
                            <p class="formxr-form-help"><?php _e('Email address that will appear as sender in outgoing emails.', 'formxr'); ?></p>
                        </div>

                        <!-- Enable Captcha -->
                        <div class="formxr-form-group">
                            <div class="formxr-form-check">
                                <input type="checkbox" 
                                       id="enable_captcha" 
                                       name="enable_captcha" 
                                       class="formxr-form-check-input" 
                                       value="1" 
                                       <?php checked($enable_captcha, 1); ?>>
                                <label for="enable_captcha" class="formxr-form-check-label">
                                    <?php _e('Enable Captcha Protection', 'formxr'); ?>
                                </label>
                            </div>
                            <p class="formxr-form-help"><?php _e('Add captcha protection to prevent spam submissions.', 'formxr'); ?></p>
                        </div>

                        <!-- Email Notifications -->
                        <div class="formxr-form-group">
                            <div class="formxr-form-check">
                                <input type="checkbox" 
                                       id="email_notifications" 
                                       name="email_notifications" 
                                       class="formxr-form-check-input" 
                                       value="1" 
                                       <?php checked($email_notifications, 1); ?>>
                                <label for="email_notifications" class="formxr-form-check-label">
                                    <?php _e('Enable Email Notifications', 'formxr'); ?>
                                </label>
                            </div>
                            <p class="formxr-form-help"><?php _e('Send email notifications for new submissions.', 'formxr'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Email Section -->
        <div class="formxr-section">
            <div class="formxr-section-header">
                <h2 class="formxr-section-title"><?php _e('Test Email', 'formxr'); ?></h2>
                <p class="formxr-section-description"><?php _e('Send a test email to verify your email settings are working correctly.', 'formxr'); ?></p>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <form method="post" action="" class="formxr-test-email-form">
                        <?php wp_nonce_field('formxr_send_test_email', 'formxr_test_email_nonce'); ?>
                        <div class="formxr-form-grid">
                            <div class="formxr-form-group">
                                <label for="test_email" class="formxr-form-label">
                                    <?php _e('Test Email Address', 'formxr'); ?>
                                </label>
                                <input type="email" 
                                       id="test_email" 
                                       name="test_email" 
                                       class="formxr-form-control" 
                                       value="<?php echo esc_attr(get_option('admin_email')); ?>" 
                                       placeholder="test@example.com"
                                       required>
                                <p class="formxr-form-help"><?php _e('Enter the email address where you want to send the test email.', 'formxr'); ?></p>
                            </div>
                            <div class="formxr-form-group">
                                <button type="submit" name="send_test_email" class="formxr-btn formxr-btn-secondary">
                                    <span class="formxr-btn-icon">📧</span>
                                    <?php _e('Send Test Email', 'formxr'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Messages Section -->
        <div class="formxr-section">
            <div class="formxr-section-header">
                <h2 class="formxr-section-title"><?php _e('Custom Messages', 'formxr'); ?></h2>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <div class="formxr-form-grid">
                        <!-- Success Message -->
                        <div class="formxr-form-group formxr-form-group-full">
                            <label for="success_message" class="formxr-form-label">
                                <?php _e('Success Message', 'formxr'); ?>
                            </label>
                            <textarea id="success_message" 
                                      name="success_message" 
                                      class="formxr-form-control" 
                                      rows="3"
                                      placeholder="<?php _e('Thank you for your submission!', 'formxr'); ?>"><?php echo esc_textarea($success_message); ?></textarea>
                            <p class="formxr-form-help"><?php _e('Message shown to users after successful form submission.', 'formxr'); ?></p>
                        </div>

                        <!-- Error Message -->
                        <div class="formxr-form-group formxr-form-group-full">
                            <label for="error_message" class="formxr-form-label">
                                <?php _e('Error Message', 'formxr'); ?>
                            </label>
                            <textarea id="error_message" 
                                      name="error_message" 
                                      class="formxr-form-control" 
                                      rows="3"
                                      placeholder="<?php _e('An error occurred. Please try again.', 'formxr'); ?>"><?php echo esc_textarea($error_message); ?></textarea>
                            <p class="formxr-form-help"><?php _e('Message shown to users when form submission fails.', 'formxr'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Styling Section -->
        <div class="formxr-section">
            <div class="formxr-section-header">
                <h2 class="formxr-section-title"><?php _e('Styling & Appearance', 'formxr'); ?></h2>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <div class="formxr-form-grid">
                        <!-- Primary Color -->
                        <div class="formxr-form-group">
                            <label for="primary_color" class="formxr-form-label">
                                <?php _e('Primary Color', 'formxr'); ?>
                            </label>
                            <div class="formxr-color-input">
                                <input type="color" 
                                       id="primary_color" 
                                       name="primary_color" 
                                       class="formxr-form-control formxr-color-picker" 
                                       value="<?php echo esc_attr($primary_color); ?>">
                                <input type="text" 
                                       class="formxr-form-control formxr-color-text" 
                                       value="<?php echo esc_attr($primary_color); ?>" 
                                       placeholder="#2AACE2">
                            </div>
                            <p class="formxr-form-help"><?php _e('Primary color used in buttons and highlights.', 'formxr'); ?></p>
                        </div>

                        <!-- Secondary Color -->
                        <div class="formxr-form-group">
                            <label for="secondary_color" class="formxr-form-label">
                                <?php _e('Secondary Color', 'formxr'); ?>
                            </label>
                            <div class="formxr-color-input">
                                <input type="color" 
                                       id="secondary_color" 
                                       name="secondary_color" 
                                       class="formxr-form-control formxr-color-picker" 
                                       value="<?php echo esc_attr($secondary_color); ?>">
                                <input type="text" 
                                       class="formxr-form-control formxr-color-text" 
                                       value="<?php echo esc_attr($secondary_color); ?>" 
                                       placeholder="#8062AA">
                            </div>
                            <p class="formxr-form-help"><?php _e('Secondary color used in accents and borders.', 'formxr'); ?></p>
                        </div>

                        <!-- Custom CSS -->
                        <div class="formxr-form-group formxr-form-group-full">
                            <label for="custom_css" class="formxr-form-label">
                                <?php _e('Custom CSS', 'formxr'); ?>
                            </label>
                            <textarea id="custom_css" 
                                      name="custom_css" 
                                      class="formxr-form-control formxr-code-editor" 
                                      rows="8"
                                      placeholder="/* Add your custom CSS here */"><?php echo esc_textarea($custom_css); ?></textarea>
                            <p class="formxr-form-help"><?php _e('Add custom CSS to override default styles. Use with caution.', 'formxr'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Settings Section -->
        <div class="formxr-section">
            <div class="formxr-section-header">
                <h2 class="formxr-section-title"><?php _e('Advanced Settings', 'formxr'); ?></h2>
            </div>
            
            <div class="formxr-widget">
                <div class="formxr-widget-content">
                    <div class="formxr-alert formxr-alert-warning">
                        <span class="formxr-alert-icon">⚠️</span>
                        <strong><?php _e('Warning:', 'formxr'); ?></strong>
                        <?php _e('These settings can affect plugin functionality. Only modify if you know what you\'re doing.', 'formxr'); ?>
                    </div>

                    <div class="formxr-form-grid">
                        <!-- Database Actions -->
                        <div class="formxr-form-group formxr-form-group-full">
                            <label class="formxr-form-label">
                                <?php _e('Database Actions', 'formxr'); ?>
                            </label>
                            <div class="formxr-button-group">
                                <button type="button" 
                                        class="formxr-btn formxr-btn-secondary" 
                                        onclick="formxrExportData()">
                                    <span class="formxr-btn-icon">📤</span>
                                    <?php _e('Export Data', 'formxr'); ?>
                                </button>
                                
                                <button type="button" 
                                        class="formxr-btn formxr-btn-warning" 
                                        onclick="formxrClearData()">
                                    <span class="formxr-btn-icon">🗑️</span>
                                    <?php _e('Clear All Data', 'formxr'); ?>
                                </button>
                                
                                <button type="button" 
                                        class="formxr-btn formxr-btn-error" 
                                        onclick="formxrResetPlugin()">
                                    <span class="formxr-btn-icon">🔄</span>
                                    <?php _e('Reset Plugin', 'formxr'); ?>
                                </button>
                            </div>
                            <p class="formxr-form-help"><?php _e('Use these actions carefully. Always backup your data first.', 'formxr'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="formxr-section">
            <div class="formxr-form-actions">
                <button type="submit" name="save_settings" class="formxr-btn formxr-btn-primary formxr-btn-large">
                    <span class="formxr-btn-icon">💾</span>
                    <?php _e('Save All Settings', 'formxr'); ?>
                </button>
                
                <button type="reset" class="formxr-btn formxr-btn-secondary formxr-btn-large">
                    <span class="formxr-btn-icon">🔄</span>
                    <?php _e('Reset Form', 'formxr'); ?>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Color picker sync
    const colorPickers = document.querySelectorAll('.formxr-color-picker');
    colorPickers.forEach(picker => {
        const textInput = picker.parentNode.querySelector('.formxr-color-text');
        
        picker.addEventListener('change', function() {
            textInput.value = this.value;
        });
        
        textInput.addEventListener('change', function() {
            picker.value = this.value;
        });
    });
});

function formxrExportData() {
    if (confirm('<?php _e('This will download all your FormXR data. Continue?', 'formxr'); ?>')) {
        window.location.href = '<?php echo admin_url('admin-ajax.php?action=formxr_export_data&_wpnonce=' . wp_create_nonce('formxr_export')); ?>';
    }
}

function formxrClearData() {
    if (confirm('<?php _e('This will permanently delete all submissions but keep questionnaires. Are you sure?', 'formxr'); ?>')) {
        // Add AJAX call to clear data
        alert('<?php _e('This feature will be implemented in the next update.', 'formxr'); ?>');
    }
}

function formxrResetPlugin() {
    if (confirm('<?php _e('This will reset ALL plugin data and settings. This cannot be undone! Are you absolutely sure?', 'formxr'); ?>')) {
        if (confirm('<?php _e('FINAL WARNING: This will delete everything. Type "RESET" in the next dialog to confirm.', 'formxr'); ?>')) {
            const confirmation = prompt('<?php _e('Type "RESET" to confirm:', 'formxr'); ?>');
            if (confirmation === 'RESET') {
                // Add AJAX call to reset plugin
                alert('<?php _e('This feature will be implemented in the next update.', 'formxr'); ?>');
            }
        }
    }
}
</script>

<?php
// Include footer
include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
?>
