{capture name=path}{l s='Pagar com boleto bancário' mod='ebanxexpress'}{/capture}

<div class="box">
  <h1 class="page-heading">{l s='Pagar com boleto bancário' mod='ebanxexpress'}</h1>

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

  <form action="{$action_url}" method="post" id="ebanx_form_boleto" class="std ebanx-payment-form">
    <input type="hidden" name="ebanx_payment_method" value="boleto" />
    <input type="hidden" name="ebanx_payment_type_code" value="boleto" />

    <div class="form-group">
      <label for="ebanx_document">CPF <sup>*</sup></label>
      <input type="text" class="form-control" id="ebanx_document" name="ebanx_document" value="" required>
    </div>

    <div class="form-group">
      <label for="ebanx_birth_date">Data de nascimento <sup>*</sup></label>
      <input type="text" class="form-control" id="ebanx_birth_date" name="ebanx_birth_date" value="" required>
    </div>

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
