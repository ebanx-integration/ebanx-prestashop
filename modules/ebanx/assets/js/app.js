$(document).ready(function() {
  /**
   * Validates the CPF
   * @param  string cpf
   * @return boolean
   */
  function validateCpf(cpf) {
    var digits = cpf.replace(/[\D]/g, '')
      , dv1, dv2, sum, mod;

    if (digits.length == 11) {
      d = digits.split('');

      sum = d[0] * 10 + d[1] * 9 + d[2] * 8 + d[3] * 7 + d[4] * 6 + d[5] * 5 + d[6] * 4 + d[7] * 3 + d[8] * 2;
      mod = sum % 11;
      dv1 = (11 - mod < 10 ? 11 - mod : 0);

      sum = d[0] * 11 + d[1] * 10 + d[2] * 9 + d[3] * 8 + d[4] * 7 + d[5] * 6 + d[6] * 5 + d[7] * 4 + d[8] * 3 + dv1 * 2;
      mod = sum % 11;
      dv2 = (11 - mod < 10 ? 11 - mod : 0);

      return dv1 == d[9] && dv2 == d[10];
    }

    return false;
  }

  function addError(msg) {
    var error  = $('.ebanx-error')
      , oldMsg = error.html()
      , newMsg = oldMsg;

    if (oldMsg.length > 0) {
      newMsg += '<br/>';
    }

    newMsg += msg;

    error.html(newMsg);
  }

  function clearErrors() {
    $('.ebanx-error').html('');
    $('.ebanx-error').hide();
  }

  /**
   * Boleto form validation
   */
  $('#ebanx_form_boleto').on('submit', function(e) {
    var cpf       = $('#ebanx_document').val()
      , birthDate = $('#ebanx_birth_date').val()
      , valid     = true;

    clearErrors();

    if (!validateCpf(cpf)) {
      valid = false;
      addError('O CPF digitado é inválido.');
    }

    if (!birthDate.match(/\d\d\/\d\d\/\d\d\d\d/)) {
      valid = false;
      addError('A data de nascimento é inválida.');
    }

    if (!valid) {
      $('.ebanx-error').show();
      e.preventDefault();
      return;
    }
  });
});