/**
 * FormXR Admin JavaScript
 * Core admin functionality and utilities
 */

(function($) {
    'use strict';
    
    // Global FormXR Admin object
    window.FormXRAdmin = {
        
        // Initialize admin functionality
        init: function() {
            this.bindEvents();
            this.initComponents();
            this.initTooltips();
        },
        
        // Bind global event handlers
        bindEvents: function() {
            // Navigation handling
            $(document).on('click', '.formxr-nav-item', this.handleNavigation);
            
            // Form submissions
            $(document).on('submit', '.formxr-form', this.handleFormSubmit);
            
            // Button loading states
            $(document).on('click', '.formxr-btn[data-loading]', this.handleButtonLoading);
            
            // Confirm dialogs
            $(document).on('click', '[data-confirm]', this.handleConfirmDialog);
            
            // Tab switching
            $(document).on('click', '.formxr-tab-nav [data-tab]', this.handleTabSwitch);
            
            // Auto-save functionality
            $(document).on('input change', '[data-autosave]', this.debounce(this.handleAutoSave, 1000));
        },
        
        // Initialize components
        initComponents: function() {
            this.initCharts();
            this.initDataTables();
            this.initDatePickers();
            this.initColorPickers();
        },
        
        // Initialize tooltips
        initTooltips: function() {
            if (typeof jQuery.fn.tooltip !== 'undefined') {
                $('.formxr-tooltip').tooltip();
            }
        },
        
        // Handle navigation
        handleNavigation: function(e) {
            const $item = $(this);
            const href = $item.attr('href');
            
            // Add loading state
            $item.addClass('loading');
            
            // Remove loading state after navigation
            setTimeout(() => {
                $item.removeClass('loading');
            }, 500);
        },
        
        // Handle form submissions
        handleFormSubmit: function(e) {
            const $form = $(this);
            const $submitBtn = $form.find('[type="submit"]');
            
            // Add loading state
            $submitBtn.prop('disabled', true).addClass('loading');
            
            // Add spinner
            const originalText = $submitBtn.html();
            $submitBtn.html('<span class="formxr-spinner"></span> ' + $submitBtn.data('loading-text') || 'Processing...');
            
            // Reset on completion (this would be called from server response)
            setTimeout(() => {
                $submitBtn.prop('disabled', false).removeClass('loading').html(originalText);
            }, 2000);
        },
        
        // Handle button loading states
        handleButtonLoading: function(e) {
            const $btn = $(this);
            const loadingText = $btn.data('loading-text') || 'Loading...';
            const originalText = $btn.html();
            
            $btn.prop('disabled', true)
                .addClass('loading')
                .html('<span class="formxr-spinner"></span> ' + loadingText);
            
            // This would typically be reset by the actual operation completion
        },
        
        // Handle confirm dialogs
        handleConfirmDialog: function(e) {
            const message = $(this).data('confirm');
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        },
        
        // Handle tab switching
        handleTabSwitch: function(e) {
            e.preventDefault();
            
            const $tab = $(this);
            const targetTab = $tab.data('tab');
            const $tabContainer = $tab.closest('.formxr-tabs');
            
            // Update tab navigation
            $tabContainer.find('.formxr-tab-nav [data-tab]').removeClass('active');
            $tab.addClass('active');
            
            // Update tab content
            $tabContainer.find('.formxr-tab-content > div').removeClass('active');
            $tabContainer.find('.formxr-tab-content [data-tab-content="' + targetTab + '"]').addClass('active');
        },
        
        // Handle auto-save
        handleAutoSave: function() {
            const $input = $(this);
            const formData = $input.closest('form').serialize();
            
            // Show saving indicator
            FormXRAdmin.showNotice('Saving...', 'info', 1000);
            
            // Make AJAX request to save
            $.ajax({
                url: formxr_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'formxr_autosave',
                    nonce: formxr_admin.nonce,
                    form_data: formData
                },
                success: function(response) {
                    if (response.success) {
                        FormXRAdmin.showNotice('Saved', 'success', 1000);
                    } else {
                        FormXRAdmin.showNotice('Save failed', 'error', 3000);
                    }
                },
                error: function() {
                    FormXRAdmin.showNotice('Save failed', 'error', 3000);
                }
            });
        },
        
        // Initialize charts (if Chart.js is available)
        initCharts: function() {
            if (typeof Chart === 'undefined') return;
            
            $('.formxr-chart').each(function() {
                const $canvas = $(this);
                const chartData = $canvas.data('chart');
                
                if (chartData) {
                    new Chart($canvas[0], chartData);
                }
            });
        },
        
        // Initialize data tables
        initDataTables: function() {
            if (typeof jQuery.fn.DataTable === 'undefined') return;
            
            $('.formxr-data-table').DataTable({
                responsive: true,
                pageLength: 25,
                language: {
                    search: 'Search:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Previous'
                    }
                }
            });
        },
        
        // Initialize date pickers
        initDatePickers: function() {
            if (typeof jQuery.fn.datepicker === 'undefined') return;
            
            $('.formxr-date-picker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });
        },
        
        // Initialize color pickers
        initColorPickers: function() {
            if (typeof jQuery.fn.spectrum === 'undefined') return;
            
            $('.formxr-color-picker').spectrum({
                preferredFormat: 'hex',
                showInput: true,
                allowEmpty: true
            });
        },
        
        // Utility: Debounce function
        debounce: function(func, wait, immediate) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },
        
        // Show notification
        showNotice: function(message, type = 'info', duration = 3000) {
            const $notice = $('<div class="formxr-notice formxr-notice-' + type + '">' + message + '</div>');
            
            // Add to page
            $('body').append($notice);
            
            // Animate in
            $notice.fadeIn(200);
            
            // Auto remove
            setTimeout(() => {
                $notice.fadeOut(200, function() {
                    $(this).remove();
                });
            }, duration);
        },
        
        // Confirm dialog with custom styling
        confirm: function(message, callback) {
            const $modal = $(`
                <div class="formxr-modal-overlay">
                    <div class="formxr-modal">
                        <div class="formxr-modal-header">
                            <h3>Confirm Action</h3>
                        </div>
                        <div class="formxr-modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="formxr-modal-footer">
                            <button class="formxr-btn formxr-btn-secondary formxr-cancel">Cancel</button>
                            <button class="formxr-btn formxr-btn-primary formxr-confirm">Confirm</button>
                        </div>
                    </div>
                </div>
            `);
            
            $('body').append($modal);
            
            $modal.find('.formxr-confirm').on('click', function() {
                callback(true);
                $modal.remove();
            });
            
            $modal.find('.formxr-cancel, .formxr-modal-overlay').on('click', function(e) {
                if (e.target === this) {
                    callback(false);
                    $modal.remove();
                }
            });
        },
        
        // Export functionality
        exportData: function(type, data) {
            if (type === 'csv') {
                this.exportCSV(data);
            } else if (type === 'json') {
                this.exportJSON(data);
            }
        },
        
        // Export as CSV
        exportCSV: function(data) {
            const csv = this.arrayToCSV(data);
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'formxr-export-' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        },
        
        // Export as JSON
        exportJSON: function(data) {
            const json = JSON.stringify(data, null, 2);
            const blob = new Blob([json], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'formxr-export-' + new Date().toISOString().split('T')[0] + '.json';
            a.click();
            window.URL.revokeObjectURL(url);
        },
        
        // Convert array to CSV
        arrayToCSV: function(data) {
            if (!data.length) return '';
            
            const headers = Object.keys(data[0]);
            const csvContent = [
                headers.join(','),
                ...data.map(row => headers.map(header => {
                    const value = row[header];
                    return typeof value === 'string' && value.includes(',') ? `"${value}"` : value;
                }).join(','))
            ].join('\n');
            
            return csvContent;
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        FormXRAdmin.init();
    });
    
})(jQuery);
