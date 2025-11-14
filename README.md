# Bank Wire Payment Discount Module for PrestaShop

[![PrestaShop](https://img.shields.io/badge/PrestaShop-1.7%20%7C%208.x-blue.svg)](https://www.prestashop.com/)
[![License](https://img.shields.io/badge/License-AFL--3.0-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.1%2B-purple.svg)](https://php.net)

A PrestaShop module that provides a configurable discount for customers who choose to pay via bank transfer. The discount is prominently displayed throughout the checkout process, showing both the percentage and absolute value to encourage customers to use bank transfer payments.

![Module Preview](https://via.placeholder.com/800x400?text=Bank+Wire+Payment+Discount+Module)

## Table of Contents

- [Description](#description)
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [How It Works](#how-it-works)
- [Technical Details](#technical-details)
- [Customization](#customization)
  - [Styling](#styling)
  - [Templates](#templates)
  - [Languages](#languages)
- [Compatibility](#compatibility)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)

## Description

This module provides a configurable discount for customers who choose to pay via bank transfer in PrestaShop. The discount is prominently displayed throughout the checkout process, showing both the percentage and absolute value to encourage customers to use bank transfer payments.

### Why Use This Module?

- **Reduce Payment Processing Fees**: Bank transfers have lower processing costs than credit cards
- **Increase Profit Margins**: Offering a discount can still be more profitable than card processing fees
- **Encourage Direct Payments**: Customers see clear savings incentive
- **Professional Display**: Eye-catching banners and badges throughout checkout
- **Easy Management**: Simple configuration interface

## Features

- **Configurable Discount Percentage**: Shop owners can set the discount percentage from the module configuration page
- **Prominent Display**: The discount is displayed in multiple locations:
  - At the top of the payment section with a large, eye-catching banner
  - Next to the bank wire payment option
  - In the shopping cart footer as a reminder
- **Automatic Application**: The discount can be automatically applied when bank transfer is selected
- **Responsive Design**: All displays are fully responsive and work on mobile devices
- **Multi-language Support**: Complete translations for 11 languages (DE, EN, FR, ES, HR, SV, RO, PL, IT, CS, NL)
- **Easy Configuration**: Simple admin interface to enable/disable and configure the discount percentage

## Installation

### Method 1: Direct Upload (Recommended)

1. Download or create a zip file containing the module
2. Go to your PrestaShop back office
3. Navigate to **Modules > Module Manager**
4. Click **Upload a module** button
5. Select the `bankwirepaymentdiscount.zip` file
6. PrestaShop will automatically extract and install the module
7. Click **Configure** to set up the discount

### Method 2: Manual Installation

1. Extract the module files
2. Upload the entire `bankwirepaymentdiscount` folder to your PrestaShop `modules/` directory via FTP or file manager
3. Go to your PrestaShop back office
4. Navigate to **Modules > Module Manager**
5. Search for "Bank Wire Payment Discount"
6. Click **Install**

### Creating Distribution Package

To create a distribution package for this module:

```bash
# Clone or navigate to the module directory
cd prestashop-bankwire-discount

# If using composer dependencies (optional):
composer install --no-dev --optimize-autoloader

# Create the zip file (excludes git and development files)
git archive --format=zip --prefix=bankwirepaymentdiscount/ -o bankwirepaymentdiscount.zip HEAD

# Or manually create zip with required files only
zip -r bankwirepaymentdiscount.zip bankwirepaymentdiscount/ \
  -x "*.git*" "CONTRIBUTING.md" "GITHUB_SETUP.md" "LOGO_NEEDED.txt"
```

**Note**: You need to add a `logo.png` file (140x140px) before distribution. See `LOGO_NEEDED.txt` for requirements.

## Configuration

1. After installation, click **Configure** on the module
2. Enable or disable the discount using the toggle switch
3. Set your desired discount percentage (e.g., 5 for 5%)
4. Click **Save**

## How It Works

### Display Locations

1. **Payment Page Top Banner**: A prominent banner at the top of the payment section shows:
   - The discount percentage
   - The absolute discount amount in currency
   - Information that the discount will be automatically applied

2. **Bank Wire Payment Option**: The bank wire payment option is enhanced with:
   - A "SAVE!" badge
   - Discount information showing both percentage and amount
   - Attractive styling to draw attention

3. **Shopping Cart**: An informational banner in the cart reminds customers about the available discount

### Discount Application

The module provides methods to automatically apply the discount as a cart rule when bank wire payment is selected. The discount:
- Is calculated as a percentage of the total order amount
- Is applied with tax
- Shows clearly in the order summary
- Is automatically removed if a different payment method is selected

## Technical Details

### Hooks Used

- `header`: Initialize resources
- `displayPaymentTop`: Show prominent discount banner on payment page
- `displayPaymentByBinaries`: Display discount info with payment method
- `paymentOptions`: Hook into payment options
- `actionFrontControllerSetMedia`: Load CSS and JavaScript files
- `displayShoppingCartFooter`: Show discount reminder in cart
- `actionPaymentCCAdd`: Hook for payment processing
- `paymentReturn`: Display info after payment

### Files Structure

```
bankwirepaymentdiscount/
├── bankwirepaymentdiscount.php          # Main module class
├── config.xml                            # Module configuration
├── composer.json                         # Composer configuration for autoloading
├── logo.png                              # Module icon (140x140px) - REQUIRED
├── index.php                             # Security file
├── LICENSE                               # AFL-3.0 license
├── README.md                             # Documentation
├── CHANGELOG.md                          # Version history
├── controllers/
│   ├── index.php
│   └── front/
│       ├── applydiscount.php            # AJAX controller for discount application
│       └── index.php
├── translations/                         # Multi-language support
│   ├── de.php, en.php, fr.php, etc.     # 11 language files
│   └── index.php
└── views/
    ├── css/
    │   ├── bankwirepaymentdiscount.css  # Module styles
    │   └── index.php
    ├── js/
    │   ├── bankwirepaymentdiscount.js   # Frontend JavaScript
    │   └── index.php
    └── templates/
        └── hook/
            ├── payment_discount.tpl      # Payment option discount template
            ├── payment_top_discount.tpl  # Top banner template
            ├── cart_discount_info.tpl    # Cart info template
            └── index.php
```

## Configuration Options

### BANKWIRE_DISCOUNT_ENABLED
- **Type**: Boolean
- **Default**: true
- **Description**: Enable or disable the discount functionality

### BANKWIRE_DISCOUNT_PERCENT
- **Type**: Float
- **Default**: 5
- **Description**: The discount percentage (e.g., 5 for 5%)

## Customization

### Styling

The module uses CSS classes that can be customized:
- `.bankwire-discount-info` - Discount info boxes
- `.bankwire-payment-top-discount` - Top banner
- `.bankwire-cart-discount-info` - Cart info banner
- `.bankwire-payment-option` - Enhanced payment option

You can override these styles in your theme's CSS file.

### Templates

All templates are located in `views/templates/hook/` and can be overridden using PrestaShop's standard template override system:
1. Copy the template to your theme's `modules/bankwirepaymentdiscount/` folder
2. Modify as needed
3. Clear cache

### Languages

The module includes complete translations for 11 languages:

| Language | Code | File |
|----------|------|------|
| 🇩🇪 German | de | translations/de.php |
| 🇬🇧 English | en | translations/en.php |
| 🇫🇷 French | fr | translations/fr.php |
| 🇪🇸 Spanish | es | translations/es.php |
| 🇭🇷 Croatian | hr | translations/hr.php |
| 🇸🇪 Swedish | sv | translations/sv.php |
| 🇷🇴 Romanian | ro | translations/ro.php |
| 🇵🇱 Polish | pl | translations/pl.php |
| 🇮🇹 Italian | it | translations/it.php |
| 🇨🇿 Czech | cs | translations/cs.php |
| 🇳🇱 Dutch | nl | translations/nl.php |

All translations include:
- Admin configuration interface
- Frontend payment messages
- Cart discount notifications
- Payment banner text

The module automatically uses the language configured in PrestaShop for both customers and administrators.

## Compatibility

- **PrestaShop Version**: 1.7 and later (tested with PrestaShop 8.0, 8.1, 8.2)
- **PHP Version**: 7.2+ (recommended: PHP 8.0 or later for PrestaShop 8.2)
- **Dependencies**: None (works with standard PrestaShop installation)
- **Module Type**: PrestaShop native module compliant with PrestaShop 8.x standards

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to:
- Follow PrestaShop coding standards
- Update documentation as needed
- Add tests for new features
- Update the CHANGELOG.md

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

## Support

### Getting Help

- **Issues**: [Open an issue](../../issues) on GitHub
- **Documentation**: Check the [README](README.md) and inline code documentation
- **PrestaShop Forums**: Ask in the PrestaShop community forums

### Reporting Bugs

When reporting bugs, please include:
- PrestaShop version
- PHP version
- Module version
- Steps to reproduce
- Expected vs actual behavior
- Screenshots if applicable

## License

This module is licensed under the **Academic Free License 3.0 (AFL-3.0)**.

See the [LICENSE](LICENSE) file for full details.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed version history.

### Quick Summary

**Version 1.0.0** (2024-11-13)
- Initial release
- Configurable discount percentage
- Multiple display locations
- Responsive design
- Automatic discount application support

---

**Made with ❤️ for the PrestaShop community**
