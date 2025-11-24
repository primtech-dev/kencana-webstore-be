import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import bootstrap from 'bootstrap/dist/js/bootstrap';
import { showDeleteModal } from '../../utils/delete-modal-helper';
import { initTooltips } from '../../utils/tooltip-helper';

// ============= LOAN AMOUNT HANDLERS =============

// Edit Loan Amount button handler
function btnEditLoanAmount(id, amount) {
    // Update form action URL
    const form = document.getElementById('editLoanAmountForm');
    const baseAction = form.action.split('/credit-simulations/')[0] + '/credit-simulations/loan-amounts/';
    form.action = baseAction + id;

    // Set input value
    document.getElementById('edit_amount').value = amount;

    // Show modal
    const editModal = new bootstrap.Modal(document.getElementById('editLoanAmountModal'));
    editModal.show();
}

// Delete Loan Amount button handler
function btnDeleteLoanAmount(id, name) {
    showDeleteModal({
        modalId: 'deleteLoanAmountModal',
        formId: 'deleteLoanAmountForm',
        itemNameId: 'delete_loan_amount_name',
        id: id,
        name: name,
        route: window.creditSimulationRoutes.destroyLoanAmount
    });
}

// ============= TENOR HANDLERS =============

// Edit Tenor button handler
function btnEditTenor(id, months) {
    // Update form action URL
    const form = document.getElementById('editTenorForm');
    const baseAction = form.action.split('/credit-simulations/')[0] + '/credit-simulations/tenors/';
    form.action = baseAction + id;

    // Set input value
    document.getElementById('edit_months').value = months;

    // Show modal
    const editModal = new bootstrap.Modal(document.getElementById('editTenorModal'));
    editModal.show();
}

// Delete Tenor button handler
function btnDeleteTenor(id, name) {
    showDeleteModal({
        modalId: 'deleteTenorModal',
        formId: 'deleteTenorForm',
        itemNameId: 'delete_tenor_name',
        id: id,
        name: name,
        route: window.creditSimulationRoutes.destroyTenor
    });
}

// Make functions globally accessible
window.btnEditLoanAmount = btnEditLoanAmount;
window.btnDeleteLoanAmount = btnDeleteLoanAmount;
window.btnEditTenor = btnEditTenor;
window.btnDeleteTenor = btnDeleteTenor;

$(function() {
    // ============= LOAN AMOUNT DATATABLE =============
    new DataTable('#loan-amount-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: window.creditSimulationRoutes.indexLoanAmount,
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
            },
            {
                data: 'amount',
                name: 'amount',
                render: function(data, type, row) {
                    return 'Rp ' + data;
                }
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
            lengthMenu: '_MENU_ data per halaman',
            info: 'Menampilkan <span class="fw-semibold">_START_</span> sampai <span class="fw-semibold">_END_</span> dari <span class="fw-semibold">_TOTAL_</span> data',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: 'Cari:',
            zeroRecords: 'Tidak ada data yang ditemukan',
            emptyTable: 'Tidak ada data tersedia',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(disaring dari _MAX_ total data)'
        },
        order: [[1, 'asc']],
        drawCallback: function() {
            initTooltips();
        }
    });

    // ============= TENOR DATATABLE =============
    new DataTable('#tenor-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: window.creditSimulationRoutes.indexTenor,
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
            },
            {
                data: 'months',
                name: 'months',
                render: function(data, type, row) {
                    return data + ' Bulan';
                }
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
            lengthMenu: '_MENU_ tenor per halaman',
            info: 'Menampilkan <span class="fw-semibold">_START_</span> sampai <span class="fw-semibold">_END_</span> dari <span class="fw-semibold">_TOTAL_</span> tenor',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: 'Cari:',
            zeroRecords: 'Tidak ada data yang ditemukan',
            emptyTable: 'Tidak ada data tersedia',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 tenor',
            infoFiltered: '(disaring dari _MAX_ total tenor)'
        },
        order: [[1, 'asc']],
        drawCallback: function() {
            initTooltips();
        }
    });

    // ============= FORM HANDLERS =============

    // Number input validation - only allow positive numbers
    $('#create_amount, #edit_amount, #create_months, #edit_months').on('input', function() {
        // Remove any non-numeric characters except decimal point
        this.value = this.value.replace(/[^0-9]/g, '');

        // Ensure positive number
        if (parseInt(this.value) < 0) {
            this.value = '';
        }
    });

    // Reset create forms on modal close
    $('#createLoanAmountModal').on('hidden.bs.modal', function() {
        $('#createLoanAmountForm')[0].reset();
    });

    $('#createTenorModal').on('hidden.bs.modal', function() {
        $('#createTenorForm')[0].reset();
    });

    // Initialize tooltips
    initTooltips();
});
