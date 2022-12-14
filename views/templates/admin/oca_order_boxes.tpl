{*
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA

*}
<div class="panel">
    {if empty($oca_boxes)}
        <div class="alert alert-danger">{l s='Es necesario agregar al menos una caja para generar ordenes' mod='rg_ocaepak'}</div>
    {/if}
    {foreach $oca_boxes as $ind=>$box}
        <div class="form-group">
            <h4 style="display: inline-block; margin-right: 16px;">{l s='Caja' mod='rg_ocaepak'}: {$box['l']|escape:'htmlall':'UTF-8'}cm×{$box['d']|escape:'htmlall':'UTF-8'}cm×{$box['h']|escape:'htmlall':'UTF-8'}cm</h4>
            {l s='Cantidad' mod='rg_ocaepak'}: <input type="number" name="oca-box-q-{$ind|escape:'htmlall':'UTF-8'}" id="oca-box-q-{$ind|escape:'htmlall':'UTF-8'}" min="0" step="1" value="0" class="fixed-width-sm" style="display: inline-block;  margin-right: 16px;">
        </div>
    {/foreach}
</div>
