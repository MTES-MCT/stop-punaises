import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt';

$(function() {
  if ($('div.liste-signalements').length > 0) {
    startListeSignalementsApp();
  }
});

var listTable = null;
function startListeSignalementsApp() {
  listTable = $('table#datatable').DataTable({
    searching: true,
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

  $('#search-free').on('keyup', function() {
    refreshTableWithSearch();
  });
  $('#search-address').on('keyup', function() {
    refreshTableWithSearch();
  });
  $('#filter-infectation').on('change', function() {
    refreshTableWithSearch();
  });
}

function refreshTableWithSearch() {
  let searchText = $('#search-free').val();
  listTable.columns(0).search(searchText);
  let searchAddress = $('#search-address').val();
  listTable.columns(5).search(searchAddress);
  let niveauInfectation = $('#filter-infectation').val();
  listTable.columns(4).search(niveauInfectation);
  listTable.draw();
}