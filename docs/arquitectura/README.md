# Arquitectura vigente de RememberMind

Este documento define la organización canónica del código. Los inventarios de
`docs/architecture-audit` y `docs/refactorizacion-total` son históricos y no
deben utilizarse para decidir dónde agregar código nuevo.

## Estructura principal

```text
app/
├── Backend/
│   └── Modulos/
│       └── <Area>/
│           ├── Acciones/      # casos de uso transaccionales
│           └── Servicios/     # consultas y procesos reutilizables
├── Frontend/
│   └── Livewire/
│       ├── Administracion/
│       ├── Enfermeria/
│       ├── Medico/
│       ├── Psicologia/
│       ├── Superadministrador/
│       └── Compartido/
├── Http/
│   ├── Controllers/<Area>/    # adaptadores HTTP delgados
│   └── Requests/<Area>/       # validación de entrada
├── Models/                    # persistencia Eloquent y relaciones
├── Policies/                  # autorización por recurso
├── Exports/                   # generación de PDF y Excel
├── Jobs/                      # procesos asíncronos
├── Mail/                      # correos
└── Providers/                 # integración con Laravel/Fortify/Jetstream

resources/
├── frontend/                  # CSS y JavaScript fuente
└── views/                     # Blade, Livewire, correos y PDF
```

## Dirección de dependencias

```text
HTTP / Livewire -> Acciones y Servicios -> Models / Policies -> PostgreSQL
```

- Los controladores y componentes Livewire reciben entrada, autorizan y delegan.
- Las acciones representan una operación transaccional completa.
- Los servicios concentran consultas y procesos reutilizables de un módulo.
- Los modelos describen persistencia, relaciones, casts e invariantes locales.
- Un modelo no debe crear usuarios, residentes, turnos u otros agregados para
  completar silenciosamente información faltante.
- Backend no puede depender de Frontend ni de `Http`.
- El código compartido por varios roles vive en `Frontend/Livewire/Compartido`.

## Módulos vigentes

- Admisiones
- Alertas
- Clínica
- Documentos
- Enfermería
- Identidad
- Medicación
- Reportes
- Residentes

El futuro sistema experto se incorporará como módulo cuando exista código
ejecutable y pruebas. Hasta entonces su diseño permanece exclusivamente en
`docs/sistema-experto`.

## Convenciones

- Namespace y ruta física deben coincidir exactamente con PSR-4.
- No se crean raíces paralelas como `app/Actions`, `app/Livewire` o
  `app/Services`; se usan las ubicaciones canónicas anteriores.
- Las rutas se organizan por área, no por rol administrativo accidental.
- Las vistas no ejecutan consultas ni escriben directamente en la BDD.
- Toda mutación clínica debe tener actor, autorización y transacción explícitos.
- La BDD Operativa V2.1 de 70 tablas se define en `docs/base-de-datos`.

## Verificación

`tests/Feature/Architecture/FolderOrganizationTest.php` protege la estructura
canónica y verifica la correspondencia entre namespaces y carpetas.
