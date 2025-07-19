/**
 * FormXR Admin Page Specific JavaScript
 * Page-specific functionality that extends the core admin functionality
 */

(function($) {
    'use strict';
    
    // Extend FormXRAdmin with page-specific functionality
    $.extend(window.FormXRAdmin, {
        
        // Page-specific initialization
        initPageSpecific: function() {
            this.initDashboard();
            this.initQuestionnaires();
            this.initSubmissions();
            this.initSettings();
        },
        
        // Dashboard specific functionality
        initDashboard: function() {
            if ($('.formxr-dashboard').length === 0) return;
            
            // Initialize dashboard charts
            this.initDashboardCharts();
            
            // Real-time stats update
            this.initRealTimeStats();
        },
        
        // Dashboard charts
        initDashboardCharts: function() {
            // Submissions chart
            const submissionsData = $('.formxr-submissions-chart').data('chart-data');
            if (submissionsData && typeof Chart !== 'undefined') {
                const ctx = document.getElementById('submissionsChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: submissionsData,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Submissions Over Time'
                                }
                            }
                        }
                    });
                }
            }
            
            // Revenue chart
            const revenueData = $('.formxr-revenue-chart').data('chart-data');
            if (revenueData && typeof Chart !== 'undefined') {
                const ctx = document.getElementById('revenueChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'bar',
                        data: revenueData,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Revenue Potential'
                                }
                            }
                        }
                    });
                }
            }
        },
        
        // Real-time stats update
        initRealTimeStats: function() {
            setInterval(() => {
                this.updateDashboardStats();
            }, 30000); // Update every 30 seconds
        },
        
        updateDashboardStats: function() {
            $.ajax({
                url: formxr_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'formxr_get_dashboard_stats',
                    nonce: formxr_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateStatsDisplay(response.data);
                    }
                }
            });
        },
        
        updateStatsDisplay: function(stats) {
            $('.formxr-stat-total-submissions').text(stats.total_submissions);
            $('.formxr-stat-submissions-today').text(stats.submissions_today);
            $('.formxr-stat-submissions-week').text(stats.submissions_week);
            $('.formxr-stat-submissions-month').text(stats.submissions_month);
        },
        
        // Questionnaires specific functionality
        initQuestionnaires: function() {
            if ($('.formxr-questionnaires').length === 0) return;
            
            // Questionnaire actions
            $(document).on('click', '.formxr-duplicate-questionnaire', this.duplicateQuestionnaire);
            $(document).on('click', '.formxr-toggle-questionnaire', this.toggleQuestionnaire);
        },
        
        duplicateQuestionnaire: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const questionnaireId = $btn.data('questionnaire-id');
            
            FormXRAdmin.showNotice('Duplicating questionnaire...', 'info');
            
            $.ajax({
                url: formxr_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'formxr_duplicate_questionnaire',
                    nonce: formxr_admin.nonce,
                    questionnaire_id: questionnaireId
                },
                success: (response) => {
                    if (response.success) {
                        FormXRAdmin.showNotice('Questionnaire duplicated successfully!', 'success');
                        location.reload();
                    } else {
                        FormXRAdmin.showNotice(response.data.message || 'Failed to duplicate questionnaire', 'error');
                    }
                },
                error: () => {
                    FormXRAdmin.showNotice('Error duplicating questionnaire', 'error');
                }
            });
        },
        
        toggleQuestionnaire: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const questionnaireId = $btn.data('questionnaire-id');
            const currentStatus = $btn.data('status');
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            
            $.ajax({
                url: formxr_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'formxr_toggle_questionnaire',
                    nonce: formxr_admin.nonce,
                    questionnaire_id: questionnaireId,
                    status: newStatus
                },
                success: (response) => {
                    if (response.success) {
                        $btn.data('status', newStatus);
                        $btn.text(newStatus === 'active' ? 'Deactivate' : 'Activate');
                        $btn.toggleClass('formxr-btn-success formxr-btn-secondary');
                        
                        FormXRAdmin.showNotice(`Questionnaire ${newStatus === 'active' ? 'activated' : 'deactivated'}`, 'success');
                    } else {
                        FormXRAdmin.showNotice(response.data.message || 'Failed to update questionnaire', 'error');
                    }
                },
                error: () => {
                    FormXRAdmin.showNotice('Error updating questionnaire', 'error');
                }
            });
        },
        
        // Submissions specific functionality
        initSubmissions: function() {
            if ($('.formxr-submissions').length === 0) return;
            
            // Bulk actions
            $(document).on('change', '.formxr-select-all-submissions', this.handleSelectAll);
            $(document).on('click', '.formxr-bulk-delete', this.handleBulkDelete);
            $(document).on('click', '.formxr-export-submissions', this.handleExportSubmissions);
        },
        
        handleSelectAll: function() {
            const checked = $(this).is(':checked');
            $('.formxr-submission-checkbox').prop('checked', checked);
        },
        
        handleBulkDelete: function(e) {
            e.preventDefault();
            
            const selectedIds = $('.formxr-submission-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            
            if (selectedIds.length === 0) {
                FormXRAdmin.showNotice('Please select submissions to delete', 'warning');
                return;
            }
            
            FormXRAdmin.confirm(`Are you sure you want to delete ${selectedIds.length} submission(s)?`, (confirmed) => {
                if (confirmed) {
                    $.ajax({
                        url: formxr_admin.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'formxr_bulk_delete_submissions',
                            nonce: formxr_admin.nonce,
                            submission_ids: selectedIds
                        },
                        success: (response) => {
                            if (response.success) {
                                FormXRAdmin.showNotice('Submissions deleted successfully', 'success');
                                location.reload();
                            } else {
                                FormXRAdmin.showNotice(response.data.message || 'Failed to delete submissions', 'error');
                            }
                        },
                        error: () => {
                            FormXRAdmin.showNotice('Error deleting submissions', 'error');
                        }
                    });
                }
            });
        },
        
        handleExportSubmissions: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const format = $btn.data('format') || 'csv';
            const questionnaireId = $btn.data('questionnaire-id') || '';
            
            // Show loading
            $btn.prop('disabled', true).text('Exporting...');
            
            // Create download link
            const url = new URL(formxr_admin.ajax_url);
            url.searchParams.set('action', 'formxr_export_submissions');
            url.searchParams.set('nonce', formxr_admin.nonce);
            url.searchParams.set('format', format);
            if (questionnaireId) {
                url.searchParams.set('questionnaire_id', questionnaireId);
            }
            
            // Trigger download
            const a = document.createElement('a');
            a.href = url.toString();
            a.download = `formxr-submissions-${new Date().toISOString().split('T')[0]}.${format}`;
            a.click();
            
            // Reset button
            setTimeout(() => {
                $btn.prop('disabled', false).text($btn.data('original-text') || 'Export');
            }, 2000);
        },
        
        // Settings specific functionality
        initSettings: function() {
            if ($('.formxr-settings').length === 0) return;
            
            // Test email functionality
            $(document).on('click', '.formxr-test-email', this.handleTestEmail);
            
            // Settings form auto-save
            $(document).on('change', '.formxr-settings input, .formxr-settings select, .formxr-settings textarea', 
                this.debounce(this.handleSettingsChange, 1000));
        },
        
        handleTestEmail: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const testEmail = $('#formxr_test_email').val();
            
            if (!testEmail) {
                FormXRAdmin.showNotice('Please enter a test email address', 'warning');
                return;
            }
            
            $btn.prop('disabled', true).text('Sending...');
            
            $.ajax({
                url: formxr_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'formxr_test_email',
                    nonce: formxr_admin.nonce,
                    test_email: testEmail
                },
                success: (response) => {
                    if (response.success) {
                        FormXRAdmin.showNotice('Test email sent successfully!', 'success');
                    } else {
                        FormXRAdmin.showNotice(response.data.message || 'Failed to send test email', 'error');
                    }
                    $btn.prop('disabled', false).text('Send Test Email');
                },
                error: () => {
                    FormXRAdmin.showNotice('Error sending test email', 'error');
                    $btn.prop('disabled', false).text('Send Test Email');
                }
            });
        },
        
        handleSettingsChange: function() {
            const $form = $('.formxr-settings-form');
            const formData = $form.serialize();
            
            FormXRAdmin.showNotice('Saving settings...', 'info', 1000);
            
            $.ajax({
                url: formxr_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'formxr_save_settings',
                    nonce: formxr_admin.nonce,
                    settings: formData
                },
                success: (response) => {
                    if (response.success) {
                        FormXRAdmin.showNotice('Settings saved', 'success', 2000);
                    } else {
                        FormXRAdmin.showNotice('Failed to save settings', 'error');
                    }
                },
                error: () => {
                    FormXRAdmin.showNotice('Error saving settings', 'error');
                }
            });
        }
    });
    
    // Initialize page-specific functionality when document is ready
    $(document).ready(function() {
        if (window.FormXRAdmin) {
            FormXRAdmin.initPageSpecific();
        }
    });
    
})(jQuery);