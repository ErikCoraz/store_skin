document.addEventListener('DOMContentLoaded', function () {                  // Script per gestire la dark mode
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

setTimeout(() => {                                                   // Script per rimuovere i messaggi di successo dopo 3 secondi
    document.querySelector('.success').classList.add('fade-out');
    setTimeout(() => document.querySelector('.success').remove(), 500);
}, 3000);

function toggleChiSiamo() {                                      // Funzione per mostrare/nascondere il contenuto della sezione "Chi Siamo"
    const content = document.getElementById("chi-siamo-content");
    const freccia = document.getElementById("freccia");

    if (content.style.display === "block") {
        content.style.display = "none";
        freccia.style.transform = "rotate(0deg)";
    } else {
        content.style.display = "block";
        freccia.style.transform = "rotate(90deg)";
    }
}