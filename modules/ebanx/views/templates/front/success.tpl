{capture name=path}{l s='Pagamento recebido' mod='ebanx'}{/capture}

<div class="box">
  <h1 class="page-heading">{l s='Pagamento recebido' mod='ebanx'}</h1>

  {assign var='current_step' value='payment'}
  {include file="$tpl_dir./order-steps.tpl"}

  <div class="ebanx-success">Seu pagamento foi recebido com sucesso!</div>

  {if strlen($ebanx['boleto_url'])}
  <p>Clique no botão abaixo para imprimir seu boleto:</p>
  <p>
    <a target="_blank" href="{$ebanx['boleto_url']}" style="margin-top: 15px; display: block;">
      <img src="{$modules_dir}/ebanx/assets/img/ebanx-print-boleto.png" alt="Imprimir boleto">
    </a>
  </p>
  {/if}
</div>

<p class="cart_navigation exclusive">
  <a class="button-exclusive btn btn-default" href="{$url_orders}" title="{l s='Detalhes do pedido' mod='ebanx'}"><i class="icon-chevron-left"></i>{l s='Detalhes do pedido' mod='ebanx'}</a>
</p>