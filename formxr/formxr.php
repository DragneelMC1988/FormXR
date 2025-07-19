<?php
/*
Plugin Name: FormXR
Description: Advanced questionnaire system with conditional logic, dynamic pricing, and step-based forms. Create powerful forms with drag-and-drop builder, conditional questions, and real-time pricing calculations.
Version: 2.0
Author: Ayal Othman
Text Domain: formxr
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Network: false
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('FORMXR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FORMXR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('FORMXR_VERSION', '2.0');

class FormXR {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Check and upgrade database if needed (run on admin pages)
        if (is_admin()) {
            add_action('admin_init', array($this, 'check_database_version'));
        }
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register shortcode
        add_shortcode('formxr_form', array($this, 'render_form_shortcode'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_formxr_submit_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_nopriv_formxr_submit_form', array($this, 'handle_form_submission'));
        add_action('wp_ajax_formxr_calculate_price', array($this, 'calculate_price_ajax'));
        add_action('wp_ajax_nopriv_formxr_calculate_price', array($this, 'calculate_price_ajax'));
        add_action('wp_ajax_formxr_save_complete_questionnaire', array($this, 'save_complete_questionnaire_ajax'));
        add_action('wp_ajax_formxr_delete_questionnaire', array($this, 'delete_questionnaire_ajax'));
        add_action('wp_ajax_formxr_test_email', array($this, 'test_email_ajax'));
        
        // Export handler
        add_action('wp_ajax_formxr_export_csv', array($this, 'export_csv'));
        
        // Settings handling
        add_action('admin_init', array($this, 'register_settings'));
        
        // Activation notice
        add_action('admin_notices', array($this, 'activation_notice'));
    }
    
    public function activate() {
        $this->create_submissions_table();
        $this->set_default_settings();
        
        // Set initial database version
        if (!get_option('formxr_db_version')) {
            update_option('formxr_db_version', '2.0');
        }
        
        flush_rewrite_rules();
        
        // Set activation notice
        set_transient('formxr_activation_notice', true, 30);
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function check_database_version() {
        $current_version = get_option('formxr_db_version', '1.0');
        
        if (version_compare($current_version, '2.0', '<')) {
            $this->upgrade_database_to_2_0();
            update_option('formxr_db_version', '2.0');
        }
    }
    
    private function upgrade_database_to_2_0() {
        global $wpdb;
        
        // Check if steps table exists and has description column
        $steps_table = $wpdb->prefix . 'formxr_steps';
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM `$steps_table` LIKE 'description'");
        
        if (empty($column_exists)) {
            // Add description column to steps table
            $wpdb->query("ALTER TABLE `$steps_table` ADD COLUMN `description` TEXT AFTER `title`");
        }
        
        // Ensure other columns exist as well
        $can_skip_exists = $wpdb->get_results("SHOW COLUMNS FROM `$steps_table` LIKE 'can_skip'");
        if (empty($can_skip_exists)) {
            $wpdb->query("ALTER TABLE `$steps_table` ADD COLUMN `can_skip` TINYINT(1) DEFAULT 0 AFTER `description`");
        }
        
        $step_order_exists = $wpdb->get_results("SHOW COLUMNS FROM `$steps_table` LIKE 'step_order'");
        if (empty($step_order_exists)) {
            $wpdb->query("ALTER TABLE `$steps_table` ADD COLUMN `step_order` INT(3) DEFAULT 0 AFTER `can_skip`");
        }
    }
    
    private function create_submissions_table() {
        global $wpdb;
        
        // Create questionnaires table
        $questionnaires_table = $wpdb->prefix . 'formxr_questionnaires';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_questionnaires = "CREATE TABLE $questionnaires_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            status varchar(20) DEFAULT 'active',
            pricing_enabled tinyint(1) DEFAULT 0,
            min_price decimal(10,2) DEFAULT 100,
            max_price decimal(10,2) DEFAULT 2000,
            base_price decimal(10,2) DEFAULT 500,
            currency varchar(10) DEFAULT 'USD',
            form_title varchar(255),
            form_description text,
            email_recipients text,
            email_subject varchar(255),
            email_template text,
            notification_enabled tinyint(1) DEFAULT 1,
            conditions longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Create steps table
        $steps_table = $wpdb->prefix . 'formxr_steps';
        
        $sql_steps = "CREATE TABLE $steps_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            questionnaire_id mediumint(9) NOT NULL,
            step_number int(3) NOT NULL,
            title varchar(255) NOT NULL,
            description text,
            can_skip tinyint(1) DEFAULT 0,
            step_order int(3) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY questionnaire_id (questionnaire_id)
        ) $charset_collate;";
        
        // Create questions table
        $questions_table = $wpdb->prefix . 'formxr_questions';
        
        $sql_questions = "CREATE TABLE $questions_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            step_id mediumint(9) NOT NULL,
            question_text text NOT NULL,
            question_type varchar(20) NOT NULL,
            options text,
            is_required tinyint(1) DEFAULT 0,
            question_order int(3) DEFAULT 0,
            pricing_amount decimal(10,2) DEFAULT 0,
            pricing_visibility varchar(20) DEFAULT 'hidden',
            conditions text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY step_id (step_id)
        ) $charset_collate;";
        
        // Create submissions table
        $submissions_table = $wpdb->prefix . 'formxr_submissions';
        
        $sql_submissions = "CREATE TABLE $submissions_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            questionnaire_id mediumint(9) NOT NULL,
            submission_data longtext NOT NULL,
            calculated_price decimal(10,2) DEFAULT 0,
            price_type varchar(20) DEFAULT 'monthly',
            user_email varchar(100),
            user_ip varchar(45),
            submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY questionnaire_id (questionnaire_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_questionnaires);
        dbDelta($sql_steps);
        dbDelta($sql_questions);
        dbDelta($sql_submissions);
    }
    
    private function set_default_settings() {
        add_option('formxr_email_method', 'wp_mail');
        add_option('formxr_smtp_host', '');
        add_option('formxr_smtp_port', '587');
        add_option('formxr_smtp_username', '');
        add_option('formxr_smtp_password', '');
        add_option('formxr_smtp_secure', 'tls');
        add_option('formxr_from_email', get_option('admin_email'));
        add_option('formxr_from_name', get_bloginfo('name'));
        add_option('formxr_test_email', get_option('admin_email'));
        add_option('formxr_currency', 'USD');
        add_option('formxr_enable_notifications', 1);
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('FormXR Dashboard', 'formxr'),
            __('FormXR', 'formxr'),
            'manage_options',
            'formxr',
            array($this, 'admin_page'),
            'dashicons-feedback',
            30
        );
        
        // Change first submenu to Dashboard
        add_submenu_page(
            'formxr',
            __('Dashboard', 'formxr'),
            __('Dashboard', 'formxr'),
            'manage_options',
            'formxr',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'formxr',
            __('Questionnaires', 'formxr'),
            __('Questionnaires', 'formxr'),
            'manage_options',
            'formxr-questionnaires',
            array($this, 'questionnaires_page')
        );
        
        add_submenu_page(
            'formxr',
            __('Analytics', 'formxr'),
            __('Analytics', 'formxr'),
            'manage_options',
            'formxr-analytics',
            array($this, 'analytics_page')
        );
        
        add_submenu_page(
            'formxr',
            __('Submissions', 'formxr'),
            __('Submissions', 'formxr'),
            'manage_options',
            'formxr-submissions',
            array($this, 'submissions_page')
        );
        
        add_submenu_page(
            'formxr',
            __('Settings', 'formxr'),
            __('Settings', 'formxr'),
            'manage_options',
            'formxr-settings',
            array($this, 'settings_page')
        );
    }
    
    public function admin_page() {
        include FORMXR_PLUGIN_PATH . 'templates/admin-main.php';
    }
    
    public function questionnaires_page() {
        // Handle questionnaire actions
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'new':
                    include FORMXR_PLUGIN_PATH . 'templates/admin-questionnaire-new.php';
                    return;
                case 'edit':
                    include FORMXR_PLUGIN_PATH . 'templates/admin-questionnaire-edit.php';
                    return;
                case 'builder':
                    include FORMXR_PLUGIN_PATH . 'templates/admin-questionnaire-builder.php';
                    return;
            }
        }
        include FORMXR_PLUGIN_PATH . 'templates/admin-questionnaires.php';
    }
    
    public function settings_page() {
        include FORMXR_PLUGIN_PATH . 'templates/admin-settings.php';
    }
    
    public function submissions_page() {
        include FORMXR_PLUGIN_PATH . 'templates/admin-submissions.php';
    }
    
    public function analytics_page() {
        include FORMXR_PLUGIN_PATH . 'templates/admin-analytics.php';
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_script('formxr-frontend', FORMXR_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), FORMXR_VERSION, true);
        // Legacy CSS disabled - new frontend template has embedded CSS
        // wp_enqueue_style('formxr-frontend', FORMXR_PLUGIN_URL . 'assets/css/frontend.css', array(), FORMXR_VERSION);
        
        // Add Alpine.js for reactive UI
        wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', array(), '3.0.0', true);
        wp_script_add_data('alpinejs', 'defer', true);
        
        wp_localize_script('formxr-frontend', 'formxr_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('formxr_nonce'),
            'currency' => get_option('formxr_currency', 'USD'),
            'pricing_enabled' => get_option('formxr_pricing_enabled', 0)
        ));
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'formxr') !== false) {
            wp_enqueue_script('formxr-admin', FORMXR_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), FORMXR_VERSION, true);
            wp_enqueue_script('formxr-questionnaire-builder', FORMXR_PLUGIN_URL . 'assets/js/questionnaire-builder.js', array(), FORMXR_VERSION, true);
            wp_enqueue_style('formxr-admin', FORMXR_PLUGIN_URL . 'assets/css/admin.css', array(), FORMXR_VERSION);
            
            // Add Alpine.js for reactive UI in admin
            wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js', array(), '3.0.0', true);
            wp_script_add_data('alpinejs', 'defer', true);
            
            wp_localize_script('formxr-admin', 'formxr_admin_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('formxr_admin_nonce'),
                'strings' => array(
                    'confirm_delete' => __('Are you sure you want to delete this questionnaire?', 'formxr'),
                    'saving' => __('Saving...', 'formxr'),
                    'saved' => __('Saved!', 'formxr'),
                    'error' => __('Error occurred', 'formxr')
                )
            ));
        }
    }
    
    public function render_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'theme' => 'default'
        ), $atts);
        
        $questionnaire_id = intval($atts['id']);
        
        ob_start();
        include FORMXR_PLUGIN_PATH . 'templates/frontend-form.php';
        return ob_get_clean();
    }
    
    public function register_settings() {
        register_setting('formxr_settings', 'formxr_email_method');
        register_setting('formxr_settings', 'formxr_smtp_host');
        register_setting('formxr_settings', 'formxr_smtp_port');
        register_setting('formxr_settings', 'formxr_smtp_username');
        register_setting('formxr_settings', 'formxr_smtp_password');
        register_setting('formxr_settings', 'formxr_smtp_secure');
        register_setting('formxr_settings', 'formxr_from_email');
        register_setting('formxr_settings', 'formxr_from_name');
        register_setting('formxr_settings', 'formxr_test_email');
        register_setting('formxr_settings', 'formxr_currency');
        register_setting('formxr_settings', 'formxr_enable_notifications');
    }
    
    public function activation_notice() {
        if (get_transient('formxr_activation_notice')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <h3>🎉 <?php _e('FormXR Activated Successfully!', 'formxr'); ?></h3>
                <p>
                    <?php _e('Thank you for installing FormXR! Get started by:', 'formxr'); ?>
                </p>
                <ol>
                    <li><a href="<?php echo admin_url('admin.php?page=formxr-settings'); ?>"><?php _e('Configure your settings', 'formxr'); ?></a></li>
                    <li><a href="<?php echo admin_url('admin.php?page=formxr-questionnaires&action=new'); ?>"><?php _e('Create your first questionnaire', 'formxr'); ?></a></li>
                    <li><?php _e('Use the shortcode', 'formxr'); ?> <code>[formxr_form id="X"]</code> <?php _e('to display your form', 'formxr'); ?></li>
                </ol>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=formxr'); ?>" class="button button-primary"><?php _e('Go to FormXR Dashboard', 'formxr'); ?></a>
                    <a href="<?php echo admin_url('admin.php?page=formxr-settings'); ?>" class="button"><?php _e('Settings', 'formxr'); ?></a>
                </p>
            </div>
            <?php
            delete_transient('formxr_activation_notice');
        }
    }
    
    public function calculate_price_ajax() {
        check_ajax_referer('formxr_nonce', 'nonce');
        
        $questionnaire_id = isset($_POST['questionnaire_id']) ? intval($_POST['questionnaire_id']) : 0;
        $answers = isset($_POST['answers']) ? $_POST['answers'] : array();
        
        $calculated_price = $this->calculate_questionnaire_price($questionnaire_id, $answers);
        
        wp_send_json_success(array(
            'price' => $calculated_price,
            'formatted_price' => $this->format_price($calculated_price)
        ));
    }
    
    public function save_complete_questionnaire_ajax() {
        check_ajax_referer('formxr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'formxr'));
        }
        
        $questionnaire_data = isset($_POST['questionnaire_data']) ? json_decode(stripslashes($_POST['questionnaire_data']), true) : array();
        
        if (empty($questionnaire_data)) {
            wp_send_json_error(__('No questionnaire data provided', 'formxr'));
        }
        
        global $wpdb;
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Save questionnaire
            $questionnaire_id = $this->save_questionnaire(array(
                'title' => $questionnaire_data['title'],
                'description' => $questionnaire_data['description'],
                'pricing_enabled' => $questionnaire_data['pricing_enabled'] ? 1 : 0,
                'email_recipients' => sanitize_text_field($questionnaire_data['email_recipients'] ?? ''),
                'email_subject' => sanitize_text_field($questionnaire_data['email_subject'] ?? ''),
                'email_template' => wp_kses_post($questionnaire_data['email_template'] ?? ''),
                'notification_enabled' => $questionnaire_data['notification_enabled'] ? 1 : 0,
                'status' => 'active'
            ));
            
            if (!$questionnaire_id) {
                throw new Exception('Failed to save questionnaire');
            }
            
            // Save steps and questions
            foreach ($questionnaire_data['steps'] as $step_index => $step_data) {
                // Validate step title
                if (empty(trim($step_data['title']))) {
                    throw new Exception("Step " . ($step_index + 1) . " must have a title");
                }
                
                $step_id = $this->save_step(array(
                    'questionnaire_id' => $questionnaire_id,
                    'step_number' => $step_index + 1,
                    'title' => $step_data['title'],
                    'description' => $step_data['description'] ?? '',
                    'can_skip' => false,
                    'step_order' => $step_index
                ));
                
                if (is_wp_error($step_id)) {
                    throw new Exception('Failed to save step: ' . $step_id->get_error_message());
                }
                
                if (!$step_id) {
                    throw new Exception('Failed to save step: ' . $wpdb->last_error . ' | Query: ' . $wpdb->last_query);
                }
                
                // Validate that step has at least one question
                if (empty($step_data['questions']) || !is_array($step_data['questions'])) {
                    throw new Exception("Step " . ($step_index + 1) . " must have at least one question");
                }
                
                // Check if step has at least one question with text
                $hasValidQuestion = false;
                foreach ($step_data['questions'] as $question_data) {
                    if (!empty(trim($question_data['text']))) {
                        $hasValidQuestion = true;
                        break;
                    }
                }
                
                if (!$hasValidQuestion) {
                    throw new Exception("Step " . ($step_index + 1) . " must have at least one question with text");
                }
                
                // Save questions for this step
                foreach ($step_data['questions'] as $question_index => $question_data) {
                    // Skip questions without text
                    if (empty(trim($question_data['text']))) {
                        continue;
                    }
                    
                    $options = '';
                    if (!empty($question_data['options'])) {
                        $options = json_encode($question_data['options']);
                    }
                    
                    $question_id = $this->save_question(array(
                        'step_id' => $step_id,
                        'question_text' => $question_data['text'],
                        'question_type' => $question_data['type'],
                        'options' => $options,
                        'is_required' => $question_data['required'] ? 1 : 0,
                        'question_order' => $question_index,
                        'pricing_amount' => floatval($question_data['price_impact'] ?? 0)
                    ));
                    
                    if (!$question_id) {
                        throw new Exception('Failed to save question: ' . $wpdb->last_error);
                    }
                }
            }
            
            // Save conditions if any
            if (!empty($questionnaire_data['conditions'])) {
                foreach ($questionnaire_data['conditions'] as $condition) {
                    // Save conditions logic here
                    // For now, store as JSON in questionnaire table
                    $wpdb->update(
                        $wpdb->prefix . 'formxr_questionnaires',
                        array('conditions' => json_encode($questionnaire_data['conditions'])),
                        array('id' => $questionnaire_id)
                    );
                }
            }
            
            $wpdb->query('COMMIT');
            
            wp_send_json_success(array(
                'id' => $questionnaire_id,
                'message' => __('Questionnaire created successfully!', 'formxr')
            ));
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(__('Failed to create questionnaire: ', 'formxr') . $e->getMessage());
        }
    }
    
    public function delete_questionnaire_ajax() {
        check_ajax_referer('formxr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'formxr'));
        }
        
        $questionnaire_id = isset($_POST['questionnaire_id']) ? intval($_POST['questionnaire_id']) : 0;
        
        if ($this->delete_questionnaire($questionnaire_id)) {
            wp_send_json_success(__('Questionnaire deleted successfully!', 'formxr'));
        } else {
            wp_send_json_error(__('Failed to delete questionnaire', 'formxr'));
        }
    }
    
    public function reorder_questions_ajax() {
        check_ajax_referer('formxr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'formxr'));
        }
        
        $question_ids = isset($_POST['question_ids']) ? $_POST['question_ids'] : array();
        
        if ($this->reorder_questions($question_ids)) {
            wp_send_json_success(__('Questions reordered successfully!', 'formxr'));
        } else {
            wp_send_json_error(__('Failed to reorder questions', 'formxr'));
        }
    }
    
    public function test_email_ajax() {
        check_ajax_referer('formxr_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Unauthorized', 'formxr'));
        }
        
        $test_email = sanitize_email($_POST['test_email']);
        
        if (empty($test_email)) {
            wp_send_json_error(__('Please provide a valid email address', 'formxr'));
        }
        
        $result = $this->send_test_email($test_email);
        
        if ($result) {
            wp_send_json_success(__('Test email sent successfully!', 'formxr'));
        } else {
            wp_send_json_error(__('Failed to send test email. Please check your email settings.', 'formxr'));
        }
    }
    
    public function handle_form_submission() {
        check_ajax_referer('formxr_submit_form', 'nonce');
        
        $questionnaire_id = isset($_POST['questionnaire_id']) ? intval($_POST['questionnaire_id']) : 0;
        $form_data = isset($_POST['form_data']) ? json_decode(stripslashes($_POST['form_data']), true) : array();
        $calculated_price = isset($_POST['calculated_price']) ? floatval($_POST['calculated_price']) : 0;
        $price_type = isset($_POST['price_type']) ? sanitize_text_field($_POST['price_type']) : 'monthly';
        $email = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '';
        
        // If email is in form_data, extract it
        if (empty($email) && isset($form_data['customer_email'])) {
            $email = sanitize_email($form_data['customer_email']);
        }
        
        // Validate required fields
        if (!$questionnaire_id) {
            wp_send_json_error('Missing questionnaire ID');
            return;
        }
        
        // Get questionnaire data
        global $wpdb;
        $questionnaire = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}formxr_questionnaires WHERE id = %d",
            $questionnaire_id
        ));
        
        if (!$questionnaire) {
            wp_send_json_error('Questionnaire not found');
            return;
        }
        
        // Store submission
        $table_name = $wpdb->prefix . 'formxr_submissions';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'questionnaire_id' => $questionnaire_id,
                'submission_data' => json_encode($form_data),
                'calculated_price' => $calculated_price,
                'price_type' => $price_type,
                'user_email' => $email,
                'user_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'submitted_at' => current_time('mysql')
            ),
            array('%d', '%s', '%f', '%s', '%s', '%s', '%s')
        );
        
        if ($result !== false) {
            // Send email notification if enabled
            $email_enabled = get_option('formxr_email_notifications', 1);
            if ($email_enabled) {
                $this->send_submission_notification($questionnaire, $form_data, $calculated_price, $email, $price_type);
            }
            
            wp_send_json_success(array(
                'message' => __('Form submitted successfully!', 'formxr'),
                'price' => $calculated_price,
                'formatted_price' => $this->format_price($calculated_price)
            ));
        } else {
            wp_send_json_error(__('Failed to save submission', 'formxr'));
        }
    }
    
    // Database Helper Methods
    public function save_questionnaire($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'formxr_questionnaires';
        
        $questionnaire_data = array(
            'title' => sanitize_text_field($data['title']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'pricing_enabled' => isset($data['pricing_enabled']) ? 1 : 0,
            'min_price' => floatval($data['min_price'] ?? 100),
            'max_price' => floatval($data['max_price'] ?? 2000),
            'base_price' => floatval($data['base_price'] ?? 500),
            'currency' => sanitize_text_field($data['currency'] ?? 'USD'),
            'form_title' => sanitize_text_field($data['form_title'] ?? ''),
            'form_description' => sanitize_textarea_field($data['form_description'] ?? ''),
            'email_recipients' => sanitize_textarea_field($data['email_recipients'] ?? ''),
            'email_subject' => sanitize_text_field($data['email_subject'] ?? ''),
            'email_template' => wp_kses_post($data['email_template'] ?? ''),
            'notification_enabled' => isset($data['notification_enabled']) ? 1 : 0,
            'status' => sanitize_text_field($data['status'] ?? 'active')
        );
        
        if (isset($data['id']) && $data['id'] > 0) {
            // Update existing
            $questionnaire_data['updated_at'] = current_time('mysql');
            $result = $wpdb->update(
                $table_name,
                $questionnaire_data,
                array('id' => intval($data['id'])),
                array('%s', '%s', '%d', '%f', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s'),
                array('%d')
            );
            return $result !== false ? $data['id'] : false;
        } else {
            // Insert new
            $result = $wpdb->insert($table_name, $questionnaire_data);
            return $result !== false ? $wpdb->insert_id : false;
        }
    }
    
    public function save_step($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'formxr_steps';
        
        // Check if table exists and has required columns
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            return new WP_Error('table_missing', 'Steps table does not exist');
        }
        
        // Check for required columns
        $columns = $wpdb->get_col("SHOW COLUMNS FROM `$table_name`");
        $required_columns = array('questionnaire_id', 'step_number', 'title', 'description', 'can_skip', 'step_order');
        $missing_columns = array_diff($required_columns, $columns);
        
        if (!empty($missing_columns)) {
            // Try to run database upgrade
            $this->upgrade_database_to_2_0();
            
            // Re-check columns
            $columns = $wpdb->get_col("SHOW COLUMNS FROM `$table_name`");
            $missing_columns = array_diff($required_columns, $columns);
            
            if (!empty($missing_columns)) {
                return new WP_Error('missing_columns', 'Missing columns: ' . implode(', ', $missing_columns));
            }
        }
        
        $step_data = array(
            'questionnaire_id' => intval($data['questionnaire_id']),
            'step_number' => intval($data['step_number'] ?? 1),
            'title' => sanitize_text_field($data['title']),
            'description' => sanitize_textarea_field($data['description'] ?? ''),
            'can_skip' => isset($data['can_skip']) ? 1 : 0,
            'step_order' => intval($data['step_order'] ?? 0)
        );
        
        $formats = array('%d', '%d', '%s', '%s', '%d', '%d');
        
        if (isset($data['id']) && $data['id'] > 0) {
            // Update existing
            $result = $wpdb->update(
                $table_name,
                $step_data,
                array('id' => intval($data['id'])),
                $formats,
                array('%d')
            );
            
            if ($result === false) {
                return new WP_Error('update_failed', 'Database update failed: ' . $wpdb->last_error);
            }
            
            return $result !== false ? $data['id'] : false;
        } else {
            // Insert new
            $result = $wpdb->insert($table_name, $step_data, $formats);
            
            if ($result === false) {
                return new WP_Error('insert_failed', 'Database insert failed: ' . $wpdb->last_error);
            }
            
            return $result !== false ? $wpdb->insert_id : false;
        }
    }
    
    public function save_question($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'formxr_questions';
        
        $question_data = array(
            'step_id' => intval($data['step_id']),
            'question_text' => sanitize_textarea_field($data['question_text']),
            'question_type' => sanitize_text_field($data['question_type']),
            'options' => sanitize_textarea_field($data['options'] ?? ''),
            'is_required' => isset($data['is_required']) ? 1 : 0,
            'question_order' => intval($data['question_order'] ?? 0),
            'pricing_amount' => floatval($data['pricing_amount'] ?? 0),
            'pricing_visibility' => sanitize_text_field($data['pricing_visibility'] ?? 'hidden'),
            'conditions' => sanitize_textarea_field($data['conditions'] ?? '')
        );
        
        if (isset($data['id']) && $data['id'] > 0) {
            // Update existing
            $result = $wpdb->update(
                $table_name,
                $question_data,
                array('id' => intval($data['id'])),
                array('%d', '%s', '%s', '%s', '%d', '%d', '%f', '%s', '%s'),
                array('%d')
            );
            return $result !== false ? $data['id'] : false;
        } else {
            // Insert new
            $result = $wpdb->insert($table_name, $question_data);
            return $result !== false ? $wpdb->insert_id : false;
        }
    }
    
    public function delete_questionnaire($questionnaire_id) {
        global $wpdb;
        
        $questionnaire_id = intval($questionnaire_id);
        
        // Delete in reverse order (submissions, questions, steps, questionnaire)
        $wpdb->delete($wpdb->prefix . 'formxr_submissions', array('questionnaire_id' => $questionnaire_id));
        
        // Get steps to delete questions
        $steps = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}formxr_steps WHERE questionnaire_id = %d",
            $questionnaire_id
        ));
        
        foreach ($steps as $step_id) {
            $wpdb->delete($wpdb->prefix . 'formxr_questions', array('step_id' => $step_id));
        }
        
        $wpdb->delete($wpdb->prefix . 'formxr_steps', array('questionnaire_id' => $questionnaire_id));
        $wpdb->delete($wpdb->prefix . 'formxr_questionnaires', array('id' => $questionnaire_id));
        
        return true;
    }
    
    public function reorder_questions($question_ids) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'formxr_questions';
        
        foreach ($question_ids as $order => $question_id) {
            $wpdb->update(
                $table_name,
                array('question_order' => $order + 1),
                array('id' => intval($question_id)),
                array('%d'),
                array('%d')
            );
        }
        
        return true;
    }
    
    public function get_questionnaire($questionnaire_id) {
        global $wpdb;
        
        $questionnaire = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}formxr_questionnaires WHERE id = %d",
            $questionnaire_id
        ));
        
        if (!$questionnaire) {
            return false;
        }
        
        // Get steps
        $steps = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}formxr_steps WHERE questionnaire_id = %d ORDER BY step_order ASC",
            $questionnaire_id
        ));
        
        // Get questions for each step
        foreach ($steps as $step) {
            $step->questions = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}formxr_questions WHERE step_id = %d ORDER BY question_order ASC",
                $step->id
            ));
        }
        
        $questionnaire->steps = $steps;
        
        return $questionnaire;
    }
    
    private function calculate_questionnaire_price($questionnaire_id, $answers) {
        global $wpdb;
        
        // Get questionnaire pricing settings
        $questionnaire = $wpdb->get_row($wpdb->prepare(
            "SELECT base_price, min_price, max_price FROM {$wpdb->prefix}formxr_questionnaires WHERE id = %d",
            $questionnaire_id
        ));
        
        if (!$questionnaire) {
            return 0;
        }
        
        $base_price = floatval($questionnaire->base_price);
        $min_price = floatval($questionnaire->min_price);
        $max_price = floatval($questionnaire->max_price);
        
        $total_price = $base_price;
        
        // Get all questions for this questionnaire
        $questions = $wpdb->get_results($wpdb->prepare("
            SELECT q.*, s.step_number 
            FROM {$wpdb->prefix}formxr_questions q
            JOIN {$wpdb->prefix}formxr_steps s ON q.step_id = s.id
            WHERE s.questionnaire_id = %d
            ORDER BY s.step_order, q.question_order
        ", $questionnaire_id));
        
        foreach ($questions as $question) {
            $question_id = $question->id;
            
            if (!isset($answers[$question_id])) {
                continue;
            }
            
            $answer = $answers[$question_id];
            
            // Apply pricing from question
            if ($question->pricing_amount != 0) {
                $total_price += floatval($question->pricing_amount);
            }
            
            // Apply conditional pricing if conditions exist
            if (!empty($question->conditions)) {
                $conditions = json_decode($question->conditions, true);
                if (is_array($conditions)) {
                    foreach ($conditions as $condition) {
                        if ($this->evaluate_condition($condition, $answer)) {
                            $total_price += floatval($condition['price_change'] ?? 0);
                        }
                    }
                }
            }
        }
        
        // Apply min/max constraints
        $total_price = max($min_price, min($max_price, $total_price));
        
        return round($total_price, 2);
    }
    
    private function evaluate_condition($condition, $answer) {
        $operator = $condition['operator'] ?? 'equals';
        $value = $condition['value'] ?? '';
        
        switch ($operator) {
            case 'equals':
                return $answer === $value;
            case 'not_equals':
                return $answer !== $value;
            case 'contains':
                return strpos($answer, $value) !== false;
            case 'not_contains':
                return strpos($answer, $value) === false;
            case 'greater_than':
                return floatval($answer) > floatval($value);
            case 'less_than':
                return floatval($answer) < floatval($value);
            case 'greater_equal':
                return floatval($answer) >= floatval($value);
            case 'less_equal':
                return floatval($answer) <= floatval($value);
            default:
                return false;
        }
    }
    
    private function format_price($price) {
        $currency = get_option('formxr_currency', 'USD');
        return number_format($price, 2) . ' ' . $currency;
    }
    
    // Email Helper Methods
    private function send_test_email($test_email) {
        $subject = __('FormXR Test Email', 'formxr');
        $message = __('This is a test email from FormXR. If you received this, your email configuration is working correctly!', 'formxr');
        
        return $this->send_email($test_email, $subject, $message);
    }
    
    private function send_submission_notification($questionnaire, $form_data, $calculated_price, $user_email, $price_type = 'monthly') {
        // Check if notifications are enabled
        if (!get_option('formxr_enable_notifications', 1)) {
            return true;
        }
        
        $recipients = explode(',', $questionnaire->email_recipients ?? '');
        $recipients = array_map('trim', $recipients);
        $recipients = array_filter($recipients, 'is_email');
        
        // If no recipients configured, send to admin email
        if (empty($recipients)) {
            $recipients = array(get_option('admin_email'));
        }
        
        $subject = $questionnaire->email_subject ?: sprintf(__('New submission for %s', 'formxr'), $questionnaire->title);
        
        // Prepare email content
        if (!empty($questionnaire->email_template)) {
            $message = $this->process_email_template($questionnaire->email_template, $questionnaire, $form_data, $calculated_price, $user_email, $price_type);
        } else {
            $message = $this->get_default_email_template($questionnaire, $form_data, $calculated_price, $user_email, $price_type);
        }
        
        $success = true;
        foreach ($recipients as $recipient) {
            if (!$this->send_email($recipient, $subject, $message)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    private function send_email($to, $subject, $message) {
        $email_method = get_option('formxr_email_method', 'wp_mail');
        $from_email = get_option('formxr_from_email', get_option('admin_email'));
        $from_name = get_option('formxr_from_name', get_bloginfo('name'));
        
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = sprintf('From: %s <%s>', $from_name, $from_email);
        
        // Configure SMTP only if FormXR SMTP is selected and settings are provided
        if ($email_method === 'formxr_smtp') {
            $smtp_host = get_option('formxr_smtp_host');
            if (!empty($smtp_host)) {
                add_action('phpmailer_init', array($this, 'configure_smtp'));
            }
        }
        // For 'wp_mail' and 'plugin' methods, use default WordPress mail handling
        
        $result = wp_mail($to, $subject, $message, $headers);
        
        // Remove SMTP configuration after sending
        if ($email_method === 'formxr_smtp') {
            $smtp_host = get_option('formxr_smtp_host');
            if (!empty($smtp_host)) {
                remove_action('phpmailer_init', array($this, 'configure_smtp'));
            }
        }
        
        return $result;
    }
    
    public function configure_smtp($phpmailer) {
        $phpmailer->isSMTP();
        $phpmailer->Host = get_option('formxr_smtp_host');
        $phpmailer->Port = get_option('formxr_smtp_port', 587);
        $phpmailer->SMTPSecure = get_option('formxr_smtp_secure', 'tls');
        
        $username = get_option('formxr_smtp_username');
        $password = get_option('formxr_smtp_password');
        
        if (!empty($username) && !empty($password)) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $username;
            $phpmailer->Password = $password;
        }
    }
    
    private function process_email_template($template, $questionnaire, $form_data, $calculated_price, $user_email, $price_type = 'monthly') {
        $placeholders = array(
            '{{questionnaire_title}}' => $questionnaire->title,
            '{{user_email}}' => $user_email,
            '{{calculated_price}}' => $this->format_price($calculated_price),
            '{{price_type}}' => $price_type,
            '{{submission_date}}' => date('Y-m-d H:i:s'),
            '{{site_name}}' => get_bloginfo('name'),
            '{{site_url}}' => get_site_url()
        );
        
        // Add form data as placeholders
        foreach ($form_data as $key => $value) {
            $placeholders["{{" . $key . "}}"] = is_array($value) ? implode(', ', $value) : $value;
        }
        
        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }
    
    private function get_default_email_template($questionnaire, $form_data, $calculated_price, $user_email, $price_type = 'monthly') {
        $message = '<h2>' . sprintf(__('New submission for: %s', 'formxr'), esc_html($questionnaire->title)) . '</h2>';
        
        if (!empty($user_email)) {
            $message .= '<p><strong>' . __('User Email:', 'formxr') . '</strong> ' . esc_html($user_email) . '</p>';
        }
        
        if ($calculated_price > 0) {
            $price_display = $this->format_price($calculated_price);
            if ($price_type === 'monthly') {
                $price_display .= '/month';
            }
            $message .= '<p><strong>' . __('Calculated Price:', 'formxr') . '</strong> ' . $price_display . '</p>';
        }
        
        $message .= '<h3>' . __('Submission Details:', 'formxr') . '</h3>';
        
        foreach ($form_data as $key => $value) {
            $display_value = is_array($value) ? implode(', ', $value) : $value;
            $message .= '<p><strong>' . esc_html(ucwords(str_replace('_', ' ', $key))) . ':</strong> ' . esc_html($display_value) . '</p>';
        }
        
        $message .= '<hr>';
        $message .= '<p><em>' . sprintf(__('Submitted on %s at %s', 'formxr'), date('Y-m-d'), date('H:i:s')) . '</em></p>';
        
        return $message;
    }
    
    public function export_csv() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'formxr'));
        }
        
        global $wpdb;
        $submissions_table = $wpdb->prefix . 'formxr_submissions';
        $questionnaires_table = $wpdb->prefix . 'formxr_questionnaires';
        
        $submissions = $wpdb->get_results("
            SELECT s.*, q.title as questionnaire_title 
            FROM $submissions_table s 
            LEFT JOIN $questionnaires_table q ON s.questionnaire_id = q.id 
            ORDER BY s.submitted_at DESC
        ");
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="formxr-submissions-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, array(
            __('ID', 'formxr'),
            __('Questionnaire', 'formxr'),
            __('Email', 'formxr'),
            __('Price', 'formxr'),
            __('Submission Data', 'formxr'),
            __('Submitted At', 'formxr')
        ));
        
        foreach ($submissions as $submission) {
            fputcsv($output, array(
                $submission->id,
                $submission->questionnaire_title,
                $submission->user_email,
                $submission->calculated_price,
                $submission->submission_data,
                $submission->submitted_at
            ));
        }
        
        fclose($output);
        exit;
    }
}

// Initialize the plugin
new FormXR();
