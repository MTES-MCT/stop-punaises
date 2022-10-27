import $ from 'jquery';
$(function() {
  if ($('form.front-signalement').length > 0) {
    startCreerSignalementFrontApp();
  }
});

var frontSignalementStep = 1;
const TYPE_TRACES = 'traces';
const TYPE_RECHERCHE = 'recherche';
const TYPE_INSECTES = 'insectes';
const TYPE_LOCALISATION = 'localisation';
var hasTraces = false;
var hasRechercheInsecte = false;
var hasInsectes = false;
var hasLocalisation = false;

function startCreerSignalementFrontApp() {
  $('.btn-next').on('click', function(){
    refreshSignalementStep(1);
  });
  $('.btn-next-next').on('click', function(){
    refreshSignalementStep(2);
  });
  $('.link-back').on('click', function(){
    refreshSignalementStep(-1);
  });
  $('.link-back-back').on('click', function(){
    refreshSignalementStep(-2);
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
    case 10:
      return checkSignalementStep10();
    case 11:
      return checkSignalementStep11();

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

function updateNiveauInfestation($type, $value) {
  switch ($type) {
    case TYPE_TRACES:
      hasTraces = hasTraces || $value;
      break;
    case TYPE_RECHERCHE:
      hasRechercheInsecte = hasRechercheInsecte || $value;
      break;
    case TYPE_INSECTES:
      hasInsectes = hasInsectes || $value;
      break;
    case TYPE_LOCALISATION:
      hasLocalisation = hasLocalisation || $value;
      break;
  }

  let niveauInfestation = 0;
  if (hasTraces) {
    niveauInfestation = 1;
    if (hasInsectes) {
      niveauInfestation = 3;
      if (hasLocalisation) {
        niveauInfestation = 4;
      }
    } else if (hasRechercheInsecte) {
      niveauInfestation = 2;
    }
  }
  $('#niveau-infestation span').text(niveauInfestation);
  $('#niveau-infestation span').removeClass('niveau-0 niveau-1 niveau-2 niveau-3 niveau-4');
  $('#niveau-infestation span').addClass('niveau-' + niveauInfestation);

  switch (niveauInfestation) {
    case 0:
      $('#niveau-infestation-txt').text('Aucune infestation');
      break;
    case 1:
      $('#niveau-infestation-txt').text('Infestation faible');
      break;
    case 2:
      $('#niveau-infestation-txt').text('Infestation moyenne');
      break;
    case 3:
      $('#niveau-infestation-txt').text('Infestation élevée');
      break;
    case 4:
      $('#niveau-infestation-txt').text('Infestation très élevée');
      break;
  }
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

  updateNiveauInfestation(TYPE_LOCALISATION, $('#signalement_front_infestationLogementsVoisins_0').prop('checked'));
  
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

  updateNiveauInfestation(TYPE_TRACES, $('#signalement_front_piquresExistantes_0').prop('checked'));
  updateNiveauInfestation(TYPE_TRACES, $('#signalement_front_piquresConfirmees_0').prop('checked'));
  
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

  updateNiveauInfestation(TYPE_TRACES, $('#signalement_front_dejectionsTrouvees_0').prop('checked'));
  
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

  updateNiveauInfestation(TYPE_RECHERCHE, $('#signalement_front_oeufsEtLarvesTrouves_0').prop('checked'));
  if ($('#signalement_front_oeufsEtLarvesTrouves_0').prop('checked')) {
    updateNiveauInfestation(TYPE_INSECTES, $('#signalement_front_oeufsEtLarvesFaciliteDetections_0').prop('checked'));
  }
  
  return canGoNext;
}

function checkSignalementStep10() {
  let canGoNext = true;
  if (!checkChoicesInput('punaisesTrouvees', 2)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('punaisesNombrePiecesConcernees', 2)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('punaisesFaciliteDetections', 2)) {
    canGoNext = false;
  }
  if (!checkChoicesInput('punaisesLieuxObservations', 4)) {
    canGoNext = false;
  }

  updateNiveauInfestation(TYPE_RECHERCHE, $('#signalement_front_punaisesTrouvees_0').prop('checked'));
  if ($('#signalement_front_punaisesTrouvees_0').prop('checked')) {
    updateNiveauInfestation(TYPE_INSECTES, $('#signalement_front_punaisesFaciliteDetections_0').prop('checked'));
  }
  
  return canGoNext;
}

function checkSignalementStep11() {
  let canGoNext = true;
  if (!checkSingleInput('signalement_front_nomOccupant')) {
    canGoNext = false;
  }
  if (!checkSingleInput('signalement_front_prenomOccupant')) {
    canGoNext = false;
  }
  if (!checkSingleInput('signalement_front_telephoneOccupant')) {
    canGoNext = false;
  }
  if (!checkSingleInput('signalement_front_emailOccupant')) {
    canGoNext = false;
  }
  
  return canGoNext;
}
