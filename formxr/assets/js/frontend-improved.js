/**
 * FormXR Frontend JavaScript - Improved
 * Handles multi-step forms, conditional logic, and price calculations
 */

(function($) {
    'use strict';
    
    // Global FormXR Frontend object
    window.FormXRFrontend = {
        
        // Configuration
        config: {
            currentStep: 1,
            totalSteps: 0,
            formData: {},
            currentPriceType: 'monthly',
            priceCalculation: {
                basePrice: 0,
                multipliers: {},
                conditions: []
            }
        },
        
        // Initialize frontend functionality
        init: function() {
            this.initializeForm();
            this.bindEvents();
            this.updateDisplay();
        },
        
        // Initialize form structure
        initializeForm: function() {
            const $steps = $('.formxr-step:not(.formxr-final-step)');
            this.config.totalSteps = $steps.length;
            
            // Set up initial state
            $steps.removeClass('active').first().addClass('active');
            
            // Initialize progress
            this.updateProgressBar();
            
            // Set price type
            const checkedPriceType = $('input[name="price_type"]:checked').val();
            if (checkedPriceType) {
                this.config.currentPriceType = checkedPriceType;
            }
            
            // Initialize form controls
            this.initializeControls();
        },
        
        // Initialize form controls
        initializeControls: function() {
            // Range sliders
            $('.formxr-range').each((index, element) => {
                this.updateRangeValue(element);
            });
            
            // File uploads
            this.initFileUploads();
            
            // Conditional logic
            this.initConditionalLogic();
        },
        
        // Bind event handlers
        bindEvents: function() {
            // Navigation buttons
            $(document).on('click', '.formxr-next-btn', (e) => {
                e.preventDefault();
                this.nextStep();
            });
            
            $(document).on('click', '.formxr-prev-btn', (e) => {
                e.preventDefault();
                this.prevStep();
            });
            
            // Form input changes
            $(document).on('change input', '.formxr-form input, .formxr-form select, .formxr-form textarea', (e) => {
                this.handleInputChange(e);
            });
            
            // Price type toggle
            $(document).on('change', 'input[name="price_type"]', (e) => {
                this.config.currentPriceType = $(e.target).val();
                this.updatePriceDisplay();
            });
            
            // Range sliders
            $(document).on('input', '.formxr-range', (e) => {
                this.updateRangeValue(e.target);
                this.handleInputChange(e);
            });
            
            // Form submission
            $(document).on('submit', '.formxr-form', (e) => {
                this.handleFormSubmit(e);
            });
            
            // Option selection
            $(document).on('click', '.formxr-option', (e) => {
                this.handleOptionClick(e);
            });
        },
        
        // Handle input changes
        handleInputChange: function(e) {
            const $input = $(e.target);
            const $question = $input.closest('.formxr-question');
            const questionId = $question.data('question-id');
            
            if (questionId) {
                const value = this.getInputValue($input);
                this.config.formData[questionId] = value;
                
                // Trigger conditional logic
                this.evaluateConditionalLogic();
                
                // Update price calculation
                this.updatePriceCalculation();
                
                // Validate current step
                this.validateCurrentStep();
            }
        },
        
        // Get input value based on type
        getInputValue: function($input) {
            const type = $input.attr('type');
            const tagName = $input.prop('tagName').toLowerCase();
            
            if (type === 'checkbox') {
                return $input.is(':checked') ? $input.val() : null;
            } else if (type === 'radio') {
                const name = $input.attr('name');
                return $(`input[name="${name}"]:checked`).val() || null;
            } else if (tagName === 'select' && $input.prop('multiple')) {
                return $input.val() || [];
            } else {
                return $input.val();
            }
        },
        
        // Handle option clicks (for custom radio/checkbox styling)
        handleOptionClick: function(e) {
            const $option = $(e.currentTarget);
            const $input = $option.find('input');
            const type = $input.attr('type');
            
            if (type === 'radio') {
                // Clear other selections in the same group
                const name = $input.attr('name');
                $(`.formxr-option input[name="${name}"]`).closest('.formxr-option').removeClass('selected');
                $option.addClass('selected');
                $input.prop('checked', true).trigger('change');
            } else if (type === 'checkbox') {
                $option.toggleClass('selected');
                $input.prop('checked', !$input.prop('checked')).trigger('change');
            }
        },
        
        // Navigation methods
        nextStep: function() {
            if (!this.validateCurrentStep()) {
                this.showValidationErrors();
                return;
            }
            
            if (this.config.currentStep < this.config.totalSteps) {
                this.showStep(this.config.currentStep + 1);
            } else {
                this.submitForm();
            }
        },
        
        prevStep: function() {
            if (this.config.currentStep > 1) {
                this.showStep(this.config.currentStep - 1);
            }
        },
        
        showStep: function(stepNumber) {
            const $currentStep = $(`.formxr-step:nth-child(${this.config.currentStep})`);
            const $nextStep = $(`.formxr-step:nth-child(${stepNumber})`);
            
            // Animate transition
            $currentStep.fadeOut(200, () => {
                $currentStep.removeClass('active');
                $nextStep.addClass('active').fadeIn(200);
                
                this.config.currentStep = stepNumber;
                this.updateProgressBar();
                this.updateNavigationButtons();
                
                // Scroll to top
                $('html, body').animate({
                    scrollTop: $('.formxr-form-container').offset().top - 50
                }, 300);
            });
        },
        
        // Validation
        validateCurrentStep: function() {
            const $currentStep = $(`.formxr-step.active`);
            const $requiredInputs = $currentStep.find('input[required], select[required], textarea[required]');
            let isValid = true;
            
            $requiredInputs.each((index, element) => {
                const $input = $(element);
                const value = this.getInputValue($input);
                
                if (!value || (Array.isArray(value) && value.length === 0)) {
                    isValid = false;
                    $input.addClass('formxr-error');
                } else {
                    $input.removeClass('formxr-error');
                }
            });
            
            return isValid;
        },
        
        showValidationErrors: function() {
            this.showNotice('Please fill in all required fields.', 'error');
        },
        
        // Update displays
        updateProgressBar: function() {
            const percentage = ((this.config.currentStep - 1) / this.config.totalSteps) * 100;
            
            $('.formxr-step-counter').text(`Step ${this.config.currentStep} of ${this.config.totalSteps}`);
            $('.formxr-progress-percentage').text(`${Math.round(percentage)}%`);
            $('.formxr-progress-bar-fill').css('width', `${percentage}%`);
        },
        
        updateNavigationButtons: function() {
            const $prevBtn = $('.formxr-prev-btn');
            const $nextBtn = $('.formxr-next-btn');
            
            // Previous button
            if (this.config.currentStep === 1) {
                $prevBtn.hide();
            } else {
                $prevBtn.show();
            }
            
            // Next button text
            if (this.config.currentStep === this.config.totalSteps) {
                $nextBtn.text($nextBtn.data('submit-text') || 'Submit');
            } else {
                $nextBtn.text($nextBtn.data('next-text') || 'Next');
            }
        },
        
        updateDisplay: function() {
            this.updateProgressBar();
            this.updateNavigationButtons();
            this.updatePriceDisplay();
        },
        
        // Range slider handling
        updateRangeValue: function(rangeElement) {
            const $range = $(rangeElement);
            const $valueDisplay = $range.siblings('.formxr-range-value');
            const value = $range.val();
            const prefix = $range.data('prefix') || '';
            const suffix = $range.data('suffix') || '';
            
            $valueDisplay.text(prefix + value + suffix);
        },
        
        // Price calculation
        updatePriceCalculation: function() {
            let totalPrice = this.config.priceCalculation.basePrice;
            
            // Apply multipliers based on form data
            Object.keys(this.config.formData).forEach(questionId => {
                const value = this.config.formData[questionId];
                const multiplier = this.config.priceCalculation.multipliers[questionId];
                
                if (multiplier && value) {
                    if (typeof multiplier === 'object') {
                        // Value-specific multipliers
                        if (multiplier[value]) {
                            totalPrice += multiplier[value];
                        }
                    } else {
                        // Simple numeric multiplier
                        totalPrice += (parseFloat(value) || 0) * multiplier;
                    }
                }
            });
            
            this.updatePriceDisplay(totalPrice);
        },
        
        updatePriceDisplay: function(price = null) {
            if (price === null) {
                this.updatePriceCalculation();
                return;
            }
            
            const $priceDisplay = $('.formxr-price-amount');
            const currency = $priceDisplay.data('currency') || '$';
            const period = this.config.currentPriceType === 'monthly' ? '/month' : '/year';
            
            // Adjust price for period
            const displayPrice = this.config.currentPriceType === 'yearly' ? price * 12 * 0.8 : price; // 20% discount for yearly
            
            $priceDisplay.text(currency + displayPrice.toFixed(2));
            $('.formxr-price-period').text(period);
        },
        
        // Conditional logic
        initConditionalLogic: function() {
            // Initialize with server-provided conditions
            if (window.formxr_conditions) {
                this.config.priceCalculation.conditions = window.formxr_conditions;
            }
        },
        
        evaluateConditionalLogic: function() {
            this.config.priceCalculation.conditions.forEach(condition => {
                const questionValue = this.config.formData[condition.question_id];
                const conditionMet = this.evaluateCondition(questionValue, condition.operator, condition.value);
                
                // Show/hide target elements
                const $target = $(`.formxr-question[data-question-id="${condition.target_question_id}"]`);
                
                if (conditionMet) {
                    $target.show().addClass('formxr-condition-met');
                } else {
                    $target.hide().removeClass('formxr-condition-met');
                    // Clear hidden field values
                    $target.find('input, select, textarea').val('').prop('checked', false);
                }
            });
        },
        
        evaluateCondition: function(value, operator, conditionValue) {
            switch (operator) {
                case 'equals':
                    return value == conditionValue;
                case 'not_equals':
                    return value != conditionValue;
                case 'greater_than':
                    return parseFloat(value) > parseFloat(conditionValue);
                case 'less_than':
                    return parseFloat(value) < parseFloat(conditionValue);
                case 'contains':
                    return Array.isArray(value) ? value.includes(conditionValue) : String(value).includes(conditionValue);
                default:
                    return false;
            }
        },
        
        // File uploads
        initFileUploads: function() {
            $('.formxr-file-upload').each((index, element) => {
                const $upload = $(element);
                const $input = $upload.find('input[type="file"]');
                const $preview = $upload.find('.formxr-file-preview');
                
                $input.on('change', (e) => {
                    const files = e.target.files;
                    $preview.empty();
                    
                    Array.from(files).forEach(file => {
                        const $fileItem = $(`
                            <div class="formxr-file-item">
                                <span class="formxr-file-name">${file.name}</span>
                                <span class="formxr-file-size">${this.formatFileSize(file.size)}</span>
                            </div>
                        `);
                        $preview.append($fileItem);
                    });
                });
            });
        },
        
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        // Form submission
        handleFormSubmit: function(e) {
            e.preventDefault();
            
            if (!this.validateCurrentStep()) {
                this.showValidationErrors();
                return;
            }
            
            this.submitForm();
        },
        
        submitForm: function() {
            const $form = $('.formxr-form');
            const $submitBtn = $('.formxr-next-btn');
            
            // Show loading state
            $submitBtn.prop('disabled', true).html('<span class="formxr-spinner"></span> Submitting...');
            
            // Prepare form data
            const submissionData = {
                action: 'formxr_submit_form',
                nonce: formxr_frontend.nonce,
                questionnaire_id: $form.data('questionnaire-id'),
                form_data: this.config.formData,
                calculated_price: this.calculateFinalPrice()
            };
            
            $.ajax({
                url: formxr_frontend.ajax_url,
                type: 'POST',
                data: submissionData,
                success: (response) => {
                    if (response.success) {
                        this.showSuccessStep(response.data);
                    } else {
                        this.showError(response.data.message || 'Submission failed. Please try again.');
                        $submitBtn.prop('disabled', false).text('Submit');
                    }
                },
                error: () => {
                    this.showError('Connection error. Please check your internet connection and try again.');
                    $submitBtn.prop('disabled', false).text('Submit');
                }
            });
        },
        
        showSuccessStep: function(data) {
            $('.formxr-step.active').removeClass('active');
            $('.formxr-final-step').addClass('active').fadeIn();
            
            // Hide navigation
            $('.formxr-navigation').hide();
            
            // Update progress to 100%
            $('.formxr-progress-bar-fill').css('width', '100%');
            $('.formxr-progress-percentage').text('100%');
        },
        
        calculateFinalPrice: function() {
            // This would implement the actual price calculation logic
            // For now, return a basic calculation
            return this.config.priceCalculation.basePrice;
        },
        
        // Utility methods
        showNotice: function(message, type = 'info', duration = 5000) {
            const $notice = $(`<div class="formxr-notice formxr-notice-${type}">${message}</div>`);
            
            $('.formxr-form-container').prepend($notice);
            
            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, duration);
        },
        
        showError: function(message) {
            this.showNotice(message, 'error');
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        if ($('.formxr-form').length) {
            FormXRFrontend.init();
        }
    });
    
})(jQuery);
