import $ from 'jquery';
$(function() {
  if ($('div.creer-signalement').length > 0) {
    startCreerSignalementApp();
  }
});

function startCreerSignalementApp() {
    $('nav.stepper-next a').on('click', function() {
      $('.fr-stepper__state span').text('2');
      $('.fr-stepper__title span[data-step=1]').addClass('fr-stepper__hidden');
      $('.fr-stepper__title span[data-step=2]').removeClass('fr-stepper__hidden');
      $('.fr-stepper__steps').attr('data-fr-current-step', '2');
      $('#form-creer-signalement-step-1').addClass('fr-stepper__hidden');
      $('#form-creer-signalement-step-2').removeClass('fr-stepper__hidden');
    });
}