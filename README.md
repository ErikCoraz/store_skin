# Store di Skin di League of Legends

Questo progetto è uno store online per la vendita di **skin di League of Legends**, sviluppato per un compito scolastico multidisciplinare.

## Tecnologie utilizzate

* **HTML/CSS** per il frontend
* **PHP** per la logica lato server
* **MySQL** (gestito tramite HeidiSQL) per la base dati
* **Apache** come server web locale&#x20;
* **Git** e **GitHub** per il controllo versione

## Struttura del progetto

```
store-skin/
├── index.php                 # Homepage con carrellata di skin e filtri
├── login.php                 # Login utente e admin
├── register.php              # Registrazione utenti
├── logout.php                # Logout
├── cart.php                  # Carrello utenti
│
├── admin/                   # Area admin
│   ├── dashboard.php
│   ├── add_skin.php
│   ├── edit_skin.php
│   └── delete_skin.php
│
├── includes/                # File riutilizzabili
│   ├── db.php               # Connessione DB
│   └── auth.php             # Gestione sessioni e login
│
├── assets/
│   ├── css/style.css        # Stili personalizzati
│   └── img/                 # Immagini delle skin
│
├── sql/esportazione.sql     # Esportazione struttura + contenuto DB
├── README.md                # Spiegazione del progetto
├── .gitignore               # File ignorati da Git
└── LICENSE (facoltativo)
```

## Funzionalità principali

* Login/Logout per utenti e admin
* Registrazione utenti con password hashata
* Visualizzazione skin nella homepage
* Filtri e ricerca per skin
* Carrello funzionante solo per utenti registrati
* Aggiunta, modifica e cancellazione skin lato admin (via interfaccia)
* Skin "Out of Stock" quando esaurite

## Account admin di default

* **Username**: `admin`
* **Password**: `admin123`

## Istruzioni di installazione

1. Clonare il progetto in `htdocs/`

   ```bash
   git clone https://github.com/ErikCoraz/progetto.git
   ```
2. Avviare Apache e MySQL
3. Importare il file `sql/esportazione.sql` in un nuovo database chiamato `store_skin`
4. Visitare `http://localhost/store-skin/`

---

**Progetto scolastico** realizzato da Erik Corazza, Diego Cicognani, Giovanni Colori- 2025
