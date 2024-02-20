import $ from 'jquery';
$(function() {
  if ($('div.creer-signalement').length > 0) {
    startCreerSignalementApp();
  }
});

function startCreerSignalementApp() {
  // Navigation
  $('nav.stepper-next button').on('click', function() {
    if (checkSignalementFirstStep()) {
      $('.fr-stepper__state span').text('2');
      $('.fr-stepper__title span[data-step=1]').addClass('fr-stepper__hidden');
      $('.fr-stepper__title span[data-step=2]').removeClass('fr-stepper__hidden');
      $('.fr-stepper__steps').attr('data-fr-current-step', '2');
      $('#form-creer-signalement-step-1').addClass('fr-stepper__hidden');
      $('#form-creer-signalement-step-2').removeClass('fr-stepper__hidden');
    }
  });

  $('nav.stepper-previous button.go-previous').on('click', function() {
    $('.fr-stepper__state span').text('1');
    $('.fr-stepper__title span[data-step=1]').removeClass('fr-stepper__hidden');
    $('.fr-stepper__title span[data-step=2]').addClass('fr-stepper__hidden');
    $('.fr-stepper__steps').attr('data-fr-current-step', '1');
    $('#form-creer-signalement-step-1').removeClass('fr-stepper__hidden');
    $('#form-creer-signalement-step-2').addClass('fr-stepper__hidden');
  });

  // First tab
  $('select#signalement_history_typeLogement').on('change', function() {
    if($(this).val() == 'appartement') {
      $('div#form-group-localisation-immeuble').removeClass('fr-form-group-hidden')
    } else {
      $('div#form-group-localisation-immeuble').addClass('fr-form-group-hidden')
    }
  });

  // Second tab
  $('select#signalement_history_typeIntervention').on('change', function() {
    let hasTypeTraitement = $(this).val().indexOf('traitement') > -1;
    switch ($(this).val()) {
      case 'diagnostic':
        $('.display-if-traitement').hide();
        $('.display-if-diagnostic').show(100);
        break;
      case 'traitement':
        $('.display-if-diagnostic').hide();
        $('.display-if-traitement').show(100);
        break;
      case 'diagnostic-traitement':
        $('.display-if-diagnostic').show(100);
        $('.display-if-traitement').show(100);
        break;
      default:
        $('.display-if-diagnostic').hide();
        $('.display-if-traitement').hide();
        break;
    }

    if (hasTypeTraitement && $('#signalement_history_typeTraitement').val() == 'biocide') {
      $('.display-if-biocide').show();
    } else {
      $('.display-if-biocide').hide();
    }

    if ($(this).val() !== '') {
      $('.stepper-previous .fr-btn').removeAttr('disabled');
    } else {
      $('.stepper-previous .fr-btn').attr('disabled', 'disabled');
    }
  });

  $('select#signalement_history_entreprise').on('change', function() {
    let ajaxURL = $("form").data('ajax');
    $.ajax({
      url: ajaxURL,
      data: { idEntreprise: $(this).val() }
    }).done(function(jsonData) {
      $('#signalement_history_agent').empty();
      $('#signalement_history_agent').append('<option value="" selected="selected">Nom de l&apos;agent</option>');
      for (let idEmploye in jsonData.data) {
        $('#signalement_history_agent').append('<option value="'+idEmploye+'">'+jsonData.data[idEmploye]+'</option>');
      }
    });
  });

  $('select#signalement_history_typeTraitement').on('change', function() {
    if ($(this).val() == 'biocide') {
      $('.display-if-biocide').show();
    } else {
      $('.display-if-biocide').hide();
    }
  });
    
}

function checkSignalementSingleInput(idInput) {
  const input = $('input#' + idInput);
  const errorText = input.siblings('.fr-error-text');
  if (input.val() === '') {
      errorText.removeClass('fr-hidden');
      input.attr('aria-describedby', idInput + '-error');
      return false;
  } 
  errorText.addClass('fr-hidden');
  input.removeAttr('aria-describedby');
  return true;
}

function checkSignalementSingleSelect(idSelect) {
  $('select#' + idSelect).siblings('.fr-error-text').addClass('fr-hidden');
  if ($('select#' + idSelect).val() == '') {
    $('select#' + idSelect).siblings('.fr-error-text').removeClass('fr-hidden');
    $('select#' + idSelect).attr('aria-describedby', idSelect + '-error');
    return false;
  }
  return true;
}

function checkSignalementFirstStep() {
  let buffer = true;

  buffer = checkSignalementSingleInput('signalement_history_adresse');
  let cpRegex = /[0-9]{5}/;
  const divCodePostal = $('#signalement_history_codePostal')
  if (divCodePostal.val() === '') {
    divCodePostal.siblings('.fr-error-text').text('Veuillez renseigner le code postal.').removeClass('fr-hidden');
    divCodePostal.attr('aria-describedby', 'signalement_history_codePostal-error');
    buffer = false;
  } else if (!$('#signalement_history_codePostal').val().match(cpRegex)) {
    divCodePostal.siblings('.fr-error-text').text('Le format du code postal est incorrect.').removeClass('fr-hidden');
    divCodePostal.attr('aria-describedby', 'signalement_history_codePostal-error');
    buffer = false;
  } else {
    divCodePostal.siblings('.fr-error-text').text('Veuillez renseigner le code postal.').addClass('fr-hidden');
  }
  buffer = checkSignalementSingleInput('signalement_history_ville') && buffer;
  buffer = checkSignalementSingleSelect('signalement_history_typeLogement') && buffer;
  buffer = checkSignalementSingleInput('signalement_history_nomOccupant') && buffer;
  buffer = checkSignalementSingleInput('signalement_history_prenomOccupant') && buffer;

  let telRegex = /[0-9]{10}/;
  if ($('input#signalement_history_telephoneOccupant').val() != '' &&  !$('#signalement_history_telephoneOccupant').val().match(telRegex)) {
    $('input#signalement_history_telephoneOccupant').siblings('.fr-error-text').text('Le format du numéro de téléphone est incorrect.').removeClass('fr-hidden');
    $('input#signalement_history_telephoneOccupant').attr('aria-describedby', 'signalement_history_telephoneOccupant-error');
    buffer = false;
  } else {
    $('input#signalement_history_telephoneOccupant').siblings('.fr-error-text').text('Veuillez renseigner le numéro de téléphone de l\'occupant.').addClass('fr-hidden');
  }

  let emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
  if ($('input#signalement_history_emailOccupant').val() != '' && !$('#signalement_history_emailOccupant').val().match(emailRegex)) {
    $('input#signalement_history_emailOccupant').siblings('.fr-error-text').removeClass('fr-hidden');
    $('input#signalement_history_emailOccupant').attr('aria-describedby', 'signalement_history_emailOccupant-error');
    buffer = false;
  } else {
    $('input#signalement_history_emailOccupant').siblings('.fr-error-text').addClass('fr-hidden');
  }

  if (buffer && $('input#signalement_history_codeInsee').val() == '') {
    $('input#signalement_history_codeInsee').val(0);
  }
  
  return buffer;
}