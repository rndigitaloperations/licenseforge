document.getElementById('menu-toggle').addEventListener('click', function() {
  const sidebar = document.getElementById('sidebar')
  if (!sidebar) return
  if (sidebar.classList.contains('-translate-x-full')) {
    sidebar.classList.remove('-translate-x-full')
    sidebar.classList.add('translate-x-0')
  } else {
    sidebar.classList.remove('translate-x-0')
    sidebar.classList.add('-translate-x-full')
  }
})