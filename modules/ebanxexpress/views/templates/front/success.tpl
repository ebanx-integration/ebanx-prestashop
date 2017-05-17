{capture name=path}{l s='Pagamento recebido' mod='ebanxexpress'}{/capture}

<div class="box">
  <h1 class="page-heading">{l s='Pagamento recebido' mod='ebanxexpress'}</h1>

  {assign var='current_step' value='payment'}
  {include file="$tpl_dir./order-steps.tpl"}

  <div class="ebanx-success">Seu pagamento foi recebido com sucesso!</div>

  {if strlen($ebanx['boleto_url'])}
  <p>Clique no botão abaixo para imprimir seu boleto:</p>
  <p>
    <a target="_blank" href="{$ebanx['boleto_url']}" style="margin-top: 15px; display: block;">
      <img src="{$img_path}/ebanx-print-boleto.png" alt="Imprimir boleto">
    </a>
  </p>
  {/if}
</div>

<p class="cart_navigation exclusive">
  <a class="button-exclusive btn btn-default" href="{$url_orders}" title="{l s='Detalhes do pedido' mod='ebanxexpress'}"><i class="icon-chevron-left"></i>{l s='Detalhes do pedido' mod='ebanxexpress'}</a>
</p>
