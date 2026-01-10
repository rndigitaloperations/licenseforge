const editModal = document.getElementById('edit-modal');
const createModal = document.getElementById('create-modal');

document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const user_id = button.dataset.user_id;
        const product_id = button.dataset.product_id;
        const suspended = button.dataset.suspended === 'true';

        document.getElementById('edit-id').value = id;
        document.getElementById('edit-user_id').value = user_id;
        document.getElementById('edit-product_id').value = product_id;
        document.getElementById('edit-suspended').checked = suspended;

        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
    });
});

document.getElementById('edit-cancel').addEventListener('click', () => {
    editModal.classList.add('hidden');
    editModal.classList.remove('flex');
});

document.getElementById('open-create-btn').addEventListener('click', () => {
    createModal.classList.remove('hidden');
    createModal.classList.add('flex');
});

document.getElementById('create-cancel').addEventListener('click', () => {
    createModal.classList.add('hidden');
    createModal.classList.remove('flex');
});