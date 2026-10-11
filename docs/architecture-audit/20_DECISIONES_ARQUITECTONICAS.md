# Registro de decisiones arquitectónicas

«ACEPTADA POR EVIDENCIA» expresa una conclusión de esta auditoría sobre viabilidad/estado observado. Ninguna entrada autoriza cambios de código sin aprobación de la autora. «PROPUESTA» es recomendación; «REQUIERE DECISIÓN» necesita validar alcance o preferencia.

| ADR | Estado | Decisión / contexto | Alternativa evaluada | Consecuencias y validación |
|---|---|---|---|---|
| ADR-001 Mantener Laravel | ACEPTADA POR EVIDENCIA | Framework 13 ya en lock; cubre sesión, ORM, colas y web | Reemplazar backend | Evita reescritura; verificar dependencias/runtime al ejecutar baseline |
| ADR-002 Monolito modular | PROPUESTA | 12 límites funcionales, un despliegue | Capas globales actuales o 24 módulos | Migrar por vertical, evitar dependencias circulares; véase 04/06 |
| ADR-003 MVC + capas | PROPUESTA | Controllers/Livewire como interfaz; Actions para procesos | MVC con toda lógica en Controller/Model; Clean estricta | Eloquent aceptado como persistencia/dominio pragmático; tests de invariantes |
| ADR-004 PostgreSQL | ACEPTADA POR EVIDENCIA | Ya activo: servidor 18.4, no migración MySQL supuesta | Cambiar motor por conveniencia local | Conservar y probar constraints/concurrencia en PostgreSQL, corregir docs |
| ADR-005 Vue + TS + Inertia 3 | REQUIERE DECISIÓN | Apto para UI compleja manteniendo Laravel y sesión | Conservar Livewire con backend mejorado | Reescritura UI 16–24 semanas-persona estimadas; spike de compatibilidad |
| ADR-006 Convivencia Livewire/Vue | PROPUESTA | Frontera por ruta/bundle, una Action canónica | Reescritura completa o dos frontends indefinidos | Retiro por corte; enlaces completos entre runtimes |
| ADR-007 RBAC + permisos + alcance | REQUIERE DECISIÓN | Seis roles base y grants explícitos por plantilla; Policies por residente | 9–11 roles por capacidad | Menos roles exige gestionar diferencias; jamás profesión=permiso automático |
| ADR-008 Persona/usuario/profesional | PROPUESTA | Identidad repetida y cuenta mezclada con empleo | Seguir copiando datos por módulo | Reconciliación costosa; persona sin usuario; migración por claves legacy |
| ADR-009 Experto desacoplado | PROPUESTA | Puerto y adaptador local/fake, Python futuro | Controller llama Python o Python escribe BD | Contratos/versiones e idempotencia; clínica funciona sin motor |
| ADR-010 No microservicios | ACEPTADA POR EVIDENCIA | Una desarrolladora y un dominio transaccional | Servicios distribuidos por profesión | Evita operación y consistencia distribuida; Python sería integración acotada |
| ADR-011 Historia desde el núcleo | PROPUESTA | Hechos/versiones/correcciones y dos tiempos | Depender de updated_at/activity_log | Más datos, consultas claras, no Event Sourcing universal |
| ADR-012 Identificadores | REQUIERE DECISIÓN | bigint identity interno, código legacy público separado | Mantener máximo+1 o UUID global | Migrar FK/morph con cuidado; no cambiar User PK en primer corte |
| ADR-013 No repositorios/bus universales | PROPUESTA | Eloquent/Queries/Actions directas | Repositorio/interfaz para cada tabla | Menos abstracciones; revisar solo cuando exista segundo backend real |
| ADR-014 Documentos privados | PROPUESTA | Support/Documents con versiones y propietario explícito | Disco public actual | Descargar mediante Policy; migrar URLs/archivos y verificar hashes |
| ADR-015 Chart.js y tokens actuales | PROPUESTA | Reutilizar biblioteca y semántica | ECharts + nueva paleta simultáneamente | Menor costo; wrapper Vue y pruebas de lifecycle/accesibilidad |
| ADR-016 shadcn-vue/Tailwind | REQUIERE DECISIÓN | Selección de componentes tras spike | Instalar starter kit o actualizar todo CSS de golpe | Fijar versiones; conservar Tailwind 3 mientras no se pruebe transición |
| ADR-017 Modelo completo de 66 tablas | REQUIERE DECISIÓN | 51 funcionales +15 técnicas; 57 piloto | Forzar 35–50 comprimiendo hechos distintos | Revisar alcance A; no implementar 66 tablas en una fase |
| ADR-018 Superadmin técnico | PROPUESTA | Sin bypass clínico permanente; concesión restringida/auditada | Gate::before true a todo | Separa administración técnica de responsabilidad asistencial |

## Procedimiento de aprobación

Registrar para cada ADR fecha, autora, estado aprobado/rechazado y motivo cuando se decida. Si se rechaza Vue, los ADR de integridad, capas y datos siguen siendo útiles: no dependen del framework de presentación. Si se reduce alcance del piloto, mantener el catálogo completo como futuro, sin crear tablas vacías.

## Fuentes externas verificadas

Consultadas el 2026-09-09; son referencias de compatibilidad y diseño, no evidencia de comportamiento del repositorio.

* [Laravel 13 releases](https://laravel.com/framework/docs/13.x/releases): mantener framework y comprobar requisitos al actualizar.
* [Inertia 3 upgrade guide](https://inertiajs.com/docs/v3/getting-started/upgrade-guide): adaptadores y mínimos de Laravel/PHP.
* [shadcn-vue Laravel](https://shadcn-vue.com/docs/installation/laravel): integración oficial; no se ejecutó instalación.
* [PostgreSQL constraints](https://www.postgresql.org/docs/current/ddl-constraints.html): restricciones relacionales y unicidad parcial.
* [WCAG 2.2](https://www.w3.org/TR/WCAG22/): referencia de accesibilidad; sin declaración de conformidad actual.
