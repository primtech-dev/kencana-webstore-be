import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import { initTooltips } from '../../utils/tooltip-helper';

$(function () {
    const routes = window.inventoryRoutes;
    if (!routes || !routes.index) return;

    const showBranch = !!routes.showBranch;

    const columns = [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'branch_name', name: 'branch_name', visible: showBranch },
        { data: 'sku', name: 'sku' },
        { data: 'product', name: 'product' },
        { data: 'unit_name', name: 'unit_name', orderable: false, defaultContent: '-' },
        { data: 'on_hand', name: 'on_hand', className: 'text-end' },
        { data: 'reserved', name: 'reserved', className: 'text-end' },
        { data: 'available', name: 'available', className: 'text-end' },
        { data: 'updated_at', name: 'updated_at' },
        { data: 'action', orderable: false, searchable: false, className: 'text-center' },
    ];

    const table = new DataTable('#stock-table', {
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: routes.index,
            type: 'GET',
            cache: false,
            data: function (d) {
                d.branch_id = $('#st_branch').val() || '';
                d.status = $('#st_status').val() || '';
            },
            error: function (xhr) {
                console.error('Gagal memuat stok', xhr.responseText);
                if (window.toast) window.toast.error('Gagal memuat data stok.');
            },
        },
        columns,
        order: [[8, 'desc']],
        drawCallback: function () {
            initTooltips(document.querySelector('#stock-table'));
        },
    });

    $('#stockFilters select').on('change', () => table.ajax.reload());
    $('#st_reset').on('click', () => {
        $('#stockFilters select').val('');
        table.ajax.reload();
    });
});
