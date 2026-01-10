document.querySelectorAll('.toggle-password').forEach(function(el) {
    el.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input[type="password"], input[type="text"]');
        if (input.type === 'password') {
            input.type = 'text';
            this.textContent = '🙈';
        } else {
            input.type = 'password';
            this.textContent = '👁️';
        }
    });
});