# Auditoría de formularios de Enfermería — 14/10/2026

## Dictamen y alcance

**Cierre integral: FAIL.** Hay defectos reproducidos de acceso, contexto y coherencia entre escritores, además de mejoras visuales pendientes. No se observaron fallos en las suites seleccionadas que detectaran estos casos: pasar pruebas existentes no equivale a cerrar el flujo.

Auditoría sobre el árbol de trabajo local compartido, con modificaciones previas sin commit. Sin cambios de producto, permisos, BDD ni datos operativos durante esta auditoría. Solo este informe y artefactos sintéticos de prueba. Sin commit/push.

Se inspeccionaron las entradas de Signos, Dolor, Observación cognitiva, Conducta, Ingesta, Hidratación, Eliminación, Movilidad, Heridas/curaciones y Medicación; también los caminos alternativos HTTP y de Registros/Seguimiento diario. No equivale a una auditoría de todos los módulos de RememberMind.

## Backend: corregir primero

### B1 — P1 — Lectura de cuidados de un residente no asignado

- **Confirmado mediante petición HTTP en SQLite aislado.** Una cuenta Enfermería con los permisos habituales recibió HTTP 200 e hidratación de otro residente sintético sin asignación.
- `CuidadoController::index` (línea 32) autoriza únicamente `ResidentePolicy::view`, que para usuarios no familiares comprueba `residentes.ver`. La ruta añade `planes_cuidado.ver`, pero ninguno de esos controles verifica la asignación de Enfermería. La respuesta incluye varias familias clínicas completas.
- Impacto: el alcance correcto del directorio y de los formularios se puede eludir usando esta entrada HTTP.
- Corregir: aplicar el alcance contextual de consulta antes de leer, y permisos específicos para el contenido que se devuelve. No conceder acceso total a cuidados por poder ver un residente o un plan.
- Fuente: `app/Modulos/Cuidados/Presentacion/Http/Controllers/CuidadoController.php:32`, `app/Modulos/Residentes/Presentacion/Policies/ResidentePolicy.php:25`, registro `ruta_227` de Cuidados.

### B2 — P1 — Jornada proporcionada por el cliente en escritores HTTP

- **Confirmado mediante petición HTTP.** Hidratación se guardó con una jornada sintética futura que no era la jornada actual autorizada.
- `GestionarCuidadosHttp::registrar` valida existencia de `cod_jornada` y lo incorpora a `create`. Autorizar la operación sobre el residente actual no valida la jornada enviada.
- La definición genérica también recibe jornada en otras familias. En curación, el camino con fotografías la deriva del servidor; sin fotografías conserva el valor recibido.
- Corregir: derivar jornada del contexto autorizado, o validar explícitamente el contexto clínico cuando el contrato permita otra jornada. Probar los caminos con y sin fotos.
- Fuente: `app/Modulos/Cuidados/Aplicacion/Acciones/GestionarCuidadosHttp.php:33`, `:85`, `:130`.

### B3 — P1 — Validación distinta según la entrada

- **Confirmado mediante petición HTTP.** Hidratación aceptó volumen 0 y tolerancia `FUERA_DE_CATALOGO`; el escritor del formulario exige entero 1–10000 y su catálogo real.
- Los caminos HTTP de Ingesta, Eliminación y Movilidad también conservan definiciones y escritura directa diferentes a los escritores nuevos. Es evidencia estática adicional, no una explotación ejecutada de cada combinación.
- Corregir: converger en el caso de uso canónico respetando las competencias de cada actor, con pruebas de paridad de validación, contexto, historia y efectos secundarios. No basta compartir CSS ni cambiar únicamente el Request.
- Fuente: `GestionarCuidadosHttp.php:130` y `CuidadosEnfermeriaService::registrarHidratacion` (línea 328).

### B4 — P1 — Corrección de Seguimiento sobrescribe valores anteriores

- **Confirmado en código y en prueba Livewire existente ejecutada.** La prueba cambia ingesta 50 → 75 e hidratación 250 → 300, y exige conservar una sola fila con el valor nuevo.
- `SeguimientoDiarioService` actualiza la Atención y los registros relacionados mediante `actualizarOCrear`. No se conserva allí una versión clínica anterior ni se exige una razón de corrección; el componente anuncia «corregido con trazabilidad».
- Clasificación: **CONTRACT_DRIFT** frente a la preservación de historia exigida por AGENTS. La prueba actual reproduce el comportamiento y no demuestra conservación de procedencia clínica.
- Corregir: acordar/aplicar el mecanismo de corrección del dominio, preservando originales, autor y motivo. Activitylog técnico no sustituye esa procedencia. Si falta soporte estructural aprobado, aislar la edición y documentar la decisión necesaria; no añadir esquema por conveniencia.
- Fuente: `SeguimientoDiarioService.php:79`, `:316`; `SeguimientoDiarioPanel.php:435`; `tests/Feature/SeguimientoDiarioPersistenciaTest.php:79`.

### B5 — P2 — Eliminación: «Otro» sin especificación

- **Confirmado por escritor directo y lectura de componente/JS.** `color_orina=OTRO` se guarda sin descripción. El componente compartido genera elecciones, pero ningún campo condicional para especificar ese otro valor.
- También existen opciones Otro en aspecto de orina y color de heces. El incumplimiento corresponde a la instrucción de la propietaria de desplegar un campo al seleccionar Otro.
- Corregir: campo condicional con trim, validación de presencia y conservación explícita en la narrativa existente; limpiar detalles residuales al cambiar opción/tipo. No requiere inventar columnas.
- Fuente: `resources/views/components/ui/elimination-choice-group.blade.php:1`, `mis-residentes-eliminacion.blade.php:36`, `CuidadosEnfermeriaService.php:154`.

### B6 — P2 — Sueño: mapeo de Agitación a Insomnio

- **Confirmado en prueba aislada de la acción**, habilitando únicamente el permiso existente de crear sueño en la cuenta sintética: `calidad=INSOMNIO` y `agitacion=false` guardan `insomnio=false`. La acción deriva insomnio de agitación.
- El formulario genérico además solicita inicio, fin y deambulación, que no se trasladan a esa llamada de guardado. Su selector principal actualmente ofrece consulta de Sueño; este hallazgo no autoriza convertirlo en un formulario nuevo ni conceder creación a usuarios operativos.
- Corregir el mapeo del camino existente y retirar o conectar los campos que no persisten conforme al contrato. Agitación e insomnio son conceptos distintos; no se infiere uno del otro.
- Fuente: `RegistrarCuidadosDeTurno.php:98`; `registros-enfermeria.blade.php:154`; `RegistrosEnfermeria.php:479`.

## Calidad del registro: decisión pendiente

**Conducta vacía:** confirmado que `RegistrarConducta::ejecutar(..., [])` inserta una fila VIGENTE y auditoría sin observaciones. El test actual denominado «registro mínimo» permite precisamente ese caso. Cognición sí exige al menos un indicador o descripción significativa.

Clasificación: **GAP DE CONTRATO**, no afirmar una obligación clínica nueva desde la auditoría. Definir el mínimo documental de Conducta, y entonces alinear backend/UI/test. Conservar la diferencia entre false y ausencia. Fuente: `RegistrarConducta.php:20`, `tests/Feature/RegistroConductaTest.php:60`.

## Visual y UX

| Hallazgo | Prioridad | Evidencia / siguiente corrección |
|---|---|---|
| Cognición confirmada aún dice «Sin guardar · Respuestas seleccionadas» | P2 | Reproducido en Livewire después de confirmar una fila. Condicionar el resumen al resultado persistido. `observacion-cognitiva.blade.php:72`. |
| Contexto/resumen consumen casi toda la primera pantalla móvil | P2 | Capturas actuales a 390 px: Cognición coloca el inicio de captura junto al footer; Medicación dedica cerca de media pantalla a cabecera/contexto. Compactar jerarquía y disclosures sin ocultar residente ni alertas. |
| Jerarquía de cards todavía puede mejorar | P2 | En capturas, contexto, apoyo y captura tienen superficies/elevaciones muy similares. Reservar el relieve mayor para secciones principales y distinguir consulta de captura mediante encabezados y espaciado. Recomendación de diseño, no fallo de persistencia. |
| Unificación no completa en entradas genéricas | P2 | Registros conserva forms/labels/controles construidos con utilidades locales; los formularios nuevos usan patterns y componentes compartidos. Consolidar gradualmente por responsabilidad y verificar todos los consumidores. `registros-enfermeria.blade.php:90`. |
| Consistencia entre dashboards sin gate verde | P2 | `npm test`: falla `shell-visual-consistency.test.js:104` por diferencias de superficies/tinta/sombras de Enfermería. Es shell, no un error de captura. Resolver con el contrato visual vigente; no cambiar la expectativa solo para pasar. |

**Evidencia visual:** Cognición y Conducta en 1440/1280/1024/768/390; Medicación en esos cinco tamaños y nueve estados, 45 capturas. Se inspeccionaron capturas de composición y estados representativos; los harnesses comprobaron viewport, overflow e interacción. Runtime con Blade/Alpine y transporte sintético, no sesión operativa.

La galería CSS de 14 familias × cinco anchos conserva estados y pasa los pares de contraste muestreados (texto mínimo 5.75:1, borde 3.21:1). **No acredita contraste compuesto exhaustivo de cada glass ni tema oscuro de todos los formularios.** Tampoco sustituye el recorrido visual completo de Signos, Dolor, Ingesta, Hidratación, Eliminación, Movilidad, Heridas, Seguimiento e Incidentes. Ese cierre sigue pendiente; no se declara PASS global visual.

## Pruebas ejecutadas

- Suite seleccionada: **319 PASS / 2599 assertions**, SQLite en memoria: MisPacientesRedisenada, ObservacionCognitiva, RegistroConducta, FotografiasHeridas, AdministracionMedicacionFormulario, SeguridadCriticaEnfermeria, Fase3SignosVitales, ObjetivosSignosVitales y SignosVitalesPanelClasificacion.
- SeguimientoDiarioPersistencia: **2 PASS / 19 assertions**. Su expectativa de corrección no prueba preservación del original (B4).
- Exportación visual de Medicación: **1 PASS / 9 assertions**.
- Sondas de auditoría: **6 reproducidas / 15 assertions**. Sus aserciones verifican el comportamiento defectuoso o la brecha actual; no son un gate de aceptación verde.
- `npm test`: **186 PASS / 1 FAIL** (187 totales); fallo de shell detallado arriba.
- Build: **PASS**, aviso existente de JS >500 kB (510.74 kB), CSS principal 1209.43 kB sin gzip. Optimización de bundles es deuda adicional, no motivo para rediseñar el dominio.
- PostgreSQL, concurrencia, suite PHP global y QA oscuro exhaustivo: **no ejecutados en esta auditoría**. No extrapolar SQLite ni evidencia histórica.

Sondas reproducibles: `storage/app/qa/formularios-auditoria-2026-10-14/FormulariosAuditoriaTest.php` (ignorado por Git). Ejecutar con `php vendor/bin/phpunit -c phpunit.xml storage/app/qa/formularios-auditoria-2026-10-14/FormulariosAuditoriaTest.php --filter test_audit --no-progress`. Hereda fixtures sintéticos actuales de RegistroConductaTest. Los dos intentos iniciales de preparación tuvieron errores del harness (fecha de nacimiento ausente y permiso de Sueño ausente); se corrigió únicamente la preparación, sin tocar producto.

Capturas: `%TEMP%/remembermind-cognicion-qa/auditoria-formularios/`, `%TEMP%/remembermind-conducta-qa/auditoria-formularios/`, `%TEMP%/remembermind-medicacion-audit-qa/evidence/`. Build guardado en el mismo directorio QA de la auditoría.

## Orden recomendado

1. B1–B3: cerrar alcance de lectura, contexto de jornada y paridad de escritores HTTP.
2. B4: preservar historia al corregir Seguimiento; resolver cualquier decisión estructural separadamente.
3. B5/B6 y estado confirmado de Cognición.
4. Compactar móvil y consolidar componentes existentes; cerrar QA por formulario/estado/tema.
5. Resolver el gate de shell y ejecutar PostgreSQL/negativos/concurrencia pertinentes tras las correcciones.

Seguridad del alcance revisado: **FAIL por B1/B2**. Visual integral: **FAIL por defecto observado y cobertura incompleta**, con los subcasos ejecutados descritos. Sin certificaciones clínicas, WCAG ni producción.

## Seguimiento de correcciones seleccionadas — 14/10/2026

La propietaria seleccionó B5 y los hallazgos visuales/UX para corrección, manteniendo Otro. Resultado, reutilización, pruebas y cobertura responsive en [CORRECCIONES_FORMULARIOS_UX_2026_10_14.md](CORRECCIONES_FORMULARIOS_UX_2026_10_14.md). El estado confirmado de Cognición, la composición móvil, los genéricos y el gate de dashboards se corrigieron; Otro requiere precisión en el escritor canónico y la conserva en la narrativa existente. Este seguimiento no cambia retrospectivamente la evidencia de la auditoría ni declara cerrados los otros hallazgos backend.
