document.getElementById('menu-toggle').addEventListener('click', function() {
    var menu = document.getElementById('menu');
    if (menu.classList.contains('hidden')) {
      menu.classList.remove('hidden');
    } else {
      menu.classList.add('hidden');
    }
  });

  document.getElementById('store-dropdown-toggle').addEventListener('click', function(event) {
    event.stopPropagation();
    var dropdown = document.getElementById('store-dropdown');
    if (dropdown.classList.contains('hidden')) {
      dropdown.classList.remove('hidden');
    } else {
      dropdown.classList.add('hidden');
    }
  });

  document.getElementById('user-dropdown-toggle').addEventListener('click', function(event) {
    event.stopPropagation();
    var dropdown = document.getElementById('user-dropdown');
    if (dropdown.classList.contains('hidden')) {
      dropdown.classList.remove('hidden');
    } else {
      dropdown.classList.add('hidden');
    }
  });

  document.addEventListener('click', function(event) {
    var storeDropdown = document.getElementById('store-dropdown');
    var userDropdown = document.getElementById('user-dropdown');
    var storeDropdownToggle = document.getElementById('store-dropdown-toggle');
    var userDropdownToggle = document.getElementById('user-dropdown-toggle');

    if (window.innerWidth < 768) {
      if (!storeDropdownToggle || !storeDropdownToggle.contains(event.target)) {
        if (storeDropdown && !storeDropdown.contains(event.target)) {
          storeDropdown.classList.add('hidden');
        }
      }

      if (!userDropdownToggle || !userDropdownToggle.contains(event.target)) {
        if (userDropdown && !userDropdown.contains(event.target)) {
          userDropdown.classList.add('hidden');
        }
      }
    }
  });