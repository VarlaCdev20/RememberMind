# Migración integral de RememberMind

Migración técnica implementada y validada sobre ARQUI-NUEVA mediante ejecución completa y reejecución dirigida del falso positivo, con las excepciones de Eloquent y las capacidades futuras del motor indicadas abajo.

## Resultado y ubicación

Rama local: `ARQUI-NUEVA`. Checkout de trabajo: `C:/Users/CARLAENCINAS/Documents/Codex/2026-10-10/files-pasted-by-the-user-prompt/work/RememberMind`. Base inspeccionada: `ARQUITECTURA 0d3942bd638c1294cad184c51a3f6224b8d1ab75`. Destino del PR: `REINICIO 7a6758e8bee25106c6282b9480d38e82ea72b0e3`.

Laravel permanece en la raíz, con doce módulos: Identidad, Institucion, Residentes, Admisiones, Clinica, Medicacion, Valoraciones, Cuidados, Social, Alertas, Reportes y ApoyoDecision. Cada uno dispone de Dominio, Aplicacion, Infraestructura y Presentacion. Las clases y vistas funcionales están físicamente dentro de su propietario. La limpieza posterior conserva `.gitkeep` solo en las capas principales sin implementación; las subcarpetas nacen cuando contienen archivos. Véase [la continuación de la limpieza](LIMPIEZA_CARPETAS_2026_10_10.md). La ubicación y las cifras originales de este informe son evidencia histórica del corte de migración.

Se trasladaron **282 archivos de clases**, **248 vistas** y **1 archivo de conocimiento**: 531 traslados físicos. Además se extrajeron **82 clases** desde interfaces existentes: 76 de Aplicacion, 2 de Dominio y 4 de Infraestructura, con 188 métodos públicos; este conteo incluye consultas, reglas, auxiliares y notificaciones, no significa el mismo número de procesos clínicos distintos. El inventario tiene 613 filas con origen, destino y estado comprobados. No quedan raíces ejecutables app/Backend ni app/Frontend.

## Funcionalidades conservadas

- Admisión formal atómica: preadmisión aprobada, residente, contacto, consentimiento, historia, admisión y ocupación inicial. Aprobar la solicitud no crea al residente. Los bloqueos y la protección frente a dobles admisiones permanecen.
- Medicación: prescripción, horarios, administración, omisión, PRN, anulaciones, historial y autoría profesional conservan sus flujos y estados. Enfermería no obtiene permiso para prescribir por esta migración.
- Clínica y valoraciones: signos, objetivos y basales, expediente, estudios, notas, instrumentos, Barthel y valoraciones profesionales conservan datos, umbrales, interpretación y errores existentes.
- Cuidados y alertas: planes, intervenciones, programación, ejecuciones, pases, incidentes, atención/cierre, historia y asignaciones delegan sus escrituras a Aplicacion.
- Identidad, institución y red de apoyo: cuentas, roles, personal, horarios, plazas, contactos, visitas, consentimiento, actividades y documentos conservan sus contratos. Los correos y la generación/almacenamiento documental extraídos residen en Infraestructura.

Los controladores y Livewire reciben/validan entradas, delegan y traducen el resultado a la misma interfaz. Las consultas/interpretaciones clínicas extraídas conservan literalmente los resultados del origen. No se introdujeron nuevas reglas clínicas, umbrales, roles, estados ni permisos. Se repiten en los casos de uso los controles existentes de sus rutas/Requests/UI y las Policies de vínculo del residente donde corresponden.

## Compatibilidad e integración

Se compararon las 296 rutas en orden, incluyendo métodos, URI, nombres, middleware, dominios, defaults, restricciones y campos de binding; sólo cambia el namespace según el mapa de traslados. Se conservan los alias de 71 componentes Livewire y sus 41 registros explícitos; 248 nombres lógicos de vistas apuntan a destinos modulares reales. El finder se extiende con sus paths/hints existentes y no mantiene copias Blade paralelas. Las Policies trasladadas tienen registros explícitos. El Design System en resources/frontend continúa como fuente visual y Tailwind incluye las vistas modulares.

Las pruebas de arquitectura protegen las cuatro capas, namespace/ruta, dependencia hacia Presentacion, pureza de Dominio/motor, ausencia de imports legacy, propiedad de vistas, contrato de rutas y transacciones/escrituras Eloquent fuera de Presentacion. La inspección AST de escrituras sigue las cadenas y variables de modelos; no constituye una prueba formal de todas las posibles mutaciones dinámicas. Blade ya no ejecuta las consultas Eloquent detectadas; conserva las referencias de Policy y catálogos constantes que no consultan la base.

## Esquema y excepción Eloquent

**Sin modificaciones de migraciones, tablas, columnas, PK/FK, relaciones estructurales ni factories.** Se conserva el baseline vigente: 71 tablas operativas (incluida la extensión de objetivos aprobada anteriormente) y 23 tablas expertas ya autorizadas. No se consultó ni migró una base operativa.

Las 96 clases/base de app/Models fueron inventariadas y comparadas individualmente. Se conservan sus FQCN para proteger relaciones, factories, autenticación y referencias polimórficas de auditoría/Spatie/Sanctum. Incluyen User, el Pivot ResidenteContacto, dos bases abstractas, 69 modelos operativos concretos y 23 modelos expertos concretos. MODELOS_ELOQUENT.json documenta propietario, tabla/PK, herencia y motivos por modelo; conservarlos aquí es una excepción explícita de este corte, no un traslado físico. ModeloExperto cambia el import de su builder trasladado; User, Residente y RegistroMovilidad ajustan referencias a servicios movidos. Las identidades y guardas de los modelos no cambian. Un futuro traslado exige tratar las identidades polimórficas almacenadas y su compatibilidad con datos existentes.

## Motor experto y límites reales

El motor PHP técnico COG-MEM existente se trasladó a motor-experto: 20 archivos existentes trasladados y dos extracciones (puerto y adaptador de hechos), 22 archivos PHP puros en total; 12 clases de adaptación/persistencia permanecen en ApoyoDecision. No depende de Laravel ni Eloquent y ejecuta sus fixtures técnicos sin bootstrap del framework. No se reescribió en Python y no se duplicó el motor dentro de Laravel.

SolicitudEvaluacionTecnicaV1 valida siete campos exactos, versión 1, uso PRUEBA_TECNICA, códigos existentes y fecha con zona horaria; calcula una clave estable de idempotencia. EvaluacionTecnicaSolicitada implementa ShouldDispatchAfterCommit. La prueba de contrato confirma emisión tras el commit exterior, ausencia tras rollback y espera en una transacción anidada. La persistencia técnica existente conserva locks y rechazo de duplicados.

La clave no garantiza deduplicación durable del transporte por sí misma. **No están activados** publicador de valoraciones clínicas, job/listener clínico, cola de inferencia clínica, nuevos pesos multicriterio, metodología adicional ni revisión profesional simulada. Contratos v1 y README identifican las decisiones pendientes de activación, hechos autorizados, transporte durable, reintentos/errores y revisión. Las carpetas previstas vacías no se presentan como capacidades implementadas.

## Verificaciones ejecutadas

| Verificación | Resultado | Evidencia cuantitativa |
|---|---|---|
| Baseline completo 0d3942bd | PASS | 991/1006 pasaron; 15 omitidas; 0 fallos; 0 errores; 19809 aserciones; 1071.61 s |
| Primera regresión de migración | Fallo | 999/1015 pasaron; 15 omitidas; 0 fallos; 1 errores; 27038 aserciones; 1101.88 s |
| Corrección Historical2026 | PASS | 3/3 pasaron; 0 omitidas; 0 fallos; 0 errores; 35 aserciones; 9.93 s |
| Regresión final del código congelado | Fallo | 1006/1022 pasaron; 15 omitidas; 1 fallos; 0 errores; 30244 aserciones; 1035.52 s |
| Corrección de comprobación numérica de gráficos (clase completa) | PASS | 21/21 pasaron; 0 omitidas; 0 fallos; 0 errores; 459 aserciones; 5.54 s |
| Validación consolidada por identidad de prueba (métrica derivada) | PASS | 1007/1022 identidades pasaron; 15 omitidas; 0 fallos pendientes; 0 errores |
| Arquitectura + alertas + vistas/casos seleccionados | PASS | 40/40 pasaron; 0 omitidas; 0 fallos; 0 errores; 3869 aserciones; 35.47 s |
| Policies y permisos directos finales + arquitectura/vistas | PASS | 29/29 pasaron; 0 omitidas; 0 fallos; 0 errores; 3822 aserciones; 20.15 s |
| PostgreSQL: admisión/camas/medicación | PASS | 19/19 pasaron; 0 omitidas; 0 fallos; 0 errores; 162 aserciones; 28.69 s |
| PostgreSQL: integración experta técnica | PASS | 70/70 pasaron; 0 omitidas; 0 fallos; 0 errores; 1899 aserciones; 200.36 s |
| PostgreSQL: bloqueo explícito en dos conexiones | PASS | 1/1 pasaron; 0 omitidas; 0 fallos; 0 errores; 4 aserciones; 0.67 s |
| Clasificación/análisis clínico comparado con baseline | PASS | 337 resultados exactos; revisión AST adicional de los resúmenes |
| Pruebas clínicas de consultas extraídas | PASS | 14 pruebas, 62 aserciones |
| Motor puro independiente | PASS | 5 casos técnicos sin Laravel/Eloquent |
| npm test | PASS | 147 pruebas |
| npm run build | PASS | Vite; 97 módulos; 3,94 s |
| composer validate / dump-autoload --optimize | PASS | composer.json válido; 10506 clases |
| PHP lint | PASS | 401 archivos, 0 fallos |
| Pint | PASS | Alcance controlado de módulos/motor y archivos modificados |
| php artisan view:cache | PASS | Compilación Blade satisfactoria |
| Comparación de rutas | PASS | 296 contratos ordenados idénticos tras mapear namespaces |

Los conjuntos se solapan: no sumar sus conteos para afirmar una cobertura total mayor. La validación consolidada toma las 1022 identidades class::name de la ejecución completa y sustituye únicamente los resultados de las 21 pruebas reejecutadas por sus resultados posteriores. No es una única ejecución completa verde; no se suman aserciones ni duraciones de conjuntos solapados. Los logs originales están en work; los XML finales y el de la corrección se adjuntan a la entrega. VERIFICACIONES.json conserva métricas, procedencia y lista de omisiones. Las credenciales/configuraciones de PostgreSQL están fuera del repositorio y de los entregables.

La primera regresión falló en una referencia literal antigua de Historical2026Seeder; se corrigió y su prueba aislada pasó (3/35). Durante las extracciones también se detectó una dependencia de auth y doble rollback en horarios; se corrigió conservando el contrato original y sus 2 pruebas/8 aserciones pasaron. Esas ejecuciones intermedias no se ocultaron ni se usan como resultado final.

La ejecución completa final tuvo un único fallo: ExploradorAdministrativoTest buscaba NaN/Infinity en toda la página y encontró NaN dentro del identificador aleatorio de Livewire QCGGNaNeSj3JK5LpgXDr, fuera de los gráficos. Se limitó la inspección a todos los nodos data-grafico, conservando la comprobación del tipo esperado, cantidades y porcentajes. La clase completa se reejecutó satisfactoriamente. Después de la ejecución completa no se cambió código de producción; sólo esa comprobación del test y su orden de imports. Se conservan ambos XML originales y el fallo original aparece explícitamente en la tabla.

Las 15 omisiones de SQLite corresponden a capacidades Jetstream desactivadas (registro público, API tokens, borrado de cuenta, verificación de correo y 2FA) y a dos pruebas opcionales de locks PostgreSQL. Las dos pruebas PostgreSQL se ejecutaron por separado: concurrencia real de dos procesos durante admisión y bloqueo de fila entre conexiones. La carrera produce una admisión aceptada y otra rechazada, con un solo residente/ocupación. Baseline PostgreSQL crítico: 19 pruebas/162 aserciones, también PASS.

## Deuda y excepciones preservadas

1. app/Models conserva 96 FQCN por la excepción descrita; no hay adaptador ejecutable de namespaces Backend/Frontend. Los nombres históricos de rutas, vistas lógicas, aliases y algunas clases AdultoMayor siguen como contratos públicos; las entidades/tablas reales son V2. Documentación histórica que menciona Backend/Frontend permanece como historia, no como raíz de ejecución.
2. DEC-OPEN-001: los flujos originales de decisión médica no usan una única transición (DECISION_ADMISION frente a PENDIENTE_ASIGNACION/DERIVADO/ADMITIDO→ACTIVO). Esta migración preserva cada ruta existente; unificarla exige decisión funcional, no una sustitución arquitectónica silenciosa.
3. AsistenciaPanel y TiposActividadPanel conservan la autorización original de acceso/lectura sin inventar un nuevo permiso de mutación. GuardarPersonalInstitucional conserva el contrato original de acceso a su formulario. RedApoyo conserva la deuda de alcance por ID en marcar responsable/emergencia; SaludMedicacionPanel conserva particularidades previas de selección/edición y valoración médica de admisión conserva su guard profesional original. Estos puntos requieren un corte funcional de seguridad específico.
4. SignosVitales/seguimiento mantienen diferencias y peculiaridades originales: ventana longitudinal 24h implementada como 36h y tratamiento distinto de medicación programada/administrada. Se separó el cálculo sin afirmar correcciones clínicas inexistentes.
5. Algunas auditorías de exportación/descarga y consultas generales permanecen en controladores/paneles como coordinación de presentación. No quedan transacciones ni escrituras Eloquent de los flujos detectados en Presentacion. Los servicios de aplicación existentes que mezclan coordinación y acceso Eloquent se conservan donde la separación adicional no tenía responsabilidad real; no se introdujo Repository/Unit of Work/CQRS.
6. No se realizó una auditoría manual visual de todas las pantallas en navegador. Sí se compilaron vistas/assets y ejecutaron las pruebas de interfaz, rutas y permisos existentes. No se enviaron correos reales ni se utilizaron datos operativos.

## Git y entrega

Commits técnicos locales sobre la base inspeccionada (anteriores al commit de este informe):

- `7a8cfed3` refactor(arquitectura): establece modulos y decision hibrida
- `814bb828` refactor(admisiones): migra acciones atomicas y consultas institucionales
- `0da42a45` refactor(clinica): separa reglas y migra medicacion y alertas
- `48334e01` refactor(modulos): migra cuidados identidad documentos y reportes
- `83228b4f` refactor(http): ubica controladores validaciones y policies por propietario
- `174d2238` refactor(livewire): migra componentes y conserva alias publicos
- `08dbdf0d` refactor(vistas): mueve Blade y compone rutas por propietario
- `427ec848` refactor: separar motor experto tecnico y contratos tras commit
- `23fecfdb` refactor(infraestructura): ubica exportaciones jobs y documenta modelos
- `1bb60e13` refactor(aplicacion): extrae operaciones y elimina dependencias legacy

La referencia REINICIO conserva su SHA exacto; no se modificó main, no hubo push ni merge. El checkout Desktop avanzó externamente de 0d3942bd a ed6df0e6 durante el trabajo. No se atribuye ese cambio a la migración ni se revierte; la base de ARQUI-NUEVA sigue siendo 0d3942bd. Sus archivos no confirmados (incluido ASISTENCIA COMU.xlsx) permanecen fuera del worktree y no fueron leídos ni copiados para esta migración.

PR_REINICIO.md contiene título, base/head y descripción listos para revisar/publicar. El diff contra REINICIO también incluye 42 commits antecedentes de ARQUITECTURA previos a 0d3942bd; la migración propia se compara con 0d3942bd. No se publica automáticamente. El parche y bundle de la entrega incluyen los commits de migración y requieren esa base; no se aplican automáticamente sobre el checkout Desktop que avanzó.

Entregables: INFORME_MIGRACION.md, INVENTARIO_MIGRACION.md/.json, MODELOS_ELOQUENT.json, ARBOL_REAL.txt, VERIFICACIONES.json, PR_REINICIO.md, LEEME_ENTREGA.md, COMMITS.txt, parche/bundle Git, los cinco XML finales y SHA256SUMS.txt. El árbol muestra archivos realmente existentes y omite dependencias, secretos y contenido generado.
