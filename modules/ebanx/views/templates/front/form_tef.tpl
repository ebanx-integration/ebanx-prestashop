{capture name=path}{l s='Pagar com transferência eletrônica' mod='ebanx'}{/capture}

<div class="box">
  <h1 class="page-heading">{l s='Pagar com transferência eletrônica' mod='ebanx'}</h1>

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

  <form action="{$action_url}" method="post" id="ebanx_form_tef" class="std ebanx-payment-form">
    <input type="hidden" name="ebanx_payment_method" value="tef" />

    <div class="form-group">
      <label for="ebanx_document">CPF <sup>*</sup></label>
      <input type="text" class="form-control" id="ebanx_document" name="ebanx_document" value="" required>
    </div>

    <div class="form-group">
      <label for="ebanx_birth_date">Data de nascimento <sup>*</sup></label>
      <input type="text" class="form-control" id="ebanx_birth_date" name="ebanx_birth_date" value="" required>
    </div>

    <div class="form-group">
      <label for="ebanx_document">Banco <sup>*</sup></label>
      <select class="form-control" id="ebanx_payment_type_code" name="ebanx_payment_type_code" required>
        <option value=""></option>
        <option value="banrisul">Banrisul</option>
        <option value="bradesco">Bradesco</option>
        <option value="bancodobrasil">Banco do Brasil</option>
        <option value="hsbc">HSBC</option>
        <option value="itau">Itaú</option>
      </select>
    </div>

    <p class="submit2">
      <button type="submit" class="btn btn-default button button-medium">
        <span>
          {l s='Finalizar compra' mod='ebanx'}
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
      <span><i class="icon-chevron-left"></i> {l s='Outras formas de pagamento' mod='ebanx'}</span>
    </a>
  </li>
</ul>