<style type="text/css" media="all">{literal}div#center_column{ width: 757px; }{/literal}</style>

{capture name=path}{l s='Cartão de crédito' mod='ebanx'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Finalizar compra' mod='ebanx'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
  <p class="warning">{l s='Seu carrinho de compras está vazio.'}</p>
{else}

<h3>{l s='Cartão de crédito' mod='ebanx'}</h3>

<div class="ebanx-error">
</div>

{if strlen($request_error)}
<div class="request-error">
  {$request_error}
</div>
{/if}

<form action="{$action_url}" method="post" id="ebanx_form_cc">
  <input type="hidden" name="ebanx_payment_method" value="creditcard" />

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
      <label for="ebanx_cc_name">Nome no cartão <sup>*</sup></label>
      <input type="text" id="ebanx_cc_name" name="ebanx_cc_name" value="" required>
    </p>


    <p class="required text">
      <label for="ebanx_cc_number">Número do cartão <sup>*</sup></label>
      <input type="text" id="ebanx_cc_number" name="ebanx_cc_number" value="" required>
    </p>

    <p class="required text">
      <label for="ebanx_cc_cvv">CVV <sup>*</sup></label>
      <input type="text" id="ebanx_cc_cvv" name="ebanx_cc_cvv" value="" required>
    </p>

    <p class="required text">
      <label>Data de validade <sup>*</sup></label>
      <select id="ebanx_cc_exp_month" name="ebanx_cc_exp_month" required>
        <option></option>
        {for $i = 1 to 12}
          <option value="{$i}">{$i}</option>
        {/for}
      </select>

      <select id="ebanx_cc_exp_year" name="ebanx_cc_exp_year" required>
        <option></option>
        {for $i = date('Y') to (date('Y') + 15)}
          <option value="{$i}">{$i}</option>
        {/for}
      </select>
    </p>

    <p class="required text">
      <label for="ebanx_birth_date">Bandeira <sup>*</sup></label>
      <select id="ebanx_payment_type_code" name="ebanx_payment_type_code" required>
        <option></option>
        <option value="diners">Diners</option>
        <option value="hipercard">Hipercard</option>
        <option value="mastercard">Mastercard</option>
        <option value="visa">Visa</option>
      </select>
    </p>
  </fieldset>

  <p class="cart_navigation">
    <input type="submit" name="submit" value="{l s='Finalizar compra' mod='ebanx'}" class="exclusive_large" />
    <a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Outras formas de pagamento' mod='ebanx'}</a>
  </p>
</form>
{/if}