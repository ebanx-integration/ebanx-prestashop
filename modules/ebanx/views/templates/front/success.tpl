<style type="text/css" media="all">{literal}div#center_column{ width: 757px; }{/literal}</style>

<h2>{l s='Pagamento recebido' mod='ebanx'}</h2>

<div class="ebanx-success">
Seu pagamento foi recebido com sucesso!
</div>

{if strlen($ebanx['boleto_url'])}
  <p>Clique no botão abaixo para imprimir seu boleto:</p>
  <p>
    <a target="_blank" href="{$ebanx['boleto_url']}">
      <img src="{$img_path}ebanx-print-boleto.png" alt="Imprimir boleto">
    </a>
  </p>
{/if}

<p>Para visualizar os detalhes do pedido, visite a área do cliente:</p>
<p><a href="{$url_orders}" class="button_large">Detalhes do pedido</a></p>