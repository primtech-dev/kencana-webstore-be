import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import bootstrap from 'bootstrap/dist/js/bootstrap';
import { showDeleteModal } from '../../utils/delete-modal-helper';
import { initTooltips } from '../../utils/tooltip-helper';

/**
 * Handle Edit Button
 */
function btnEdit(id, name) {
    const form = document.getElementById('editTagForm');

    // Replace placeholder with actual ID
    form.action = form.action.replace('__ID__', id);

    document.getElementById('edit_tag_name').value = name;

    const modal = new bootstrap.Modal(document.getElementById('editTagModal'));
    modal.show();
}
/**
 * Handle Delete Button
 */
function btnDelete(id, name) {
    showDeleteModal({
        modalId: 'deleteTagModal',
        formId: 'deleteTagForm',
        itemNameId: 'delete_tag_name',
        id: id,
        name: name,
        route: window.tagRoutes.destroy
    });
}

// Make functions accessible from HTML
window.btnEdit = btnEdit;
window.btnDelete = btnDelete;

/**
 * DataTable Initialization
 */
$(function () {
    new DataTable('#tags-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: window.tagRoutes.index,
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
                        return '<span class="text-muted fst-italic">Tidak ada nama tag</span>';
                    }
                    if (data.length > 80) {
                        return `<span data-bs-toggle="tooltip" title="${data}">
                                    ${data.substring(0, 80)}...
                                </span>`;
                    }
                    return data;
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
            lengthMenu: '_MENU_ tag per halaman',
            info: 'Menampilkan <span class="fw-semibold">_START_</span> sampai <span class="fw-semibold">_END_</span> dari <span class="fw-semibold">_TOTAL_</span> tag',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: 'Cari:',
            zeroRecords: 'Tidak ada data ditemukan',
            emptyTable: 'Belum ada tag yang dibuat',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 tag',
            infoFiltered: '(disaring dari _MAX_ total tag)'
        },

        order: [[2, 'desc']], // sort by created_at

        drawCallback: function () {
            initTooltips();
        }
    });

    initTooltips();
});
