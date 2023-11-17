import $ from 'jquery';
$(function() {
  if ($('div.search-address').length > 0) {
    initSearchAddress();
  }
});

function initSearchAddress() {
  var idTimeoutInputAddress = null;
  var ajaxObject = null;

  $('input#rechercheAdresse').on('input', function() {
    if (idTimeoutInputAddress !== null) {
      clearTimeout(idTimeoutInputAddress);
    }
    if (ajaxObject !== null) {
      ajaxObject.abort();
    }

    $('#rechercheAdresseListe select').empty();
    $('#rechercheAdresseIcon .fr-icon-timer-line').show();
    $('#rechercheAdresseIcon .fr-icon-map-pin-2-line').hide();

    idTimeoutInputAddress = setTimeout(
      () => {
        let searchField = $(this).val();
        if ($('#code-postal').length > 0) {
          searchField += ' ' + $('#code-postal').val();
        }
        ajaxObject = $.ajax({
          url: 'https://api-adresse.data.gouv.fr/search/?q=' + searchField
        }).done(function(jsonData) {
          for (let feature of jsonData.features) {
            let adresseLabel = feature.properties.label;
            let adresseName = feature.properties.name;
            let adressePostCode = feature.properties.postcode;
            let adresseCity = feature.properties.city;
            let adresseCityCode = feature.properties.citycode;
            let adresseGeolocLat = feature.geometry.coordinates[1];
            let adresseGeolocLng = feature.geometry.coordinates[0];
            let elementData = '';
            elementData += ' data-label="'+adresseLabel+'"';
            elementData += ' data-name="'+adresseName+'"';
            elementData += ' data-postcode="'+adressePostCode+'"';
            elementData += ' data-city="'+adresseCity+'"';
            elementData += ' data-citycode="'+adresseCityCode+'"';
            elementData += ' data-geoloclat="'+adresseGeolocLat+'"';
            elementData += ' data-geoloclng="'+adresseGeolocLng+'"';
            $('#rechercheAdresseListe select').append( '<option '+elementData+' class="fr-mb-1v fr-p-1v">'+adresseLabel+'</option>' );
            $('#rechercheAdresseListe').show();
            $('#rechercheAdresseIcon .fr-icon-timer-line').hide();
            $('#rechercheAdresseIcon .fr-icon-map-pin-2-line').show();

            $('#rechercheAdresseListe select option').on('click', function() {
              let formPrefix = ($('#signalement_history_adresse').length > 0) ? 'signalement_history' : 'signalement_front';
              $('#rechercheAdresse').val($(this).data('label'));
              $('#' + formPrefix + '_adresse').val($(this).data('name'));
              $('#' + formPrefix + '_codePostal').val($(this).data('postcode'));
              $('#' + formPrefix + '_ville').val($(this).data('city'));
              $('#' + formPrefix + '_codeInsee').val($(this).data('citycode'));
              let geoloc = $(this).data('geoloclat')+'|'+ $(this).data('geoloclng');
              $('#' + formPrefix + '_geoloc').val(geoloc);
              $('#rechercheAdresseListe').hide();
              if ($('.address-fields').length > 0) {
                $('.address-fields').removeClass('fr-hidden');
                $('.skip-search-address').hide();
              }
            });
          }
        });
      },
      300
    );
  });

  $('#rechercheAdresseListe select').on('keypress', function(e){		  
    var code = e.keyCode || e.which;
    if (code == 32 && $('#rechercheAdresseListe').is(':visible')) {
      $('#rechercheAdresseListe select option:selected').trigger('click');
    }
  });

  $('a.skip-search-address').on('click', function(){
    if ($('.address-fields').length > 0) {
      $('.address-fields').removeClass('fr-hidden');
      $('.search-address').slideUp(200);
    }
  });

  $('#adresse_afficher_les_champs button').on('click', function () {
    $('#adresse_detail').toggleClass('fr-hidden');
    if ($('#adresse_afficher_les_champs button').hasClass('fr-icon-eye-line')) {
      $('#adresse_afficher_les_champs button').removeClass('fr-icon-eye-line');
      $('#adresse_afficher_les_champs button').addClass('fr-icon-eye-off-line');
    } else {
      $('#adresse_afficher_les_champs button').addClass('fr-icon-eye-line');
      $('#adresse_afficher_les_champs button').removeClass('fr-icon-eye-off-line');
    }
  });
}
