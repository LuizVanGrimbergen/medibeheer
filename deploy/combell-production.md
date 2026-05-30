# Medibeheer op Combell (productie)

Gids voor achtergrondtaken op shared hosting: scheduler (fase 1) en database-queue (fase 2).

## Vereisten

- PHP **8.3**
- Document root wijst naar `public/`, **of** een root `.htaccess` die alles naar `public/` doorstuurt
- `.env` met Combell database- en mailgegevens
- `QUEUE_CONNECTION=database` in `.env`
- `composer install --no-dev`, `npm ci && npm run build`, `php artisan migrate --force`

## Fase 1 — Scheduler (verplicht)

Medicatie-pushherinneringen en privacy-opruiming draaien via `routes/console.php`:

| Taak | Frequentie |
|------|------------|
| `patient:send-medication-due-reminders` | elke minuut |
| `privacy:purge-expired-data` | dagelijks om middernacht |

### Cronjob scheduler

Elke minuut in Combell **Cronjobs**:

```cron
* * * * * /data/sites/web/livlismondecom/subsites/medibeheer.be/scripts/run-scheduler.sh
```

Alternatief:

```cron
* * * * * cd /data/sites/web/livlismondecom/subsites/medibeheer.be && php artisan schedule:run >> /dev/null 2>&1
```

## Fase 2 — Database queue (verplicht voor async mail)

Async mail (bijv. “uitnodiging geaccepteerd”) gebruikt de database-queue. Zonder queue worker blijven jobs in de `jobs`-tabel staan.

### Cronjob queue worker

Elke minuut, **naast** de scheduler-cron:

```cron
* * * * * /data/sites/web/livlismondecom/subsites/medibeheer.be/scripts/run-queue-worker.sh
```

Alternatief:

```cron
* * * * * cd /data/sites/web/livlismondecom/subsites/medibeheer.be && php artisan queue:work database --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

### Welke mail is sync vs async?

| Mail | Verzending | Reden |
|------|------------|-------|
| Uitnodiging (family/doctor) | **Sync** (`sendNow`) | Directe foutmelding bij SMTP-probleem; uitnodiging wordt teruggedraaid |
| Medicatieplan-voorstel | **Sync** (`sendNow`) | Idem |
| Uitnodiging geaccepteerd | **Async** (`queue`) | Blokkeert acceptatie-flow niet |

Alleen acceptatie-notificaties implementeren `ShouldQueue` en gaan via `queue()`. Uitnodigingen en medicatieplan-voorstellen gebruiken `sendNow()` zonder `ShouldQueue`.

### Lokaal

`composer run dev` start `php artisan queue:listen` automatisch mee.

## Controleren

```bash
cd /data/sites/web/livlismondecom/subsites/medibeheer.be

php artisan schedule:list
php artisan schedule:run
php artisan queue:monitor database:default
php artisan queue:failed
curl -I https://medibeheer.be/up
```

## Veelvoorkomende problemen

| Symptoom | Oplossing |
|----------|-----------|
| Geen push-herinneringen | Scheduler-cron ontbreekt |
| Acceptatie-mail komt niet aan | Queue worker-cron ontbreekt; check `jobs` / `failed_jobs` |
| Jobs stapelen op | Queue worker-cron elke minuut; check `php artisan queue:failed` |
| DB connection refused | Combell `DB_HOST` (*.db.webhosting.be), niet `127.0.0.1` |

## Volgende fases

- **Fase 3:** Spatie Laravel Health
- **Fase 4:** VPS + Supervisor (Combell VPS of Hetzner)
