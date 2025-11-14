<?php

namespace PrestaShop\Module\BankWirePaymentDiscount\Module;

use Cart;
use CartRule;
use Configuration;
use HelperForm;
use Language;
use Module;
use Tools;
use Validate;

class BankWirePaymentDiscount extends Module
{
    protected $config_form = false;

    public const CONFIG_KEY_PERCENT = 'BANKWIRE_DISCOUNT_PERCENT';
    public const CONFIG_KEY_ENABLED = 'BANKWIRE_DISCOUNT_ENABLED';
    public const DEFAULT_DISCOUNT_PERCENT = 5.0;
    public const DEFAULT_ENABLED = true;
    public const CART_RULE_PREFIX = 'BANKWIRE_DISCOUNT_';
    private const MODULE_FILE = __DIR__ . '/../../bankwirepaymentdiscount.php';


    public function __construct()
    {
        $this->name = 'bankwirepaymentdiscount';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Custom Module';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Bank Wire Payment Discount');
        $this->description = $this->l('Provides a configurable discount for customers who choose to pay via bank transfer.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
    }

    /**
     * Install the module
     */
    public function install()
    {
        Configuration::updateValue(self::CONFIG_KEY_PERCENT, (float) self::DEFAULT_DISCOUNT_PERCENT);
        Configuration::updateValue(self::CONFIG_KEY_ENABLED, (bool) self::DEFAULT_ENABLED);

        return parent::install()
            && $this->registerHook('displayPaymentByBinaries')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('displayPaymentTop');
    }

    /**
     * Uninstall the module
     */
    public function uninstall()
    {
        Configuration::deleteByName(self::CONFIG_KEY_PERCENT);
        Configuration::deleteByName(self::CONFIG_KEY_ENABLED);

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $output .= $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output .= $this->renderForm();

        return $output;
    }

    /**
     * Create the form that will be displayed in the configuration page
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of the form
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Discount'),
                        'name' => self::CONFIG_KEY_ENABLED,
                        'is_bool' => true,
                        'desc' => $this->l('Enable or disable the bank wire payment discount'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'suffix' => '%',
                        'desc' => $this->l('Enter the discount percentage for bank wire payments (e.g., 5 for 5%)'),
                        'name' => self::CONFIG_KEY_PERCENT,
                        'label' => $this->l('Discount Percentage'),
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs
     */
    protected function getConfigFormValues()
    {
        return [
            self::CONFIG_KEY_PERCENT => $this->getConfiguredDiscountPercent(),
            self::CONFIG_KEY_ENABLED => $this->isDiscountEnabled(),
        ];
    }

    /**
     * Save form data
     */
    protected function postProcess()
    {
        $errors = [];

        $discountPercent = str_replace(',', '.', Tools::getValue(self::CONFIG_KEY_PERCENT));
        $discountEnabled = (bool) (int) Tools::getValue(self::CONFIG_KEY_ENABLED);

        if (!Validate::isUnsignedFloat($discountPercent)) {
            $errors[] = $this->l('The discount percentage must be a positive number.');
        } elseif ($discountPercent > 100) {
            $errors[] = $this->l('The discount percentage cannot be greater than 100%.');
        }

        if (!empty($errors)) {
            return $this->displayError(implode('<br>', $errors));
        }

        Configuration::updateValue(self::CONFIG_KEY_PERCENT, (float) $discountPercent);
        Configuration::updateValue(self::CONFIG_KEY_ENABLED, $discountEnabled);

        return $this->displayConfirmation($this->l('Settings updated successfully.'));
    }

    /**
     * Determine if the bank wire discount is enabled.
     */
    public function isDiscountEnabled(): bool
    {
        return (bool) Configuration::get(self::CONFIG_KEY_ENABLED, self::DEFAULT_ENABLED);
    }

    /**
     * Retrieve the configured discount percentage.
     */
    public function getConfiguredDiscountPercent(): float
    {
        return (float) Configuration::get(self::CONFIG_KEY_PERCENT, self::DEFAULT_DISCOUNT_PERCENT);
    }

    /**
     * Add CSS and JS to the front office
     */
    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller->php_self == 'order') {
            $this->context->controller->registerStylesheet(
                'module-bankwirepaymentdiscount-style',
                'modules/' . $this->name . '/views/css/bankwirepaymentdiscount.css',
                [
                    'media' => 'all',
                    'priority' => 200,
                ]
            );

            $this->context->controller->registerJavascript(
                'module-bankwirepaymentdiscount-js',
                'modules/' . $this->name . '/views/js/bankwirepaymentdiscount.js',
                [
                    'position' => 'bottom',
                    'priority' => 200,
                ]
            );
        }
    }

    /**
     * Hook to modify payment options and add discount information
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active || !$this->isDiscountEnabled()) {
            return [];
        }

        // We don't return payment options here, but we can inject information via JavaScript
        // The actual modification of the payment option display will be done via CSS/JS

        return [];
    }

    /**
     * Hook to display discount information
     */
    public function hookDisplayPaymentByBinaries($params)
    {
        if (!$this->active || !$this->isDiscountEnabled()) {
            return;
        }

        $cart = $this->context->cart;
        $discountPercent = $this->getConfiguredDiscountPercent();

        if ($cart->id) {
            $total = $cart->getOrderTotal(true, Cart::BOTH);
            $discountAmount = ($total * $discountPercent) / 100;

            $this->context->smarty->assign([
                'discount_percent' => $discountPercent,
                'discount_amount' => Tools::displayPrice($discountAmount),
                'discount_amount_raw' => $discountAmount,
            ]);

            return $this->display(self::MODULE_FILE, 'views/templates/hook/payment_discount.tpl');
        }
    }

    /**
     * Hook to add discount info in shopping cart footer
     */
    public function hookDisplayShoppingCartFooter($params)
    {
        if (!$this->active || !$this->isDiscountEnabled()) {
            return;
        }

        $cart = $this->context->cart;
        $discountPercent = $this->getConfiguredDiscountPercent();

        if ($cart->id) {
            $total = $cart->getOrderTotal(true, Cart::BOTH);
            $discountAmount = ($total * $discountPercent) / 100;

            $this->context->smarty->assign([
                'discount_percent' => $discountPercent,
                'discount_amount' => Tools::displayPrice($discountAmount),
            ]);

            return $this->display(self::MODULE_FILE, 'views/templates/hook/cart_discount_info.tpl');
        }
    }

    /**
     * Hook to display discount at the top of payment section
     */
    public function hookDisplayPaymentTop($params)
    {
        if (!$this->active || !$this->isDiscountEnabled()) {
            return;
        }

        $cart = $this->context->cart;
        $discountPercent = $this->getConfiguredDiscountPercent();

        if ($cart->id) {
            $total = $cart->getOrderTotal(true, Cart::BOTH);
            $discountAmount = ($total * $discountPercent) / 100;

            $this->context->smarty->assign([
                'discount_percent' => $discountPercent,
                'discount_amount' => Tools::displayPrice($discountAmount),
                'discount_amount_raw' => $discountAmount,
                'cart_total' => Tools::displayPrice($total),
                'apply_discount_url' => $this->context->link->getModuleLink(
                    $this->name,
                    'applydiscount',
                    ['ajax' => 1]
                ),
            ]);

            return $this->display(self::MODULE_FILE, 'views/templates/hook/payment_top_discount.tpl');
        }
    }

    /**
     * Calculate discount amount based on cart total
     */
    public function getDiscountAmount($cart)
    {
        $discountPercent = $this->getConfiguredDiscountPercent();
        $total = $cart->getOrderTotal(true, Cart::BOTH);

        return ($total * $discountPercent) / 100;
    }

    /**
     * Apply discount to cart when bank wire is selected
     */
    public function applyBankWireDiscount($cart)
    {
        if (!$this->active || !$this->isDiscountEnabled()) {
            return false;
        }

        $discountPercent = $this->getConfiguredDiscountPercent();

        if ($discountPercent <= 0) {
            return false;
        }

        // Check if discount already exists
        $cartRules = $cart->getCartRules();
        foreach ($cartRules as $rule) {
            if (!empty($rule['code']) && strpos($rule['code'], self::CART_RULE_PREFIX) === 0) {
                return true; // Already applied
            }
        }

        // Create a new cart rule for this discount
        $cartRule = new CartRule();
        $cartRule->name = [];

        foreach (Language::getLanguages(true) as $language) {
            $cartRule->name[$language['id_lang']] = 'Bank Wire Discount (' . $discountPercent . '%)';
        }

        $cartRule->code = self::CART_RULE_PREFIX . (int) $cart->id;
        $cartRule->id_customer = (int) $cart->id_customer;
        $cartRule->date_from = date('Y-m-d H:i:s');
        $cartRule->date_to = date('Y-m-d H:i:s', strtotime('+1 day'));
        $cartRule->quantity = 1;
        $cartRule->quantity_per_user = 1;
        $cartRule->reduction_percent = $discountPercent;
        $cartRule->reduction_tax = 1;
        $cartRule->active = 1;
        $cartRule->cart_rule_restriction = 0;
        $cartRule->customer_restriction = $cartRule->id_customer > 0 ? 1 : 0;
        $cartRule->reduction_currency = (int) $cart->id_currency;
        $cartRule->minimum_amount_currency = (int) $cart->id_currency;
        $cartRule->minimum_amount = 0;
        $cartRule->highlight = 0;
        $cartRule->partial_use = 0;

        if ($cartRule->add()) {
            // Add the cart rule to the cart
            $cart->addCartRule($cartRule->id);
            return true;
        }

        return false;
    }
}
