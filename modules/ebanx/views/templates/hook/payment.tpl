{if in_array($country_code, $countries_available)}
    {if $country_code == 'BR'}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a href="{$action_checkout}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_checkout}"  alt="Checkout - Boleto Bancário, Cartão de Crédito" style="width: 100%; height: 100%;">
            </a>
        </div>
    </div>
    {/if}

    {if $country_code == 'PE'}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a href="{$action_checkout}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_checkout}"  alt="Checkout - PagoEfectivo, SafetyPay" style="width: 100%; height: 100%;">
            </a>
        </div>
    </div>
    {/if}

    {if $country_code == 'MX'}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a href="{$action_checkout}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_checkout}"  alt="Checkout - OXXO, Tarjeta de Crédito/Débito" style="width: 100%; height: 100%;">
            </a>
        </div>
    </div>
    {/if}
    {if $country_code == 'CO'}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a href="{$action_checkout}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_checkout}"  alt="Checkout - PSE - Pago Seguros en Línea" style="width: 100%; height: 100%;">
            </a>
        </div>
    </div>
    {/if}
    {if $country_code == 'CL'}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a href="{$action_checkout}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_checkout}"  alt="Checkout - Servipag, Sencillito" style="width: 100%; height: 100%;">
            </a>
        </div>
    </div>
    {/if}

{/if}
