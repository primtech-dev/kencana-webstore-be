import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import { showDeleteModal } from '../../../utils/delete-modal-helper';
import { initTooltips } from '../../../utils/tooltip-helper';

function btnDelete(id, title) {
    showDeleteModal({
        modalId: 'deleteArticleModal',
        formId: 'deleteArticleForm',
        itemNameId: 'delete_article_title',
        id: id,
        name: title,
        route: window.articleRoutes.destroy
    });
}

// Make function globally accessible
window.btnDelete = btnDelete;

$(function() {
    new DataTable('#articles-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: window.articleRoutes.index,
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
            },
            {
                data: 'title',
                name: 'title',
                render: function(data) {
                    if (!data) {
                        return '<span class="text-muted fst-italic">Tidak ada judul</span>';
                    }
                    // Truncate long titles
                    if (data.length > 60) {
                        return `<span data-bs-toggle="tooltip" title="${data}">${data.substring(0, 60)}...</span>`;
                    }
                    return data;
                }
            },
            {
                data: 'seo_url',
                name: 'seo_url',
                render: function(data) {
                    if (!data) {
                        return '<span class="text-muted fst-italic">Tidak ada SEO URL</span>';
                    }
                    // Truncate long SEO URLs
                    if (data.length > 40) {
                        return `<span data-bs-toggle="tooltip" title="${data}">${data.substring(0, 40)}...</span>`;
                    }
                    return `<span>${data}</span>`;
                }
            },
            {
                data: 'meta_title',
                name: 'meta_title',
                render: function(data) {
                    if (!data) {
                        return '<span class="text-muted fst-italic">Tidak ada meta title</span>';
                    }
                    // Truncate long meta titles
                    if (data.length > 50) {
                        return `<span data-bs-toggle="tooltip" title="${data}">${data.substring(0, 50)}...</span>`;
                    }
                    return data;
                }
            },
            {
                data: 'author',
                name: 'author.name',
                render: function(data, type, row) {
                    if (!data || !data.name) {
                        return '<span class="text-muted fst-italic">Tidak ada penulis</span>';
                    }
                    return `<span>${data.name}</span>`;
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
            lengthMenu: '_MENU_ artikel per halaman',
            info: 'Menampilkan <span class="fw-semibold">_START_</span> sampai <span class="fw-semibold">_END_</span> dari <span class="fw-semibold">_TOTAL_</span> artikel',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: 'Cari:',
            zeroRecords: 'Tidak ada data yang ditemukan',
            emptyTable: 'Tidak ada data tersedia',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 artikel',
            infoFiltered: '(disaring dari _MAX_ total artikel)'
        },
        order: [[5, 'desc']],
        drawCallback: function() {
            // Re-initialize tooltips after table is drawn
            initTooltips();
        }
    });

    // Initialize tooltips on page load
    initTooltips();
});
