import $ from 'jquery';
$(function() {
  if ($('form.front-signalement').length > 0) {
    const frontSignalementController = new PunaisesFrontSignalementController();
    frontSignalementController.init();
  }
});

class PunaisesFrontSignalementController {
  self;
  TYPE_TRACES = 'traces';
  TYPE_RECHERCHE = 'recherche';
  TYPE_INSECTES = 'insectes';
  TYPE_LOCALISATION = 'localisation';
  frontSignalementStep = 1;
  hasTraces = false;
  hasRechercheInsecte = false;
  hasInsectes = false;
  hasLocalisation = false;

  init() {
    self = this;
    $('.btn-next').on('click', function(){
      self.refreshStep(1);
    });
    $('.btn-next-next').on('click', function(){
      self.refreshStep(2);
    });
    $('.link-back').on('click', function(){
      self.refreshStep(-1);
    });
    $('.link-back-back').on('click', function(){
      self.refreshStep(-2);
    });
    $('.front-signalement').on('submit', function(event){
      event.preventDefault();
      self.submit();
    });
  }

  refreshStep(offset) {
    if (self.checkStep()) {
      self.frontSignalementStep += offset;
    
      $('.current-step').slideUp(200, function() {
        $('.current-step').removeClass('current-step');
        $('#step-' + self.frontSignalementStep).slideDown(200, function() {
          $('#step-' + self.frontSignalementStep).addClass('current-step');
        });
      });
    }
  }

  checkStep() {
    switch (self.frontSignalementStep) {
      case 1:
        return self.checkStep1();
      case 3:
        return self.checkStep3();
      case 4:
        return self.checkStep4();
      case 6:
        return self.checkStep6();
      case 7:
        return self.checkStep7();
      case 9:
        return self.checkStep9();
      case 10:
        return self.checkStep10();
      case 11:
        return self.checkStep11();
      default:
        return true;
    }
  }

  checkSingleInput(idInput) {
    $('input#' + idInput).siblings('.fr-error-text').addClass('fr-hidden');
    if ($('input#' + idInput).val() == '') {
      $('input#' + idInput).siblings('.fr-error-text').removeClass('fr-hidden');
      return false;
    }
    return true;
  }

  checkChoicesInput(idInput, count) {
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

  updateNiveauInfestation($type, $value) {
    switch ($type) {
      case self.TYPE_TRACES:
        self.hasTraces = self.hasTraces || $value;
        break;
      case self.TYPE_RECHERCHE:
        self.hasRechercheInsecte = self.hasRechercheInsecte || $value;
        break;
      case self.TYPE_INSECTES:
        self.hasInsectes = self.hasInsectes || $value;
        break;
      case self.TYPE_LOCALISATION:
        self.hasLocalisation = self.hasLocalisation || $value;
        break;
    }

    let niveauInfestation = 0;
    if (self.hasTraces) {
      niveauInfestation = 1;
      if (self.hasInsectes) {
        niveauInfestation = 3;
        if (self.hasLocalisation) {
          niveauInfestation = 4;
        }
      } else if (self.hasRechercheInsecte) {
        niveauInfestation = 2;
      }
    }
    $('#niveau-infestation span').text(niveauInfestation);
    $('#niveau-infestation span').removeClass('niveau-0 niveau-1 niveau-2 niveau-3 niveau-4');
    $('#niveau-infestation span').addClass('niveau-' + niveauInfestation);
    $('#signalement_front_niveauInfestation').val(niveauInfestation);

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

  checkStep1() {
    return self.checkSingleInput('code-postal');
  }

  checkStep3() {
    let canGoNext = true;
    if (!self.checkChoicesInput('typeLogement', 2)) {
      canGoNext = false;
    }
    if (!self.checkSingleInput('signalement_front_superficie')) {
      canGoNext = false;
    }
    if (!self.checkSingleInput('signalement_front_adresse')) {
      canGoNext = false;
    }
    if (!self.checkSingleInput('signalement_front_codePostal')) {
      canGoNext = false;
    }
    if (!self.checkSingleInput('signalement_front_ville')) {
      canGoNext = false;
    }
    
    return canGoNext;
  }

  checkStep4() {
    let canGoNext = true;
    if (!self.checkChoicesInput('dureeInfestation', 3)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('infestationLogementsVoisins', 3)) {
      canGoNext = false;
    }

    self.updateNiveauInfestation(self.TYPE_LOCALISATION, $('#signalement_front_infestationLogementsVoisins_0').prop('checked'));
    
    return canGoNext;
  }

  checkStep6() {
    let canGoNext = true;
    if (!self.checkChoicesInput('piquresExistantes', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('piquresConfirmees', 2)) {
      canGoNext = false;
    }

    self.updateNiveauInfestation(self.TYPE_TRACES, $('#signalement_front_piquresExistantes_0').prop('checked'));
    self.updateNiveauInfestation(self.TYPE_TRACES, $('#signalement_front_piquresConfirmees_0').prop('checked'));
    
    return canGoNext;
  }

  checkStep7() {
    let canGoNext = true;
    if (!self.checkChoicesInput('dejectionsTrouvees', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('dejectionsNombrePiecesConcernees', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('dejectionsFaciliteDetections', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('dejectionsLieuxObservations', 4)) {
      canGoNext = false;
    }

    self.updateNiveauInfestation(self.TYPE_TRACES, $('#signalement_front_dejectionsTrouvees_0').prop('checked'));
    
    return canGoNext;
  }

  checkStep9() {
    let canGoNext = true;
    if (!self.checkChoicesInput('oeufsEtLarvesTrouves', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('oeufsEtLarvesNombrePiecesConcernees', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('oeufsEtLarvesFaciliteDetections', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('oeufsEtLarvesLieuxObservations', 4)) {
      canGoNext = false;
    }

    self.updateNiveauInfestation(self.TYPE_RECHERCHE, $('#signalement_front_oeufsEtLarvesTrouves_0').prop('checked'));
    if ($('#signalement_front_oeufsEtLarvesTrouves_0').prop('checked')) {
      self.updateNiveauInfestation(self.TYPE_INSECTES, $('#signalement_front_oeufsEtLarvesFaciliteDetections_0').prop('checked'));
    }
    
    return canGoNext;
  }

  checkStep10() {
    let canGoNext = true;
    if (!self.checkChoicesInput('punaisesTrouvees', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('punaisesNombrePiecesConcernees', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('punaisesFaciliteDetections', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('punaisesLieuxObservations', 4)) {
      canGoNext = false;
    }

    self.updateNiveauInfestation(self.TYPE_RECHERCHE, $('#signalement_front_punaisesTrouvees_0').prop('checked'));
    if ($('#signalement_front_punaisesTrouvees_0').prop('checked')) {
      self.updateNiveauInfestation(self.TYPE_INSECTES, $('#signalement_front_punaisesFaciliteDetections_0').prop('checked'));
    }
    
    return canGoNext;
  }

  checkStep11() {
    let canGoNext = true;
    if (!self.checkSingleInput('signalement_front_nomOccupant')) {
      canGoNext = false;
    }
    if (!self.checkSingleInput('signalement_front_prenomOccupant')) {
      canGoNext = false;
    }
    if (!self.checkSingleInput('signalement_front_telephoneOccupant')) {
      canGoNext = false;
    }
    if (!self.checkSingleInput('signalement_front_emailOccupant')) {
      canGoNext = false;
    }
    
    return canGoNext;
  }

  submit() {
    console.log('submit');
  }
}


