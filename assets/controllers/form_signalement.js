import $ from 'jquery';
$(function() {
  if ($('div.creer-signalement').length > 0) {
    startCreerSignalementApp();
  }
});

function startCreerSignalementApp() {
  var idTimeoutInputAddress = null;
  var ajaxObject = null;

  // Navigation
  $('nav.stepper-next a').on('click', function() {
    $('.fr-stepper__state span').text('2');
    $('.fr-stepper__title span[data-step=1]').addClass('fr-stepper__hidden');
    $('.fr-stepper__title span[data-step=2]').removeClass('fr-stepper__hidden');
    $('.fr-stepper__steps').attr('data-fr-current-step', '2');
    $('#form-creer-signalement-step-1').addClass('fr-stepper__hidden');
    $('#form-creer-signalement-step-2').removeClass('fr-stepper__hidden');
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

  $('input#rechercheAdresse').on('input', function() {
    if (idTimeoutInputAddress !== null) {
      clearTimeout(idTimeoutInputAddress);
    }
    if (ajaxObject !== null) {
      ajaxObject.abort();
    }

    $('#rechercheAdresseListe').empty();
    $('#rechercheAdresseIcon .fr-icon-timer-line').show();
    $('#rechercheAdresseIcon .fr-icon-map-pin-2-line').hide();

    idTimeoutInputAddress = setTimeout(
      () => {
        ajaxObject = $.ajax({
          url: 'https://api-adresse.data.gouv.fr/search/?q=' + $(this).val()
        }).done(function(jsonData) {
          for (let feature of jsonData.features) {
            let adresseLabel = feature.properties.label;
            let adresseName = feature.properties.name;
            let adressePostCode = feature.properties.postcode;
            let adresseCity = feature.properties.city;
            let adresseCityCode = feature.properties.citycode;
            let elementData = '';
            elementData += ' data-name="'+adresseName+'"';
            elementData += ' data-postcode="'+adressePostCode+'"';
            elementData += ' data-city="'+adresseCity+'"';
            elementData += ' data-citycode="'+adresseCityCode+'"';
            $('#rechercheAdresseListe').append( '<div '+elementData+' class="fr-mb-1v fr-p-1v">'+adresseLabel+'</div>' );
            $('#rechercheAdresseListe').show();
            $('#rechercheAdresseIcon .fr-icon-timer-line').hide();
            $('#rechercheAdresseIcon .fr-icon-map-pin-2-line').show();

            $('#rechercheAdresseListe div').on('click', function() {
              $('#signalement_adresse').val($(this).data('name'));
              $('#signalement_codePostal').val($(this).data('postcode'));
              $('#signalement_ville').val($(this).data('city'));
              $('#signalement_codeInsee').val($(this).data('citycode'));
              $('#rechercheAdresseListe').hide();
            });
          }
        });
      },
      300
    );
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

  $('select#signalement_typeTraitement').on('change', function() {
    if ($(this).val() == 'biocide') {
      $('.display-if-biocide').show();
    } else {
      $('.display-if-biocide').hide();
    }
  });
    
}