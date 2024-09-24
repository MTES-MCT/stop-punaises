L = window.L;

const sudOuest = L.latLng(58, -5);
const nordEst = L.latLng(41, 10);

const bounds = L.latLngBounds(sudOuest, nordEst);
var map = L.map('map-signalements-view', {
    center: [47.11, -0.01],
    maxBounds: bounds,
    minZoom: 5,
    maxZoom: 18,
    zoom: 6
});
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {crossOrigin: true}).addTo(map);
var heat;

async function getMarkers(offset) {
    const response = await fetch('?load_markers=true&offset=' + offset, {
        headers: {
            'X-TOKEN': document.querySelector('#carto__js').getAttribute('data-token')
        },
        method: 'POST',
        body: new FormData(document.querySelector('form#bo_carto_filter'))
    });
    const result = await response.json();
    if (heat){
        heat.remove();
    }
    if (result.signalements) {  
        const heatValues = [];
        result.signalements.forEach(signalement => {      
            if (!isNaN(parseFloat(signalement.geoloc?.lng)) && !isNaN(parseFloat(signalement.geoloc?.lat))) {  
                // pour l'instant on ne prend pas en compte le niveauInfestation
                // on n'affiche pas les signalements au statut resolved
                if ('trace' === signalement.statut){
                    heatValues.push([signalement.geoloc.lat, signalement.geoloc.lng, 0.5]);
                }  
                if ('en cours' === signalement.statut){
                    heatValues.push([signalement.geoloc.lat, signalement.geoloc.lng, 1]);
                }         
            }
        })
        heat = L.heatLayer(heatValues, {maxZoom:15}).addTo(map);
    } else {
        alert('Erreur lors du chargement des signalements...')
    }
}
