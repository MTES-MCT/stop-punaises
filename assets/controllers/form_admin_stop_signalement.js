import $ from 'jquery';
$(function() {
  if ($('.form-back-admin-close-signalement').length > 0) {
    checkFormAdminStopSignalement();
  }
});

function checkFormAdminStopSignalement() {
    $('.form-back-admin-close-signalement').on('submit', function(e) {
        if (!confirm('Êtes-vous sûr(e) de vouloir fermer ce signalement ?')) {
          e.preventDefault()
        }
    });
}