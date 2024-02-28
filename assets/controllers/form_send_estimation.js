import $ from 'jquery';
$(function() {
  if ($('#fr-modal-send-estimation').length > 0) {
    checkFormSendEstimation();
  }
});

function checkFormSendEstimation() {
    $('#fr-modal-send-estimation button.color-check').on('click', function(e) {
        let hasStopped = false;
        $('input[name=montant]').siblings('.fr-error-text').addClass('fr-hidden');
        const montantInput = $('input[name=montant]');
        const montantValue = montantInput.val().replace(',', '.');
        if (montantValue !== '' && !isNaN(parseFloat(montantValue))) {
          montantInput.val(parseFloat(montantValue));
        } else {
          hasStopped = true;
          montantInput.siblings('.fr-error-text').removeClass('fr-hidden');
          montantInput.attr('aria-describedby', 'montant-error');
        }

        $('textarea[name=commentaire]').siblings('.fr-error-text').addClass('fr-hidden');
        if ($('textarea[name=commentaire]').val() == '') {
            hasStopped = true;
          $('textarea[name=commentaire]').siblings('.fr-error-text').removeClass('fr-hidden');
          $('textarea[name=commentaire]').attr('aria-describedby', 'commentaire-error');
        }

        if (hasStopped) {
            e.preventDefault();
        }
    });
}