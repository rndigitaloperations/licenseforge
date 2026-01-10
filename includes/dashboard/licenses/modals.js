const editModal = document.getElementById('edit-modal');
const createModal = document.getElementById('create-modal');
const editForm = document.getElementById('edit-form');
const createForm = document.getElementById('create-form');

document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const owner = button.dataset.owner;
        const domainOrIp = button.dataset.domainOrIp;
        const status = button.dataset.status;
        const productId = button.dataset.productId;

        document.getElementById('edit-id').value = id;
        document.getElementById('edit-owner').value = owner;

        if (domainOrIp !== null && domainOrIp !== undefined) {
            document.getElementById('edit-domain-or-ip').value = domainOrIp;
        } else {
            document.getElementById('edit-domain-or-ip').value = '';
        }

        document.getElementById('edit-product').value = productId;
        document.getElementById('edit-status').value = status;

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