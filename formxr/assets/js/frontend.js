/**
 * ExScopeXR Frontend JavaScript
 * Handles multi-step form navigation, price calculation, and form submission
 */

(function($) {
    'use strict';
    
    let currentStep = 1;
    let totalSteps = 0;
    let formData = {};
    let currentPriceType = 'monthly';
    
    $(document).ready(function() {
        initializeForm();
        bindEvents();
        updatePriceDisplay();
    });
    
    function initializeForm() {
        totalSteps = $('.exseo-step:not(.final-step)').length;
        $('#exseo-total-steps').text(totalSteps);
        updateProgressBar();
        
        // Set initial price type
        const checkedPriceType = $('input[name="price_type"]:checked').val();
        if (checkedPriceType) {
            currentPriceType = checkedPriceType;
        }
        
        // Initialize range sliders
        $('.exseo-range').each(function() {
            updateRangeValue(this);
        });
    }
    
    function bindEvents() {
        // Price type toggle
        $('input[name="price_type"]').on('change', function() {
            currentPriceType = $(this).val();
            updatePriceDisplay();
        });
        
        // Form input changes
        $('.exseo-form-steps').on('change', 'input, select, textarea', function() {
            const questionId = $(this).closest('.exseo-question').data('question-id');
            const value = getInputValue($(this));
            
            if (questionId && value !== null) {
                formData[questionId] = value;
                updatePriceDisplay();
            }
        });
        
        // Range slider updates
        $('.exseo-range').on('input', function() {
            updateRangeValue(this);
            const questionId = $(this).closest('.exseo-question').data('question-id');
            if (questionId) {
                formData[questionId] = $(this).val();
                updatePriceDisplay();
            }
        });
        
        // Form submission
        $('#exseo-form').on('submit', function(e) {
            e.preventDefault();
            submitForm();
        });
        
        // Keyboard navigation
        $(document).on('keydown', function(e) {
            if (e.which === 37) { // Left arrow
                goToPreviousStep();
            } else if (e.which === 39) { // Right arrow
                goToNextStep();
            }
        });
    }
    
    function getInputValue($input) {
        const type = $input.attr('type');
        const tagName = $input.prop('tagName').toLowerCase();
        
        if (type === 'checkbox') {
            const questionContainer = $input.closest('.exseo-question');
            const checkedValues = [];
            questionContainer.find('input[type="checkbox"]:checked').each(function() {
                checkedValues.push($(this).val());
            });
            return checkedValues.length > 0 ? checkedValues.join(', ') : null;
        } else if (type === 'radio') {
            const questionContainer = $input.closest('.exseo-question');
            const checkedValue = questionContainer.find('input[type="radio"]:checked').val();
            return checkedValue || null;
        } else if (tagName === 'select' || type === 'text' || type === 'email' || type === 'number' || type === 'range' || tagName === 'textarea') {
            const value = $input.val().trim();
            return value !== '' ? value : null;
        }
        
        return null;
    }
    
    function updateRangeValue(rangeInput) {
        const $range = $(rangeInput);
        const value = $range.val();
        $range.siblings('.range-labels').find('.range-value').text(value);
    }
    
    // Global function for step navigation (called from template)
    window.exseoGoToStep = function(step) {
        if (step === 'final') {
            goToFinalStep();
        } else if (typeof step === 'number') {
            goToStep(step);
        }
    };
    
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        
        // Validate current step before moving
        if (step > currentStep && !validateCurrentStep()) {
            return;
        }
        
        // Hide current step
        $('.exseo-step[data-step="' + currentStep + '"]').hide();
        
        // Show target step
        $('.exseo-step[data-step="' + step + '"]').show();
        
        currentStep = step;
        $('#exseo-current-step').text(currentStep);
        updateProgressBar();
        
        // Scroll to top of form
        $('html, body').animate({
            scrollTop: $('#exseo-form-container').offset().top - 20
        }, 300);
    }
    
    function goToFinalStep() {
        if (!validateCurrentStep()) {
            return;
        }
        
        // Hide current step
        $('.exseo-step[data-step="' + currentStep + '"]').hide();
        
        // Show final step
        $('.exseo-step.final-step').show();
        
        // Update progress
        $('#exseo-current-step').text('Review');
        updateProgressBar(100);
        
        // Populate review
        populateReview();
        
        // Scroll to top
        $('html, body').animate({
            scrollTop: $('#exseo-form-container').offset().top - 20
        }, 300);
    }
    
    function goToPreviousStep() {
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    }
    
    function goToNextStep() {
        if (currentStep < totalSteps) {
            goToStep(currentStep + 1);
        } else {
            goToFinalStep();
        }
    }
    
    function validateCurrentStep() {
        const $currentStepElement = $('.exseo-step[data-step="' + currentStep + '"]');
        const $requiredInputs = $currentStepElement.find('input[required], select[required], textarea[required]');
        
        let isValid = true;
        
        $requiredInputs.each(function() {
            const $input = $(this);
            const value = getInputValue($input);
            
            if (!value) {
                isValid = false;
                $input.addClass('error');
                
                // Show error message
                if (!$input.siblings('.error-message').length) {
                    $input.after('<span class="error-message">This field is required</span>');
                }
            } else {
                $input.removeClass('error');
                $input.siblings('.error-message').remove();
            }
        });
        
        if (!isValid) {
            // Scroll to first error
            const $firstError = $currentStepElement.find('.error').first();
            if ($firstError.length) {
                $('html, body').animate({
                    scrollTop: $firstError.offset().top - 100
                }, 300);
            }
        }
        
        return isValid;
    }
    
    function updateProgressBar(percentage) {
        if (percentage === undefined) {
            percentage = (currentStep / totalSteps) * 100;
        }
        
        $('#exseo-progress-fill').css('width', percentage + '%');
    }
    
    function populateReview() {
        const $reviewContainer = $('#exseo-review-answers');
        $reviewContainer.empty();
        
        // Get all questions and their answers
        $('.exseo-question').each(function() {
            const $question = $(this);
            const questionId = $question.data('question-id');
            const questionTitle = $question.find('.question-label').text().replace('*', '').trim();
            const answer = formData[questionId];
            
            if (answer) {
                const $reviewItem = $('<div class="review-item">' +
                    '<div class="review-question">' + escapeHtml(questionTitle) + '</div>' +
                    '<div class="review-answer">' + escapeHtml(answer) + '</div>' +
                '</div>');
                
                $reviewContainer.append($reviewItem);
            }
        });
        
        // Update final price display
        updatePriceDisplay(true);
    }
    
    function updatePriceDisplay(isFinal = false) {
        if (Object.keys(formData).length === 0) {
            return; // No answers yet
        }
        
        const data = {
            action: 'exseo_calculate_price',
            answers: formData,
            price_type: currentPriceType,
            nonce: exseo_ajax.nonce
        };
        
        $.ajax({
            url: exseo_ajax.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    const priceText = response.data.formatted_price;
                    const priceAmount = response.data.price;
                    const currency = exseo_ajax.currency;
                    const suffix = currentPriceType === 'monthly' ? '/month' : ' one-time';
                    
                    // Update regular price display
                    $('#exseo-current-price').text(priceAmount + ' ' + currency);
                    $('#exseo-price-type-display').text(suffix);
                    
                    // Update final price displays if on final step
                    if (isFinal) {
                        $('#exseo-final-price').text(priceAmount + ' ' + currency);
                        $('#exseo-final-price-type').text(suffix);
                    }
                }
            },
            error: function() {
                console.log('Error calculating price');
            }
        });
    }
    
    function submitForm() {
        // Add email to form data if enabled
        const email = $('#user_email').val();
        if (email) {
            formData.email = email;
        }
        
        // Validate final step
        if (!validateFinalStep()) {
            return;
        }
        
        // Show loading
        showLoading();
        
        const data = {
            action: 'exseo_submit_form',
            answers: formData,
            price_type: currentPriceType,
            email: email,
            nonce: exseo_ajax.nonce
        };
        
        $.ajax({
            url: exseo_ajax.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    showSuccess(response.data.formatted_price);
                } else {
                    showError(response.data || 'An error occurred. Please try again.');
                }
            },
            error: function() {
                hideLoading();
                showError('Network error. Please check your connection and try again.');
            }
        });
    }
    
    function validateFinalStep() {
        const email = $('#user_email').val();
        
        if ($('#user_email').attr('required') && (!email || !isValidEmail(email))) {
            $('#user_email').addClass('error');
            if (!$('#user_email').siblings('.error-message').length) {
                $('#user_email').after('<span class="error-message">Please enter a valid email address</span>');
            }
            return false;
        }
        
        $('#user_email').removeClass('error');
        $('#user_email').siblings('.error-message').remove();
        return true;
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function showLoading() {
        $('.exseo-step.final-step').hide();
        $('#exseo-loading').show();
    }
    
    function hideLoading() {
        $('#exseo-loading').hide();
    }
    
    function showSuccess(price) {
        $('#exseo-success-price').text(price);
        $('#exseo-success').show();
    }
    
    function showError(message) {
        $('.exseo-step.final-step').show();
        alert('Error: ' + message);
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
})(jQuery);
