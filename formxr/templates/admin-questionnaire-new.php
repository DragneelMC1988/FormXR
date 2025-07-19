<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap formxr-questionnaire-builder" x-data="questionnaireBuilder()" x-cloak>  
    <div class="formxr-page-header">
        <div class="formxr-page-title">
            <h1>
                <span class="dashicons dashicons-plus-alt"></span>
                <?php _e('Create New Questionnaire', 'formxr'); ?>
            </h1>
            <div class="formxr-header-actions">
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="btn-formxr btn-outline">
                    <?php _e('← Back to List', 'formxr'); ?>
                </a>
            </div>
        </div>
    </div>

    <div class="formxr-container">
        <!-- Success Message -->
        <div x-show="questionnaire.saved" class="notice notice-success">
            <p><strong><?php _e('Questionnaire saved successfully!', 'formxr'); ?></strong></p>
            <div class="shortcode-display">
                <?php _e('Shortcode:', 'formxr'); ?> <strong>[formxr_form id="<span x-text="questionnaire.id"></span>"]</strong>
                <button @click="copyShortcode()" class="btn-formxr btn-small"><?php _e('Copy', 'formxr'); ?></button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="builder-tabs">
            <button class="tab-button" :class="{ active: currentTab === 'basic' }" @click="currentTab = 'basic'">
                Step 1: Basic Info
            </button>
            <button class="tab-button" :class="{ active: currentTab === 'steps' }" @click="currentTab = 'steps'" :disabled="!questionnaire.title">
                Step 2: Configure Steps
            </button>
            <button class="tab-button" :class="{ active: currentTab === 'email' }" @click="currentTab = 'email'" :disabled="questionnaire.steps.length === 0">
                Step 3: Email Template
            </button>
            <button class="tab-button" :class="{ active: currentTab === 'conditions' }" @click="currentTab = 'conditions'" :disabled="questionnaire.steps.length === 0">
                Step 4: Add Conditions
            </button>
        </div>

        <!-- Content -->
        <div class="builder-content">
            <!-- Step 1: Basic Info -->
            <div x-show="currentTab === 'basic'">
                <div class="form-group">
                    <label class="form-label">Questionnaire Title *</label>
                    <input type="text" class="form-input" x-model="questionnaire.title" placeholder="Enter questionnaire title">
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" x-model="questionnaire.description" placeholder="Enter questionnaire description"></textarea>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="enable-pricing" x-model="questionnaire.pricing_enabled">
                        <label for="enable-pricing">Enable Pricing</label>
                    </div>
                </div>
            </div>

            <!-- Step 2: Configure Steps -->
            <div x-show="currentTab === 'steps'">
                <template x-for="(step, stepIndex) in questionnaire.steps" :key="stepIndex">
                    <div class="step-section">
                        <div class="step-header">
                            <div class="step-title">Step <span x-text="stepIndex + 1"></span></div>
                            <button @click="removeStep(stepIndex)" class="btn-formxr btn-danger" x-show="questionnaire.steps.length > 1">
                                Remove Step
                            </button>
                        </div>
                        
                        <div class="step-content">
                            <div class="form-group">
                                <label class="form-label">Step Title *</label>
                                <input type="text" class="form-input" x-model="step.title" placeholder="Enter step title">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Step Description</label>
                                <textarea class="form-textarea" x-model="step.description" placeholder="Enter step description"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Questions</label>
                                <div class="questions-list">
                                    <template x-for="(question, questionIndex) in step.questions" :key="questionIndex">
                                        <div class="question-item">
                                            <div class="question-fields">
                                                <div class="question-row">
                                                    <div>
                                                        <input type="text" class="form-input" x-model="question.text" placeholder="Question Label">
                                                    </div>
                                                    <div>
                                                        <select class="form-select" x-model="question.type">
                                                            <option value="text">Text</option>
                                                            <option value="textarea">Textarea</option>
                                                            <option value="email">Email</option>
                                                            <option value="checkbox">Checkbox</option>
                                                            <option value="radio">Radio</option>
                                                            <option value="select">Select</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <div class="checkbox-group">
                                                            <input type="checkbox" :id="'required-' + stepIndex + '-' + questionIndex" x-model="question.required">
                                                            <label :for="'required-' + stepIndex + '-' + questionIndex">Required</label>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <button @click="removeQuestion(stepIndex, questionIndex)" class="btn-formxr btn-danger">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <!-- Options for select, radio, checkbox -->
                                                <div x-show="['select', 'radio', 'checkbox'].includes(question.type)">
                                                    <label class="form-label">Options (one per line)</label>
                                                    <textarea class="form-textarea" x-model="question.options_text" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <button @click="addQuestion(stepIndex)" class="btn-formxr btn-outline">Add Question</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="formxr-step-actions">
                    <button @click="addStep()" class="btn-formxr btn-outline">Add Another Step</button>
                </div>
            </div>

            <!-- Step 3: Email Template -->
            <div x-show="currentTab === 'email'">
                <div class="form-group">
                    <label class="form-label">Email Recipients *</label>
                    <textarea class="form-textarea" x-model="questionnaire.email_recipients" placeholder="Enter email addresses separated by commas (e.g., admin@example.com, manager@example.com)"></textarea>
                    <small class="formxr-help-text">Leave empty to use the default admin email</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Subject</label>
                    <input type="text" class="form-input" x-model="questionnaire.email_subject" placeholder="New submission for {{questionnaire_title}}">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Template</label>
                    <textarea class="form-textarea" x-model="questionnaire.email_template" rows="10" placeholder="Use placeholders like {{user_email}}, {{questionnaire_title}}, etc."></textarea>
                    <small class="formxr-help-text">Leave empty to use the default template</small>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="enable-notifications" x-model="questionnaire.notification_enabled">
                        <label for="enable-notifications">Enable Email Notifications</label>
                    </div>
                </div>

                <!-- Available Placeholders -->
                <div class="conditions-section">
                    <h3>Available Placeholders</h3>
                    <p>You can use these placeholders in your email template:</p>
                    
                    <!-- System Placeholders -->
                    <div class="placeholder-section">
                        <h4>System Placeholders:</h4>
                        <div class="placeholder-grid">
                            <div class="placeholder-item">
                                <code>{{questionnaire_title}}</code> - Questionnaire title
                            </div>
                            <div class="placeholder-item">
                                <code>{{user_email}}</code> - User's email address
                            </div>
                            <div class="placeholder-item">
                                <code>{{calculated_price}}</code> - Calculated price
                            </div>
                            <div class="placeholder-item">
                                <code>{{submission_date}}</code> - Submission date
                            </div>
                            <div class="placeholder-item">
                                <code>{{site_name}}</code> - Website name
                            </div>
                            <div class="placeholder-item">
                                <code>{{site_url}}</code> - Website URL
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Question Placeholders -->
                    <div x-show="getQuestionPlaceholders().length > 0">
                        <h4>Question Placeholders:</h4>
                        <div class="placeholder-grid placeholder-grid-wide">
                            <template x-for="placeholder in getQuestionPlaceholders()" :key="placeholder.key">
                                <div class="placeholder-item">
                                    <code x-text="'{{' + placeholder.key + '}}'"></code> - <span x-text="placeholder.label"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="template-actions">
                        <button @click="insertDefaultTemplate()" class="btn-formxr btn-outline">Insert Default Template</button>
                    </div>
                </div>
            </div>

            <!-- Step 4: Add Conditions -->
            <div x-show="currentTab === 'conditions'">
                <div class="conditions-section">
                    <h3>Conditional Logic</h3>
                    <p>Add conditions to control the flow of your questionnaire.</p>
                    
                    <template x-for="(condition, conditionIndex) in questionnaire.conditions" :key="conditionIndex">
                        <div class="condition-item">
                            <div>
                                <label class="form-label">If Question</label>
                                <select class="form-select" x-model="condition.question_id">
                                    <option value="">Select question...</option>
                                    <template x-for="(step, stepIndex) in questionnaire.steps" :key="stepIndex">
                                        <template x-for="(question, questionIndex) in step.questions" :key="questionIndex">
                                            <option :value="stepIndex + '-' + questionIndex" x-text="'Step ' + (stepIndex + 1) + ': ' + question.text"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Equals</label>
                                <input type="text" class="form-input" x-model="condition.value" placeholder="Value">
                            </div>
                            <div>
                                <label class="form-label">Go to Step</label>
                                <select class="form-select" x-model="condition.goto_step">
                                    <option value="">Select step...</option>
                                    <template x-for="(step, stepIndex) in questionnaire.steps" :key="stepIndex">
                                        <option :value="stepIndex" x-text="'Step ' + (stepIndex + 1) + ': ' + step.title"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <button @click="removeCondition(conditionIndex)" class="btn-formxr btn-danger">Remove</button>
                            </div>
                        </div>
                    </template>
                    
                    <button @click="addCondition()" class="btn-formxr btn-outline">Add Condition</button>
                </div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="actions-bar">
            <div>
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="btn-formxr btn-outline">Cancel</a>
            </div>
            <div>
                <button @click="saveQuestionnaire()" class="btn-formxr" :disabled="saving" x-show="!questionnaire.saved">
                    <span x-show="!saving">Finish & Save</span>
                    <span x-show="saving">Saving...</span>
                </button>
                <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires'); ?>" class="btn-formxr" x-show="questionnaire.saved">
                    Back to Questionnaires
                </a>
            </div>
        </div>
    </div>
</div>
