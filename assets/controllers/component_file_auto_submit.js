import $ from 'jquery';
$(function() {
  if ($('form.file-auto-submit').length > 0) {
    initFileAutoSubmit();
  }
});

function initFileAutoSubmit() {
  $('form.file-auto-submit input.fr-upload').on('change', function(event) {
    for (let file of event.target.files) {
      let errorDiv = $('.fr-upload-group .fr-error-text');
      if (file.size > 10 * 1024 * 1024) {
          errorDiv.text('Merci d\'ajouter une photo de moins de 10 Mo.')
          errorDiv.removeClass('fr-hidden');
          break;
      } else if(file.type !== 'image/jpeg' && file.type !== 'image/png') {
          errorDiv.text('Merci de choisir un fichier au format jpg ou png.')
          errorDiv.removeClass('fr-hidden');
          break;
      } else {
          $(this).parent().parent().trigger('submit');
      }
    }

  });
}