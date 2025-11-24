import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import bootstrap from 'bootstrap/dist/js/bootstrap';
import { showDeleteModal } from '../../utils/delete-modal-helper';
import { initTooltips } from '../../utils/tooltip-helper';

function btnEdit(id, question, answer) {
    // Update form action URL
    const form = document.getElementById('editFaqForm');
    const baseAction = form.action.split('/faqs/')[0] + '/faqs/';
    form.action = baseAction + id;

    // Set input values
    document.getElementById('edit_faq_question').value = question;
    document.getElementById('edit_faq_answer').value = answer || '';

    // Show modal
    const editModal = new bootstrap.Modal(document.getElementById('editFaqModal'));
    editModal.show();
}

function btnDelete(id, question) {
    showDeleteModal({
        modalId: 'deleteFaqModal',
        formId: 'deleteFaqForm',
        itemNameId: 'delete_faq_question',
        id: id,
        name: question,
        route: window.faqRoutes.destroy
    });
}

// Make functions globally accessible
window.btnEdit = btnEdit;
window.btnDelete = btnDelete;

$(function() {
    new DataTable('#faqs-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: window.faqRoutes.index,
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
            },
            {
                data: 'question',
                name: 'question',
                render: function(data) {
                    if (!data) {
                        return '<span class="text-muted fst-italic">Tidak ada pertanyaan</span>';
                    }
                    // Truncate long questions
                    if (data.length > 80) {
                        return `<span data-bs-toggle="tooltip" title="${data}">${data.substring(0, 80)}...</span>`;
                    }
                    return data;
                }
            },
            {
                data: 'answer',
                name: 'answer',
                render: function(data) {
                    if (!data) {
                        return '<span class="text-muted fst-italic">Tidak ada jawaban</span>';
                    }
                    // Truncate long answers
                    if (data.length > 100) {
                        return `<span data-bs-toggle="tooltip" title="${data}">${data.substring(0, 100)}...</span>`;
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
            lengthMenu: '_MENU_ faq per halaman',
            info: 'Menampilkan <span class="fw-semibold">_START_</span> sampai <span class="fw-semibold">_END_</span> dari <span class="fw-semibold">_TOTAL_</span> faq',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: 'Cari:',
            zeroRecords: 'Tidak ada data yang ditemukan',
            emptyTable: 'Tidak ada data tersedia',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 faq',
            infoFiltered: '(disaring dari _MAX_ total faq)'
        },
        order: [[3, 'desc']],
        drawCallback: function() {
            // Re-initialize tooltips after table is drawn
            initTooltips();
        }
    });

    // Initialize tooltips on page load
    initTooltips();
});
