<?php
/**
 * Admin Questionnaire Builder Template
 * Complete rewrite with consistent header/footer structure
 */
if (!defined('ABSPATH')) {
    exit;
}

// Include header
include_once FORMXR_PLUGIN_DIR . 'templates/admin-header.php';

global $wpdb;

// Get questionnaire ID
$questionnaire_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$questionnaire_id) {
    echo '<div class="formxr-alert formxr-alert-error">';
    echo '<span class="formxr-alert-icon">❌</span>';
    echo __('Invalid questionnaire ID.', 'formxr');
    echo '</div>';
    include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
    return;
}

// Get questionnaire data
$questionnaire = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}formxr_questionnaires 
    WHERE id = %d
", $questionnaire_id));

if (!$questionnaire) {
    echo '<div class="formxr-alert formxr-alert-error">';
    echo '<span class="formxr-alert-icon">❌</span>';
    echo __('Questionnaire not found.', 'formxr');
    echo '</div>';
    include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
    return;
}

// Get questions
$questions = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}formxr_questions 
    WHERE questionnaire_id = %d 
    ORDER BY order_num ASC
", $questionnaire_id));
?>

<div class="formxr-admin-wrap" x-data="questionnaireBuilder()" x-cloak>
    <!-- Page Header -->
    <div class="formxr-page-header">
        <div class="formxr-page-header-content">
            <h1 class="formxr-page-title">
                <span class="formxr-page-icon">🛠️</span>
                <?php _e('Questionnaire Builder', 'formxr'); ?>
            </h1>
            <p class="formxr-page-subtitle">
                <?php printf(__('Advanced builder for: %s', 'formxr'), esc_html($questionnaire->title)); ?>
            </p>
        </div>
        <div class="formxr-page-actions">
            <a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=edit&id=' . $questionnaire_id); ?>" class="formxr-btn formxr-btn-secondary">
                <span class="formxr-btn-icon">✏️</span>
                <?php _e('Basic Editor', 'formxr'); ?>
            </a>
            <button type="button" @click="saveBuilder()" class="formxr-btn formxr-btn-primary">
                <span class="formxr-btn-icon">💾</span>
                <?php _e('Save Changes', 'formxr'); ?>
            </button>
        </div>
    </div>

    <!-- Builder Toolbar -->
    <div class="formxr-section">
        <div class="formxr-builder-toolbar">
            <div class="formxr-toolbar-group">
                <h3 class="formxr-toolbar-title"><?php _e('Question Types', 'formxr'); ?></h3>
                <div class="formxr-question-types">
                    <button type="button" 
                            @click="addQuestionType('text')" 
                            class="formxr-question-type-btn">
                        <span class="formxr-question-type-icon">📝</span>
                        <span class="formxr-question-type-label"><?php _e('Text', 'formxr'); ?></span>
                    </button>
                    
                    <button type="button" 
                            @click="addQuestionType('textarea')" 
                            class="formxr-question-type-btn">
                        <span class="formxr-question-type-icon">📄</span>
                        <span class="formxr-question-type-label"><?php _e('Textarea', 'formxr'); ?></span>
                    </button>
                    
                    <button type="button" 
                            @click="addQuestionType('email')" 
                            class="formxr-question-type-btn">
                        <span class="formxr-question-type-icon">📧</span>
                        <span class="formxr-question-type-label"><?php _e('Email', 'formxr'); ?></span>
                    </button>
                    
                    <button type="button" 
                            @click="addQuestionType('number')" 
                            class="formxr-question-type-btn">
                        <span class="formxr-question-type-icon">🔢</span>
                        <span class="formxr-question-type-label"><?php _e('Number', 'formxr'); ?></span>
                    </button>
                    
                    <button type="button" 
                            @click="addQuestionType('select')" 
                            class="formxr-question-type-btn">
                        <span class="formxr-question-type-icon">📋</span>
                        <span class="formxr-question-type-label"><?php _e('Dropdown', 'formxr'); ?></span>
                    </button>
                    
                    <button type="button" 
                            @click="addQuestionType('radio')" 
                            class="formxr-question-type-btn">
                        <span class="formxr-question-type-icon">🔘</span>
                        <span class="formxr-question-type-label"><?php _e('Radio', 'formxr'); ?></span>
                    </button>
                    
                    <button type="button" 
                            @click="addQuestionType('checkbox')" 
                            class="formxr-question-type-btn">
                        <span class="formxr-question-type-icon">☑️</span>
                        <span class="formxr-question-type-label"><?php _e('Checkbox', 'formxr'); ?></span>
                    </button>
                    
                    <button type="button" 
                            @click="addQuestionType('date')" 
                            class="formxr-question-type-btn">
                        <span class="formxr-question-type-icon">📅</span>
                        <span class="formxr-question-type-label"><?php _e('Date', 'formxr'); ?></span>
                    </button>
                    
                    <button type="button" 
                            @click="addQuestionType('file')" 
                            class="formxr-question-type-btn">
                        <span class="formxr-question-type-icon">📎</span>
                        <span class="formxr-question-type-label"><?php _e('File', 'formxr'); ?></span>
                    </button>
                </div>
            </div>
            
            <div class="formxr-toolbar-group">
                <h3 class="formxr-toolbar-title"><?php _e('Actions', 'formxr'); ?></h3>
                <div class="formxr-toolbar-actions">
                    <button type="button" @click="previewForm()" class="formxr-btn formxr-btn-info">
                        <span class="formxr-btn-icon">👁️</span>
                        <?php _e('Preview', 'formxr'); ?>
                    </button>
                    
                    <button type="button" @click="importQuestions()" class="formxr-btn formxr-btn-secondary">
                        <span class="formxr-btn-icon">📥</span>
                        <?php _e('Import', 'formxr'); ?>
                    </button>
                    
                    <button type="button" @click="exportQuestions()" class="formxr-btn formxr-btn-secondary">
                        <span class="formxr-btn-icon">📤</span>
                        <?php _e('Export', 'formxr'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Builder Canvas -->
    <div class="formxr-grid formxr-grid-builder">
        <!-- Questions Panel -->
        <div class="formxr-builder-panel formxr-questions-panel">
            <div class="formxr-panel-header">
                <h3 class="formxr-panel-title"><?php _e('Questions', 'formxr'); ?></h3>
                <span class="formxr-question-count" x-text="questions.length + ' questions'"></span>
            </div>
            
            <div class="formxr-panel-content">
                <div class="formxr-questions-list" x-ref="questionsList">
                    <template x-for="(question, index) in questions" :key="question.id">
                        <div class="formxr-question-builder-item" 
                             :class="{ 'active': selectedQuestion === index }"
                             @click="selectQuestion(index)"
                             x-ref="questionItem">
                            
                            <div class="formxr-question-handle">
                                <span class="formxr-drag-icon">⋮⋮</span>
                            </div>
                            
                            <div class="formxr-question-preview">
                                <div class="formxr-question-type-indicator">
                                    <span x-text="getQuestionTypeIcon(question.type)"></span>
                                </div>
                                <div class="formxr-question-content">
                                    <div class="formxr-question-text" x-text="question.question || 'Untitled Question'"></div>
                                    <div class="formxr-question-meta">
                                        <span class="formxr-question-type" x-text="question.type"></span>
                                        <span x-show="question.required" class="formxr-required-indicator">Required</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="formxr-question-actions">
                                <button type="button" 
                                        @click.stop="duplicateQuestion(index)" 
                                        class="formxr-question-action-btn" 
                                        title="<?php _e('Duplicate', 'formxr'); ?>">
                                    📋
                                </button>
                                <button type="button" 
                                        @click.stop="deleteQuestion(index)" 
                                        class="formxr-question-action-btn formxr-delete-btn" 
                                        title="<?php _e('Delete', 'formxr'); ?>">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    <!-- Empty State -->
                    <div x-show="questions.length === 0" class="formxr-empty-state">
                        <div class="formxr-empty-icon">❓</div>
                        <h4><?php _e('No Questions Yet', 'formxr'); ?></h4>
                        <p><?php _e('Click on a question type above to add your first question.', 'formxr'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Editor Panel -->
        <div class="formxr-builder-panel formxr-editor-panel">
            <div class="formxr-panel-header">
                <h3 class="formxr-panel-title"><?php _e('Question Editor', 'formxr'); ?></h3>
                <span x-show="selectedQuestion !== null" x-text="'Question ' + (selectedQuestion + 1)"></span>
            </div>
            
            <div class="formxr-panel-content">
                <div x-show="selectedQuestion !== null" class="formxr-question-editor">
                    <template x-if="questions[selectedQuestion]">
                        <div class="formxr-form-group-container">
                            <!-- Question Text -->
                            <div class="formxr-form-group">
                                <label class="formxr-form-label">
                                    <?php _e('Question Text', 'formxr'); ?> <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       class="formxr-form-control" 
                                       x-model="questions[selectedQuestion].question"
                                       placeholder="<?php _e('Enter your question...', 'formxr'); ?>">
                            </div>

                            <!-- Question Type -->
                            <div class="formxr-form-group">
                                <label class="formxr-form-label">
                                    <?php _e('Question Type', 'formxr'); ?>
                                </label>
                                <select class="formxr-form-control" x-model="questions[selectedQuestion].type">
                                    <option value="text"><?php _e('Text Input', 'formxr'); ?></option>
                                    <option value="textarea"><?php _e('Textarea', 'formxr'); ?></option>
                                    <option value="email"><?php _e('Email', 'formxr'); ?></option>
                                    <option value="number"><?php _e('Number', 'formxr'); ?></option>
                                    <option value="select"><?php _e('Dropdown', 'formxr'); ?></option>
                                    <option value="radio"><?php _e('Radio Buttons', 'formxr'); ?></option>
                                    <option value="checkbox"><?php _e('Checkboxes', 'formxr'); ?></option>
                                    <option value="date"><?php _e('Date', 'formxr'); ?></option>
                                    <option value="file"><?php _e('File Upload', 'formxr'); ?></option>
                                </select>
                            </div>

                            <!-- Required Toggle -->
                            <div class="formxr-form-group">
                                <div class="formxr-form-check">
                                    <input type="checkbox" 
                                           class="formxr-form-check-input" 
                                           x-model="questions[selectedQuestion].required">
                                    <label class="formxr-form-check-label">
                                        <?php _e('Required Question', 'formxr'); ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Placeholder -->
                            <div class="formxr-form-group" 
                                 x-show="['text', 'textarea', 'email', 'number'].includes(questions[selectedQuestion].type)">
                                <label class="formxr-form-label">
                                    <?php _e('Placeholder Text', 'formxr'); ?>
                                </label>
                                <input type="text" 
                                       class="formxr-form-control" 
                                       x-model="questions[selectedQuestion].placeholder"
                                       placeholder="<?php _e('Enter placeholder text...', 'formxr'); ?>">
                            </div>

                            <!-- Options -->
                            <div class="formxr-form-group" 
                                 x-show="['select', 'radio', 'checkbox'].includes(questions[selectedQuestion].type)">
                                <label class="formxr-form-label">
                                    <?php _e('Options', 'formxr'); ?>
                                </label>
                                <div class="formxr-options-editor">
                                    <template x-for="(option, optIndex) in getOptionsArray(questions[selectedQuestion])" :key="optIndex">
                                        <div class="formxr-option-item">
                                            <input type="text" 
                                                   class="formxr-form-control" 
                                                   x-model="option.value"
                                                   @input="updateOptionsFromArray(questions[selectedQuestion])"
                                                   placeholder="<?php _e('Option text...', 'formxr'); ?>">
                                            <button type="button" 
                                                    @click="removeOption(questions[selectedQuestion], optIndex)" 
                                                    class="formxr-btn formxr-btn-sm formxr-btn-error">
                                                🗑️
                                            </button>
                                        </div>
                                    </template>
                                    <button type="button" 
                                            @click="addOption(questions[selectedQuestion])" 
                                            class="formxr-btn formxr-btn-sm formxr-btn-secondary">
                                        <span class="formxr-btn-icon">➕</span>
                                        <?php _e('Add Option', 'formxr'); ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Help Text -->
                            <div class="formxr-form-group">
                                <label class="formxr-form-label">
                                    <?php _e('Help Text', 'formxr'); ?>
                                </label>
                                <textarea class="formxr-form-control" 
                                          rows="2"
                                          x-model="questions[selectedQuestion].help_text"
                                          placeholder="<?php _e('Optional help text for this question...', 'formxr'); ?>"></textarea>
                            </div>

                            <!-- Advanced Settings -->
                            <div class="formxr-form-group">
                                <details class="formxr-details">
                                    <summary class="formxr-details-summary"><?php _e('Advanced Settings', 'formxr'); ?></summary>
                                    <div class="formxr-details-content">
                                        <!-- Custom CSS Class -->
                                        <div class="formxr-form-group">
                                            <label class="formxr-form-label">
                                                <?php _e('CSS Class', 'formxr'); ?>
                                            </label>
                                            <input type="text" 
                                                   class="formxr-form-control" 
                                                   x-model="questions[selectedQuestion].css_class"
                                                   placeholder="<?php _e('custom-class', 'formxr'); ?>">
                                        </div>

                                        <!-- Conditional Logic -->
                                        <div class="formxr-form-group">
                                            <div class="formxr-form-check">
                                                <input type="checkbox" 
                                                       class="formxr-form-check-input" 
                                                       x-model="questions[selectedQuestion].conditional">
                                                <label class="formxr-form-check-label">
                                                    <?php _e('Enable Conditional Logic', 'formxr'); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div x-show="selectedQuestion === null" class="formxr-no-selection">
                    <div class="formxr-empty-icon">👈</div>
                    <h4><?php _e('Select a Question', 'formxr'); ?></h4>
                    <p><?php _e('Click on a question from the left panel to edit its properties.', 'formxr'); ?></p>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="formxr-builder-panel formxr-preview-panel">
            <div class="formxr-panel-header">
                <h3 class="formxr-panel-title"><?php _e('Live Preview', 'formxr'); ?></h3>
                <button type="button" @click="refreshPreview()" class="formxr-btn formxr-btn-sm formxr-btn-secondary">
                    🔄 <?php _e('Refresh', 'formxr'); ?>
                </button>
            </div>
            
            <div class="formxr-panel-content">
                <div class="formxr-form-preview">
                    <form class="formxr-preview-form">
                        <template x-for="(question, index) in questions" :key="question.id">
                            <div class="formxr-preview-question" 
                                 :class="{ 'highlighted': selectedQuestion === index }">
                                
                                <!-- Question Label -->
                                <label class="formxr-preview-label">
                                    <span x-text="question.question || 'Untitled Question'"></span>
                                    <span x-show="question.required" class="formxr-required">*</span>
                                </label>

                                <!-- Question Input Based on Type -->
                                <div class="formxr-preview-input">
                                    <!-- Text Input -->
                                    <input x-show="question.type === 'text'" 
                                           type="text" 
                                           class="formxr-form-control" 
                                           :placeholder="question.placeholder || ''"
                                           disabled>

                                    <!-- Textarea -->
                                    <textarea x-show="question.type === 'textarea'" 
                                              class="formxr-form-control" 
                                              rows="3"
                                              :placeholder="question.placeholder || ''"
                                              disabled></textarea>

                                    <!-- Email -->
                                    <input x-show="question.type === 'email'" 
                                           type="email" 
                                           class="formxr-form-control" 
                                           :placeholder="question.placeholder || ''"
                                           disabled>

                                    <!-- Number -->
                                    <input x-show="question.type === 'number'" 
                                           type="number" 
                                           class="formxr-form-control" 
                                           :placeholder="question.placeholder || ''"
                                           disabled>

                                    <!-- Date -->
                                    <input x-show="question.type === 'date'" 
                                           type="date" 
                                           class="formxr-form-control" 
                                           disabled>

                                    <!-- File -->
                                    <input x-show="question.type === 'file'" 
                                           type="file" 
                                           class="formxr-form-control" 
                                           disabled>

                                    <!-- Select -->
                                    <select x-show="question.type === 'select'" 
                                            class="formxr-form-control" 
                                            disabled>
                                        <option><?php _e('Select an option...', 'formxr'); ?></option>
                                        <template x-for="option in getOptionsArray(question)" :key="option.value">
                                            <option x-text="option.value"></option>
                                        </template>
                                    </select>

                                    <!-- Radio -->
                                    <div x-show="question.type === 'radio'" class="formxr-radio-group">
                                        <template x-for="option in getOptionsArray(question)" :key="option.value">
                                            <div class="formxr-radio-item">
                                                <input type="radio" 
                                                       :name="'preview_radio_' + index" 
                                                       :id="'preview_radio_' + index + '_' + option.value"
                                                       disabled>
                                                <label :for="'preview_radio_' + index + '_' + option.value" 
                                                       x-text="option.value"></label>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Checkbox -->
                                    <div x-show="question.type === 'checkbox'" class="formxr-checkbox-group">
                                        <template x-for="option in getOptionsArray(question)" :key="option.value">
                                            <div class="formxr-checkbox-item">
                                                <input type="checkbox" 
                                                       :id="'preview_checkbox_' + index + '_' + option.value"
                                                       disabled>
                                                <label :for="'preview_checkbox_' + index + '_' + option.value" 
                                                       x-text="option.value"></label>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Help Text -->
                                <p x-show="question.help_text" 
                                   class="formxr-preview-help" 
                                   x-text="question.help_text"></p>
                            </div>
                        </template>

                        <!-- Submit Button -->
                        <div class="formxr-preview-submit">
                            <button type="button" class="formxr-btn formxr-btn-primary" disabled>
                                <?php _e('Submit', 'formxr'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function questionnaireBuilder() {
    return {
        questions: <?php echo json_encode(array_map(function($q) {
            return [
                'id' => $q->id,
                'question' => $q->question,
                'type' => $q->type,
                'options' => $q->options,
                'required' => $q->required == 1,
                'placeholder' => '',
                'help_text' => '',
                'css_class' => '',
                'conditional' => false
            ];
        }, $questions)); ?>,
        selectedQuestion: null,
        nextId: <?php echo count($questions) + 1; ?>,
        
        selectQuestion(index) {
            this.selectedQuestion = index;
        },
        
        addQuestionType(type) {
            const newQuestion = {
                id: this.nextId++,
                question: '',
                type: type,
                options: type === 'select' || type === 'radio' || type === 'checkbox' ? 'Option 1\nOption 2\nOption 3' : '',
                required: false,
                placeholder: '',
                help_text: '',
                css_class: '',
                conditional: false
            };
            
            this.questions.push(newQuestion);
            this.selectedQuestion = this.questions.length - 1;
        },
        
        deleteQuestion(index) {
            if (confirm('<?php _e('Are you sure you want to delete this question?', 'formxr'); ?>')) {
                this.questions.splice(index, 1);
                if (this.selectedQuestion >= this.questions.length) {
                    this.selectedQuestion = this.questions.length > 0 ? this.questions.length - 1 : null;
                }
            }
        },
        
        duplicateQuestion(index) {
            const originalQuestion = this.questions[index];
            const duplicatedQuestion = {
                ...originalQuestion,
                id: this.nextId++,
                question: originalQuestion.question + ' (Copy)'
            };
            
            this.questions.splice(index + 1, 0, duplicatedQuestion);
            this.selectedQuestion = index + 1;
        },
        
        getOptionsArray(question) {
            if (!question.options) return [];
            return question.options.split('\n').filter(opt => opt.trim()).map(opt => ({ value: opt.trim() }));
        },
        
        updateOptionsFromArray(question) {
            // This would be implemented to sync array changes back to string
        },
        
        addOption(question) {
            const options = this.getOptionsArray(question);
            options.push({ value: 'New Option' });
            question.options = options.map(opt => opt.value).join('\n');
        },
        
        removeOption(question, index) {
            const options = this.getOptionsArray(question);
            options.splice(index, 1);
            question.options = options.map(opt => opt.value).join('\n');
        },
        
        getQuestionTypeIcon(type) {
            const icons = {
                'text': '📝',
                'textarea': '📄',
                'email': '📧',
                'number': '🔢',
                'select': '📋',
                'radio': '🔘',
                'checkbox': '☑️',
                'date': '📅',
                'file': '📎'
            };
            return icons[type] || '❓';
        },
        
        previewForm() {
            alert('<?php _e('Preview functionality would open in a new window or modal.', 'formxr'); ?>');
        },
        
        importQuestions() {
            alert('<?php _e('Import functionality would allow uploading question sets.', 'formxr'); ?>');
        },
        
        exportQuestions() {
            alert('<?php _e('Export functionality would download question data.', 'formxr'); ?>');
        },
        
        refreshPreview() {
            // Force re-render of preview
            this.$nextTick(() => {
                // Preview refresh logic
            });
        },
        
        saveBuilder() {
            // Save the questionnaire data via AJAX
            const data = {
                action: 'formxr_save_builder',
                questionnaire_id: <?php echo $questionnaire_id; ?>,
                questions: this.questions,
                nonce: '<?php echo wp_create_nonce('formxr_save_builder'); ?>'
            };
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('<?php _e('Questions saved successfully!', 'formxr'); ?>');
                } else {
                    alert('<?php _e('Error saving questions. Please try again.', 'formxr'); ?>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('<?php _e('Error saving questions. Please try again.', 'formxr'); ?>');
            });
        }
    }
}
</script>

<?php
// Include footer
include_once FORMXR_PLUGIN_DIR . 'templates/admin-footer.php';
?>
