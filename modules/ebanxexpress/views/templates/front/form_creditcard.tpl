{capture name=path}{l s='Pagar com cartão de crédito' mod='ebanxexpress'}{/capture}

<div class="box">
  <h1 class="page-heading">{l s='Pagar com cartão de crédito' mod='ebanxexpress'}</h1>

  {assign var='current_step' value='payment'}
  {include file="$tpl_dir./order-steps.tpl"}

  {if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Seu carrinho de compras está vazio.'}</p>
  {else}

  <div class="ebanx-error">
  </div>

  {if strlen($request_error)}
  <div class="request-error">
    {$request_error}
  </div>
  {/if}

  <form action="{$action_url}" method="post" id="ebanx_form_cc" class="std ebanx-payment-form">
    <input type="hidden" name="ebanxexpress_payment_method" value="creditcard" />

    <div class="form-group">
      <label for="ebanx_document">CPF <sup>*</sup></label>
      <input type="text" class="form-control" id="ebanx_document" name="ebanx_document" value="" required>
    </div>

    <div class="form-group">
      <label for="ebanx_birth_date">Data de nascimento <sup>*</sup></label>
      <input type="text" class="form-control" id="ebanx_birth_date" name="ebanx_birth_date" value="" required>
    </div>

    <div class="form-group">
      <label for="ebanx_cc_name">Nome no cartão <sup>*</sup></label>
      <input type="text" class="form-control" id="ebanx_cc_name" name="ebanx_cc_name" value="" required>
    </div>

    <div class="form-group">
      <label for="ebanx_cc_number">Número do cartão <sup>*</sup></label>
      <input type="text" class="form-control" id="ebanx_cc_number" name="ebanx_cc_number" value="" required>
    </div>

    <div class="form-group">
      <label for="ebanx_cc_cvv">CVV <sup>*</sup></label>
      <input type="text" class="form-control" id="ebanx_cc_cvv" name="ebanx_cc_cvv" value="" size="4" required>
    </div>

    <div class="form-group">
      <label for="ebanx_cc_exp_month">Data de validade (mm/aaaa)<sup>*</sup></label>
      <input type="text" class="form-control" id="ebanx_cc_exp" name="ebanx_cc_exp" required>
    </div>

    <div class="form-group">
      <label for="ebanx_payment_type_code">Bandeira <sup>*</sup></label>
      <select class="form-control" id="ebanx_payment_type_code" name="ebanx_payment_type_code" required>
        <option></option>
        <option value="amex">American Express</option>
        <option value="aura">Aura</option>
        <option value="diners">Diners</option>
        <option value="discover">Discover</option>
        <option value="elo">Elo</option>
        <option value="hipercard">Hipercard</option>
        <option value="mastercard">Mastercard</option>
        <option value="visa">Visa</option>
      </select>
    </div>

    {if $enable_installments && $max_installments > 1}
      <p><strong>Atenção:</strong> o parcelamento pode incluir juros.</p>
    <div class="form-group">
      <label for="ebanx_installments">Parcelas <sup>*</sup></label>
      <select class="form-control" id="ebanx_installments" name="ebanx_installments" required>
        <option></option> 
        {for $i = 1 to $max_installments}
          {*<option value="{$i}">{$i}x {$installments_total[$i] / $i}</option>*}
          <option value="{$i}">{$i}x {displayPrice price =($installments_total[$i] / $i)}</option>
        {/for}
      </select>
    </div>
    {/if}

    <p class="submit2">
      <button type="submit" class="btn btn-default button button-medium">
        <span>
          {l s='Finalizar compra' mod='ebanxexpress'}
          <i class="icon-chevron-right right"></i>
        </span>
      </button>
    </p>
  </form>
  {/if}
</div>

<ul class="footer_links clearfix">
  <li>
    <a class="btn btn-defaul button button-small" href="">
      <span><i class="icon-chevron-left"></i> {l s='Outras formas de pagamento' mod='ebanxexpress'}</span>
    </a>
  </li>
</ul>