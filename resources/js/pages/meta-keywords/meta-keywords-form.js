import $ from 'jquery';

function toastError(msg) {
    if (window.toast && typeof window.toast.error === 'function') {
        window.toast.error(msg);
    } else {
        console.warn('Toast not available:', msg);
    }
}

function simpleSlugify(str) {
    if (!str) return '';
    return str.toString()
        .normalize('NFKD')
        .replace(/[^\w\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .toLowerCase();
}

$(function () {
    const $name = $('#name');
    const $slug = $('#slug');
    const $form = $('#metaKeywordForm');

    $name.on('input', function () {
        const nameVal = $(this).val().trim();
        if (!$slug.length) return;
        if (!$slug.val().trim()) {
            $slug.val(simpleSlugify(nameVal));
        }
    });

    $form.on('submit', function (e) {
        const nameVal = $name.val().trim();
        const slugVal = $slug.val().trim();

        if (!nameVal) {
            e.preventDefault();
            toastError('Nama meta keyword harus diisi.');
            $name.focus();
            return false;
        }

        if (slugVal && !/^[a-z0-9\-]+$/.test(slugVal)) {
            e.preventDefault();
            toastError('Slug hanya boleh huruf kecil, angka, dan tanda minus (-).');
            $slug.focus();
            return false;
        }

        const $btn = $(this).find('button[type="submit"]').first();
        $btn.prop('disabled', true);
        $btn.data('orig-html', $btn.html());
        $btn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...`);
        return true;
    });

    if (typeof window.serverValidationErrors !== 'undefined' && Array.isArray(window.serverValidationErrors)) {
        window.serverValidationErrors.forEach(err => toastError(err));
    }
});
