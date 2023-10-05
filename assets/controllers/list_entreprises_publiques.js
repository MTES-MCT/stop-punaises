import $ from 'jquery';

$(function() {
  if ($('select#select-entreprises-order').length > 0) {
    startListeEntreprisesPubliquesApp();
  }
});

function startListeEntreprisesPubliquesApp() {
  $('select#select-entreprises-order').on('change', function() {
    window.location.href = $('select#select-entreprises-order').data('redirect') + '&order=' + $('select#select-entreprises-order').val()
  });
}