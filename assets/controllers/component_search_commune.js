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

        $('#rechercheAdresseListe').empty();

        idTimeoutInputCommune = setTimeout(
            () => {
                let searchField = $(this).val();

                ajaxObject = $.ajax({
                    url: 'https://api-adresse.data.gouv.fr/search/?q=' + searchField + '&type=municipality&limit10'
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
                        $('#rechercheAdresseListe').append( '<div '+elementData+' class="fr-mb-1v fr-p-1v">'+city+'</div>' );
                        $('#rechercheAdresseListe').show();

                        $('#rechercheAdresseListe div').on('click', function() {
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
}
