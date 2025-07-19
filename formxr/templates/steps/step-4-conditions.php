<?php
/**
 * Step 4: Conditions
 * Configure pricing conditions and display logic
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="formxr-step-content">
    <div class="formxr-section">
        <div class="formxr-section-header">
            <h2 class="formxr-section-title">
                <?php _e('Step 4 - Conditions', 'formxr'); ?>
            </h2>
            <p class="formxr-section-description">
                <?php _e('Set up pricing conditions and display logic for your questionnaire.', 'formxr'); ?>
            </p>
        </div>
        
        <!-- Pricing Conditions (only if pricing is enabled) -->
        <div class="formxr-widget" x-show="basicInfo.enablePricing" x-transition>
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Pricing Conditions', 'formxr'); ?>
                </h3>
                <button type="button" 
                        @click="addPricingCondition()" 
                        class="formxr-btn formxr-btn-sm formxr-btn-primary">
                    <span class="formxr-btn-icon">➕</span>
                    <?php _e('Add Condition', 'formxr'); ?>
                </button>
            </div>
            <div class="formxr-widget-content">
                <p class="formxr-form-help">
                    <?php _e('Create rules that adjust pricing based on user responses.', 'formxr'); ?>
                </p>
                
                <!-- Base Price -->
                <div class="formxr-base-price-section">
                    <h4><?php _e('Base Pricing', 'formxr'); ?></h4>
                    <div class="formxr-form-row">
                        <div class="formxr-form-group">
                            <label for="base_price" class="formxr-form-label">
                                <?php _e('Base Price ($)', 'formxr'); ?>
                            </label>
                            <input type="number" 
                                   id="base_price" 
                                   name="base_price" 
                                   class="formxr-form-control" 
                                   x-model="conditionsConfig.basePrice"
                                   min="0" 
                                   step="0.01"
                                   placeholder="0.00">
                        </div>
                        <div class="formxr-form-group">
                            <label for="min_price" class="formxr-form-label">
                                <?php _e('Minimum Price ($)', 'formxr'); ?>
                            </label>
                            <input type="number" 
                                   id="min_price" 
                                   name="min_price" 
                                   class="formxr-form-control" 
                                   x-model="conditionsConfig.minPrice"
                                   min="0" 
                                   step="0.01"
                                   placeholder="0.00">
                        </div>
                        <div class="formxr-form-group">
                            <label for="max_price" class="formxr-form-label">
                                <?php _e('Maximum Price ($)', 'formxr'); ?>
                            </label>
                            <input type="number" 
                                   id="max_price" 
                                   name="max_price" 
                                   class="formxr-form-control" 
                                   x-model="conditionsConfig.maxPrice"
                                   min="0" 
                                   step="0.01"
                                   placeholder="10000.00">
                        </div>
                    </div>
                </div>
                
                <!-- Pricing Conditions List -->
                <div class="formxr-conditions-list">
                    <template x-for="(condition, index) in conditionsConfig.pricingConditions" :key="index">
                        <div class="formxr-condition-item">
                            <div class="formxr-condition-header">
                                <h5 x-text="'Condition ' + (index + 1)"></h5>
                                <button type="button" 
                                        @click="removePricingCondition(index)" 
                                        class="formxr-btn formxr-btn-xs formxr-btn-error">
                                    <?php _e('Remove', 'formxr'); ?>
                                </button>
                            </div>
                            
                            <div class="formxr-condition-content">
                                <div class="formxr-form-row">
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('If Question', 'formxr'); ?>
                                        </label>
                                        <select :name="'pricing_conditions[' + index + '][question]'"
                                                class="formxr-form-control" 
                                                x-model="condition.question">
                                            <option value=""><?php _e('Select a question', 'formxr'); ?></option>
                                            <template x-for="(step, stepIndex) in questionsConfig.steps" :key="stepIndex">
                                                <template x-for="(question, questionIndex) in step.questions" :key="questionIndex">
                                                    <option :value="'step_' + stepIndex + '_q_' + questionIndex" 
                                                            x-text="question.label || 'Question ' + (questionIndex + 1)"></option>
                                                </template>
                                            </template>
                                        </select>
                                    </div>
                                    
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('Condition', 'formxr'); ?>
                                        </label>
                                        <select :name="'pricing_conditions[' + index + '][operator]'"
                                                class="formxr-form-control" 
                                                x-model="condition.operator">
                                            <option value="equals"><?php _e('Equals', 'formxr'); ?></option>
                                            <option value="not_equals"><?php _e('Not Equals', 'formxr'); ?></option>
                                            <option value="contains"><?php _e('Contains', 'formxr'); ?></option>
                                            <option value="greater_than"><?php _e('Greater Than', 'formxr'); ?></option>
                                            <option value="less_than"><?php _e('Less Than', 'formxr'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('Value', 'formxr'); ?>
                                        </label>
                                        <input type="text" 
                                               :name="'pricing_conditions[' + index + '][value]'"
                                               class="formxr-form-control" 
                                               x-model="condition.value"
                                               placeholder="<?php _e('Comparison value', 'formxr'); ?>">
                                    </div>
                                </div>
                                
                                <div class="formxr-form-row">
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('Price Action', 'formxr'); ?>
                                        </label>
                                        <select :name="'pricing_conditions[' + index + '][action]'"
                                                class="formxr-form-control" 
                                                x-model="condition.action">
                                            <option value="add"><?php _e('Add Amount', 'formxr'); ?></option>
                                            <option value="subtract"><?php _e('Subtract Amount', 'formxr'); ?></option>
                                            <option value="multiply"><?php _e('Multiply by', 'formxr'); ?></option>
                                            <option value="set"><?php _e('Set Total To', 'formxr'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('Amount ($)', 'formxr'); ?>
                                        </label>
                                        <input type="number" 
                                               :name="'pricing_conditions[' + index + '][amount]'"
                                               class="formxr-form-control" 
                                               x-model="condition.amount"
                                               min="0" 
                                               step="0.01"
                                               placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <!-- Empty state for pricing conditions -->
                    <div x-show="conditionsConfig.pricingConditions.length === 0" class="formxr-empty-state">
                        <p><?php _e('No pricing conditions set. The base price will be used for all submissions.', 'formxr'); ?></p>
                        <button type="button" 
                                @click="addPricingCondition()" 
                                class="formxr-btn formxr-btn-primary">
                            <?php _e('Add First Condition', 'formxr'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Display Conditions -->
        <div class="formxr-widget">
            <div class="formxr-widget-header">
                <h3 class="formxr-widget-title">
                    <?php _e('Display Conditions', 'formxr'); ?>
                </h3>
                <button type="button" 
                        @click="addDisplayCondition()" 
                        class="formxr-btn formxr-btn-sm formxr-btn-primary">
                    <span class="formxr-btn-icon">➕</span>
                    <?php _e('Add Condition', 'formxr'); ?>
                </button>
            </div>
            <div class="formxr-widget-content">
                <p class="formxr-form-help">
                    <?php _e('Create rules to show or hide questions based on previous answers.', 'formxr'); ?>
                </p>
                
                <!-- Display Conditions List -->
                <div class="formxr-conditions-list">
                    <template x-for="(condition, index) in conditionsConfig.displayConditions" :key="index">
                        <div class="formxr-condition-item">
                            <div class="formxr-condition-header">
                                <h5 x-text="'Display Condition ' + (index + 1)"></h5>
                                <button type="button" 
                                        @click="removeDisplayCondition(index)" 
                                        class="formxr-btn formxr-btn-xs formxr-btn-error">
                                    <?php _e('Remove', 'formxr'); ?>
                                </button>
                            </div>
                            
                            <div class="formxr-condition-content">
                                <div class="formxr-form-row">
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('Show Question', 'formxr'); ?>
                                        </label>
                                        <select :name="'display_conditions[' + index + '][target_question]'"
                                                class="formxr-form-control" 
                                                x-model="condition.targetQuestion">
                                            <option value=""><?php _e('Select question to show/hide', 'formxr'); ?></option>
                                            <template x-for="(step, stepIndex) in questionsConfig.steps" :key="stepIndex">
                                                <template x-for="(question, questionIndex) in step.questions" :key="questionIndex">
                                                    <option :value="'step_' + stepIndex + '_q_' + questionIndex" 
                                                            x-text="question.label || 'Question ' + (questionIndex + 1)"></option>
                                                </template>
                                            </template>
                                        </select>
                                    </div>
                                    
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('When', 'formxr'); ?>
                                        </label>
                                        <select :name="'display_conditions[' + index + '][source_question]'"
                                                class="formxr-form-control" 
                                                x-model="condition.sourceQuestion">
                                            <option value=""><?php _e('Select trigger question', 'formxr'); ?></option>
                                            <template x-for="(step, stepIndex) in questionsConfig.steps" :key="stepIndex">
                                                <template x-for="(question, questionIndex) in step.questions" :key="questionIndex">
                                                    <option :value="'step_' + stepIndex + '_q_' + questionIndex" 
                                                            x-text="question.label || 'Question ' + (questionIndex + 1)"></option>
                                                </template>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="formxr-form-row">
                                    <div class="formxr-form-group">
                                        <label class="formxr-form-label">
                                            <?php _e('Condition', 'formxr'); ?>
                                        </label>
                                        <select :name="'display_conditions[' + index + '][operator]'"
                                                class="formxr-form-control" 
                                                x-model="condition.operator">
                                            <option value="equals"><?php _e('Equals', 'formxr'); ?></option>
                                            <option value="not_equals"><?php _e('Not Equals', 'formxr'); ?></option>
                                            <option value="contains"><?php _e('Contains', 'formxr'); ?></option>
                                            <option value="not_empty"><?php _e('Is Not Empty', 'formxr'); ?></option>
                                            <option value="empty"><?php _e('Is Empty', 'formxr'); ?></option>
                                        </select>
                                    </div>
                                    
                                    <div class="formxr-form-group" x-show="!['not_empty', 'empty'].includes(condition.operator)">
                                        <label class="formxr-form-label">
                                            <?php _e('Value', 'formxr'); ?>
                                        </label>
                                        <input type="text" 
                                               :name="'display_conditions[' + index + '][value]'"
                                               class="formxr-form-control" 
                                               x-model="condition.value"
                                               placeholder="<?php _e('Comparison value', 'formxr'); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <!-- Empty state for display conditions -->
                    <div x-show="conditionsConfig.displayConditions.length === 0" class="formxr-empty-state">
                        <p><?php _e('No display conditions set. All questions will always be visible.', 'formxr'); ?></p>
                        <button type="button" 
                                @click="addDisplayCondition()" 
                                class="formxr-btn formxr-btn-primary">
                            <?php _e('Add First Condition', 'formxr'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing disabled notice -->
        <div class="formxr-widget" x-show="!basicInfo.enablePricing" x-transition>
            <div class="formxr-widget-content">
                <div class="formxr-notice formxr-notice-info">
                    <strong><?php _e('Pricing is disabled', 'formxr'); ?></strong>
                    <p><?php _e('Pricing conditions are not available because pricing is disabled for this questionnaire. You can enable pricing in Step 1 if needed.', 'formxr'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.formxr-base-price-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e0e0e0;
}

.formxr-base-price-section h4 {
    margin: 0 0 1rem 0;
    color: #495057;
}

.formxr-condition-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 1rem;
    background: white;
}

.formxr-condition-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.formxr-condition-header h5 {
    margin: 0;
    color: #495057;
}

.formxr-condition-content {
    padding: 1rem;
}

.formxr-notice {
    padding: 1rem;
    border-radius: 6px;
    border-left: 4px solid #17a2b8;
    background: #d1ecf1;
    color: #0c5460;
}

.formxr-notice-info {
    border-left-color: #17a2b8;
    background: #d1ecf1;
    color: #0c5460;
}

.formxr-notice strong {
    display: block;
    margin-bottom: 0.5rem;
}

.formxr-notice p {
    margin: 0;
}
</style>

<script>
// Add these to the main questionnaireCreator function
const conditionsConfig = {
    basePrice: 100,
    minPrice: 0,
    maxPrice: 10000,
    pricingConditions: [],
    displayConditions: []
};

function addPricingCondition() {
    this.conditionsConfig.pricingConditions.push({
        question: '',
        operator: 'equals',
        value: '',
        action: 'add',
        amount: 0
    });
}

function removePricingCondition(index) {
    this.conditionsConfig.pricingConditions.splice(index, 1);
}

function addDisplayCondition() {
    this.conditionsConfig.displayConditions.push({
        targetQuestion: '',
        sourceQuestion: '',
        operator: 'equals',
        value: ''
    });
}

function removeDisplayCondition(index) {
    this.conditionsConfig.displayConditions.splice(index, 1);
}
</script>
