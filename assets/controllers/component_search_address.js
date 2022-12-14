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

    $('#rechercheAdresseListe').empty();
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
              let formPrefix = ($('#signalement_adresse').length > 0) ? 'signalement' : 'signalement_front';
              $('#' + formPrefix + '_adresse').val($(this).data('name'));
              $('#' + formPrefix + '_codePostal').val($(this).data('postcode'));
              $('#' + formPrefix + '_ville').val($(this).data('city'));
              $('#' + formPrefix + '_codeInsee').val($(this).data('citycode'));
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

  $('a.skip-search-address').on('click', function(){
    if ($('.address-fields').length > 0) {
      $('.address-fields').removeClass('fr-hidden');
      $('.search-address').slideUp(200);
    }
  });
}