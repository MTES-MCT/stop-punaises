import $ from 'jquery';
$(function() {
  if ($('.form-back-stop-intervention').length > 0) {
    checkFormBackStopIntervention();
  }
});

function checkFormBackStopIntervention() {
    $('.form-back-stop-intervention').on('submit', function(e) {
        if (!confirm('Êtes-vous sûr(e) de vouloir annuler cette intervention ?')) {
          e.preventDefault()
        }
    });
}