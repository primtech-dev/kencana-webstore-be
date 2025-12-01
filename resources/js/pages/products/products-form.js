// resources/js/pages/products/products-form.js
// Refactored & cleaned version
// - Keep Quill editors, Select2 (dynamic load), wizard tab handling.
// - Clean variant add/remove (keamanan index, reindex saat submit).
// - Image preview for product gallery.
// - Defensive checks for missing DOM elements.
//
// Optional: if select2 is installed via npm, uncomment import:
// import 'select2';

import $ from 'jquery';
import 'bootstrap/js/dist/tab';
import { initQuillEditor, getQuillContent } from '../../utils/quill-helper';

let editorShortDesc = null;
let editorDesc = null;

function toastError(msg) {
    if (window.toast && typeof window.toast.error === 'function') return window.toast.error(msg);
    console.warn('Toast error:', msg);
}
function toastSuccess(msg) {
    if (window.toast && typeof window.toast.success === 'function') return window.toast.success(msg);
    console.log('Toast success:', msg);
}

/** Load external script once (returns Promise) */
function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) return resolve();
        const s = document.createElement('script');
        s.src = src;
        s.async = true;
        s.onload = () => resolve();
        s.onerror = () => reject(new Error('Failed to load ' + src));
        document.head.appendChild(s);
    });
}

/** Initialize Select2 on selector (dynamic load if needed) */
async function initSelect2(selector = '#categoriesSelect') {
    try {
        if (!document.querySelector(selector)) return;
        // load CDN if select2 not present
        if (typeof $().select2 !== 'function') {
            await loadScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
        }
        if (typeof $().select2 === 'function') {
            $(selector).select2({
                placeholder: 'Pilih kategori...',
                width: '100%',
                allowClear: true
            });
        }
    } catch (err) {
        console.warn('Select2 init failed:', err);
    }
}

/** Utility - safe query */
function $qs(selector) {
    return $(selector).length ? $(selector) : null;
}

/** Returns next variant index based on existing rows (ensures unique sequential indices) */
function nextVariantIndex() {
    const rows = $('#variantsContainer .variant-row');
    if (!rows.length) return 0;
    const indices = [];
    rows.each(function() {
        const name = $(this).find('input[name*="[variant_name]"], input[name*="[id]"]').first().attr('name') || '';
        const match = name.match(/variants\[(\d+)\]/);
        if (match) indices.push(parseInt(match[1], 10));
    });
    if (!indices.length) return 0;
    return Math.max(...indices) + 1;
}

/** Normalize existing variant rows to have correct numeric indices (reindex sequentially) */
function reindexVariantRows() {
    const rows = $('#variantsContainer .variant-row');
    rows.each(function(i) {
        const row = $(this);
        // update every input/select/textarea name
        row.find('[name]').each(function() {
            const el = $(this);
            const currentName = el.attr('name');
            // replace first occurrence of variants[...] with new index
            const newName = currentName.replace(/variants\[\d+\]/, `variants[${i}]`);
            el.attr('name', newName);
        });
    });
}

/** Build a safe variant template (uses nextVariantIndex) */
function buildVariantTemplate(index) {
    // Keep layout minimal but matching server fields
    return `
<div class="variant-row card p-3 mb-3" data-row-id="${Date.now()}">
  <input type="hidden" name="variants[${index}][id]" value="" />

  <div class="row g-3 align-items-center">
    <!-- IDENTITAS -->
    <div class="col-md-6">
      <label class="form-label">Nama Varian</label>
      <input type="text" name="variants[${index}][variant_name]" class="form-control" />
    </div>

    <div class="col-md-3">
      <label class="form-label">SKU</label>
      <input type="text" name="variants[${index}][sku]" class="form-control" />
    </div>

    <div class="col-md-3">
      <label class="form-label">Harga Jual (IDR)</label>
      <input type="number" name="variants[${index}][price]" class="form-control" min="0" value="0" />
    </div>

    <!-- DIMENSI -->
    <div class="col-md-4">
      <label class="form-label">Panjang (cm)</label>
      <input type="number" step="0.01" name="variants[${index}][length]" class="form-control" />
    </div>

    <div class="col-md-4">
      <label class="form-label">Lebar (cm)</label>
      <input type="number" step="0.01" name="variants[${index}][width]" class="form-control" />
    </div>

    <div class="col-md-4">
      <label class="form-label">Tinggi (cm)</label>
      <input type="number" step="0.01" name="variants[${index}][height]" class="form-control" />
    </div>

    <!-- STATUS & GAMBAR -->
    <div class="col-md-3">
      <label class="form-label">Aktif</label>
      <select name="variants[${index}][is_active]" class="form-select">
        <option value="1" selected>Ya</option>
        <option value="0">Tidak</option>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label">Boleh Dijual</label>
      <select name="variants[${index}][is_sellable]" class="form-select">
        <option value="1" selected>Ya</option>
        <option value="0">Tidak</option>
      </select>
    </div>

    <div class="col-md-6 text-end">
      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-variant mt-2">Hapus Varian</button>
    </div>

    <!-- GAMBAR VARIAN -->
    <div class="col-md-6">
      <label class="form-label">Gambar Varian (multiple)</label>
      <input type="file" name="variants[${index}][images][]" accept="image/*" multiple class="form-control" />
    </div>
  </div>
</div>
`;
}

/** Initialize Quill editors (if helpers available) */
function initQuillSafe(selector, opts) {
    if (!document.querySelector(selector)) return Promise.resolve(null);
    return initQuillEditor(selector, opts)
        .then(instance => instance)
        .catch(err => {
            console.error(`Quill init failed for ${selector}:`, err);
            toastError('Gagal memuat editor');
            return null;
        });
}

/** Image preview helper for file input -> preview container */
function setupImagePreview(fileInputSelector, previewContainerSelector) {
    const $input = $qs(fileInputSelector);
    const $preview = $qs(previewContainerSelector);
    if (!$input || !$preview) return;

    $input.on('change', function(e) {
        const files = Array.from(e.target.files || []);
        $preview.empty();
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const $el = $(`
                    <div class="position-relative" style="margin-right:8px;">
                        <img src="${ev.target.result}" class="image-thumb"/>
                        <button type="button" class="btn btn-sm btn-danger js-remove-local-image" style="position:absolute; right:5px; top:5px;"><i class="ti ti-x"></i></button>
                    </div>`);
                $preview.append($el);
            };
            reader.readAsDataURL(file);
        });
    });

    // remove preview element
    $(document).on('click', '.js-remove-local-image', function() {
        $(this).closest('div.position-relative').remove();
        toastError('Jika ingin benar-benar membatalkan upload gambar, reload halaman atau hapus file input manual.');
    });
}

/** Delete existing image via AJAX (edit mode) */
function bindDeleteImageAjax() {
    $(document).on('click', '.js-delete-image', function() {
        const id = $(this).data('id');
        if (!id) return;
        if (!confirm('Hapus gambar ini?')) return;
        $.ajax({
            url: `/products/images/${id}`,
            type: 'DELETE',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
            success(resp) {
                if (resp && resp.success) {
                    toastSuccess('Gambar dihapus');
                    location.reload();
                } else {
                    toastError('Gagal menghapus gambar');
                }
            },
            error() { toastError('Gagal menghapus gambar (server error)'); }
        });
    });
}

/** Setup wizard tab safe navigation */
function setupWizardNavigation() {
    // ensure nav buttons are not type=submit
    $('.nav-tabs').find('button').attr('type', 'button');

    // handle next / prev
    $(document).on('click', '[data-wizard-next], [data-wizard-prev]', function(e) {
        e.preventDefault();
        const $active = $('.nav-tabs .nav-link.active');
        if ($(this).is('[data-wizard-next]')) {
            const $next = $active.closest('li').next('li').find('.nav-link');
            if ($next.length) $next.tab('show');
        } else {
            const $prev = $active.closest('li').prev('li').find('.nav-link');
            if ($prev.length) $prev.tab('show');
        }
    });

    // links handling (prevent full page nav)
    $(document).on('click', '.nav-tabs a.nav-link', function(e) {
        e.preventDefault();
        $(this).tab('show');
    });
}

/** Clean up variant rows before submit:
 *  - remove rows with no variant_name, no id and no file
 *  - reindex rows to be sequential
 */
function setupFormSubmitCleanup() {
    const $form = $qs('#productForm');
    if (!$form) return;

    $form.on('submit', function(e) {
        // remove empty rows
        $('#variantsContainer .variant-row').each(function() {
            const $row = $(this);
            const nameVal = ($row.find('input[name*="[variant_name]"]').val() || '').trim();
            const idVal = ($row.find('input[name*="[id]"]').val() || '').trim();
            const fileInput = $row.find('input[type="file"]')[0];
            const hasFiles = fileInput && fileInput.files && fileInput.files.length > 0;
            if (!nameVal && !idVal && !hasFiles) {
                $row.remove();
            }
        });

        // reindex after removal
        reindexVariantRows();

        // parse attributes JSON -> create hidden input attributes
        const attrTextarea = $('textarea[name="attributes_json"]');
        if (attrTextarea.length) {
            const attrVal = attrTextarea.val();
            try {
                const json = attrVal ? JSON.parse(attrVal) : {};
                if ($('input[name="attributes"]').length) {
                    $('input[name="attributes"]').val(JSON.stringify(json));
                } else {
                    $('<input>').attr({type:'hidden', name:'attributes', value: JSON.stringify(json)}).appendTo('#productForm');
                }
            } catch (err) {
                e.preventDefault();
                toastError('Atribut tidak valid JSON');
                return false;
            }
        }

        // sync Quill contents
        if (editorShortDesc) $('#short_description').val(getQuillContent(editorShortDesc));
        if (editorDesc) $('#description').val(getQuillContent(editorDesc));

        // allow normal submit
        return true;
    });
}

/** Show server validation messages (toast) */
function showServerValidationErrors() {
    if (window.serverValidationErrors && Array.isArray(window.serverValidationErrors)) {
        window.serverValidationErrors.forEach(err => toastError(err));
    }
}

/** Attach add/remove handlers for variants (delegated) */
function bindVariantControls() {
    // Add variant
    $('#btn-add-variant').off('click').on('click', function(e) {
        e.preventDefault();
        const idx = nextVariantIndex();
        const html = buildVariantTemplate(idx);
        $('#variantsContainer').append(html);
        // scroll to newly added
        $('html, body').animate({ scrollTop: $('#variantsContainer').offset().top + $('#variantsContainer').height() }, 300);
    });

    // Remove variant (delegated)
    $(document).on('click', '.btn-remove-variant', function(e) {
        e.preventDefault();
        $(this).closest('.variant-row').remove();
        reindexVariantRows();
    });
}

/** Initialize everything on DOM ready */
$(function() {
    // Quill editors
    initQuillSafe('#short_description', { height: '300px', placeholder: 'Tulis konten Anda di sini...' })
        .then(inst => editorShortDesc = inst);
    initQuillSafe('#description', { height: '300px', placeholder: 'Tulis konten Anda di sini...' })
        .then(inst => editorDesc = inst);

    setupWizardNavigation();
    initSelect2('#categoriesSelect');
    bindVariantControls();
    setupImagePreview('#productImagesInput', '#imagePreview');
    bindDeleteImageAjax();
    setupFormSubmitCleanup();
    showServerValidationErrors();

    // Defensive: if there are no variant rows, add one empty
    if (!$('#variantsContainer .variant-row').length) {
        const idx = 0;
        $('#variantsContainer').append(buildVariantTemplate(idx));
    }
});
