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
      let inputDiv = $('.fr-upload-group .fr-upload');
      if (file.size > 10 * 1024 * 1024) {
          errorDiv.text('Le fichier est trop lourd. Merci d\'ajouter une photo de moins de 10 Mo.').removeClass('fr-hidden');
          inputDiv.attr('aria-describedby', 'file-upload-error');
          break;
      } else if(file.type !== 'image/jpeg' && file.type !== 'image/png') {
          errorDiv.text('Format de fichier non supporté. Merci de choisir un fichier au format jpg ou png.').removeClass('fr-hidden');
          inputDiv.attr('aria-describedby', 'file-upload-error');
          break;
      } else {
          $(this).parent().parent().trigger('submit');
      }
    }

  });
}