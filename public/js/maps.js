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

map.on('zoomend', async function() {
    const zoomLevel = map.getZoom();
    await getMarkers();
});

map.on('moveend', async function() {
    await getMarkers();
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {crossOrigin: true}).addTo(map);
let abortController;
var heat;

async function getMarkers() {
    if (abortController) {
        abortController.abort();
    }
    abortController = new AbortController();

    try {
        const bounds = map.getBounds();
        const southWest = bounds.getSouthWest();
        const northEast = bounds.getNorthEast();
        const formData = new FormData(document.querySelector('form#bo_carto_filter'));
        formData.append('swLat', southWest.lat);
        formData.append('swLng', southWest.lng);
        formData.append('neLat', northEast.lat);
        formData.append('neLng', northEast.lng);

        const response = await fetch('?load_markers=true', {
            headers: {
                'X-TOKEN': document.querySelector('#carto__js').getAttribute('data-token')
            },
            method: 'POST',
            body: formData,
            signal: abortController.signal // Passer le signal d'annulation
        });
        const result = await response.json();
        if (heat){
            heat.remove();
        }
        if (result.signalements) {  
            const heatValues = [];
            result.signalements.forEach(signalement => {     
                const geoloc = JSON.parse(signalement.geoloc);  
                if (!isNaN(parseFloat(geoloc?.lng)) && !isNaN(parseFloat(geoloc?.lat))) {  
                    // pour l'instant on ne prend pas en compte le niveauInfestation
                    // on n'affiche pas les signalements au statut resolved
                    if ('trace' === signalement.statut){
                        heatValues.push([geoloc.lat, geoloc.lng, 0.5]);
                    }  
                    if ('en cours' === signalement.statut){
                        heatValues.push([geoloc.lat, geoloc.lng, 1]);
                    }         
                }
            })
            heat = L.heatLayer(heatValues, {maxZoom:15}).addTo(map);
        } else {
            alert('Erreur lors du chargement des signalements...')
        }
    } catch (error) {
        if (error.name === 'AbortError') {
            console.log('Requête annulée');
        } else {
            console.error('Erreur lors de l\'analyse du JSON:', error);
        }
    }
}
