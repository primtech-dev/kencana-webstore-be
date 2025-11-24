import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import bootstrap from 'bootstrap/dist/js/bootstrap';
import { showDeleteModal } from '../../utils/delete-modal-helper';
import { initTooltips } from '../../utils/tooltip-helper';

// Edit button handler
function btnEdit(id, name, car_unit, phoneNumber, address) {
    // Update form action URL
    const form = document.getElementById('editCustomerForm');
    const baseAction = form.action.split('/customers/')[0] + '/customers/';
    form.action = baseAction + id;

    // Set input values
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_car_unit').value = car_unit;
    document.getElementById('edit_phone_number').value = phoneNumber;
    document.getElementById('edit_address').value = address;

    // Show modal
    const editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
    editModal.show();
}

// Delete button handler
function btnDelete(id, name) {
    showDeleteModal({
        modalId: 'deleteCustomerModal',
        formId: 'deleteCustomerForm',
        itemNameId: 'delete_customer_name',
        id: id,
        name: name,
        route: window.customerRoutes.destroy
    });
}

// Make functions globally accessible
window.btnEdit = btnEdit;
window.btnDelete = btnDelete;

$(function() {
    // Initialize DataTable
    new DataTable('#customers-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: window.customerRoutes.index,
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
                data: 'car_unit',
                name: 'car_unit',
            },
            {
                data: 'address',
                name: 'address',
                render: function(data, type, row) {
                    if (data.length > 50) {
                        return data.substring(0, 50) + '...';
                    }
                    return data;
                }
            },
            {
                data: 'phone_number',
                name: 'phone_number',
                render: function(data, type, row) {
                    return `<a href="tel:${data}" class="text-decoration-none">${data}</a>`;
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
            lengthMenu: '_MENU_ customer per halaman',
            info: 'Menampilkan <span class="fw-semibold">_START_</span> sampai <span class="fw-semibold">_END_</span> dari <span class="fw-semibold">_TOTAL_</span> customer',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: 'Cari:',
            zeroRecords: 'Tidak ada data yang ditemukan',
            emptyTable: 'Tidak ada data tersedia',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 customer',
            infoFiltered: '(disaring dari _MAX_ total customer)'
        },
        order: [[5, 'desc']],
        drawCallback: function() {
            initTooltips();
        }
    });

    // Phone number validation - only allow numbers
    $('#create_phone_number, #edit_phone_number').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Reset create form on modal close
    $('#createCustomerModal').on('hidden.bs.modal', function() {
        $('#createCustomerForm')[0].reset();
    });

    // Initialize tooltips
    initTooltips();
});
