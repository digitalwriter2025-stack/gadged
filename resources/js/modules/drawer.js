/**
 * Wire `[data-drawer-open="id"]` triggers to native `<dialog>` drawers.
 * The dialog handles focus trapping and Escape; this adds backdrop-click and
 * close-button handling.
 */
export function initDrawers() {
    document.querySelectorAll('[data-drawer-open]').forEach((trigger) => {
        const drawer = document.getElementById(trigger.dataset.drawerOpen);
        if (!(drawer instanceof HTMLDialogElement)) {
            return;
        }

        trigger.addEventListener('click', () => {
            drawer.showModal();
            trigger.setAttribute('aria-expanded', 'true');
        });

        drawer.addEventListener('close', () => trigger.setAttribute('aria-expanded', 'false'));
    });

    document.querySelectorAll('dialog.drawer').forEach((drawer) => {
        drawer.querySelectorAll('[data-drawer-close]').forEach((button) => {
            button.addEventListener('click', () => drawer.close());
        });

        drawer.addEventListener('click', (event) => {
            if (event.target === drawer) {
                drawer.close();
            }
        });

        window.matchMedia('(min-width: 1024px)').addEventListener('change', (query) => {
            if (query.matches && drawer.open) {
                drawer.close();
            }
        });
    });
}

/**
 * Wire `[data-modal-open="id"]` triggers to native `<dialog data-modal>` popups.
 */
export function initModals() {
    document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
        const modal = document.getElementById(trigger.dataset.modalOpen);
        if (modal instanceof HTMLDialogElement) {
            trigger.addEventListener('click', () => modal.showModal());
        }
    });

    document.querySelectorAll('dialog[data-modal]').forEach((modal) => {
        modal.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => modal.close());
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.close();
            }
        });
    });
}
