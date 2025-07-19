/**
 * FormXR Questionnaire Builder JavaScript
 * Handles the questionnaire creation interface
 */

function questionnaireBuilder() {
    return {
        currentTab: 'basic',
        saving: false,
        
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
                            options_text: ''
                        }
                    ]
                }
            ],
            conditions: [],
            saved: false
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
                        options_text: ''
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
                options_text: ''
            });
        },

        removeQuestion(stepIndex, questionIndex) {
            if (this.questionnaire.steps[stepIndex].questions.length > 1) {
                this.questionnaire.steps[stepIndex].questions.splice(questionIndex, 1);
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

            template += `

Total Price: {{calculated_price}}
Submission Date: {{submission_date}}

Best regards,
{{site_name}}
{{site_url}}`;

            this.questionnaire.email_template = template;
        },

        async saveQuestionnaire() {
            if (!this.questionnaire.title.trim()) {
                alert('Please enter a questionnaire title');
                this.currentTab = 'basic';
                return;
            }

            // Validate steps
            for (let i = 0; i < this.questionnaire.steps.length; i++) {
                const step = this.questionnaire.steps[i];
                if (!step.title.trim()) {
                    alert(`Please enter a title for Step ${i + 1}`);
                    this.currentTab = 'steps';
                    return;
                }
                
                // Check if step has at least one question with text
                const hasValidQuestion = step.questions.some(q => q.text.trim());
                if (!hasValidQuestion) {
                    alert(`Step ${i + 1} must have at least one question with text`);
                    this.currentTab = 'steps';
                    return;
                }
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
                    console.log('Questionnaire saved successfully:', result.data);
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
        }
    }
}

// Make sure Alpine.js can access the function
window.questionnaireBuilder = questionnaireBuilder;
