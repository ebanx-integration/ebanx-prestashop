{if $ebanx_boleto_enabled}
<div class="row">
    <div class="col-xs-12 col-md-6">
        <p class="payment_module">
            <a class="cash" href="{$action_url_boleto}" title="Boleto bancário EBANX">
                 {l s='Boleto bancário EBANX' mod='ebanx'}
            </a>
        </p>
    </div>
</div>
{/if}

{if $ebanx_tef_enabled}
<div class="row">
    <div class="col-xs-12 col-md-6">
        <p class="payment_module">
            <a class="cheque" href="{$action_url_tef}" title="{l s='Transferência eletrônica EBANX' mod='ebanx'}">
            {l s='Transferência eletrônica EBANX' mod='ebanx'}
            </a>
        </p>
    </div>
</div>
{/if}

{if $ebanx_cc_enabled}
<div class="row">
    <div class="col-xs-12 col-md-6">
        <p class="payment_module">
            <a class="bankwire" href="{$action_url_cc}" title="{l s='Cartão de crédito' mod='ebanx'}">
            {l s='Cartão de crédito' mod='ebanx'}
            </a>
        </p>
    </div>
</div>
{/if}