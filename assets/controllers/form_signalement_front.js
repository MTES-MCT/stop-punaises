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
  STEP_SUBMIT = 11;
  step = 1;
  hasTraces = false;
  hasRechercheInsecte = false;
  hasInsectes = false;
  hasLocalisation = false;

  init() {
    self = this;
    $('.btn-next').on('click', function(){
      switch (self.step) {
        case self.STEP_SUBMIT:
          self.submitAdd();
          break;
        default:
          self.refreshStep(1);
          break;
      }
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
  }

  refreshStep(offset) {
    let acceptRefresh = (offset > 0) ? self.checkStep() : true;
    if (acceptRefresh) {
      self.step += offset;
    
      $('.current-step').slideUp(200, function() {
        $('.current-step').removeClass('current-step');
        $('#step-' + self.step).slideDown(200, function() {
          $('#step-' + self.step).addClass('current-step');
        });
        self.initStep();
      });
    }
  }

  initStep() {
    switch (self.step) {
      case 6:
        return self.initStep6();
      case 7:
        return self.initStep7();
      case 9:
        return self.initStep9();
      case 10:
        return self.initStep10();
      default:
        return true;
    }
  }

  checkStep() {
    switch (self.step) {
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
    if (!self.checkChoicesInput('typeLogement', 3)) {
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

  initStep6() {
    self.updateStep6();
    $('#step-6 input[name="signalement_front[piquresExistantes]"]').on('click', function() {
      self.updateStep6();
    });
  }

  updateStep6() {
    let isVisible = $('#step-6 #signalement_front_piquresExistantes_0').prop('checked');
    if (isVisible) {
      $('#step-6 #form-group-piquresConfirmees').slideDown(200);
      $('#step-6 #form-group-photos').slideDown(200);
    } else {
      $('#step-6 #form-group-piquresConfirmees').slideUp(200);
      $('#step-6 #form-group-photos').slideUp(200);
    }
  }

  checkStep6() {
    let canGoNext = true;
    if (!self.checkChoicesInput('piquresExistantes', 2)) {
      canGoNext = false;
    }
    if ($('#signalement_front_piquresExistantes_0').prop('checked') && !self.checkChoicesInput('piquresConfirmees', 2)) {
      canGoNext = false;
    }

    self.updateNiveauInfestation(self.TYPE_TRACES, $('#signalement_front_piquresExistantes_0').prop('checked'));
    self.updateNiveauInfestation(self.TYPE_TRACES, $('#signalement_front_piquresConfirmees_0').prop('checked'));
    
    return canGoNext;
  }

  initStep7() {
    self.updateStep7();
    $('#step-7 input[name="signalement_front[dejectionsTrouvees]"]').on('click', function() {
      self.updateStep7();
    });
  }

  updateStep7() {
    let isVisible = $('#step-7 #signalement_front_dejectionsTrouvees_0').prop('checked');
    if (isVisible) {
      $('#step-7 #form-group-dejectionsNombrePiecesConcernees').slideDown(200);
      $('#step-7 #form-group-dejectionsFaciliteDetections').slideDown(200);
      $('#step-7 #form-group-dejectionsLieuxObservations').slideDown(200);
    } else {
      $('#step-7 #form-group-dejectionsNombrePiecesConcernees').slideUp(200);
      $('#step-7 #form-group-dejectionsFaciliteDetections').slideUp(200);
      $('#step-7 #form-group-dejectionsLieuxObservations').slideUp(200);
    }
  }

  checkStep7() {
    let canGoNext = true;
    if (!self.checkChoicesInput('dejectionsTrouvees', 2)) {
      canGoNext = false;
    }
    if ($('#signalement_front_dejectionsTrouvees_0').prop('checked')) {
      if (!self.checkChoicesInput('dejectionsNombrePiecesConcernees', 2)) {
        canGoNext = false;
      }
      if (!self.checkChoicesInput('dejectionsFaciliteDetections', 2)) {
        canGoNext = false;
      }
      if (!self.checkChoicesInput('dejectionsLieuxObservations', 4)) {
        canGoNext = false;
      }
    }

    self.updateNiveauInfestation(self.TYPE_TRACES, $('#signalement_front_dejectionsTrouvees_0').prop('checked'));
    
    return canGoNext;
  }

  initStep9() {
    self.updateStep9();
    $('#step-9 input[name="signalement_front[oeufsEtLarvesTrouves]"]').on('click', function() {
      self.updateStep9();
    });
  }

  updateStep9() {
    let isVisible = $('#step-9 #signalement_front_oeufsEtLarvesTrouves_0').prop('checked');
    if (isVisible) {
      $('#step-9 #form-group-oeufsEtLarvesNombrePiecesConcernees').slideDown(200);
      $('#step-9 #form-group-oeufsEtLarvesFaciliteDetections').slideDown(200);
      $('#step-9 #form-group-oeufsEtLarvesLieuxObservations').slideDown(200);
    } else {
      $('#step-9 #form-group-oeufsEtLarvesNombrePiecesConcernees').slideUp(200);
      $('#step-9 #form-group-oeufsEtLarvesFaciliteDetections').slideUp(200);
      $('#step-9 #form-group-oeufsEtLarvesLieuxObservations').slideUp(200);
    }
  }

  checkStep9() {
    let canGoNext = true;
    if (!self.checkChoicesInput('oeufsEtLarvesTrouves', 2)) {
      canGoNext = false;
    }
    if ($('#signalement_front_oeufsEtLarvesTrouves_0').prop('checked')) {
      if (!self.checkChoicesInput('oeufsEtLarvesNombrePiecesConcernees', 2)) {
        canGoNext = false;
      }
      if (!self.checkChoicesInput('oeufsEtLarvesFaciliteDetections', 2)) {
        canGoNext = false;
      }
      if (!self.checkChoicesInput('oeufsEtLarvesLieuxObservations', 4)) {
        canGoNext = false;
      }
    }

    self.updateNiveauInfestation(self.TYPE_RECHERCHE, $('#signalement_front_oeufsEtLarvesTrouves_0').prop('checked'));
    if ($('#signalement_front_oeufsEtLarvesTrouves_0').prop('checked')) {
      self.updateNiveauInfestation(self.TYPE_INSECTES, $('#signalement_front_oeufsEtLarvesFaciliteDetections_0').prop('checked'));
    }
    
    return canGoNext;
  }

  initStep10() {
    self.updateStep10();
    $('#step-10 input[name="signalement_front[punaisesTrouvees]"]').on('click', function() {
      self.updateStep10();
    });
  }

  updateStep10() {
    let isVisible = $('#step-10 #signalement_front_punaisesTrouvees_0').prop('checked');
    if (isVisible) {
      $('#step-10 #form-group-punaisesNombrePiecesConcernees').slideDown(200);
      $('#step-10 #form-group-punaisesFaciliteDetections').slideDown(200);
      $('#step-10 #form-group-punaisesLieuxObservations').slideDown(200);
    } else {
      $('#step-10 #form-group-punaisesNombrePiecesConcernees').slideUp(200);
      $('#step-10 #form-group-punaisesFaciliteDetections').slideUp(200);
      $('#step-10 #form-group-punaisesLieuxObservations').slideUp(200);
    }
  }

  checkStep10() {
    let canGoNext = true;
    if (!self.checkChoicesInput('punaisesTrouvees', 2)) {
      canGoNext = false;
    }
    if ($('#signalement_front_punaisesTrouves_0').prop('checked')) {
      if (!self.checkChoicesInput('punaisesNombrePiecesConcernees', 2)) {
        canGoNext = false;
      }
      if (!self.checkChoicesInput('punaisesFaciliteDetections', 2)) {
        canGoNext = false;
      }
      if (!self.checkChoicesInput('punaisesLieuxObservations', 4)) {
        canGoNext = false;
      }
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

  submitAdd() {
    $('.front-signalement #step-11 .btn-next').attr('disabled', 'disabled');
    $.ajax({
      type: 'POST',
      url: $('.front-signalement').attr('action'),
      data: $('.front-signalement').serialize(),
  
      success: function() {
        self.refreshStep(1);  
      },
      error: function (xhr, desc, err) {
        console.log(xhr);
        if (xhr.responseJSON != undefined) {
          alert("Erreur lors de l'ajout du signalement (" + xhr.responseJSON.errors[0].message + ")");
        } else {
          alert("Erreur lors de l'ajout du signalement");
        }
        $('.front-signalement #step-11 .btn-next').removeAttr('disabled');
      }
    });
  }
}


