# RememberMind — AGENTS.md

## Stack

- Laravel 13 + Jetstream (Livewire stack) + Fortify + Sanctum
- `php ^8.3`, `livewire/livewire ^4.3`
- `spatie/laravel-permission ^7.3`, `spatie/laravel-activitylog ^4.12`
- `barryvdh/laravel-dompdf`, `maatwebsite/excel`, `spatie/laravel-pdf`
- Tailwind CSS 3 + Alpine.js, **dark mode via `class` strategy**

## Dev commands

```bash
composer dev          # concurrently: server + queue:listen + pail + vite
composer test         # config:clear → php artisan test
npm run dev           # vite dev server
php artisan migrate --seed   # run all migrations + seeders
```

## Critical architecture

### User model is non-standard
- `User` PK = `cod_usu` (string `USU_XXXX`), not auto-increment
- Login field = `correo` (not `email`), configured in `config/fortify.php`
- Registration is **disabled** (`// Features::registration()` in fortify.php)
- Login gated on `estado === 'ACTIVO'` in `app/Providers/FortifyServiceProvider.php:45`
- `User::getNameAttribute()` returns `"nombres ap_paterno ap_materno"`

### Auth flow
1. User enters `correo` + password
2. `FortifyServiceProvider::authenticateUsing` checks credentials then `estado`
3. All admin routes use guard `auth:sanctum` + `config('jetstream.auth_session')` + `verified`
4. Permission-based middleware on every admin sub-route

### Routes
- `GET /` → welcome view
- `GET /dashboard` → `DashboardController@index` (single-dashboard-per-role pattern)
- All backend under `Route::prefix('admin')` with permission middleware
- Livewire components organized by functional area under `app/Livewire/`

### Seeder order matters
`DatabaseSeeder` runs in this sequence:
1. Infrastructure: `EstadoAdultoSeeder`, `RolesAndPermissionsSeeder`, permission seeders
2. Institutional data: `AreasInstitucionalesSeeder`, `CargoAdministrativoSeeder`, `EspecialidadSeeder`, etc.
3. Demo data: `AdminSeeder`, `AdultoMayorSeeder`, `EnfermeriaPacienteSeeder`

### Design system
All CSS variables in `resources/css/design-of-system/` (8 files imported by `app.css`).
Tailwind colors are **CSS variable-driven** (see `tailwind.config.js` — e.g. `fondo.panel`, `boton.principal`, `sidebar.bg`).
Never hardcode colors; use the semantic tokens.

## Testing

```bash
composer test          # runs config:clear + php artisan test
php artisan test --filter=Something
phpunit               # direct phpunit call also works
```

Tests use SQLite `:memory:` (see `phpunit.xml`). No external DB needed for tests.

## Key packages

| Purpose | Package |
|---------|---------|
| PDF | `barryvdh/laravel-dompdf`, `spatie/laravel-pdf`, `spatie/browsershot` |
| Excel | `maatwebsite/excel` |
| Roles/Permissions | `spatie/laravel-permission` |
| Audit log | `spatie/laravel-activitylog` |
| Animations | `gsap`, `aos`, `three`, `chart.js` |

## Environment

Runs on **Laragon** (Windows). The development database is PostgreSQL. Tests use SQLite `:memory:` and must remain portable between both engines.
