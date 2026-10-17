import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import { showDeleteModal } from '../../utils/delete-modal-helper';
import { initTooltips } from '../../utils/tooltip-helper';

window.btnDeleteOrder = function (id, title) {
    showDeleteModal({
        modalId: 'deleteOrderModal',
        formId: 'deleteOrderForm',
        itemNameId: 'delete_order_title',
        id,
        name: title,
        route: window.orderRoutes.destroy
    });
};

window.openChangeStatusModal = function(orderId, orderNo, currentStatus = '', allowed = null) {
    try {
        const modalEl = document.getElementById('changeStatusModal');
        if (!modalEl) {
            console.error('Modal changeStatusModal tidak ditemukan di DOM.');
            return;
        }

        const orderIdInput = document.getElementById('changeStatusOrderId');
        const orderNoSpan = document.getElementById('changeStatusOrderNo');
        const form = document.getElementById('changeStatusForm');
        const statusSelect = document.getElementById('status');
        const hint = document.getElementById('changeStatusHint');
        const labels = window.orderStatusLabels || {};

        if (orderIdInput) orderIdInput.value = orderId;
        if (orderNoSpan) orderNoSpan.textContent = orderNo ? `(${orderNo})` : '';

        if (form) {
            const tmpl = form.getAttribute('data-action-template');
            if (tmpl) {
                form.setAttribute('action', tmpl.replace(':id', orderId));
            }
            const notes = document.getElementById('status_notes');
            if (notes) notes.value = '';
        }

        if (statusSelect) {
            // Opsi = status saat ini (untuk sekadar menambah catatan) + status berikutnya yang diizinkan
            const values = [];
            if (currentStatus) values.push(currentStatus);
            (Array.isArray(allowed) ? allowed : Object.keys(labels)).forEach(v => {
                if (!values.includes(v)) values.push(v);
            });

            statusSelect.innerHTML = '';
            values.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v;
                opt.textContent = (labels[v] || v) + (v === currentStatus ? ' (saat ini)' : '');
                statusSelect.appendChild(opt);
            });
            statusSelect.value = currentStatus || values[0];

            if (hint) {
                hint.textContent = (Array.isArray(allowed) && allowed.length === 0)
                    ? 'Status akhir: hanya catatan yang dapat ditambahkan.'
                    : '';
            }
        }

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    } catch (e) {
        console.error('openChangeStatusModal error', e);
    }
};

$(document).on('click', '.js-change-status', function (e) {
    e.preventDefault();
    const $b = $(this);
    let allowed = null;
    try { allowed = JSON.parse($b.attr('data-allowed') || 'null'); } catch (err) { allowed = null; }
    window.openChangeStatusModal($b.data('id'), $b.data('no'), $b.data('status') || '', allowed);
});

document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('changeStatusModal');
    if (!modalEl) return;

    let choicesInstance = null;

    modalEl.addEventListener('shown.bs.modal', function () {
        const sel = document.getElementById('status');
        if (!sel) return console.warn('status select not found on modal shown');

        // If Choices library is present and not initialized yet, init it
        try {
            if (window.Choices && !sel._choicesInstance) {
                choicesInstance = new Choices(sel, {
                    searchEnabled: false,
                    shouldSort: false,
                    itemSelectText: '',
                    classNames: {
                        containerOuter: 'choices',
                    }
                });
                sel._choicesInstance = choicesInstance;
                console.log('Choices initialized for #status');
                return;
            }
        } catch (e) {
            console.warn('Choices init error:', e);
        }

        // If plugin not available OR initialization failed, ensure select is visible
        sel.style.display = '';
        sel.classList.add('form-select');
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        const sel = document.getElementById('status');
        // destroy choices to prevent duplicate UI on next show (if created)
        try {
            if (sel && sel._choicesInstance && typeof sel._choicesInstance.destroy === 'function') {
                sel._choicesInstance.destroy();
                sel._choicesInstance = null;
                console.log('Choices destroyed for #status');
            }
        } catch (e) {
            console.warn('Choices destroy error:', e);
        }
    });
});

$(function() {
    // halaman detail order tidak punya tabel daftar
    if (!window.orderRoutes || !window.orderRoutes.index) {
        return;
    }

    const table = new DataTable('#orders-table', {
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: window.orderRoutes.index,
            type: 'GET',
            dataType: 'json',
            cache: false,
            data: function (d) {
                d.status = $('#filter_status').val() || '';
                d.payment_status = $('#filter_payment_status').val() || '';
                d.branch_id = $('#filter_branch').val() || '';
                d.date_from = $('#filter_date_from').val() || '';
                d.date_to = $('#filter_date_to').val() || '';
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('DataTables AJAX error:', textStatus, errorThrown, xhr.responseText);
                if (window.toast) window.toast.error('Gagal memuat data order. Cek console.');
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_no', name: 'order_no' },
            { data: 'customer', orderable: false },
            ...(window.orderRoutes.showBranch ? [{ data: 'branch', orderable: false }] : []),
            { data: 'total_amount', name: 'total_amount', className: 'text-end' },
            { data: 'status', name: 'status' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', orderable: false, searchable: false }
        ],
        order: [[window.orderRoutes.showBranch ? 6 : 5, 'desc']], // default terbaru
        drawCallback: function() {
            try { if (window.lucide && typeof window.lucide.replace === 'function') window.lucide.replace(); } catch(e) {}
            initTooltips(document.querySelector('#orders-table'));
        }
    });

    $('#orderFilters select, #orderFilters input').on('change', function () {
        table.ajax.reload();
    });
    $('#btnResetFilters').on('click', function () {
        $('#orderFilters select').val('');
        $('#orderFilters input').val('');
        table.ajax.reload();
    });

    $(document).on('click', '.js-delete-order', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name') || '';
        showDeleteModal({
            modalId: 'deleteOrderModal',
            formId: 'deleteOrderForm',
            itemNameId: 'delete_order_title',
            id,
            name,
            route: window.orderRoutes.destroy
        });
    });

});
