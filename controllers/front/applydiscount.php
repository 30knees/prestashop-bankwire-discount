<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PrestaShop\Module\BankWirePaymentDiscount\Module\BankWirePaymentDiscount as BankWireModule;

class BankWirePaymentDiscountApplyDiscountModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $this->ajax = true;
    }

    /**
     * Process AJAX request to apply discount
     */
    public function displayAjax()
    {
        $response = [
            'success' => false,
            'message' => '',
        ];

        header('Content-Type: application/json');

        if (!$this->context->cart->id) {
            $response['message'] = 'No cart found';
            $this->ajaxRender(json_encode($response));
            return;
        }

        $cart = $this->context->cart;
        $module = Module::getInstanceByName('bankwirepaymentdiscount');

        if (!Validate::isLoadedObject($module)) {
            $response['message'] = 'Module not found';
            $this->ajaxRender(json_encode($response));
            return;
        }

        if (!$module->active || !$module->isDiscountEnabled()) {
            $response['message'] = 'Module disabled';
            $this->ajaxRender(json_encode($response));
            return;
        }

        // Check if already applied
        $cartRules = $cart->getCartRules();
        foreach ($cartRules as $rule) {
            if (strpos($rule['code'], BankWireModule::CART_RULE_PREFIX) === 0) {
                $response['success'] = true;
                $response['message'] = 'Discount already applied';
                $response['discount_amount'] = $rule['value_real'];
                $response['discount_amount_formatted'] = Tools::displayPrice($rule['value_real'], $this->context->currency);
                $this->ajaxRender(json_encode($response));
                return;
            }
        }

        // Apply the discount
        if ($module->applyBankWireDiscount($cart)) {
            $discountAmount = $module->getDiscountAmount($cart);
            $response['success'] = true;
            $response['message'] = 'Discount applied successfully';
            $response['discount_amount'] = $discountAmount;
            $response['discount_amount_formatted'] = Tools::displayPrice($discountAmount, $this->context->currency);
        } else {
            $response['message'] = 'Failed to apply discount';
        }

        $this->ajaxRender(json_encode($response));
    }
}
