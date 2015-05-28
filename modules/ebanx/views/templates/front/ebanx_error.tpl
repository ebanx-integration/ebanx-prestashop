<style type="text/css" media="all">{literal}div#center_column{ width: 757px; }{/literal}</style>
{capture name=path}{l s='Pagamento via EBANX' mod='ebanx'}{/capture}

  {if strlen($error)}
  <div class="request-error">
    {$error} <?php var_dump($error) ?>
  </div>
  {/if}

<h3>{l s='Pagamento via EBANX' mod='ebanx'}</h3>

<a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Voltar' mod='ebanx'}</a>