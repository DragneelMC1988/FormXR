<?php
/**
 * Step 2: Questions Configuration
 * Multi-step option and question setup
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="formxr-step-content">
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title">
                <?php _e('Step 2 - Questions Setup', 'formxr'); ?>
            </h2>
            <p class="formxr-section-description">
                <?php _e('Configure your questionnaire structure and add questions.', 'formxr'); ?>
            </p>
        </div>
        
        <!-- Multi-Step Toggle -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Questionnaire Structure', 'formxr'); ?>
                </h3>
            </div>
            <div class="formxr-widget-content">
                <div class="formxr-form-check">
                    <input type="checkbox" 
                           id="enable_multi_step" 
                           name="enable_multi_step"
                           class="formxr-form-check-input" 
                           x-model="questionsConfig.enableMultiStep"
                           @change="toggleMultiStep()"
                           value="1">
                    <label for="enable_multi_step" class="formxr-form-check-label">
                        <strong><?php _e('Enable Multi-Step Questionnaire', 'formxr'); ?></strong>
                    </label>
                </div>
                <p class="formxr-form-help">
                    <?php _e('When enabled, your questionnaire will be divided into multiple steps/groups. Users will answer one group at a time and can navigate between steps.', 'formxr'); ?>
                </p>
            </div>
        </div>

        <!-- Multi-Step Configuration -->
        <div class="formxr-widget" x-show="questionsConfig.enableMultiStep" x-transition>
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Question Groups (Steps)', 'formxr'); ?>
                </h3>
                <button type="button" 
                        @click="addStep()" 
                        class="formxr-btn formxr-btn-sm formxr-btn-primary">
                    <span class="formxr-btn-icon">➕</span>
                    <?php _e('Add Step', 'formxr'); ?>
                </button>
            </div>
            <div class="formxr-widget-content">
                <!-- Steps List -->
                <div class="formxr-steps-container">
                    <template x-for="(step, stepIndex) in questionsConfig.steps" :key="stepIndex">
                        <div class="formxr-step-group">
                            <div class="formxr-step-header">
                                <div class="formxr-step-info">
                                    <h4 class="formxr-step-title">
                                        <span class="formxr-step-number" x-text="'Step ' + (stepIndex + 1)"></span>
                                    </h4>
                                    <button type="button" 
                                            @click="removeStep(stepIndex)" 
                                            x-show="questionsConfig.steps.length > 1"
                                            class="formxr-btn formxr-btn-sm formxr-btn-error">
                                        <?php _e('Remove Step', 'formxr'); ?>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="formxr-step-content-area">
                                <!-- Step Title and Description -->
                                <div class="formxr-form-grid">
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('Step Title', 'formxr'); ?>
                                        </label>
                                        <input type="text" 
                                               :name="'steps[' + stepIndex + '][title]'"
                                               class="formxr-form-control" 
                                               x-model="step.title"
                                               placeholder="<?php _e('e.g., Personal Information', 'formxr'); ?>">
                                    </div>
                                    
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('Step Description', 'formxr'); ?>
                                        </label>
                                        <textarea :name="'steps[' + stepIndex + '][description]'"
                                                  class="formxr-form-control" 
                                                  x-model="step.description"
                                                  rows="2"
                                                  placeholder="<?php _e('Optional description for this step...', 'formxr'); ?>"></textarea>
                                    </div>
                                </div>
                                
                                <!-- Questions for this step -->
                                <div class="formxr-questions-section">
                                    <div class="formxr-questions-header">
                                        <h5><?php _e('Questions', 'formxr'); ?></h5>
                                        <button type="button" 
                                                @click="addQuestion(stepIndex)" 
                                                class="formxr-btn formxr-btn-sm formxr-btn-secondary">
                                            <span class="formxr-btn-icon">➕</span>
                                            <?php _e('Add Question', 'formxr'); ?>
                                        </button>
                                    </div>
                                    
                                    <!-- Questions List -->
                                    <div class="formxr-questions-list">
                                        <template x-for="(question, questionIndex) in step.questions" :key="questionIndex">
                                            <div class="formxr-question-item">
                                                <div class="formxr-question-header">
                                                    <span class="formxr-question-number" x-text="'Q' + (questionIndex + 1)"></span>
                                                    <button type="button" 
                                                            @click="removeQuestion(stepIndex, questionIndex)" 
                                                            class="formxr-btn formxr-btn-xs formxr-btn-error">
                                                        <?php _e('Remove', 'formxr'); ?>
                                                    </button>
                                                </div>
                                                
                                                <div class="formxr-question-fields">
                                                    <div class="formxr-form-group">
                                                        <label class="formxr-form-label">
                                                            <?php _e('Question Label', 'formxr'); ?>
                                                        </label>
                                                        <input type="text" 
                                                               :name="'steps[' + stepIndex + '][questions][' + questionIndex + '][label]'"
                                                               class="formxr-form-control" 
                                                               x-model="question.label"
                                                               placeholder="<?php _e('Enter your question...', 'formxr'); ?>">
                                                    </div>
                                                    
                                                    <div class="formxr-form-row">
                                                        <div class="formxr-form-group">
                                                            <label class="formxr-form-label">
                                                                <?php _e('Field Type', 'formxr'); ?>
                                                            </label>
                                                            <select :name="'steps[' + stepIndex + '][questions][' + questionIndex + '][type]'"
                                                                    class="formxr-form-control" 
                                                                    x-model="question.type">
                                                                <option value="text"><?php _e('Text Input', 'formxr'); ?></option>
                                                                <option value="textarea"><?php _e('Textarea', 'formxr'); ?></option>
                                                                <option value="email"><?php _e('Email', 'formxr'); ?></option>
                                                                <option value="checkbox"><?php _e('Checkbox', 'formxr'); ?></option>
                                                                <option value="radio"><?php _e('Radio Buttons', 'formxr'); ?></option>
                                                                <option value="select"><?php _e('Select Dropdown', 'formxr'); ?></option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="formxr-form-group">
                                                            <div class="formxr-form-check">
                                                                <input type="checkbox" 
                                                                       :id="'required_' + stepIndex + '_' + questionIndex"
                                                                       :name="'steps[' + stepIndex + '][questions][' + questionIndex + '][required]'"
                                                                       class="formxr-form-check-input" 
                                                                       x-model="question.required"
                                                                       value="1">
                                                                <label :for="'required_' + stepIndex + '_' + questionIndex" 
                                                                       class="formxr-form-check-label">
                                                                    <?php _e('Required', 'formxr'); ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Options for select/radio/checkbox -->
                                                    <div class="formxr-form-group" 
                                                         x-show="['select', 'radio', 'checkbox'].includes(question.type)">
                                                        <label class="formxr-form-label">
                                                            <?php _e('Options', 'formxr'); ?>
                                                        </label>
                                                        <textarea :name="'steps[' + stepIndex + '][questions][' + questionIndex + '][options]'"
                                                                  class="formxr-form-control" 
                                                                  x-model="question.options"
                                                                  @input="updateOptionPrices(stepIndex, questionIndex)"
                                                                  rows="3"
                                                                  placeholder="<?php _e('Enter options, one per line...', 'formxr'); ?>"></textarea>
                                                        <p class="formxr-form-help">
                                                            <?php _e('Enter each option on a new line.', 'formxr'); ?>
                                                        </p>
                                                    </div>
                                                    
                                                    <!-- Question Pricing -->
                                                    <div class="formxr-form-group" x-show="basicInfo.enablePricing">
                                                        <label class="formxr-form-label">
                                                            <?php _e('Pricing', 'formxr'); ?>
                                                        </label>
                                                        
                                                        <!-- Base Question Price (for simple questions) -->
                                                        <div x-show="!['select', 'radio', 'checkbox'].includes(question.type)">
                                                            <div class="formxr-input-group">
                                                                <span class="formxr-input-group-text">$</span>
                                                                <input type="number" 
                                                                       :name="'steps[' + stepIndex + '][questions][' + questionIndex + '][price]'"
                                                                       class="formxr-form-control" 
                                                                       x-model="question.price"
                                                                       min="0"
                                                                       step="0.01"
                                                                       placeholder="0.00">
                                                            </div>
                                                            <p class="formxr-form-help">
                                                                <?php _e('Base price for this question (0 for free)', 'formxr'); ?>
                                                            </p>
                                                        </div>
                                                        
                                                        <!-- Option-based pricing (for multi-choice questions) -->
                                                        <div x-show="['select', 'radio', 'checkbox'].includes(question.type) && question.options">
                                                            <p class="formxr-form-help">
                                                                <?php _e('Set individual prices for each option:', 'formxr'); ?>
                                                            </p>
                                                            <template x-for="(option, optionIndex) in getQuestionOptions(stepIndex, questionIndex)" :key="optionIndex">
                                                                <div class="formxr-option-price">
                                                                    <label x-text="option" class="formxr-form-label"></label>
                                                                    <div class="formxr-input-group">
                                                                        <span class="formxr-input-group-text">$</span>
                                                                        <input type="number" 
                                                                               class="formxr-form-control" 
                                                                               x-model="question.optionPrices[option]"
                                                                               min="0"
                                                                               step="0.01"
                                                                               placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <!-- Empty state for questions -->
                                        <div x-show="step.questions.length === 0" class="formxr-empty-state">
                                            <p><?php _e('No questions added to this step yet.', 'formxr'); ?></p>
                                            <button type="button" 
                                                    @click="addQuestion(stepIndex)" 
                                                    class="formxr-btn formxr-btn-primary">
                                                <?php _e('Add First Question', 'formxr'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Empty state for steps -->
                <div x-show="questionsConfig.steps.length === 0" class="formxr-empty-state">
                    <p><?php _e('No steps created yet.', 'formxr'); ?></p>
                    <button type="button" 
                            @click="addStep()" 
                            class="formxr-btn formxr-btn-primary">
                        <?php _e('Create First Step', 'formxr'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Single Step Configuration -->
        <div class="formxr-widget" x-show="!questionsConfig.enableMultiStep" x-transition>
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Questions', 'formxr'); ?>
                </h3>
                <button type="button" 
                        @click="addQuestion(0)" 
                        class="formxr-btn formxr-btn-sm formxr-btn-primary">
                    <span class="formxr-btn-icon">➕</span>
                    <?php _e('Add Question', 'formxr'); ?>
                </button>
            </div>
            <div class="formxr-widget-content">
                <p class="formxr-form-help">
                    <?php _e('All questions will be displayed on a single page.', 'formxr'); ?>
                </p>
                
                <!-- Single step questions (use first step) -->
                <div class="formxr-questions-list" x-show="questionsConfig.steps.length > 0">
                    <template x-for="(question, questionIndex) in questionsConfig.steps[0].questions" :key="questionIndex">
                        <div class="formxr-question-item">
                            <div class="formxr-question-header">
                                <span class="formxr-question-number" x-text="'Q' + (questionIndex + 1)"></span>
                                <button type="button" 
                                        @click="removeQuestion(0, questionIndex)" 
                                        class="formxr-btn formxr-btn-xs formxr-btn-error">
                                    <?php _e('Remove', 'formxr'); ?>
                                </button>
                            </div>
                            
                            <div class="formxr-question-fields">
                                <div class="formxr-form-group">
                                    <label class="formxr-form-label">
                                        <?php _e('Question Label', 'formxr'); ?>
                                    </label>
                                    <input type="text" 
                                           :name="'questions[' + questionIndex + '][label]'"
                                           class="formxr-form-control" 
                                           x-model="question.label"
                                           placeholder="<?php _e('Enter your question...', 'formxr'); ?>">
                                </div>
                                
                                <div class="formxr-form-row">
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('Field Type', 'formxr'); ?>
                                        </label>
                                        <select :name="'questions[' + questionIndex + '][type]'"
                                                class="formxr-form-control" 
                                                x-model="question.type">
                                            <option value="text"><?php _e('Text Input', 'formxr'); ?></option>
                                            <option value="textarea"><?php _e('Textarea', 'formxr'); ?></option>
                                            <option value="email"><?php _e('Email', 'formxr'); ?></option>
                                            <option value="checkbox"><?php _e('Checkbox', 'formxr'); ?></option>
                                            <option value="radio"><?php _e('Radio Buttons', 'formxr'); ?></option>
                                            <option value="select"><?php _e('Select Dropdown', 'formxr'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="formxr-form-group">
                                        <div class="formxr-form-check">
                                            <input type="checkbox" 
                                                   :id="'single_required_' + questionIndex"
                                                   :name="'questions[' + questionIndex + '][required]'"
                                                   class="formxr-form-check-input" 
                                                   x-model="question.required"
                                                   value="1">
                                            <label :for="'single_required_' + questionIndex" 
                                                   class="formxr-form-check-label">
                                                <?php _e('Required', 'formxr'); ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Options for select/radio/checkbox -->
                                <div class="formxr-form-group" 
                                     x-show="['select', 'radio', 'checkbox'].includes(question.type)">
                                    <label class="formxr-form-label">
                                        <?php _e('Options', 'formxr'); ?>
                                    </label>
                                    <textarea :name="'questions[' + questionIndex + '][options]'"
                                              class="formxr-form-control" 
                                              x-model="question.options"
                                              @input="updateOptionPrices(0, questionIndex)"
                                              rows="3"
                                              placeholder="<?php _e('Enter options, one per line...', 'formxr'); ?>"></textarea>
                                    <p class="formxr-form-help">
                                        <?php _e('Enter each option on a new line.', 'formxr'); ?>
                                    </p>
                                </div>
                                
                                <!-- Question Pricing -->
                                <div class="formxr-form-group" x-show="basicInfo.enablePricing">
                                    <label class="formxr-form-label">
                                        <?php _e('Pricing', 'formxr'); ?>
                                    </label>
                                    
                                    <!-- Base Question Price (for simple questions) -->
                                    <div x-show="!['select', 'radio', 'checkbox'].includes(question.type)">
                                        <div class="formxr-input-group">
                                            <span class="formxr-input-group-text">$</span>
                                            <input type="number" 
                                                   :name="'questions[' + questionIndex + '][price]'"
                                                   class="formxr-form-control" 
                                                   x-model="question.price"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                        </div>
                                        <p class="formxr-form-help">
                                            <?php _e('Base price for this question (0 for free)', 'formxr'); ?>
                                        </p>
                                    </div>
                                    
                                    <!-- Option-based pricing (for multi-choice questions) -->
                                    <div x-show="['select', 'radio', 'checkbox'].includes(question.type) && question.options">
                                        <p class="formxr-form-help">
                                            <?php _e('Set individual prices for each option:', 'formxr'); ?>
                                        </p>
                                        <template x-for="(option, optionIndex) in getQuestionOptions(0, questionIndex)" :key="optionIndex">
                                            <div class="formxr-option-price">
                                                <label x-text="option" class="formxr-form-label"></label>
                                                <div class="formxr-input-group">
                                                    <span class="formxr-input-group-text">$</span>
                                                    <input type="number" 
                                                           class="formxr-form-control" 
                                                           x-model="question.optionPrices[option]"
                                                           min="0"
                                                           step="0.01"
                                                           placeholder="0.00">
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Empty state -->
                <div x-show="questionsConfig.steps.length === 0 || questionsConfig.steps[0].questions.length === 0" class="formxr-empty-state">
                    <p><?php _e('No questions added yet.', 'formxr'); ?></p>
                    <button type="button" 
                            @click="addQuestion(0)" 
                            class="formxr-btn formxr-btn-primary">
                        <?php _e('Add First Question', 'formxr'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.formxr-step-group {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 1rem;
    background: white;
}

.formxr-step-header {
    padding: 1rem;
    border-bottom: 1px solid #e0e0e0;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.formxr-step-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.formxr-step-title {
    margin: 0;
    font-size: 1.1rem;
    color: #2c3e50;
}

.formxr-step-number {
    color: #007cba;
    font-weight: bold;
}

.formxr-step-content-area {
    padding: 1.5rem;
}

.formxr-questions-section {
    margin-top: 1.5rem;
}

.formxr-questions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.formxr-questions-header h5 {
    margin: 0;
    color: #495057;
}

.formxr-question-item {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin-bottom: 1rem;
    background: #fdfdfd;
}

.formxr-question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 6px 6px 0 0;
}

.formxr-question-number {
    font-weight: bold;
    color: #007cba;
}

.formxr-question-fields {
    padding: 1rem;
}

.formxr-form-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: end;
}

.formxr-empty-state {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.formxr-empty-state p {
    margin-bottom: 1rem;
}

.toggleMultiStep() {
    if (!this.questionsConfig.enableMultiStep) {
        // Convert to single step - merge all questions into first step
        if (this.questionsConfig.steps.length > 1) {
            const allQuestions = [];
            this.questionsConfig.steps.forEach(step => {
                allQuestions.push(...step.questions);
            });
            this.questionsConfig.steps = [{
                title: 'Questions',
                description: '',
                questions: allQuestions
            }];
        }
    } else {
        // Ensure we have at least one step
        if (this.questionsConfig.steps.length === 0) {
            this.addStep();
        }
    }
}
</style>

<script>
// Add to the main questionnaireCreator function
function toggleMultiStep() {
    if (!this.questionsConfig.enableMultiStep) {
        // Convert to single step - merge all questions into first step
        if (this.questionsConfig.steps.length > 1) {
            const allQuestions = [];
            this.questionsConfig.steps.forEach(step => {
                allQuestions.push(...step.questions);
            });
            this.questionsConfig.steps = [{
                title: 'Questions',
                description: '',
                questions: allQuestions
            }];
        }
    } else {
        // Ensure we have at least one step
        if (this.questionsConfig.steps.length === 0) {
            this.addStep();
        }
    }
}
</script>

<style>
.formxr-input-group {
    display: flex;
    align-items: center;
}

.formxr-input-group-text {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-right: none;
    padding: 0.5rem 0.75rem;
    border-radius: 4px 0 0 4px;
    font-weight: 500;
    color: #495057;
    min-width: 40px;
    text-align: center;
}

.formxr-input-group .formxr-form-control {
    border-radius: 0 4px 4px 0;
    border-left: none;
}

.formxr-option-price {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
}

.formxr-option-price .formxr-form-label {
    margin: 0;
    min-width: 150px;
    font-weight: normal;
    color: #495057;
}

.formxr-option-price .formxr-input-group {
    flex: 1;
    max-width: 150px;
}

@media (max-width: 768px) {
    .formxr-option-price {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }
    
    .formxr-option-price .formxr-form-label {
        min-width: auto;
    }
    
    .formxr-option-price .formxr-input-group {
        max-width: none;
    }
}
</style>
