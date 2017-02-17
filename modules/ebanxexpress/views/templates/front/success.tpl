{capture name=path}{l s='Pagamento recebido' mod='ebanxexpress'}{/capture}

<div class="box">
  <h1 class="page-heading">{l s='Pagamento recebido' mod='ebanxexpress'}</h1>

  {assign var='current_step' value='payment'}
  {include file="$tpl_dir./order-steps.tpl"}

  <div class="ebanx-success">Seu pagamento foi recebido com sucesso!</div>

</div>

<p class="cart_navigation exclusive">
  <a class="button-exclusive btn btn-default" href="{$url_orders}" title="{l s='Detalhes do pedido' mod='ebanx'}"><i class="icon-chevron-left"></i>{l s='Detalhes do pedido' mod='ebanxexpress'}</a>
</p>