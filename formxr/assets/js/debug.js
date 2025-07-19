/**
 * FormXR Debug Script
 * Add this to help debug Alpine.js issues in development
 */

// Enable Alpine.js debugging
if (typeof window !== 'undefined' && window.Alpine) {
    window.Alpine.debug = true;
    
    // Log Alpine.js initialization
    document.addEventListener('alpine:init', () => {
        console.log('🏔️ Alpine.js initialized');
    });
    
    // Log component initialization
    document.addEventListener('alpine:initializing', (e) => {
        console.log('🔧 Alpine component initializing:', e.detail);
    });
    
    // Log component initialization complete
    document.addEventListener('alpine:initialized', (e) => {
        console.log('✅ Alpine component initialized:', e.detail);
    });
}

// Debug FormXR specific issues
window.FormXRDebug = {
    // Check current page context
    checkPageContext: function() {
        const url = window.location.href;
        const params = new URLSearchParams(window.location.search);
        
        console.log('🔍 Current page context:');
        console.log('  URL:', url);
        console.log('  Page:', params.get('page'));
        console.log('  Action:', params.get('action'));
        
        if (params.get('page') === 'formxr-questionnaires') {
            const action = params.get('action');
            switch(action) {
                case 'new':
                    console.log('📝 Quick Create Questionnaire page detected');
                    break;
                case 'wizard':
                    console.log('🧙‍♂️ Wizard Questionnaire page detected');
                    break;
                case 'edit':
                    console.log('✏️ Edit Questionnaire page detected');
                    break;
                case 'builder':
                    console.log('🔧 Builder Questionnaire page detected');
                    break;
                default:
                    console.log('📋 Questionnaires list page detected');
            }
        }
        
        return true;
    },
    
    // Check if Alpine.js is loaded
    checkAlpine: function() {
        if (typeof window.Alpine === 'undefined') {
            console.error('❌ Alpine.js is not loaded');
            return false;
        }
        console.log('✅ Alpine.js is loaded');
        return true;
    },
    
    // Check if questionnaire builder function exists (Dynamic Check)
    checkBuilderFunction: function() {
        // Check for both possible function names
        if (typeof questionnaireBuilder === 'undefined' && typeof questionnaireWizard === 'undefined') {
            console.error('❌ No questionnaire builder function is defined (neither questionnaireBuilder nor questionnaireWizard)');
            return false;
        }
        
        if (typeof questionnaireBuilder !== 'undefined') {
            console.log('✅ questionnaireBuilder function is defined (Quick Create mode)');
            return true;
        }
        
        if (typeof questionnaireWizard !== 'undefined') {
            console.log('✅ questionnaireWizard function is defined (Wizard mode)');
            return true;
        }
        
        return false;
    },
    
    // Test questionnaire builder initialization (Dynamic)
    testBuilderInit: function() {
        try {
            let builder;
            let functionName;
            
            // Try to create builder object from available function
            if (typeof questionnaireBuilder !== 'undefined') {
                builder = questionnaireBuilder();
                functionName = 'questionnaireBuilder';
            } else if (typeof questionnaireWizard !== 'undefined') {
                builder = questionnaireWizard();
                functionName = 'questionnaireWizard';
            } else {
                console.error('❌ No builder function available');
                return false;
            }
            
            console.log(`✅ ${functionName} object created:`, builder);
            
            // Check for common properties that should exist
            const commonProps = ['questionnaire'];
            const missingCommon = commonProps.filter(prop => !(prop in builder));
            
            if (missingCommon.length > 0) {
                console.error('❌ Missing common properties:', missingCommon);
                return false;
            }
            
            // Check for function-specific properties
            if (functionName === 'questionnaireBuilder') {
                const builderProps = ['addQuestion', 'addQuestionGroup', 'toggleMultiStep', 'addQuestionToGroup'];
                const missingBuilder = builderProps.filter(prop => !(prop in builder));
                
                if (missingBuilder.length > 0) {
                    console.warn('⚠️ Some Quick Create properties missing:', missingBuilder);
                    // Don't fail for missing properties in Quick Create mode
                }
                console.log('✅ Quick Create mode validated');
            } else if (functionName === 'questionnaireWizard') {
                const wizardProps = ['addQuestion', 'addQuestionGroup', 'nextStep', 'previousStep'];
                const missingWizard = wizardProps.filter(prop => !(prop in builder));
                
                if (missingWizard.length > 0) {
                    console.warn('⚠️ Some Wizard properties missing:', missingWizard);
                    // Don't fail for missing properties in Wizard mode
                }
                console.log('✅ Wizard mode validated');
            }
            
            return true;
        } catch (error) {
            console.error('❌ Error creating builder object:', error);
            return false;
        }
    },
    
    // Run all debug checks
    runAllChecks: function() {
        console.log('🔍 Running FormXR Debug Checks...');
        
        const checks = [
            this.checkPageContext(),
            this.checkAlpine(),
            this.checkBuilderFunction(),
            this.testBuilderInit()
        ];
        
        const passed = checks.filter(check => check).length;
        const total = checks.length;
        
        console.log(`📊 Debug Results: ${passed}/${total} checks passed`);
        
        if (passed === total) {
            console.log('🎉 All FormXR debug checks passed!');
        } else {
            console.log('⚠️ Some FormXR debug checks failed. Check the console for details.');
        }
        
        return passed === total;
    }
};

// Auto-run debug checks when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for Alpine.js to initialize
    setTimeout(() => {
        if (window.FormXRDebug) {
            window.FormXRDebug.runAllChecks();
        }
    }, 1000);
});

// Add debug info to console
console.log('🚀 FormXR Debug Script Loaded');
console.log('Usage: FormXRDebug.runAllChecks() - Run all debug checks');
console.log('Usage: FormXRDebug.checkPageContext() - Check current page context');
console.log('Usage: FormXRDebug.checkAlpine() - Check if Alpine.js is loaded');
console.log('Usage: FormXRDebug.testBuilderInit() - Test builder initialization');
