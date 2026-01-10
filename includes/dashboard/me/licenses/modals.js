const editModal = document.getElementById('edit-modal');
const editForm = document.getElementById('edit-form');

document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.dataset.id;
        const domainOrIp = button.dataset.domainOrIp;

        document.getElementById('edit-id').value = id;

        if (domainOrIp !== null && domainOrIp !== undefined) {
            document.getElementById('edit-domain-or-ip').value = domainOrIp;
        } else {
            document.getElementById('edit-domain-or-ip').value = '';
        }

        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
    });
});

document.getElementById('edit-cancel').addEventListener('click', () => {
    editModal.classList.add('hidden');
    editModal.classList.remove('flex');
});