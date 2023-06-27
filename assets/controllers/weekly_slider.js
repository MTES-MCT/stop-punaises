require('jquery-ui/ui/widgets/slider');
import $ from 'jquery';

$( function() {
  const startDate = new Date(2021, 0, 2)
  const currentDate = new Date()
  const currentDateMs = currentDate.getTime() / 1000
  $("#weekly-slider").slider({
    min: startDate.getTime() / 1000,
    max: currentDateMs,
    step: 86400*7,
    slide: function( event, ui ) {
      refreshSliderValue(ui.value, false)
    },
    change: function( event, ui ) {
      refreshSliderValue(ui.value, true)
    },
    create: function(event, ui){
        $(this).slider('value', currentDateMs )
        refreshSliderValue(currentDateMs, true)
    }
  })
  function refreshSliderValue(dateMs, refreshHeatMap){
    const selectedDateString = new Date(dateMs * 1000).toLocaleDateString('fr-FR')
    $("#weekly-slider-selection").text(selectedDateString)
    $("input[name=filter-date]").val(selectedDateString)
    if (refreshHeatMap){
      getMarkers(0)
    }
  }
} );