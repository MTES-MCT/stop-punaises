import $ from 'jquery';
$(function() {
  if ($('form.front-signalement').length > 0) {
    startCreerSignalementFrontApp();
  }
});

var frontSignalementStep = 1;

function startCreerSignalementFrontApp() {
  $('.btn-next').on('click', function(){
    refreshSignalementStep(1);
  });
  $('.link-back').on('click', function(){
    refreshSignalementStep(-1);
  });
}

function refreshSignalementStep(offset) {
  frontSignalementStep += offset;

  $('.current-step').slideUp(200, function() {
    $('.current-step').removeClass('current-step');
    $('#step-' + frontSignalementStep).addClass('current-step');
    $('#step-' + frontSignalementStep).slideDown(200);
  });
}