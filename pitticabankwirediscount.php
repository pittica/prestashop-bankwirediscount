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

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * Bankwire module class.
 *
 * @category Module
 * @package  Pittica/PrestaShop/BankwireDiscount
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-bankwirediscount/blob/main/pitticabankwirediscount.php
 * @since    1.0.0
 */
class PitticaBankwireDiscount extends PaymentModule
{
    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->name             = 'pitticabankwirediscount';
        $this->tab              = 'payments_gateways';
        $this->version          = '1.0.0';
        $this->author           = 'Pittica';
        $this->controllers      = array(
            'validation'
        );
        $this->is_eu_compatible = 1;
        $this->bootstrap        = true;
        $this->currencies       = true;
        $this->currencies_mode  = 'checkbox';
        
        parent::__construct();
        
        $this->displayName = $this->l('Bankwire Discount');
        $this->description = $this->l('Applies a discount to the bankwire payment.');
        
        $this->ps_versions_compliancy = array(
            'min' => '1.7.7.0',
            'max' => _PS_VERSION_
        );
    }
    
    /**
     * {@inheritDoc}
     *
     * @return boolean
     * @since  1.0.0
     */
    public function install()
    {
        Configuration::updateValue('PITTICA_BANKWIRE_DISCOUNT', 0);
        
        return parent::install() && $this->registerHook('paymentReturn') && $this->registerHook('paymentOptions') && $this->registerHook('actionFrontControllerSetMedia');
    }
    
    /**
     * {@inheritDoc}
     *
     * @return boolean
     * @since  1.0.0
     */
    public function uninstall()
    {
        return parent::uninstall() && Configuration::deleteByName('PITTICA_BANKWIRE_DISCOUNT');
    }
    
    /**
     * Validates the POST action in module configuration.
     *
     * @return string
     * @since  1.0.0
     */
    protected function _postValidation()
    {
        $errors = array();
        
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('BANK_WIRE_PAYMENT_INVITE', Tools::getValue('BANK_WIRE_PAYMENT_INVITE'));
            
            if (!Tools::getValue('BANK_WIRE_DETAILS')) {
                $errors[] = $this->l('Account details are required.');
            } elseif (!Tools::getValue('BANK_WIRE_OWNER')) {
                $errors[] = $this->l('Account owner is required.');
            }
        }
        
        return $errors;
    }
    
    /**
     * Processes the POST action in module configuration.
     *
     * @return string
     * @since  1.0.0
     */
    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('BANK_WIRE_DETAILS', Tools::getValue('BANK_WIRE_DETAILS'));
            Configuration::updateValue('BANK_WIRE_OWNER', Tools::getValue('BANK_WIRE_OWNER'));
            Configuration::updateValue('BANK_WIRE_ADDRESS', Tools::getValue('BANK_WIRE_ADDRESS'));
            
            $custom_text = array();
            $languages   = Language::getLanguages(false);
            
            foreach ($languages as $lang) {
                if (Tools::getIsset('BANK_WIRE_CUSTOM_TEXT_' . $lang['id_lang'])) {
                    $custom_text[$lang['id_lang']] = Tools::getValue('BANK_WIRE_CUSTOM_TEXT_' . $lang['id_lang']);
                }
            }
            
            Configuration::updateValue('BANK_WIRE_RESERVATION_DAYS', Tools::getValue('BANK_WIRE_RESERVATION_DAYS'));
            Configuration::updateValue('PITTICA_BANKWIRE_DISCOUNT', (float) Tools::getValue('PITTICA_BANKWIRE_DISCOUNT'));
            Configuration::updateValue('BANK_WIRE_CUSTOM_TEXT', $custom_text);
        }
        
        return $this->displayConfirmation($this->trans('Settings updated', array(), 'Admin.Global'));
    }
    
    /**
     * {@inheritDoc}
     *
     * @return string
     * @since  1.0.0
     */
    public function getContent()
    {
        $output = '';
        
        if (Tools::isSubmit('btnSubmit')) {
            $errors = $this->_postValidation();
            
            if (!count($errors)) {
                $output .= $this->_postProcess();
            } else {
                foreach ($errors as $error) {
                    $output .= $this->displayError($error);
                }
            }
        } else {
            $output .= '<br />';
        }
        
        return $output . $this->renderForm();
    }
    
    /**
     * Renders settings form.
     *
     * @return void
     * @since  1.0.0
     */
    protected function renderForm()
    {
        $custom_text = array();
        $languages   = Language::getLanguages(false);
        
        foreach ($languages as $lang) {
            $custom_text[$lang['id_lang']] = Tools::getValue('BANK_WIRE_CUSTOM_TEXT_' . $lang['id_lang'], Configuration::get('BANK_WIRE_CUSTOM_TEXT', $lang['id_lang']));
        }
        
        $config = array(
            'BANK_WIRE_DETAILS' => Tools::getValue('BANK_WIRE_DETAILS', Configuration::get('BANK_WIRE_DETAILS')),
            'BANK_WIRE_OWNER' => Tools::getValue('BANK_WIRE_OWNER', Configuration::get('BANK_WIRE_OWNER')),
            'BANK_WIRE_ADDRESS' => Tools::getValue('BANK_WIRE_ADDRESS', Configuration::get('BANK_WIRE_ADDRESS')),
            'BANK_WIRE_RESERVATION_DAYS' => Tools::getValue('BANK_WIRE_RESERVATION_DAYS', Configuration::get('BANK_WIRE_RESERVATION_DAYS')),
            'BANK_WIRE_CUSTOM_TEXT' => $custom_text,
            'BANK_WIRE_PAYMENT_INVITE' => Tools::getValue('BANK_WIRE_PAYMENT_INVITE', Configuration::get('BANK_WIRE_PAYMENT_INVITE')),
            'PITTICA_BANKWIRE_DISCOUNT' => Tools::getValue('PITTICA_BANKWIRE_DISCOUNT', (float) Configuration::get('PITTICA_BANKWIRE_DISCOUNT'))
        );
        
        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $lang                             = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form                = array();
        $helper->id                       = (int) Tools::getValue('id_carrier');
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'btnSubmit';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module= ' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars                 = array(
            'fields_value' => $config,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        
        return $helper->generateForm(array(
            array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Account details'),
                        'icon' => 'icon-envelope'
                    ),
                    'input' => array(
                        array(
                            'type' => 'text',
                            'label' => $this->l('Account owner'),
                            'name' => 'BANK_WIRE_OWNER',
                            'required' => true
                        ),
                        array(
                            'type' => 'textarea',
                            'label' => $this->l('Account details'),
                            'name' => 'BANK_WIRE_DETAILS',
                            'desc' => $this->l('Such as bank branch, IBAN number, BIC, etc.'),
                            'required' => true
                        ),
                        array(
                            'type' => 'textarea',
                            'label' => $this->l('Bank address'),
                            'name' => 'BANK_WIRE_ADDRESS',
                            'required' => true
                        ),
                        array(
                            'type' => 'html',
                            'label' => $this->l('Discount percentage'),
                            'name' => 'PITTICA_BANKWIRE_DISCOUNT',
                            'html_content' => '<div class="input-group"><input type="number" name="PITTICA_BANKWIRE_DISCOUNT" min="0" max="100" step="0.1" class="form-control" value="' . $config['PITTICA_BANKWIRE_DISCOUNT'] . '" /><span class="input-group-addon">%</span></div>'
                        )
                    ),
                    'submit' => array(
                        'title' => $this->trans('Save', array(), 'Admin.Actions')
                    )
                )
            ),
            array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Customization'),
                        'icon' => 'icon-cogs'
                    ),
                    'input' => array(
                        array(
                            'type' => 'html',
                            'label' => $this->l('Reservation period'),
                            'desc' => $this->l('Number of days the items remain reserved'),
                            'name' => 'BANK_WIRE_RESERVATION_DAYS',
                            'html_content' => '<div class="input-group"><input type="number" name="BANK_WIRE_RESERVATION_DAYS" min="0" max="365" step="1" class="form-control" value="' . $config['BANK_WIRE_RESERVATION_DAYS'] . '" /><span class="input-group-addon">' . $this->l('days') . '</span></div>'
                        ),
                        array(
                            'type' => 'textarea',
                            'label' => $this->l('Information to the customer'),
                            'name' => 'BANK_WIRE_CUSTOM_TEXT',
                            'desc' => $this->l('Information on the bank transfer (processing time, starting of the shipping...)'),
                            'lang' => true
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Display the invitation to pay in the order confirmation page'),
                            'name' => 'BANK_WIRE_PAYMENT_INVITE',
                            'is_bool' => true,
                            'hint' => $this->l('Your country\'s legislation may require you to send the invitation to pay by email only. Disabling the option will hide the invitation on the confirmation page.'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                    'label' => $this->trans('Enabled', array(), 'Admin.Global')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                    'label' => $this->trans('Disabled', array(), 'Admin.Global')
                                )
                            )
                        )
                    ),
                    'submit' => array(
                        'title' => $this->trans('Save', array(), 'Admin.Actions')
                    )
                )
            )
        ));
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        if ($this->context->controller->php_self === 'order' && (float) Configuration::get('PITTICA_BANKWIRE_DISCOUNT')) {
            $cart        = $params['cart'];
            $percentage  = (float) Configuration::get('PITTICA_BANKWIRE_DISCOUNT');
            $discount    = ($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS) / 100.0) * $percentage;
            $discount_wt = ($cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) / 100.0) * $percentage;
            $total       = Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH) - $discount);

            $presenter = new CartPresenter();
            $presented_cart = $presenter->present($cart);

            $presented_cart['totals']['total']['value'] = $total;
            $presented_cart['totals']['total_including_tax']['value'] = $total;
            
            if (!empty($presented_cart['subtotals']['tax'])) {
                $presented_cart['subtotals']['tax']['value'] = Tools::displayPrice(($cart->getOrderTotal(true, Cart::BOTH) - $discount) - ($cart->getOrderTotal(false, Cart::BOTH) - $discount_wt));
            }

            $this->smarty->assign(
                array(
                    'cart' => $presented_cart,
                    'configuration' => array(
                        'display_prices_tax_incl' => (bool) (new TaxConfiguration())->includeTaxes(),
                        'taxes_enabled' => (bool) Configuration::get('PS_TAX')
                    )
                )
            );

            Media::addJsDef(array(
                'pittica_bankwirediscount_label' => sprintf($this->l('Bankwire discount (%1$s)'), $percentage . '%'),
                'pittica_bankwirediscount_discount' => Tools::displayPrice($discount),
                'pittica_bankwirediscount_total' => $total,
                'pittica_bankwirediscount_totals' => $this->fetch('checkout/_partials/cart-summary-totals.tpl')
            ));

            $this->context->controller->registerJavascript(
                $this->name,
                'modules/' . $this->name . '/views/js/lib.js',
                array(
                    'position' => 'bottom'
                )
            );
        }
    }
    
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return array();
        }
        
        $cart = $params['cart'];
        
        if (!$this->checkCurrency($cart)) {
            return array();
        }
        
        $owner       = Configuration::get('BANK_WIRE_OWNER');
        $details     = Configuration::get('BANK_WIRE_DETAILS');
        $address     = Tools::nl2br(Configuration::get('BANK_WIRE_ADDRESS'));
        $reservation = (int) Configuration::get('BANK_WIRE_RESERVATION_DAYS');
        $text        = Tools::nl2br(Configuration::get('BANK_WIRE_CUSTOM_TEXT', $this->context->language->id));
        $percentage  = (float) Configuration::get('PITTICA_BANKWIRE_DISCOUNT');
        $discount    = ($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS) / 100.0) * $percentage;
        
        $this->smarty->assign(array(
            'total' => sprintf($discount ? $this->l('%1$s-%2$s (tax incl.)') : $this->l('%1$s (tax incl.)'), Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH)), Tools::displayPrice($discount)),
            'bankwireDetails' => $details ? $details : '',
            'bankwireAddress' => $address ? $address : '_____________________',
            'bankwireOwner' => $owner ? $owner : '_____________________',
            'bankwireReservationDays' => $reservation > 0 ? $reservation : 7,
            'bankwireCustomText' => $text ? $text : '',
            'discount' => $percentage ? Tools::displayPrice($discount) : ''
        ));
        
        $option = new PaymentOption();
        $option
            ->setModuleName($this->name)
            ->setCallToActionText($discount ? sprintf($this->l('Pay by bank wire (you save %1$s)'), $percentage . '%') : $this->l('Pay by bank wire'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
            ->setAdditionalInformation($this->fetch('module:' . $this->name . '/views/templates/hook/paymentOptions.tpl'));
        
        return array(
            $option
        );
    }
    
    public function hookPaymentReturn($params)
    {
        if (!$this->active || !Configuration::get('BANK_WIRE_PAYMENT_INVITE')) {
            return;
        }
        
        $state = $params['order']->getCurrentState();
        
        if (in_array($state, Configuration::getMultiple(array(
            'PS_OS_BANKWIRE',
            'PS_OS_OUTOFSTOCK',
            'PS_OS_OUTOFSTOCK_UNPAID'
        )))) {
            $owner       = Configuration::get('BANK_WIRE_OWNER');
            $details     = Configuration::get('BANK_WIRE_DETAILS');
            $address     = Tools::nl2br(Configuration::get('BANK_WIRE_ADDRESS'));
            $totalToPaid = $params['order']->getOrdersTotalPaid() - $params['order']->getTotalPaid();
            
            $this->smarty->assign(array(
                'shop_name' => $this->context->shop->name,
                'total' => Tools::displayPrice($totalToPaid, new Currency($params['order']->id_currency), false),
                'bankwireDetails' => $details ? $details : '',
                'bankwireAddress' => $address ? $address : '_____________________',
                'bankwireOwner' => $owner ? $owner : '_____________________',
                'status' => 'ok',
                'reference' => $params['order']->reference,
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ));
        } else {
            $this->smarty->assign(array(
                'status' => 'failed',
                'contact_url' => $this->context->link->getPageLink('contact', true)
            ));
        }
        
        return $this->fetch('module:' . $this->name . '/views/templates/hook/paymentReturn.tpl');
    }
    
    /**
     * 
     * @param Cart $cart
     * 
     * @return boolean
     * @since  1.0.0
     */
    public function checkCurrency($cart)
    {
        $currency_order    = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * {@inheritDoc}
     * 
     * @return void
     * @since  1.0.0
     */
    protected function createOrderFromCart(Cart $cart, Currency $currency, $productList, $addressId, $context, $reference, $secure_key, $payment_method, $name, $dont_touch_amount, $amount_paid, $warehouseId, $cart_total_paid, $debug, $order_status, $id_order_state, $carrierId = null)
    {
        $order               = new Order();
        $order->product_list = $productList;
        
        $computingPrecision = Context::getContext()->getComputingPrecision();
        
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
            $address          = new Address((int) $addressId);
            $context->country = new Country((int) $address->id_country, (int) $cart->id_lang);
            
            if (!$context->country->active) {
                throw new PrestaShopException('The delivery address country is not active.');
            }
        }
        
        $carrier = null;
        
        if (!$cart->isVirtualCart() && isset($carrierId)) {
            $carrier           = new Carrier((int) $carrierId, (int) $cart->id_lang);
            $order->id_carrier = (int) $carrier->id;
            $carrierId         = (int) $carrier->id;
        } else {
            $order->id_carrier = 0;
            $carrierId         = 0;
        }
        
        $discount_percentage = (float) Configuration::get('PITTICA_BANKWIRE_DISCOUNT');
        $discount            = ($cart->getOrderTotal(true, Cart::ONLY_PRODUCTS) / 100.0) * $discount_percentage;
        $discount_wt         = ($cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) / 100.0) * $discount_percentage;
        
        $order->id_customer         = (int) $cart->id_customer;
        $order->id_address_invoice  = (int) $cart->id_address_invoice;
        $order->id_address_delivery = (int) $addressId;
        $order->id_currency         = $currency->id;
        $order->id_lang             = (int) $cart->id_lang;
        $order->id_cart             = (int) $cart->id;
        $order->reference           = $reference;
        $order->id_shop             = (int) $context->shop->id;
        $order->id_shop_group       = (int) $context->shop->id_shop_group;
        
        $order->secure_key = ($secure_key ? pSQL($secure_key) : pSQL($context->customer->secure_key));
        $order->payment    = $this->l('Bankwire');
        
        if (isset($name)) {
            $order->module = $this->name;
        }
        
        $order->recyclable      = $cart->recyclable;
        $order->gift            = (int) $cart->gift;
        $order->gift_message    = $cart->gift_message;
        $order->mobile_theme    = $cart->mobile_theme;
        $order->conversion_rate = $currency->conversion_rate;
        $amount_paid            = !$dont_touch_amount ? Tools::ps_round((float) $discount, $computingPrecision) : $amount_paid;
        $order->total_paid_real = 0;
        
        $order->total_products           = Tools::ps_round((float) $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS, $order->product_list, $carrierId), $computingPrecision);
        $order->total_products_wt        = Tools::ps_round((float) $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS, $order->product_list, $carrierId), $computingPrecision);
        $order->total_discounts_tax_excl = Tools::ps_round((float) abs($cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $order->product_list, $carrierId) + $discount_wt), $computingPrecision);
        $order->total_discounts_tax_incl = Tools::ps_round((float) abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS, $order->product_list, $carrierId) + $discount), $computingPrecision);
        $order->total_discounts          = $order->total_discounts_tax_incl;
        $order->total_shipping_tax_excl  = Tools::ps_round((float) $cart->getPackageShippingCost($carrierId, false, null, $order->product_list), $computingPrecision);
        $order->total_shipping_tax_incl  = Tools::ps_round((float) $cart->getPackageShippingCost($carrierId, true, null, $order->product_list), $computingPrecision);
        $order->total_shipping           = $order->total_shipping_tax_incl;
        
        if (null !== $carrier && Validate::isLoadedObject($carrier)) {
            $order->carrier_tax_rate = $carrier->getTaxesRate(new Address((int) $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
        }
        
        $order->total_wrapping_tax_excl = Tools::ps_round((float) abs($cart->getOrderTotal(false, Cart::ONLY_WRAPPING, $order->product_list, $carrierId)), $computingPrecision);
        $order->total_wrapping_tax_incl = Tools::ps_round((float) abs($cart->getOrderTotal(true, Cart::ONLY_WRAPPING, $order->product_list, $carrierId)), $computingPrecision);
        $order->total_wrapping          = $order->total_wrapping_tax_incl;
        
        $order->total_paid_tax_excl = Tools::ps_round((float) $cart->getOrderTotal(false, Cart::BOTH, $order->product_list, $carrierId) - $discount_wt, $computingPrecision);
        $order->total_paid_tax_incl = Tools::ps_round((float) $cart->getOrderTotal(true, Cart::BOTH, $order->product_list, $carrierId) - $discount, $computingPrecision);
        $order->total_paid          = $order->total_paid_tax_incl;
        $order->round_mode          = Configuration::get('PS_PRICE_ROUND_MODE');
        $order->round_type          = Configuration::get('PS_ROUND_TYPE');
        
        $order->invoice_date  = '0000-00-00 00:00:00';
        $order->delivery_date = '0000-00-00 00:00:00';
        
        if ($debug) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - Order is about to be added', 1, null, 'Cart', (int) $cart->id, true);
        }
        
        $result = $order->add();
        
        if (!$result) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - Order cannot be created', 3, null, 'Cart', (int) $cart->id, true);
            throw new PrestaShopException('Can\'t save Order');
        }
        
        if ($order_status->logable && number_format($cart_total_paid, $computingPrecision) != number_format($amount_paid, $computingPrecision)) {
            $id_order_state = Configuration::get('PS_OS_ERROR');
        }
        
        if ($debug) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - OrderDetail is about to be added', 1, null, 'Cart', (int) $cart->id, true);
        }
        
        $order_detail = new OrderDetail(null, null, $context);
        $order_detail->createList($order, $cart, $id_order_state, $order->product_list, 0, true, $warehouseId);
        
        if ($debug) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - OrderCarrier is about to be added', 1, null, 'Cart', (int) $cart->id, true);
        }
        
        if (null !== $carrier) {
            $order_carrier                         = new OrderCarrier();
            $order_carrier->id_order               = (int) $order->id;
            $order_carrier->id_carrier             = $carrierId;
            $order_carrier->weight                 = (float) $order->getTotalWeight();
            $order_carrier->shipping_cost_tax_excl = (float) $order->total_shipping_tax_excl;
            $order_carrier->shipping_cost_tax_incl = (float) $order->total_shipping_tax_incl;
            $order_carrier->add();
        }
        
        return array(
            'order' => $order,
            'orderDetail' => $order_detail
        );
    }
}
