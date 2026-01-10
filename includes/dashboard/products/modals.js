const editModal = document.getElementById('edit-modal');
const createModal = document.getElementById('create-modal');
const editForm = document.getElementById('edit-form');
const createForm = document.getElementById('create-form');

document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const category_id = button.dataset.category_id
        const image_url = button.dataset.image_url;
        const name = button.dataset.name;
        const description = button.dataset.description;
        const price = button.dataset.price.replace('.', ',');
        const status = button.dataset.status;
        const domain_or_ip = button.dataset.domainOrIp;
        const type = button.dataset.type;

        document.getElementById('edit-id').value = id;
        document.getElementById('edit-category_id').value = category_id;
        document.getElementById('edit-image_url').value = image_url;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-price').value = price;
        document.getElementById('edit-status').value = status;
        document.getElementById('edit-domain-or-ip').value = domain_or_ip;
        document.getElementById('edit-type').value = type;

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