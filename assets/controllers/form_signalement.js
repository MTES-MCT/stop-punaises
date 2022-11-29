import $ from 'jquery';
$(function() {
  if ($('div.creer-signalement').length > 0) {
    startCreerSignalementApp();
  }
});

function startCreerSignalementApp() {
  // Navigation
  $('nav.stepper-next a').on('click', function() {
    if (checkSignalementFirstStep()) {
      $('.fr-stepper__state span').text('2');
      $('.fr-stepper__title span[data-step=1]').addClass('fr-stepper__hidden');
      $('.fr-stepper__title span[data-step=2]').removeClass('fr-stepper__hidden');
      $('.fr-stepper__steps').attr('data-fr-current-step', '2');
      $('#form-creer-signalement-step-1').addClass('fr-stepper__hidden');
      $('#form-creer-signalement-step-2').removeClass('fr-stepper__hidden');
    }
  });

  $('nav.stepper-previous a').on('click', function() {
    $('.fr-stepper__state span').text('1');
    $('.fr-stepper__title span[data-step=1]').removeClass('fr-stepper__hidden');
    $('.fr-stepper__title span[data-step=2]').addClass('fr-stepper__hidden');
    $('.fr-stepper__steps').attr('data-fr-current-step', '1');
    $('#form-creer-signalement-step-1').removeClass('fr-stepper__hidden');
    $('#form-creer-signalement-step-2').addClass('fr-stepper__hidden');
  });

  // First tab
  $('select#signalement_typeLogement').on('change', function() {
    if($(this).val() == 'appartement') {
      $('div#form-group-localisation-immeuble').removeClass('fr-form-group-hidden')
    } else {
      $('div#form-group-localisation-immeuble').addClass('fr-form-group-hidden')
    }
  });

  // Second tab
  $('select#signalement_typeIntervention').on('change', function() {
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

    if (hasTypeTraitement && $('#signalement_typeTraitement').val() == 'biocide') {
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

  $('select#signalement_entreprise').on('change', function() {
    let ajaxURL = $("form").data('ajax');
    $.ajax({
      url: ajaxURL,
      data: { idEntreprise: $(this).val() }
    }).done(function(jsonData) {
      $('#signalement_agent').empty();
      $('#signalement_agent').append('<option value="" selected="selected">Nom de l&apos;agent</option>');
      for (let idEmploye in jsonData.data) {
        $('#signalement_agent').append('<option value="'+idEmploye+'">'+jsonData.data[idEmploye]+'</option>');
      }
    });
  });

  $('select#signalement_typeTraitement').on('change', function() {
    if ($(this).val() == 'biocide') {
      $('.display-if-biocide').show();
    } else {
      $('.display-if-biocide').hide();
    }
  });
    
}

function checkSignalementSingleInput(idInput) {
  $('input#' + idInput).siblings('.fr-error-text').addClass('fr-hidden');
  if ($('input#' + idInput).val() == '') {
    $('input#' + idInput).siblings('.fr-error-text').removeClass('fr-hidden');
    return false;
  }
  return true;
}

function checkSignalementSingleSelect(idSelect) {
  $('select#' + idSelect).siblings('.fr-error-text').addClass('fr-hidden');
  if ($('select#' + idSelect).val() == '') {
    $('select#' + idSelect).siblings('.fr-error-text').removeClass('fr-hidden');
    return false;
  }
  return true;
}

function checkSignalementFirstStep() {
  let buffer = true;

  buffer = checkSignalementSingleInput('signalement_adresse');
  buffer = checkSignalementSingleInput('signalement_codePostal') && buffer;
  buffer = checkSignalementSingleInput('signalement_ville') && buffer;
  buffer = checkSignalementSingleSelect('signalement_typeLogement') && buffer;
  buffer = checkSignalementSingleInput('signalement_nomOccupant') && buffer;
  buffer = checkSignalementSingleInput('signalement_prenomOccupant') && buffer;

  if ($('input#signalement_telephoneOccupant').val() != '' && $('input#signalement_telephoneOccupant').val().length < 10) {
    $('input#signalement_telephoneOccupant').siblings('.fr-error-text').removeClass('fr-hidden');
    buffer = false;
  } else {
    $('input#signalement_telephoneOccupant').siblings('.fr-error-text').addClass('fr-hidden');
  }
  let emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
  if ($('input#signalement_emailOccupant').val() != '' && !$('#signalement_emailOccupant').val().match(emailRegex)) {
    $('input#signalement_emailOccupant').siblings('.fr-error-text').removeClass('fr-hidden');
    buffer = false;
  } else {
    $('input#signalement_emailOccupant').siblings('.fr-error-text').addClass('fr-hidden');
  }

  if (buffer && $('input#signalement_codeInsee').val() == '') {
    $('input#signalement_codeInsee').val(0);
  }
  
  return buffer;
}