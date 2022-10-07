import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt';

$(function() {
  if ($('div.liste-signalements').length > 0) {
    startListeSignalementsApp();
  }
});

function startListeSignalementsApp() {
  $('table#datatable').DataTable({
    searching: false,
    ordering: true,
    lengthChange: false,
    language: {
      emptyTable: "Aucune donnée à afficher",
      info: "Résultats _START_ - _END_ sur _TOTAL_",
      infoEmpty: "Résultats 0 - 0 sur 0",
      paginate: {
        first: "|&lt;",
        previous: "&lt;",
        next: "&gt;",
        last: "&gt;|"
      }
    }
  });
}