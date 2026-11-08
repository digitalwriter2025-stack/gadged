/**
 * PC builder page: print, "Hide Unconfigured Components", the clear-build
 * confirmation, and auto-submitting the sort select on the choose page.
 */
export function initPcBuilder() {
    document.querySelectorAll('select[data-autosubmit]').forEach((select) => {
        select.addEventListener('change', () => select.form?.requestSubmit());
    });

    const page = document.querySelector('[data-pc-builder]');
    if (!page) {
        return;
    }

    page.querySelector('[data-print]')?.addEventListener('click', () => window.print());

    page.querySelector('[data-hide-unconfigured]')?.addEventListener('change', (event) => {
        page.querySelectorAll('[data-unconfigured]').forEach((row) => {
            row.hidden = event.target.checked;
        });
    });

    page.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });
}
