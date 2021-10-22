{**
 * PrestaShop Module - pitticabankwirediscount
 *
 * Copyright 2021 Pittica S.r.l.
 *
 * @category  Module
 * @package   Pittica/PrestaShop/BankwireDiscount
 * @author    Lucio Benini <info@pittica.com>
 * @copyright 2021 Pittica S.r.l.
 * @license   http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link      https://github.com/pittica/prestashop-bankwirediscount
 *}

<section>
  <div>
    {if $discount}
    <p><strong>{l s='You save %s' sprintf=[$discount] mod='pitticabankwirediscount'}</strong></p>
    {/if}
    {l s='Please transfer the invoice amount to our bank account. You will receive our order confirmation by e-mail containing bank details and order number.' mod='pitticabankwirediscount'}
    {if $bankwireReservationDays}
    {l s='Goods will be reserved %s days for you and we\'ll process the order immediately after receiving the payment.' sprintf=[$bankwireReservationDays] mod='pitticabankwirediscount'}
    {/if}
    {if $bankwireCustomText }
    <a data-toggle="modal" data-target="#pitticabankwirediscount-modal">{l s='More information' mod='pitticabankwirediscount'}</a>
    {/if}
  </div>
  <div class="modal fade" id="pitticabankwirediscount-modal" tabindex="-1" role="dialog" aria-labelledby="Bankwire information" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h2>{l s='Bankwire' mod='pitticabankwirediscount'}</h2>
        </div>
        <div class="modal-body">
          <p>{l s='Payment is made by transfer of the invoice amount to the following account:' mod='pitticabankwirediscount'}</p>
          {include file='module:pitticabankwirediscount/views/templates/hook/_partials/payment-infos.tpl'}
          {$bankwireCustomText nofilter}
        </div>
      </div>
    </div>
  </div>
</section>
