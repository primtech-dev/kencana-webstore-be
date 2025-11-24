import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import bootstrap from 'bootstrap/dist/js/bootstrap';
import { showDeleteModal } from '../../utils/delete-modal-helper';
import { initTooltips } from '../../utils/tooltip-helper';

function btnEdit(id, name, email) {
    // Update form action URL
    const form = document.getElementById('editUserForm');
    const baseAction = form.action.split('/users/')[0] + '/users/';
    form.action = baseAction + id;

    // Set input values
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;

    // Show modal
    const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    editModal.show();
}

function btnResetPassword(id, name) {
    // Update form action URL
    const form = document.getElementById('resetPasswordForm');
    const baseAction = form.action.split('/users/')[0] + '/users/';
    form.action = baseAction + id + '/reset-password';

    // Set user name
    document.getElementById('reset_user_name').textContent = name;

    // Show modal
    const resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    resetModal.show();
}

function btnDelete(id, name) {
    showDeleteModal({
        modalId: 'deleteUserModal',
        formId: 'deleteUserForm',
        itemNameId: 'delete_user_name',
        id: id,
        name: name,
        route: window.userRoutes.destroy
    });
}

// Make functions globally accessible
window.btnEdit = btnEdit;
window.btnResetPassword = btnResetPassword;
window.btnDelete = btnDelete;

$(function() {
    new DataTable('#users-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: window.userRoutes.index,
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
                data: 'email',
                name: 'email',
                render: function(data, type, row) {
                    if (!data) {
                        return '<span class="text-muted fst-italic">No email</span>';
                    }
                    return `<a href="mailto:${data}" class="text-decoration-none">${data}</a>`;
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
            lengthMenu: '_MENU_ user per halaman',
            info: 'Menampilkan <span class="fw-semibold">_START_</span> sampai <span class="fw-semibold">_END_</span> dari <span class="fw-semibold">_TOTAL_</span> user',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: 'Cari:',
            zeroRecords: 'Tidak ada data yang ditemukan',
            emptyTable: 'Tidak ada data tersedia',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 user',
            infoFiltered: '(disaring dari _MAX_ total user)'
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
