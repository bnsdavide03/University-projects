# 📄 Relazione Tecnica – Progetto *Quiz Online*

## 📌 Introduzione

Il progetto *Quiz Online* è stato sviluppato in due fasi distinte:

1. **Implementazione dell'applicazione web**, secondo i requisiti iniziali, utilizzando **PHP** e **MariaDB**, seguendo un approccio full-stack autonomo.
2. **Migrazione dell'intero sistema informativo** verso un'infrastruttura basata su **Django (Python)** e **PostgreSQL**, orchestrata tramite una **Servlet Java**.

&nbsp;

## 🧱 Scelte Progettuali

### 🔹 **Fase 2 – Migrazione verso PostgreSQL**

#### ✅ **Orchestrazione centralizzata via Servlet Java**

La migrazione è stata gestita tramite una **Servlet Java** che ha il ruolo di coordinatore. La servlet:

1. Richiede l'avvio della migrazione
2. Recupera i dati via HTTP da PHP
3. Invia i dati al backend Django via POST

Questa strategia ha permesso di automatizzare la procedura mantenendo i due sistemi disaccoppiati e indipendenti.

#### ✅ **Formato di interscambio JSON standardizzato**

Tutti i dati migrati vengono esportati da PHP in formato **JSON strutturato**, con una risposta coerente del tipo:

```json
{
  "success": true,
  "data": [...],
  "message": "..."
}
```

Il formato comune ha semplificato la serializzazione, il parsing e il logging durante la migrazione.

#### ✅ **Importazione in Django**

L’API Django riceve i dati ed esegue operazioni usando il metodo `update_or_create`. Questo garantisce che la stessa migrazione possa essere eseguita più volte senza creare duplicati, migliorando l’affidabilità.

#### ✅ **Mappatura dinamica degli ID**

Poiché gli ID nei due sistemi non coincidono, Django mantiene una **mappa temporanea in memoria** (`original_to_django_ids`) per tracciare la corrispondenza tra gli ID originali (MariaDB) e i nuovi ID (PostgreSQL).

Questa scelta assicura la corretta ricostruzione delle relazioni tra entità (es. domande appartenenti a quiz, partecipazioni di utenti, risposte utente).

#### ✅ **Struttura Django**

Il backend Django segue la stessa logica modulare:

* Un’app `importer` dedicata alla migrazione
* Un endpoint per ogni entità (`/users/`, `/quizzes/`, ecc.)
* Un endpoint speciale `/clear_id_map/` per il reset della mappatura

&nbsp;