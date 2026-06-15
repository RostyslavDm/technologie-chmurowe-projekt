# *Plant Care Diary — application/project description*

The application is a personal plant care tracker. The core idea is simple: a user registers,
adds their houseplants to their account, and keeps a diary of every time they water, fertilise,
or repot them. Instead of trying to remember when you last watered your monstera (a succulent, in my case),
one can open the app and it'd tell them everything.

## User's perspective

A guest visiting the site can browse a public catalogue of plant types (succulents, tropical plants,
herbs, etc.) and see general care information — but they can't do anything personal until they register.

Once logged in, a user can add their own plants to their account, giving each one a name
(e.g. "my big monstera by the window") and linking it to a plant type from the catalogue.
Every time they water or fertilise a plant, they add a care log entry — a short note with a timestamp.
Over time this builds up a history: "I watered this plant on the 1st, 8th, and 15th — looks like I'm doing
it every 7 days."

*! Search and filtering*

Users can search and filter in the plant catalogue — filtering by watering frequency or searching by name —
with pagination and AJAX so results refresh without a full page reload.

## The admin's role

The admin *never* interacts with anyone's plants. Their job is purely system management
— they maintain the plant types catalogue (adding new types, deactivating outdated ones),
and they manage user accounts and roles.

**The roles in short:**
- **Guest** — browses the public plant catalogue, registers or logs in;
- **User** — manages their own plants and care diary;
- **Admin** — manages plant types (catalogue) and user accounts.

| Action | Guest | User | Admin |
|---|---|---|---|
| Browse public plant catalogue | ✓ | ✓ | ✓ |
| Register | ✓ | — | — |
| Add own plants | — | ✓ | ✓ |
| Log watering / care entries | — | ✓ | ✓ |
| View own plant diary | — | ✓ | ✓ |
| Manage plant types (catalogue) | — | — | ✓ |
| Manage users & roles | — | — | ✓ |
| Search & filter catalogue | ✓ | ✓ | ✓ |

---

# Technical side

## Stack (as deployed)

| Layer | What |
|---|---|
| Hosting | Azure App Service for Containers (Linux, plan B1) |
| Image registry | Azure Container Registry (ACR) |
| CI/CD | GitHub Actions → testy → build obrazu → push do ACR → auto-deploy (webhook) |
| Container | `webdevops/php-nginx:8.2` (nginx + PHP-FPM w jednym obrazie) |
| Backend | PHP 8.2 / Laravel 12 |
| DB access | [Medoo](https://medoo.in/) |
| Database | SQLite (plik na trwałym wolumenie `/home`, autoinicjalizacja przy starcie) |
| Sessions | file-backed (`SESSION_DRIVER=file`) |
| Hashing | bcrypt (`password_hash` / `password_verify`) |
| HTTPS | terminowane na Azure App Service; Laravel `trustProxies` + `URL::forceScheme` |
| Frontend | HTML5 UP *Twenty* + Blade |

> Lokalnie aplikację można uruchomić również na MySQL (XAMPP) — wystarczy ustawić `DB_TYPE=mysql`
> w `.env`. Na chmurze używamy SQLite, bo nie wymaga osobnego serwera bazy (patrz niżej).

## Database

Schemat działającej bazy: [`database/plant_diary_sqlite.sql`](database/plant_diary_sqlite.sql) —
6 tabel (roles, users, user_roles, plant_types, plants, care_logs) z kluczami obcymi, ograniczeniami
CHECK, indeksami i danymi startowymi (role + typy roślin).

Baza jest tworzona automatycznie przy pierwszym starcie kontenera (`App\Services\Database`):
jeśli plik SQLite nie istnieje, aplikacja zakłada go ze schematu, a następnie idempotentnie
dopilnowuje istnienia konta administratora.

**Dlaczego SQLite, a nie zarządzany MySQL?** Konto Azure for Students blokowało utworzenie
MySQL Flexible Server (brak mocy obliczeniowej w dostępnych regionach). SQLite w kontenerze,
z plikiem na trwałym wolumenie `/home`, daje działającą i trwałą bazę bez osobnego serwera.

## Deployment (Azure)

- **Resource Group:** `technologie-chmurowe-rg` (region `switzerlandnorth` — jedyny dozwolony
  przez politykę konta studenckiego).
- **ACR:** przechowuje obraz `plantcare:latest`.
- **App Service (plan B1):** uruchamia kontener z ACR; HTTPS z domyślnym certyfikatem
  `*.azurewebsites.net`.
- **Trwałość danych:** `WEBSITES_ENABLE_APP_SERVICE_STORAGE=true`, baza pod
  `DB_DATABASE=/home/data/plant_diary.sqlite` (przeżywa restart kontenera).

## CI/CD (GitHub Actions)

Workflow [`.github/workflows/docker-build.yml`](.github/workflows/docker-build.yml):
1. **test** — instaluje zależności, uruchamia testy PHPUnit (bramka jakości);
2. **build-and-push** — buduje obraz Dockera i wypycha do ACR (rusza tylko, gdy testy przejdą);
3. **auto-deploy** — webhook ACR powiadamia App Service, który pobiera nowy obraz.

## Testy

- **Jednostkowe** (`tests/Unit`) — hashowanie haseł (bcrypt) i logika ról.
- **Feature / HTTP** (`tests/Feature`) — publiczne strony (`/up`, `/login`, `/register`)
  odpowiadają przez cały stos aplikacji.

Uruchomienie lokalnie: `php artisan test`.

## Konto administratora (do demonstracji)

- **Email:** `admin@plantcare.app`
- **Hasło:** `Admin12345`

## Todo list
- [x] Public plant-type catalogue (read-only, visible to guests)
- [x] User: add their own plants
- [x] User: log a care entry, view their diary
- [x] Admin UI: CRUD for plant types, user / role management
- [x] Search & filter + pagination + AJAX on the public catalogue
- [x] Containerised deployment to Azure (App Service + ACR)
- [x] CI/CD pipeline (GitHub Actions: tests → build → push → deploy)
- [x] HTTPS + bcrypt password hashing
