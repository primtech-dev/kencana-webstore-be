import $ from 'jquery';
import { initChoices } from '../../../utils/choices-helper';
import { initQuillEditor, getQuillContent } from '../../../utils/quill-helper';

let tagsChoices;
let editor;

// Generate SEO URL from title
function generateSeoUrl(title) {
    return title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

// Auto-fill meta title from title
function autoFillMetaTitle(title) {
    const metaTitle = $('#meta_title');
    if (!metaTitle.val()) {
        metaTitle.val(title);
    }
}

// Preview image
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            $('#preview-img').attr('src', e.target.result);
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
            if (window.toast) {
                window.toast.error('Format file tidak valid. Gunakan JPG, JPEG, PNG, atau WEBP');
            }
            input.value = '';
            $('#image-preview').hide();
            return false;
        }

        if (fileSize > 2) {
            if (window.toast) {
                window.toast.error('Ukuran file terlalu besar. Maksimal 2MB');
            }
            input.value = '';
            $('#image-preview').hide();
            return false;
        }

        return true;
    }
}

$(function() {
    // Initialize Quill Editor with custom height
    initQuillEditor('#content', {
        height: '300px',
        placeholder: 'Tulis konten artikel Anda di sini...'
    })
        .then(editorInstance => {
            editor = editorInstance;
        })
        .catch(error => {
            console.error('Failed to initialize Quill:', error);
            if (window.toast) {
                window.toast.error('Gagal memuat editor');
            }
        });

    // Initialize Choices.js for tags
    tagsChoices = initChoices('#tags', {
        searchEnabled: true,
        removeItemButton: true,
        searchPlaceholderValue: 'Cari tag...',
        itemSelectText: '',
        shouldSort: false,
        placeholderValue: 'Pilih tag untuk artikel',
        noResultsText: 'Tag tidak ditemukan',
        noChoicesText: 'Tidak ada tag tersedia',
    })[0];

    // Auto-generate SEO URL from title
    $('#title').on('input', function() {
        const title = $(this).val();
        const seoUrl = generateSeoUrl(title);

        $('#seo_url').val(seoUrl);
        autoFillMetaTitle(title);
    });

    // Manual SEO URL editing
    $('#seo_url').on('input', function() {
        const value = $(this).val();
        const cleaned = generateSeoUrl(value);
        $(this).val(cleaned);
    });

    // Image preview
    $('#image').on('change', function() {
        if (validateImage(this)) {
            previewImage(this);
        }
    });

    // Auto-suggest image alt text from title
    $('#title').on('blur', function() {
        const imageAltText = $('#image_alt_text');
        if (!imageAltText.val()) {
            imageAltText.val($(this).val());
        }
    });

    // Add character counter to meta description
    if ($('#meta_description').length) {
        $('#meta_description').after(
            '<div class="form-text"><span id="meta_description_counter" class="text-muted">0/160 karakter (optimal)</span></div>'
        );

        $('#meta_description').on('input', function() {
            const currentLength = $(this).val().length;
            const counter = $('#meta_description_counter');
            counter.text(`${currentLength}/160 karakter (optimal)`);

            if (currentLength > 160) {
                counter.removeClass('text-muted').addClass('text-warning');
            } else if (currentLength > 140) {
                counter.removeClass('text-muted text-warning').addClass('text-success');
            } else {
                counter.removeClass('text-warning text-success').addClass('text-muted');
            }
        });
    }

    // Add character counter to meta title
    if ($('#meta_title').length) {
        $('#meta_title').after(
            '<div class="form-text"><span id="meta_title_counter" class="text-muted">0/60 karakter (optimal)</span></div>'
        );

        $('#meta_title').on('input', function() {
            const currentLength = $(this).val().length;
            const counter = $('#meta_title_counter');
            counter.text(`${currentLength}/60 karakter (optimal)`);

            if (currentLength > 60) {
                counter.removeClass('text-muted').addClass('text-warning');
            } else if (currentLength > 50) {
                counter.removeClass('text-muted text-warning').addClass('text-success');
            } else {
                counter.removeClass('text-warning text-success').addClass('text-muted');
            }
        });
    }

    // Form validation before submit
    $('#articleForm').on('submit', function(e) {
        const tags = $('#tags').val();

        if (!tags || tags.length === 0) {
            e.preventDefault();
            if (window.toast) {
                window.toast.error('Silakan pilih minimal satu tag');
            }
            return false;
        }

        // Ensure Quill content is saved to textarea
        if (editor) {
            $('#content').val(getQuillContent(editor));
        }

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
    });

    // Trigger counters on page load if there's old input
    if ($('#meta_description').val()) {
        $('#meta_description').trigger('input');
    }
    if ($('#meta_title').val()) {
        $('#meta_title').trigger('input');
    }
});
