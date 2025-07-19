<?php
/**
 * Step 1: Basic Information
 * Inputs: Questionnaire Title, Description, Enable Pricing
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="formxr-step-content">
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title">
                <?php _e('Step 1 - Basic Information', 'formxr'); ?>
            </h2>
            <p class="formxr-section-description">
                <?php _e('Let\'s start by setting up the basic information for your questionnaire.', 'formxr'); ?>
            </p>
        </div>
        
        <div class="formxr-widget">
            <div class="formxr-widget-content">
                <div class="formxr-form-grid">
                    <!-- Questionnaire Title -->
                    <div class="formxr-form-group formxr-form-group-full">
                        <label for="questionnaire_title" class="formxr-form-label">
                            <?php _e('Questionnaire Title', 'formxr'); ?> 
                            <span class="formxr-required">*</span>
                        </label>
                        <input type="text" 
                               id="questionnaire_title" 
                               name="title" 
                               class="formxr-form-control" 
                               x-model="basicInfo.title"
                               placeholder="<?php _e('Enter a clear, descriptive title for your questionnaire', 'formxr'); ?>" 
                               required>
                        <p class="formxr-form-help">
                            <?php _e('This will be displayed to users when they see your questionnaire. Make it clear and descriptive.', 'formxr'); ?>
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="formxr-form-group formxr-form-group-full">
                        <label for="questionnaire_description" class="formxr-form-label">
                            <?php _e('Description', 'formxr'); ?>
                        </label>
                        <textarea id="questionnaire_description" 
                                  name="description" 
                                  class="formxr-form-control" 
                                  rows="4"
                                  x-model="basicInfo.description"
                                  placeholder="<?php _e('Provide additional context or instructions for users filling out this questionnaire...', 'formxr'); ?>"></textarea>
                        <p class="formxr-form-help">
                            <?php _e('Optional description that will help users understand the purpose of this questionnaire.', 'formxr'); ?>
                        </p>
                    </div>

                    <!-- Enable Pricing -->
                    <div class="formxr-form-group formxr-form-group-full">
                        <div class="formxr-form-check">
                            <input type="checkbox" 
                                   id="enable_pricing" 
                                   name="enable_pricing"
                                   class="formxr-form-check-input" 
                                   x-model="basicInfo.enablePricing"
                                   value="1">
                            <label for="enable_pricing" class="formxr-form-check-label">
                                <strong><?php _e('Enable Pricing', 'formxr'); ?></strong>
                            </label>
                        </div>
                        <p class="formxr-form-help">
                            <?php _e('Check this if you want to calculate prices based on user responses. You can configure pricing details in later steps.', 'formxr'); ?>
                        </p>
                    </div>

                    <!-- Show Price in Frontend -->
                    <div class="formxr-form-group formxr-form-group-full" x-show="basicInfo.enablePricing">
                        <div class="formxr-form-check">
                            <input type="checkbox" 
                                   id="show_price_frontend" 
                                   name="show_price_frontend"
                                   class="formxr-form-check-input" 
                                   x-model="basicInfo.showPriceFrontend"
                                   value="1">
                            <label for="show_price_frontend" class="formxr-form-check-label">
                                <strong><?php _e('Show Price in Frontend', 'formxr'); ?></strong>
                            </label>
                        </div>
                        <p class="formxr-form-help">
                            <?php _e('When enabled, price calculations will be visible to users. When disabled, prices are calculated and stored for analytics/submissions but hidden from users.', 'formxr'); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Preview Card -->
                <div class="formxr-preview-card" x-show="basicInfo.title.length > 0">
                    <h4><?php _e('Preview', 'formxr'); ?></h4>
                    <div class="formxr-questionnaire-preview">
                        <h3 x-text="basicInfo.title || '<?php _e('Questionnaire Title', 'formxr'); ?>'"></h3>
                        <p x-show="basicInfo.description.length > 0" x-text="basicInfo.description"></p>
                        <div class="formxr-pricing-badges" x-show="basicInfo.enablePricing">
                            <span class="formxr-badge formxr-badge-info">
                                <?php _e('Pricing Enabled', 'formxr'); ?>
                            </span>
                            <span class="formxr-badge formxr-badge-success" x-show="basicInfo.showPriceFrontend">
                                <?php _e('Price Visible to Users', 'formxr'); ?>
                            </span>
                            <span class="formxr-badge formxr-badge-warning" x-show="!basicInfo.showPriceFrontend">
                                <?php _e('Price Hidden from Users', 'formxr'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.formxr-preview-card {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}

.formxr-questionnaire-preview {
    padding: 1rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.formxr-questionnaire-preview h3 {
    margin: 0 0 0.5rem 0;
    color: #2c3e50;
    font-size: 1.25rem;
}

.formxr-questionnaire-preview p {
    margin: 0 0 1rem 0;
    color: #6c757d;
    line-height: 1.5;
}

.formxr-pricing-badge,
.formxr-pricing-badges {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.formxr-required {
    color: #dc3545;
}

.formxr-form-check {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.formxr-form-check-input {
    margin-top: 0.25rem;
}

.formxr-form-check-label {
    flex: 1;
    cursor: pointer;
}

.formxr-form-help {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
    margin-bottom: 0;
}
</style>
