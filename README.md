# PHP Session Curl Scraper / Estrattore Sessione PHP

Uno script PHP da linea di comando che simula la navigazione web con **gestione di sessione e cookie**, permettendo di:

* Effettuare login su un sito (via POST, opzionale)
* Accedere successivamente a una seconda pagina con la stessa sessione
* Estrarre uno o più elementi HTML dal contenuto della pagina
* Restituire il risultato in formato JSON

---

## 🇮🇹 Istruzioni in Italiano

### 🧰 Requisiti

* PHP 7.4 o superiore
* Estensione `curl` abilitata
* Accesso da terminale (CLI)

### 🚀 Utilizzo

Esegui da terminale:

```bash
php php-session-curl-scraper.php \
  --login-url="https://example.com/login.php" \
  --login-fields="username=myuser&password=mypass" \
  --page2-url="https://example.com/area/protected.php" \
  --selectors="#title,.info,h2" \
  --user-agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
```

#### 🔹 Parametri

| Parametro        | Descrizione                                                                                                  |
| ---------------- | ------------------------------------------------------------------------------------------------------------ |
| `--login-url`    | URL della pagina di login (oppure la homepage se non serve login)                                            |
| `--login-fields` | Campi POST da inviare al form di login (es. `username=user&password=pass`). Se vuoto, il login viene saltato |
| `--page2-url`    | URL della seconda pagina da cui estrarre i dati                                                              |
| `--selectors`    | Elenco di selettori CSS separati da virgola (`#id`, `.class`, `tag`)                                         |
| `--user-agent`   | (Opzionale) User-Agent da simulare                                                                           |

### 🧩 Esempi

#### Esempio con login

```bash
php php-session-curl-scraper.php \
  --login-url="https://example.com/login.php" \
  --login-fields="username=john&password=1234" \
  --page2-url="https://example.com/dashboard.php" \
  --selectors="#welcome,.user-name,h1"
```

#### Esempio senza login

```bash
php php-session-curl-scraper.php \
  --login-url="https://example.com/" \
  --login-fields="" \
  --page2-url="https://example.com/about.php" \
  --selectors="#content,.price"
```

### 📦 Output JSON di esempio

```json
{
  "success": true,
  "values": {
    "#title": ["Benvenuto"],
    ".info": ["Utente loggato", "Ultimo accesso: oggi"],
    "h1": ["Dashboard"]
  },
  "cookies": {
    "PHPSESSID": "abc123xyz",
    "user": "john_doe"
  },
  "error": null
}
```

### ⚠️ Note di Sicurezza

* **Non** includere credenziali reali nei file caricati su GitHub.
* Per uso pubblico, utilizza variabili d’ambiente o un file `.env` ignorato da Git (`.gitignore`).
* Lo script non segue link JavaScript o form complessi, ma può essere adattato facilmente.

---

## 🇬🇧 English Instructions

### 🧰 Requirements

* PHP 7.4 or higher
* `curl` extension enabled
* Access from command line (CLI)

### 🚀 Usage

Run from terminal:

```bash
php php-session-curl-scraper.php \
  --login-url="https://example.com/login.php" \
  --login-fields="username=myuser&password=mypass" \
  --page2-url="https://example.com/area/protected.php" \
  --selectors="#title,.info,h2" \
  --user-agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
```

#### 🔹 Parameters

| Parameter        | Description                                                                                 |
| ---------------- | ------------------------------------------------------------------------------------------- |
| `--login-url`    | Login page URL (or homepage if no login is required)                                        |
| `--login-fields` | POST fields for login form (e.g. `username=user&password=pass`). If empty, login is skipped |
| `--page2-url`    | URL of the second page to extract data from                                                 |
| `--selectors`    | Comma-separated list of CSS selectors (`#id`, `.class`, `tag`)                              |
| `--user-agent`   | (Optional) User-Agent to simulate                                                           |

### 🧩 Examples

#### Example with login

```bash
php php-session-curl-scraper.php \
  --login-url="https://example.com/login.php" \
  --login-fields="username=john&password=1234" \
  --page2-url="https://example.com/dashboard.php" \
  --selectors="#welcome,.user-name,h1"
```

#### Example without login

```bash
php php-session-curl-scraper.php \
  --login-url="https://example.com/" \
  --login-fields="" \
  --page2-url="https://example.com/about.php" \
  --selectors="#content,.price"
```

### 📦 Example JSON Output

```json
{
  "success": true,
  "values": {
    "#title": ["Welcome"],
    ".info": ["User logged in", "Last access: today"],
    "h1": ["Dashboard"]
  },
  "cookies": {
    "PHPSESSID": "abc123xyz",
    "user": "john_doe"
  },
  "error": null
}
```

### ⚠️ Security Notes

* **Do not** include real credentials in public GitHub repositories.
* Use environment variables or `.env` files excluded with `.gitignore` for sensitive data.
* The script does not follow JavaScript links or dynamic forms but can be easily customized.

---

## 📄 Licenza / License

Rilasciato sotto licenza **MIT**. / Released under the **MIT License**.

