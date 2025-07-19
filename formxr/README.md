# FormXR - Advanced Questionnaire System

A comprehensive WordPress plugin that creates multi-step questionnaires with dynamic pricing calculations, email notifications, and modern Alpine.js interface. Perfect for service providers who want to provide personalized pricing based on client needs.

## Features

### 🎯 Multi-Step Questionnaire System
- Create unlimited questionnaires with custom steps and questions
- Support for multiple input types (text, textarea, select, radio, checkbox, number, email)
- Progress indicator and step navigation
- Responsive, mobile-friendly design with Alpine.js

### 💰 Dynamic Pricing Engine
- Per-questionnaire base price configuration
- Per-question price impact (fixed amount or percentage)
- Advanced conditional pricing rules
- Multiple currency support
- Real-time price calculation

### 📧 Advanced Email System
- SMTP configuration with TLS/SSL support
- Test email functionality with modal interface
- Multi-recipient notifications
- Template placeholders for dynamic content
- User confirmation emails

### 🛠️ Modern Admin Interface
- Database-driven questionnaire management
- Intuitive settings page for SMTP configuration
- Submissions dashboard with detailed analytics
- Alpine.js powered interactive forms

## Installation

### Automatic Installation
1. Go to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Search for "FormXR"
4. Install and activate the plugin

### Manual Installation
1. Download the plugin files
   - Upload the `formxr` folder to `/wp-content/plugins/`
2. Access your WordPress admin dashboard
   - Go to **Plugins > Installed Plugins**
   - Find "FormXR" and click "Activate"

## Quick Start

1. After activation:
   - Go to FormXR in the admin menu
   - Configure SMTP settings for email functionality

## Configuration

### SMTP Settings
1. Navigate to **FormXR > Settings**
2. Configure your SMTP server details:
   - SMTP Host (e.g., smtp.gmail.com)
   - SMTP Port (587 for TLS, 465 for SSL)
   - Username and Password
   - Encryption method (TLS/SSL)

### Creating Questionnaires
1. Go to **FormXR > Questionnaires** or **Add New Questionnaire**
2. Set questionnaire title and description
3. Add steps to organize your questions
4. Add questions with various input types:
   - Text input
   - Textarea
   - Select dropdown
   - Radio buttons
   - Checkboxes
   - Number input
   - Email input
5. Configure pricing impact for each question option

### Displaying Forms
Use the shortcode `[formxr_form]` on any page or post to display your form.

#### Shortcode Options
```
[formxr_form id="1"]  // Display specific questionnaire
[formxr_form]         // Display first active questionnaire
```

## Database Structure

FormXR uses a modern database structure with dedicated tables:

- **Questionnaires**: `wp_formxr_questionnaires` - Main questionnaire data
- **Steps**: `wp_formxr_steps` - Questionnaire steps
- **Questions**: `wp_formxr_questions` - Individual questions
- **Submissions**: `wp_formxr_submissions` - Form submissions

## AJAX Endpoints

- `formxr_submit_form`: Form submission handling
- `formxr_calculate_price`: Real-time price calculation
- `formxr_save_questionnaire`: Questionnaire management
- `formxr_test_email`: Email configuration testing
- `formxr_export_csv`: Data export

## Frontend Integration

### Basic Usage
```html
[formxr_form]
```

### Advanced Usage
```html
[formxr_form id="2"]
```

## Data Structure

- **Questionnaires**: Database table `wp_formxr_questionnaires`
- **Steps**: Database table `wp_formxr_steps`
- **Questions**: Database table `wp_formxr_questions`
- **Submissions**: Database table `wp_formxr_submissions`

## Styling

### CSS Classes
```css
.formxr-form-container {
    /* Main form container */
}

.formxr-price-display {
    /* Price display area */
}
```

## Customization

### Custom Price Calculation
```php
add_filter('formxr_calculate_price', 'your_custom_function');
```

### Form HTML Modification
```php
add_filter('formxr_form_html', 'your_form_modifier');
```

## Troubleshooting

### Common Issues

1. **Form not displaying**
   - Check if shortcode is spelled correctly: `[formxr_form]`
   - Verify questionnaire is marked as active
   - Check if questionnaire has steps and questions

2. **Email not sending**
   - Verify SMTP settings in FormXR > Settings
   - Use the test email feature to verify configuration
   - Check server email logs

3. **Price calculation not working**
   - Ensure questions have pricing values configured
   - Check browser console for JavaScript errors
   - Verify Alpine.js is loading correctly

4. **Admin interface issues**
   - Clear browser cache
   - Check for JavaScript conflicts with other plugins
   - Verify WordPress admin permissions

## Support

For support and feature requests, please contact the plugin developer or check the plugin documentation.

## Changelog

### Version 2.0
- Complete rewrite with modern database structure
- Alpine.js integration for reactive frontend
- Improved SMTP configuration with test functionality
- Modern admin interface
- Better error handling and validation

### Version 1.x (Legacy ExScopeXR)
- Initial WordPress post-type based system
- Basic questionnaire functionality
- jQuery-based frontend
- CSV export functionality
- Price rule builder with visual interface

### 🎨 Professional Design
- Modern, gradient-based styling
- Smooth animations and transitions
- Mobile-responsive layout
- Customizable form appearance

## Installation

1. **Upload the Plugin**
   - Download the plugin files
   - Upload the `exscopexr` folder to `/wp-content/plugins/`
   - Or install via WordPress admin: Plugins > Add New > Upload Plugin

2. **Activate the Plugin**
   - Go to Plugins page in WordPress admin
   - Find "ExScopeXR" and click "Activate"

3. **Initial Setup**
   - Go to ExScopeXR in the admin menu
   - Configure basic settings (min/max price, currency, etc.)
   - Create your first questions

## Quick Start Guide

### Step 1: Configure Settings
1. Navigate to **ExScopeXR > Settings**
2. Set your pricing parameters:
   - **Base Price**: Starting price (e.g., 500)
   - **Minimum Price**: Lowest possible price (e.g., 100)
   - **Maximum Price**: Highest possible price (e.g., 2000)
   - **Currency**: Your preferred currency (EUR, USD, GBP, etc.)

### Step 2: Create Questions
1. Go to **ExScopeXR > Questions** or **Add New Question**
2. Configure each question:
   - **Title**: The question text
   - **Step Number**: Which step it appears in
   - **Question Type**: Choose input type
   - **Options**: For select/radio/checkbox types
   - **Price Impact**: How this question affects pricing
   - **Required**: Whether the field is mandatory

### Step 3: Set Up Pricing Logic
For each question, you can:
- Set a **basic price impact** (fixed amount or percentage)
- Create **advanced price rules** for specific answers
- Example: "If answer equals 'Yes', add +200 EUR"

### Step 4: Embed the Form
Use the shortcode `[exseo_form]` on any page or post to display your form.

## Question Types

| Type | Description | Use Case |
|------|-------------|----------|
| **Text** | Single line text input | Company name, website URL |
| **Textarea** | Multi-line text area | Project description |
| **Select** | Dropdown menu | Budget range, service type |
| **Radio** | Single choice buttons | Yes/No questions |
| **Checkbox** | Multiple selections | Services needed |
| **Number** | Numeric input | Number of pages, employees |
| **Range** | Slider input | Scale ratings (1-10) |

## Pricing Configuration

### Basic Price Impact
Each question can have a basic price impact:
- **None**: No effect on price
- **Fixed**: Add/subtract a specific amount
- **Percentage**: Add/subtract a percentage of current price

### Advanced Price Rules
Create conditional pricing based on specific answers:
```
If answer "equals" "Enterprise" then change price by "+500"
If answer "contains" "urgent" then change price by "+300"
If answer "greater than" "100" then change price by "+1000"
```

### Pricing Constraints
- **Minimum Price**: Prevents prices from going too low
- **Maximum Price**: Caps the highest possible price
- **Base Price**: Starting point for calculations

## Form Customization

### Display Options
- **Show Price During Progress**: Live price updates as users answer
- **Price Type Toggle**: Allow switching between monthly/one-time
- **Email Collection**: Require email addresses
- **Form Title & Description**: Customize the header

### Styling
The plugin includes modern CSS with:
- Gradient backgrounds
- Smooth animations
- Mobile-responsive design
- Professional color scheme

## Admin Features

### Dashboard Overview
- Total questions created
- Form submissions count
- Recent activity
- Quick action buttons

### Submissions Management
- View all form submissions
- Detailed submission analysis
- User answers and calculated prices
- Export to CSV for external analysis

### Analytics
- Average pricing
- Submission trends
- Popular answer patterns
- Revenue projections

## Shortcode Usage

### Basic Usage
```
[exseo_form]
```

### With Parameters (Future Enhancement)
```
[exseo_form theme="modern" steps="1,2,3"]
```

## Technical Details

### Database Tables
- **Questions**: Stored as custom post type `exseo_question`
- **Submissions**: Custom table `wp_exseo_submissions`
- **Settings**: WordPress options table

### AJAX Endpoints
- `exseo_calculate_price`: Real-time price calculation
- `exseo_submit_form`: Form submission handling
- `exseo_export_csv`: Data export

### Security Features
- Nonce verification for all forms
- Input sanitization and validation
- User capability checks
- SQL injection protection

## Customization

### CSS Customization
Override styles by adding CSS to your theme:
```css
.exseo-form-container {
    /* Your custom styles */
}
```

### Hook Integration
The plugin provides WordPress hooks for developers:
```php
// Modify price calculation
add_filter('exseo_calculate_price', 'your_custom_function');

// Customize form output
add_filter('exseo_form_html', 'your_form_modifier');
```

## Troubleshooting

### Common Issues

**Form not displaying**
- Check if shortcode is spelled correctly: `[exseo_form]`
- Ensure plugin is activated
- Verify you have created questions

**Price calculation not working**
- Check AJAX functionality (JavaScript console)
- Verify pricing rules are properly configured
- Ensure min/max prices are logical

**Styling issues**
- Clear cache if using caching plugins
- Check for theme CSS conflicts
- Ensure WordPress jQuery is loaded

### Support
For technical support or feature requests:
- Check WordPress admin for error messages
- Review browser console for JavaScript errors
- Verify server requirements (PHP 7.4+, WordPress 5.0+)

## Changelog

### Version 1.0
- Initial release
- Multi-step form system
- Dynamic pricing engine
- Admin management interface
- CSV export functionality
- Mobile-responsive design

## License

This plugin is licensed under the GPL v2 or later.

---

**Created by Ayal Othman**  
A powerful WordPress plugin for SEO agencies and consultants who need sophisticated pricing forms with dynamic calculations.
