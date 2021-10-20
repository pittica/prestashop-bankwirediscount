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

<dl>
    <dt>{l s='Amount' d='Modules.Wirepayment.Shop'}</dt>
    <dd>{$total}</dd>
    <dt>{l s='Name of account owner' d='Modules.Wirepayment.Shop'}</dt>
    <dd>{$bankwireOwner}</dd>
    <dt>{l s='Please include these details' d='Modules.Wirepayment.Shop'}</dt>
    <dd>{$bankwireDetails nofilter}</dd>
    <dt>{l s='Bank name' d='Modules.Wirepayment.Shop'}</dt>
    <dd>{$bankwireAddress nofilter}</dd>
</dl>
