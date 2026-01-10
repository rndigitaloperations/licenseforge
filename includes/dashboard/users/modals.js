const editModal = document.getElementById('edit-modal');
const createModal = document.getElementById('create-modal');
const editForm = document.getElementById('edit-form');
const createForm = document.getElementById('create-form');

document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const username = button.dataset.username;
        const email = button.dataset.email;
        const role = button.dataset.role;

        document.getElementById('edit-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-username').value = username;
        document.getElementById('edit-email').value = email;
        document.getElementById('edit-role').value = role;

        document.getElementById('edit-password').value = '';

        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
    });
});

document.getElementById('edit-cancel').addEventListener('click', () => {
    editModal.classList.add('hidden');
    editModal.classList.remove('flex');
});

document.getElementById('open-create-btn').addEventListener('click', () => {
    document.getElementById('create-name').value = '';
    document.getElementById('create-username').value = '';
    document.getElementById('create-email').value = '';
    document.getElementById('create-password').value = '';
    document.getElementById('create-role').value = 'user';

    createModal.classList.remove('hidden');
    createModal.classList.add('flex');
});

document.getElementById('create-cancel').addEventListener('click', () => {
    createModal.classList.add('hidden');
    createModal.classList.remove('flex');
});