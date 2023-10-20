import $ from 'jquery';

$(function() {
  if ($('select#select-entreprises-order').length > 0) {
    startListeEntreprisesPubliquesApp();
  }
  if ($('select#select-entreprises-filter').length > 0) {
    startListeEntreprisesPubliquesApp();
  }
});

function startListeEntreprisesPubliquesApp() {
  $('select#select-entreprises-order').on('change', function() {
    window.location.href = $('select#select-entreprises-order').data('redirect') + '&order=' + $('select#select-entreprises-order').val() + '&filter=' + $('select#select-entreprises-filter').val()
  });
  $('select#select-entreprises-filter').on('change', function() {
    window.location.href = $('select#select-entreprises-filter').data('redirect') + '&filter=' + $('select#select-entreprises-filter').val() + '&order=' + $('select#select-entreprises-order').val()
  });
}