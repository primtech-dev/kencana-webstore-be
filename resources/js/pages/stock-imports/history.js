import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

$(function () {
    const routes = window.importRoutes;
    if (!routes || !routes.history) return;

    const table = new DataTable('#imports-table', {
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: routes.history,
            type: 'GET',
            cache: false,
            data: function (d) {
                d.source = $('#im_source').val() || '';
                d.date_from = $('#im_date_from').val() || '';
                d.date_to = $('#im_date_to').val() || '';
            },
            error: function (xhr) {
                console.error('Gagal memuat riwayat import', xhr.responseText);
                if (window.toast) window.toast.error('Gagal memuat riwayat import.');
            },
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at', responsivePriority: 2 },
            { data: 'user', name: 'user', orderable: false, searchable: false },
            { data: 'source', name: 'source', responsivePriority: 3 },
            { data: 'file_name', name: 'file_name', defaultContent: '-' },
            { data: 'branch_list', name: 'branch_list', orderable: false, searchable: false },
            { data: 'rows_changed', name: 'rows_changed', className: 'text-end' },
            { data: 'rows_zeroed', name: 'rows_zeroed', className: 'text-end' },
            { data: 'rows_skipped', name: 'rows_skipped', className: 'text-end' },
            { data: 'action', orderable: false, searchable: false, className: 'text-center', responsivePriority: 1 },
        ],
        order: [[1, 'desc']],
    });

    $('#importFilters select, #importFilters input').on('change', () => table.ajax.reload());
    $('#im_reset').on('click', () => {
        $('#importFilters select').val('');
        $('#importFilters input').val('');
        table.ajax.reload();
    });
});
