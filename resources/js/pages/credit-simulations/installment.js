import $ from 'jquery';

let rowCounter = 0;

// Initialize row counter based on existing rows
$(function() {
    rowCounter = $('#installmentTableBody tr').length;

    // Attach event listeners to existing installment inputs
    attachInstallmentListeners();

    // Reindex all rows on page load
    reindexRows();
});

// Add new row for unassigned tenor
function addNewRow() {
    const template = document.getElementById('newRowTemplate');
    const clone = template.content.cloneNode(true);
    const tbody = document.getElementById('installmentTableBody');

    // Update row index
    const row = clone.querySelector('tr');
    row.setAttribute('data-row-index', rowCounter);

    // Update row number
    const rowNumber = clone.querySelector('.row-number');
    rowNumber.textContent = rowCounter + 1;

    // Update name attributes with proper index
    const inputs = clone.querySelectorAll('input, select');
    inputs.forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
            input.setAttribute('name', name.replace('[]', `[${rowCounter}]`));
        }
    });

    tbody.appendChild(clone);
    rowCounter++;

    // Attach listeners to new row
    attachInstallmentListeners();

    // Update select options to hide already selected tenors
    updateTenorSelectOptions();
}

// Remove row
function removeRow(button) {
    const row = button.closest('tr');

    // Check if it's the last row
    const tbody = document.getElementById('installmentTableBody');
    const rows = tbody.querySelectorAll('tr');

    if (rows.length === 1) {
        if (window.toast) {
            window.toast.error('Minimal harus ada satu tenor');
        }
        return;
    }

    row.remove();
    reindexRows();
    updateTenorSelectOptions();
}

// Reindex all rows after add/remove
function reindexRows() {
    const tbody = document.getElementById('installmentTableBody');
    const rows = tbody.querySelectorAll('tr');

    rows.forEach((row, index) => {
        // Update row number
        const rowNumber = row.querySelector('.row-number, td:first-child');
        if (rowNumber) {
            rowNumber.textContent = index + 1;
        }

        // Update row index attribute
        row.setAttribute('data-row-index', index);

        // Update all input/select name attributes
        const inputs = row.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('installments[')) {
                const newName = name.replace(/installments\[\d+\]/, `installments[${index}]`);
                input.setAttribute('name', newName);
            }
        });
    });

    // Update counter
    rowCounter = rows.length;
}

// Attach event listeners to installment inputs and tenor selects
function attachInstallmentListeners() {
    // Tenor select change
    $('.tenor-select').off('change').on('change', function() {
        updateTenorSelectOptions();
    });
}

// Update tenor select options to hide already selected tenors
function updateTenorSelectOptions() {
    const selectedTenorIds = [];

    // Collect all selected tenor IDs (from both hidden inputs and selects)
    $('#installmentTableBody tr').each(function() {
        const hiddenInput = $(this).find('input[name*="[tenor_id]"]');
        const select = $(this).find('.tenor-select');

        if (hiddenInput.length && hiddenInput.val()) {
            selectedTenorIds.push(hiddenInput.val());
        } else if (select.length && select.val()) {
            selectedTenorIds.push(select.val());
        }
    });

    // Update all selects to disable already selected options
    $('.tenor-select').each(function() {
        const currentSelect = $(this);
        const currentValue = currentSelect.val();

        currentSelect.find('option').each(function() {
            const option = $(this);
            const optionValue = option.val();

            if (optionValue && selectedTenorIds.includes(optionValue) && optionValue !== currentValue) {
                option.prop('disabled', true).addClass('text-muted');
            } else {
                option.prop('disabled', false).removeClass('text-muted');
            }
        });
    });
}

// Format number with thousand separators
function formatNumber(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Form validation before submit
$('#installmentForm').on('submit', function(e) {
    const rows = $('#installmentTableBody tr');

    if (rows.length === 0) {
        e.preventDefault();
        if (window.toast) {
            window.toast.error('Minimal harus ada satu tenor');
        }
        return false;
    }

    // Validate all installment inputs are filled
    let hasError = false;
    rows.each(function() {
        const installmentInput = $(this).find('.installment-input');
        const tenorSelect = $(this).find('.tenor-select');

        if (installmentInput.val() === '' || parseFloat(installmentInput.val()) < 0) {
            hasError = true;
            installmentInput.addClass('is-invalid');
        } else {
            installmentInput.removeClass('is-invalid');
        }

        if (tenorSelect.length && tenorSelect.val() === '') {
            hasError = true;
            tenorSelect.addClass('is-invalid');
        } else if (tenorSelect.length) {
            tenorSelect.removeClass('is-invalid');
        }
    });

    if (hasError) {
        e.preventDefault();
        if (window.toast) {
            window.toast.error('Mohon lengkapi semua data angsuran');
        }
        return false;
    }
});

// Number input validation - only allow positive numbers
$('#installmentTableBody').on('input', '.installment-input', function() {
    // Remove any non-numeric characters except decimal point
    this.value = this.value.replace(/[^0-9.]/g, '');

    // Ensure only one decimal point
    const parts = this.value.split('.');
    if (parts.length > 2) {
        this.value = parts[0] + '.' + parts.slice(1).join('');
    }

    // Ensure positive number
    if (parseFloat(this.value) < 0) {
        this.value = '';
    }
});

// Make functions globally accessible
window.addNewRow = addNewRow;
window.removeRow = removeRow;
