<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get questionnaire ID from shortcode attributes  
$questionnaire_id = isset($questionnaire_id) ? intval($questionnaire_id) : 0;

if (!$questionnaire_id) {
    // Get the first active questionnaire if no ID specified
    $questionnaire = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}formxr_questionnaires WHERE status = 'active' ORDER BY created_at DESC LIMIT 1");
    if (!$questionnaire) {
        echo '<p>No active questionnaire found.</p>';
        return;
    }
    $questionnaire_id = $questionnaire->id;
} else {
    // Get specific questionnaire
    $questionnaire = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}formxr_questionnaires WHERE id = %d",
        $questionnaire_id
    ));
    if (!$questionnaire) {
        echo '<p>Questionnaire not found.</p>';
        return;
    }
}

// Get steps for this questionnaire
$steps = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}formxr_steps WHERE questionnaire_id = %d ORDER BY step_order ASC",
    $questionnaire_id
));

if (empty($steps)) {
    echo '<p>No steps found for this questionnaire.</p>';
    return;
}

// Get all questions for all steps
$questions_by_step = array();
foreach ($steps as $step) {
    $questions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}formxr_questions WHERE step_id = %d ORDER BY question_order ASC",
        $step->id
    ));
    $questions_by_step[$step->id] = $questions;
}

// Form settings
$currency = get_option('formxr_currency', 'EUR');
$base_price = get_option('formxr_base_price', 500);
$enable_email = get_option('formxr_enable_email_collection', 1);
$show_price_progress = get_option('formxr_show_price_progress', 1);
$allow_price_toggle = get_option('formxr_allow_price_type_toggle', 1);
$default_price_type = get_option('formxr_default_price_type', 'monthly');
?>

<div class="formxr-form-container" id="formxr-form-container" x-data="formxrForm()">
    <div class="formxr-form-header">
        <h2 class="formxr-form-title"><?php echo esc_html($questionnaire->title); ?></h2>
        <p class="formxr-form-description"><?php echo esc_html($questionnaire->description); ?></p>
        
        <?php if ($show_price_progress): ?>
            <div class="formxr-price-display">
                <div class="price-info">
                    <div class="current-price">
                        <span class="price-label">Estimated Price:</span>
                        <span class="price-amount" x-text="formatPrice(currentPrice)"><?php echo number_format($base_price, 2); ?> <?php echo $currency; ?></span>
                        <span class="price-type" x-text="priceTypeDisplay">/month</span>
                    </div>
                    
                    <?php if ($allow_price_toggle): ?>
                        <div class="price-toggle">
                            <button type="button" @click="togglePriceType()" class="price-toggle-btn">
                                <span x-text="priceType === 'monthly' ? 'Switch to One-time' : 'Switch to Monthly'">Switch to One-time</span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="progress-bar">
                    <div class="progress-fill" :style="'width: ' + progress + '%'"></div>
                </div>
                <div class="progress-text">
                    <span x-text="'Step ' + currentStep + ' of ' + totalSteps">Step 1 of <?php echo count($steps); ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <form id="formxr-form" class="formxr-multi-step-form" @submit.prevent="submitForm()">
        <?php foreach ($steps as $step_index => $step): ?>
            <div class="form-step" x-show="currentStep === <?php echo $step_index + 1; ?>">
                <h3 class="step-title"><?php echo esc_html($step->title); ?></h3>
                <?php if ($step->description): ?>
                    <p class="step-description"><?php echo esc_html($step->description); ?></p>
                <?php endif; ?>
                
                <?php if (isset($questions_by_step[$step->id])): ?>
                    <?php foreach ($questions_by_step[$step->id] as $question): ?>
                        <div class="question-wrapper">
                            <label class="question-label">
                                <?php echo esc_html($question->question_text); ?>
                                <?php if ($question->is_required): ?>
                                    <span class="required">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php
                            $options = $question->options ? json_decode($question->options, true) : array();
                            $question_name = 'question_' . $question->id;
                            
                            switch ($question->question_type) {
                                case 'text':
                                    echo '<input type="text" name="' . $question_name . '" class="form-input" x-model="formData.' . $question_name . '"' . ($question->is_required ? ' required' : '') . '>';
                                    break;
                                    
                                case 'textarea':
                                    echo '<textarea name="' . $question_name . '" class="form-textarea" rows="4" x-model="formData.' . $question_name . '"' . ($question->is_required ? ' required' : '') . '></textarea>';
                                    break;
                                    
                                case 'select':
                                    echo '<select name="' . $question_name . '" class="form-select" x-model="formData.' . $question_name . '"' . ($question->is_required ? ' required' : '') . '>';
                                    echo '<option value="">Choose an option...</option>';
                                    foreach ($options as $option) {
                                        echo '<option value="' . esc_attr($option['value']) . '">' . esc_html($option['label']) . '</option>';
                                    }
                                    echo '</select>';
                                    break;
                                    
                                case 'radio':
                                    foreach ($options as $option) {
                                        echo '<div class="radio-option">';
                                        echo '<input type="radio" name="' . $question_name . '" value="' . esc_attr($option['value']) . '" id="' . $question_name . '_' . $option['value'] . '" x-model="formData.' . $question_name . '"' . ($question->is_required ? ' required' : '') . '>';
                                        echo '<label for="' . $question_name . '_' . $option['value'] . '">' . esc_html($option['label']) . '</label>';
                                        echo '</div>';
                                    }
                                    break;
                                    
                                case 'checkbox':
                                    foreach ($options as $option) {
                                        echo '<div class="checkbox-option">';
                                        echo '<input type="checkbox" name="' . $question_name . '[]" value="' . esc_attr($option['value']) . '" id="' . $question_name . '_' . $option['value'] . '">';
                                        echo '<label for="' . $question_name . '_' . $option['value'] . '">' . esc_html($option['label']) . '</label>';
                                        echo '</div>';
                                    }
                                    break;
                                    
                                case 'number':
                                    echo '<input type="number" name="' . $question_name . '" class="form-input" x-model="formData.' . $question_name . '"' . ($question->is_required ? ' required' : '') . '>';
                                    break;
                                    
                                case 'email':
                                    echo '<input type="email" name="' . $question_name . '" class="form-input" x-model="formData.' . $question_name . '"' . ($question->is_required ? ' required' : '') . '>';
                                    break;
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="step-navigation">
                    <?php if ($step_index > 0): ?>
                        <button type="button" @click="previousStep()" class="btn btn-secondary">Previous</button>
                    <?php endif; ?>
                    
                    <?php if ($step_index < count($steps) - 1): ?>
                        <button type="button" @click="nextStep()" class="btn btn-primary">Next</button>
                    <?php else: ?>
                        <?php if ($enable_email): ?>
                            <div class="email-field">
                                <label for="customer_email">Email Address *</label>
                                <input type="email" name="customer_email" id="customer_email" x-model="formData.customer_email" required class="form-input">
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-success" :disabled="submitting">
                            <span x-show="!submitting">Submit</span>
                            <span x-show="submitting">Submitting...</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </form>
    
    <!-- Results Display -->
    <div x-show="showResults" class="formxr-results">
        <h3>Thank you for your submission!</h3>
        <div class="final-price">
            <strong>Estimated Price: <span x-text="formatPrice(currentPrice)"></span> <span x-text="priceTypeDisplay"></span></strong>
        </div>
    </div>
</div>

<style>
.formxr-form-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.formxr-form-header {
    text-align: center;
    margin-bottom: 30px;
}

.formxr-form-title {
    color: #2c3e50;
    margin-bottom: 10px;
}

.formxr-price-display {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 6px;
    margin: 20px 0;
}

.current-price {
    font-size: 1.2em;
    margin-bottom: 15px;
}

.price-amount {
    font-weight: bold;
    color: #27ae60;
    font-size: 1.4em;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #ecf0f1;
    border-radius: 4px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-fill {
    height: 100%;
    background: #3498db;
    transition: width 0.3s ease;
}

.form-step {
    margin-bottom: 30px;
}

.step-title {
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.question-wrapper {
    margin-bottom: 20px;
}

.question-label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #2c3e50;
}

.required {
    color: #e74c3c;
}

.form-input, .form-textarea, .form-select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.radio-option, .checkbox-option {
    margin: 8px 0;
}

.radio-option input, .checkbox-option input {
    margin-right: 8px;
}

.step-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ecf0f1;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-success {
    background: #27ae60;
    color: white;
}

.btn:hover {
    opacity: 0.9;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.email-field {
    margin: 20px 0;
}

.formxr-results {
    text-align: center;
    padding: 40px 20px;
}

.final-price {
    font-size: 1.3em;
    color: #27ae60;
    margin-top: 20px;
}
</style>

<script>
function formxrForm() {
    return {
        currentStep: 1,
        totalSteps: <?php echo count($steps); ?>,
        priceType: '<?php echo $default_price_type; ?>',
        currentPrice: <?php echo $base_price; ?>,
        basePrice: <?php echo $base_price; ?>,
        currency: '<?php echo $currency; ?>',
        submitting: false,
        showResults: false,
        formData: {},
        
        get progress() {
            return (this.currentStep / this.totalSteps) * 100;
        },
        
        get priceTypeDisplay() {
            return this.priceType === 'monthly' ? '/month' : '';
        },
        
        nextStep() {
            if (this.currentStep < this.totalSteps) {
                this.currentStep++;
                this.updatePrice();
            }
        },
        
        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        
        togglePriceType() {
            this.priceType = this.priceType === 'monthly' ? 'onetime' : 'monthly';
            this.updatePrice();
        },
        
        updatePrice() {
            // Calculate price based on form data
            let price = this.basePrice;
            // Add pricing logic here based on answers
            this.currentPrice = price;
        },
        
        formatPrice(price) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(price) + ' ' + this.currency;
        },
        
        async submitForm() {
            this.submitting = true;
            
            try {
                const formData = new FormData();
                formData.append('action', 'formxr_submit_form');
                formData.append('questionnaire_id', <?php echo $questionnaire_id; ?>);
                formData.append('form_data', JSON.stringify(this.formData));
                formData.append('calculated_price', this.currentPrice);
                formData.append('price_type', this.priceType);
                formData.append('nonce', '<?php echo wp_create_nonce('formxr_submit_form'); ?>');
                
                const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.showResults = true;
                    this.currentStep = 1; // Reset form
                } else {
                    alert('Error submitting form: ' + (result.data || 'Unknown error'));
                }
            } catch (error) {
                alert('Error submitting form: ' + error.message);
            }
            
            this.submitting = false;
        }
    }
}
</script>
                        <div class="price-type-toggle">
                            <label class="toggle-switch">
                                <input type="radio" name="price_type" value="monthly" <?php checked($default_price_type, 'monthly'); ?>>
                                <span>Monthly</span>
                            </label>
                            <label class="toggle-switch">
                                <input type="radio" name="price_type" value="onetime" <?php checked($default_price_type, 'onetime'); ?>>
                                <span>One-time</span>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($steps)): ?>
        <form id="exseo-form" class="exseo-multi-step-form">
            <?php wp_nonce_field('exseo_form_submit', 'exseo_form_nonce'); ?>
            
            <!-- Progress Indicator -->
            <div class="exseo-progress-container">
                <div class="exseo-progress-bar">
                    <div class="progress-fill" id="exseo-progress-fill"></div>
                </div>
                <div class="progress-text">
                    <span id="exseo-current-step">1</span> of <span id="exseo-total-steps"><?php echo count($steps); ?></span>
                </div>
            </div>
            
            <!-- Form Steps -->
            <div class="exseo-form-steps">
                <?php foreach ($steps as $step_number => $step_questions): ?>
                    <div class="exseo-step" data-step="<?php echo $step_number; ?>" <?php echo $step_number == 1 ? 'style="display: block;"' : 'style="display: none;"'; ?>>
                        <div class="step-header">
                            <h3 class="step-title">Step <?php echo $step_number; ?></h3>
                        </div>
                        
                        <div class="step-content">
                            <?php foreach ($step_questions as $question): ?>
                                <?php
                                $question_type = get_post_meta($question->ID, '_exseo_question_type', true);
                                $options = get_post_meta($question->ID, '_exseo_options', true);
                                $required = get_post_meta($question->ID, '_exseo_required', true);
                                ?>
                                
                                <div class="exseo-question" data-question-id="<?php echo $question->ID; ?>">
                                    <label class="question-label">
                                        <?php echo esc_html($question->post_title); ?>
                                        <?php if ($required): ?>
                                            <span class="required">*</span>
                                        <?php endif; ?>
                                    </label>
                                    
                                    <div class="question-input">
                                        <?php if ($question_type === 'text'): ?>
                                            <input type="text" 
                                                   name="question_<?php echo $question->ID; ?>" 
                                                   id="question_<?php echo $question->ID; ?>"
                                                   class="exseo-input"
                                                   <?php echo $required ? 'required' : ''; ?>>
                                        
                                        <?php elseif ($question_type === 'textarea'): ?>
                                            <textarea name="question_<?php echo $question->ID; ?>" 
                                                     id="question_<?php echo $question->ID; ?>"
                                                     class="exseo-textarea"
                                                     rows="4"
                                                     <?php echo $required ? 'required' : ''; ?>></textarea>
                                        
                                        <?php elseif ($question_type === 'select'): ?>
                                            <select name="question_<?php echo $question->ID; ?>" 
                                                   id="question_<?php echo $question->ID; ?>"
                                                   class="exseo-select"
                                                   <?php echo $required ? 'required' : ''; ?>>
                                                <option value="">Choose an option...</option>
                                                <?php
                                                $option_list = explode("\n", $options);
                                                foreach ($option_list as $option) {
                                                    $option = trim($option);
                                                    if (!empty($option)) {
                                                        echo '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        
                                        <?php elseif ($question_type === 'radio'): ?>
                                            <div class="radio-group">
                                                <?php
                                                $option_list = explode("\n", $options);
                                                foreach ($option_list as $index => $option) {
                                                    $option = trim($option);
                                                    if (!empty($option)) {
                                                        echo '<label class="radio-option">';
                                                        echo '<input type="radio" name="question_' . $question->ID . '" value="' . esc_attr($option) . '" ' . ($required ? 'required' : '') . '>';
                                                        echo '<span class="radio-text">' . esc_html($option) . '</span>';
                                                        echo '</label>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        
                                        <?php elseif ($question_type === 'checkbox'): ?>
                                            <div class="checkbox-group">
                                                <?php
                                                $option_list = explode("\n", $options);
                                                foreach ($option_list as $index => $option) {
                                                    $option = trim($option);
                                                    if (!empty($option)) {
                                                        echo '<label class="checkbox-option">';
                                                        echo '<input type="checkbox" name="question_' . $question->ID . '[]" value="' . esc_attr($option) . '">';
                                                        echo '<span class="checkbox-text">' . esc_html($option) . '</span>';
                                                        echo '</label>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        
                                        <?php elseif ($question_type === 'number'): ?>
                                            <input type="number" 
                                                   name="question_<?php echo $question->ID; ?>" 
                                                   id="question_<?php echo $question->ID; ?>"
                                                   class="exseo-input"
                                                   <?php echo $required ? 'required' : ''; ?>>
                                        
                                        <?php elseif ($question_type === 'range'): ?>
                                            <div class="range-input">
                                                <input type="range" 
                                                       name="question_<?php echo $question->ID; ?>" 
                                                       id="question_<?php echo $question->ID; ?>"
                                                       class="exseo-range"
                                                       min="1" max="10" value="5"
                                                       <?php echo $required ? 'required' : ''; ?>>
                                                <div class="range-labels">
                                                    <span>1</span>
                                                    <span class="range-value">5</span>
                                                    <span>10</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($question->post_content): ?>
                                        <div class="question-description">
                                            <?php echo wp_kses_post($question->post_content); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="step-navigation">
                            <?php if ($step_number > 1): ?>
                                <button type="button" class="exseo-btn exseo-btn-secondary" onclick="exseoGoToStep(<?php echo $step_number - 1; ?>)">
                                    Previous
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($step_number < count($steps)): ?>
                                <button type="button" class="exseo-btn exseo-btn-primary" onclick="exseoGoToStep(<?php echo $step_number + 1; ?>)">
                                    Next
                                </button>
                            <?php else: ?>
                                <button type="button" class="exseo-btn exseo-btn-primary" onclick="exseoGoToStep('final')">
                                    Review & Submit
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <!-- Final Review Step -->
                <div class="exseo-step final-step" data-step="final" style="display: none;">
                    <div class="step-header">
                        <h3 class="step-title">Review Your Information</h3>
                    </div>
                    
                    <div class="step-content">
                        <div class="review-container">
                            <div class="review-answers">
                                <h4>Your Answers</h4>
                                <div id="exseo-review-answers"></div>
                            </div>
                            
                            <div class="review-pricing">
                                <h4>Your SEO Package</h4>
                                <div class="pricing-summary">
                                    <div class="final-price">
                                        <span class="price-amount" id="exseo-final-price"><?php echo number_format($base_price, 2); ?> <?php echo $currency; ?></span>
                                        <span class="price-type" id="exseo-final-price-type">/month</span>
                                    </div>
                                    <p class="pricing-description">
                                        Based on your responses, this is your personalized SEO pricing.
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($enable_email): ?>
                                <div class="contact-info">
                                    <h4>Contact Information</h4>
                                    <div class="exseo-question">
                                        <label class="question-label">
                                            Email Address <span class="required">*</span>
                                        </label>
                                        <div class="question-input">
                                            <input type="email" 
                                                   name="user_email" 
                                                   id="user_email"
                                                   class="exseo-input"
                                                   placeholder="your@email.com"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="step-navigation">
                            <button type="button" class="exseo-btn exseo-btn-secondary" onclick="exseoGoToStep(<?php echo count($steps); ?>)">
                                Back to Edit
                            </button>
                            <button type="submit" class="exseo-btn exseo-btn-success" id="exseo-submit-btn">
                                Submit Assessment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Loading State -->
            <div class="exseo-loading" id="exseo-loading" style="display: none;">
                <div class="loading-spinner"></div>
                <p>Processing your assessment...</p>
            </div>
            
            <!-- Success Message -->
            <div class="exseo-success" id="exseo-success" style="display: none;">
                <div class="success-content">
                    <div class="success-icon">✓</div>
                    <h3>Assessment Complete!</h3>
                    <p>Thank you for completing the SEO assessment. We'll be in touch soon with your personalized strategy.</p>
                    <div class="success-price">
                        <strong>Your Price: <span id="exseo-success-price"></span></strong>
                    </div>
                </div>
            </div>
        </form>
        
    <?php else: ?>
        <div class="exseo-no-questions">
            <h3>Form Not Ready</h3>
            <p>No questions have been configured yet. Please contact the administrator.</p>
        </div>
    <?php endif; ?>
</div>
