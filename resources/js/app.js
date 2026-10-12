const menuToggle = document.querySelector('[data-menu-toggle]');
menuToggle?.addEventListener('click', () => {
    const expanded = menuToggle.getAttribute('aria-expanded') !== 'true';
    menuToggle.setAttribute('aria-expanded', String(expanded));
    document.getElementById('workspace-nav').classList.toggle('hidden', !expanded);
});

const deleteDialog = document.getElementById('delete-dialog');
let pendingDeleteForm = null;

document.querySelectorAll('[data-delete-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        pendingDeleteForm = form;
        deleteDialog.showModal();
    });
});

deleteDialog?.querySelector('[data-delete-cancel]').addEventListener('click', () => deleteDialog.close());
deleteDialog?.querySelector('[data-delete-confirm]').addEventListener('click', () => {
    pendingDeleteForm?.submit();
});
deleteDialog?.addEventListener('close', () => {
    pendingDeleteForm = null;
});
