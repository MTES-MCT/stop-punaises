import $ from 'jquery';
$(function() {
  if ($('div.search-address').length > 0) {
    initSearchAddress();
  }
});

function initSearchAddress() {
  var idTimeoutInputAddress = null;
  var ajaxObject = null;
  var selectedSuggestionIndex = -1;
  var isAutocompleteOpen = false;

  $(document).on('click', function(event) {
    if (!$(event.target).closest('.fr-autocomplete-list').length && isAutocompleteOpen) {
      closeSuggestions();
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
        let searchField = $(this).val();
        if ($('#code-postal').length > 0) {
          searchField += ' ' + $('#code-postal').val();
        }
        ajaxObject = $.ajax({
          url: 'https://data.geopf.fr/geocodage/search/?q=' + searchField
        }).done(function(jsonData) {
          let ariaPosinset = 1;
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

            const setSize = jsonData.features.length;
            $('#rechercheAdresseListe')
                .append(
                    '<li '
                    + elementData
                    + ' class="fr-col-12 fr-p-3v fr-text-label--blue-france fr-autocomplete-suggestion"'
                    + ' role="option" tabindex="-1"'
                    + ' aria-selected="false"'
                    + ' aria-posinset="' + ariaPosinset + '"'
                    + ' aria-setsize="' + setSize + '"'
                    + '>'
                    + adresseLabel
                    + '</li>');

            ++ariaPosinset;

            $('#rechercheAdresseListe').show();
            $('#rechercheAdresseIcon .fr-icon-timer-line').hide();
            $('#rechercheAdresseIcon .fr-icon-map-pin-2-line').show();

            const OFFSET = 200;
            if ($('#rechercheAdresseListe').offset().top + OFFSET > $(window).scrollTop() + window.innerHeight) {
              scrollTo({
                top: $(window).scrollTop() + OFFSET,
                behavior: "smooth"
              });
            }

            $('#rechercheAdresseListe li').on('click', function() {
              let formPrefix = ($('#signalement_history_adresse').length > 0)
                  ? 'signalement_history'
                  : 'signalement_front';
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
          isAutocompleteOpen = true;
        });
      },
      300
    );
  });


  $('input#rechercheAdresse').on('keydown', function (event) {
    if (event.key === 'ArrowDown') {
      event.preventDefault();
      handleDown();
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      handleUp();
    } else if (event.key === 'Enter') {
      event.preventDefault();
      handleEnter();
    } else if (event.key === 'Tab') {
      closeSuggestions();
  }
  });

  $('#toggle-skip-search-address').on('click', function(){
    if ($('.address-fields').length > 0) {
      if ($('#toggle-skip-search-address').is(':checked')) {
        $('.address-fields').removeClass('fr-hidden');
        $('.search-address').slideUp(200);
      } else {
        $('.address-fields').addClass('fr-hidden');
        $('.search-address').slideDown(200);
      }
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

  function handleDown() {
    const suggestions = $('.fr-autocomplete-suggestion');
    if (selectedSuggestionIndex < suggestions.length - 1) {
      selectedSuggestionIndex++;
      updateSelectedSuggestion();
    }
  }

  function handleUp() {
    if (selectedSuggestionIndex > 0) {
      selectedSuggestionIndex--;
      updateSelectedSuggestion();
    }
  }

  function updateSelectedSuggestion() {
    const suggestions = $('.fr-autocomplete-suggestion');
    suggestions.each(function(index, suggestion) {
      if (index === selectedSuggestionIndex) {
        $(suggestion)
            .addClass('fr-autocomplete-suggestion-highlighted')
            .attr('aria-selected', 'true');
      } else {
        $(suggestion)
            .removeClass('fr-autocomplete-suggestion-highlighted')
            .attr('aria-selected', 'false');
      }
    });
  }

  function handleEnter() {
    const suggestions = $('.fr-autocomplete-suggestion');
    if (selectedSuggestionIndex !== -1) {
      let formPrefix = ($('#signalement_history_adresse').length > 0)
          ? 'signalement_history'
          : 'signalement_front';

      $('#rechercheAdresse').val(suggestions.eq(selectedSuggestionIndex).data('label'));
      $('#' + formPrefix + '_adresse').val(suggestions.eq(selectedSuggestionIndex).data('name'));
      $('#' + formPrefix + '_codePostal').val(suggestions.eq(selectedSuggestionIndex).data('postcode'));
      $('#' + formPrefix + '_ville').val(suggestions.eq(selectedSuggestionIndex).data('city'));
      $('#' + formPrefix + '_codeInsee').val(suggestions.eq(selectedSuggestionIndex).data('citycode'));
      let geoloc = suggestions.eq(selectedSuggestionIndex).data('geoloclat')
          + '|'
          + suggestions.eq(selectedSuggestionIndex).data('geoloclng');

      $('#' + formPrefix + '_geoloc').val(geoloc);

      $('#rechercheAdresseListe').hide();
      if ($('.address-fields').length > 0) {
        $('.address-fields').removeClass('fr-hidden');
        $('.skip-search-address').hide();
      }

      closeSuggestions();
    }
  }

  function closeSuggestions() {
    $('.fr-autocomplete-list').html('');
    selectedSuggestionIndex = -1;
    isAutocompleteOpen = false;
  }
}
