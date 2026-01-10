const editModal = document.getElementById('edit-modal');
const createModal = document.getElementById('create-modal');
const editForm = document.getElementById('edit-form');
const createForm = document.getElementById('create-form');

document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const image_url = button.dataset.image_url;
        const name = button.dataset.name;
        const description = button.dataset.description;

        document.getElementById('edit-id').value = id;
        document.getElementById('edit-image_url').value = image_url;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-description').value = description;

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