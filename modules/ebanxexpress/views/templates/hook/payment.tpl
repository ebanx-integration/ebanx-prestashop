{if $country_code == 'BR'}
    {if $ebanx_boleto_enabled}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a target="_blank" href="{$action_url_boleto}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_boleto}"  alt="Débito Online" style="width: 100%; height: 100%;">
            </a>
            <!-- <p class="payment_module">
                <a class="cash" href="{$action_url_boleto}" title="Boleto bancário EBANX">
                     {l s='Boleto bancário EBANX' mod='ebanxexpress'}
                </a>
            </p> -->
        </div>
    </div>
    {/if}

    {if $ebanx_tef_enabled}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a target="_blank" href="{$action_url_tef}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_tef}"  alt="Débito Online" style="width: 100%; height: 100%;">
            </a>
            <!-- <p class="payment_module">
                <a class="cheque" href="{$action_url_tef}" title="{l s='Transferência eletrônica via EBANX' mod='ebanxexpress'}">
                {l s='Transferência eletrônica via EBANX' mod='ebanxexpress'}
                </a>
            </p> -->
        </div>
    </div>
    {/if}

    {if $ebanx_cc_enabled}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a target="_blank" href="{$action_url_boleto}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_cc}"  alt="Cartão de Crédito" style="width: 100%; height: 100%;">
            </a>
            <!-- <p class="payment_module">
                <a class="bankwire" href="{$action_url_cc}" title="{l s='Cartão de crédito' mod='ebanxexpress'}">
                {l s='Cartão de crédito' mod='ebanxexpress'}
                </a>
            </p> -->
        </div>
    </div>
    {/if}
{/if}

{if $country_code == 'PE'}
<div class="row">
    <div class="col-xs-12 col-md-6">
        <p class="payment_module">
            <a class="cash" href="{$action_checkout}" title="EBANX">
                 {l s='Pago Efectivo, SafetyPay' mod='ebanxexpress'}
            </a>
        </p>
    </div>
</div>
{/if}
