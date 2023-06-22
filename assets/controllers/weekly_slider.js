require('jquery-ui/ui/widgets/slider');
import $ from 'jquery';

$( function() {
  const startDate = new Date(2023, 0, 2)
  const currentDate = new Date()
  $("#weekly-slider").slider({
    min: startDate.getTime() / 1000,
    max: currentDate.getTime() / 1000,
    step: 86400*7,
    slide: function( event, ui ) {
      const selectedDateString = new Date(ui.value * 1000).toLocaleDateString('fr-FR')
      $("#weekly-slider-selection").text(selectedDateString)
      $("input[name=filter-date]").val(selectedDateString)
    }
  })
  const selectedDateString = startDate.toLocaleDateString('fr-FR')
  $("#weekly-slider-selection").text(selectedDateString)
  $("input[name=filter-date]").val(selectedDateString)
} );