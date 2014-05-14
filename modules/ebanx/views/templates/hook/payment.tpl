{if $ebanx_boleto_enabled}
<p class="payment_module">
    <a href="{$action_url_boleto}" title="{l s='Boleto bancário EBANX' mod='ebanx'}">
        <img src="{$image_boleto}" alt="{l s='Boleto bancário EBANX' mod='ebanx'}" width="86" height="49" />
            {l s='Boleto bancário EBANX' mod='ebanx'}
    </a>
</p>
{/if}

{if $ebanx_tef_enabled}
<p class="payment_module">
    <a href="{$action_url_tef}" title="{l s='Transferência eletrônica EBANX' mod='ebanx'}">
        <img src="{$image_tef}" alt="{l s='Transferência eletrônica EBANX' mod='ebanx'}" width="86" height="49" />
            {l s='Transferência eletrônica EBANX' mod='ebanx'}
    </a>
</p>
{/if}

{if $ebanx_cc_enabled}
<p class="payment_module">
    <a href="{$action_url_cc}" title="{l s='Cartão de crédito' mod='ebanx'}">
        <img src="{$image_cc}" alt="{l s='Cartão de crédito' mod='ebanx'}" width="86" height="49" />
            {l s='Cartão de crédito' mod='ebanx'}
    </a>
</p>
{/if}