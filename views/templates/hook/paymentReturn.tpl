{**
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
 *}

{if $status == 'ok'}
    <p>
      {l s='Your order on %s is complete.' sprintf=[$shop_name] mod='pitticabankwirediscount'}<br/>
      {l s='Please send us a bank wire with:' mod='pitticabankwirediscount'}
    </p>
    {include file='module:pitticabankwirediscount/views/templates/hook/_partials/payment-infos.tpl'}
    <p>
      {l s='Please specify your order reference %s in the bankwire description.' sprintf=[$reference] mod='pitticabankwirediscount'}<br/>
      {l s='We\'ve also sent you this information by e-mail.' mod='pitticabankwirediscount'}
    </p>
    <strong>{l s='Your order will be sent as soon as we receive payment.' mod='pitticabankwirediscount'}</strong>
    <p>
      {l s='If you have questions, comments or concerns:' mod='pitticabankwirediscount'}
      <a href="{$contact_url}">{l s='please contact our expert customer support team' mod='pitticabankwirediscount'}</a>.
    </p>
{else}
    <p class="warning">
      {l s='We noticed a problem with your order. If you think this is an error:' mod='pitticabankwirediscount'}
      <a href="{$contact_url}">{l s='please contact our expert customer support team' mod='pitticabankwirediscount'}</a>.
    </p>
{/if}
