import $ from 'jquery';

$(function() {
    if ($('div.search-commune').length > 0) {
        initSearchCommune();
    }
});

function initSearchCommune() {
    var idTimeoutInputCommune = null;
    var ajaxObject = null;

    $('input#signalement_transport_ville').on('input', function() {
        if (idTimeoutInputCommune !== null) {
            clearTimeout(idTimeoutInputCommune);
        }
        if (ajaxObject !== null) {
            ajaxObject.abort();
        }

        $('#rechercheAdresseListe select').empty();

        idTimeoutInputCommune = setTimeout(
            () => {
                let searchField = $(this).val();

                ajaxObject = $.ajax({
                    url: 'https://data.geopf.fr/geocodage/search/?q=' + searchField + '&type=municipality&limit10'
                }).done(function(jsonData) {
                    for (let feature of jsonData.features) {
                        let postCode = feature.properties.postcode;
                        let city = feature.properties.city;
                        let cityCode = feature.properties.citycode;
                        let geolocLat = feature.geometry.coordinates[1];
                        let geolocLng = feature.geometry.coordinates[0];
                        let elementData = '';
                        elementData += ' data-postcode="'+postCode+'"';
                        elementData += ' data-city="'+city+'"';
                        elementData += ' data-citycode="'+cityCode+'"';
                        elementData += ' data-geoloclat="'+geolocLat+'"';
                        elementData += ' data-geoloclng="'+geolocLng+'"';
                        $('#rechercheAdresseListe select').append( '<option '+elementData+' class="fr-mb-1v fr-p-1v">'+city+'</option>' );
                        $('#rechercheAdresseListe').show();
                        
                        const OFFSET = 200;
                        if ($('#rechercheAdresseListe').offset().top + OFFSET > $(window).scrollTop() + window.innerHeight) {
                          scrollTo({
                            top: $(window).scrollTop() + OFFSET,
                            behavior: "smooth"
                          });
                        }

                        $('#rechercheAdresseListe select option').on('click', function() {
                            $('#signalement_transport_ville').val($(this).data('city'));
                            $('#signalement_transport_codePostal').val($(this).data('postcode'));
                            $('#signalement_transport_codeInsee').val($(this).data('citycode'));
                            let geoloc = $(this).data('geoloclat')+'|'+ $(this).data('geoloclng');
                            $('#signalement_transport_geoloc').val(geoloc);
                            $('#rechercheAdresseListe').hide();
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
}
