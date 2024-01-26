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
    'resume',
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
  OPEN_TERRITORIES = [];
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
    self.fetchTerritoireOpened();
    $('input').on('keyup', function(e){
      if (e.which == 13) {
        e.preventDefault();
        $('#step-'+self.stepStr + ' .btn-next').trigger( "click" );
      }
    });
    $('.btn-next').on('click', function(){
      if (!self.isTerritoryOpen && self.stepStr === 'info_usager') {
        if (self.checkStepInfoUsagerOpen()) {
          self.submitAdd();
        }
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
    $('.link-edit').on('click', function(e){
      e.preventDefault();
      let offset = self.OPEN_STEP_LIST.indexOf($(this).attr('data-step')) - self.OPEN_STEP_LIST.indexOf(self.stepStr);
      self.refreshStep(offset);
    });
  }

  async fetchTerritoireOpened() {
     const territoires = $('form.front-signalement').attr('data-territoires-actives');
     if (territoires.length > 0) {
       this.OPEN_TERRITORIES = territoires.split(',');
     }
  }

  checkCodePostal(idInput) {
    if ($('input#' + idInput).val() !== '') {    
      const postalCodePattern = /^\d{5}$/;
      if (!postalCodePattern.test($('input#' + idInput).val())) {
        $('input#' + idInput).siblings('.fr-error-text').removeClass('fr-hidden');
        return false;
      }
    } else {
      $('input#' + idInput).siblings('.fr-error-text').removeClass('fr-hidden');
      return false;
    }
    $('input#' + idInput).siblings('.fr-error-text').addClass('fr-hidden');
    return true;

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
    } else {
      scrollTo({
        top: 0,
        behavior: "smooth",
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
      case 'resume':
        return self.initStepResume();
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

  checkValidSuperficieInput(idInput) {
    $('input#' + idInput).siblings('.fr-error-text').addClass('fr-hidden');
    $('input#' + idInput).siblings('.fr-error-text').text('Veuillez renseigner la superficie de votre logement.');

    if ($('input#' + idInput).val() == '') {
      $('input#' + idInput).siblings('.fr-error-text').removeClass('fr-hidden');
      return false;
    }
    let intVal = parseInt($('input#' + idInput).val() );
    if (isNaN(intVal)) {
      $('input#' + idInput).siblings('.fr-error-text').text('La superficie n\'est pas au bon format.');
      $('input#' + idInput).siblings('.fr-error-text').removeClass('fr-hidden');
      return false;
    }
    if (intVal > 30000) {
      $('input#' + idInput).siblings('.fr-error-text').text('La superficie doit être inférieure à 30000 m².');
      $('input#' + idInput).siblings('.fr-error-text').removeClass('fr-hidden');
      return false;
    }
    $('input#' + idInput).val(intVal);
    return true;
  }  

  checkChoicesInput(idInput, count) {
    $('#signalement_front_' + idInput + '_legend').siblings('.fr-error-text').addClass('fr-hidden');

    let canGoNext = false;
    for (let i = 0; i < count; i++) {
      if ($('input#signalement_front_' + idInput + '_' + i).prop('checked')) {
        canGoNext = true;
      }
    }

    if (!canGoNext) {
      $('#signalement_front_' + idInput + '_legend').siblings('.fr-error-text').removeClass('fr-hidden');
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
    let canGoNext = self.checkCodePostal('code-postal');
    if (canGoNext) {
      let inputContent = $('input#code-postal').val();
      let zipCode = inputContent.substring(0, 2);
      if (zipCode === '97') {
        zipCode = inputContent.substring(0, 3);
      }
      self.isTerritoryOpen = (self.OPEN_TERRITORIES.indexOf(zipCode) > -1);
    }
    return canGoNext;
  }

  checkStepInfoLogement() {
    let canGoNext = true;
    if (!self.checkChoicesInput('typeLogement', 3)) {
      canGoNext = false;
    }

    if (!self.checkValidSuperficieInput('signalement_front_superficie')) {
      canGoNext = false;
    }
    
    if (!self.checkSingleInput('signalement_front_adresse')) {
      canGoNext = false;
    }
    if (!self.checkSingleInput('signalement_front_codePostal')) {
      canGoNext = false;
    }
    if (!self.checkCodePostal('signalement_front_codePostal')) {
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
      if (zipCode === '97') {
        zipCode = inputContent.substring(0, 3);
      }
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

  initStepResume(){
    const typeLogement = $("input[name='signalement_front[typeLogement]']:checked").val();
    $("#recapTypeLogement").text(typeLogement.charAt(0).toUpperCase() + typeLogement.slice(1));
    $("#recapSuperficie").text($("input[name='signalement_front[superficie]']").val());
    $("#recapAdresse").text($("input[name='signalement_front[adresse]']").val());
    $("#recapCodePostal").text($("input[name='signalement_front[codePostal]']").val());
    $("#recapVille").text($("input[name='signalement_front[ville]']").val());
    const locataire = $("input[name='signalement_front[locataire]']:checked").val();
    $("#recapLocataire").empty();
    $("#recapNomProprietaire").empty();
    $("#recapNomProprietaireContainer").hide();
    if(locataire == 1){
      $("#recapLocataire").text("Locataire");
      $("#recapNomProprietaireContainer").show();
      $("#recapNomProprietaire").text($("input[name='signalement_front[nomProprietaire]']").val());
    }else{
      $("#recapLocataire").text("Propriétaire");
    }
    const logementSocial = $("input[name='signalement_front[logementSocial]']:checked").val();
    $("#recapLogementSocial").text("Non");
    if(logementSocial == 1){
      $("#recapLogementSocial").text("Oui");
    }
    const allocataire = $("input[name='signalement_front[allocataire]']:checked").val();
    $("#recapAllocataire").text("Non");
    $("#recapNumeroAllocataire").empty();
    $("#recapNumeroAllocataireContainer").hide();
    if(allocataire == 1){
      $("#recapAllocataire").text("Oui");
      $("#recapNumeroAllocataireContainer").show();
      $("#recapNumeroAllocataire").text($("input[name='signalement_front[numeroAllocataire]']").val());
    }
    const dureeInfestation = $("input[name='signalement_front[dureeInfestation]']:checked").next("label").text();
    $("#recapDureeInfestation").text(dureeInfestation);
    const infestationLogementsVoisins = $("input[name='signalement_front[infestationLogementsVoisins]']:checked").next("label").text();
    $("#recapInfestationLogementsVoisins").text(infestationLogementsVoisins);
    const piquresExistantes = $("input[name='signalement_front[piquresExistantes]']:checked").val();
    $("#recapPiquresExistantes").text("Non");
    $("#recapPiquresConfirmeesContainer").hide();
    if(piquresExistantes == 1){
      $("#recapPiquresExistantes").text("Oui");
      $("#recapPiquresConfirmeesContainer").show();
      const piquresConfirmees = $("input[name='signalement_front[piquresConfirmees]']:checked").val();
      $("#recapPiquresConfirmees").text("Non");
      if(piquresConfirmees == 1){
        $("#recapPiquresConfirmees").text("Oui");
      }
    }
    const dejectionsTrouvees = $("input[name='signalement_front[dejectionsTrouvees]']:checked").val();
    $("#recapDejectionsTrouvees").text("Non");
    $(".isDejectionsTrouvees").hide();
    if(dejectionsTrouvees == "true"){
      $("#recapDejectionsTrouvees").text("Oui");
      $(".isDejectionsTrouvees").show();
      const dejectionsNombrePiecesConcernees = $("input[name='signalement_front[dejectionsNombrePiecesConcernees]']:checked").next("label").text();
      $("#recapDejectionsNombrePiecesConcernees").text(dejectionsNombrePiecesConcernees);
      const dejectionsFaciliteDetections = $("input[name='signalement_front[dejectionsFaciliteDetections]']:checked").next("label").text();
      $("#recapDejectionsFaciliteDetections").text(dejectionsFaciliteDetections);
      $("#recapDejectionsLieuxObservations").empty()
      $("input[name='signalement_front[dejectionsLieuxObservations][]']:checked").each(function() {
        $("#recapDejectionsLieuxObservations").append($(this).next("label").text() + ", ");
      });
      const sliced = $("#recapDejectionsLieuxObservations").text().slice(0,-2)
      $("#recapDejectionsLieuxObservations").text(sliced);
    }
    const oeufsEtLarvesTrouves = $("input[name='signalement_front[oeufsEtLarvesTrouves]']:checked").val();
    $("#recapOeufsEtLarvesTrouves").text("Non");
    $(".isOeufsEtLarvesTrouves").hide();
    if(oeufsEtLarvesTrouves == "true"){
      $("#recapOeufsEtLarvesTrouves").text("Oui");
      $(".isOeufsEtLarvesTrouves").show();
      const oeufsEtLarvesNombrePiecesConcernees = $("input[name='signalement_front[oeufsEtLarvesNombrePiecesConcernees]']:checked").next("label").text();
      $("#recapOeufsEtLarvesNombrePiecesConcernees").text(oeufsEtLarvesNombrePiecesConcernees);
      const oeufsEtLarvesFaciliteDetections = $("input[name='signalement_front[oeufsEtLarvesFaciliteDetections]']:checked").next("label").text();
      $("#recapOeufsEtLarvesFaciliteDetections").text(oeufsEtLarvesFaciliteDetections);
      $("#recapOeufsEtLarvesLieuxObservations").empty()
      $("input[name='signalement_front[oeufsEtLarvesLieuxObservations][]']:checked").each(function() {
        $("#recapOeufsEtLarvesLieuxObservations").append($(this).next("label").text() + ", ");
      });
      const sliced = $("#recapOeufsEtLarvesLieuxObservations").text().slice(0,-2)
      $("#recapOeufsEtLarvesLieuxObservations").text(sliced);
    }
    const punaisesTrouvees = $("input[name='signalement_front[punaisesTrouvees]']:checked").val();
    $("#recapPunaisesTrouvees").text("Non");
    $(".isPunaisesTrouvees").hide();
    if(punaisesTrouvees == "true"){
      $("#recapPunaisesTrouvees").text("Oui");
      $(".isPunaisesTrouvees").show();
      const punaisesNombrePiecesConcernees = $("input[name='signalement_front[punaisesNombrePiecesConcernees]']:checked").next("label").text();
      $("#recapPunaisesNombrePiecesConcernees").text(punaisesNombrePiecesConcernees);
      const punaisesFaciliteDetections = $("input[name='signalement_front[punaisesFaciliteDetections]']:checked").next("label").text();
      $("#recapPunaisesFaciliteDetections").text(punaisesFaciliteDetections);
      $("#recapPunaisesLieuxObservations").empty()
      $("input[name='signalement_front[punaisesLieuxObservations][]']:checked").each(function() {
        $("#recapPunaisesLieuxObservations").append($(this).next("label").text() + ", ");
      });
      const sliced = $("#recapPunaisesLieuxObservations").text().slice(0,-2)
      $("#recapPunaisesLieuxObservations").text(sliced);
    }
    $("#recapNomOccupant").text($("input[name='signalement_front[nomOccupant]']").val());
    $("#recapPrenomOccupant").text($("input[name='signalement_front[prenomOccupant]']").val());
    $("#recapTelephoneOccupant").text($("input[name='signalement_front[telephoneOccupant]']").val());
    $("#recapEmailOccupant").text($("input[name='signalement_front[emailOccupant]']").val());
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
        $('.front-signalement #step-info_usager .btn-next').removeAttr('disabled');
      }
    });
  }
}


