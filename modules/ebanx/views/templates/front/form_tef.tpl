<style type="text/css" media="all">{literal}div#center_column{ width: 757px; }{/literal}</style>

{capture name=path}{l s='Boleto bancário EBANX' mod='ebanx'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Finalizar compra' mod='ebanx'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
  <p class="warning">{l s='Seu carrinho de compras está vazio.'}</p>
{else}

<h3>{l s='Transferência bancária EBANX' mod='ebanx'}</h3>

<div class="ebanx-error">
</div>

{if strlen($request_error)}
<div class="request-error">
  {$request_error}
</div>
{/if}

<form action="{$action_url}" method="post" id="ebanx_form_tef">
  <input type="hidden" name="ebanx_payment_method" value="tef" />

  <fieldset class="ebanx-form">
    <p class="required text">
      <label for="ebanx_document">CPF <sup>*</sup></label>
      <input type="text" id="ebanx_document" name="ebanx_document" value="" required>
    </p>

    <p class="required text">
      <label for="ebanx_birth_date">Data de nascimento <sup>*</sup></label>
      <input type="text" id="ebanx_birth_date" name="ebanx_birth_date" value="" required>
    </p>

    <p class="required text">
      <label for="ebanx_document">Banco <sup>*</sup></label>
      <select id="ebanx_payment_type_code" name="ebanx_payment_type_code">
        <option value=""></option>
        <option value="banrisul">Banrisul</option>
        <option value="bradesco">Bradesco</option>
        <option value="bancodobrasil">Banco do Brasil</option>
        <option value="hsbc">HSBC</option>
        <option value="itau">Itaú</option>
      </select>
    </p>
  </fieldset>

  <p class="cart_navigation">
    <input type="submit" name="submit" value="{l s='Finalizar compra' mod='ebanx'}" class="exclusive_large" />
    <a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Outras formas de pagamento' mod='ebanx'}</a>
  </p>
</form>
{/if}