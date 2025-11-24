import $ from 'jquery';
import { initQuillEditor, getQuillContent } from '../../../utils/quill-helper';

let contentEditor;
let termsEditor;

// Generate SEO URL from title (if needed)
function generateSeoUrl(title) {
    return title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

// Preview image
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            $('#preview-img').attr('src', e.target.result);
            $('#current-image-container').hide();
            $('#image-preview-placeholder').hide();
            $('#image-preview').show();
        };

        reader.readAsDataURL(input.files[0]);
    }
}

// Validate file size and type
function validateImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileSize = file.size / 1024 / 1024;
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

        if (!validTypes.includes(file.type)) {
            window.toast?.error('Format file tidak valid. Gunakan JPG, JPEG, PNG, atau WEBP');
            input.value = '';
            $('#image-preview').hide();
            return false;
        }

        if (fileSize > 2) {
            window.toast?.error('Ukuran file terlalu besar. Maksimal 2MB');
            input.value = '';
            $('#image-preview').hide();
            return false;
        }

        return true;
    }
}

$(function () {

    // ================================
    // INITIALIZE QUILL EDITORS
    // ================================
    initQuillEditor('#content', {
        height: '300px',
        placeholder: 'Tulis konten produk di sini...'
    })
        .then(editorInstance => {
            contentEditor = editorInstance;
        })
        .catch(error => {
            console.error('Failed to initialize Quill (content):', error);
            window.toast?.error('Gagal memuat editor konten');
        });

    initQuillEditor('#terms_and_condition', {
        height: '300px',
        placeholder: 'Tulis syarat & ketentuan produk di sini...'
    })
        .then(editorInstance => {
            termsEditor = editorInstance;
        })
        .catch(error => {
            console.error('Failed to initialize Quill (terms):', error);
            window.toast?.error('Gagal memuat editor S&K');
        });

    // ================================
    // IMAGE PREVIEW
    // ================================
    $('#image').on('change', function () {
        if (validateImage(this)) {
            previewImage(this);
        }
    });

    // ================================
    // FORM SUBMIT
    // ================================
    $('#productForm').on('submit', function () {

        // Save content editor data
        if (contentEditor) {
            $('#content').val(getQuillContent(contentEditor));
        }

        // Save terms editor data
        if (termsEditor) {
            $('#terms_and_condition').val(getQuillContent(termsEditor));
        }

        // Loading button
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
    });
});
