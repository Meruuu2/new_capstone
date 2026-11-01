import './bootstrap';
import './reservation-calendar';

let confirmedForm = null;

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (confirmedForm === form) {
        confirmedForm = null;
        return;
    }

    if (!form.hasAttribute('data-confirm')) {
        return;
    }

    const dialog = document.getElementById('ua-confirm-dialog');
    if (!(dialog instanceof HTMLDialogElement)) {
        return;
    }

    event.preventDefault();

    const title = dialog.querySelector('[data-confirm-title]');
    const message = dialog.querySelector('[data-confirm-message]');
    const confirmButton = dialog.querySelector('[data-confirm-submit]');
    const cancelButton = dialog.querySelector('[data-confirm-cancel]');
    if (!(title instanceof HTMLElement) || !(message instanceof HTMLElement)
        || !(confirmButton instanceof HTMLButtonElement) || !(cancelButton instanceof HTMLButtonElement)) {
        return;
    }

    const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
    const previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    title.textContent = form.dataset.confirmTitle || 'Please confirm';
    message.textContent = form.dataset.confirm;
    confirmButton.textContent = form.dataset.confirmAction || 'Continue';
    dialog.dataset.tone = form.dataset.confirmTone || 'default';
    dialog.showModal();
    cancelButton.focus();

    const close = () => {
        dialog.close();
        previousFocus?.focus();
    };

    cancelButton.onclick = close;
    confirmButton.onclick = () => {
        if (!form.isConnected || !form.reportValidity()) {
            close();
            return;
        }

        dialog.close();
        confirmedForm = form;
        form.requestSubmit(submitter instanceof HTMLButtonElement || submitter instanceof HTMLInputElement
            ? submitter
            : undefined);
    };

    dialog.oncancel = () => previousFocus?.focus();
    dialog.onclick = (clickEvent) => {
        if (clickEvent.target === dialog) {
            close();
        }
    };
});
