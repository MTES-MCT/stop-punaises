import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt';
import 'datatables.net-responsive-dt';

$(function() {
  if ($('div.liste-entreprises').length > 0) {
    startListeEntreprisesApp();
    refreshTableWithSearch();
  }
});

var listTable = null;
function startListeEntreprisesApp() {
  listTable = $('table#datatable').DataTable({
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
      zeroRecords: "Aucune entreprise trouvée",
      search: 'Rechercher',
      paginate: {
        first: "|&lt;",
        previous: "Page précédente",
        next: "Page suivante",
        last: "&gt;|"
      },
      aria: {
        sortAscending: ' - activez pour trier dans un ordre croissant',
        sortDescending: ' - activez pour trier dans un ordre décroissant'
      }
    },
    drawCallback: function(settings, json) {
      $('#datatable_paginate').attr('role', 'navigation');
      $('#datatable_paginate').attr('aria-label', 'Pagination');
      $('#datatable_previous')
          .attr('title', 'Page précédente')
          .addClass('fr-pagination__link fr-pagination__link--prev fr-pagination__link--lg-label');

      $('#datatable_next')
          .attr('title', 'Page suivante')
          .addClass('fr-pagination__link fr-pagination__link--next fr-pagination__link--lg-label');

      $('a.paginate_button').each(function(index, element) {
        $(element).attr('href', '#')
        if ($(element).text().indexOf('Page') == -1) {
          $(element).attr('title', 'Page ' + index)
        }
      })
      $("a.paginate_button").on("click", function(e){
        e.preventDefault();
      })
    }
  });

  $('#search-free').on('keyup', function() {
    refreshTableWithSearch();
  });

  listTable.on('draw', function() {
    $("span#count-entreprise").text(generateTableTitleFromDatatable('entreprise'));
    $("caption#count-entreprise-caption").text(generateTableTitleFromDatatable('entreprise'));
    document.title = generatePageTitleFromDatatable('Les entreprises partenaires', 'entreprise');
  })
  
  $('#select-sort-table-by').on('change', function() {
    listTable.order([Number($('#select-sort-table-by').val()), 'asc'])
      .draw();
  })
}

function refreshTableWithSearch() {
  let searchText = $('#search-free').val();
  listTable.columns(1).search(searchText);
  listTable.draw();
}

function generatePageTitleFromDatatable(prefix, element) {
  let countElements = listTable.page.info().recordsDisplay;
  let plural = '';
  if(countElements > 1) {
    plural = 's';
  }
  let currentPage = (listTable.page.info().page) + 1;
  let totalPage = listTable.page.info().pages;
  return prefix +  ' - ' + countElements + ' ' + element + plural +'  trouvée' + plural + ' - page ' + currentPage + ' sur ' + totalPage + ' - Stop punaises';
}

function generateTableTitleFromDatatable(element) {
  let countElements = listTable.page.info().recordsDisplay;
  let plural = '';
  if(countElements > 1) {
    plural = 's';
  }
  
  return countElements + ' ' + element + plural +'  trouvée' + plural;
}
