import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import { showDeleteModal } from '../../../utils/delete-modal-helper';
import { initTooltips } from '../../../utils/tooltip-helper';

function btnDelete(id, name) {
    showDeleteModal({
        modalId: 'deleteProductModal',
        formId: 'deleteProductForm',
        itemNameId: 'delete_product_title',
        id: id,
        name: name,
        route: window.productRoutes.destroy
    });
}

window.btnDelete = btnDelete;

$(function () {
    new DataTable('#products-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: window.productRoutes.index,
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
            },
            {
                data: 'name',
                name: 'name',
                render: function (data) {
                    if (!data) {
                        return '<span class="text-muted fst-italic">Tidak ada nama</span>';
                    }
                    if (data.length > 60) {
                        return `<span data-bs-toggle="tooltip" title="${data}">${data.substring(0, 60)}...</span>`;
                    }
                    return data;
                }
            },
            {
                data: 'slug',
                name: 'slug',
                render: function (data) {
                    if (!data) return '<span class="text-muted fst-italic">Tidak ada slug</span>';
                    if (data.length > 40) {
                        return `<span data-bs-toggle="tooltip" title="${data}">${data.substring(0, 40)}...</span>`;
                    }
                    return data;
                }
            },
            {
                data: 'is_active',
                name: 'is_active',
                render: function (data) {
                    return data
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-danger">Nonaktif</span>';
                }
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'text-center'
            }
        ],
        language: {
            paginate: {
                first: '<i class="ti ti-chevrons-left"></i>',
                previous: '<i class="ti ti-chevron-left"></i>',
                next: '<i class="ti ti-chevron-right"></i>',
                last: '<i class="ti ti-chevrons-right"></i>'
            },
            lengthMenu: '_MENU_ product per halaman',
            info: 'Menampilkan <span class="fw-semibold">_START_</span> sampai <span class="fw-semibold">_END_</span> dari <span class="fw-semibold">_TOTAL_</span> produk',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: 'Cari:',
            zeroRecords: 'Tidak ada data yang ditemukan',
            emptyTable: 'Tidak ada data tersedia',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 produk',
            infoFiltered: '(disaring dari _MAX_ total produk)'
        },
        order: [[4, 'desc']],
        drawCallback: function () {
            initTooltips();
        }
    });

    initTooltips();
});
