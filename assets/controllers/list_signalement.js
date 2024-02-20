import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt';
import 'datatables.net-responsive-dt';

$(function() {
  if ($('div.liste-signalements').length > 0) {
    startListeSignalementsApp();
  }
  refreshTableWithSearch();
});

let listTable = null;
let searchTimeout = null;
function startListeSignalementsApp() {
  let options = {
    responsive: true,
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
  }
  let idTable = 'table#datatable'
  if ($('table#datatable-ajax').length > 0) {
    idTable += '-ajax';
    options.ajax = '/bo/liste-signalements';
    options.serverSide = true;
    // refresh count when ajax call is done
    options.fnDrawCallback = function( oSettings ) {
      $("span#count-signalement").text(generateTableTitleFromDatatable('signalement'));
      document.title = generatePageTitleFromDatatable('Les signalements usagers', 'signalement');
    }
  }
  listTable = $(idTable).DataTable(options);
  initComponentsEvents();

  $('#select-sort-table-by').on('change', function() {
    listTable.order([Number($('#select-sort-table-by').val()), 'asc'])
      .draw();
  })
}

function initComponentsEvents() {
  if ($('#search-free').length > 0) {
    $('#search-free').on('keyup', function() {
      refreshTableWithSearch();
    });
  }
  if ($('#search-address').length > 0) {
    $('#search-address').on('keyup', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        refreshTableWithSearch();
      }, 150);
    });
  }
  if ($('#filter-infestation').length > 0) {
    $('#filter-infestation').on('change', function() {
      refreshTableWithSearch();
    });
  }
  if ($('#filter-entreprise').length > 0) {
    $('#filter-entreprise').on('change', function() {
      refreshTableWithSearch();
    });
  }
  if ($('#filter-statut').length > 0) {
    $('#filter-statut').on('change', function() {
      refreshTableWithSearch();
    });
  }
  if ($('#filter-etat-infestation').length > 0) {
    $('#filter-etat-infestation').on('change', function() {
      refreshTableWithSearch();
    });
  }
  if ($('#filter-motif-cloture').length > 0) {
    $('#filter-motif-cloture').on('change', function() {
      refreshTableWithSearch();
    });
  }
  if ($('#filter-territoire').length > 0) {
    $('#filter-territoire').on('change', function() {
      refreshTableWithSearch();
    });
  }
  if ($('#filter-type').length > 0) {
    $('#filter-type').on('change', function() {
      refreshTableWithSearch();
    });
  }
  if ($('#filter-date').length > 0) {
    $('#filter-date').on('change', function() {
      refreshTableWithSearch();
    });
  }
  if ($('#filter-signalement-type').length > 0) {
    $('#filter-signalement-type').on('change', function() {
      refreshTableWithSearch();
    });
  }
}

function refreshTableWithSearch() {
  if ($('.liste-signalements-hors-perimetres').length > 0) {
    refreshTableHorsPerimetre();
    return;
  }
  if ($('.liste-signalements-usagers').length > 0) {
    refreshTableUsagers();
    return;
  }
  if ($('.liste-signalements-erp-transports').length > 0) {
    refreshTableErpTransports();
    return;
  }
  if ($('.liste-signalements-historique').length > 0) {
    refreshTableHistorique();
  }
}

function refreshTableHorsPerimetre() {
  if ($('#filter-territoire').length > 0) {
    let territoire = $('#filter-territoire').val();
    listTable.columns(2).search(territoire);
  }
  if ($('#filter-date').length > 0) {
    let dateInput = $('#filter-date').val();
    let dateFilter = '';
    if (dateInput != '') {
      let dateSplit = dateInput.split('-');
      dateFilter = dateSplit[2] + '/' + dateSplit[1] + '/' + dateSplit[0];
    }
    listTable.columns(1).search(dateFilter);
  }
  if ($('#search-address').length > 0) {
    let territoire = $('#search-address').val();
    listTable.columns(3).search(territoire);
  }
  listTable.draw();
  $("span#count-signalement").text(generateTableTitleFromDatatable('signalement'));
  document.title = generatePageTitleFromDatatable('Les signalements hors périmètre', 'signalement');
}

function refreshTableErpTransports() {
  if ($('#filter-date').length > 0) {
    let dateInput = $('#filter-date').val();
    let dateFilter = '';
    if (dateInput != '') {
      let dateSplit = dateInput.split('-');
      dateFilter = dateSplit[2] + '/' + dateSplit[1] + '/' + dateSplit[0];
    }
    listTable.columns(1).search(dateFilter);
  }
  if ($('#filter-signalement-type').length > 0) {
    let type = $('#filter-signalement-type').val();
    listTable.columns(2).search(type);
  }
  if ($('#search-address').length > 0) {
    let address = $('#search-address').val();
    listTable.columns(4).search(address);
  }
  if ($('#filter-territoire').length > 0) {
    let territoire = $('#filter-territoire').val().substring(0,2);
    listTable.columns(5).search(territoire);
  }
  listTable.draw();
  $("span#count-signalement").text(generateTableTitleFromDatatable('signalement'));
  document.title = generatePageTitleFromDatatable('Les signalements ERP et transports', 'signalement');
}

function refreshTableUsagers() {
  if ($('#filter-date').length > 0) {
    let dateInput = $('#filter-date').val();
    listTable.columns(2).search(dateInput);
  }
  if ($('#filter-infestation').length > 0) {
    let niveauInfestation = $('#filter-infestation').val();
    listTable.columns(3).search(niveauInfestation);
  }
  if ($('#search-address').length > 0) {
    let address = $('#search-address').val();
    listTable.columns(4).search(address);
  }
  if ($('#filter-type').length > 0) {
    let type = $('#filter-type').val();
    listTable.columns(5).search(type);
  }
  if ($('#filter-statut').length > 0) {
    let statut = $('#filter-statut').val();
    listTable.columns(0).search(statut);
  }

  if ($('#filter-territoire').length > 0) {
    let territoire = $('#filter-territoire').val();
    listTable.columns(1).search(territoire);
  }
  if ($('#filter-etat-infestation').length > 0) {
    let etatInfestation = $('#filter-etat-infestation').val();
    listTable.columns(6).search(etatInfestation);
  }
  if ($('#filter-motif-cloture').length > 0) {
    let motifCloture = $('#filter-motif-cloture').val();
    listTable.columns(7).search(motifCloture);
  }

  listTable.draw();
  $("span#count-signalement").text(generateTableTitleFromDatatable('signalement'));
  document.title = generatePageTitleFromDatatable('Les signalements usagers', 'signalement');
}

function refreshTableHistorique() {
  let indexColumnRef = 0;
  let indexColumnInfestation = 4;
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
  let niveauInfestation = $('#filter-infestation').val();
  listTable.columns(indexColumnInfestation).search(niveauInfestation);
  let typeSignalement = $('#filter-type').val();
  listTable.columns(indexColumnType).search(typeSignalement);
  listTable.draw();
  $("span#count-signalement").text(generateTableTitleFromDatatable('signalement'));
  document.title = generatePageTitleFromDatatable('Les données historiques', 'signalement');
}

function generatePageTitleFromDatatable(prefix, element) {
  let countElements = listTable.page.info().recordsDisplay;
  let plural = '';
  if(countElements > 1) {
    plural = 's';
  }
  let currentPage = (listTable.page.info().page) + 1;
  let totalPage = listTable.page.info().pages;
  
  return prefix +  ' - ' + countElements + ' ' + element + plural +'  trouvé' + plural + ' - page ' + currentPage + ' sur ' + totalPage + ' - Stop punaises';
}

function generateTableTitleFromDatatable(element) {
  let countElements = listTable.page.info().recordsDisplay;
  let plural = '';
  if(countElements > 1) {
    plural = 's';
  }
  
  return countElements + ' ' + element + plural +'  trouvé' + plural;
}
