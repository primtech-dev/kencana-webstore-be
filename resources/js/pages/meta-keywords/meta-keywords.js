import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import { showDeleteModal } from '../../utils/delete-modal-helper';
import { initTooltips } from '../../utils/tooltip-helper';

window.btnDeleteMetaKeyword = function (id, title) {
    showDeleteModal({
        modalId: 'deleteMetaKeywordModal',
        formId: 'deleteMetaKeywordForm',
        itemNameId: 'delete_meta_keyword_title',
        id,
        name: title,
        route: window.metaKeywordRoutes.destroy
    });
};

$(function() {
    if (!window.metaKeywordRoutes || !window.metaKeywordRoutes.index) {
        console.error('metaKeywordRoutes.index not defined.');
        return;
    }

    const table = new DataTable('#meta-keywords-table', {
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: window.metaKeywordRoutes.index,
            type: 'GET',
            dataType: 'json',
            cache: false,
            error: function(xhr, textStatus, errorThrown) {
                console.error('DataTables AJAX error:', textStatus, errorThrown, xhr.responseText);
                if (window.toast) window.toast.error('Gagal memuat data meta keyword. Cek console.');
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug' },
            { data: 'is_active', name: 'is_active', className: 'text-center', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[4, 'desc']],
        drawCallback: function() {
            try { if (window.lucide && typeof window.lucide.replace === 'function') window.lucide.replace(); } catch(e) {}
            initTooltips(document.querySelector('#meta-keywords-table'));
        }
    });

    $(document).on('click', '.js-delete-meta-keyword', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name') || '';
        showDeleteModal({
            modalId: 'deleteMetaKeywordModal',
            formId: 'deleteMetaKeywordForm',
            itemNameId: 'delete_meta_keyword_title',
            id,
            name,
            route: window.metaKeywordRoutes.destroy
        });
    });

    try { if (window.lucide && typeof window.lucide.replace === 'function') window.lucide.replace(); } catch(e) {}
    initTooltips(document);
});
