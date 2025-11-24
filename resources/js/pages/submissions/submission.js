import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import bootstrap from 'bootstrap/dist/js/bootstrap';
import { initTooltips } from '../../utils/tooltip-helper';

// Helper function untuk escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

$(function() {
    // Initialize DataTable
    new DataTable('#submissions-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: window.submissionRoutes.index,
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
            },
            {
                data: 'name',
                name: 'name'
            },
            {
                data: 'phone_number',
                name: 'phone_number',
                render: function(data, type, row) {
                    return `<a href="tel:${data}" class="text-decoration-none">${data}</a>`;
                }
            },
            {
                data: 'car_unit',
                name: 'car_unit'
            },
            {
                data: 'address',
                name: 'address',
                render: function(data, type, row) {
                    if (!data) return '-';
                    if (data.length > 50) {
                        return data.substring(0, 50) + '...';
                    }
                    return data;
                }
            },
            {
                data: 'message',
                name: 'message',
                orderable: false,
                render: function(data, type, row) {
                    if (!data) return '-';

                    const truncated = data.length > 50 ? data.substring(0, 50) + '...' : data;

                    return `<span class="message-tooltip"
                                  data-bs-toggle="tooltip"
                                  data-bs-placement="top"
                                  data-bs-html="true"
                                  title="${escapeHtml(data)}">
                                ${escapeHtml(truncated)}
                            </span>`;
                }
            },
            {
                data: 'submission_datetime',
                name: 'submission_datetime',
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
            lengthMenu: '_MENU_ pengajuan per halaman',
            info: 'Menampilkan <span class="fw-semibold">_START_</span> sampai <span class="fw-semibold">_END_</span> dari <span class="fw-semibold">_TOTAL_</span> pengajuan',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: 'Cari:',
            zeroRecords: 'Tidak ada data yang ditemukan',
            emptyTable: 'Tidak ada data tersedia',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 pengajuan',
            infoFiltered: '(disaring dari _MAX_ total pengajuan)'
        },
        order: [[6, 'desc']],
        drawCallback: function() {
            initTooltips();
        }
    });

    // Initialize tooltips
    initTooltips();
});
