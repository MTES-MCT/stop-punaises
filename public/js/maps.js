L = window.L;

const sudOuest = L.latLng(8, -80);
const nordEst = L.latLng(70, 20);
const bounds = L.latLngBounds(sudOuest, nordEst);
var map = L.map('map-signalements-view', {
    center: [47.11, -0.01],
    maxBounds: bounds,
    minZoom: 5,
    maxZoom: 18,
    zoom: 5
});
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);

const popupTemplate = (options) => {
    let TEMPLATE = `<div class="fr-grid-row" style="width: 500px">
                        <div class="fr-col-8">
                            <a href="${options.url}" class="fr-badge fr-badge--${options.type} fr-mt-1v fr-mb-0">#${options.reference}</a>
                            <p><strong>${options.name}</strong><br>
                            <small>
                            ${options.address} <br>
                            ${options.zip} ${options.city}</small></p>
                        </div>
                        <div class="fr-col-4 fr-mt-1v fr-mb-0 fr-text--center"></div>
                        </div>`;
    return TEMPLATE;
}
const MAP_MARKERS_PAGE_SIZE = 9000; // @todo: is high cause duplicate result, the query findAllWithGeoData should be reviewed

async function getMarkers(offset) {
    await fetch('?load_markers=true&offset=' + offset, {
        headers: {
            'X-TOKEN': document.querySelector('#carto__js').getAttribute('data-token')
        },
        method: 'POST',
        // body: new FormData(document.querySelector('form#bo_filters_form'))
    }).then(r => r.json().then(res => {
        // let marker;
        if (res.signalements) {

            var heatValues = new Array();
            res.signalements.forEach(signalement => {
                if (!isNaN(parseFloat(signalement.geoloc?.lng)) && !isNaN(parseFloat(signalement.geoloc?.lat))) {                    
                    heatValues.push([signalement.geoloc.lat, signalement.geoloc.lng, 10]);
                }
            })

            var heat = L.heatLayer(heatValues, {radius: 25}).addTo(map);
        } else {
            alert('Erreur lors du chargement des signalements...')
        }
    }))
}

window.onload = async () => {
    await getMarkers(0);
}