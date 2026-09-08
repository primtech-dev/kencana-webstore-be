import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import { showDeleteModal } from '../../utils/delete-modal-helper';
import { initTooltips } from '../../utils/tooltip-helper';

window.btnDeleteSubCategory = function (id, title) {
    showDeleteModal({
        modalId: 'deleteSubCategoryModal',
        formId: 'deleteSubCategoryForm',
        itemNameId: 'delete_sub_category_title',
        id,
        name: title,
        route: window.subCategoryRoutes.destroy
    });
};

$(function() {
    if (!window.subCategoryRoutes || !window.subCategoryRoutes.index) {
        console.error('subCategoryRoutes.index not defined.');
        return;
    }

    const table = new DataTable('#sub-categories-table', {
        processing: true,
        serverSide: true,
        responsive: true,
        autoWidth: false,
        ajax: {
            url: window.subCategoryRoutes.index,
            type: 'GET',
            dataType: 'json',
            cache: false,
            error: function(xhr, textStatus, errorThrown) {
                console.error('DataTables AJAX error:', textStatus, errorThrown, xhr.responseText);
                if (window.toast) window.toast.error('Gagal memuat data sub kategori. Cek console.');
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'thumbnail', name: 'thumbnail', orderable: false, searchable: false },
            { data: 'slug', name: 'slug' },
            { data: 'parent', name: 'parent', orderable: false, searchable: false },
            { data: 'position', name: 'position' },
            { data: 'is_active', name: 'is_active', className: 'text-center', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[6, 'desc']],
        drawCallback: function() {
            try { if (window.lucide && typeof window.lucide.replace === 'function') window.lucide.replace(); } catch(e) {}
            initTooltips(document.querySelector('#sub-categories-table'));
        }
    });

    $(document).on('click', '.js-delete-sub-category', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name') || '';
        showDeleteModal({
            modalId: 'deleteSubCategoryModal',
            formId: 'deleteSubCategoryForm',
            itemNameId: 'delete_sub_category_title',
            id,
            name,
            route: window.subCategoryRoutes.destroy
        });
    });

    // initial icons/tooltips
    try { if (window.lucide && typeof window.lucide.replace === 'function') window.lucide.replace(); } catch(e) {}
    initTooltips(document);
});
