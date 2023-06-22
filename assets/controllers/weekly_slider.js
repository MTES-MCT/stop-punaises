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
      const selectedDateString = new Date(ui.value * 1000).toLocaleDateString('fr-FR')
      $("#weekly-slider-selection").text(selectedDateString)
      $("input[name=filter-date]").val(selectedDateString)
      getMarkers(0);
    },
    create: function(event, ui){
        $(this).slider('value', currentDateMs )
        const selectedDateString = new Date(currentDateMs * 1000).toLocaleDateString('fr-FR')
        $("#weekly-slider-selection").text(selectedDateString)
        $("input[name=filter-date]").val(selectedDateString)
        getMarkers(0);
    }
  })
} );