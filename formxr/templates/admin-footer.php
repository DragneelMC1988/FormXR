<?php
/**
 * Admin Footer Template
 * Shared footer for all admin pages
 */
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="formxr-admin-footer">
    <div class="formxr-footer-content">
        <div class="formxr-footer-left">
            <p class="formxr-footer-text">
                <strong>FormXR</strong> <?php _e('is part of the', 'formxr'); ?> 
                <strong class="formxr-expoxr-brand">ExpoXR Family</strong>
            </p>
            <p class="formxr-footer-version">
                <?php printf(__('Version %s', 'formxr'), FORMXR_VERSION); ?>
            </p>
        </div>
        
        <div class="formxr-footer-right">
            <div class="formxr-footer-links">
                <a href="https://expoxr.com" target="_blank" class="formxr-footer-link">
                    <?php _e('ExpoXR.com', 'formxr'); ?>
                </a>
                <a href="https://docs.expoxr.com/formxr" target="_blank" class="formxr-footer-link">
                    <?php _e('Documentation', 'formxr'); ?>
                </a>
                <a href="https://support.expoxr.com" target="_blank" class="formxr-footer-link">
                    <?php _e('Support', 'formxr'); ?>
                </a>
            </div>
            
            <div class="formxr-footer-social">
                <a href="https://twitter.com/expoxr" target="_blank" class="formxr-social-link" title="Follow us on Twitter">
                    <span class="formxr-social-icon">🐦</span>
                </a>
                <a href="https://github.com/expoxr/formxr" target="_blank" class="formxr-social-link" title="View on GitHub">
                    <span class="formxr-social-icon">🐙</span>
                </a>
                <a href="https://linkedin.com/company/expoxr" target="_blank" class="formxr-social-link" title="Connect on LinkedIn">
                    <span class="formxr-social-icon">💼</span>
                </a>
            </div>
        </div>
    </div>
    
    <div class="formxr-footer-bottom">
        <p class="formxr-footer-copyright">
            &copy; <?php echo date('Y'); ?> ExpoXR. <?php _e('All rights reserved.', 'formxr'); ?>
        </p>
    </div>
</div>
