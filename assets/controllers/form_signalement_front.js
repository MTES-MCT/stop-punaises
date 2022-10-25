import $ from 'jquery';
$(function() {
  if ($('form.front-signalement').length > 0) {
    startCreerSignalementFrontApp();
  }
});

var frontSignalementStep = 1;

function startCreerSignalementFrontApp() {
  $('.btn-next').on('click', function(){
    refreshSignalementStep(1);
  });
  $('.link-back').on('click', function(){
    refreshSignalementStep(-1);
  });
}

function refreshSignalementStep(offset) {
  if (checkSignalementStep()) {
    frontSignalementStep += offset;
  
    $('.current-step').slideUp(200, function() {
      $('.current-step').removeClass('current-step');
      $('#step-' + frontSignalementStep).slideDown(200, function() {
        $('#step-' + frontSignalementStep).addClass('current-step');
      });
    });
  }
}

function checkSignalementStep() {
  switch (frontSignalementStep) {
    case 1:
      return checkSignalementStep1();
    case 3:
      return checkSignalementStep3();

    default:
      return true;
  }
}

function checkSignalementStep1() {
  if ($('input#code-postal').val() == '' || $('input#code-postal').val().length < 5) {
    $('input#code-postal').siblings('.fr-error-text').removeClass('fr-hidden');
    return false;
  }
  $('input#code-postal').siblings('.fr-error-text').addClass('fr-hidden');
  return true;
}

function checkSignalementStep3() {
  $('#signalement_front_typeLogement').siblings('.fr-error-text').addClass('fr-hidden');
  $('#signalement_front_superficie').siblings('.fr-error-text').addClass('fr-hidden');
  $('#signalement_front_adresse').siblings('.fr-error-text').addClass('fr-hidden');
  $('#signalement_front_codePostal').siblings('.fr-error-text').addClass('fr-hidden');
  $('#signalement_front_ville').siblings('.fr-error-text').addClass('fr-hidden');

  let canGoNext = true;
  if (!$('input#signalement_front_typeLogement_0').prop('checked') && !$('input#signalement_front_typeLogement_1').prop('checked') && !$('input#signalement_front_typeLogement_2').prop('checked')) {
    $('#signalement_front_typeLogement').siblings('.fr-error-text').removeClass('fr-hidden');
    canGoNext = false;
  }
  if ($('input#signalement_front_superficie').val() == '') {
    $('#signalement_front_superficie').siblings('.fr-error-text').removeClass('fr-hidden');
    canGoNext = false;
  }
  if ($('input#signalement_front_adresse').val() == '') {
    $('#signalement_front_adresse').siblings('.fr-error-text').removeClass('fr-hidden');
    canGoNext = false;
  }
  if ($('input#signalement_front_codePostal').val() == '') {
    $('#signalement_front_codePostal').siblings('.fr-error-text').removeClass('fr-hidden');
    canGoNext = false;
  }
  if ($('input#signalement_front_ville').val() == '') {
    $('#signalement_front_ville').siblings('.fr-error-text').removeClass('fr-hidden');
    canGoNext = false;
  }
  
  return canGoNext;
}