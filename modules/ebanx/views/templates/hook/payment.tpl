{if $country_code == 'BR'}
<div class="row">
    <div class="col-xs-12 col-md-6">
        <p class="payment_module">
            <a class="cash" href="{$action_checkout}" title="EBANX Checkout - Boleto bancário, Cartão de Crédito e TEF">
                    {l s='EBANX Checkout - Boleto bancário, Cartão de Crédito e TEF' mod='ebanx'}
            </a>
        </p>
    </div>
</div>
{/if}

{if $country_code == 'PE'}
<div class="row">
    <div class="col-xs-12 col-md-6">
        <p class="payment_module">
            <a class="cash" href="{$action_checkout}" title="EBANX">
                 {l s='Pago Efectivo, SafetyPay' mod='ebanx'}
            </a>
        </p>
    </div>
</div>
{/if}

{if $country_code == 'MX'}
<div class="row">
    <div class="col-xs-12 col-md-6">
        <p class="payment_module">
            <a class="cash" href="{$action_checkout}" title="EBANX">
                 {l s='OXXO' mod='ebanx'}
            </a>
        </p>
    </div>
</div>
{/if}
