const tableBody = document.querySelector('#category-table tbody');
const saveOrderBtn = document.getElementById('save-order');
let draggedRow = null;

tableBody.querySelectorAll('tr').forEach(row => {
    row.draggable = false;

    const handle = row.querySelector('.drag-handle');
    if (!handle) return;

    handle.style.cursor = 'move';

    handle.addEventListener('mousedown', () => {
        row.draggable = true;
    });
    handle.addEventListener('mouseup', () => {
        row.draggable = true;
    });

    row.addEventListener('dragstart', (e) => {
        if (!row.draggable) {
            e.preventDefault();
            return false;
        }
        draggedRow = row;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', row.outerHTML);
        setTimeout(() => {
            row.classList.add('opacity-50');
        }, 0);
    });

    row.addEventListener('dragend', () => {
        draggedRow.classList.remove('opacity-50');
        draggedRow = null;
        saveOrderBtn.classList.remove('hidden');
        row.draggable = false;
    });

    row.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    });

    row.addEventListener('drop', (e) => {
        e.preventDefault();
        if (draggedRow !== row) {
            const draggedIndex = Array.from(tableBody.children).indexOf(draggedRow);
            const targetIndex = Array.from(tableBody.children).indexOf(row);

            if (draggedIndex < targetIndex) {
                row.after(draggedRow);
            } else {
                row.before(draggedRow);
            }
            saveOrderBtn.classList.remove('hidden');
        }
    });
});

saveOrderBtn.addEventListener('click', () => {
    const orders = Array.from(tableBody.children).map((row, index) => {
        return {
            id: row.getAttribute('data-id'),
            order_id: index + 1
        };
    });

    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=reorder&orders=' + encodeURIComponent(JSON.stringify(orders)),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            saveOrderBtn.classList.add('hidden');
            alert('Order saved!');
        } else {
            alert('Failed to save order.');
        }
    });
});