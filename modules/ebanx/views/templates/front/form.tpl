<style type="text/css" media="all">{literal}div#center_column{ width: 757px; }{/literal}</style>

{capture name=path}{l s='Pagamento via EBANX' mod='ebanx'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Resumo da compra' mod='ebanx'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
  <p class="warning">{l s='Seu carrinho de compras está vazio.'}</p>
{else}

<h3>{l s='Pagamento via EBANX' mod='ebanx'}</h3>
<form action="{$action_url}" method="post">
  <img src="{$image}" alt="{l s='ebanx' mod='ebanx'}" style="margin: 0px 10px 5px 0px;" />

  <p class="cart_navigation">
    <input type="submit" name="submit" value="{l s='Finalizar compra' mod='ebanx'}" class="exclusive_large" />
    <a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Outros formas de pagamento' mod='ebanx'}</a>
  </p>
</form>
{/if}