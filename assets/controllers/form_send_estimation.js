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
        if ($('input[name=montant]').val() == '') {
            hasStopped = true;
          $('input[name=montant]').siblings('.fr-error-text').removeClass('fr-hidden');
        }

        $('textarea[name=commentaire]').siblings('.fr-error-text').addClass('fr-hidden');
        if ($('textarea[name=commentaire]').val() == '') {
            hasStopped = true;
          $('textarea[name=commentaire]').siblings('.fr-error-text').removeClass('fr-hidden');
        }

        if (hasStopped) {
            e.preventDefault();
        }
    });
}