import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt';

$(function() {
  if ($('div.liste-entreprises').length > 0) {
    startListeEntreprisesApp();
  }
});

var listTable = null;
function startListeEntreprisesApp() {
  listTable = $('table#datatable').DataTable({
    searching: true,
    ordering: true,
    lengthChange: false,
    language: {
      emptyTable: "Aucune donnée à afficher",
      info: "Résultats _START_ - _END_ sur _TOTAL_",
      infoEmpty: "Résultats 0 - 0 sur 0",
      infoFiltered: "(sur un total de _MAX_)",
      zeroRecords: "Aucune entreprise trouvée",
      paginate: {
        first: "|&lt;",
        previous: "&lt;",
        next: "&gt;",
        last: "&gt;|"
      }
    }
  });

  $('#search-free').on('keyup', function() {
    refreshTableWithSearch();
  });
}

function refreshTableWithSearch() {
  let searchText = $('#search-free').val();
  listTable.columns(1).search(searchText);
  listTable.draw();
}