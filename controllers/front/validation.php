<?php

/**
 * PrestaShop Module - pitticabankwirediscount
 *
 * Copyright 2020-2021 Pittica S.r.l.
 *
 * @category  Module
 * @package   Pittica/PrestaShop/BankwireDiscount
 * @author    Lucio Benini <info@pittica.com>
 * @copyright 2020-2021 Pittica S.r.l.
 * @license   http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link      https://github.com/pittica/prestashop-bankwirediscount
 */

class PitticaBankwireDiscountValidationModuleFrontController extends ModuleFrontController
{
    /**
     * {@inheritDoc}
     * 
     * @return void
     * @since  1.0.0
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
        
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect($this->context->link->getPageLink('order', null, null, array(
                'step' => '1'
            )));
        }
        
        $authorized = false;
        
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'pitticabankwirediscount') {
                $authorized = true;
                break;
            }
        }
        
        if (!$authorized) {
            die($this->module->getTranslator()->l('This payment method is not available.'));
        }
        
        $customer = new Customer($cart->id_customer);
        
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect($this->context->link->getPageLink('order', null, null, array(
                'step' => '1'
            )));
        }
        
        $currency = $this->context->currency;
        $total    = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $discount = ($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS) / 100.0) * (float) Configuration::get('PITTICA_BANKWIRE_DISCOUNT');
        
        $this->module->validateOrder($cart->id, Configuration::get('PS_OS_BANKWIRE'), $total - $discount, $this->module->l('Bankwire'), null, array(
            '{bankwire_owner}' => Configuration::get('BANK_WIRE_OWNER'),
            '{bankwire_details}' => Tools::nl2br(Configuration::get('BANK_WIRE_DETAILS')),
            '{bankwire_address}' => Tools::nl2br(Configuration::get('BANK_WIRE_ADDRESS'))
        ), (int) $currency->id, false, $customer->secure_key);
        
        Tools::redirect($this->context->link->getPageLink('order-confirmation', null, null, array(
            'id_cart' => $cart->id,
            'id_module' => $this->module->id,
            'id_order' => $this->module->currentOrder,
            'key' => $customer->secure_key
        )));
    }
}
