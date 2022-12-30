import $ from 'jquery';
$(function() {
  if ($('#fr-modal-refuse-signalement').length > 0) {
    checkFormRefuseSignalement();
  }
});

function checkFormRefuseSignalement() {
    $('#fr-modal-refuse-signalement button.color-check').on('click', function(e) {
        $('textarea[name=commentaire]').siblings('.fr-error-text').addClass('fr-hidden');
        if ($('textarea[name=commentaire]').val() == '') {
          $('textarea[name=commentaire]').siblings('.fr-error-text').removeClass('fr-hidden');
          e.preventDefault();
        }
    });
}