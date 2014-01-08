<style type="text/css" media="all">{literal}div#center_column{ width: 757px; }{/literal}</style>

{capture name=path}{l s='Pagamento via EBANX' mod='ebanx'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Resumo da compra' mod='ebanx'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
  <p class="warning">{l s='Seu carrinho de compras está vazio.'}</p>
{else}

<h3>{l s='Pagamento via EBANX' mod='ebanx'}</h3>
<form action="{$action_url}" method="post">
  <img src="{$image}" alt="{l s='ebanx' mod='ebanx'}" style="margin: 0px 10px 5px 0px;" />
  <p style="margin-top:20px;">
    {l s='Opções de parcelamento: ' mod='ebanx'}
  </p>
  <table>
    <tr>
      <td width="100">{l s='Parcelas' mod='ebanx'}</td>
      <td>
        <select name="ebanx_installments" id="ebanx_installments_number">
          <option value="1">1x {displayPrice price=$total}</option>
          {for $i=2 to $max_installments}
            <option value="{$i}">{$i}x {displayPrice price=$total_installments/$i}</option>
          {/for}
        </select>
      </td>
    </tr>
    <tr id="ebanx_installments_card">
      <td>{l s='Cartão de crédito' mod='ebanx'}</td>
      <td>
        <select name="ebanx_installments_card">
          <option value="visa">Visa</option>
          <option value="mastercard">MasterCard</option>
        </select>
      </td>
    </tr>
  </table>

  <p class="cart_navigation">
    <input type="submit" name="submit" value="{l s='Finalizar compra' mod='ebanx'}" class="exclusive_large" />
    <a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Outros formas de pagamento' mod='ebanx'}</a>
  </p>
</form>
{/if}

{literal}
<script>
    var installmentsNumber = document.getElementById('ebanx_installments_number')
      , installmentsCard   = document.getElementById('ebanx_installments_card');

    function toggleInstallmentsCards() {
        installmentsNumber.disabled = false;
        installmentsCard.disabled = false;

        if (installmentsNumber.value == 1) {
            installmentsCard.style.display = 'none';
        } else {
            installmentsCard.style.display = 'table-row';
        }
    }

    installmentsNumber.onchange = toggleInstallmentsCards;

    toggleInstallmentsCards();
    setTimeout(toggleInstallmentsCards, 1000);
</script>
{/literal}