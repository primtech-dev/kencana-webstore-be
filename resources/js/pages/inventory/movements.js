import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

$(function () {
    const routes = window.inventoryRoutes;
    if (!routes || !routes.movements) return;

    const hideProduct = !!routes.hideProductColumn;
    const showBranch = !!routes.showBranch;

    const table = new DataTable('#movements-table', {
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: routes.movements,
            type: 'GET',
            cache: false,
            data: function (d) {
                d.variant_id = routes.variantId || '';
                d.batch_id = routes.batchId || '';
                d.branch_id = $('#mv_branch').val() || '';
                d.reason = $('#mv_reason').val() || '';
                d.date_from = $('#mv_date_from').val() || '';
                d.date_to = $('#mv_date_to').val() || '';
            },
            error: function (xhr) {
                console.error('Gagal memuat riwayat stok', xhr.responseText);
                if (window.toast) window.toast.error('Gagal memuat riwayat stok.');
            },
        },
        columns: [
            { data: 'created_at', name: 'created_at' },
            { data: 'branch_name', name: 'branch_name', visible: showBranch },
            { data: 'product', name: 'product', visible: !hideProduct },
            { data: 'change', name: 'change', className: 'text-end' },
            { data: 'resulting_on_hand', name: 'resulting_on_hand', className: 'text-end' },
            { data: 'reason', name: 'reason' },
            { data: 'reference', name: 'reference', orderable: false, searchable: false },
            { data: 'performer', name: 'performer', orderable: false },
            { data: 'note', name: 'note', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
    });

    $('#movementFilters select, #movementFilters input').on('change', () => table.ajax.reload());
    $('#mv_reset').on('click', () => {
        $('#movementFilters select').val('');
        $('#movementFilters input').val('');
        table.ajax.reload();
    });

    // Admin pusat: tombol "Lihat" pada tabel stok per cabang memilih cabang di filter riwayat
    $(document).on('click', '.js-pick-branch', function () {
        $('#mv_branch').val($(this).data('branch')).trigger('change');
        const el = document.getElementById('movements-table');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
