# Arquitectura hibrida aprobada — 2026-10-10

La solicitud explicita de la propietaria sustituye las ubicaciones Backend/Frontend de AGENTS y las propuestas historicas app/Modules, tablas inglesas, bigint, Vue/Inertia y clinical-app. Laravel permanece en la raiz; se usan doce propietarios funcionales en app/Modulos con Dominio, Aplicacion, Infraestructura y Presentacion. Los roles son permisos y consumidores, no modulos.

Base de partida: ARQUITECTURA 0d3942bd638c1294cad184c51a3f6224b8d1ab75. Referencia REINICIO: 7a6758e8bee25106c6282b9480d38e82ea72b0e3. Checkout separado ARQUI-NUEVA; las modificaciones no confirmadas del checkout Desktop no se copian ni alteran.

Fuentes consultadas: docs/architecture-audit/04_ARQUITECTURA_TO_BE.md, 05_CAPAS_Y_RESPONSABILIDADES.md, 06_MAPA_MODULOS.md, 15_PLAN_REFACTORIZACION_BACKEND.md, 18_SISTEMA_EXPERTO_INTEGRACION.md, 20_DECISIONES_ARQUITECTONICAS.md; docs/arquitectura/REMEMBERMIND_ARQUITECTURA_FRONTEND.md (deprecado); docs/sistema-experto/ARQUITECTURA_COMPLETA_SISTEMA_EXPERTO.md (propuesta); baseline congelado y decisiones posteriores.

El baseline vigente contiene 71 tablas operativas y 23 tablas expertas aprobadas por separado (D-123/D-137). No se cambia ninguna migracion, tabla, columna, cod_*, FK, factory ni dato clinico. En database solo se adapta un import de Historical2026Seeder a su clase trasladada. Los 96 modelos/bases conservan App\Models para evitar cambiar identidades polimorficas/Activitylog, relaciones, Fortify/Jetstream/Spatie y factories durante este corte; su propiedad se documenta individualmente en MODELOS_ELOQUENT.json.

Se conserva Livewire 4, Blade y resources/frontend/styles/design-system. Los alias publicos se registran explicitamente; las vistas modulares conservan sus nombres logicos mediante rutas de busqueda compatibles. Las rutas se componen respetando orden, grupos, middleware y binding. Las policies movidas se registran explicitamente.

El motor tecnico PHP existente se separa en motor-experto y no se reescribe en Python. No se autoriza activar inferencias clinicas, modificar reglas/umbrales o introducir pesos multicriterio. ApoyoDecision conserva lectura autorizada, persistencia tecnica y restricciones de prueba. La ejecucion clinica asincrona y la revision profesional requieren contrato/activacion aprobados; no se conecta un job a eventos clinicos sin esos contratos.

Los casos de uso criticos conservan transacciones y bloqueos. El trabajo se verifica por cortes y los limites de ejecucion se registran en INFORME_MIGRACION_HIBRIDA.md.
