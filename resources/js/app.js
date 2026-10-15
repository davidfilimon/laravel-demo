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
        const clearingCompleted = form.hasAttribute('data-clear-completed');
        deleteDialog.querySelector('#delete-title').textContent = clearingCompleted ? 'Clear completed tasks?' : 'Delete this task?';
        deleteDialog.querySelector('#delete-description').textContent = clearingCompleted
            ? 'This will permanently remove all completed tasks. Pending tasks will be kept. You can’t undo this action.'
            : 'This will permanently remove the task. You can’t undo this action.';
        deleteDialog.querySelector('[data-delete-cancel]').textContent = clearingCompleted ? 'Keep tasks' : 'Keep task';
        deleteDialog.querySelector('[data-delete-confirm]').textContent = clearingCompleted ? 'Clear completed tasks' : 'Delete task';
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

document.querySelector('[data-task-sort]')?.addEventListener('change', (event) => {
    event.target.form.requestSubmit();
});
