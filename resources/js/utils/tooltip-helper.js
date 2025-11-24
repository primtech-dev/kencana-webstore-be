import bootstrap from 'bootstrap/dist/js/bootstrap';

/**
 * Initialize Bootstrap Tooltips
 * Call this function after dynamic content is loaded
 */
export function initTooltips() {
    // Dispose existing tooltips first to avoid duplicates
    const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    existingTooltips.forEach(element => {
        const tooltipInstance = bootstrap.Tooltip.getInstance(element);
        if (tooltipInstance) {
            tooltipInstance.dispose();
        }
    });

    // Initialize new tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    return [...tooltipTriggerList].map(tooltipTriggerEl =>
        new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        })
    );
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
});
