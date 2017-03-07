<?php

/**
 * Copyright (c) 2014, EBANX Tecnologia da Informação Ltda.
 *  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 *
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * Neither the name of EBANX nor the names of its
 * contributors may be used to endorse or promote products derived from
 * this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require_once dirname(dirname(dirname(__FILE__))) . '/bootstrap.php';

/**
 * The checkout controller. It creates a new order and redirects the user
 * to EBANX.
 */
class EbanxExpressDirectModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $testEnv  = (intval(Configuration::get('EBANX_EXPRESS_TESTING')) == 1);

        $cart     = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $currency = new Currency($cart->id_currency);
        $address  = new Address($cart->id_address_invoice);
        $state    = new State($address->id_state);
        $method   = Tools::getValue('ebanx_payment_method');

        // Append timestamp for test purposes
        $orderId  = $testEnv ? substr($cart->id . time(), 0, 20) : $cart->id;

        $total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));

        // Fix missing street number
        $streetNumber = preg_replace('/\D/', '', $address->address1);
        $streetNumber = ($streetNumber > 0) ? $streetNumber : '1';



        $params = array(
            'mode'      => 'full'
          , 'operation' => 'request'
        //   , 'notification_url' => _PS_BASE_URL_.'/index.php?fc=module&module=ebanxexpress&controller=notify'
          , 'payment'   => array(
              'payment_type_code' => Tools::getValue('ebanx_payment_type_code')
            , 'amount_total'      => $cart->getOrderTotal(true)
            , 'currency_code'     => $currency->iso_code
            , 'merchant_payment_code' => $orderId
            , 'order_number'  => $cart->id
            , 'name'          => $customer->firstname . ' ' . $customer->lastname
            , 'birth_date'    => Tools::getValue('ebanx_birth_date')
            , 'document'      => Tools::getValue('ebanx_document')
            , 'email'         => $customer->email
            , 'address'       => $address->address1 . ' ' . $address->address2
            , 'street_number' => $streetNumber
            , 'state'         => $state->iso_code
            , 'zipcode'       => $address->postcode
            , 'city'          => $address->city
            , 'country'       => 'br'
            , 'phone_number'  => (strlen($address->phone) > 0) ? $address->phone : $address->phone_mobile
          )
        );

        // Add credit card fields to request
        if ($method == 'creditcard')
        {
            $params['payment']['creditcard'] = array(
                'card_number'   => Tools::getValue('ebanx_cc_number')
              , 'card_name'     => Tools::getValue('ebanx_cc_name')
              , 'card_due_date' => Tools::getValue('ebanx_cc_exp')
              , 'card_cvv'      => Tools::getValue('ebanx_cc_cvv')
            );

            // If has installments, adjust total
            if (intval(Tools::getValue('ebanx_installments')) > 1)
            {
              if (intval(Tools::getValue('ebanx_installments')) > 1 && Tools::getValue('ebanx_installments') < 12)
              {
                $installments = intval(Tools::getValue('ebanx_installments'));

                $params['payment']['instalments']  = $installments;
                $params['payment']['amount_total'] = EbanxExpress::calculateTotalWithInterest($cart->getOrderTotal(true), $installments);
              }
            }
        }
        try
        {
            // var_dump($params);
            // die;
            $response = \Ebanx\Ebanx::doRequest($params);
        }
        catch (\Exception $e)
        {
            $errorMessage = $this->getEbanxErrorMessage($e->getMessage());

            // Go back to the other screen
            Tools::redirect($_SERVER['HTTP_REFERER'] . '&ebanx_error=' . urlencode($errorMessage));
        }

        if ($response->status == 'SUCCESS')
        {
            $baseUrl = _PS_BASE_URL_ . __PS_BASE_URI__;

            // Create a new order via validateOrder()
            $ebanx = new EbanxExpress();

            $ebanx->validateOrder($cart->id, Configuration::get('EBANX_EXPRESS_STATUS_OPEN'), $total, $ebanx->displayName);

            // If the request was successfull, create a new order
            $order = new Order($ebanx->currentOrder);
            $hash  = $response->payment->hash;

            // $ebanx->saveOrderData($order->id, $hash, $method);
            // Tools::redirect($baseUrl . 'index.php?fc=module&module=ebanxexpress&controller=success&hash=' . $hash);
            if ($method == 'boleto')
            {
                $ebanx->saveOrderData($order->id, $hash, $method, $response->payment->boleto_url);
                Tools::redirect($baseUrl . 'index.php?fc=module&module=ebanxexpress&controller=success&hash=' . $hash);
            }
            else if ($method == 'tef')
            {
              $ebanx->saveOrderData($order->id, $hash, $method);
              Tools::redirect($response->redirect_url);
            }
            else
            {
              $ebanx->saveOrderData($order->id, $hash, $method);
              Tools::redirect($baseUrl . 'index.php?fc=module&module=ebanxexpress&controller=success&hash=' . $hash);
            }

        }
        else
        {
            $errorMessage = $this->getEbanxErrorMessage($response->status_code);

            // Go back to the other screen
            Tools::redirect($baseUrl . 'index.php?fc=module&module=ebanxexpress&controller=payment&method='.$method . '&ebanx_error=' . urlencode($errorMessage));
        }
    }

    /**
     * Returns user friendly error messages
     * @param  string $errorCode The error code
     * @return string
     */
    protected function getEbanxErrorMessage($errorCode)
    {
        $errors = array(
            "BP-DR-1"  => array(
                "br" =>"O modo deve ser full ou iframe.",
                "en" => "Mode must be either full or iframe.",
                "esp" =>"El modo debe ser full o iframe."
            )
            , "BP-DR-2"  => array(
                "br" =>"É necessário selecionar um método de pagamento.",
                "en" => "The field was not filled.",
                "esp" =>"Es necesario seleccionar el método de pago."
            )
            , "BP-DR-3"  => array(
                "br" =>"É necessário selecionar uma moeda.",
                "en" => "The field was not filled.",
                "esp" =>"Es necesario seleccionar una moneda."
            )
            , "BP-DR-4"  => array(
                "br" =>"A moeda não é suportada pelo EBANX.",
                "en" => "Currency is not active in the system.",
                "esp" =>"La moneda no esta activa en nuestro sistema."
            )
            , "BP-DR-5"  => array(
                "br" =>"É necessário informar o total do pagamento.",
                "en" => "The field was not filled.",
                "esp" =>"Es necesario informar la cantidad total del pago."
            )
            , "BP-DR-6"  => array(
                "br" =>"O valor do pagamento deve ser maior do que X.",
                "en" => "The payment amount is too low.",
                "esp" =>"La cantidad del pago debe ser mayor que X."
            )
            , "BP-DR-7"  => array(
                "br" =>"O valor do pagamento deve ser menor do que X.",
                "en" => "The payment amount is too high.",
                "esp" =>"La cantidad del pago debe ser mayor que X."
            )
            , "BP-DR-8"  => array(
                "br" =>"O valor total somado ao valor de envio deve ser igual ao valor total.",
                "en" => "The amount sent was not right.",
                "esp" =>"La cantidad enviada no es correcta."
            )
            , "BP-DR-13" => array(
                "br" =>"É necessário informar um nome.",
                "en" => "The name field was not filled.",
                "esp" =>"Es necesario colocar un nombre."
            )
            , "BP-DR-14" => array(
                "br" =>"O nome não pode conter mais de 100 caracteres.",
                "en" => "The name parameter has more characters than the limit allowed.",
                "esp" =>"El nombre no puede tener más de 100 caracteres."
            )
            , "BP-DR-15" => array(
                "br" =>"É necessário informar um email.",
                "en" => "The email field was not filled.",
                "esp" =>"Es necesario colocar un email."
            )
            , "BP-DR-16" => array(
                "br" =>"O email não pode conter mais de 100 caracteres.",
                "en" => "The email parameter has more caracters than the limit allowed.",
                "esp" =>"El email no puede contener más de 100 caracteres."
            )
            , "BP-DR-17" => array(
                "br" =>"O email informado é inválido.",
                "en" => "The e-mail sent was not valid.",
                "esp" =>"El email no es válido."
            )
            , "BP-DR-18" => array(
                "br" =>"O cliente está suspenso no EBANX.",
                "en" => "Customer is suspended on EBANX. If unexpected, please contact EBANX Support Team.",
                "esp" =>"El cliente está suspendido en EBANX."
            )
            , "BP-DR-19" => array(
                "br" =>"É necessário informar a data de nascimento.",
                "en" => "The birthdate filed was not filled.",
                "esp" =>"Es necesario colocar la fecha de nacimiento."
            )
            , "BP-DR-20" => array(
                "br" =>"A data de nascimento deve estar no formato dd/mm/aaaa.",
                "en" => "The birth date sent was not valid, must be in (dd/MM/yyyy) format.",
                "esp" =>"La fecha de nacimiento debe estar en formato dd/mm/aaaa."
            )
            , "BP-DR-21" => array(
                "br" =>"É preciso ser maior de 16 anos.",
                "en" => "The customer has not the age required.",
                "esp" =>"El cliente debe tener más de 16 años."
            )
            , "BP-DR-22" => array(
                "br" =>"É necessário informar um CPF ou CNPJ.",
                "en" => "The CPF field was not filled.",
                "esp" =>"Es necesario colocar un CPF o CNPJ."
            )
            , "BP-DR-23" => array(
                "br" =>"O CPF informado não é válido.",
                "en" => " Document field must be valid",
                "esp" =>"El CPF no es válido."
            )
            , "BP-DR-24" => array(
                "br" =>"É necessário informar um CEP.",
                "en" => "The postal address code field was not filled.",
                "esp" =>"Es necesario colocar un código postal."
            )
            , "BP-DR-25" => array(
                "br" =>"É necessário informar o endereço.",
                "en" => "The address field was not filled.",
                "esp" =>"Es necesario colocar una dirección."
            )
            , "BP-DR-26" => array(
                "br" =>"É necessário informar o número do endereço.",
                "en" => "The street number field was not filled.",
                "esp" =>"Es necesario colocar un número de calle."
            )
            , "BP-DR-27" => array(
                "br" =>"É necessário informar a cidade.",
                "en" => "The city field was not filled.",
                "esp" =>"Es necesario colocar la ciudad."
            )
            , "BP-DR-28" => array(
                "br" =>"É necessário informar o estado.",
                "en" => "The state field was not filled.",
                "esp" =>"Es necesario colocar el estado."
            )
            , "BP-DR-29" => array(
                "br" =>"O estado informado é inválido. Deve se informar a sigla do estado (Ex.: SP).",
                "en" => "The state field must be a valid code. (Ex: SP).",
                "esp" =>"El Estado no es válido. Usted debe verificar su estado (por ejemplo:. SP)."
            )
            , "BP-DR-30" => array(
                "br" =>"O código do país deve ser 'br'.",
                "en" => "The parameter “country” must be ‘br'(Brazil).",
                "esp" =>"El código del país debe ser br."
            )
            , "BP-DR-31" => array(
                "br" =>"É necessário informar um telefone.",
                "en" => "The phone number field was not filled.",
                "esp" =>"Es necesario colocar un teléfono."
            )
            , "BP-DR-32" => array(
                "br" =>"O telefone informado é inválido.",
                "en" => "The phone number sent was not valid.",
                "esp" =>"El teléfono es inválido."
            )
            , "BP-DR-33" => array(
                "br" =>"Número de parcelas inválido.",
                "en" => "Invalid value for instalments.",
                "esp" =>"Número de cuotas es inválido."
            )
            , "BP-DR-34" => array(
                "br" =>"Número de parcelas inválido.",
                "en" => "Invalid value for instalment.",
                "esp" =>"Número de cuotas es inválido."

            )
            , "BP-DR-35" => array(
                "br" =>"Método de pagamento inválido: X.",
                "en" => "The payment method is not enable.",
                "esp" =>"Método de pago es inválido: X"
            )
            , "BP-DR-36" => array(
                "br" =>"O método de pagamento não está ativo.",
                "en" => "Payment type is not active.",
                "esp" =>"El Método de pago no está activo."
            )
            , "BP-DR-39" => array(
                "br" =>"CPF, nome e data de nascimento não combinam.",
                "en" => "CPF, name and birth date do not match.",
                "esp" =>"CPF, nombre y fecha de nacimiento no coinciden."
            )
            , "BP-DR-40" => array(
                "br" =>"Cliente atingiu o limite de pagamentos para o período.",
                "en" => "Customer reached payment limit.",
                "esp" =>"El cliente alcanzó el límite de pago."
            )
            , "BP-DR-41" => array(
                "br" =>"Deve-se escolher um tipo de pessoa - física ou jurídica.",
                "en" => "Field must contain a valid person type.",
                "esp" =>"Se debe escoger un tipo de persona- física o jurídica."
            )
            , "BP-DR-42" => array(
                "br" =>"É necessário informar os dados do responsável pelo pagamento.",
                "en" => "The responsible field was not filled.",
                "esp" =>"Debe introducir los datos del responsable del pago."
            )
            , "BP-DR-43" => array(
                "br" =>"É necessário informar o nome do responsável pelo pagamento.",
                "en" => "The responsible name was not filled.",
                "esp" =>"Debe introducir el nombre del responsable del pago."
            )
            , "BP-DR-44" => array(
                "br" =>"É necessário informar o CPF do responsável pelo pagamento.",
                "en" => "The responsible document was not filled.",
                "esp" =>"Debe informar el CPF del responsable del pago."

            , "BP-DR-45" => array(
                "br" =>"É necessário informar a data de nascimento do responsável pelo pagamento.",
                "en" => "The responsible birthdate was not filled.",
                "esp" =>"Debe introducir la fecha de nacimiento responsable del pago."
            )
            , "BP-DR-46" => array(
                "br" =>"CPF, nome e data de nascimento do responsável não combinam.",
                "en" => "Company responsible’s CPF, name and birth date do not match.",
                "esp" =>"CPF, nombre y fecha de nacimiento del responsable no coinciden."
            )
            , "BP-DR-47" => array(
                "br" =>"A conta bancário deve conter no máximo 10 caracteres.",
                "en" => "The bank account has more characters than the limit allowed.",
                "esp" =>"La cuenta de banco debe contener no máximo de 10 caracteres."
            )
            , "BP-DR-48" => array(
                "br" =>"É necessário informar os dados do cartão de crédito.",
                "en" => "Field creditcard is required for this payment type.",
                "esp" =>"Colocar los datos de la tarjeta de crédito."
            )
            , "BP-DR-49" => array(
                "br" =>"É necessário informar o número do cartão de crédito.",
                "en" => "The credit number was not filled.",
                "esp" =>"Colocar el número de la tarjeta de crédito."
            )
            , "BP-DR-51" => array(
                "br" =>"É necessário informar o nome do titular do cartão de crédito.",
                "en" => "The creditcard name was not filled.",
                "esp" =>"Colocar el nombre del titular de la tarjeta de crédito."
            )
            , "BP-DR-52" => array(
                "br" =>"O nome do titular do cartão deve conter no máximo 50 caracteres.",
                "en" => "The creditcard name has more characters than the limit allowed.",
                "esp" =>"El nombre del titular de la tarjeta no debe superar los 50 caracteres."
            )
            , "BP-DR-54" => array(
                "br" =>"É necessário informar o CVV do cartão de crédito.",
                "en" => "The creditcard cvv was not filled.",
                "esp" =>"Colocar el CVV de la tarjeta de crédito."
            )
            , "BP-DR-55" => array(
                "br" =>"O CVV deve conter no máximo 4 caracteres.",
                "en" => "The creditcard cvv has more characters than the limit allowed.",
                "esp" =>"El CVV no debe superar los 4 caracteres."
            )
            , "BP-DR-56" => array(
                "br" =>"É necessário informar a data de venciomento do cartão de crédito.",
                "en" => "The creditcard due date was not filled",
                "esp" =>"Colocar la fecha de vencimiento de la tarjeta de crédito."
            )
            , "BP-DR-57" => array(
                "br" =>"A data de vencimento do cartão de crédito deve estar no formato dd/mm/aaaa.",
                "en" => "The credicard due date sent was not valid, must be in (dd/MM/yyyy) format.",
                "esp" =>"La fecha de vencimiento de la tarjeta de crédito debe estar en formato dd / mm / aaaa."
            )
            , "BP-DR-58" => array(
                "br" =>"A data de vencimento do cartão é inválida.",
                "en" => "The credicard due_date is invalid.",
                "esp" =>"La fecha de caducidad de la tarjeta no es válida."
            )
            , "BP-DR-59" => array(
                "br" =>"A data de vencimento do cartão é inválida.",
                "en" => "The credicard due_date is invalid.",
                "esp" =>"La fecha de caducidad de la tarjeta no es válida."
            )
            , "BP-DR-61" => array(
                "br" =>"Não foi possível criar um token para este cartão de crédito.",
                "en" => "It was not possible to complete the operation.",
                "esp" =>"No fue posible completar esta operación."
            )
            , "BP-DR-62" => array(
                "br" =>"Pagamentos recorrentes não estão habilitados para este merchant.",
                "en" => "Recurring payment is not allowed on your account.",
                "esp" =>"Pagos recurrentes no están permitidos para este merchant."
            )
            , "BP-DR-63" => array(
                "br" =>"Token não encontrado para este adquirente.",
                "en" => "Token not found for this acquirer.",
                "esp" =>"Token no encontrado para este adquirente."
            )
            , "BP-DR-64" => array(
                "br" =>"Token não encontrado.",
                "en" => "Token not found.",
                "esp" =>"Token no encontrado."
            )
            , "BP-DR-65" => array(
                "br" =>"O token informado já está sendo utilizado.",
                "en" => "The token that you are trying to create already exist.",
                "esp" =>"El token que usted está intentando crear ya existe."
            )
            , "BP-DR-66" => array(
                "br" =>"Token inválido. O token deve ter entre 32 e 128 caracteres.",
                "en" => "Invalid Token. The parameter has more characters than the limit allowed.",
                "esp" =>"Token Inválido. El Token debe tener entre 32 y 128 caracteres."
            )
            , "BP-DR-67" => array(
                "br" =>"A data de venciomento do cartão de crédito é inválida.",
                "en" => "The credicard due_date is invalid.",
                "esp" =>"La fecha de vencimiento de la tarjeta de crédito no es válida."
            )
            , "BP-DR-68" => array(
                "br" =>"É necessário informar o número da conta bancária.",
                "en" => "The banck account was not filled.",
                "esp" =>"Colocar el número de la cuenta de banco."
            )
            , "BP-DR-69" => array(
                "br" =>"A conta bancária não pode conter mais de 10 caracteres.",
                "en" => "The bank account has more characters than the limit allowed.",
                "esp" =>"La cuenta de banco no puede tener más de 10 caracteres."
            )
            , "BP-DR-70" => array(
                "br" =>"É necessário informar a agência bancária.",
                "en" => "The bank agency was not filled.",
                "esp" =>"Colocar la agencia bancaria."
            )
            , "BP-DR-71" => array(
                "br" =>"O código do banco não pode ter mais de 5 caracteres.",
                "en" => "The bank code has more characters than the limit allowed.",
                "esp" =>"El código de banco no puede tener más de 5 caracteres."
            )
            , "BP-DR-72" => array(
                "br" =>"É necessário informar o código do banco.",
                "en" => "The bank code was not filled.",
                "esp" =>"Colocar el código de banco."
            )
            , "BP-DR-73" => array(
                "br" =>"É necessário informar os dados da conta para débito em conta.",
                "en" => "The debit account was not filled.",
                "esp" =>"Colocar los datos de la cuenta de débito."
            )
            , "BP-DR-75" => array(
                "br" =>"O número do cartão é inválido.",
                "en" => "Card number is invalid",
                "esp" =>"El número de la tarjeta es inválido."
            )
            , "BP-DR-83" => array(
                "br" =>"O cartão não pode ser estrangeiro.",
                "en" => "Foreign credit card cannot be processed.",
                "esp" =>"Tarjeta de crédito extranjera no puede ser procesada."
            )
            , "BP-DR-101" => array(
                "br" =>"O cartão utilizado não pode ser usado em transações online.",
                "en" => "Card is not eligible for ecommerce",
                "esp" =>"La tarjeta no puede ser utilizada para compras en línea."
            )
            , "BP-R-1" => array(
                "br" =>"É necessário informar a moeda.",
                "en" => "The currency code was not filled.",
                "esp" =>"Es necesario colocar la moneda."
            )
            , "BP-R-2" => array(
                "br" =>"É necessário informar o valor do pagamento.",
                "en" => "The amount was not filled.",
                "esp" =>"Es necesario colocar la cantidad del pago."
            )
            , "BP-R-3" => array(
                "br" =>"É necessário informar o código do pedido.",
                "en" => "The payment code was not filled.",
                "esp" =>"Es necesario colocar el código del pedido."
            )
            , "BP-R-4" => array(
                "br" =>"É necessário informar o nome.",
                "en" => "The name was not filled.",
                "esp" =>"Es necesario colocar el nombre."
            )
            , "BP-R-5" => array(
                "br" =>"É necessário informar o email.",
                "en" => "The email was not filled.",
                "esp" =>"Es necesario colocar el email."
            )
            , "BP-R-6" => array(
                "br" =>"É necessário selecionar o método de pagamento.",
                "en" => "The payment tyoe code was not filled.",
                "esp" =>"Es necesario seleccionar el método de pago."
            )
            , "BP-R-7" => array(
                "br" =>"O método de pagamento não está ativo.",
                "en" => "Payment type is not active",
                "esp" =>"El método de pago no está activo."
            )
            , "BP-R-8" => array(
                "br" =>"O método de pagamento é inválido.",
                "en" => "The payment type code sent was not valid.",
                "esp" =>"El método de pago es inválido."
            )
            , "BP-R-9" => array(
                "br" =>"O valor do pagamento deve ser positivo: X.",
                "en" => "Amount must be positive.",
                "esp" =>"La cantidad del pago debe ser positivo: X."

            , "BP-R-10" => array(
                "br" =>"O valor do pagamento deve ser maior do que X.",
                "en" => "The amount is too low.",
                "esp" =>"La cantidad del pago debe ser mayor que X."
            )r
            , "BP-R-11" => array(
                "br" =>"O método de pagamento não suporta parcelamento.",
                "en" => "Payment type does not support instalments",
                "esp" =>"El método de pago no es compatible con las cuotas."
            )
            , "BP-R-12" => array(
                "br" =>"O número máximo de parcelas é X. O valor informado foi de X parcelas.",
                "en" => "The number of instalment is not with the right value.",
                "esp" =>"El número máximo de cuotas es de X. El valor X era cuotas."
            )
            , "BP-R-13" => array(
                "br" =>"O valor mínimo das parcelas é de R$ X.",
                "en" => "The amount of each instalments must be greater than or equal to R$ X.",
                "esp" =>"La cantidad mínima de las cuotas es de R$ X."
            )
            , "BP-R-17" => array(
                "br" =>"O pagamento não está aberto.",
                "en" => "This payment does not have the open status.",
                "esp" =>"Este pago no tiene un estatus abierto."
            )
            , "BP-R-18" => array(
                "br" =>"O típo de pessoa é inválido.",
                "en" => "The person type sent was not valid.",
                "esp" =>"El tipo de persona es inválido."
            )
            , "BP-R-19" => array(
                "br" =>"O checkout com CNPJ não está habilitado.",
                "en" => "Checkout by CNPJ is not enable on your account.",
                "esp" =>"El Checkout con CNPJ no está habilitado."
            )
            , "BP-R-20" => array(
                "br" =>"A data de vencimento deve estar no formato dd/mm/aaaa.",
                "en" => "The due date sent was not valid, must be in (dd/MM/yyyy) format.",
                "esp" =>"La fecha de vencimiento debe estar en el formato dd/ mm/aaaa."
            )
            , "BP-R-21" => array(
                "br" =>"A data de vencimento é inválida.",
                "en" => "The due date sent was not valid.",
                "esp" =>"La fecha de vencimiento es inválida."
            )
            , "BP-R-22" => array(
                "br" =>"A data de vencimento é inválida.",
                "en" => "The due date sent was not valid.",
                "esp" =>"La fecha de vencimiento es inválida."
            )
            , "BP-R-23" => array(
                "br" =>"A moeda não está ativa no sistema.",
                "en" => "The currency that you are trying to use is not enabled.",
                "esp" =>"La moneda no está activa en nuestro sistema."
            )
            , "BP-ZIP-1" => array(
                "br" =>"O CEP não foi informado.",
                "en" => "Zipcode not informed.",
                "esp" =>"El código postal no fue informado."
            )
            , "BP-ZIP-2" => array(
                "br" =>"O CEP não é válido.",
                "en" => "Zipcode is not valid.",
                "esp" =>"El código postal no es válido."
            )
            , "BP-ZIP-3" => array(
                "br" =>"O endereço não pode ser encontrado.",
                "en" => "The address could not be retrieved.",
                "esp" =>"La dirección no pudo ser encontrada."
            )
            , "BP-DPAR-4" => array(
                "br" =>"Chave de integração inválida.",
                "en" => "Invalid integration key.",
                "esp" =>"Clave de integracion es inválida."
            )
        );

        if (array_key_exists($errorCode, $errors))
        {
            if (array_key_exists($this->context->language->iso_code ,$errors[$errorCode])) {
                return $errors[$errorCode][$this->context->language->iso_code];
            }
            return $errors[$errorCode]['en'];
        }

        return 'Ocorreu um erro desconhecido. Por favor contacte o administrador.';
    }
}
