{if $country_code == 'BR'}
    {if $ebanx_boleto_enabled}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a href="{$action_url_boleto}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_boleto}"  alt="Débito Online" style="width: 100%; height: 100%;">
            </a>
        </div>
    </div>
    {/if}

    {if $ebanx_tef_enabled}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a href="{$action_url_tef}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_tef}"  alt="Débito Online" style="width: 100%; height: 100%;">
            </a>
        </div>
    </div>
    {/if}

    {if $ebanx_cc_enabled}
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <a href="{$action_url_cc}" style="margin-bottom: 10px; display: block;">
                <img src="{$image_cc}"  alt="Cartão de Crédito" style="width: 100%; height: 100%;">
            </a>
        </div>
    </div>
    {/if}
{/if}
