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
<fieldset>
    <legend><img src="../modules/{$moduleName|trim|escape:'htmlall':'UTF-8'}/logo.gif" alt="logo" /> {l s='OCA ePak Information' mod='rg_ocaepak'}</legend>
    <div id="oca-ajax-container" class="text-center" style="text-align: center;">
        <h3>{l s='Loading' mod='rg_ocaepak'}...</h3>
        <img src="../img/loadingAnimation.gif" alt="{l s='Loading' mod='rg_ocaepak'}">
    </div>
</fieldset>
<script>{literal}
    $(document).ready(function() {
        $.ajax({
            url: {/literal}'{$ocaAjaxUrl|escape:'quotes':'UTF-8'}'{literal},
            data: {
                ajax: true,
                action: 'carrier',
                order_id: {/literal}'{$ocaOrderId|escape:'htmlall':'UTF-8'}'{literal}
            },
            success : function(result){
                $('#oca-ajax-container').replaceWith(result);
            }
        });
    });
</script>{/literal}
{if $ocaOrdersEnabled}
    <fieldset id="oca-orders-gen">
        <div class="panel-heading">
            <img src="../modules/{$moduleName|trim|escape:'htmlall':'UTF-8'}/logo.gif" alt="logo" /> {l s='OCA ePak Orders' mod='rg_ocaepak'}
        </div>
        {if $ocaGuiHeader}
            <div class="form-group">
                %HEADER_GOES_HERE%
            </div>
        {/if}
        {if $ocaOrderStatus === 'submitted'}
            <div class="form-group">
                <div>
                    {l s='OCA Order Id' mod='rg_ocaepak'}: {$ocaOrder->reference|escape:'htmlall':'UTF-8'}<br />
                    {l s='Status' mod='rg_ocaepak'}: {$ocaStatus|escape:'htmlall':'UTF-8'}<br />
                    {if $ocaAccepts}
                    {l s='Ingressed Packages' mod='rg_ocaepak'}: {$ocaAccepts|escape:'htmlall':'UTF-8'}<br />
                    {/if}
                    {if $ocaRejects}
                        {l s='Rejected Packages' mod='rg_ocaepak'}: {$ocaRejects|escape:'htmlall':'UTF-8'}<br />
                    {/if}
                    {l s='Tracking' mod='rg_ocaepak'}: <a href="https://www1.oca.com.ar/OEPTrackingWeb/trackingenvio.asp?numero1={$ocaOrder->tracking|escape:'htmlall':'UTF-8'}" target="_blank">{$ocaOrder->tracking|escape:'htmlall':'UTF-8'}</a><br />
                    <button id="oca-cancel-button" class="btn btn-danger" onclick="cancelOcaOrder()">{l s='Cancel OCA Order' mod='rg_ocaepak'}</button>
                    <button id="oca-print-button" class="btn btn-primary" onclick="printIframe()">{l s='Print Package Stickers' mod='rg_ocaepak'}</button><br />
                    <iframe src="{$stickerUrl|escape:'htmlall':'UTF-8'}" id="oca-sticker" frameborder="0" style="margin: 18px; width: 0; height: 0; max-width: 100%;"></iframe>
                    {literal}<script>
                        $('#oca-print-button, #oca-cancel-button').hide();
                        $('#oca-sticker').load(function () {
                            var $tables = $('#etiquetas > table', $(this).contents());
                            if ($tables.length > 0) {
                                $(this).height($(this).contents().height());
                                $(this).width($(this).contents().width());
                                $('#oca-print-button, #oca-cancel-button').show();
                            } else {
                                $('#oca-sticker').hide().after('{/literal}{l s='No stickers available' mod='rg_ocaepak'}{literal}');
                            }
                        });
                        function printIframe() {
                            var ua = window.navigator.userAgent;
                            var msie = ua.indexOf ("MSIE ");
                            var iframe = document.getElementById('oca-sticker');
                            if (msie > 0) {
                                iframe.contentWindow.document.execCommand('print', false, null);
                            } else {
                                iframe.contentWindow.print();
                            }
                        }
                        function cancelOcaOrder() {
                            //@todo: only show cancel butten if order cancellable
                            if (confirm('{/literal}{l s='This will cancel the current Oca Order' mod='rg_ocaepak'}{literal}')) {
                                window.location.href = 'index.php?controller=AdminOrders&id_order={/literal}{$ocaOrderId|escape:'htmlall':'UTF-8'}{literal}&vieworder&oca-order-cancel=1&token={/literal}{$smarty.get.token|escape:'htmlall':'UTF-8'} {literal}#oca-epak-orders';
                            }
                        }
                    </script>{/literal}
                </div>
            </div>
        {elseif $ocaOrderStatus === 'unsubmitted'}
            %ORDER_GENERATOR_GOES_HERE%
        {/if}
    </fieldset>
{/if}
<style type="text/css">
    #oca-orders-gen .margin-form {
        padding-left: 0;
    }
    #oca-orders-gen form label {
        width: 100px;
    }
    #oca-orders-gen form .preference_description {
        width: 200px;
    }
</style>
