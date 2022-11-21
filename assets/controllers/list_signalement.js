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
    pageLength: 20,
    searching: true,
    ordering: true,
    lengthChange: false,
    language: {
      emptyTable: "Aucune donnée à afficher",
      info: "Résultats _START_ - _END_ sur _TOTAL_",
      infoEmpty: "Résultats 0 - 0 sur 0",
      infoFiltered: "(sur un total de _MAX_)",
      zeroRecords: "Aucun signalement trouvé",
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
  if ($('#filter-entreprise').length > 0) {
    $('#filter-entreprise').on('change', function() {
      refreshTableWithSearch();
    });
  }
  $('#filter-type').on('change', function() {
    refreshTableWithSearch();
  });
}

function refreshTableWithSearch() {
  let indexColumnRef = 0;
  let indexColumnInfectation = 4;
  let indexColumnAddress = 5;
  let indexColumnType = 6;
  if ($('#filter-entreprise').length > 0) {
    let entreprise = $('#filter-entreprise').val();
    listTable.columns(5).search(entreprise);
    indexColumnAddress = 6;
    indexColumnType = 7;
  }
  let searchText = $('#search-free').val();
  listTable.columns(indexColumnRef).search(searchText);
  let searchAddress = $('#search-address').val();
  listTable.columns(indexColumnAddress).search(searchAddress);
  let niveauInfectation = $('#filter-infectation').val();
  listTable.columns(indexColumnInfectation).search(niveauInfectation);
  let typeSignalement = $('#filter-type').val();
  listTable.columns(indexColumnType).search(typeSignalement);
  listTable.draw();
  let countSignalement = listTable.rows( {search:'applied'} ).count();
  $("span#count-signalement").text(countSignalement);
}


