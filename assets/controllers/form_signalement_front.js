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
  OPEN_STEP_LIST = [
    'home',
    'info_intro',
    'info_logement',
    'info_locataire',
    'info_problemes',
    'traces_punaises_intro',
    'traces_punaises_piqures',
    'traces_punaises_dejections',
    'insectes_larves_intro',
    'insectes_larves_oeufs',
    'insectes_punaises',
    'info_usager',
    'recommandation',
    'professionnel_info',
    'autotraitement_info',
    'professionnel_sent',
    'autotraitement_sent',
  ];
  CLOSED_STEP_LIST = [
    'home',
    'info_usager',
  ];
  SOCIAL_STEP_LIST = [
    'home',
    'info_intro',
    'info_logement',
    'info_locataire',
    'info_usager',
    'autotraitement_info',
    'autotraitement_sent',
  ];
  OPEN_TERRITORIES = [ '13', '69' ];
  step = 1;
  stepStr = 'home';
  isTerritoryOpen = true;
  isLogementSocial = false;
  hasTraces = false;
  hasRechercheInsecte = false;
  hasInsectes = false;
  hasLocalisation = false;

  init() {
    self = this;
    $('.btn-next').on('click', function(){
      if (!self.isTerritoryOpen && self.stepStr === 'info_usager') {
        self.submitAdd();
        return;
      }
      
      self.refreshStep(1);
    });
    $('.btn-next-next').on('click', function(){
      switch (self.stepStr) {
        case 'recommandation':
          $('#signalement_front_autotraitement').val(true);
          self.refreshStep(2);
          break;
        case 'professionnel_info':
        case 'autotraitement_info':
          self.submitAdd();
          break;
        default:
          self.refreshStep(2);
          break;
      }
    });
    $('.link-back').on('click', function(){
      self.refreshStep(-1);
    });
    $('.link-back-back').on('click', function(){
      self.refreshStep(-2);
    });
    if ($('form.front-signalement').data('code-postal') !== '') {
      $('#code-postal').val($('form.front-signalement').data('code-postal'));
      $('#step-home button').click();
    }
  }

  refreshStep(offset) {
    let acceptRefresh = (offset > 0) ? self.checkStep() : true;
    if (acceptRefresh) {
      self.step += offset;
      if (self.isLogementSocial) {
        self.stepStr = self.SOCIAL_STEP_LIST[self.step - 1];
      } else if (self.isTerritoryOpen) {
        self.stepStr = self.OPEN_STEP_LIST[self.step - 1];
      } else {
        self.stepStr = self.CLOSED_STEP_LIST[self.step - 1];
      }
    
      $('.current-step').slideUp(200, function() {
        $('.current-step').removeClass('current-step');
        $('#step-' + self.stepStr).slideDown(200, function() {
          $('#step-' + self.stepStr).addClass('current-step');
        });
        self.initStep();
      });
    }
  }

  initStep() {
    switch (self.stepStr) {
      case 'info_locataire':
        return self.initStepInfoLocataire();
      case 'traces_punaises_piqures':
        return self.initStepTracesPunaisesPiqures();
      case 'traces_punaises_dejections':
        return self.initStepTracesPunaisesDejections();
      case 'insectes_larves_oeufs':
        return self.initStepInsectesLarvesOeufs();
      case 'insectes_punaises':
        return self.initStepInsectesPunaises();
      case 'info_usager':
        return self.initStepInfoUsager();
      case 'professionnel_info':
        return self.initProfessionnelInfo();
      case 'autotraitement_info':
        return self.initAutotraitementInfo();
      default:
        return true;
    }
  }

  checkStep() {
    switch (self.stepStr) {
      case 'home':
        return self.checkStepHome();
      case 'info_logement':
        return self.checkStepInfoLogement();
      case 'info_locataire':
        return self.checkStepInfoLocataire();
      case 'info_problemes':
        return self.checkStepInfoProblemes();
      case 'traces_punaises_piqures':
        return self.checkStepTracesPunaisesPiqures();
      case 'traces_punaises_dejections':
        return self.checkStepTracesPunaisesDejections();
      case 'insectes_larves_oeufs':
        return self.checkStepInsectesLarvesOeufs();
      case 'insectes_punaises':
        return self.checkStepInsectesPunaises();
      case 'info_usager':
        return self.checkStepInfoUsagerOpen();
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
    if (self.hasTraces || self.hasRechercheInsecte) {
      niveauInfestation = 1;
    }
    if ((self.hasTraces && self.hasRechercheInsecte) || self.hasInsectes) {
      niveauInfestation = 2;
    }
    if ((self.hasTraces || self.hasRechercheInsecte) && self.hasInsectes) {
      niveauInfestation = 3;
    }
    if (self.hasTraces && self.hasLocalisation && (self.hasRechercheInsecte || self.hasInsectes)) {
      niveauInfestation = 4;
    }

    $('#niveau-infestation span').text(niveauInfestation);
    $('#niveau-infestation span').removeClass('niveau-0 niveau-1 niveau-2 niveau-3 niveau-4');
    $('#niveau-infestation span').addClass('niveau-' + niveauInfestation);
    $('#signalement_front_niveauInfestation').val(niveauInfestation);

    $('.if-recommandation-zero').hide();
    $('.if-recommandation-not-zero').show();

    switch (niveauInfestation) {
      case 0:
        $('#niveau-infestation-txt').text('Aucune infestation');
        $('.if-recommandation-zero').show();
        $('.if-recommandation-not-zero').hide();
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

  checkStepHome() {
    let canGoNext = self.checkSingleInput('code-postal');
    if (canGoNext) {
      let inputContent = $('input#code-postal').val();
      let zipCode = inputContent.substring(0, 2);
      self.isTerritoryOpen = (self.OPEN_TERRITORIES.indexOf(zipCode) > -1);
    }
    return canGoNext;
  }

  checkStepInfoLogement() {
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

    // Cas particulier du champ de recherche : erreur à afficher si les champs d'adresse sont invisibles
    if ($('.address-fields').hasClass('fr-hidden')) {
      $('.search-address .fr-error-text').removeClass('fr-hidden');
    } else {
      $('.search-address .fr-error-text').addClass('fr-hidden');
    }
    
    // Re-vérification code postal
    if (canGoNext) {
      let inputContent = $('input#signalement_front_codePostal').val();
      let zipCode = inputContent.substring(0, 2);
      self.isTerritoryOpen = (self.OPEN_TERRITORIES.indexOf(zipCode) > -1);
      if (!self.isTerritoryOpen) {
        self.step = 1;
      }
    }
    
    return canGoNext;
  }

  initStepInfoLocataire() {
    self.updateStepInfoLocataireProprietaire();
    $('#step-'+self.stepStr+' input[name="signalement_front[locataire]"]').on('click', function() {
      self.updateStepInfoLocataireProprietaire();
    });
    self.updateStepInfoLocataireAllocataire();
    $('#step-'+self.stepStr+' input[name="signalement_front[allocataire]"]').on('click', function() {
      self.updateStepInfoLocataireAllocataire();
    });
  }

  updateStepInfoLocataireProprietaire() {
    let isVisible = $('#step-'+self.stepStr+' #signalement_front_locataire_1').prop('checked');
    if (isVisible) {
      $('#step-'+self.stepStr+' #form-group-nomProprietaire').slideDown(200);
    } else {
      $('#step-'+self.stepStr+' #form-group-nomProprietaire').slideUp(200);
    }
  }

  updateStepInfoLocataireAllocataire() {
    let isVisible = $('#step-'+self.stepStr+' #signalement_front_allocataire_0').prop('checked');
    if (isVisible) {
      $('#step-'+self.stepStr+' #form-group-numeroAllocataire').slideDown(200);
    } else {
      $('#step-'+self.stepStr+' #form-group-numeroAllocataire').slideUp(200);
    }
  }

  checkStepInfoLocataire() {
    let canGoNext = true;
    if (!self.checkChoicesInput('locataire', 2)) {
      canGoNext = false;
    }
    if ($('#signalement_front_locataire_1').prop('checked') && !self.checkSingleInput('signalement_front_nomProprietaire')) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('logementSocial', 2)) {
      canGoNext = false;
    }
    if (!self.checkChoicesInput('allocataire', 2)) {
      canGoNext = false;
    }
    if ($('#signalement_front_allocataire_0').prop('checked') && !self.checkSingleInput('signalement_front_numeroAllocataire')) {
      canGoNext = false;
    }

    if (canGoNext) {
      self.isLogementSocial = $('#signalement_front_logementSocial_0').prop('checked');
    }
    
    return canGoNext;
  }

  checkStepInfoProblemes() {
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

  initStepTracesPunaisesPiqures() {
    self.updateStepTracesPunaisesPiqures();
    $('#step-'+self.stepStr+' input[name="signalement_front[piquresExistantes]"]').on('click', function() {
      self.updateStepTracesPunaisesPiqures();
    });

    $('#file-upload').on('change', function(event) {
      $('.fr-front-signalement-photos').empty();
      for (let i = 0; i < event.target.files.length; i++) {
        let imgSrc = URL.createObjectURL(event.target.files[i]);
        let strAppend = '<div class="fr-col-6 fr-col-md-3" style="text-align: center;">';
        strAppend += '<img src="' + imgSrc + '" width="100" height="100">';
        strAppend += '</div>';
        $('.fr-front-signalement-photos').append(strAppend);   
      }
    });
  }

  updateStepTracesPunaisesPiqures() {
    let isVisible = $('#step-'+self.stepStr+' #signalement_front_piquresExistantes_0').prop('checked');
    if (isVisible) {
      $('#step-'+self.stepStr+' #form-group-piquresConfirmees').slideDown(200);
      $('#step-'+self.stepStr+' #form-group-photos').slideDown(200);
    } else {
      $('#step-'+self.stepStr+' #form-group-piquresConfirmees').slideUp(200);
      $('#step-'+self.stepStr+' #form-group-photos').slideUp(200);
    }
  }

  checkStepTracesPunaisesPiqures() {
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

  initStepTracesPunaisesDejections() {
    self.updateStepTracesPunaisesDejections();
    $('#step-'+self.stepStr+' input[name="signalement_front[dejectionsTrouvees]"]').on('click', function() {
      self.updateStepTracesPunaisesDejections();
    });
  }

  updateStepTracesPunaisesDejections() {
    let isVisible = $('#step-'+self.stepStr+' #signalement_front_dejectionsTrouvees_0').prop('checked');
    if (isVisible) {
      $('#step-'+self.stepStr+' #form-group-dejectionsNombrePiecesConcernees').slideDown(200);
      $('#step-'+self.stepStr+' #form-group-dejectionsFaciliteDetections').slideDown(200);
      $('#step-'+self.stepStr+' #form-group-dejectionsLieuxObservations').slideDown(200);
    } else {
      $('#step-'+self.stepStr+' #form-group-dejectionsNombrePiecesConcernees').slideUp(200);
      $('#step-'+self.stepStr+' #form-group-dejectionsFaciliteDetections').slideUp(200);
      $('#step-'+self.stepStr+' #form-group-dejectionsLieuxObservations').slideUp(200);
    }
  }

  checkStepTracesPunaisesDejections() {
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

  initStepInsectesLarvesOeufs() {
    self.updateStepInsectesLarvesOeufs();
    $('#step-'+self.stepStr+' input[name="signalement_front[oeufsEtLarvesTrouves]"]').on('click', function() {
      self.updateStepInsectesLarvesOeufs();
    });
  }

  updateStepInsectesLarvesOeufs() {
    let isVisible = $('#step-'+self.stepStr+' #signalement_front_oeufsEtLarvesTrouves_0').prop('checked');
    if (isVisible) {
      $('#step-'+self.stepStr+' #form-group-oeufsEtLarvesNombrePiecesConcernees').slideDown(200);
      $('#step-'+self.stepStr+' #form-group-oeufsEtLarvesFaciliteDetections').slideDown(200);
      $('#step-'+self.stepStr+' #form-group-oeufsEtLarvesLieuxObservations').slideDown(200);
    } else {
      $('#step-'+self.stepStr+' #form-group-oeufsEtLarvesNombrePiecesConcernees').slideUp(200);
      $('#step-'+self.stepStr+' #form-group-oeufsEtLarvesFaciliteDetections').slideUp(200);
      $('#step-'+self.stepStr+' #form-group-oeufsEtLarvesLieuxObservations').slideUp(200);
    }
  }

  checkStepInsectesLarvesOeufs() {
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

  initStepInsectesPunaises() {
    self.updateStepInsectesPunaises();
    $('#step-'+self.stepStr+' input[name="signalement_front[punaisesTrouvees]"]').on('click', function() {
      self.updateStepInsectesPunaises();
    });
  }

  updateStepInsectesPunaises() {
    let isVisible = $('#step-'+self.stepStr+' #signalement_front_punaisesTrouvees_0').prop('checked');
    if (isVisible) {
      $('#step-'+self.stepStr+' #form-group-punaisesNombrePiecesConcernees').slideDown(200);
      $('#step-'+self.stepStr+' #form-group-punaisesFaciliteDetections').slideDown(200);
      $('#step-'+self.stepStr+' #form-group-punaisesLieuxObservations').slideDown(200);
    } else {
      $('#step-'+self.stepStr+' #form-group-punaisesNombrePiecesConcernees').slideUp(200);
      $('#step-'+self.stepStr+' #form-group-punaisesFaciliteDetections').slideUp(200);
      $('#step-'+self.stepStr+' #form-group-punaisesLieuxObservations').slideUp(200);
    }
  }

  checkStepInsectesPunaises() {
    let canGoNext = true;
    if (!self.checkChoicesInput('punaisesTrouvees', 2)) {
      canGoNext = false;
    }
    if ($('#signalement_front_punaisesTrouvees_0').prop('checked')) {
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

  initStepInfoUsager() {
    if (self.isLogementSocial) {
      $('.if-territory-open').hide();
      $('.if-territory-not-open').hide();
      $('.if-logement-social').show();

    } else if (self.isTerritoryOpen) {
      $('.if-territory-open').show();
      $('.if-territory-not-open').hide();
      $('.if-logement-social').hide();

    } else {
      $('#signalement_front_autotraitement').val(true);
      $('.if-territory-open').hide();
      $('.if-logement-social').hide();
      $('.if-territory-not-open').show();
      $('.if-territory-not-open').append('<input type="hidden" id="hidden-postal-code" name="signalement_front[codePostal]" value="'+ $('input#code-postal').val() +'">');
    }
  }

  checkStepInfoUsagerOpen() {
    let canGoNext = true;
    if (!self.checkSingleInput('signalement_front_nomOccupant')) {
      canGoNext = false;
    }
    if (!self.checkSingleInput('signalement_front_prenomOccupant')) {
      canGoNext = false;
    }
    if (self.isTerritoryOpen && !self.isLogementSocial) {
      if (!self.checkSingleInput('signalement_front_telephoneOccupant') || $('#signalement_front_telephoneOccupant').val().length < 10 ) {
        $('input#signalement_front_telephoneOccupant').siblings('.fr-error-text').removeClass('fr-hidden');
        canGoNext = false;
      }
    }
    let emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
    if (!self.checkSingleInput('signalement_front_emailOccupant') || !$('#signalement_front_emailOccupant').val().match(emailRegex)) {
      $('input#signalement_front_emailOccupant').siblings('.fr-error-text').removeClass('fr-hidden');
      canGoNext = false;
    }
    
    return canGoNext;
  }

  initProfessionnelInfo() {
    $('#professionnel-email').text($('#signalement_front_emailOccupant').val());
  }

  initAutotraitementInfo() {
    $('#autotraitement-email').text($('#signalement_front_emailOccupant').val());
  }

  submitAdd() {
    $('.front-signalement #step-info_usager .btn-next').attr('disabled', 'disabled');
    $('.front-signalement #step-professionnel_info .btn-next-next').attr('disabled', 'disabled');
    $('.front-signalement #step-autotraitement_info .btn-next-next').attr('disabled', 'disabled');
    if (self.isLogementSocial) {
      $('#signalement_front_autotraitement').val(true);
    }
    var formData = new FormData($('.front-signalement')[0]);
    $.ajax({
      type: 'POST',
      url: $('.front-signalement').attr('action'),
      data: formData,
      contentType:false,
      cache:false,
      processData:false,
  
      success: function() {
        if (!self.isTerritoryOpen) {
          let codePostal = $('input#code-postal').val();
          window.location.href = $('input#url-entreprises-publiques').val() + codePostal;
          return;
        }

        let nbStep = 2;
        if (self.isLogementSocial) {
          nbStep = 1;
        }
        self.refreshStep(nbStep);
      },
      error: function (xhr, desc, err) {
        console.log(xhr);
        if (xhr.responseJSON != undefined) {
          alert("Erreur lors de l'ajout du signalement (" + xhr.responseJSON.errors[0].message + ")");
        } else {
          alert("Erreur lors de l'ajout du signalement");
        }
        $('.front-signalement #step-professionnel_info .btn-next').removeAttr('disabled');
        $('.front-signalement #step-autotraitement_info .btn-next').removeAttr('disabled');
      }
    });
  }
}


