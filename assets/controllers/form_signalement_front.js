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
    case 4:
      return checkSignalementStep4();
    case 6:
      return checkSignalementStep6();
    case 7:
      return checkSignalementStep7();
    case 9:
      return checkSignalementStep9();

    default:
      return true;
  }
}

function checkSingleInput(idInput) {
  $('input#' + idInput).siblings('.fr-error-text').addClass('fr-hidden');
  if ($('input#' + idInput).val() == '') {
    $('input#' + idInput).siblings('.fr-error-text').removeClass('fr-hidden');
    return false;
  }
  return true;
}

function checkChoicesInput(idInput, count) {
  $('div#signalement_front_' + idInput).siblings('.fr-error-text').addClass('fr-hidden');

  let canGoNext = false;
  for (let i = 0; i < count; i++) {
    if ($('input#signalement_front_' + idInput + '_' + i).prop('checked')) {
      canGoNext = true;
    }
  }

  if (!canGoNext) {
    $('div#signalement_front_' + idInput).siblings('.fr-error-text').removeClass('fr-hidden');
  }
  
 return canGoNext;
}

function checkSignalementStep1() {
  return checkSingleInput('code-postal');
}

function checkSignalementStep3() {
  let canGoNext = true;
  if (!checkChoicesInput('typeLogement', 2)) {
    canGoNext = false;
  }
  if (!checkSingleInput('signalement_front_superficie')) {
    canGoNext = false;
  }
  if (!checkSingleInput('signalement_front_adresse')) {
    canGoNext = false;
  }
  if (!checkSingleInput('signalement_front_codePostal')) {
    canGoNext = false;
  }
  if (!checkSingleInput('signalement_front_ville')) {
    canGoNext = false;
  }
  
  return canGoNext;
}

function checkSignalementStep4() {
  let canGoNext = true;
  if (!checkChoicesInput('dureeInfestation', 3)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('infestationLogementsVoisins', 3)) {
    canGoNext = false;
  }
  
  return canGoNext;
}

function checkSignalementStep6() {
  let canGoNext = true;
  if (!checkChoicesInput('piquresExistantes', 2)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('piquresConfirmees', 2)) {
    canGoNext = false;
  }
  
  return canGoNext;
}

function checkSignalementStep7() {
  let canGoNext = true;
  if (!checkChoicesInput('dejectionsTrouvees', 2)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('dejectionsNombrePiecesConcernees', 2)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('dejectionsFaciliteDetections', 2)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('dejectionsLieuxObservations', 4)) {
    canGoNext = false;
  }
  
  return canGoNext;
}

function checkSignalementStep9() {
  let canGoNext = true;
  if (!checkChoicesInput('oeufsEtLarvesTrouves', 2)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('oeufsEtLarvesNombrePiecesConcernees', 2)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('oeufsEtLarvesFaciliteDetections', 2)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('oeufsEtLarvesLieuxObservations', 4)) {
    canGoNext = false;
  }
  
  return canGoNext;
}
