# RememberMind

Sistema web de gestión integral para residencia geriátrica, seguimiento clínico, funcional y cognitivo.

## Objetivo
Proporcionar una plataforma integral, robusta y segura para el registro, atención, supervisión clínica y gestión de residentes geriátricos, asegurando trazabilidad médica, control riguroso de fármacos, alertas oportunas y soporte a la toma de decisiones multidisciplinarias.

## Stack tecnológico
- **Backend:** PHP 8.3+, Laravel 13, Fortify, Sanctum
- **Frontend:** Livewire 4, Alpine.js, Tailwind CSS 3, Chart.js, GSAP, AOS
- **Base de Datos:** PostgreSQL (entorno principal de desarrollo y producción), SQLite `:memory:` (testing automatizado)
- **Permisos y Auditoría:** Spatie Laravel Permission 7.3, Spatie Activitylog 4.12
- **Reportes:** Barryvdh DomPDF 3.1, Spatie Laravel PDF 2.8, Maatwebsite Excel 3.1

## Requisitos
- PHP 8.3 o superior con extensiones activas (`pdo_pgsql`, `pdo_sqlite`, `intl`, `mbstring`, `openssl`, `curl`, `gd` o `imagick`)
- Composer 2+
- Node.js 20+ y npm
- PostgreSQL 15+

## Instalación
```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
```

## Variables de entorno
Configurar en `.env` los parámetros de conexión PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=remembermind
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

## Base de datos
BDD Operativa V2.1 — 70 tablas operativas congeladas y normalizadas:
- Consultar siempre la documentación oficial en `docs/base-de-datos/`.
- Prohibida la mutación estructural sin autorización previa.
- Todas las entidades emplean nomenclatura estandarizada `cod_<entidad>`.

## Migraciones
```bash
php artisan migrate --seed
```

## Ejecución
```bash
composer dev
```
O ejecutando los servicios por separado:
```bash
php artisan serve
npm run dev
```

## Compilación frontend
```bash
npm run build
```

## Pruebas
```bash
# Suite completa de tests PHP (SQLite en memoria)
composer test

# Tests específicos de verificación
php artisan test --filter=BddOperativaV2Test

# Tests frontend JavaScript (runner nativo node:test)
npm test
```

## Roles principales
- **Superadministrador:** Configuración institucional, gestión de accesos y supervisión global.
- **Administrador:** Gestión operativa de admisiones, personal y residentes.
- **Médico:** Valoraciones médicas integrales, diagnósticos, órdenes médicas y evolución clínica.
- **Enfermero / Personal de Cuidado:** Administración de medicación, registro de signos vitales, notas de enfermería y pases de turno.
- **Psicólogo / Fisioterapeuta / Nutricionista / Pedagogo:** Valoraciones y planes de intervención especializada.
- **Familiar:** Consulta protegida del estado y avances de su residente asignado (con estricto control anti-IDOR).

## Arquitectura
- `app/Backend/Modulos/`: Servicios y casos de uso organizados por módulo (`Admisiones`, `Alertas`, `Clinica`, `Identidad`, `Medicacion`, `Reportes`).
- `app/Frontend/Livewire/`: Componentes reactivos por dominio funcional.
- `app/Models/`: Modelos Eloquent normalizados bajo esquema V2 (`cod_usuario`, `cod_residente`).
- `resources/frontend/`: Estilos (`styles/`) y scripts (`scripts/`).
- `resources/views/`: Vistas Blade y plantillas modulares.

## Documentación
- [Índice General](docs/README.md)
- [Arquitectura Vigente](docs/arquitectura/README.md)
- [Base de Datos Operativa V2.1 (70 Tablas)](docs/base-de-datos/README.md)
- [Auditoría del Sistema](docs/auditoria.md)

## Seguridad
- Autenticación estricta mediante `correo` y verificación de `estado === 'ACTIVO'`.
- Registro público deshabilitado institucionalmente.
- Control de acceso granular por roles y permisos (RBAC Spatie).
- Documentación clínica almacenada en disco privado (`storage/app/private/`).
- Integridad referencial con validación exhaustiva de llaves foráneas.

## Estado del proyecto
Esquema V2.1 operativo, arquitectura modular consolidada, suite de pruebas automatizadas al 100% de aprobación.
