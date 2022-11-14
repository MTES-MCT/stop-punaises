import $ from 'jquery';
$(function() {
  if ($('form.file-auto-submit').length > 0) {
    initFileAutoSubmit();
  }
});

function initFileAutoSubmit() {
  $('form.file-auto-submit input.fr-upload').on('change', function() {
    $(this).parent().parent().trigger('submit');
  });
}