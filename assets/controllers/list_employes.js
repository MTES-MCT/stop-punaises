import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt';
import 'datatables.net-responsive-dt';

$(function() {
  if ($('div.sublist-employes').length > 0) {
    startListeEmployesApp();
  }
});

var listTable = null;
function startListeEmployesApp() {
  listTable = $('table#datatable').DataTable({    
    responsive: true,
    pageLength: 20,
    searching: false,
    ordering: true,
    lengthChange: false,
    language: {
      emptyTable: "Aucune donnée à afficher",
      info: "Résultats _START_ - _END_ sur _TOTAL_",
      infoEmpty: "Résultats 0 - 0 sur 0",
      infoFiltered: "(sur un total de _MAX_)",
      zeroRecords: "Aucun employé trouvé",
      paginate: {
        first: "|&lt;",
        previous: "&lt; Page précédente",
        next: "Page suivante &gt;",
        last: "&gt;|"
      }
    },
    drawCallback: function(settings, json) {
      $('#datatable_paginate').attr('role', 'navigation');
      $('#datatable_paginate').attr('aria-label', 'Pagination');
      $('#datatable_previous').attr('title', 'Page précédente');
      $('#datatable_next').attr('title', 'Page suivante');
      $('.paginate_button').each(function(index, element) {
        if ($(element).text().indexOf('Page') == -1) {
          $(element).attr('title', 'Page ' + index)
        }
      })
    }
  });

  $('#select-sort-table-by').on('change', function() {
    listTable.order([Number($('#select-sort-table-by').val()), 'asc'])
      .draw();
  })
}