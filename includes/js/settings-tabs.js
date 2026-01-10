function getTabFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return params.get('tab');
}
document.addEventListener('DOMContentLoaded', () => {
    const tabFromUrl = getTabFromUrl();
    const defaultTab = 'payments';
    const activeTabId = tabFromUrl || defaultTab;
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    const tabToActivate = document.querySelector(`.tab[data-tab="${activeTabId}"]`);
    const paneToActivate = document.getElementById(activeTabId);
    if (tabToActivate && paneToActivate) {
        tabToActivate.classList.add('active');
        paneToActivate.classList.add('active');
    } else {
        document.querySelector(`.tab[data-tab="${defaultTab}"]`).classList.add('active');
        document.getElementById(defaultTab).classList.add('active');
    }
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const tabId = tab.getAttribute('data-tab');
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(tabId).classList.add('active');
            history.replaceState(null, '', `?tab=${tabId}`);
        });
    });
});