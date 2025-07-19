/**
 * FormXR Questionnaire Builder JavaScript
 * Advanced tab-based questionnaire builder with pricing integration
 */

function questionnaireBuilder() {
    return {
        currentTab: 'basic',
        saving: false,
        tabs: ['basic', 'steps', 'pricing', 'email', 'conditions'],
        
        questionnaire: {
            id: null,
            title: '',
            description: '',
            pricing_enabled: false,
            email_recipients: '',
            email_subject: '',
            email_template: '',
            notification_enabled: true,
            steps: [
                {
                    title: '',
                    description: '',
                    questions: [
                        {
                            text: '',
                            type: 'text',
                            required: false,
                            options_text: '',
                            pricing: {
                                enabled: false,
                                impact_type: 'add',
                                base_value: 0,
                                option_pricing: {}
                            }
                        }
                    ]
                }
            ],
            pricing: {
                enabled: false,
                currency: 'USD',
                type: 'fixed',
                base_price: 0
            },
            conditions: [],
            saved: false
        },

        get currentTabIndex() {
            return this.tabs.indexOf(this.currentTab);
        },

        get isFirstTab() {
            return this.currentTabIndex === 0;
        },

        get isLastTab() {
            return this.currentTabIndex === this.tabs.length - 1;
        },

        get progressPercentage() {
            return ((this.currentTabIndex + 1) / this.tabs.length) * 100;
        },

        switchToTab(tabId) {
            if (this.tabs.includes(tabId)) {
                this.currentTab = tabId;
            }
        },

        nextTab() {
            if (!this.validateCurrentTab()) {
                return false;
            }

            if (!this.isLastTab) {
                const nextIndex = this.currentTabIndex + 1;
                this.currentTab = this.tabs[nextIndex];
                return true;
            }
            return false; // Last tab reached
        },

        nextTabOrSave() {
            if (this.isLastTab) {
                // Final step - save the questionnaire
                this.saveQuestionnaire();
            } else {
                // Move to next step
                this.nextTab();
            }
        },

        prevTab() {
            if (!this.isFirstTab) {
                const prevIndex = this.currentTabIndex - 1;
                this.currentTab = this.tabs[prevIndex];
            }
        },

        validateCurrentTab() {
            switch (this.currentTab) {
                case 'basic':
                    return this.validateBasicTab();
                case 'steps':
                    return this.validateStepsTab();
                case 'pricing':
                    return this.validatePricingTab();
                case 'email':
                    return this.validateEmailTab();
                case 'conditions':
                    return this.validateConditionsTab();
                default:
                    return true;
            }
        },

        validateBasicTab() {
            if (!this.questionnaire.title.trim()) {
                alert('Please enter a questionnaire title');
                return false;
            }
            return true;
        },

        validateStepsTab() {
            if (this.questionnaire.steps.length === 0) {
                alert('Please add at least one step');
                return false;
            }

            for (let i = 0; i < this.questionnaire.steps.length; i++) {
                const step = this.questionnaire.steps[i];
                if (!step.title.trim()) {
                    alert(`Please enter a title for Step ${i + 1}`);
                    return false;
                }
                
                const hasValidQuestion = step.questions.some(q => q.text.trim());
                if (!hasValidQuestion) {
                    alert(`Step ${i + 1} must have at least one question with text`);
                    return false;
                }
            }
            return true;
        },

        validatePricingTab() {
            if (this.questionnaire.pricing.enabled) {
                if (!this.questionnaire.pricing.currency) {
                    alert('Please select a currency');
                    return false;
                }
                if (this.questionnaire.pricing.base_price < 0) {
                    alert('Base price cannot be negative');
                    return false;
                }
            }
            return true;
        },

        validateEmailTab() {
            if (this.questionnaire.notification_enabled) {
                if (!this.questionnaire.email_recipients.trim()) {
                    alert('Please enter email recipients');
                    return false;
                }
                if (!this.questionnaire.email_subject.trim()) {
                    alert('Please enter an email subject');
                    return false;
                }
            }
            return true;
        },

        validateConditionsTab() {
            // Validate conditions if any
            return true;
        },

        addStep() {
            this.questionnaire.steps.push({
                title: '',
                description: '',
                questions: [
                    {
                        text: '',
                        type: 'text',
                        required: false,
                        options_text: '',
                        pricing: {
                            enabled: false,
                            impact_type: 'add',
                            base_value: 0,
                            option_pricing: {}
                        }
                    }
                ]
            });
        },

        removeStep(stepIndex) {
            if (this.questionnaire.steps.length > 1) {
                this.questionnaire.steps.splice(stepIndex, 1);
            }
        },

        addQuestion(stepIndex) {
            this.questionnaire.steps[stepIndex].questions.push({
                text: '',
                type: 'text',
                required: false,
                options_text: '',
                pricing: {
                    enabled: false,
                    impact_type: 'add',
                    base_value: 0,
                    option_pricing: {}
                }
            });
        },

        removeQuestion(stepIndex, questionIndex) {
            if (this.questionnaire.steps[stepIndex].questions.length > 1) {
                this.questionnaire.steps[stepIndex].questions.splice(questionIndex, 1);
            }
        },

        updateQuestionType(stepIndex, questionIndex, newType) {
            const question = this.questionnaire.steps[stepIndex].questions[questionIndex];
            question.type = newType;
            
            // Clear options if not a multi-option type
            if (!['select', 'radio', 'checkbox'].includes(newType)) {
                question.options_text = '';
            }
        },

        addCondition() {
            this.questionnaire.conditions.push({
                question_id: '',
                value: '',
                goto_step: ''
            });
        },

        removeCondition(conditionIndex) {
            this.questionnaire.conditions.splice(conditionIndex, 1);
        },

        getQuestionPlaceholders() {
            const placeholders = [];
            this.questionnaire.steps.forEach((step, stepIndex) => {
                step.questions.forEach((question, questionIndex) => {
                    if (question.text.trim()) {
                        // Create placeholder based on question text
                        const placeholder = question.text.toLowerCase()
                            .replace(/[^a-z0-9\s]/g, '')
                            .replace(/\s+/g, '_')
                            .substring(0, 30);
                        
                        placeholders.push({
                            key: `question_${stepIndex + 1}_${questionIndex + 1}`,
                            label: question.text,
                            placeholder: `{{${placeholder}}}`,
                            description: `Answer to: ${question.text.substring(0, 50)}${question.text.length > 50 ? '...' : ''}`
                        });
                    }
                });
            });
            return placeholders;
        },

        insertDefaultTemplate() {
            const questionPlaceholders = this.getQuestionPlaceholders();
            let template = `Dear {{user_email}},

Thank you for completing our questionnaire: {{questionnaire_title}}

Here is a summary of your responses:
`;

            questionPlaceholders.forEach(q => {
                template += `\n- ${q.description.replace('Answer to: ', '')}: {{${q.key}}}`;
            });

            if (this.questionnaire.pricing.enabled) {
                template += `\n\nTotal Price: {{calculated_price}}`;
            }

            template += `
Submission Date: {{submission_date}}

Best regards,
{{site_name}}
{{site_url}}`;

            this.questionnaire.email_template = template;
        },

        async saveQuestionnaire() {
            // Validate all tabs before saving
            for (let tab of this.tabs) {
                const currentTab = this.currentTab;
                this.currentTab = tab;
                if (!this.validateCurrentTab()) {
                    return;
                }
                this.currentTab = currentTab;
            }

            this.saving = true;

            try {
                // Process questions options
                this.questionnaire.steps.forEach(step => {
                    step.questions.forEach(question => {
                        if (question.options_text && ['select', 'radio', 'checkbox'].includes(question.type)) {
                            question.options = question.options_text.split('\n').filter(opt => opt.trim());
                        }
                    });
                });

                // Clean up data before sending
                const cleanedQuestionnaire = {
                    ...this.questionnaire,
                    steps: this.questionnaire.steps.map(step => ({
                        ...step,
                        questions: step.questions.filter(q => q.text.trim()) // Only include questions with text
                    })).filter(step => step.title.trim()) // Only include steps with titles
                };

                const formData = new FormData();
                formData.append('action', 'formxr_save_complete_questionnaire');
                formData.append('nonce', formxr_admin_ajax.nonce);
                formData.append('questionnaire_data', JSON.stringify(cleanedQuestionnaire));

                const response = await fetch(formxr_admin_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    this.questionnaire.id = result.data.id;
                    this.questionnaire.saved = true;
                    alert('Questionnaire saved successfully!');
                    
                    // Optionally redirect to questionnaires list
                    if (confirm('Would you like to go back to the questionnaires list?')) {
                        window.location.href = formxr_admin_ajax.questionnaires_url || 'admin.php?page=formxr-questionnaires';
                    }
                } else {
                    console.error('Save error:', result);
                    alert('Error: ' + (result.data?.message || result.data || 'Failed to save questionnaire'));
                }
            } catch (error) {
                console.error('Save error:', error);
                alert('Error saving questionnaire: ' + error.message);
            } finally {
                this.saving = false;
            }
        },

        copyShortcode() {
            const shortcode = `[formxr_form id="${this.questionnaire.id}"]`;
            navigator.clipboard.writeText(shortcode).then(() => {
                alert('Shortcode copied to clipboard!');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = shortcode;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Shortcode copied to clipboard!');
            });
        },

        // Pricing-related methods
        togglePricing() {
            this.questionnaire.pricing.enabled = !this.questionnaire.pricing.enabled;
        },

        toggleQuestionPricing(stepIndex, questionIndex) {
            const question = this.questionnaire.steps[stepIndex].questions[questionIndex];
            question.pricing.enabled = !question.pricing.enabled;
        },

        updatePricingImpact(stepIndex, questionIndex, impactType) {
            const question = this.questionnaire.steps[stepIndex].questions[questionIndex];
            question.pricing.impact_type = impactType;
        },

        // Helper method to format currency
        formatCurrency(amount) {
            const currency = this.questionnaire.pricing.currency || 'USD';
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: currency
            }).format(amount);
        },

        // Method to get available questions for conditions
        getAvailableQuestions() {
            const questions = [];
            this.questionnaire.steps.forEach((step, stepIndex) => {
                step.questions.forEach((question, questionIndex) => {
                    if (question.text.trim()) {
                        questions.push({
                            id: `step_${stepIndex}_question_${questionIndex}`,
                            text: question.text,
                            step: stepIndex + 1,
                            type: question.type
                        });
                    }
                });
            });
            return questions;
        }
    }
}

// Make sure Alpine.js can access the function
window.questionnaireBuilder = questionnaireBuilder;

// Additional jQuery-based enhancements
(function($) {
    'use strict';

    $(document).ready(function() {
        // Add any additional jQuery-based functionality here
        
        // Auto-resize textareas
        $(document).on('input', 'textarea', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Add option functionality for multi-option questions
        $(document).on('click', '.add-option-btn', function() {
            const container = $(this).prev('.options-container');
            const optionCount = container.children().length;
            const newOption = `
                <div class="formxr-form-group">
                    <div class="formxr-d-flex formxr-align-items-center">
                        <input type="text" class="formxr-form-control" placeholder="Option ${optionCount + 1}">
                        <button type="button" class="formxr-btn formxr-btn-sm formxr-btn-danger remove-option-btn" style="margin-left: 10px;">Remove</button>
                    </div>
                </div>
            `;
            container.append(newOption);
        });

        // Remove option functionality
        $(document).on('click', '.remove-option-btn', function() {
            $(this).closest('.formxr-form-group').remove();
        });

        // Tab keyboard navigation
        $(document).on('keydown', '.formxr-tab-button', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).click();
            }
        });

        // Form validation styling
        $(document).on('blur', '.formxr-form-control[required]', function() {
            if (!$(this).val().trim()) {
                $(this).addClass('formxr-error');
            } else {
                $(this).removeClass('formxr-error');
            }
        });
    });

})(jQuery);
