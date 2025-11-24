import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import bootstrap from 'bootstrap/dist/js/bootstrap';
import { showDeleteModal } from '../../utils/delete-modal-helper';
import { initTooltips } from '../../utils/tooltip-helper';

// Preview image for create form
function previewCreateImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            $('#create_preview_img').attr('src', e.target.result);
            $('#create_image_preview_placeholder').hide();
            $('#create_image_preview').show();
        };

        reader.readAsDataURL(input.files[0]);
    }
}

// Preview image for edit form
function previewEditImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            $('#edit_preview_img').attr('src', e.target.result);
            $('#edit_current_image_container').hide();
            $('#edit_image_preview').show();
        };

        reader.readAsDataURL(input.files[0]);
    }
}

// Validate image
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
            return false;
        }

        if (fileSize > 2) {
            if (window.toast) {
                window.toast.error('Ukuran file terlalu besar. Maksimal 2MB');
            }
            input.value = '';
            return false;
        }

        return true;
    }
}

// Edit button handler
function btnEdit(id, name, job, rating, comment, imageSrc) {
    // Update form action URL
    const form = document.getElementById('editTestimonialForm');
    const baseAction = form.action.split('/testimonials/')[0] + '/testimonials/';
    form.action = baseAction + id;

    // Set input values
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_job').value = job;
    document.getElementById('edit_comment').value = comment;

    // Set rating stars
    const ratingInput = document.querySelector(`#edit_star${rating}`);
    if (ratingInput) {
        ratingInput.checked = true;
    }

    // Set current image
    document.getElementById('edit_current_img').src = imageSrc;
    document.getElementById('edit_current_image_container').style.display = 'block';
    document.getElementById('edit_image_preview').style.display = 'none';

    // Show modal
    const editModal = new bootstrap.Modal(document.getElementById('editTestimonialModal'));
    editModal.show();
}

// Delete button handler
function btnDelete(id, name) {
    showDeleteModal({
        modalId: 'deleteTestimonialModal',
        formId: 'deleteTestimonialForm',
        itemNameId: 'delete_testimonial_name',
        id: id,
        name: name,
        route: window.testimonialRoutes.destroy
    });
}

// Make functions globally accessible
window.btnEdit = btnEdit;
window.btnDelete = btnDelete;

// Render rating stars in table
function renderRating(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="ti ti-star-filled text-warning"></i>';
        } else {
            stars += '<i class="ti ti-star text-muted"></i>';
        }
    }
    return stars;
}

$(function() {
    // Initialize DataTable
    new DataTable('#testimonials-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: window.testimonialRoutes.index,
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
                data: 'job',
                name: 'job'
            },
            {
                data: 'rating',
                name: 'rating',
                render: function(data, type, row) {
                    return renderRating(data);
                }
            },
            {
                data: 'comment',
                name: 'comment',
                render: function(data, type, row) {
                    if (data.length > 50) {
                        return data.substring(0, 50) + '...';
                    }
                    return data;
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
            lengthMenu: '_MENU_ testimoni per halaman',
            info: 'Menampilkan <span class="fw-semibold">_START_</span> sampai <span class="fw-semibold">_END_</span> dari <span class="fw-semibold">_TOTAL_</span> testimoni',
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Memuat...</span></div>',
            search: 'Cari:',
            zeroRecords: 'Tidak ada data yang ditemukan',
            emptyTable: 'Tidak ada data tersedia',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 testimoni',
            infoFiltered: '(disaring dari _MAX_ total testimoni)'
        },
        order: [[5, 'desc']],
        drawCallback: function() {
            initTooltips();
        }
    });

    // Image preview handlers
    $('#create_image').on('change', function() {
        if (validateImage(this)) {
            previewCreateImage(this);
        }
    });

    $('#edit_image').on('change', function() {
        if (validateImage(this)) {
            previewEditImage(this);
        }
    });

    // Reset create form on modal close
    $('#createTestimonialModal').on('hidden.bs.modal', function() {
        $('#createTestimonialForm')[0].reset();
        $('#create_image_preview').hide();
        $('#create_image_preview_placeholder').show();
        $('#create_star_rating input[type="radio"]').prop('checked', false);
    });

    // Initialize tooltips
    initTooltips();
});
