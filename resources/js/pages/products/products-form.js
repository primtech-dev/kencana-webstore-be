// resources/js/pages/products/products-form.js
// Full script — copy/paste this file content.
//
// Optional: if you install select2 via npm, run:
//   npm i select2
// then uncomment the import below and remove dynamic loader:
// import 'select2';

import $ from 'jquery';
import 'bootstrap/js/dist/tab'; // ensure bootstrap Tab API available

function toastError(msg) {
    if (window.toast && typeof window.toast.error === 'function') return window.toast.error(msg);
    console.warn('Toast error:', msg);
}
function toastSuccess(msg) {
    if (window.toast && typeof window.toast.success === 'function') return window.toast.success(msg);
    console.log('Toast success:', msg);
}

/**
 * Load external script (returns Promise)
 * @param {string} src
 * @returns {Promise<void>}
 */
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

/**
 * Initialize Select2 (dynamic load if needed)
 * selector default: '#categoriesSelect'
 */
async function initSelect2(selector = '#categoriesSelect') {
    try {
        // If select2 is not present, dynamically load it from CDN
        if (typeof $().select2 !== 'function') {
            await loadScript('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
        }
        if ($(selector).length && typeof $().select2 === 'function') {
            $(selector).select2({
                placeholder: 'Pilih kategori...',
                width: '100%',
                allowClear: true
            });
        }
    } catch (err) {
        console.warn('Select2 load/init failed:', err);
    }
}

$(function() {
    // Ensure nav tab buttons are not type=submit (defensive)
    $('.nav-tabs').find('button').attr('type', 'button');

    // -------------- Wizard navigation (safe) --------------
    // Prevent any wizard next/prev from submitting the form.
    $(document).on('click', '[data-wizard-next], [data-wizard-prev]', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $btn = $(this);
        if ($btn.is('[data-wizard-next]')) {
            const $active = $('.nav-tabs .nav-link.active');
            const $nextLi = $active.closest('li').next('li');
            const $nextLink = $nextLi.find('.nav-link');
            if ($nextLink.length) $nextLink.tab('show');
        } else {
            const $active = $('.nav-tabs .nav-link.active');
            const $prevLi = $active.closest('li').prev('li');
            const $prevLink = $prevLi.find('.nav-link');
            if ($prevLink.length) $prevLink.tab('show');
        }
    });

    // Prev/Next custom buttons in layout (#btn-next-tab, #btn-prev-tab)
    $(document).on('click', '#btn-next-tab, #btn-prev-tab', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const id = $(this).attr('id');
        const $active = $('.nav-tabs .nav-link.active');

        if (id === 'btn-next-tab') {
            const $nextLi = $active.closest('li').next('li');
            const $nextLink = $nextLi.find('.nav-link');
            if ($nextLink.length) $nextLink.tab('show');
        } else {
            const $prevLi = $active.closest('li').prev('li');
            const $prevLink = $prevLi.find('.nav-link');
            if ($prevLink.length) $prevLink.tab('show');
        }
    });

    // If nav uses <a> links, ensure they don't submit (defensive)
    $(document).on('click', '.nav-tabs a.nav-link', function(e){
        // let bootstrap handle the tab switch; ensure not submitting
        e.preventDefault();
        $(this).tab('show');
    });

    // -------------- Init Select2 (dynamic) --------------
    initSelect2('#categoriesSelect');

    // -------------- Variants dynamic handling --------------
    let variantIndex = $('#variantsContainer .variant-row').length || 0;

    $('#btn-add-variant').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const idx = variantIndex++;
        const template = `
            <div class="variant-row" data-row-id="${Date.now()}">
                <input type="hidden" name="variants[${idx}][id]" value="" />
                <div class="row gy-2">
                    <div class="col-md-4"><label class="form-label">Nama Varian</label><input type="text" name="variants[${idx}][variant_name]" class="form-control" /></div>
                    <div class="col-md-2"><label class="form-label">SKU</label><input type="text" name="variants[${idx}][sku]" class="form-control" /></div>
                    <div class="col-md-2"><label class="form-label">Harga (sen)</label><input type="number" name="variants[${idx}][price_cents]" class="form-control" value="0" /></div>
                    <div class="col-md-2"><label class="form-label">Retail (sen)</label><input type="number" name="variants[${idx}][retail_price_cents]" class="form-control" value="" /></div>
                    <div class="col-md-2"><label class="form-label">Cost (sen)</label><input type="number" name="variants[${idx}][cost_cents]" class="form-control" value="" /></div>
                    <div class="col-md-2"><label class="form-label">Panjang (cm)</label><input type="text" name="variants[${idx}][length]" class="form-control" /></div>
                    <div class="col-md-2"><label class="form-label">Lebar (cm)</label><input type="text" name="variants[${idx}][width]" class="form-control" /></div>
                    <div class="col-md-2"><label class="form-label">Tinggi (cm)</label><input type="text" name="variants[${idx}][height]" class="form-control" /></div>
                    <div class="col-md-3"><label class="form-label">Aktif</label><select name="variants[${idx}][is_active]" class="form-select"><option value="1">Ya</option><option value="0">Tidak</option></select></div>
                    <div class="col-md-3"><label class="form-label">Boleh Dijual</label><select name="variants[${idx}][is_sellable]" class="form-select"><option value="1">Ya</option><option value="0">Tidak</option></select></div>
                    <div class="col-md-6"><label class="form-label">Gambar Varian (boleh multiple)</label><input type="file" name="variants[${idx}][images][]" accept="image/*" multiple class="form-control" /></div>
                    <div class="col-md-12 text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-variant">Hapus Varian</button></div>
                </div>
                <hr/>
            </div>
        `;
        $('#variantsContainer').append(template);
        // auto scroll to area (nice UX)
        $('html, body').animate({ scrollTop: $('#variantsContainer').offset().top + $('#variantsContainer').height() }, 300);
    });

    $(document).on('click', '.btn-remove-variant', function(e) {
        e.preventDefault();
        $(this).closest('.variant-row').remove();
    });

    // -------------- Product images preview (main gallery) --------------
    $('#productImagesInput').on('change', function(e) {
        const files = Array.from(e.target.files || []);
        const $preview = $('#imagePreview').empty();
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const img = `<div class="position-relative" style="margin-right:8px;"><img src="${ev.target.result}" class="image-thumb"/><button type="button" class="btn btn-sm btn-danger js-remove-local-image" style="position:absolute; right:5px; top:5px;"><i class="ti ti-x"></i></button></div>`;
                $preview.append(img);
            };
            reader.readAsDataURL(file);
        });
    });

    $(document).on('click', '.js-remove-local-image', function(){
        $(this).closest('div.position-relative').remove();
        toastError('Jika ingin benar-benar membatalkan upload gambar, reload halaman atau hapus file input manual.');
    });

    // -------------- Delete existing image via AJAX (edit mode) --------------
    $(document).on('click', '.js-delete-image', function(){
        const id = $(this).data('id');
        if (!confirm('Hapus gambar ini?')) return;
        $.ajax({
            url: `/products/images/${id}`,
            type: 'DELETE',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
            success(resp) {
                if (resp.success) {
                    toastSuccess('Gambar dihapus');
                    location.reload();
                } else toastError('Gagal menghapus');
            },
            error() { toastError('Gagal menghapus gambar (server error)'); }
        });
    });

    // -------------- Before submit: cleanup and attributes parsing --------------
    $('#productForm').on('submit', function(e){
        // Remove variant rows that have no name, no id and no file => avoid server-side validation errors
        $('#variantsContainer .variant-row').each(function(){
            const $row = $(this);
            const nameVal = ($row.find('input[name*="[variant_name]"]').val() || '').trim();
            const idVal = ($row.find('input[name*="[id]"]').val() || '').trim();
            const fileInput = $row.find('input[type="file"]')[0];
            const hasFiles = fileInput && fileInput.files && fileInput.files.length > 0;

            if (!nameVal && !idVal && !hasFiles) {
                $row.remove();
            }
        });

        // Attributes JSON -> hidden input 'attributes' (server tolerant)
        const attrJson = $('textarea[name="attributes_json"]').val();
        try {
            const json = attrJson ? JSON.parse(attrJson) : {};
            if ($('input[name="attributes"]').length) {
                $('input[name="attributes"]').val(JSON.stringify(json));
            } else {
                $('<input>').attr({type:'hidden',name:'attributes',value:JSON.stringify(json)}).appendTo('#productForm');
            }
        } catch (err) {
            e.preventDefault();
            toastError('Atribut tidak valid JSON');
            return false;
        }

        // allow normal submit to continue
        return true;
    });

    // -------------- Show server validation errors (only after load) --------------
    if (window.serverValidationErrors && Array.isArray(window.serverValidationErrors)) {
        window.serverValidationErrors.forEach(err => toastError(err));
    }
});
