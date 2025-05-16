document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggle-dark');
    if (toggle) {
        toggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? 'on' : 'off');
        });

        if (localStorage.getItem('darkMode') === 'on') {
            document.body.classList.add('dark-mode');
        }
    }
});